<?php

class MY_Model extends CI_Model
{
    public $STATUS_ON = 1;
    public $LIMIT = 20;
    public $TABLE_NAME;

    public function set_table($table_name)
    {
        $this->TABLE_NAME = $table_name;
    }

    public function add($data)
    {
        $this->db->insert($this->TABLE_NAME, $data);

        return $this->db->insert_id();
    }

    public function get_by_condition($condition = null, $limit_value = null, $limit_offset = null,
    $order_column = null, $order = 'desc', $like = array(), $where_in = array())
    {
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

        $query = $this->db->get($this->TABLE_NAME);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
    }

    public function get_one_by_condition($condition)
    {
        $res = $this->get_by_condition($condition);
        if ($res) {
            return $res[0];
        }

        return;
    }

    public function get_by_condition_array($condition = null, $limit_value = null, $limit_offset = null,
    $order_column = null, $order = 'desc')
    {
        if ($condition) {
            $this->db->where($condition);
        }

        if ($limit_value !== null && $limit_offset !== null) {
            $this->db->limit($limit_offset, $limit_value);
        }

        if ($order_column && $order) {
            $this->db->order_by($order_column, $order);
        }

        $query = $this->db->get($this->TABLE_NAME);
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return;
        }
    }

    public function get_one_by_condition_array($condition)
    {
        $res = $this->get_by_condition_array($condition);
        if ($res) {
            return $res[0];
        }

        return;
    }

    public function update($data, $where)
    {
        $this->db->where($where);
        $this->db->set($data);

        return $this->db->update($this->TABLE_NAME);
    }

    public function get_info_by_condition($condition = null,$select =null ,$limit_value = null, $limit_offset = null,
    $order_column = null, $order = 'desc', $like = array(), $where_in = array()){
        if($select){
            $this->db->select($select);
        }
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

        $query = $this->db->get($this->TABLE_NAME);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
    }



}
