<?php

class Sign_report_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('sign_report');
    }

    public function get_report($platform, $create_date, $limit_offset = null, $limit = null)
    {
        $sql = 'select * from sign_report where platform = ? and create_date >= ? and create_date <= ? group by p_uid ';
        $binds = array($platform, $create_date, $create_date + 60 * 60 * 24);
        if ($limit_offset && $limit) {
            $sql .= 'limit ?,?';
            $binds[] = $limit_offset;
            $binds[] = $limit;
        }
        $query = $this->db->query($sql, $binds);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
    }
    public function sign_sum($condition = null, $limit_value = null, $limit_offset = null,
    $order_column = null, $order = 'desc', $like = array(), $where_in = array())
    {
        $sql = 'SELECT sign_report_id,platform,user_id,p_uid,server_id,game_id,create_date,game_father_id FROM sign_report GROUP BY platform';
        // $this->db->count_all('sign_report_id');
        $this->db->select('platform,count(sign_report_id) as sign_count');
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
        $query = $this->db->get('sign_report');
        // $query = $this->db->result($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return;
        }
    }
}
