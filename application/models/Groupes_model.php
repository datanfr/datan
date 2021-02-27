<?php
  class Groupes_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_groupes_all($active, $legislature) {
      if ($active == TRUE) {
        if ($legislature == legislature_current()) {
          $query = $this->db->query('
          SELECT *, date_format(dateDebut, "%d %M %Y") as dateDebutFR, e.effectif,
            CASE WHEN o.libelle = "Non inscrit" THEN "Députés non inscrits" ELSE o.libelle END AS libelle
          FROM organes o
          LEFT JOIN groupes_effectif e ON o.uid = e.organeRef
          WHERE o.legislature = '.legislature_current().' AND o.coteType = "GP" AND o.dateFin IS NULL
          ORDER BY e.effectif DESC, o.libelle
          ');
        } else {
          $query = $this->db->query('
          SELECT *, date_format(dateDebut, "%d %M %Y") as dateDebutFR, e.effectif,
            CASE WHEN o.libelle = "Non inscrit" THEN "Députés non inscrits" ELSE o.libelle END AS libelle
          FROM organes o
          LEFT JOIN groupes_effectif e ON o.uid = e.organeRef
          WHERE o.legislature = '.$legislature.' AND o.coteType = "GP"
          ORDER BY e.effectif DESC, o.libelle
          ');
        }
      } else {
        $query = $this->db->query('
        SELECT *, date_format(dateDebut, "%d %M %Y") as dateDebutFR,
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
      $query = $this->db->query('
        SELECT COUNT(o.uid) AS n
        FROM organes o
        WHERE o.coteType = "GP" AND o.dateFin IS NULL AND o.libelleAbrev != "NI"
      ');
      return $query->row_array();
    }

    public function get_number_inactive_groupes(){
      $query = $this->db->query('
        SELECT COUNT(o.uid) AS n
        FROM organes o
        WHERE o.coteType = "GP" AND o.dateFin IS NOT NULL AND o.libelleAbrev != "NI" AND legislature = 15
      ');
      return $query->row_array();
    }

    public function get_number_mps_groupes(){
      $query = $this->db->query('
      SELECT count(mpId) AS n
      FROM deputes_all
      WHERE legislature = '.legislature_current().' AND libelleAbrev != "NI" AND dateFin IS NULL
      ');
      return $query->row_array();
    }

    public function get_number_mps_unattached(){
      $query = $this->db->query('
      SELECT count(mpId) AS n
      FROM deputes_all
      WHERE legislature = '.legislature_current().' AND libelleAbrev = "NI" AND dateFin IS NULL
      ');
      return $query->row_array();
    }

    public function get_groupe_random(){
      $query = $this->db->query('
      SELECT A.*, e.effectif
      FROM
      (
        SELECT o.uid, o.libelle, o.libelleAbrev, o.couleurAssociee
        FROM organes o
        WHERE o.legislature = 15 AND o.coteType = "GP" AND o.dateFin IS NULL AND o.libelle != "Non inscrit"
        ORDER BY RAND()
        LIMIT 1
      ) A
      LEFT JOIN groupes_effectif e ON A.uid = e.organeRef
      ');

      $array = $query->row_array();

      return $array;
    }

    public function get_groupes_individal($groupe, $legislature){
      $query = $this->db->query('
      SELECT o.uid, o.coteType, o.libelle, o.libelleEdition, o.libelleAbrev, o.libelleAbrege, o.dateDebut, o.dateFin, o.regime, o.legislature, o.positionPolitique, o.preseance, o.couleurAssociee, ge.classement, ge.effectif, ROUND((ge.effectif / 577) * 100) AS effectifShare,
      ROUND(gs.age) AS age, ROUND(gs.womenPct) AS womenPct, womenN,
      date_format(dateDebut, "%d %M %Y") as dateDebutFR, date_format(dateFin, "%d %M %Y") as dateFinFr
      FROM organes o
      LEFT JOIN groupes_effectif ge ON o.uid = ge.organeRef
      LEFT JOIN groupes_stats gs ON o.uid = gs.organeRef
      WHERE o.legislature = '.$legislature.' AND o.libelleAbrev = "'.$groupe.'" AND o.coteType = "GP"
      ');

      $groupe = $query->row_array();

      return $groupe;
    }

    public function get_groupes_president($groupe_uid, $active){
      if ($active == TRUE) {
        $query = $this->db->query('
        SELECT A.*, civ, d.nameFirst, d.nameLast, d.nameUrl, dpt.slug AS dpt_slug, dpt.departement_nom, dpt.departement_code
        FROM
        (
        SELECT mpId, dateDebut, date_format(dateDebut, "%d %M %Y") as dateDebutFR, dateFin, codeQualite, libQualiteSex
        FROM mandat_groupe
        WHERE organeRef = "'.$groupe_uid.'" AND preseance = 1 AND legislature = 15 AND dateFin IS NULL
        ) A
        LEFT JOIN deputes d ON d.mpId = A.mpId
        LEFT JOIN mandat_principal mp ON mp.mpId = A.mpId
        LEFT JOIN departement dpt ON mp.electionDepartementNumero = dpt.departement_code
        WHERE mp.legislature = 15 AND mp.typeOrgane = "ASSEMBLEE" AND mp.dateFin IS NULL
        ');
      } else {
        $query = $this->db->query('
        SELECT A.*, civ, d.nameFirst, d.nameLast, d.nameUrl, dpt.slug AS dpt_slug, dpt.departement_nom, dpt.departement_code
        FROM
        (
        SELECT mpId, dateDebut, date_format(dateDebut, "%d %M %Y") as dateDebutFR, dateFin, codeQualite, libQualiteSex
        FROM mandat_groupe
        WHERE organeRef = "'.$groupe_uid.'" AND preseance = 1 AND legislature = 15
        ORDER BY dateFin DESC
        LIMIT 1
        ) A
        LEFT JOIN deputes d ON d.mpId = A.mpId
        LEFT JOIN mandat_principal mp ON mp.mpId = A.mpId
        LEFT JOIN departement dpt ON mp.electionDepartementNumero = dpt.departement_code
        WHERE mp.legislature = 15 AND mp.typeOrgane = "ASSEMBLEE"
        ');
      }


      return $query->row_array();
    }

    public function get_groupe_membres($groupe_uid, $active){
      if ($active == TRUE) {
        $query = $this->db->query('
        SELECT A.*, civ, d.nameFirst, d.nameLast, d.nameUrl, dpt.slug AS dpt_slug, dpt.departement_nom, dpt.departement_code
        FROM
        (
        SELECT mpId, dateFin, codeQualite, libQualiteSex
        FROM mandat_groupe
        WHERE organeRef = "'.$groupe_uid.'" AND nominPrincipale = 1 AND legislature = 15 AND dateFin IS NULL AND preseance IN (20, 28)
        ) A
        LEFT JOIN deputes d ON d.mpId = A.mpId
        LEFT JOIN mandat_principal mp ON mp.mpId = A.mpId
        LEFT JOIN departement dpt ON mp.electionDepartementNumero = dpt.departement_code
        WHERE mp.legislature = 15 AND mp.typeOrgane = "ASSEMBLEE" AND mp.dateFin IS NULL AND mp.preseance = 50
        ORDER BY d.nameLast
        ');
      } else {
        $query = $this->db->query('
        SELECT A.*, d.civ, d.nameFirst, d.nameLast, d.nameUrl, dpt.slug AS dpt_slug, dpt.departement_nom, dpt.departement_code
        FROM
        (
        SELECT mg.mpId, mg.dateFin, mg.codeQualite, mg.libQualiteSex
        FROM mandat_groupe mg
        WHERE mg.organeRef = "'.$groupe_uid.'" AND mg.nominPrincipale = 1 AND mg.legislature = 15 AND mg.preseance IN (20, 28)
        GROUP BY mg.mpId
        ) A
        LEFT JOIN deputes d ON d.mpId = A.mpId
        LEFT JOIN mandat_principal mp ON mp.mpId = A.mpId
        LEFT JOIN departement dpt ON mp.electionDepartementNumero = dpt.departement_code
        WHERE mp.legislature = 15 AND mp.typeOrgane = "ASSEMBLEE" AND mp.preseance = 50
        GROUP BY A.mpId
        ');
      }


      return $query->result_array();
    }

    public function get_groupe_apparentes($groupeId, $active){
      if ($active == TRUE) {
        $query = $this->db->query('
        SELECT A.*, civ, d.nameFirst, d.nameLast, d.nameUrl, dpt.slug AS dpt_slug, dpt.departement_nom, dpt.departement_code
        FROM
        (
        SELECT mpId, dateFin, codeQualite, libQualiteSex
        FROM mandat_groupe
        WHERE organeRef = "'.$groupeId.'" AND nominPrincipale = 1 AND legislature = 15 AND dateFin IS NULL AND preseance IN (24)
        ) A
        LEFT JOIN deputes d ON d.mpId = A.mpId
        LEFT JOIN mandat_principal mp ON mp.mpId = A.mpId
        LEFT JOIN departement dpt ON mp.electionDepartementNumero = dpt.departement_code
        WHERE mp.legislature = 15 AND mp.typeOrgane = "ASSEMBLEE" AND mp.dateFin IS NULL AND mp.preseance = 50
        ORDER BY d.nameLast
        ');
      } else {
        $query = $this->db->query('
          SELECT A.*, civ, d.nameFirst, d.nameLast, d.nameUrl, dpt.slug AS dpt_slug, dpt.departement_nom, dpt.departement_code
          FROM
          (
          SELECT mpId, dateFin, codeQualite, libQualiteSex
          FROM mandat_groupe
          WHERE organeRef = "'.$groupeId.'" AND nominPrincipale = 1 AND legislature = 15  AND preseance IN (24)
          ) A
          LEFT JOIN deputes d ON d.mpId = A.mpId
          LEFT JOIN mandat_principal mp ON mp.mpId = A.mpId
          LEFT JOIN departement dpt ON mp.electionDepartementNumero = dpt.departement_code
          WHERE mp.legislature = 15 AND mp.typeOrgane = "ASSEMBLEE" AND mp.dateFin IS NULL AND mp.preseance = 50
          ORDER BY d.nameLast
        ');
      }

      return $query->result_array();
    }

    public function get_effectif_inactif($groupe_uid){
      $query = $this->db->query('
      SELECT COUNT(A.mpId) AS effectif
      FROM
      (
      SELECT mg.mpId
      FROM mandat_groupe mg
      LEFT JOIN organes o ON mg.organeRef = o.uid
      WHERE mg.organeRef = "'.$groupe_uid.'" AND mg.dateFin = o.dateFin
      GROUP BY mg.mpId
      ) A
      ');

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
      $query = $this->db->query('
        SELECT *,
          ROUND(cohesion, 2) AS cohesion,
          ROUND(participation * 100) AS participation,
          ROUND(majoriteAccord * 100) AS majorite
        FROM class_groups
        WHERE organeRef = "'.$groupe_uid.'"
      ');

      return $query->row_array();
    }

    public function get_stats_avg(){
      $query = $this->db->query('
        SELECT
          ROUND(AVG(cohesion), 2) AS cohesion,
          ROUND(AVG(participation) * 100) AS participation,
          ROUND(AVG(majoriteAccord) * 100) AS majorite
        FROM class_groups
      ');

      return $query->row_array();
    }


    public function get_stats_proximite($groupe_uid){
      $query = $this->db->query('
      SELECT t1.prox_group, ROUND(t1.score * 100) AS score, o.libelle, o.libelleAbrege, o.libelleAbrev, o.positionPolitique
      FROM class_groups_proximite t1
      LEFT JOIN organes o ON o.uid = t1.prox_group
      WHERE t1.organeRef = "'.$groupe_uid.'" AND t1.prox_group != "'.$groupe_uid.'" AND o.dateFin IS NULL AND t1.score IS NOT NULL AND o.libelleAbrev != "NI" AND votesN > 20
      ORDER BY t1.score DESC
      ');
      return $query->result_array();
    }

    public function get_stats_proximite_all($groupe_uid){
      $query = $this->db->query('
      SELECT t1.prox_group, ROUND(t1.score * 100) AS accord, t1.votesN,  o.libelle, o.libelleAbrege, o.libelleAbrev, o.positionPolitique, o.dateFin,
		IF(o.dateFin IS NULL, 0, 1) AS ended
      FROM class_groups_proximite t1
      LEFT JOIN organes o ON o.uid = t1.prox_group
      WHERE t1.organeRef = "'.$groupe_uid.'" AND t1.prox_group != "'.$groupe_uid.'" AND t1.score IS NOT NULL
      ORDER BY t1.score DESC
      ');

      return $query->result_array();
    }

    public function get_groupe_color($x){
      switch ($x['libelleAbrev']) {
        case 'SOC':
          $couleur = "#e30040";
          break;

        case 'UDI_I':
          $couleur = "#5c5e8e";
          break;

        default:
          $couleur = $x['couleurAssociee'];
          break;
      }
      return $couleur;
    }

  }
?>
