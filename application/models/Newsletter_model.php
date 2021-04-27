 <?php
  class Newsletter_model extends CI_Model{
    public function __construct(){
      $this->load->database();
      $this->load->helper('email_helper');
      //$this->load->library('parser');
    }

    public function create_newsletter(){
      $email = $this->input->post('email');
      if ($this->get_by_email($email)){
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
  }
?>
