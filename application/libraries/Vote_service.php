<?php

class Vote_service
{

    // Minimum share of MPs with a known seat to draw the real seat plan
    const PLAN_REEL_MIN = 0.95;

    private $votes_order = array('pour', 'abstention', 'contre', 'non-votant', 'absent');

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('groupes_model');
    }

    /**
     * Hemicycle of a vote: each MP on their real seat (placeHemicyle) when the data allows it,
     * otherwise a schematic layout (groups from left to right, then by vote).
     */
    public function get_hemicycle(array $deputes, array $absents, array $mandats, string $date): array
    {
        $votes = array();
        foreach ($deputes as $depute) {
            // In the vote without a cast ballot: non-voting (e.g. presiding the sitting)
            $vote = in_array($depute['vote_libelle'], array('pour', 'abstention', 'contre')) ? $depute['vote_libelle'] : 'non-votant';
            $votes[] = array('vote' => $vote, 'groupe' => (string) $depute['libelleAbrev'], 'depute' => $depute);
        }
        foreach ($absents as $absent) {
            $votes[] = array('vote' => 'absent', 'groupe' => (string) $absent['libelleAbrev'], 'depute' => $absent);
        }

        $plan = $this->read_plan('hemicycle_sieges.svg');
        list($places, $moved) = $this->get_places_reelles($votes, $mandats, $date, $plan['positions']);
        if ($votes && count($places) >= self::PLAN_REEL_MIN * count($votes)) {
            // The open data only gives the current seat: an MP who changed group after the vote now sits with their new group
            $places = array_diff_key($places, array_flip($moved));
            $places = $this->complete_places($votes, $places, $plan['positions']);
            return $this->build($plan, $votes, $places, TRUE);
        }

        $plan = $this->read_plan('hemicycle_empty.svg');
        return $this->build($plan, $votes, $this->get_places_schematiques($votes, $plan['positions']), FALSE);
    }

    // Recorded seat, otherwise the seat of a later MP of the same constituency and group (a substitute usually takes over the seat)
    private function get_places_reelles(array $votes, array $mandats, string $date, array $positions): array
    {
        $current = array();
        foreach ($mandats as $mandat) {
            if ($mandat['dateDebut'] <= $date && ($mandat['dateFin'] === NULL || $mandat['dateFin'] >= $date)) {
                if (!isset($current[$mandat['mpId']]) || $this->siege($mandat) !== NULL) {
                    $current[$mandat['mpId']] = $mandat;
                }
            }
        }

        $places = array();
        $moved = array();
        $occupied = array();
        foreach ($votes as $i => $vote) {
            $mandat = isset($current[$vote['depute']['mpId']]) ? $current[$vote['depute']['mpId']] : NULL;
            $siege = $mandat ? $this->siege($mandat) : NULL;
            if ($siege !== NULL && isset($positions[$siege]) && !isset($occupied[$siege])) {
                $places[$i] = $siege;
                $occupied[$siege] = TRUE;
                if ($mandat['groupe'] !== $vote['groupe']) {
                    $moved[] = $i;
                }
            }
        }

        foreach ($votes as $i => $vote) {
            if (isset($places[$i]) || !isset($current[$vote['depute']['mpId']])) {
                continue;
            }
            $mandat = $current[$vote['depute']['mpId']];
            foreach ($mandats as $suivant) {
                $siege = $this->siege($suivant);
                if ($siege !== NULL && isset($positions[$siege]) && !isset($occupied[$siege])
                    && $suivant['departement'] === $mandat['departement'] && $suivant['circo'] === $mandat['circo']
                    && $suivant['dateDebut'] > $mandat['dateDebut'] && $suivant['groupe'] === $vote['groupe']) {
                    $places[$i] = $siege;
                    $occupied[$siege] = TRUE;
                    break;
                }
            }
        }

        return array($places, $moved);
    }

    // MPs still without a seat: free seat closest to the other members of their group
    private function complete_places(array $votes, array $places, array $positions): array
    {
        $free = array_diff_key($positions, array_flip($places));
        foreach ($votes as $i => $vote) {
            if (isset($places[$i]) || !$free) {
                continue;
            }
            $x = $y = $n = 0;
            foreach ($places as $j => $siege) {
                if ($votes[$j]['groupe'] === $vote['groupe']) {
                    $x += $positions[$siege]['cx'];
                    $y += $positions[$siege]['cy'];
                    $n++;
                }
            }
            $best = NULL;
            $best_distance = INF;
            foreach ($free as $siege => $position) {
                $distance = $n ? pow($position['cx'] - $x / $n, 2) + pow($position['cy'] - $y / $n, 2) : 0;
                if ($distance < $best_distance) {
                    $best = $siege;
                    $best_distance = $distance;
                }
            }
            $places[$i] = $best;
            unset($free[$best]);
        }
        return $places;
    }

    // Groups from left to right, then by vote within each group
    private function get_places_schematiques(array $votes, array $positions): array
    {
        $order = array_flip($this->CI->groupes_model->get_groupes_order_hemicycle());
        $rank = function ($groupe) use ($order) {
            return isset($order[$groupe]) ? $order[$groupe] : $order['NI'] - 0.5;
        };
        $indexes = array_keys($votes);
        usort($indexes, function ($a, $b) use ($votes, $rank) {
            return array($rank($votes[$a]['groupe']), array_search($votes[$a]['vote'], $this->votes_order), $a)
                <=> array($rank($votes[$b]['groupe']), array_search($votes[$b]['vote'], $this->votes_order), $b);
        });

        $places = array();
        $sieges = array_keys($positions);
        foreach ($indexes as $k => $i) {
            if (!isset($sieges[$k])) {
                break;
            }
            $places[$i] = $sieges[$k];
        }
        return $places;
    }

    private function build(array $plan, array $votes, array $places, bool $reel): array
    {
        $by_siege = array_flip($places);
        $circles = array();
        $seats = array();
        foreach ($plan['positions'] as $siege => $position) {
            $circle = $position;
            $circle['seat'] = NULL;
            if (isset($by_siege[$siege])) {
                $circle['seat'] = count($seats);
                $seats[] = $votes[$by_siege[$siege]];
            }
            $circles[] = $circle;
        }

        // Counts computed on the seats actually drawn
        $groupes = array();
        foreach ($circles as $circle) {
            if ($circle['seat'] === NULL) {
                continue;
            }
            $seat = $seats[$circle['seat']];
            $abbrev = $seat['groupe'];
            if (!isset($groupes[$abbrev])) {
                $color = $this->CI->groupes_model->get_groupe_color(array($abbrev, $seat['depute']['couleurAssociee']));
                $groupes[$abbrev] = array(
                    'libelleAbrev' => $abbrev,
                    'libelle' => (string) $seat['depute']['libelle'],
                    'color' => preg_match('/^#[0-9a-f]{3,8}$/i', (string) $color) ? $color : '#9E9E9E',
                    'seats' => 0,
                    'counts' => array_fill_keys($this->votes_order, 0),
                    'x' => 0
                );
            }
            $groupes[$abbrev]['seats']++;
            $groupes[$abbrev]['counts'][$seat['vote']]++;
            $groupes[$abbrev]['x'] += $circle['cx'];
        }
        // Legend in the left-to-right order of the groups
        uasort($groupes, function ($a, $b) {
            return $a['x'] / $a['seats'] <=> $b['x'] / $b['seats'];
        });

        return array(
            'reel' => $reel,
            'viewbox' => $plan['viewbox'],
            'circles' => $circles,
            'seats' => $seats,
            'groupes' => array_values($groupes)
        );
    }

    private function read_plan(string $file): array
    {
        $svg = file_get_contents(FCPATH . 'assets/imgs/hemycicle_position/' . $file);
        preg_match('/viewBox="([\d. ]+)"/', $svg, $viewbox);
        preg_match_all('/<circle (?:id="s(\d+)" )?cx="([\d.]+)" cy="([\d.]+)" r="([\d.]+)"\/>/', $svg, $matches, PREG_SET_ORDER);
        $positions = array();
        foreach ($matches as $k => $match) {
            $positions[$match[1] !== '' ? (int) $match[1] : $k] = array('cx' => (float) $match[2], 'cy' => (float) $match[3], 'r' => (float) $match[4]);
        }
        return array('viewbox' => $viewbox[1], 'positions' => $positions);
    }

    // placeHemicyle is stored as a string, sometimes zero-padded ("001") or empty for ended mandates
    private function siege(array $mandat)
    {
        $siege = trim((string) $mandat['siege']);
        return ctype_digit($siege) ? (int) $siege : NULL;
    }

}
