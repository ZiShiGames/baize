<?php

class Month_data_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('month_data');
    }
    public function get_order_info($select = null, $condition = null, $limit_value = null, $limit_offset = null,
    $order_column = null, $order = 'desc', $like = array(), $where_in = array())
    {
        $this->db->select($select);
        // $this->db->group_by('platform');
        if ($condition) {
            $this->db->where($condition);
        }
        if (!empty($where_in)) {
            $this->db->where_in($where_in['name'], $where_in['values']);
        }

        if (!empty($like)) {
            $this->db->like($like['key'], $like['match']);
        }

        if ($limit_value !== null && $limit_offset !== null) {
            $this->db->limit($limit_offset, $limit_value);
        }

        if ($order_column && $order) {
            $this->db->order_by($order_column, $order);
        }

        $query = $this->db->get('fake');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
        echo $this->db->last_quert();
    }
}
