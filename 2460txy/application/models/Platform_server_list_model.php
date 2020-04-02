<?php

class Platform_server_list_model extends MY_Model
{
    public $START_STATUS = 0;
    public $PAYED_STATUS = 1;
    public $NOTIFIED_STATUS = 2;

    public function __construct()
    {
        parent::__construct();
        $this->set_table('platform_server_list');
    }

    public function report()
    {
        $sql = 'select u_order_id, money,user_id, platform , create_date from game_order where create_date >=1490976000 and create_date<=1492963200 and status =2 order by user_id';
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
    }

    public function user_report($condition = null, $where_in = array())
    {
        if ($condition) {
            $this->db->where($condition);
        }
        if (!empty($where_in)) {
            $this->db->where_in($where_in['name'], $where_in['values']);
        }

        $query = $this->db->get('user');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
    }

    public function sign_report($condition = null, $where_in = array())
    {
        if ($condition) {
            $this->db->where($condition);
        }
        if (!empty($where_in)) {
            $this->db->where_in($where_in['name'], $where_in['values']);
        }

        $query = $this->db->get('sign_report');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
    }

    public function search_info()
    {
    }
}
