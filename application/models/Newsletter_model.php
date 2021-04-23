 <?php
  class Newsletter_model extends CI_Model{
    public function __construct(){
      $this->load->database();
      $this->load->helper('email_helper');
      $this->load->library('parser');
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
        "email" => $email
      );
      sendMail($email, 'Bienvenue à la newsletter', NULL, TRUE, $templateId, $variables);

      // Inscription Mailjet contact list
      $list = 25834;
      sendContactList($email, $list);

      // Inscription MySQL
      return $this->db->insert('newsletter', $data);
    }

    public function get_by_email($email){
      return $this->db->where('email', urldecode($email))->limit(1)->get('newsletter')->row_array();
    }

    public function delete_newsletter($email){
      $this->db->where('email', urldecode($email));
      return $this->db->delete('newsletter');
    }

    public function update_list($email, $data, $list){
      $this->db->set($list['nameSQL'], $data[$list['nameSQL']]);
      $this->db->where('email', urldecode($email));
      $this->db->update('newsletter');
    }
  }
?>
