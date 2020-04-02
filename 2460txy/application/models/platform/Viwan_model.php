<?php

class Viwan_model extends CI_Model
{
    public $platform = 'viwan';
    public $appid;

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $username = $this->input->get('username');
        $logintime = $this->input->get('logintime');
        $token = $this->input->get('token');
        $this->session->set_userdata('token', $token);
        if (!$username||!$logintime||!$token) {
            log_message('error', "$this->platform login error, username: $username , logintime: $logintime , token : $token");
            return false;
        }
        $condition = array(
                'p_uid' => $username,
                'platform' => $this->platform,
            );
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                    'platform' => $this->platform,
                    'p_uid' => $username,
                    'create_date' => time(),
                );
            $user_id = $this->User_model->add($user);
            if (!$user_id) {
                log_message('error', "Login error user create $content");
                return false;
            }
            $user['user_id'] = $user_id;
        }
        // generate random token and save it to cache
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

        $openId = $this->input->get('openId');
        if (!$openId) {
            echo 'error';

            return;
        }
        $openKey = $this->cache->get($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "hiwan login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        // $orderId = $this->input->post('orderId');
        $money = $this->input->post('amount');
        $order_id = $this->input->post('remarks');
        if (!$money || !$order_id) {
            log_message('error', $this->platform.' money is '.$money.' order_id is '.$order_id);
            return false;
        }

        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money * 100) == $game_order->money) {
                    log_message('debug', $this->platform.' money is '.$money.' order_id is '.$order_id);
                    return $order_id;
                } else {
                    log_message('debug', $this->platform.' money errory '.$game_order->money." != $money");
                }
            }
        }
        log_message('debug', $this->platform.' money is '.$money.' order_id is '.$order_id);
        return false;
    }

    public function notify_ok()
    {
        echo 'success';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }

    public function sign_order($game_id)
    {
        $this->appid = $this->Game_model->get_key($game_id, 'appid');
        $appkey = $this->Game_model->get_key($game_id, 'key');
        $username = $this->input->get('username');
        $productname = $this->input->get('productname');
        $amount = $this->input->get('amount');
        $serverid = $this->input->get('serverid');
        $remarks = $this->input->get('remarks');
        $roleid = $this->input->get('roleid');
        $token_info = "username=" . urlencode($username) . "&productname=" . urlencode($productname) . "&amount=" . urlencode($amount)
        . "&roleid=" . urlencode($roleid) . "&serverid=" . urlencode($serverid) . "&appid=" . urlencode($this->appid)
        . "&remarks=" . urlencode($remarks) . "&appkey=" . $appkey;
        $token = md5($token_info);
        // $token = $this->session->userdata('token');
        $data = array(
            'token' =>$token,
            'appid'=>$this->appid,
        );
        return $data;
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }
}
