<?php
  class Parties_model extends CI_Model{
    public function __construct() {
      $this->load->database();
    }

    public function get_parties_active(){
      $not_included = array(
        'Non rattaché(s)',
        'Non déclaré(s)'
      );

      $this->db->select('*, CASE WHEN effectif > 1 THEN CONCAT(effectif, " députés rattachés") ELSE "1 député rattaché" END AS effectifSentence');
      $this->db->where('effectif IS NOT NULL');
      $this->db->where_not_in('libelle', $not_included);
      $query = $this->db->get('parties');

      return $query->result_array();
    }

    public function get_parties_other(){
      $this->db->where('effectif IS NULL');
      $this->db->order_by('effectifTotal', 'DESC');
      $query = $this->db->get('parties');

      return $query->result_array();
    }

    public function get_party_color($x){
      switch ($x['libelleAbrev']) {
        case 'LAREM':
          $couleur = "#2ABAFF";
          break;
        case 'REP':
          $couleur = "#00407D";
          break;
        case 'MODEM':
          $couleur = "#EF5222";
          break;
        case 'PS':
          $couleur = "#E8004B";
          break;
        case 'FI':
          $couleur = "#C94324";
          break;
        case 'PCF':
          $couleur = "#E50027";
          break;
        case 'EELV':
          $couleur = "#7AB41D ";
          break;
        case 'RPS':
          $couleur = "#d5443f";
          break;
        case 'RN':
          $couleur = "#144478";
          break;
        case 'PRG':
          $couleur = "#F1C417";
          break;
        case 'PPM':
          $couleur = "#E91E26";
          break;
        case 'DEBOU':
          $couleur = "#0082C4";
          break;
        case 'CAL':
          $couleur = "#FDB813";
          break;
        case 'CAP':
          $couleur = "#3371A3";
          break;
        case 'TPH':
          $couleur = "#E53420";
          break;
        case 'THN':
          $couleur = "#76C6F0";
          break;
        case 'UDRL':
          $couleur = "#654590";
          break;
        default:
          $couleur = 'grey';
          break;
      }
      return $couleur;
    }

    public function get_party_individual($party){
      $query = $this->db->query('
        SELECT *,
        CASE
          WHEN effectif = 1 THEN CONCAT(effectif, " député rattaché")
          WHEN effectif > 1 THEN CONCAT(effectif, " députés rattachés")
          ELSE "Aucun député rattaché" END AS effectifSentence
        FROM parties
        WHERE libelleAbrev = "'.$party.'"
      ');

      return $query->row_array();
    }

    public function get_mps_active($organeRef){
      $this->db->select('da.nameFirst, da.nameLast, da.couleurAssociee, da.mpId, da.dptSlug, da.nameUrl, da.circo AS electionCirco, da.libelle');
      $this->db->select('CASE WHEN circo = 1 THEN CONCAT("re") WHEN circo = 2 THEN CONCAT("de") ELSE CONCAT("e") END AS electionCircoAbbrev');
      $this->db->from('deputes_actifs da');
      $this->db->join('mandat_secondaire ms', 'da.mpId = ms.mpId', 'left');
      $this->db->where('ms.dateFin IS NULL');
      $this->db->where_in('ms.typeOrgane', 'PARPOL');
      $this->db->where_in('ms.organeRef', $organeRef);
      $this->db->order_by('da.nameLast', 'ASC');
      $query = $this->db->get();

      return $query->result_array();
    }

  }
?>
