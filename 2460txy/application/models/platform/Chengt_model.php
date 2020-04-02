<?php

class Chengt_model extends CI_Model
{
    public $platform = 'chengt';
    public $appkey;

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $userId = $this->input->get('userId');
        $this->session->set_userdata('userId', $userId);
        $appId = $this->input->get('appId');
        $sign = $this->input->get('sign');
        if (!$userId) {
            return false;
        }

        $condition = array(
                'p_uid' => $userId,
                'platform' => $this->platform,
            );

        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                    'platform' => $this->platform,
                    'p_uid' => $userId,
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
        // $game_url = 'https://lcby.gz.1251208707.clb.myqcloud.com/juhe/login/juhe';
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        // $url = "http://h5sdk.zytxgame.com/index.php/enter/play/chengt/1056?userId="+$this->session->userdata('userId');
        // $url = "http://h5sdk.zytxgame.com/index.php/enter/play/chengt/1056";
        $this->session->set_userdata('url', $url);
        $url_data = array(
            'url'=>$url,
            'game_id'=>$game_id,
            'passId'=>$this->platform,
            'game_father_id'=>$game->game_father_id,
        );
        $this->load->view('chengt_shell.php', $url_data);
        // header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        // $orderId = $this->input->post('orderId');
        $orderId = $this->input->post('orderId');
        $money = $this->input->post('money');

        if (!$money || !$orderId) {
            return false;
        }

        if ($orderStatus == 1) {
            if ($order_id && $money) {
                $condition = array('u_order_id' => $orderId);
                $this->load->model('Game_order_model');

                $game_order = $this->Game_order_model->get_one_by_condition($condition);
                if ($game_order) {
                    if (intval($money) == $game_order->money) {
                        return $orderId;
                    } else {
                        log_message('debug', $this->platform.' money errory '.$game_order->money." != $money");
                    }
                }
            }
        }

        return false;
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }

    public function sign_order($game_id = '')
    {
        $goodsName = $this->input->get('goodsName');
        $amount = $this->input->get('amount');
        $roleName = $this->input->get('roleName');
        $callBackInfo = $this->input->get('callBackInfo');
        if (!$goodsName || !$roleName || !$callBackInfo) {
            return false;
        }
        $this->key = $this->Game_model->get_key($game_id, 'key');
        $sign = md5($goodsName.$amount.$roleName.$callBackInfo.$this->key);

        return $sign;
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }
    public function focus($game_id='')
    {
        $data = array(
            'url'=>$this->session->userdata('url'),
        );
        return $data;
    }
}
