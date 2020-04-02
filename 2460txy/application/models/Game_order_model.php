<?php

class Game_order_model extends MY_Model
{
    public $START_STATUS = 0;
    public $PAYED_STATUS = 1;
    public $NOTIFIED_STATUS = 2;

    public function __construct()
    {
        parent::__construct();
        $this->set_table('game_order');
    }

    public function report()
    {
        $sql = 'select u_order_id, money,user_id, platform , create_date from game_order where create_date >=1484668800 and status =2 order by user_id';
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
    }
    public function report_sum($condition = null, $limit_value = null, $limit_offset = null,
    $order_column = null, $order = 'desc', $like = array(), $where_in = array())
    {
        $sql = 'SELECT u_order_id, money,user_id, platform  FROM game_order WHERE create_date >=1484668800 and status =2 order by user_id';
        $this->db->select_sum('money');
        $this->db->select('platform,count(user_id) as user_count');
        $this->db->group_by('platform');

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

        // $query = $this->db->get($this->TABLE_NAME);

        // $sql = 'select SUM(money), platform from game_order where create_date >=1484668800 and status =2 group by platform order by user_id';
        $query = $this->db->get('game_order');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
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

        $query = $this->db->get('game_order');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
        echo $this->db->last_quert();
    }
}
