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
      //$template = $this->parser->parse('emails/newsletter', array('email' => $email));
      $templateId = 2826349; /* Welcome */
      $variables = array(
        "email" => $email
      );
      sendMail($email, 'Bienvenue à la newsletter', NULL, TRUE, $templateId, $variables);

      return $this->db->insert('newsletter', $data);
    }

    public function get_by_email($email){
      return $this->db->where('email', urldecode($email))->limit(1)->get('newsletter')->row_array();
    }

    public function delete_newsletter($email){
      $this->db->where('email', urldecode($email));
      return $this->db->delete('newsletter');
    }
  }
?>
