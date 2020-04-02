<?php

class User_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('user');
    }
    public function get_by_select($select=null, $condition = null, $limit_value = null, $limit_offset = null,
    $order_column = null, $order = 'desc', $like = array(), $where_in = array(), $group_by=null)
    {
        if ($condition) {
            $this->db->where($condition);
        }
        if ($select) {
            $this->db->select($select);
        }
        if ($group_by) {
            $this->db->group_by($group_by);
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

        $query = $this->db->get($this->TABLE_NAME);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
    }
}
