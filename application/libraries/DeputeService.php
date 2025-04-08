<?php

class DeputeService 
{

    public function __construct() 
    {
        $this->CI =& get_instance();
        $this->CI->load->model('deputes_model');
        $this->CI->load->model('votes_model');
        $this->CI->load->model('groupes_model');
        $this->CI->load->model('depute_edito');
        $this->CI->load->model('meta_model');
        $this->CI->load->model('breadcrumb_model');
    }


    public function get_hatvp_info(string $mp_id): array
    {
        return [
            'hatvp' => $this->CI->deputes_model->get_hatvp_url($mp_id),
            'hatvpJobs' => $this->CI->deputes_model->get_last_hatvp_job($mp_id)
        ];
    }


    public function get_group_info(array $data, string $mp_id, string $groupe_id): array 
    {
    if (!empty($data['depute']['libelle'])) {
        $data['depute']['couleurAssociee'] = $this->CI->groupes_model->get_groupe_color(array($data['depute']['libelleAbrev'], $data['depute']['couleurAssociee']));
        
        $data['group_president'] = $this->CI->deputes_model->depute_group_president($mp_id, $groupe_id);
        $data['isGroupPresident'] = !empty($data['group_president']) ? TRUE : FALSE;
    } else {
        $groupe_id = NULL;
        $data['isGroupPresident'] = FALSE;  
    }

    return $data;  
    }


    public function get_general_infos(
        array $data,
        string $mp_id,
        string $legislature,
        string $name_last,
        string $depute_full_name
    ): array
    {
        // Infos générales
        $data['depute']['dateNaissanceFr'] = utf8_encode(
            strftime('%d %B %Y', strtotime($data['depute']['birthDate']))   // birthdate
        );
    
        $data['depute']['circo_abbrev'] = abbrev_n($data['depute']['circo'], TRUE); // circo number
        $data['politicalParty'] = $this->CI->deputes_model->get_political_party($mp_id); // political party
        $data['election_canceled'] = NULL;

        $canceled = array(
            "Annulation de l'élection sur décision du Conseil constitutionnel",
            "Démission d'office sur décision du Conseil constitutionnel"
        );

        if ($legislature == legislature_current()) {
            $data['election_canceled'] = $this->CI->deputes_model->get_election_canceled($mp_id, $legislature);

            if ($data['depute']['datePriseFonction'] == '2024-07-01') { // Elected 1st round
                $data['election_result'] = $this->CI->deputes_model->get_election_result(
                    $data['depute']['departementCode'], $data['depute']['circo'], $name_last, 2024, 1
                );
                $data['election_opponents_all'] = $this->CI->deputes_model->get_election_opponent(
                    $data['depute']['departementCode'], $data['depute']['circo'], 2024, 1
                );
                $data['election_infos'] = $this->CI->deputes_model->get_election_infos(
                    $data['depute']['departementCode'], $data['depute']['circo'], 2024, 1
                );
                $data['election_infos']['participation'] = round(
                    $data['election_infos']['votants'] * 100 / $data['election_infos']['inscrits']
                );
                $data['election_opponents']['all']['voix'] = 0;
                $data['election_opponents']['all']['candidat'] = "Reste des candidats";
                foreach ($data['election_opponents_all'] as $x) {
                    $data['election_opponents']['all']['voix'] += $x['voix'];
                }
            } elseif ($data['depute']['datePriseFonction'] == '2024-07-08') { // Elected 2nd round
                $data['election_result'] = $this->CI->deputes_model->get_election_result(
                    $data['depute']['departementCode'], $data['depute']['circo'], $name_last, 2024, 2
                );
                $data['election_opponents'] = $this->CI->deputes_model->get_election_opponent(
                    $data['depute']['departementCode'], $data['depute']['circo'], 2024, 2
                );
                $data['election_infos'] = $this->CI->deputes_model->get_election_infos(
                    $data['depute']['departementCode'], $data['depute']['circo'], 2024, 2
                );
                $data['election_infos']['participation'] = round(
                    $data['election_infos']['votants'] * 100 / $data['election_infos']['inscrits']
                );
                if ($data['election_opponents']) {
                    foreach ($data['election_opponents'] as $key => $value) {
                        $data['election_opponents'][$key]['candidat'] = $value['nameFirst'] . ' ' .
                            ucfirst(strtolower($value['nameLast']));
                    }
                }
            } elseif (isset($data['election_canceled']['causeFin']) && in_array($data['election_canceled']['causeFin'], $canceled)) {
                switch ($data['depute']['causeFin']) {
                    case "Annulation de l'élection sur décision du Conseil constitutionnel":
                        $data['election_canceled']['cause'] = "L'élection de " . $depute_full_name .
                            ", qui s'est tenue pendant les législatures de juin 2017, a été invalidée par le Conseil " .
                            "constitutionnel en " . $data['election_canceled']['dateFinFR'] . ".";
                        break;
                    default:
                        $data['election_canceled']['cause'] = NULL;
                        break;
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
    ): array 
    {
        
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



    public function get_explication_details(string $mp_id, string $legislature, array $gender): ?array
    {
        $explication = $this->CI->deputes_model->get_last_explication($mp_id, $legislature);
        
        if ($explication) {
            $explication['vote_depute_edito'] = $this->CI->depute_edito->get_explication($explication['vote_depute'], $gender);
        }

        return $explication;
    }



    public function get_mp_history_data( array $data, string $mp_id): array
    {
        $data['depute']['datePriseFonctionLettres'] = utf8_encode(strftime('%B %Y', strtotime($data['depute']['datePriseFonction'])));
        $data['mandat_edito'] = $this->CI->depute_edito->get_nbr_lettre($data['depute']['mandatesN']);
        $data['history_average'] = round($this->CI->deputes_model->get_average_length_as_mp(legislature_current()));
        $data['history_edito'] = $this->CI->depute_edito->history(round($data['depute']['mpLength']/365), $data['history_average']);
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
        $controller = $this->CI->router->fetch_class()."/".$this->CI->router->fetch_method();
        $data['ogp'] = $this->CI->meta_model->get_ogp($controller, $data['title_meta'], $data['description_meta'], $data['url'], $data);

        // MICRODATA
        $data['schema'] = $this->CI->deputes_model->get_person_schema($data['depute']);

        // CSS
        $data['critical_css'] = 'depute_individual';
        $data['css_to_load'] = [
            ['url' => css_url().'circle.css', 'async' => TRUE],
            ['url' => asset_url().'css/flickity.min.css', 'async' => TRUE]
        ];

        // JS
        $data['js_to_load'] = ['libraries/flickity/flickity.pkgd.min'];

        // PRELOADS
        $data['preloads'] = [
            ['href' => asset_url().'imgs/cover/hemicycle-front-375.jpg', 'as' => 'image', 'media' => '(max-width: 575.98px)'],
            ['href' => asset_url().'imgs/cover/hemicycle-front-768.jpg', 'as' => 'image', 'media' => '(min-width: 576px) and (max-width: 970px)'],
            ['href' => asset_url().'imgs/cover/hemicycle-front.jpg', 'as' => 'image', 'media' => '(min-width: 970.1px)']
        ];

        return $data;
    }
    
}





