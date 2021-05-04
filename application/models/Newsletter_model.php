<?php
class Newsletter_model extends CI_Model{
  public function __construct(){
    $this->load->database();
  }

  public function create_newsletter(){
    $email = $this->input->post('email');
    $exists = $this->get_by_email($email);
    if (isset($exists)){
      return false;
    }
    $data = array(
      'email' => $email,
      'general' => true,
    );
    $templateId = 2826349; /* Welcome */
    $variables = array(
      "email" => $email,
      "email_encode" => urlencode($email)
    );
    sendMail($email, 'Bienvenue à la newsletter', NULL, TRUE, $templateId, $variables);

    // Create Contact
    createContact($email);
    // Inscription Mailjet contact list
    $list = 25834;
    sendContactList($email, $list);

    // Inscription MySQL
    return $this->db->insert('newsletter', $data);
  }

  public function get_by_email($email){
    $query = $this->db->where('email', $email)->limit(1)->get('newsletter');
    return $query->row_array();
  }

  public function get_all_by_list($list){
    return $this->db->get_where('newsletter', array($list => TRUE))->result_array();
  }

  public function delete_newsletter($email){
    $this->db->where('email', ($email));
    return $this->db->delete('newsletter');
  }

  public function update_list($email, $set, $list){
    $this->db->set($list, $set);
    $this->db->where('email', ($email));
    $this->db->update('newsletter');
  }

  public function get_number_registered($list){
    return $this->db->from('newsletter')->where(array($list => 1))->count_all_results();
  }

  public function get_registered_month($list){
    $this->db->where(array($list => 1));
    $this->db->select("count(*) AS n, DATE_FORMAT(created_at, '%M %Y') as y");
    $this->db->group_by(array("YEAR(created_at)", "MONTH(created_at)"));
    $this->db->order_by('YEAR(created_at)', 'DESC');
    $this->db->order_by('MONTH(created_at)', 'DESC');
    $this->db->from('newsletter');
    $query = $this->db->get();

    return $query->result_array();
  }
}
?>
