<?php

class Depute_service
{

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('deputes_model');
        $this->CI->load->model('votes_model');
        $this->CI->load->model('groupes_model');
        $this->CI->load->model('depute_edito');
        $this->CI->load->model('meta_model');
        $this->CI->load->model('breadcrumb_model');
        $this->CI->load->model('legislature_model');
    }


    public function get_statistics($data, $legislature, $mpId, $groupe_id)
    {
        if (in_array($legislature, legislature_all())) {
            // PARTICIPATION
            $data['participation'] = $this->CI->deputes_model->get_stats_participation_solennels($mpId, $legislature);
            if ($data['participation'] && $data['participation']['votesN'] >= 10) {
                $data['no_participation'] = FALSE;
                // GET ALL DATA FOR PARTICIPATION
                $data['participation']['all'] = $this->CI->deputes_model->get_stats_participation_solennels_all($legislature);
                $data['participation']['group'] = $this->CI->deputes_model->get_stats_participation_solennels_group($legislature, $groupe_id);
                if (isset($data['participation']['score'])) {
                    $data['edito_participation']['all'] = $this->CI->depute_edito->participation($data['participation']['score'], $data['participation']['all']); //edited for ALL
                    $data['edito_participation']['group'] = $this->CI->depute_edito->participation($data['participation']['score'], $data['participation']['group']); //edited for GROUP
                }
            } else {
                $data['no_participation'] = TRUE;
            }

            // LOYALTY
            $data['loyaute'] = $this->CI->deputes_model->get_stats_loyaute($mpId, $legislature);
            if ($data['loyaute'] && $data['loyaute']['votesN'] >= 10) {
                $data['no_loyaute'] = FALSE;
                // GET ALL DATA FOR LOYALTY
                $data['loyaute']['all'] = $this->CI->deputes_model->get_stats_loyaute_all($legislature);
                $data['loyaute']['group'] = $this->CI->deputes_model->get_stats_loyaute_group($legislature, $groupe_id);
                if (isset($data['loyaute']['score'])) {
                    $data['edito_loyaute']['all'] = $this->CI->depute_edito->loyaute($data['loyaute']['score'], $data['loyaute']['all']); // edited for ALL
                    $data['edito_loyaute']['group'] = $this->CI->depute_edito->loyaute($data['loyaute']['score'], $data['loyaute']['group']); //edited for GROUP
                }
                // loyalty history
                $data['loyaute_history'] = $this->CI->deputes_model->get_stats_loyaute_history($mpId, $legislature);
            } else {
                $data['no_loyaute'] = TRUE;
            }

            // PROXIMITY WITH MAJORITY
            if (!in_array($groupe_id, $this->CI->groupes_model->get_all_groupes_majority())) {
                $data['majorite'] = $this->CI->deputes_model->get_stats_majorite($mpId, $legislature);
                if ($data['majorite'] && $data['majorite']['votesN'] >= 10) {
                    $data['no_majorite'] = FALSE;
                    // GET ALL DATA FOR PROXIMITY WITH MAJORITY
                    $data['majorite']['all'] = $this->CI->deputes_model->get_stats_majorite_all($legislature); // DOUBLE CHECK --> ONLY THOSE NOT FROM THE GROUP OF THE MAJORITY
                    $data['majorite']['group'] = $this->CI->deputes_model->get_stats_majorite_group($legislature, $groupe_id);
                    $data['edito_majorite']['all'] = $this->CI->depute_edito->majorite($data['majorite']['score'], $data['majorite']['all']); // edited for ALL
                    $data['edito_majorite']['group'] = $this->CI->depute_edito->majorite($data['majorite']['score'], $data['majorite']['group']); //edited for GROUP
                } else {
                    $data['no_majorite'] = TRUE;
                }
            }

            // PROXIMITY WITH ALL GROUPS
            if ($legislature == legislature_current() && dissolution() === false) {
                $data['accord_groupes'] = $this->CI->deputes_model->get_accord_groupes_actifs($mpId, legislature_current());
                $data['accord_groupes_all'] = $this->CI->deputes_model->get_accord_groupes_all($mpId, legislature_current());
                // Positionnement politique
                $accord_groupes_sorted = $data['accord_groupes'];
                if (empty($accord_groupes_sorted)) {
                    $data["no_votes"] = TRUE;
                } else {
                    $data["no_votes"] = FALSE;
                    $sort_key  = array_column($accord_groupes_sorted, 'accord');
                    array_multisort($sort_key, SORT_DESC, $accord_groupes_sorted);
                    $data['proximite'] = $this->CI->depute_edito->positionnement($accord_groupes_sorted, $groupe_id);
                }
            } else /* LEGISLATURE 14 */ {
                $data['accord_groupes'] = $this->CI->deputes_model->get_accord_groupes_all($mpId, $legislature);
                $data['accord_groupes_all'] = $data['accord_groupes'];

                if ($data['accord_groupes']) {
                    $data['no_votes'] = FALSE;
                } else {
                    $data['no_votes'] = TRUE;
                }
            }
            $accord_groupes_n = count($data['accord_groupes']);
            $accord_groupes_divided = round($accord_groupes_n / 2, 0, PHP_ROUND_HALF_UP);
            $data['accord_groupes_first'] = array_slice($data['accord_groupes'], 0, $accord_groupes_divided);
            $data['accord_groupes_first'] = array_slice($data['accord_groupes_first'], 0, 3);
            $data['accord_groupes_last'] = array_slice($data['accord_groupes'], $accord_groupes_divided, $accord_groupes_n);
            $data['accord_groupes_last'] = array_slice($data['accord_groupes_last'], -3);
            $data['accord_groupes_last_sorted'] = array_reverse($data['accord_groupes_last']);
        }

        return $data;
    }




    public function get_general_infos(
        array $data,
        string $mp_id,
        string $legislature,
        string $name_last,
        string $depute_full_name
    ): array {
        // Infos legislature 
        $data['legislature'] = $this->CI->legislature_model->get_legislature($legislature);
        // Infos générales
        $data['depute']['dateNaissanceFr'] = utf8_encode(
            strftime('%d %B %Y', strtotime($data['depute']['birthDate']))   // birthdate
        );
        $data['depute']['circo_abbrev'] = abbrev_n($data['depute']['circo'], TRUE); // circo number
        $data['politicalParty'] = $this->CI->deputes_model->get_political_party($mp_id); // political party

        if ($legislature == legislature_current()) {

            $data['election_result'] = $this->CI->deputes_model->get_election_result(
                $data['depute']['departementCode'],
                $data['depute']['circo'],
                $name_last,
                2024,
                $data['legislature']
            );            

            if ($data['election_result']) {
                $round = $data['election_result']['tour'];

                $data['election_opponents'] = $this->CI->deputes_model->get_election_opponent(
                    $data['depute']['departementCode'],
                    $data['depute']['circo'],
                    2024,
                    $round,
                    $data['legislature'],
                    $data['election_result']['partielle']
                );

                if ($data['election_result']['partielle'] === FALSE) {
                    $data['election_infos'] = $this->CI->deputes_model->get_election_infos(
                        $data['depute']['departementCode'],
                        $data['depute']['circo'],
                        2024,
                        $round
                    );

                    $data['election_infos']['participation'] = round(
                        $data['election_infos']['votants'] * 100 / $data['election_infos']['inscrits']
                    );
                }                

                
                if ($round == 1) { // Elected 1st round                   
                    if (!empty($data['election_opponents'])) {
                        array_walk($data['election_opponents'], function (&$candidate) {
                            $candidate['candidat'] = $candidate['nameFirst'] . ' ' . ucfirst(strtolower($candidate['nameLast']));
                        });
                    }

                    // Prepare 2 top candidates and group others
                    $topCandidates = array_slice($data['election_opponents'], 0, 2);
                    $others = array_slice($data['election_opponents'], 2);

                    if (count($others) > 0) {
                        $totalVoix = 0;
                        $totalPct = 0.0;

                    foreach ($others as $candidate) {
                        $totalVoix += $candidate['voix'];
                        $totalPct += $candidate['pct_exprimes'];
                    }

                    $topCandidates[] = [
                        'nameLast' => '',
                        'nameFirst' => '',
                        'sexe' => '',
                        'voix' => $totalVoix,
                        'pct_exprimes' => $totalPct,
                        'tour_election' => '1er',
                        'candidat' => 'Autres candidats'
                    ];
                }
                $data['election_opponents'] = $topCandidates;

                } elseif ($round == 2) { // Elected 2nd round
                    if ($data['election_opponents']) {
                        foreach ($data['election_opponents'] as $key => $value) {
                            $data['election_opponents'][$key]['candidat'] = $value['nameFirst'] . ' ' .
                                ucfirst(strtolower($value['nameLast']));
                        }
                    }
                }
            }          
        }

        return $data;
    }

    public function get_other_mps(
        string $legislature,
        string $groupe_id,
        string $name_last,
        string $mp_id,
        string $active,
        string $depute_dpt
    ): array {

        if ($legislature == legislature_current()) {
            $other_deputes = $this->CI->deputes_model->get_other_deputes($groupe_id, $name_last, $mp_id, $active, $legislature);
        } else {
            $other_deputes = $this->CI->deputes_model->get_other_deputes_legislature($name_last, $mp_id, $legislature);
        }

        $other_deputes_dpt = $this->CI->deputes_model->get_deputes_all(legislature_current(), TRUE, $depute_dpt);

        return [
            'other_deputes' => $other_deputes,
            'other_deputes_dpt' => $other_deputes_dpt,
        ];
    }



    public function get_explication_details(string $mp_id, string $legislature, array $gender, string $first_person = 'false'): ?array
    {
        $explication = $this->CI->deputes_model->get_last_explication($mp_id, $legislature);

        if ($explication) {
            $explication['vote_depute_edito'] = $this->CI->depute_edito->get_explication($explication['vote_depute'], $gender, $first_person);
        }

        return $explication;
    }



    public function get_mp_history_data(array $data, string $mp_id): array
    {
        $data['depute']['datePriseFonctionLettres'] = utf8_encode(strftime('%B %Y', strtotime($data['depute']['datePriseFonction'])));
        $data['mandat_edito'] = $this->CI->depute_edito->get_nbr_lettre($data['depute']['mandatesN']);
        $data['history_average'] = round($this->CI->deputes_model->get_average_length_as_mp(legislature_current()));
        $data['history_edito'] = $this->CI->depute_edito->history(round($data['depute']['mpLength'] / 365), $data['history_average']);
        $data['mandats'] = $this->CI->deputes_model->get_historique_mandats($mp_id);
        $data['mandatsReversed'] = array_reverse($data['mandats']);

        return $data;
    }


    public function get_mp_page_resources(array $data, string $depute_full_name, string $nameUrl): array
    {
        // META
        $data['url'] = $this->CI->meta_model->get_url();
        $data['title_meta'] = $depute_full_name . " - Activité Parlementaire | Datan";
        $data['description_meta'] = "Découvrez les résultats des votes " . $data['gender']['du'] .
            " député" . $data['gender']['e'] . " " . $depute_full_name .
            " : taux de participation, loyauté avec son groupe, proximité avec la majorité présidentielle.";
        $data['title'] = $depute_full_name;
        $data['title_breadcrumb'] = mb_substr($data['depute']['nameFirst'], 0, 1) . '. ' .
            $data['depute']['nameLast'];


        // BREADCRUMB
        $data['breadcrumb'] = [
            [
                'name'   => 'Datan',
                'url'    => base_url(),
                'active' => false
            ],
            [
                'name'   => 'Députés',
                'url'    => base_url() . 'deputes',
                'active' => false
            ],
            [
                'name'   => $data['depute']['departementNom'] . ' (' . $data['depute']['departementCode'] . ')',
                'url'    => base_url() . 'deputes/' . $data['depute']['dptSlug'],
                'active' => false
            ],
            [
                'name'   => $data['title_breadcrumb'],
                'url'    => base_url() . 'deputes/' . $data['depute']['dptSlug'] . '/depute_' . $nameUrl,
                'active' => true
            ]
        ];

        $data['breadcrumb_json'] = $this->CI->breadcrumb_model->breadcrumb_json($data['breadcrumb']);

        // OPEN GRAPH
        $controller = $this->CI->router->fetch_class() . "/" . $this->CI->router->fetch_method();
        $data['ogp'] = $this->CI->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);

        // MICRODATA
        $data['schema'] = $this->CI->deputes_model->get_person_schema($data['depute']);

        // CSS
        $data['critical_css'] = 'depute_individual';
        $data['css_to_load'] = [
            ['url' => css_url() . 'circle.css', 'async' => TRUE],
            ['url' => asset_url() . 'css/flickity.min.css', 'async' => TRUE]
        ];

        // JS
        $data['js_to_load'] = ['libraries/flickity/flickity.pkgd.min'];

        // PRELOADS
        $data['preloads'] = [
            ['href' => asset_url() . 'imgs/cover/hemicycle-front-375.jpg', 'as' => 'image', 'media' => '(max-width: 575.98px)'],
            ['href' => asset_url() . 'imgs/cover/hemicycle-front-768.jpg', 'as' => 'image', 'media' => '(min-width: 576px) and (max-width: 970px)'],
            ['href' => asset_url() . 'imgs/cover/hemicycle-front.jpg', 'as' => 'image', 'media' => '(min-width: 970.1px)']
        ];

        return $data;
    }
}
