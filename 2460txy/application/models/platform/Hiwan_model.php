<?php

class Hiwan_model extends CI_Model
{
    public $platform = 'hiwan';
    public $key = '55ewx6hhzeoxmftgvfcnll5xw4rihyi5';

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $userAccount = $this->input->get('userAccount');
        $loginTime = $this->input->get('loginTime');
        $sign = $this->input->get('sign');
        if (!$sign || !$loginTime || !$userAccount) {
            return false;
        }
        $this->key = $this->Game_model->get_key($game_id, 'key');

        $v_sign = md5($userAccount.'&'.$loginTime.'&'.$this->key);
        if ($v_sign != $sign) {
            return false;
        }

        $nickname = $this->input->get('nickname');
        $sex = $this->input->get('sex');
        $headImgUrl = $this->input->get('headImgUrl');

        $condition = array(
                'p_uid' => $userAccount,
                'platform' => $this->platform,
            );

        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                    'platform' => $this->platform,
                    'p_uid' => $userAccount,
                    'nickname' => $nickname,
                    'avatar' => $headImgUrl,
                    'sex' => $sex,
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
        $orderStatus = $this->input->get_post('orderStatus');
        $money = $this->input->get_post('amount');
        $order_id = $this->input->get_post('callBackInfo');
        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if ($game_order->money!=$money) {
            return;
        }
        $orderId = $this->input->post('orderId');
        $my_orderStatus = $this->input->post('orderStatus');
        $amount = $this->input->post('amount');
        $callBackInfo = $this->input->post('callBackInfo');
        $ser_sign = $this->input->post('sign');
        $game_id = $game_order->game_id;
        $this->key = $this->Game_model->get_key($game_id, 'key');
        $my_sign = MD5("orderId=$orderId&orderStatus=$my_orderStatus&amount=$amount&callBackInfo=$callBackInfo&appkey=$this->key");

        if ($my_sign==$ser_sign) {
            return $order_id;
        } else {
            return false;
        }
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
}
