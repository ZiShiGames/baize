<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wx_mini_report extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'redis'));
        $this->load->model('Wxpay_model');
        $this->load->model('Mini_programs_model');
        $this->load->model('Mini_user_model');
        $this->load->model('Mini_programs_model');
        $this->load->model('Mini_game_order_model');
        if (!$this->cache->redis->is_supported()) {
            echo 'no redis';
            exit;
        }
    }

    public function login()
    {
        $this->Output_model->json_print(1, 'login error');
    }
    public function enter()
    {
        $this->Output_model->json_print(1, 'login error');
    }
    public function create()
    {
        $condition = array('mini_user_id' => $_GET['user_id']);
        $user = $this->Mini_user_model->get_one_by_condition($condition);
        //创角
        $role_report_data=array(
            
            'appid'=>$user->appid,
            'user_id'=>$user->mini_user_id,
            'channel'=>$user->channel,
            'srvid'=>$_GET['srvid'],
            'nickName'=>$_GET['nickName'],
            'cproleid'=>$_GET['cproleid'],
            'level'=>$_GET['level'],
            'code'=>json_encode($_GET),
            'create_date'=>time(),
        );
        $login_id=$this->db->insert('mini_role_report',$role_report_data);
//         log_message('debug', 'mini game create ' .json_encode($_GET)); 
        $this->Output_model->json_print(1, 'login error');
    }
}
