<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Campaign_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create(int $user_id): bool
    {
        $data = [
            'text' => $this->input->post('message'),
            'start_date' => $this->input->post('startDate'),
            'end_date' => $this->input->post('endDate'),
            'author' => $user_id,
            'position' => $this->input->post('position'),
            'page' => $this->input->post('page'),
        ];

        return $this->db->insert('campaigns', $data);
    }

    public function get_campaigns(): array
    {
        $this->db->join('users u', 'u.id = campaigns.author', 'left');
        $this->db->select('campaigns.*, u.name AS author_name');
        $query = $this->db->get('campaigns');
        return $query->result_array();
    }

    public function get_campaign($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('campaigns');

        return $query->row_array();
    }

    public function update(int $id): bool
    {
        $data = [
            'text' => $this->input->post('message'),
            'start_date' => $this->input->post('startDate'),
            'end_date' => $this->input->post('endDate'),
            'position' => $this->input->post('position'),
            'page' => $this->input->post('page'),
        ];

        return $this->db->where('id', $id)->update('campaigns', $data);
    }

    public function delete(int $id): void
    {
        $this->db->where('id', $id);
        $this->db->delete('campaigns');
    }

    public function set_active_status(int $id, bool $is_active): bool
    {
        return $this->db->where('id', $id)->update('campaigns', ['is_active' => $is_active]);
    }
}
