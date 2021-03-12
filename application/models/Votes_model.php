<?php
  class Votes_model extends CI_Model{
    public function __construct(){
      $this->load->database();
    }

    public function get_all_votes($legislature, $year, $month, $limit){
      $queryString = '
      SELECT vi.*, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR,
      REPLACE(vi.titre, "n?", "n°") AS titre,
      vd.title, vd.slug, vd.category
      FROM votes_info vi
      LEFT JOIN votes_datan vd ON vi.voteId = vd.vote_id AND vd.state = "published"
      WHERE vi.legislature = '.$legislature.'
      ';
      $queryString .= $year ? ' AND YEAR(vi.dateScrutin) = "'.$year.'"' : '';
      $queryString .= $month ? ' AND MONTH(vi.dateScrutin) = "'.$month.'"' : '';
      $queryString .= ' ORDER BY vi.voteNumero DESC';
      if ($limit != FALSE) {
        $queryString .= ' LIMIT ' . $limit;
      }
      $query = $this->db->query($queryString);

      return $query->result_array();
    }

    public function get_last_votes_datan($limit = FALSE){
      $queryString = '
      SELECT vd.title AS voteTitre, vd.description, vi.dateScrutin, vi.voteNumero, vi.legislature, f.name AS category_libelle, f.slug AS category_slug, vi.sortCode, date_format(dateScrutin, "%d %M %Y") as dateScrutinFR
      FROM votes_datan vd
      LEFT JOIN votes_info vi ON vd.voteNumero = vi.voteNumero AND vd.legislature = vi.legislature
      LEFT JOIN fields f ON vd.category = f.id
      WHERE vd.state = "published"
      ORDER BY vi.voteNumero DESC'
      ;
      $queryString .= $limit ? ' LIMIT '.$limit : '';
      $query = $this->db->query($queryString);

      return $query->result_array();
    }

    public function get_n_votes_datan(){
      $query = $this->db->query('
        SELECT COUNT(vd.title) AS n
        FROM votes_datan vd
        LEFT JOIN votes_info vi ON vd.voteNumero = vi.voteNumero AND vd.legislature = vi.legislature
        JOIN fields f ON vd.category = f.id
        WHERE vd.state = "published"
      ');

      return $query->row_array();
    }

    public function get_n_votes(){
      $query = $this->db->count_all_results('votes_info');

      return $query;
    }

    public function get_last_vote(){
      $query = $this->db->query('
        SELECT voteNumero
        FROM votes_info
        WHERE legislature = 15
        ORDER BY voteNumero DESC
        LIMIT 1
      ');

      return $query->row_array();
    }

    public function get_votes_datan_category($field){
      $query = $this->db->query('
      SELECT vd.title AS vote_titre, vd.description, vi.dateScrutin, vi.voteNumero, vi.legislature, f.name AS category_libelle, f.slug AS category_slug, vi.sortCode, date_format(dateScrutin, "%d %M %Y") as dateScrutinFR
      FROM votes_datan vd
      LEFT JOIN votes_info vi ON vd.voteNumero = vi.voteNumero AND vd.legislature = vi.legislature
      LEFT JOIN fields f ON vd.category = f.id
      WHERE vd.state = "published" AND f.id = "'.$field.'"
      ORDER BY vd.id DESC
      ');

      return $query->result_array();
    }

    public function get_years_archives($legislature){
      $query = $this->db->query('
        SELECT DISTINCT(YEAR(dateScrutin)) AS votes_year
        FROM votes_info
        WHERE legislature = "'.$legislature.'"
        ORDER BY YEAR(dateScrutin) ASC
      ');

      return $query->result_array();
    }

    public function get_months_archives($legislature){

      $query = $this->db->query('
        SELECT DISTINCT(YEAR(dateScrutin)) AS years, (MONTH(dateScrutin)) AS months
        FROM votes_info
        WHERE legislature = "'.$legislature.'"
      ');

      $months = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'decembre');

      $array = $query->result_array();
      foreach ($array as $key => $value) {
        $array[$key]["month"] = $months[$value['months']-1];
        $array[$key]["index"] = number_zero($value["months"]);
      }

      return($array);
    }

    public function get_individual_vote($legislature, $num){
      $query = $this->db->query('
      SELECT A.*,
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
         SELECT vi.voteId, vi.voteNumero, vi.legislature, vi.dateScrutin, vi.libelleTypeVote, vi.typeMajorite, vi.sortCode, vi.demandeur, vi.nombreVotants, vi.suffragesExprimes, vi.nbrSuffragesRequis, vi.decomptePour AS pour, vi.decompteContre AS contre, vi.decompteAbs AS abstention, vi.decompteNv AS nonVotant, vi.voteType, vi.amdt, vi.article, vi.bister, vi.posArticle,
        REPLACE(vi.titre, "n?", "n°") AS titre, vdos.href, vdos.dossier, doss.dossierId, doss.titre AS dossier_titre, doss.senatChemin, doss.procedureParlementaireLibelle, vd.title, vd.description, vd.state, f.name AS category, f.slug AS category_slug
        FROM votes_info vi
        LEFT JOIN votes_dossiers vdos ON vi.voteNumero = vdos.voteNumero AND vi.legislature = vdos.legislature
        LEFT JOIN dossiers doss ON vdos.dossier = doss.titreChemin
        LEFT JOIN votes_datan vd ON vi.voteId = vd.vote_id AND vi.legislature = vd.legislature AND vd.state = "published"
        LEFT JOIN fields f ON vd.category = f.id
        WHERE vi.legislature = '.$legislature.' AND vi.voteNumero = "'.$num.'"
      ) A
      ');
      return $query->row_array();
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
          $x['type_edited_explication'] = "Le projet de loi de finances (PLF), présenté à l'automne par le Gouvernement, est le projet de budget pour la France. C'est un document unique rassemblant l'ensemble des recettes et des dépenses de l’État pour l'année à venir. Le projet propose le montant, la nature et l'affectation des ressources et des charges de l’État. L'Assemblée nationale vote ici sur une partie de ce projet de loi de finances.<br><br><a href='https://www.gouvernement.fr/projet-de-loi-de-finances-plf-qu-est-ce-que-c-est' targe='_blank'>Plus d'information</a>";
        }
      } elseif ($x['voteType'] == 'amendement') {
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
        $x['type_edited_explication'] = "Le projet de loi de finances (PLF), présenté à l'automne par le Gouvernement, est le projet de budget pour la France. C'est un document unique rassemblant l'ensemble des recettes et des dépenses de l’État pour l'année à venir. Le projet propose le montant, la nature et l'affectation des ressources et des charges de l’État. L'Assemblée nationale vote ici sur une partie de ce projet de loi de finances.<br><br><a href='https://www.gouvernement.fr/projet-de-loi-de-finances-plf-qu-est-ce-que-c-est' targe='_blank'>Plus d'information</a>";
      } elseif ($x['voteType'] == 'sous-amendement') {
        $x['type_edited'] = $x['voteType'];
        $x['type_edited_explication'] = "Un sous-amendement est, comme un amendement, une modification apportée à un texte juridique, d'un projet de loi par exemple. Le sous-amendement porte sur un amendement, et ne peut pas contredire le sens initial de l'amendement.";
      } elseif($x['voteId'] == 'VTANR5L15V1941'){
        $x['type_edited'] = 'déclaration de politique générale';
        $x['type_edited_explication'] = "La déclaration de politique générale est une déclaration du Premier ministre devant l'Assemblée nationale lors de l'entrée en fonction d'un nouveau gouvernement.";
      } elseif ($x['voteType'] == 'la propo') {
        $x['type_edited'] = 'la proposition de résolution';
        $x['type_edited_explication'] = "Une proposition de résolution est un texte non législatif (qui est donc purement symbolique) et qui sert à exprimer la position de l'Assemblée nationale sur un sujet donné. Ses modalités sont prévues l'article 34-1 de la <a href='http://www.assemblee-nationale.fr/connaissance/constitution.asp' target='_blank'>Constitution</a>.";
      } else {
        $x['type_edited'] = $x['voteType'];
      }

      //type_libelle
      $type_libelle = [
        "projet de loi" => "ce projet de loi",
        "amendement" => "cet amendement",
        "article" => "cet article",
        "déclaration de politique générale" => "cette déclaration de politique générale",
        "motion de renvoi en commission" => "cette motion de renvoi en commission",
        "conclusions de rejet de la commission" => "les conclusions de rejet de la commission",
        "crédits de mission" => "les crédits de mission",
        "déclaration du gouvernement" => "la déclaration du gouvernement",
        "demande de constitution de commission spéciale" => "cette demande de constitution de commission speciale",
        "motion de censure" => "cette motion de censure",
        "motion de rejet préalable" => "cette motion de rejet préalable",
        "partie du projet de loi de finances" => "cette partie du projet de loi de finances",
        "sous-amendement" => "ce sous-amendement",
        "la proposition de résolution" => "cette proposition de résolution",
        "proposition de loi" => "cette proposition de loi",
        "Projet ou proposition de loi organique" => "ce projet ou proposition de loi organique",
        "Projet de loi de finances rectificative" => "ce projet de loi de finances rectificative",
        "Ratification de traités et conventions" => "ce projet de ratificatio de traité",
        "Projet de loi de financement de la sécurité sociale" => "ce projet de loi de financement de la sécurité sociale",
        "Projet de loi de finances" => "ce projet de loi de finances"
      ];

      //echo $x["type_edited"];
      $x['type_edited_libelle'] = $type_libelle[$x['type_edited']];

      //sortCodeLibelle
      if ($x['sortCode'] == "adopté") {
        $x['sortCodeLibelle'] = "pour";
      } elseif ($x['sortCode'] == "rejeté") {
        $x['sortCodeLibelle'] = "contre";
      }

      return $x;
    }

    public function get_vote_groupes($num, $date, $legislature){
      $query = $this->db->query('
        SELECT B.*, C.n AS total_deputes, ROUND((B.nombreVotants * 100) / C.n, 1) AS percentageVotants
        FROM
        (
        SELECT A.*, A.nombrePours + A.nombreContres + A.nombreAbstentions AS nombreVotants, ROUND(gc.cohesion, 2) AS cohesion, gc.scoreGagnant, o.libelle, o.libelleAbrev, o.dateFin, o.positionPolitique, o.couleurAssociee
        FROM
        (
        	SELECT *
          FROM votes_groupes vg
        	WHERE vg.voteNumero = "'.$num.'" AND vg.legislature = "'.$legislature.'"
        ) A
        LEFT JOIN groupes_cohesion gc ON A.voteNumero = gc.voteNumero AND A.organeRef = gc.organeRef
        LEFT JOIN organes o ON A.organeRef = o.uid
        ) B
        LEFT JOIN (
        SELECT organeRef, COUNT(*) AS n
        FROM mandat_groupe
        WHERE legislature = 15 AND dateDebut <= "'.$date.'" AND (dateFin >= "'.$date.'" OR dateFin IS NULL) AND preseance != 1
        GROUP BY organeRef) C
        ON B.organeRef = C.organeRef
      ');

      return $query->result_array();
    }

    public function get_vote_deputes($num, $legislature){
      $query = $this->db->query('
      SELECT B.*
      FROM
      (
        SELECT A.*,
        CASE
        	WHEN A.vote = 1 THEN "pour"
        	WHEN A.vote = 0 THEN "abstention"
        	WHEN A.vote = -1 THEN "contre"
        ELSE NULL
        END AS vote_libelle,
        CASE
        	WHEN A.scoreLoyaute = 1 THEN "loyal"
        	WHEN A.scoreLoyaute = 0 THEN "rebel"
        ELSE NULL
        END AS loyaute_libelle,
        da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug,
        o.libelle, o.libelleAbrev
        FROM
        (
        	SELECT v.mpId, v.vote, v.mandatId, v.scoreLoyaute, v.legislature
        	FROM votes_scores v
        	WHERE v.voteNumero = '.$num.' AND legislature = '.$legislature.'
        ) A
        LEFT JOIN deputes_all da ON da.mpId = A.mpId AND da.legislature = A.legislature
        LEFT JOIN organes o ON da.groupeId = o.uid
      ) B
      ORDER BY B.nameLast ASC, B.nameFirst ASC
      ');

      return $query->result_array();
    }


    public function get_votes_datan_depute($depute_id, $limit = FALSE){
      if ($limit != FALSE) {
        $query = $this->db->query('
          SELECT A.*, f.name AS category_libelle,
          CASE
          	WHEN vs.vote = 0 THEN "abstention"
          	WHEN vs.vote = 1 THEN "pour"
          	WHEN vs.vote = -1 THEN "contre"
          	WHEN vs.vote IS NULL THEN "absent"
          	ELSE vs.vote
          END AS vote_depute,
          vs.positionGroup as vote_groupe, vs.scoreLoyaute AS depute_loyaute, vi.sortCode, vi.legislature, vp.participation, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR
          FROM
          (
          SELECT vd.id, vd.vote_id, vd.voteNumero, vd.legislature,
          vd.title AS vote_titre, vd.category
          FROM votes_datan vd
          WHERE vd.state = "published"
          ORDER BY vd.id DESC
          LIMIT 15
          ) A
          LEFT JOIN fields f ON A.category = f.id
          LEFT JOIN votes_scores vs ON A.voteNumero = vs.voteNUmero AND A.legislature = vs.legislature AND vs.mpId = "'.$depute_id.'"
          LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
          JOIN votes_participation vp ON A.voteNumero = vp.voteNumero AND A.legislature = vp.legislature AND vp.mpId = "'.$depute_id.'"
          ORDER BY vi.dateScrutin DESC
          LIMIT '.$limit.'
        ');
      } else {
        $query = $this->db->query('
          SELECT A.*, f.name AS category_libelle,
          CASE
            WHEN vs.vote = 0 THEN "abstention"
            WHEN vs.vote = 1 THEN "pour"
            WHEN vs.vote = -1 THEN "contre"
            WHEN vs.vote IS NULL THEN "absent"
            ELSE vs.vote
          END AS vote_depute,
          vs.positionGroup as vote_groupe, vs.scoreLoyaute AS depute_loyaute, vi.sortCode, vi.legislature, vp.participation, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR
          FROM
          (
          SELECT vd.id, vd.vote_id, vd.voteNumero, vd.legislature,
          vd.title AS vote_titre, vd.category
          FROM votes_datan vd
          WHERE vd.state = "published"
          ORDER BY vd.id DESC
          LIMIT 15
          ) A
          LEFT JOIN fields f ON A.category = f.id
          LEFT JOIN votes_scores vs ON A.voteNumero = vs.voteNUmero AND A.legislature = vs.legislature AND vs.mpId = "'.$depute_id.'"
          LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
          JOIN votes_participation vp ON A.voteNumero = vp.voteNumero AND A.legislature = vp.legislature AND vp.mpId = "'.$depute_id.'"
          ORDER BY vi.dateScrutin DESC
        ');
      }


      return $query->result_array();
    }

    public function get_votes_datan_depute_field($depute_id, $field, $limit){
      if ($limit != FALSE) {
        $query = $this->db->query('
          SELECT A.*, vs.positionGroup as vote_groupe, vs.scoreLoyaute AS depute_loyaute, vi.sortCode, vi.legislature, vp.participation, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR,
            CASE
          	WHEN vs.vote = 0 THEN "abstention"
          	WHEN vs.vote = 1 THEN "pour"
          	WHEN vs.vote = -1 THEN "contre"
          	WHEN vs.vote IS NULL THEN "absent"
          	ELSE vs.vote
            END AS vote_depute
          FROM
          (
              SELECT vd.id, vd.legislature, vd.voteNumero,
              vd.title AS vote_titre, vd.category, f.name AS category_libelle, f.slug
              FROM votes_datan vd
              LEFT JOIN fields f ON vd.category = f.id
              WHERE vd.state = "published" AND f.slug = "'.$field.'"
              ORDER BY vd.id DESC
          ) A
          JOIN votes_participation vp ON A.voteNumero = vp.voteNumero AND A.legislature = vp.legislature AND vp.mpId = "'.$depute_id.'"
          LEFT JOIN votes_scores vs ON A.voteNumero = vs.voteNumero AND A.legislature = vs.legislature AND vs.mpId = "'.$depute_id.'"
          LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
          ORDER BY vi.dateScrutin DESC
          LIMIT '.$limit.'
        ');
      } else {
        $query = $this->db->query('
          SELECT A.*, vs.positionGroup as vote_groupe, vs.scoreLoyaute AS depute_loyaute, vi.sortCode, vi.legislature, vp.participation, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR,
            CASE
          	WHEN vs.vote = 0 THEN "abstention"
          	WHEN vs.vote = 1 THEN "pour"
          	WHEN vs.vote = -1 THEN "contre"
          	WHEN vs.vote IS NULL THEN "absent"
          	ELSE vs.vote
            END AS vote_depute
          FROM
          (
              SELECT vd.id, vd.legislature, vd.voteNumero,
              vd.title AS vote_titre, vd.category, f.name AS category_libelle, f.slug
              FROM votes_datan vd
              LEFT JOIN fields f ON vd.category = f.id
              WHERE vd.state = "published" AND f.slug = "'.$field.'"
              ORDER BY vd.id DESC
          ) A
          JOIN votes_participation vp ON A.voteNumero = vp.voteNumero AND A.legislature = vp.legislature AND vp.mpId = "'.$depute_id.'"
          LEFT JOIN votes_scores vs ON A.voteNumero = vs.voteNumero AND A.legislature = vs.legislature AND vs.mpId = "'.$depute_id.'"
          LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
          ORDER BY vi.dateScrutin DESC
        ');
      }

      return $query->result_array();
    }

    public function get_votes_all_depute($depute_id, $legislature){
      $query = $this->db->query('
      SELECT A.voteId, A.voteNumero, A.dateScrutin, A.titre, A.title, A.legislature,
      CASE
      	WHEN A.vote = 1 THEN "pour"
          WHEN A.vote = -1 THEN "contre"
          WHEN A.vote = 0 THEN "abstention"
          WHEN A.vote IS NULL THEN "absent"
          ELSE NULL
      END AS vote,
      CASE
      	WHEN A.scoreLoyaute = 1 THEN "loyal"
      	WHEN A.scoreLoyaute = 0 THEN "rebel"
      ELSE NULL END AS loyaute
      FROM
      (
      SELECT vp.voteNumero, vp.participation, vs.vote, vs.scoreLoyaute, vi.voteId, vi.dateScrutin, vi.legislature, REPLACE(vi.titre, "n?", "n°") AS titre, vd.title AS title
      FROM votes_participation vp
      LEFT JOIN votes_scores vs ON (vs.voteNumero = vp.voteNumero AND vs.legislature = vp.legislature AND vs.mpId = "'.$depute_id.'")
      LEFT JOIN votes_info vi ON vp.voteNumero = vi.voteNumero AND vp.legislature = vi.legislature
      LEFT JOIN votes_datan vd ON vi.voteId = vd.vote_id AND vd.state = "published"
      WHERE vp.mpId = "'.$depute_id.'" AND vp.legislature = "'.$legislature.'"
      ) A
      ORDER BY voteNumero DESC
      ');

      return $query->result_array();
    }

    public function get_votes_all_groupe($uid, $legislature){
      $query = $this->db->query('
      SELECT A.*, vd.title,
      CASE
      	WHEN A.positionGroupe = 1 THEN "pour"
      	WHEN A.positionGroupe = -1 THEN "contre"
      	WHEN A.positionGroupe = 0 THEN "abstention"
      ELSE NULL END AS vote
      FROM
      (
      SELECT vi.voteId, vi.legislature, gc.voteNumero, gc.cohesion, gc.positionGroupe, vi.dateScrutin, REPLACE(vi.titre, "n?", "n°") AS titre
      FROM groupes_cohesion gc
      LEFT JOIN votes_info vi ON gc.voteNumero = vi.voteNumero AND gc.legislature = vi.legislature
      WHERE gc.organeRef = "'.$uid.'" AND gc.legislature = "'.$legislature.'"
      ) A
      LEFT JOIN  votes_datan vd ON A.voteId = vd.vote_id AND A.legislature = vd.legislature AND vd.state = "published"
      ORDER BY A.voteNumero DESC
      ');

      return $query->result_array();
    }

    public function get_votes_datan_groupe($groupe_uid, $limit = FALSE, $legislature){
      if ($limit != FALSE) {
        $query = $this->db->query('
          SELECT A.*, f.name AS category_libelle, v.positionMajoritaire AS vote, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR, vi.legislature
          FROM
          (
          SELECT vd.id, vd.voteNumero, vd.legislature, vd.title AS vote_titre, vd.slug, vd.category
          FROM votes_datan vd
          WHERE vd.state = "published"
          ORDER BY vd.id DESC
          LIMIT 15
          ) A
          LEFT JOIN fields f ON A.category = f.id
          JOIN votes_groupes v ON A.voteNumero = v.voteNumero AND A.legislature = v.legislature AND v.organeRef = "'.$groupe_uid.'"
          LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
          ORDER BY vi.dateScrutin DESC
          LIMIT '.$limit.'
        ');
      } else {
        $query = $this->db->query('
          SELECT A.*, f.name AS category_libelle, v.positionMajoritaire AS vote, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR, vi.legislature
          FROM
          (
          SELECT vd.id, vd.voteNumero, vd.legislature, vd.title AS vote_titre, vd.slug, vd.category
          FROM votes_datan vd
          WHERE vd.state = "published"
          ORDER BY vd.id DESC
          LIMIT 15
          ) A
          LEFT JOIN fields f ON A.category = f.id
          JOIN votes_groupes v ON A.voteNumero = v.voteNumero AND A.legislature = v.legislature AND v.organeRef = "'.$groupe_uid.'"
          LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
          ORDER BY vi.dateScrutin DESC
        ');
      }

      return $query->result_array();
    }

    public function get_votes_datan_groupe_field($groupe_uid, $field, $limit){
      if ($limit != FALSE) {
        $query = $this->db->query('
          SELECT A.*, f.name AS category_libelle, v.positionMajoritaire AS vote, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR, vi.legislature
          FROM
          (
            SELECT vd.id, vd. voteNumero, vd.legislature, vd.title AS vote_titre, vd.slug, vd.category
            FROM votes_datan vd
            LEFT JOIN fields f ON vd.category = f.id
            WHERE vd.state = "published" AND f.slug = "'.$field.'"
            ORDER BY vd.id DESC
            LIMIT 15
          ) A
          LEFT JOIN fields f ON A.category = f.id
          JOIN votes_groupes v ON A.voteNumero = v.voteNumero AND A.legislature = v.legislature AND v.organeRef = "'.$groupe_uid.'"
          LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
          ORDER BY vi.dateScrutin DESC
          LIMIT '.$limit.'
        ');
      } else {
        $query = $this->db->query('
          SELECT A.*, f.name AS category_libelle, v.positionMajoritaire AS vote, date_format(vi.dateScrutin, "%d %M %Y") as dateScrutinFR, vi.legislature
          FROM
          (
          SELECT vd.id, vd. voteNumero, vd.legislature, vd.title AS vote_titre, vd.slug, vd.category
          FROM votes_datan vd
            LEFT JOIN fields f ON vd.category = f.id
            WHERE vd.state = "published" AND f.slug = "'.$field.'"
            ORDER BY vd.id DESC
          ) A
          LEFT JOIN fields f ON A.category = f.id
          JOIN votes_groupes v ON A.voteNumero = v.voteNumero AND A.legislature = v.legislature AND v.organeRef = "'.$groupe_uid.'"
          LEFT JOIN votes_info vi ON A.voteNumero = vi.voteNumero AND A.legislature = vi.legislature
          ORDER BY vi.dateScrutin DESC
        ');
      }


      return $query->result_array();
    }

    public function get_key_votes_mp($mpId){
      $query = $this->db->query('
        SELECT vs.voteNumero, vs.scoreLoyaute, vs.vote,
        CASE
          WHEN vs.vote = 1 THEN "pour"
          WHEN vs.vote = 0 THEN "abstention"
          WHEN vs.vote = -1 THEN "contre"
        ELSE NULL
        END AS vote_libelle
        FROM votes_scores vs
        WHERE vs.voteNumero IN (2814, 2940, 3254) AND vs.mpId = "'.$mpId.'"
        GROUP BY vs.voteNumero
      ');

      $votes = $query->result_array();

      foreach ($votes as $key => $value) {
        $voteNumero = $value["voteNumero"];
        $array[$voteNumero] = array(
          "vote" => $value["vote"],
          "loyaute" => $value["scoreLoyaute"],
          "vote_libelle" => $value["vote_libelle"]
        );
      }

      if (!isset($array)) {
        $array = NULL;
      }

      return $array;
    }

    public function get_most_famous_votes($limit = false){
      $queryString = '
      SELECT vd.title AS voteTitre, vd.description, vi.dateScrutin, vi.voteNumero, vi.legislature, f.name AS category_libelle, f.slug AS category_slug, vi.sortCode, date_format(dateScrutin, "%d %M %Y") as dateScrutinFR, vote_id, vi.voteNumero
      FROM votes_datan vd
      LEFT JOIN votes_info vi ON vd.vote_id = vi.voteId
      LEFT JOIN fields f ON vd.category = f.id
      WHERE vd.state = "published"
      ORDER BY vi.voteNumero DESC';
      if ($limit) {
        $queryString .= ' LIMIT ' .$limit;
      }
      $query = $this->db->query($queryString);

      return $query->result_array();
    }

    public function get_votes_result_by_depute($voteNumeros){
      $query = $this->db->query('SELECT votes.voteNumero, organes.libelle, deputes_all.nameFirst,
      deputes_all.nameLast, votes.vote, organes.uid, deputes_all.mpId FROM `votes`
      LEFT JOIN mandat_principal ON votes.mandatId=mandat_principal.mandatId
      LEFT JOIN deputes_all ON votes.mandatId=deputes_all.mandatId
      LEFT JOIN organes ON deputes_all.groupeId=organes.uid
      WHERE votes.voteNumero IN ('. implode(",", $voteNumeros). ')');

      return $query->result_array();
    }
  }
