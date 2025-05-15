<?php
  class Votes_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    private function number_add_zero($x){
      return str_pad((string) $x, 2, "0", STR_PAD_LEFT);
    }

    private function number_remove_zero($x){
      $x = (string) $x;
      return ltrim($x, '0');
    }

    public function get_all_votes($legislature, $year, $month, $limit){
      $where = array();

      if ($legislature) {
        $where['vi.legislature'] = $legislature;
      }

      if ($year) {
        $where['YEAR(vi.dateScrutin)'] = $year;
      }

      if ($month) {
        $where['MONTH(vi.dateScrutin)'] = $month;
      }

      if ($limit) {
        $this->db->limit($limit);
      }

      $this->db->select('vi.*, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR');
      $this->db->select('vd.title, vd.slug, vd.category');
      $this->db->select('REPLACE(vi.titre, "n?", "n°") AS titre');
      $this->db->select('CASE WHEN vi.voteNumero < 0 THEN CONCAT("c", abs(vi.voteNumero)) ELSE vi.voteNumero END AS voteNumero', false);
      $this->db->order_by('vi.dateScrutin', 'DESC');
      $this->db->order_by('vi.voteNumero', 'DESC');
      $this->db->join('votes_datan vd', 'vi.voteId = vd.vote_id AND vd.state = "published"', 'left');
      $query = $this->db->get_where('votes_info vi', $where);
      return $query->result_array();
    }

    public function get_last_votes_datan($limit = FALSE){
      $sql = '
      SELECT vd.title AS voteTitre, vd.description, vi.dateScrutin, vi.voteNumero, vi.legislature, f.name AS category_libelle, f.slug AS category_slug, vi.sortCode, date_format(dateScrutin, "%d %M %Y") as dateScrutinFR, r.name AS reading,
        CASE WHEN vi.voteNumero < 0 THEN CONCAT("c", abs(vi.voteNumero)) ELSE vi.voteNumero END AS voteNumero
      FROM votes_datan vd
      LEFT JOIN votes_info vi ON vd.voteNumero = vi.voteNumero AND vd.legislature = vi.legislature
      LEFT JOIN fields f ON vd.category = f.id
      LEFT JOIN readings r ON r.id = vd.reading
      WHERE vd.state = "published"
      ORDER BY vi.legislature DESC, vi.dateScrutin DESC'
      ;
      $sql .= $limit ? ' LIMIT '.$this->db->escape($limit) : '';
      return $this->db->query($sql)->result_array();
    }

    public function get_votes_datan($legislature, $year, $month, $limit, $important){
      $sql = 'SELECT vd.title AS voteTitre, vd.description, vi.dateScrutin, vi.voteNumero, vi.legislature, f.name AS category_libelle, f.slug AS category_slug, vi.sortCode, date_format(dateScrutin, "%d %M %Y") as dateScrutinFR,
        vi.nombreVotants, vi.decomptePour, vi.decompteContre, vi.decompteAbs
        FROM votes_datan vd
        LEFT JOIN votes_info vi ON vd.voteNumero = vi.voteNumero AND vd.legislature = vi.legislature
        LEFT JOIN fields f ON vd.category = f.id
        WHERE vd.state = "published"
      ';
      $sql .= $legislature ? ' AND vi.legislature = '.$this->db->escape($legislature) : '';
      $sql .= $year ? ' AND YEAR(vi.dateScrutin) = '.$this->db->escape($year) : '';
      $sql .= $month ? ' AND MONTH(vi.dateScrutin) = '.$this->db->escape($month) : '';
      if ($important == TRUE) {
        $sql .= ' ORDER BY vi.nombreVotants DESC';
      } else {
        $sql .= ' ORDER BY vi.voteNumero DESC';
      }

      $sql .= $limit ? ' LIMIT ' . $limit : '';
      return $this->db->query($sql)->result_array();
    }

    public function get_n_votes_datan($legislature, $year = NULL, $month = NULL){
      if (!is_null($year)) {
        $this->db->where('YEAR(vi.dateScrutin)', $year);
      }
      if (!is_null($month)) {
        $this->db->where('MONTH(vi.dateScrutin)', $month);
      }
      $this->db->join('votes_info vi', 'vd.voteNumero = vi.voteNumero AND vd.legislature = vi.legislature', 'left');
      $this->db->where(array('vd.state' => 'published', 'vd.legislature' => $legislature));
      return $this->db->count_all_results('votes_datan vd');
    }

    public function get_n_votes($legislature, $year = NULL, $month = NULL, $type = NULL){
      if (!is_null($year)) {
        $this->db->where('YEAR(dateScrutin)', $year);
      }
      if (!is_null($month)) {
        $this->db->where('MONTH(dateScrutin)', $month);
      }
      if (!is_null($type)) {
        $this->db->where('codeTypeVote', $type);
      }
      $this->db->where('legislature', $legislature);
      return $this->db->count_all_results('votes_info');
    }

    public function get_infos_period($legislature, $year, $month){
      if (!is_null($year)) {
        $this->db->where('YEAR(dateScrutin)', $year);
      }
      if (!is_null($month)) {
        $this->db->where('MONTH(dateScrutin)', $month);
      }
      $this->db->select('count(*) AS total');
      $this->db->select('SUM(if(sortCode = "adopté", 1, 0)) AS adopted');
      $this->db->select('SUM(if(sortCode = "rejeté", 1, 0)) AS rejected');
      $this->db->where('legislature', $legislature);
      return $this->db->get('votes_info')->row_array();
    }

    public function get_last_vote($legislature){
      $where = array('legislature' => $legislature);
      $this->db->select('voteNumero');
      $this->db->order_by('voteNumero', 'DESC');
      return $this->db->get_where('votes_info', $where, 1)->row_array();
    }

    public function get_votes_datan_category($field){
      $sql = 'SELECT vd.title AS voteTitre, vd.description, vi.dateScrutin, vi.voteNumero, vi.legislature, f.name AS category_libelle, f.slug AS category_slug, vi.sortCode, date_format(dateScrutin, "%d %M %Y") as dateScrutinFR, r.name AS reading,
        CASE WHEN vi.voteNumero < 0 THEN CONCAT("c", abs(vi.voteNumero)) ELSE vi.voteNumero END AS voteNumero
        FROM votes_datan vd
        LEFT JOIN votes_info vi ON vd.voteNumero = vi.voteNumero AND vd.legislature = vi.legislature
        LEFT JOIN fields f ON vd.category = f.id
        LEFT JOIN readings r ON r.id = vd.reading
        WHERE vd.state = "published" AND f.id = ?
        ORDER BY vd.id DESC
      ';
      return $this->db->query($sql, $field)->result_array();
    }

    public function get_years_archives($legislature){
      $sql = 'SELECT DISTINCT(YEAR(dateScrutin)) AS votes_year
        FROM votes_info
        WHERE legislature = ?
        ORDER BY YEAR(dateScrutin) ASC
      ';
      return $this->db->query($sql, $legislature)->result_array();
    }

    public function get_months_archives($legislature){

      $sql = 'SELECT DISTINCT(YEAR(dateScrutin)) AS years, (MONTH(dateScrutin)) AS months
        FROM votes_info
        WHERE legislature = ?
        ORDER BY YEAR(dateScrutin) ASC, MONTH(dateScrutin) ASC
      ';
      $query = $this->db->query($sql, $legislature);

      $months = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'decembre');

      $array = $query->result_array();
      foreach ($array as $key => $value) {
        $array[$key]["month"] = $months[$value['months']-1];
        $array[$key]["index"] = $this->number_add_zero($value["months"]);
      }

      return($array);
    }

    public function get_individual_vote($legislature, $num){
      $sql = 'SELECT A.*,
        CASE
        	WHEN A.dossier_titre != "" AND A.voteType = "final" THEN CONCAT("Vote final - ", A.dossier_titre)
            WHEN A.dossier_titre != "" AND A.voteType = "motion de renvoi en commission" THEN CONCAT("Motion de renvoi en commission - ", A.dossier_titre)
            WHEN A.dossier_titre != "" AND A.amdt != "" THEN CONCAT("Amendement n°", A.amdt, " - ", A.dossier_titre)
            WHEN A.dossier_titre != "" AND A.voteType = "article" AND A.article != "" THEN CONCAT("Article n°", A.article, " - ", A.dossier_titre)
            WHEN A.dossier_titre != "" AND A.voteType = "motion de rejet préalable" THEN CONCAT("Motion de rejet préalable - ", A.dossier_titre)
            WHEN A.dossier_titre != "" THEN A.titre
            WHEN A.voteType = "declaration de politique generale" THEN CONCAT("Déclaration de politique générale")
            WHEN A.voteType = "motion de censure" THEN CONCAT("Motion de censure")
            ELSE A.titre
        END AS title_meta
        FROM
        (
           SELECT vi.voteId, vi.voteNumero, vi.legislature, vi.dateScrutin, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR, vi.seanceRef, vi.libelleTypeVote, vi.typeMajorite, vi.sortCode, vi.demandeur, vi.nombreVotants, vi.suffragesExprimes, vi.nbrSuffragesRequis, vi.decomptePour AS pour, vi.decompteContre AS contre, vi.decompteAbs AS abstention, vi.decompteNv AS nonVotant, vi.voteType, vi.amdt, vi.article, vi.bister, vi.posArticle,
          REPLACE(vi.titre, "n?", "n°") AS titre, doss.titre AS dossier_titre, doss.titreChemin AS dossierUrl, vdos.dossierId, doss.procedureParlementaireLibelle, doss.legislature AS dossierLegislature,
          vd.title, vd.description, vd.state, f.name AS category, f.slug AS category_slug, vd.created_at, vd.modified_at
          FROM votes_info vi
          LEFT JOIN dossiers_votes vdos ON vi.voteNumero = vdos.voteNumero AND vi.legislature = vdos.legislature
          LEFT JOIN dossiers doss ON vdos.dossierId = doss.dossierId
          LEFT JOIN votes_datan vd ON vi.voteId = vd.vote_id AND vi.legislature = vd.legislature AND vd.state = "published"
          LEFT JOIN fields f ON vd.category = f.id
          WHERE vi.legislature = ? AND vi.voteNumero = ?
        ) A
      ';
      return $this->db->query($sql, array($legislature, $num))->row_array();
    }

    public function get_individual_vote_edited($x){
      //print_r($x);
      if ($x['typeMajorite'] == "majorité absolue des suffrages exprimés") {
        $x['pour_percentage'] = round($x['pour'] * 100 / $x['suffragesExprimes'], 1);
        $x['contre_percentage'] = round($x['contre'] * 100 / $x['suffragesExprimes'], 1);
      } elseif (strpos($x['typeMajorite'], "majorité des membres composant") !== false) {
        // Votes examples : 119, 1114, 1115, 1432, 1573, 1711
        $x['pour_percentage'] = round($x['pour'] * 100 / 577, 1);
        $x['contre_percentage'] = round($x['contre'] * 100 / 577, 1);
      } else {
        $x['pour_percentage'] = NULL;
        $x['contre_percentage'] = NULL;
      }

      $x['pour_pct'] = round($x['pour'] / $x['nombreVotants'] * 100, 2);
      $x['abs_pct'] = round($x['abstention'] / $x['nombreVotants'] * 100, 2);
      $x['contre_pct'] = round($x['contre'] / $x['nombreVotants'] * 100, 2);

      setlocale(LC_TIME, 'french');
      $x['date_edited'] = utf8_encode(strftime('%d %B %Y', strtotime($x['dateScrutin'])));
      //echo $x['voteType'];
      //echo $x['procedureParlementaireLibelle'];

      if ($x['voteType'] == 'final') {
        if ($x['procedureParlementaireLibelle'] == 'Projet de loi ordinaire') {
          $x['type_edited'] = 'projet de loi';
          $x['type_edited_explication'] = 'Un projet de loi est un texte destiné à devenir une loi et qui émane du Gouvernement, contrairement à la proposition de loi, qui émane des députés.';
        } elseif ($x['procedureParlementaireLibelle'] == 'Proposition de loi ordinaire') {
          $x['type_edited'] = 'proposition de loi';
          $x['type_edited_explication'] = "Une proposition de loi est un texte destiné à devenir une loi et qui émane d'un député ou d'un sénateur, contrairement au projet de loi qui émane du Gouvernement.";
        } elseif ($x['procedureParlementaireLibelle'] == 'Projet ou proposition de loi constitutionnelle') {
          $x['type_edited'] = 'projet ou proposition de loi constitutionnelle';
          $x['type_edited_explication'] = "Un projet ou proposition de loi constitutionnelle est une loi visant à modifier la Constitution. Soit cette modification est à l'origine du Gouvernement (projet de loi) soit du Parlement (proposition de loi).<br><br><a href='https://www2.assemblee-nationale.fr/decouvrir-l-assemblee/role-et-pouvoirs-de-l-assemblee-nationale/les-fonctions-de-l-assemblee-nationale/les-fonctions-legislatives/la-revision-de-la-constitution' target='_blank'>Plus d'information sur la procédure de révision de la Constitution</a>";
        } elseif ($x['procedureParlementaireLibelle'] == 'Projet ou proposition de loi organique') {
          $x['type_edited'] = 'Projet ou proposition de loi organique';
          $x['type_edited_explication'] = "Un projet ou proposition de loi organique est une loi complétant la Constitution afin de préciser l'organisation des pouvoirs publics. Elle est hiérarchiquement en dessous de la Constitution, mais au dessus des lois ordinaires.";
        } elseif ($x['procedureParlementaireLibelle'] == 'Projet de loi de finances rectificative') {
          $x['type_edited'] = 'Projet de loi de finances rectificative';
          $x['type_edited_explication'] = "Un projet de loi de finances rectificative a pour but de corriger le budget initial afin de tenir compte de l'évolution de la conjecture économique et financière du pays.";
        } elseif ($x['procedureParlementaireLibelle'] == "Projet de ratification des traités et conventions") {
          $x['type_edited'] = "Ratification de traités et conventions";
          $x['type_edited_explication'] = "Alors que les traités et conventions internationales sont négociés et ratifiés en général par le Président de la République, certains accords internationaux nécessitent l'accord du Parlement.";
        } elseif($x['procedureParlementaireLibelle'] == "Projet de loi de financement de la sécurité sociale") {
          $x['type_edited'] = "Projet de loi de financement de la sécurité sociale";
          $x['type_edited_explication'] = "Le Projet de loi de financement de la sécurité sociale (PLFSS) vise à gérer les dépenses sociales et de santé. Le projet, présenté à l'automne par le gouvernement, fixe les objectifs de dépenses en fonction des recettes, et détermine donc les conditions à l'équilibre financier de la Sécurité sociale.";
        } elseif ($x['procedureParlementaireLibelle'] == "Projet de loi de finances de l'année") {
          $x['type_edited'] = 'Projet de loi de finances';
          $x['type_edited_explication'] = "Le projet de loi de finances (PLF), présenté à l'automne par le Gouvernement, est le projet de budget pour la France. C'est un document unique rassemblant l'ensemble des recettes et des dépenses de l’État pour l'année à venir. Le projet propose le montant, la nature et l'affectation des ressources et des charges de l’État. L'Assemblée nationale vote ici sur une partie de ce projet de loi de finances.<br><br><a href='https://www.gouvernement.fr/projet-de-loi-de-finances-plf-qu-est-ce-que-c-est' target='_blank'>Plus d'information</a>";
        } elseif ($x['procedureParlementaireLibelle'] == 'Résolution') {
          $x['type_edited'] = 'proposition de résolution';
          $x['type_edited_explication'] = "Une proposition de résolution est un texte non législatif (qui est donc purement symbolique) et qui sert à exprimer la position de l'Assemblée nationale sur un sujet donné.";
        } else {
          $x['type_edited'] = NULL;
          $x['type_edited_explication'] = NULL;
        }
      } elseif ($x['voteType'] == 'amendement' || $x['voteType'] == 'les amen') {
        $x['type_edited'] = 'amendement';
        $x['type_edited_explication'] = "Un amendement est une modification apportée à un texte juridique, d'un projet de loi par exemple.";
      } elseif ($x['voteType'] == 'article') {
        $x['type_edited'] = 'article';
        $x['type_edited_explication'] = "Un article est une partie d'un texte juridique, d'un projet de loi par exemple.";
      } elseif ($x['voteType'] == 'declaration de politique generale' | $x['voteType'] == 'la décl') {
        $x['type_edited'] = 'déclaration de politique générale';
        $x['type_edited_explication'] = "La déclaration de politique générale est une déclaration du Premier ministre devant l'Assemblée nationale lors de l'entrée en fonction d'un nouveau gouvernement.";
      } elseif ($x['voteType'] == 'motion de renvoi en commission') {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = "Si une motion de renvoi en commission est adoptée, les débats sur le texte sont suspendus jusqu'à la présentation par la commission principale d'un nouveau rapport.";
      } elseif ($x['voteType'] == 'conclusions de rejet de la commission') {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = "Quand un député propose une loi, une commission parlementaire est en charge de la discussion et de rédiger un rapport. Elle peut conclure au rejet de la proposition. Si cette proposition de rejet est adoptée, alors la proposition de loi est directement rejetée, avant discussion.";
      } elseif ($x['voteType'] == 'crédits de mission') {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = "Les crédits de mission sont adoptés dans les lois de finance. Ces dispositions fixent les crédits pour les différentes missions de l'État.";
      } elseif ($x['voteType'] == 'déclaration du gouvernement') {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = "La déclaration du gouvernement peut, de sa propre initiative ou à la demande d'un groupe parlementaire, faire une déclaration qui donne lieu à un débat sur un sujet déterminé. Si le gouvernement le souhaite, ce débat peut faire l'objet d'un vote.<br><br>Les modalités de ces déclarations sont prévues à <a href='http://www.assemblee-nationale.fr/connaissance/constitution.asp' target='_blank'>l'article 51-1 de la Constitution</a>.";
      } elseif ($x['voteType'] == 'demande de constitution de commission speciale') {
        $x['type_edited'] = 'demande de constitution de commission spéciale';
        $x['type_edited_explication'] = "Une commission spéciale est une commission chargée spécifiquement de l'examination d'un projet de loi. Une commission spéciale peut être proposée par le Gouvernement ou par l'Assemblée nationale. Lorsque le Gouvernement, le président d'une commission permanente ou le président d'un groupe s'y oppose, cette demane fait l'objet d'un vote.<br><br><a href='http://www2.assemblee-nationale.fr/14/autres-commissions/commissions-speciales/liens/constitution-d-une-commission-speciale' target='_blank'>Plus d'informations<a/>";
      } elseif ($x['voteType'] == 'motion de censure') {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = "Une motion de censure est le moyen dont dispose le Parlement pour montrer sa désapprobation envers la politique du Gouvernement. Pour être recevable, la motion doit être signée par au moins un dixième des membres de l'Assemblée nationale. Le vote doit avoir lieu au maximum 48 heures après le dépôt de la motion. Pour être adoptée, la motion doit obtenir la majorité absolue, autrement dit la moitié des membres composants l'Assemblée.<br><br><a href='http://www2.assemblee-nationale.fr/dans-l-hemicycle/engagements-de-responsabilite-du-gouvernement-et-motions-de-censures#node_23387' target='_blank'>Plus d'information</a>";
      } elseif ($x['voteType'] == 'motion de rejet préalable') {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = "La motion de rejet préalable est discutée et votée avant la discussion générale d'un texte de loi. Elle vise à indiquer que le texte (projet ou proposition de loi) est contraire à une ou plusieurs dispositions constitutionnelles. Si la motion est adoptée, elle entraîne le rejet du texte.<br><br><a href='http://www.assemblee-nationale.fr/encyclopedie/loi.asp' target='_blank'>Plus d'information</a>";
      } elseif ($x['voteType'] == 'partie du projet de loi de finances') {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = "Le projet de loi de finances (PLF), présenté à l'automne par le Gouvernement, est le projet de budget pour la France. C'est un document unique rassemblant l'ensemble des recettes et des dépenses de l’État pour l'année à venir. Le projet propose le montant, la nature et l'affectation des ressources et des charges de l’État. L'Assemblée nationale vote ici sur une partie de ce projet de loi de finances.<br><br><a href='https://www.gouvernement.fr/projet-de-loi-de-finances-plf-qu-est-ce-que-c-est' target='_blank'>Plus d'information</a>";
      } elseif ($x['voteType'] == 'sous-amendement') {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = "Un sous-amendement est, comme un amendement, une modification apportée à un texte juridique, d'un projet de loi par exemple. Le sous-amendement porte sur un amendement, et ne peut pas contredire le sens initial de l'amendement.";
      } elseif($x['voteId'] == 'VTANR5L15V1941'){
        $x['type_edited'] = 'déclaration de politique générale';
        $x['type_edited_explication'] = "La déclaration de politique générale est une déclaration du Premier ministre devant l'Assemblée nationale lors de l'entrée en fonction d'un nouveau gouvernement.";
      } elseif ($x['voteType'] == 'la propo') {
        $x['type_edited'] = 'proposition de résolution';
        $x['type_edited_explication'] = "Une proposition de résolution est un texte non législatif (qui est donc purement symbolique) et qui sert à exprimer la position de l'Assemblée nationale sur un sujet donné. Ses modalités sont prévues l'article 34-1 de la <a href='http://www.assemblee-nationale.fr/connaissance/constitution.asp' target='_blank'>Constitution</a>.";
      } elseif ($x['voteType'] == "motion d'ajournement") {
        $x['type_edited'] = "motion d'ajournement";
        $x['type_edited_explication'] = "Une motion d'ajournement est utilisé que dans le cadre des accords internationaux. Si la motion est adoptée, le texte retourne en commission parlementaire, où il doit être rediscuté.<br><br><a href='https://www.lemonde.fr/blog/cuisines-assemblee/2019/02/18/la-motion-dajournement/' target='_blank'>Plus d'information</a>";
      } elseif ($x['voteType'] == 'projet de loi constitutionnelle') {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = "Un projet de loi constitutionnelle est une proposition visant à modifier la Constitution, c'est à dire la loi fondamentale du pays. Un projet de loi constitutionnelle doit être adoptée à l'identique par les deux chambres (Assemblée nationale et Sénat), réunies pour l'occasion en Congrès. ";
      } else {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = NULL;
      }

      //sortCodeLibelle
      if ($x['sortCode'] == "adopté") {
        $x['sortCodeLibelle'] = "pour";
      } elseif ($x['sortCode'] == "rejeté") {
        $x['sortCodeLibelle'] = "contre";
      }

      return $x;
    }

    public function get_vote_groupes($num, $legislature, $type){
      $sql = 'SELECT A.*,
        CASE
          WHEN A.pct < 0 THEN 0
          WHEN A.pct IS NULL THEN 0
          ELSE A.pct
        END AS percentageVotants
        FROM (
          SELECT o.uid, o.libelle, o.libelleAbrev, o.legislature, vi.dateScrutin, vg.nombreMembresGroupe, vg.positionMajoritaire, vg.nombrePours, vg.nombreContres, vg.nombreAbstentions, vg.nonVotants, vg.nonVotantsVolontaires, gc.cohesion, gc.scoreGagnant, ROUND((vg.nombrePours + vg.nombreContres + vg.nombreAbstentions - vg.nonVotants) / vg.nombreMembresGroupe * 100) AS pct
          FROM votes_info vi
          LEFT JOIN organes o ON o.legislature = vi.legislature AND o.coteType = "GP" AND o.dateDebut <= vi.dateScrutin AND (o.dateFin >= vi.dateScrutin OR o.dateFin IS NULL)
          LEFT JOIN votes_groupes vg ON vg.legislature = vi.legislature AND vg.voteNumero = vi.voteNumero AND vg.organeRef = o.uid
          LEFT JOIN groupes_cohesion gc ON gc.legislature = vi.legislature AND gc.voteNumero = vi.voteNumero AND gc.organeRef = o.uid
          WHERE vi.legislature = ? AND vi.voteNumero = ?
        ) A
      ';
      $results = $this->db->query($sql, array($legislature, $num))->result_array();

      // Fix bug for Motion de censure --> positionMajoritaire only when
      if ($type == 'motion de censure') {
        foreach($results as $key => $value) {
          $percentage = $value['nombrePours'] / $value['nombreMembresGroupe'];
          if ($percentage >= 0.5) {
            $results[$key]['positionMajoritaire'] = 'pour';
          } else {
            $results[$key]['positionMajoritaire'] = 'contre';
          }
        }
      }
      
      return $results;
    }

    public function get_vote_groupes_simplified($num, $legislature){
      $sql = 'SELECT o.libelleAbrev, o.libelle, o.uid, vg.*
        FROM votes_groupes vg
        LEFT JOIN organes o ON vg.organeRef = o.uid
        WHERE vg.voteNumero = ? AND vg.legislature = ?
        ORDER BY vg.nombreMembresGroupe DESC
      ';
      return $this->db->query($sql, array($num, $legislature))->result_array();
    }

    public function get_vote_deputes($num, $legislature){
      $sql = 'SELECT B.*
        FROM
        (
          SELECT A.*,
          CASE
          	WHEN A.vote = "1" THEN "pour"
          	WHEN A.vote = "0" THEN "abstention"
          	WHEN A.vote = "-1" THEN "contre"
            WHEN A.vote = "nv" THEN "non-votant"
          ELSE NULL
          END AS vote_libelle,
          CASE
          	WHEN A.scoreLoyaute = 1 THEN "loyal"
          	WHEN A.scoreLoyaute = 0 THEN "rebelle"
          ELSE NULL
          END AS loyaute_libelle,
          da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug,
          o.libelle, o.libelleAbrev
          FROM
          (
          	SELECT v.mpId, v.vote, v.mandatId, v.scoreLoyaute, v.legislature
          	FROM votes_scores v
          	WHERE v.voteNumero = ? AND legislature = ? AND v.mandatId IS NOT NULL
          ) A
          LEFT JOIN deputes_all da ON da.mpId = A.mpId AND da.legislature = A.legislature
          LEFT JOIN mandat_groupe mg ON mg.mandatId = A.mandatId
          LEFT JOIN organes o ON mg.organeRef = o.uid
        ) B
        ORDER BY B.nameLast ASC, B.nameFirst ASC
      ';
      return $this->db->query($sql, array($num, $legislature))->result_array();
    }

    public function get_votes_datan_depute($depute_id, $limit = FALSE){
      $sql = 'SELECT vd.voteNumero, vd.legislature, vd.title AS vote_titre, vd.category, f.name AS category_libelle, f.slug AS category_slug, vi.sortCode, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR, r.name AS reading,
        CASE WHEN vd.voteNumero < 0 THEN CONCAT("c", abs(vd.voteNumero)) ELSE vd.voteNumero END AS voteNumero,
        CASE
        	WHEN vs.vote = 0 THEN "abstention"
        	WHEN vs.vote = 1 THEN "pour"
        	WHEN vs.vote = -1 THEN "contre"
        	WHEN vs.vote IS NULL THEN "absent"
        	ELSE vs.vote
        END AS vote_depute,
        e.text AS explication
        FROM votes_datan vd
        LEFT JOIN fields f ON vd.category = f.id
        LEFT JOIN votes_scores vs ON vd.voteNumero = vs.voteNumero AND vd.legislature = vs.legislature AND vs.mpId = ?
        LEFT JOIN votes_info vi ON vd.voteNumero = vi.voteNumero AND vd.legislature = vi.legislature
        LEFT JOIN readings r ON r.id = vd.reading
        LEFT JOIN explications_mp e ON e.mpId = vs.mpId AND e.legislature = vd.legislature AND e.voteNumero = vd.voteNumero AND e.state = 1
        WHERE vd.state = "published" AND vs.vote IS NOT NULL AND vs.vote != "nv"
        ORDER BY vi.dateScrutin DESC
      ';
      if ($limit){
        $sql .= ' LIMIT ' . $this->db->escape($limit);
      }
      return $this->db->query($sql, array($depute_id))->result_array();
    }

    public function get_individual_vote_depute($depute_id, $legislature, $num){
      $where = array(
        'vs.mpId' => $depute_id,
        'vs.legislature' => $legislature,
        'vs.voteNumero' => $num
      );
      $this->db->select('vs.*, o.libelleAbrev');
      $this->db->join('mandat_groupe mg', 'vs.mandatId = mg.mandatId');
      $this->db->join('organes o', 'mg.organeRef = o.uid');
      return $this->db->get_where('votes_scores vs', $where, 1)->row_array();
    }

    public function get_individual_vote_depute_participation($depute_id, $legislature, $num){
      $where = array(
        'vp.mpId' => $depute_id,
        'vp.legislature' => $legislature,
        'vp.voteNumero' => $num
      );
      $this->db->select('vp.mpId, vp.legislature, vp.voteNumero, vp.participation');
      $this->db->select('CASE
        WHEN vs.vote = 1 THEN "pour"
        WHEN vs.vote = -1 THEN "contre"
        WHEN vs.vote = 0 THEN "abstention"
        WHEN vs.vote IS NULL THEN "absent"
        ELSE NULL END AS vote', FALSE);
      $this->db->join('votes_scores vs', 'vs.legislature = vp.legislature AND vs.voteNumero = vp.voteNumero AND vs.mpId = vp.mpId', 'left');
      return $this->db->get_where('votes_participation vp', $where, 1)->row_array();
    }

    public function get_individual_vote_moc($mpId, $legislature, $num){
      $where = array(
        'vi.legislature' => $legislature,
        'vi.voteNumero' => $num
      );
      $this->db->select('d.mpId, vi.legislature, vi.voteNumero');
      $this->db->select('CASE
        WHEN v.vote = 1 THEN "pour"
        WHEN v.vote = -1 THEN "contre"
        WHEN v.vote = 0 THEN "abstention"
        WHEN v.vote IS NULL THEN "absent" 
        ELSE NULL END AS vote', FALSE);
      $this->db->join('deputes_last d', 'd.legislature = vi.legislature AND d.datePriseFonction <= vi.dateScrutin AND (d.dateFin IS NULL OR d.dateFin >= vi.dateScrutin) AND d.mpId = ' . $this->db->escape($mpId));
      $this->db->join('votes v', 'v.legislature = vi.legislature AND v.voteNumero = vi.voteNumero AND v.mpId = ' . $this->db->escape($mpId), 'left');
      return $this->db->get_where('votes_info vi', $where, 1)->row_array();
    }

    public function get_votes_all_depute($depute_id, $legislature = FALSE){
      $sql = 'SELECT A.voteId, A.dateScrutin, A.titre, A.title, A.legislature,
        CASE WHEN A.voteNumero < 0 THEN CONCAT("c", abs(A.voteNumero)) ELSE A.voteNumero END AS voteNumero,
        CASE
        	WHEN A.vote = 1 THEN "pour"
            WHEN A.vote = -1 THEN "contre"
            WHEN A.vote = 0 THEN "abstention"
            WHEN A.vote IS NULL THEN "absent"
            ELSE NULL
        END AS vote,
        CASE
        	WHEN A.scoreLoyaute = 1 THEN "loyal"
        	WHEN A.scoreLoyaute = 0 THEN "rebelle"
        ELSE NULL END AS loyaute
        FROM
        (
        SELECT vs.voteNumero, vs.vote, vs.scoreLoyaute, vi.voteId, vi.dateScrutin, vi.legislature, REPLACE(vi.titre, "n?", "n°") AS titre, vd.title AS title
        FROM votes_scores vs
        LEFT JOIN votes_info vi ON vs.voteNumero = vi.voteNumero AND vs.legislature = vi.legislature
        LEFT JOIN votes_datan vd ON vi.voteId = vd.vote_id AND vd.state = "published"
        WHERE vs.mpId = ? AND vs.vote != "nv" ';
      $sql .= $legislature ? ' AND vp.legislature = ' . $this->db->escape($legislature) : '';
      $sql .= ' ) A ORDER BY A.dateScrutin DESC, A.voteNumero DESC ';
      return $this->db->query($sql, array($depute_id))->result_array();
    }

    public function get_votes_all_groupe($uid, $legislature){
      $sql = 'SELECT A.*, vd.title,
        CASE
        	WHEN A.positionGroupe = 1 THEN "pour"
        	WHEN A.positionGroupe = -1 THEN "contre"
        	WHEN A.positionGroupe = 0 THEN "abstention"
        ELSE NULL END AS vote,
        CASE WHEN A.voteNumero < 0 THEN CONCAT("c", abs(A.voteNumero)) ELSE A.voteNumero END AS voteNumero
        FROM
        (
        SELECT vi.voteId, vi.legislature, gc.voteNumero, gc.cohesion, gc.positionGroupe, vi.dateScrutin, REPLACE(vi.titre, "n?", "n°") AS titre
        FROM groupes_cohesion gc
        LEFT JOIN votes_info vi ON gc.voteNumero = vi.voteNumero AND gc.legislature = vi.legislature
        WHERE gc.organeRef = ? AND gc.legislature = ?
        ) A
        LEFT JOIN  votes_datan vd ON A.voteId = vd.vote_id AND A.legislature = vd.legislature AND vd.state = "published"
        ORDER BY A.dateScrutin DESC, A.voteNumero
      ';
      return $this->db->query($sql, array($uid, $legislature))->result_array();
    }

    public function get_votes_datan_groupe($groupe_id, $limit = FALSE){
      $sql = 'SELECT A.*, f.name AS category_libelle, f.slug AS category_slug, v.positionMajoritaire AS vote, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR, vi.legislature, r.name AS reading,
        CASE WHEN A.voteNumero < 0 THEN CONCAT("c", abs(A.voteNumero)) ELSE A.voteNumero END AS voteNumero
        FROM
        (
        SELECT vd.id, vd.voteNumero, vd.legislature, vd.title AS vote_titre, vd.slug, vd.category, vd.reading
        FROM votes_datan vd
        WHERE vd.state = "published"
        ORDER BY vd.id DESC
        ) A
        LEFT JOIN fields f ON A.category = f.id
        JOIN votes_groupes v ON A.voteNumero = v.voteNumero AND A.legislature = v.legislature AND v.organeRef = ?
        LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
        LEFT JOIN readings r ON r.id = A.reading
        ORDER BY vi.dateScrutin DESC
      ';
      if ($limit){
        $sql .= ' LIMIT ' . $this->db->escape($limit);
      }
      return $this->db->query($sql, $groupe_id)->result_array();
    }

    public function get_key_votes_mp($mpId){
      $sql =  'SELECT vs.voteNumero, vs.scoreLoyaute, vs.vote,
        CASE
          WHEN vs.vote = 1 THEN "pour"
          WHEN vs.vote = 0 THEN "abstention"
          WHEN vs.vote = -1 THEN "contre"
        ELSE NULL
        END AS vote_libelle
        FROM votes_scores vs
        WHERE vs.voteNumero IN (184, 269, 629, 3213) AND vs.legislature = 16 AND vs.mpId = ?
      ';
      $query = $this->db->query($sql, $mpId);

      $votes = $query->result_array();

      $text = array(
        629 => "l'inscription de l'interruption volontaire de grossesse (IVG) dans la Constitution",
        269 => "la création d'une taxe temporaire sur les super-dividendes distribués par les grandes entreprises",
        184 => "la ratification de l'accord pour l'adhésion de la Suède et de la Finlande à l'OTAN",
        3213 => "du projet de loi immigration en 2023"
      );

      foreach ($votes as $key => $value) {
        $voteNumero = $value["voteNumero"];
        $votes[$key]["text"] = $text[$voteNumero];
      }

      if (!isset($votes)) {
        $votes = NULL;
      }


      return $votes;
    }

    public function get_most_famous_votes($limit = false){
      $sql = 'SELECT vd.title AS voteTitre, vd.description, vi.dateScrutin, vi.voteNumero, vi.legislature, f.name AS category_libelle, f.slug AS category_slug, vi.sortCode, date_format(dateScrutin, "%d %M %Y") as dateScrutinFR, vote_id, vi.voteNumero
        FROM votes_datan vd
        LEFT JOIN votes_info vi ON vd.vote_id = vi.voteId
        LEFT JOIN fields f ON vd.category = f.id
        WHERE vd.state = "published"
        ORDER BY vi.voteNumero DESC
      ';
      if ($limit) {
        $sql .= ' LIMIT ' .$limit;
      }
      return $this->db->query($sql, $limit)->result_array();
    }

    public function get_votes_result_by_depute($voteNumeros){
      $sql = 'SELECT votes.voteNumero, organes.libelle, deputes_all.nameFirst, deputes_all.nameLast, votes.vote, organes.uid, deputes_all.mpId
        FROM `votes`
        LEFT JOIN mandat_principal ON votes.mandatId=mandat_principal.mandatId
        LEFT JOIN deputes_all ON votes.mandatId=deputes_all.mandatId
        LEFT JOIN organes ON deputes_all.groupeId=organes.uid
        WHERE votes.voteNumero IN ?
      ';
      return $this->db->query($sql, $voteNumeros)->result_array();
    }

    public function get_vote_schema($vote, $img){
      $schema = [
        "@context" => "http://schema.org",
        "@type" => "NewsArticle",
        "headline" => "Vote à l'Assemblée : " . $vote['title'],
        "image" => $img,
      ];

      if ($vote['created_at']) {
        $schema['datePublished'] = $vote['created_at'];
      }

      if ($vote['modified_at']) {
        $schema['dateModified'] = $vote['modified_at'];
      }

      return $schema;
    }

    public function get_amendement($legislature, $voteNumero){
      $where = array(
        'va.legislature' => $legislature,
        'va.voteNumero' => $voteNumero
      );
      $this->db->select('va.amendmentId, va.amendmentHref, a.expose');
      $this->db->join('amendements a', 'a.id = va.amendmentId', 'left');
      $this->db->limit(1);
      return $this->db->get_where('votes_amendments va', $where)->row_array();
    }

    public function get_amendement_all_seanceRef($legislature, $dossier, $num){
      $where = array(
        'legislature' => $legislature,
        'dossier' => $dossier,
        'numOrdre' => $num
      );
      $this->db->select('id, legislature, num, numOrdre, texteLegislatifRef, expose');
      return $this->db->get_where('amendements', $where)->result_array();
    }

    public function get_another_dossierId($chemin){
      $this->db->where('titreChemin', $chemin);
      return $this->db->get('dossiers')->result_array();
    }

    public function get_amendement_author($id){
      return $this->db->get_where('amendements_auteurs', array('id' => $id))->row_array();
    }

    public function get_document_legislatif($id){
      return $this->db->get_where('documents_legislatifs', array('id' => $id), 1)->row_array();
    }

    public function get_dossier_mp_authors($id, $legislature){
      $where = array(
        'da.id' => $id,
        'da.value' => 'initiateur',
        'da.type' => 'acteur',
        'dl.legislature' => $legislature
      );
      $this->db->join('deputes_all dl', 'da.ref = dl.mpId');
      $this->db->select('*, CONCAT(departementNom, " (", departementCode, ")") AS cardCenter');
      $this->db->select('dl.legislature AS legislature_last');
      $this->db->order_by('dl.nameLast', 'ASC');
      $this->db->group_by('dl.mpId');
      return $this->db->get_where('dossiers_acteurs da', $where)->result_array();
    }

    public function get_dossier_mp_rapporteurs($id, $legislature){
      $where = array(
        'da.id' => $id,
        'da.value' => 'rapporteur',
        'dl.legislature' => $legislature
      );
      $this->db->join('deputes_all dl', 'da.ref = dl.mpId');
      $this->db->select('*, CONCAT(departementNom, " (", departementCode, ")") AS cardCenter');
      $this->db->select('dl.legislature AS legislature_last');
      $this->db->order_by('dl.nameLast', 'ASC');
      $this->db->group_by('dl.mpId');
      return $this->db->get_where('dossiers_acteurs da', $where)->result_array();
    }

    public function request_vote_datan(){
      $email = $this->input->post('email');
      $email = empty($email) ? NULL : $email;
      $legislature = $this->input->post('legislature');
      $voteNumero = $this->input->post('voteNumero');
      $newsletter = $this->input->post('newsletter');
      $captcha = $this->input->post('captcha');

      // Check captcha
      if ($captcha !== $this->session->userdata('captchaCode')) {
        return FALSE;
      } else {
        // Newsletter
        if ($newsletter && $email != NULL) {
          $this->newsletter_model->create_newsletter();
        }

        // Add data in table 'votes_datan_requested'
        $data = array(
          'legislature' => $legislature,
          'vote' => $voteNumero,
          'email' => $email
        );
        return $this->db->insert('votes_datan_requested', $data);
      }
    }

    public function get_requested_votes($limit = NULL){
      $sql = 'SELECT vr.legislature, vr.vote, COUNT(*) as n
        FROM votes_datan_requested vr
        LEFT JOIN votes_datan vd ON vr.vote = vd.voteNumero AND vr.legislature = vd.legislature
        WHERE vd.voteNumero IS NULL
        GROUP BY vr.legislature, vr.vote
        ORDER BY COUNT(*) DESC
      ';
      if ($limit){
        $sql .= ' LIMIT ' . $this->db->escape_like_str($limit);
      }

      return $this->db->query($sql)->result_array();
    }

    public function get_explication($mpId, $legislature, $voteNumero, $state = NULL){
      $where = array(
        'e.mpId' => $mpId,
        'e.legislature' => $legislature,
        'e.voteNumero' => $voteNumero
      );
      if ($state) {
        $where['state'] = $state;
      }
      $this->db->select('d.civ, d.nameFirst, d.nameLast, d.nameUrl, d.dptSlug, d.libelleAbrev, d.libelle, d.couleurAssociee, substr(d.mpId, 3) AS idImage, d.img, e.text, e.state');
      $this->db->select('CASE WHEN v.vote = 0 THEN "abstention" WHEN v.vote = 1 THEN "pour" WHEN v.vote = -1 THEN "contre" WHEN v.vote IS NULL THEN "absent" ELSE v.vote END AS vote', FALSE);
      $this->db->join('deputes_last d', 'e.mpId = d.mpId', 'left');
      $this->db->join('votes_scores v', 'v.legislature = e.legislature AND v.voteNumero = e.voteNumero AND v.mpId = e.mpId', 'left');
      $this->db->order_by('e.modified_at', 'DESC');

      return $this->db->get_where('explications_mp e', $where)->row_array();
    }

    public function get_explications($legislature, $voteNumero){
      $where = array(
        'e.legislature' => $legislature,
        'e.voteNumero' => $voteNumero
      );
      $this->db->select('d.civ, d.nameFirst, d.nameLast, d.nameUrl, d.legislature AS legislature_last, d.dptSlug, d.libelleAbrev, d.couleurAssociee, substr(d.mpId, 3) AS idImage, d.img, e.text, v.vote');
      $this->db->join('deputes_last d', 'e.mpId = d.mpId', 'left');
      $this->db->join('votes_scores v', 'v.legislature = e.legislature AND v.voteNumero = e.voteNumero AND v.mpId = e.mpId', 'left');
      $this->db->order_by('e.modified_at', 'DESC');
      $this->db->where('e.state', 1);
      $results = $this->db->get_where('explications_mp e', $where)->result_array();
      if ($results) {
        foreach ($results as $key => $value) {
          $return[$value['vote']][] = $value;
        }
        return $return;
      }
    }

    public function get_explications_last(){
      $this->db->select('e.*, d.nameFirst, d.nameLast, d.legislature AS legislature_last, d.libelleAbrev, d.couleurAssociee, d.dptSlug, d.nameUrl, v.vote, vd.title');
      $this->db->select('substr(e.mpId, 3) AS idImage,');
      $this->db->select('CASE WHEN e.voteNumero < 0 THEN CONCAT("c", abs(e.voteNumero)) ELSE e.voteNumero END AS voteNumero', FALSE);
      $this->db->select('CASE WHEN v.vote = 1 THEN "pour" WHEN v.vote = -1 THEN "contre" WHEN v.vote = 0 THEN "abstention" ELSE NULL END AS vote', FALSE);
      $this->db->where('e.state', 1);
      $this->db->order_by('e.created_at', 'DESC');
      $this->db->join('deputes_last d', 'e.mpId = d.mpId', 'left');
      $this->db->join('votes_datan vd', 'vd.legislature = e.legislature AND vd.voteNumero = e.voteNumero');
      $this->db->join('votes_scores v', 'v.legislature = e.legislature AND v.voteNumero = e.voteNumero AND v.mpId = e.mpId', 'left');
      return $this->db->get('explications_mp e', 3)->result_array();
    }

  }
