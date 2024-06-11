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
      $this->db->order_by('libelle', 'ASC');
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
      $this->db->select('*');
      $this->db->select('
        CASE
        WHEN effectif = 1 THEN CONCAT(effectif, " député rattaché")
        WHEN effectif > 1 THEN CONCAT(effectif, " députés rattachés")
        ELSE "Aucun député rattaché" END AS effectifSentence'
      );
      $query = $this->db->get_where('parties', array('libelleAbrev' => $party), 1);

      return $query->row_array();
    }

    public function get_mps_active($organeRef){
      $this->db->select('ms.organeRef, da.nameFirst, da.nameLast, da.couleurAssociee, da.mpId, da.dptSlug, da.nameUrl, da.circo AS electionCirco, da.libelle AS libelle, da.libelleAbrev AS libelleAbrev, da.img');
      $this->db->select('CONCAT(da.departementNom, " (", da.departementCode, ")") AS cardCenter');
      $this->db->join('deputes_all da', 'ms.mpId = da.mpId', 'left');
      $this->db->where('ms.organeRef', $organeRef);
      $this->db->where('da.legislature', legislature_current());
      $this->db->where('ms.dateFin IS NULL');
      if (dissolution() === false) {
        $this->db->where('da.dateFin IS NULL');
      }
      $this->db->order_by('da.nameLast ASC, da.nameFirst ASC');
      return $this->db->get('mandat_secondaire ms')->result_array();
    }

    public function get_organization_schema($party, $members){
      $schema = [
        "@context" => "http://schema.org",
        "@type" => "Organization",
        "name" => $party['libelle'] . " (" . $party['libelleAbrev'] . ")",
        "url" => base_url() . "partis-politiques/" . mb_strtolower($party['libelleAbrev']),
      ];

      foreach ($members as $member) {
        $schema['member'][] = [
          "@type" => "Person",
          "name" => $member['nameFirst']. ' '.$member['nameLast'],
          "familyName" => $member['nameLast'],
          "givenName" => $member['nameFirst'],
          "url" => base_url().'deputes/'.$member['dptSlug'].'/depute_'.$member['nameUrl'],
          "jobTitle" => "Député français et membre apparenté au parti " . $party['libelle']
        ];
      }

      if ($party['effectif']) {
        $schema['numberOfEmployees'] = $party['effectif'];
      }

      if ($party['dateFin'] == NULL) {
        $schema["description"] = $party['libelle'] . " (" . $party['libelleAbrev'] . ") est un parti politique français.";
      } else {
        $schema["description"] = $party['libelle'] . " (" . $party['libelleAbrev'] . ") est un ancien parti politique français.";
      }

      if ($party['img']) {
        $schema["logo"] = asset_url() . "imgs/partis/" . mb_strtolower($party['libelleAbrev']) . ".png";
      }

      return $schema;
    }

  }
?>
