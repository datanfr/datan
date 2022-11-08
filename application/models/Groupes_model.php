<?php
  class Groupes_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_groupes_all($active, $legislature) {
      if ($active && $legislature) {
        if ($legislature == legislature_current()) {
          $query = $this->db->query('SELECT *, o.legislature AS legislature, date_format(dateDebut, "%d %M %Y") as dateDebutFR, e.effectif,
            CASE WHEN o.libelle = "Non inscrit" THEN "Députés non inscrits" ELSE o.libelle END AS libelle
            FROM organes o
            LEFT JOIN groupes_effectif e ON o.uid = e.organeRef
            WHERE o.legislature = '.$this->db->escape(legislature_current()).' AND o.coteType = "GP" AND o.dateFin IS NULL AND o.libelleAbrev != "NI"
            ORDER BY e.effectif DESC, o.libelle
          ');
        } else {
          $query = $this->db->query('SELECT *, o.legislature AS legislature, date_format(dateDebut, "%d %M %Y") as dateDebutFR,
            CASE WHEN o.libelle = "Non inscrit" THEN "Députés non inscrits" ELSE o.libelle END AS libelle
            FROM organes o
            WHERE o.legislature = '.$this->db->escape($legislature).' AND o.coteType = "GP" AND o.libelleAbrev != "NI"
            ORDER BY o.libelle
          ');
        }
      } else {
        $query = $this->db->query('SELECT *, o.legislature, date_format(dateDebut, "%d %M %Y") as dateDebutFR,
          CASE WHEN o.libelle = "Non inscrit" THEN "Députés non inscrits" ELSE o.libelle END AS libelle
          FROM organes o
          LEFT JOIN groupes_effectif e ON o.uid = e.organeRef
          WHERE o.legislature = 15 AND o.coteType = "GP" AND o.dateFin IS NOT NULL AND o.libelleAbrev != "NI"
          ORDER BY e.effectif DESC, o.libelle
        ');
      }

      return $query->result_array();
    }

    public function get_all_groupes_majority($legislature = NULL){
      $this->db->where(array('coteType' => 'GP', 'positionPolitique' => 'majoritaire'));
      if ($legislature) {
        $this->db->where('legislature', $legislature);
      }
      $results = $this->db->get('organes')->result_array();
      foreach ($results as $key => $value) {
        $return[] = $value['uid'];
      }
      return $return;
    }

    public function get_majority_group($legislature = NULL){
      $legislature = $legislature ? $legislature : legislature_current();
      $sql = 'SELECT A.*
        FROM
        (
          SELECT *, CASE WHEN dateFin IS NULL THEN curdate() ELSE dateFin END AS dateFinSorted
          FROM organes
          WHERE coteType = "GP" AND legislature = ? AND positionPolitique = "majoritaire"
        ) A
        ORDER BY dateFinSorted DESC
        LIMIT 1
      ';

      $query = $this->db->query($sql, $legislature, 1);

      return $query->row_array();
    }

    public function get_all_groupes_ni($legislature = NULL){
      $this->db->where(array('coteType' => 'GP', 'libelleAbrev' => 'NI'));
      if ($legislature) {
        $this->db->where('legislature', $legislature);
      }
      $results = $this->db->get('organes')->result_array();
      foreach ($results as $key => $value) {
        $return[] = $value['uid'];
      }
      return $return;
    }

    public function get_groupes_from_mp_array($input){
      $groupes = array();
      foreach ($input as $mp) {
        if ($mp['libelleAbrev']) {
          $libelleAbrev = $mp['libelleAbrev'];
          $groupes[$libelleAbrev]["libelle"] = $mp['libelle'];
          $groupes[$libelleAbrev]["libelleAbrev"] = $libelleAbrev;
          $groupes[$libelleAbrev]["effectif"] = isset($groupes[$libelleAbrev]["effectif"]) ? $groupes[$libelleAbrev]["effectif"] + 1 : 1;
        }
      }
      array_multisort( array_column($groupes, "effectif"), SORT_DESC, $groupes );
      return $groupes;
    }

    public function get_groupes_sorted($groupes){
      $groupesLibelle = array_column($groupes, 'libelleAbrev');
      function cmp(array $a) {
        $order = array("GDR-NUPES", "LFI-NUPES", "SOC", "ECOLO", "RE", "DEM", "HOR", "LIOT", "LR", "RN");
        foreach ($order as $x) {
          $y[] = array_search($x, $a);
        }
        return $y;
      }
      $sort = cmp($groupesLibelle);
      $empty = NULL;
      foreach ($sort as $key => $value) {
        if (empty($value) && $value !== 0) {
          $empty = TRUE;
        }
      }
      foreach ($groupes as $key => $value) {
        $groupes[$key]['couleurAssociee'] = $this->groupes_model->get_groupe_color(array($value['libelleAbrev'], $value['couleurAssociee']));
      }
      if ($empty != TRUE) {
        return array_replace(array_flip($sort), $groupes);
      }
    }

    public function get_number_active_groupes(){
      $this->db->where(array('coteType' => 'GP', 'libelleAbrev != ' => 'NI', 'dateFin' => NULL, 'legislature' => legislature_current()));
      return $this->db->count_all_results('organes');
    }

    public function get_number_inactive_groupes(){
      $this->db->where(array('coteType' => 'GP', 'libelleAbrev != ' => 'NI', 'legislature' => legislature_current()));
      $this->db->where('dateFin IS NOT NULL');
      return $this->db->count_all_results('organes');
    }

    public function get_number_mps_groupes(){
      $this->db->where(array('libelleAbrev != ' => 'NI','dateFin' => NULL, 'legislature' => legislature_current()));
      return $this->db->count_all_results('deputes_all');
    }

    public function get_number_mps_unattached(){
      $this->db->where(array('libelleAbrev' => 'NI', 'dateFin' => NULL, 'legislature' => legislature_current()));
      return $this->db->count_all_results('deputes_all');
    }

    public function get_groupe_random(){
      $sql = 'SELECT o.uid, o.libelle, o.libelleAbrev, o.couleurAssociee, o.legislature, e.effectif
        FROM organes o
        LEFT JOIN groupes_effectif e ON o.uid = e.organeRef
        WHERE o.legislature = ? AND o.coteType = "GP" AND o.dateFin IS NULL AND o.libelle != "Non inscrit"
        ORDER BY RAND()
        LIMIT 1
      ';
      $query = $this->db->query($sql, legislature_current());

      return $query->row_array();
    }

    public function get_groupes_individal($groupe, $legislature){
      $sql = 'SELECT o.uid, o.coteType, o.libelle, o.libelleEdition, o.libelleAbrev, o.libelleAbrege, o.dateDebut, o.dateFin, o.regime, o.legislature, o.positionPolitique, o.preseance, o.couleurAssociee,
        ge.classement, ge.effectif, ROUND((ge.effectif / 577) * 100) AS effectifShare,
        ROUND(gs.age) AS age, ROUND(gs.womenPct) AS womenPct, womenN,
        date_format(dateDebut, "%d %M %Y") as dateDebutFR, date_format(dateFin, "%d %M %Y") as dateFinFR
        FROM organes o
        LEFT JOIN groupes_effectif ge ON o.uid = ge.organeRef
        LEFT JOIN groupes_stats gs ON o.uid = gs.organeRef
        WHERE o.legislature = ? AND o.libelleAbrev = ? AND o.coteType = "GP"
        LIMIT 1
      ';
      $query = $this->db->query($sql, array($legislature, $groupe));

      return $query->row_array();
    }

    public function get_groupe_by_id($id){
      return $this->db->get_where('organes', array('uid' => $id))->row_array();
    }

    public function get_groupes_president($groupe_uid, $legislature, $active){
      if ($active) {
        $where = array(
          'mandat_groupe.organeRef' => $groupe_uid,
          'mandat_groupe.preseance' => 1,
          'mandat_groupe.dateFin' => NULL
        );
        $this->db->select('*, date_format(dateDebut, "%d %M %Y") as dateDebutFR');
        $this->db->select('libelle AS libelle, libelleAbrev AS libelleAbrev');
        $this->db->select('CONCAT(departementNom, " (", departementCode, ")") AS cardCenter');
        $this->db->join('deputes_last', 'deputes_last.mpId = mandat_groupe.mpId', 'left');
        $query = $this->db->get_where('mandat_groupe', $where, 1);
      } else {
        $sql = 'SELECT A.*, da.*,
          CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter
          FROM
          (
            SELECT mpId, dateDebut, date_format(dateDebut, "%d %M %Y") as dateDebutFR, dateFin, date_format(dateFin, "%d %M %Y") as dateFinFR, codeQualite, libQualiteSex
            FROM mandat_groupe
            WHERE organeRef = ? AND preseance = 1 AND legislature = ?
            ORDER BY dateFin DESC
            LIMIT 1
          ) A
          LEFT JOIN deputes_last da ON da.mpId = A.mpId
          LIMIT 1
        ';
        $query = $this->db->query($sql, array($groupe_uid, $legislature));
      }

      return $query->row_array();
    }

    public function get_groupe_membres($groupe_uid, $dateFin){
      if (!$dateFin) {
        $where = array(
          'mandat_groupe.organeRef' => $groupe_uid,
          'mandat_groupe.dateFin' => NULL,
          'mandat_groupe.nominPrincipale' => 1
        );
        $this->db->select('*, libelle AS libelle, libelleAbrev AS libelleAbrev');
        $this->db->select('CONCAT(departementNom, " (", departementCode, ")") AS cardCenter');
        $this->db->where_in('mandat_groupe.preseance', array(20, 28));
        $this->db->join('deputes_last', 'deputes_last.mpId = mandat_groupe.mpId');
        $this->db->order_by('nameLast ASC, nameFirst ASC');
        $query = $this->db->get_where('mandat_groupe', $where);
      } else {
        $sql = 'SELECT A.*, da.*,
          CONCAT(departementNom, " (", departementCode, ")") AS cardCenter
          FROM
          (
          SELECT mg.mpId, mg.dateFin, mg.codeQualite, mg.libQualiteSex
          FROM mandat_groupe mg
          WHERE mg.organeRef = ? AND mg.nominPrincipale = 1 AND mg.preseance IN (20, 28) AND ? BETWEEN mg.dateDebut AND mg.dateFin
          GROUP BY mg.mpId
          ) A
          LEFT JOIN deputes_last da ON da.mpId = A.mpId
          ORDER BY da.nameLast ASC, da.nameFirst ASC
        ';
        $query = $this->db->query($sql, array($groupe_uid, $dateFin));
      }



      return $query->result_array();
    }

    public function get_groupe_apparentes($groupe_uid, $active){
      if ($active) {
        $where = array(
          'mandat_groupe.organeRef' => $groupe_uid,
          'mandat_groupe.dateFin' => NULL,
          'mandat_groupe.nominPrincipale' => 1
        );
        $this->db->select('*, libelle AS libelle, libelleAbrev AS libelleAbrev');
        $this->db->select('CONCAT(departementNom, " (", departementCode, ")") AS cardCenter');
        $this->db->where_in('mandat_groupe.preseance', array(24));
        $this->db->join('deputes_last', 'deputes_last.mpId = mandat_groupe.mpId');
        $this->db->order_by('nameLast ASC, nameFirst ASC');
        $query = $this->db->get_where('mandat_groupe', $where);
      } else {
        $sql = 'SELECT A.*, da.*,
          CONCAT(departementNom, " (", departementCode, ")") AS cardCenter
          FROM
          (
          SELECT mg.mpId, mg.dateFin, mg.codeQualite, mg.libQualiteSex
          FROM mandat_groupe mg
          WHERE mg.organeRef = ? AND mg.nominPrincipale = 1 AND mg.preseance = 24
          GROUP BY mg.mpId
          ) A
          LEFT JOIN deputes_last da ON da.mpId = A.mpId
          ORDER BY da.nameLast ASC, da.nameFirst ASC
        ';
        $query = $this->db->query($sql, $groupe_uid);
      }


      return $query->result_array();
    }

    public function get_groupe_social_media($groupe){
      switch ($groupe['libelleAbrev']) {
        case 'LAREM':
          $groupe['twitter'] = '@LaREM_AN';
          $groupe['facebook'] = 'deputesLaREM';
          break;

        case 'LR':
          $groupe['website'] = 'https://www.deputes-les-republicains.fr/';
          $groupe['twitter'] = 'Republicains_An';
          $groupe['facebook'] = 'LesDeputesLesRepublicains';
          break;

        case 'MODEM':
          $groupe['website'] = 'https://groupemodem.fr/';
          $groupe['twitter'] = 'GroupeMoDem';
          $groupe['facebook'] = 'GroupeMoDem';
          break;

        case 'SOC':
          $groupe['twitter'] = 'socialistesAN';
          $groupe['facebook'] = 'socialistesAN';
          break;

        case 'AGIR-E':
          $groupe['twitter'] = 'AgirEnsemble_AN';
          $groupe['facebook'] = 'AgirEnsembleAN';
          break;

        case 'UDI_I':
          $groupe['twitter'] = 'deputesudi_ind';
          $groupe['facebook'] = 'DeputesUDI.Ind';
          break;

        case 'FI':
        case 'LFI-NUPES':
          $groupe['twitter'] = 'FiAssemblee';
          $groupe['facebook'] = 'FiAssemblee';
          break;

        case 'EDS':
          $groupe['website'] = 'https://www.ecologie-democratie-solidarite.fr/';
          $groupe['twitter'] = 'EDSAssNat';
          $groupe['facebook'] = 'EDSAssNat';
          break;

        case 'GDR':
        case 'GDR-NUPES':
          $groupe['website'] = 'http://www.groupe-communiste.assemblee-nationale.fr/';
          $groupe['facebook'] = 'LesDeputesCommunistes';
          $groupe['twitter'] = 'deputesPCF';
          break;

        case 'LT':
        case 'LIOT':
          $groupe['twitter'] = 'GroupeLIOT_An';
          $groupe['facebook'] = 'Groupe-Libertés-et-Territoires-à-lAssemblée-nationale-1898196496883591';
          break;

        case 'RE':
          $groupe['twitter'] = 'LaREM_AN';
          $groupe['facebook'] = 'deputesRenaissance';
          break;

        case 'RN':
          $groupe['twitter'] = 'rnational_off';
          $groupe['facebook'] = 'RassemblementNational';
          break;

        case 'HOR':
          $groupe['website'] = 'https://horizonsleparti.fr/';
          $groupe['twitter'] = 'Horizons_AN';
          $groupe['facebook'] = 'horizonsleparti';

        case 'ECOLO':
          $groupe['website'] = 'https://www.eelv.fr/';
          $groupe['twitter'] = 'EELV';
          $groupe['facebook'] = 'eelv.fr';

        default:
          // code...
          break;
      }
      return $groupe;
    }

    public function get_stats($groupe_uid){
      $query = $this->db->get_where('class_groups', array('organeRef' => $groupe_uid));
      $results = $query->result_array();

      foreach ($results as $key => $value) {
        if ($value['stat'] == 'cohesion') {
          $return[$value['stat']] = array('value' => $value['value'], 'votes' => $value['votes']);
        } else {
          $return[$value['stat']] = array('value' => round($value['value'] * 100), 'votes' => $value['votes']);
        }
      }
      return $return;
    }

    public function get_stats_history($groups){
      $this->db->select('cg.*, o.libelle, o.libelleAbrev, o.couleurAssociee, cg.active, o.positionPolitique, o.dateDebut, o.dateFin');
      $this->db->where_in('cg.organeRef', $groups);
      $this->db->join('organes o', 'o.uid = cg.organeRef', 'left');
      $this->db->order_by('cg.legislature', 'ASC');
      $query = $this->db->get('class_groups cg');
      $results = $query->result_array();

      foreach ($results as $key => $value) {
        $return[$value['stat']][] = $value;
      }

      return $return;
    }

    public function get_stats_avg($legislature){
      $sql = 'SELECT stat, ROUND(AVG(value), 3) AS mean
        FROM class_groups
        WHERE legislature = ?
        GROUP BY stat
      ';
      $query = $this->db->query($sql, $legislature);
      $results = $query->result_array();
      foreach ($results as $key => $value) {
        if ($value['stat'] == 'cohesion') {
          $return[$value['stat']] = $value['mean'];
        } else {
          $return[$value['stat']] = round($value['mean'] * 100);
        }
      }
      return $return;
    }


    public function get_stats_proximite($groupe_uid){
      $sql = 'SELECT t1.prox_group, ROUND(t1.score * 100) AS score, o.libelle, o.libelleAbrege, o.libelleAbrev, o.positionPolitique, o.legislature
        FROM class_groups_proximite t1
        LEFT JOIN organes o ON o.uid = t1.prox_group
        WHERE t1.organeRef = ? AND t1.prox_group != ? AND o.dateFin IS NULL AND t1.score IS NOT NULL AND o.libelleAbrev != "NI" AND votesN > 20
        ORDER BY t1.score DESC
      ';
      $query = $this->db->query($sql, array($groupe_uid, $groupe_uid));

      return $query->result_array();
    }

    public function get_stats_proximite_all($groupe_uid){
      $sql = 'SELECT t1.prox_group, ROUND(t1.score * 100) AS score, t1.votesN,  o.libelle, o.libelleAbrege, o.libelleAbrev, o.positionPolitique, o.dateFin, o.legislature,
        IF(o.dateFin IS NULL, 0, 1) AS ended
        FROM class_groups_proximite t1
        LEFT JOIN organes o ON o.uid = t1.prox_group
        WHERE t1.organeRef = ? AND t1.prox_group != ? AND t1.score IS NOT NULL
        ORDER BY t1.score DESC
      ';
      $query = $this->db->query($sql, array($groupe_uid, $groupe_uid));

      return $query->result_array();
    }

    public function get_groupe_color($x){
      switch ($x[0]) {
        case 'SOC':
          $couleur = "#e30040";
          break;

        case 'UDI_I':
          $couleur = "#5c5e8e";
          break;

        default:
          $couleur = $x[1];
          break;
      }
      return $couleur;
    }

    public function get_organization_schema($groupe, $president, $members){
      $schema = [
        "@context" => "http://schema.org",
        "@type" => "Organization",
        "name" => $groupe['libelle'] . " (" . $groupe['libelleAbrev'] . ")",
        "description" => "Le groupe " . $groupe['libelle'] . " est un groupe parlementaire de l'Assemblée nationale française.",
        "url" => base_url() . "groupes/legislature-".$groupe['legislature']."/" . mb_strtolower($groupe['libelleAbrev']),
        "address" => [
          "@type" => "PostalAddress",
          "addressCountry" => "FR",
          "addressLocality" => "Paris",
          "postalCode" => "75355",
          "streetAddress" => "126 rue de l'Université"
        ],
        "member" => [
          [
            "@type" => "Person",
            "name" => $president['nameFirst']. ' '.$president['nameLast'],
            "familyName" => $president['nameLast'],
            "givenName" => $president['nameFirst'],
            "url" => base_url().'deputes/'.$president['dptSlug'].'/depute_'.$president['nameUrl'],
            "jobTitle" => "Député français et président du groupe " . name_group($groupe['libelle'])
          ]
        ],
        "foundingDate" => $groupe['dateDebut'],
        "logo" => asset_url() . "imgs/groupes/" . mb_strtolower($groupe['libelleAbrev']) . ".png",
        "numberOfEmployees" => $groupe["effectif"],
        "memberOf" => [
          "@type" => "Organization",
          "name" => "Assemblée nationale",
          "url" => "http://www.assemblee-nationale.fr/",
          "foundingDate" => "1958-10-04",
          "sameAs" => "https://fr.wikipedia.org/wiki/Assembl%C3%A9e_nationale_(France)",
          "location" => [
            "@type" => "Place",
            "address" => [
              "@type" => "PostalAddress",
              "addressCountry" => "FR",
              "addressLocality" => "Paris",
              "postalCode" => "75355",
              "streetAddress" => "126 rue de l'Université"
            ]
          ]
        ]
      ];

      if ($groupe['dateFin'] != NULL) {
        $schema["dissolutionDate"] = $groupe["dateFin"];
      }

      if (isset($groupe['website']) && $groupe['website']) {
        if (!isset($schema['sameAs'])) {
          $schema['sameAs'] = array($groupe['website']);
        }
      }

      if (isset($groupe['twitter']) && $groupe['twitter']) {
        $twitter = "https://twitter.com/" . $groupe['twitter'];
        if (!isset($schema['sameAs'])) {
          $schema['sameAs'] = array($twitter);
        } else {
          array_push($schema['sameAs'], $twitter);
        }
      }

      if (isset($groupe['facebook']) && $groupe['facebook']) {
        $facebook = "https://www.facebook.com/" . $groupe['facebook'];
        if (!isset($schema['sameAs'])) {
          $schema['sameAs'] = array($facebook);
        } else {
          array_push($schema['sameAs'], $facebook);
        }
      }

      if ($members) {
        // Membres
        foreach ($members["members"] as $member) {
          $array = array(
            "@type" => "Person",
            "name" => $member['nameFirst']. ' '.$member['nameLast'],
            "familyName" => $member['nameLast'],
            "givenName" => $member['nameFirst'],
            "url" => base_url().'deputes/'.$member['dptSlug'].'/depute_'.$member['nameUrl'],
          );
          if (!$groupe['dateFin']) {
            $array["jobTitle"] = "Député français et membre du groupe " . $member['libelle'];
          }
          array_push($schema["member"], $array);
        }
        // Apparentes
        foreach ($members["apparentes"] as $member) {
          $array = array(
            "@type" => "Person",
            "name" => $member['nameFirst']. ' '.$member['nameLast'],
            "familyName" => $member['nameLast'],
            "givenName" => $member['nameFirst'],
            "url" => base_url().'deputes/'.$member['dptSlug'].'/depute_'.$member['nameUrl']
          );
          if (!$groupe['dateFin']) {
            $array["jobTitle"] = "Député français et membre apparenté au groupe " . $member['libelle'];
          }
          array_push($schema["member"], $array);
        }
      }

      return $schema;
    }

    public function get_history($id){
      $families = array(
        array('PO800538', 'PO730964'), // Renaissance
        array('PO730958', 'PO800490'), // France insoumise
        array('PO270903', 'PO389395', 'PO656006', 'PO707869', 'PO730934', 'PO800508'), // Les Républicains
        array('PO730970', 'PO774834', 'PO800484'), // Modem
        array('PO758835', 'PO730946', 'PO389507', 'PO656002', 'PO713077', 'PO270907', 'PO800496'), // Socialistes
        array('PO656014', 'PO800526'), // Ecologiste
        array('PO270915', 'PO389635', 'PO656018', 'PO730940', 'PO800502'), // Communistes
        array('PO759900', 'PO800532'), // Libertés et territoires
      );

      foreach ($families as $family) {
        foreach ($family as $group) {
          if ($group == $id) {
            return $family;
          }
        }
      }

    }

  }
?>
