<?php
  class Groupes_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_groupes_all($active, $legislature) {
      if ($active && $legislature) {
        if ($legislature == legislature_current()) {
          $query = $this->db->query('SELECT *, date_format(dateDebut, "%d %M %Y") as dateDebutFR, e.effectif,
            CASE WHEN o.libelle = "Non inscrit" THEN "Députés non inscrits" ELSE o.libelle END AS libelle
            FROM organes o
            LEFT JOIN groupes_effectif e ON o.uid = e.organeRef
            WHERE o.legislature = '.$this->db->escape(legislature_current()).' AND o.coteType = "GP" AND o.dateFin IS NULL
            ORDER BY e.effectif DESC, o.libelle
          ');
        } else {
          $query = $this->db->query('SELECT *, date_format(dateDebut, "%d %M %Y") as dateDebutFR, e.effectif,
            CASE WHEN o.libelle = "Non inscrit" THEN "Députés non inscrits" ELSE o.libelle END AS libelle
            FROM organes o
            LEFT JOIN groupes_effectif e ON o.uid = e.organeRef
            WHERE o.legislature = '.$this->db->escape($legislature).' AND o.coteType = "GP"
            ORDER BY e.effectif DESC, o.libelle
          ');
        }
      } else {
        $query = $this->db->query('SELECT *, date_format(dateDebut, "%d %M %Y") as dateDebutFR,
          CASE WHEN o.libelle = "Non inscrit" THEN "Députés non inscrits" ELSE o.libelle END AS libelle
          FROM organes o
          LEFT JOIN groupes_effectif e ON o.uid = e.organeRef
          WHERE o.legislature = 15 AND o.coteType = "GP" AND o.dateFin IS NOT NULL
          ORDER BY e.effectif DESC, o.libelle
        ');
      }

      return $query->result_array();

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
      $sql = 'SELECT o.uid, o.libelle, o.libelleAbrev, o.couleurAssociee, e.effectif
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
        date_format(dateDebut, "%d %M %Y") as dateDebutFR, date_format(dateFin, "%d %M %Y") as dateFinFr
        FROM organes o
        LEFT JOIN groupes_effectif ge ON o.uid = ge.organeRef
        LEFT JOIN groupes_stats gs ON o.uid = gs.organeRef
        WHERE o.legislature = ? AND o.libelleAbrev = ? AND o.coteType = "GP"
        LIMIT 1
      ';
      $query = $this->db->query($sql, array($legislature, $groupe));

      return $query->row_array();
    }

    public function get_groupes_president($groupe_uid, $active){
      if ($active) {
        $where = array(
          'mandat_groupe.organeRef' => $groupe_uid,
          'mandat_groupe.preseance' => 1,
          'mandat_groupe.dateFin' => NULL
        );
        $this->db->select('*, date_format(dateDebut, "%d %M %Y") as dateDebutFR');
        $this->db->select('libelle AS groupLibelle, libelleAbrev AS groupLibelleAbrev');
        $this->db->select('CONCAT(departementNom, " (", departementCode, ")") AS cardCenter');
        $this->db->join('deputes_last', 'deputes_last.mpId = mandat_groupe.mpId', 'left');
        $query = $this->db->get_where('mandat_groupe', $where, 1);
      } else {
        $sql = 'SELECT A.*, civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.departementNom, da.departementCode, da.img
          FROM
          (
          SELECT mpId, dateDebut, date_format(dateDebut, "%d %M %Y") as dateDebutFR, dateFin, codeQualite, libQualiteSex
          FROM mandat_groupe
          WHERE organeRef = ? AND preseance = 1 AND legislature = 15
          ORDER BY dateFin DESC
          LIMIT 1
          ) A
          LEFT JOIN deputes_last da ON da.mpId = A.mpId
          LIMIT 1
        ';
        $query = $this->db->query($sql, $groupe_uid);
      }

      return $query->row_array();
    }

    public function get_groupe_membres($groupe_uid, $active){
      if ($active) {
        $where = array(
          'mandat_groupe.organeRef' => $groupe_uid,
          'mandat_groupe.dateFin' => NULL,
          'mandat_groupe.nominPrincipale' => 1
        );
        $this->db->select('*, libelle AS groupLibelle, libelleAbrev AS groupLibelleAbrev');
        $this->db->select('CONCAT(departementNom, " (", departementCode, ")") AS cardCenter');
        $this->db->where_in('mandat_groupe.preseance', array(20, 28));
        $this->db->join('deputes_last', 'deputes_last.mpId = mandat_groupe.mpId');
        $this->db->order_by('nameLast ASC, nameFirst ASC');
        $query = $this->db->get_where('mandat_groupe', $where);
      } else {
        $sql = 'SELECT A.*, da.civ, da.nameFirst, da.nameLast, da.nameUrl, da.dptSlug, da.departementNom, da.departementCode, da.img
          FROM
          (
          SELECT mg.mpId, mg.dateFin, mg.codeQualite, mg.libQualiteSex
          FROM mandat_groupe mg
          WHERE mg.organeRef = ? AND mg.nominPrincipale = 1 AND mg.legislature = 15 AND mg.preseance IN (20, 28)
          GROUP BY mg.mpId
          ) A
          LEFT JOIN deputes_last da ON da.mpId = A.mpId
          ORDER BY da.nameLast ASC, da.nameFirst ASC
        ';
        $query = $this->db->query($sql, $groupe_uid);
      }



      return $query->result_array();
    }

    public function get_groupe_apparentes($groupe_uid, $active){
      $where = array(
        'mandat_groupe.organeRef' => $groupe_uid,
        'mandat_groupe.dateFin' => NULL,
        'mandat_groupe.nominPrincipale' => 1
      );
      $this->db->select('*, libelle AS groupLibelle, libelleAbrev AS groupLibelleAbrev');
      $this->db->select('CONCAT(departementNom, " (", departementCode, ")") AS cardCenter');
      $this->db->where_in('mandat_groupe.preseance', array(24));
      $this->db->join('deputes_last', 'deputes_last.mpId = mandat_groupe.mpId');
      $this->db->order_by('nameLast ASC, nameFirst ASC');
      $query = $this->db->get_where('mandat_groupe', $where);

      return $query->result_array();
    }

    public function get_effectif_inactif($groupe_uid){
      $sql = 'SELECT COUNT(A.mpId) AS effectif
        FROM
        (
        SELECT mg.mpId
        FROM mandat_groupe mg
        LEFT JOIN organes o ON mg.organeRef = o.uid
        WHERE mg.organeRef = ? AND mg.dateFin = o.dateFin
        GROUP BY mg.mpId
        ) A
      ';
      $query = $this->db->query($sql, $groupe_uid);

      $result = $query->row_array();
      return $result['effectif'];
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
          $groupe['twitter'] = 'FiAssemblee';
          $groupe['facebook'] = 'FiAssemblee';
          break;

        case 'EDS':
          $groupe['website'] = 'https://www.ecologie-democratie-solidarite.fr/';
          $groupe['twitter'] = 'EDSAssNat';
          $groupe['facebook'] = 'EDSAssNat';
          break;

        case 'GDR':
          $groupe['website'] = 'http://www.groupe-communiste.assemblee-nationale.fr/';
          $groupe['facebook'] = 'LesDeputesCommunistes';
          $groupe['twitter'] = 'deputesPCF';
          break;

        case 'LT':
          $groupe['twitter'] = 'GroupeLibTerrAN';
          $groupe['facebook'] = 'Groupe-Libertés-et-Territoires-à-lAssemblée-nationale-1898196496883591';
          break;

        default:
          // code...
          break;
      }
      return $groupe;
    }

    public function get_stats($groupe_uid){
      $sql = 'SELECT *,
        ROUND(cohesion, 2) AS cohesion,
        ROUND(participation * 100) AS participation,
        ROUND(majoriteAccord * 100) AS majorite
        FROM class_groups
        WHERE organeRef = ?
      ';
      $query = $this->db->query($sql, $groupe_uid);

      return $query->row_array();
    }

    public function get_stats_avg($legislature){
      $sql = 'SELECT
        ROUND(AVG(cohesion), 2) AS cohesion,
        ROUND(AVG(participation) * 100) AS participation,
        ROUND(AVG(majoriteAccord) * 100) AS majorite
        FROM class_groups
        WHERE legislature = ?
      ';
      $query = $this->db->query($sql, $legislature);

      return $query->row_array();
    }


    public function get_stats_proximite($groupe_uid){
      $sql = 'SELECT t1.prox_group, ROUND(t1.score * 100) AS score, o.libelle, o.libelleAbrege, o.libelleAbrev, o.positionPolitique
        FROM class_groups_proximite t1
        LEFT JOIN organes o ON o.uid = t1.prox_group
        WHERE t1.organeRef = ? AND t1.prox_group != ? AND o.dateFin IS NULL AND t1.score IS NOT NULL AND o.libelleAbrev != "NI" AND votesN > 20
        ORDER BY t1.score DESC
      ';
      $query = $this->db->query($sql, array($groupe_uid, $groupe_uid));

      return $query->result_array();
    }

    public function get_stats_proximite_all($groupe_uid){
      $sql = 'SELECT t1.prox_group, ROUND(t1.score * 100) AS accord, t1.votesN,  o.libelle, o.libelleAbrege, o.libelleAbrev, o.positionPolitique, o.dateFin,
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
        "url" => base_url() . "groupes/" . $groupe['libelleAbrev'],
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
            "jobTitle" => "Député français et président du groupe " . $groupe['libelle']
          ]
        ],
        "foundingDate" => $groupe['dateDebut'],
        "logo" => asset_url() . "imgs/groupes/" . $groupe['libelleAbrev'] . ".png",
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
        print_r($groupe);
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

  }
?>
