<?php

class Qitianledifive_model extends CI_Model
{
    public $platform = 'qitianledifive';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $uid = $this->input->get('uid');
        $status = $this->input->get('status');
        if($status != 1){
            return;
        }
        $channer_id = $this->input->get('channel_id');
        $CPgame_id = $this->input->get('game_id');
        $user_id = $uid;

        $this->session->set_userdata('uid',$uid);
        $this->session->set_userdata('cpgameid',$CPgame_id);

        if (!$user_id) {
            return false;
        }
        $condition = array(
                            'p_uid' => $user_id,
                            'platform' => $this->platform,
                        );
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                            'platform' => $this->platform,
                            'p_uid' => $user_id,
                            'create_date' => time(),
                        );
            $user_id = $this->User_model->add($user);
            if (!$user_id) {
                log_message('error', $this->platform." Login error user create $content");

                return false;
            }
            $user['user_id'] = $user_id;
        }

        $this->cache->save($user['user_id'].'_token', md5($user['user_id'].$user['platform'].time()), 86400);

        return $user['user_id'];
    }

    public function game($platform, $game_id)
    {
        $openId = $this->input->get('openId');
        // $frameHeight = $this->input->get('frameHeight');
        // $frameWidth = $this->input->get('frameWidth');

        $servers = array();

        $server1 = array(
                    'server_id' => 8003,
                    'server_name' => '1服',
                );
        $servers[] = $server1;

        $game = $this->Game_model->get_by_game_id($game_id);
        if (!$game) {
            $this->Output_model->json_print(-2, '');

            return;
        }

        $game_name = $game->game_name;

        $url = "/index.php/enter/trun_to_game/$platform/$game_id?openId=$openId";

        $data = array(
                      'servers' => $servers,
                    'game_name' => $game_name,
                    'url' => $url,
                );

        $this->load->view('game_login/allu_lc_login', $data);
    }

    public function trun_to_game($game_id)
    {
        $game = $this->Game_model->get_by_game_id($game_id);
        if (!$game) {
            $this->Output_model->json_print(-2, '');

            return;
        }

        $this->cache->get('user_id');

        $openId = $this->input->get('openId');
        if (!$openId) {
            echo 'error';

            return;
        }

        //执行统计请求
        $access_token = md5(substr(time(), 0, 8).'aoyouxi');

        $condition = array('user_id' => $openId);
        $user = $this->User_model->get_one_by_condition($condition);
        if (!$user) {
            echo 'error';

            return;
        }

        $user_id = $user->p_uid;
        $openKey = $this->cache->get($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "allu login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        if ($_POST) {
            log_message('debug', $this->platform.' my money '.json_encode($_POST));
        } elseif ($_GET) {
            log_message('debug', $this->platform.' my money '.json_encode($_GET));
        }

        $money = $this->input->get_post('cash');
        // $ext = json_decode($this->input->get_post('extra'));
        // $custom_info = $ext->order;
        // echo $custom_info;
        $custom_info = $this->input->get_post('extra');
        $condition = array('u_order_id' => $custom_info);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        log_message('debug', $this->platform.' my money '.$money.' '.$game_order->money);
        if ($money*100==$game_order->money) {
            return $custom_info;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo '1';
    }

    public function notify_error()
    {
        echo '0';
        ;
    }
    public function sign_order($game_id = '')
    {
        $order = $this->input->get('order_id');
        $uid = $this->session->userdata('uid');
        $CPgame_id = $this->session->userdata('cpgameid');
        $j = array(
            'order' => $order,
            'CPgame_id' => $CPgame_id,
        );
        $data = array(
            'uid' => $uid,
            // 'ext' => json_encode($j),
            'ext' => $order,
            'CPgame_id' => $CPgame_id,
        );
        return $data;
    }

    public function init($game_id)
    {
        $data = array('usertype' => $this->session->userdata('usertype'));

        return $data;
    }
    public function create_role_collect($data)
    {
    }

    public function login_collect($data)
    {
    }
    public function focus($game_id='')
    {
        $gameid= $this->Game_model->get_key($game_id, 'gameid');
        return $gameid;
    }
}
