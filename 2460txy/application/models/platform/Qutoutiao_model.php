<?php

class Qutoutiao_model extends CI_Model
{
    public $platform = 'qutoutiao';
    private $host = 'https://newidea4-gamecenter-backend.1sapp.com';
    private $userInfoUrl = '/x/open/user/ticket';
    private $queryUrl = '/x/pay/union/order/query';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $platform = $this->input->get('platform');
        $ticket = $this->input->get('ticket');

        $app_id = $this->Game_model->get_key($game_id, 'appId');
        $app_key = $this->Game_model->get_key($game_id, 'appKey');

        // $this->load->model('Qttgame_model', $app_id, $app_key);
        $content = $this->getUserInfo($ticket, $platform, $app_id, $app_key);
        if ($content) {
            // $servers = json_decode($content);
            $user_id = $content['data']['open_id'];
        }

        if (!$user_id) {
            return false;
        }

        $this->session->set_userdata('uid', $user_id);

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
                log_message('error', $this->platform . " Login error user create $content");

                return false;
            }
            $user['user_id'] = $user_id;
        }

        $this->cache->save($user['user_id'] . '_token', md5($user['user_id'] . $user['platform'] . time()), 86400);

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
        $access_token = md5(substr(time(), 0, 8) . 'aoyouxi');

        $condition = array('user_id' => $openId);
        $user = $this->User_model->get_one_by_condition($condition);
        if (!$user) {
            echo 'error';

            return;
        }

        $user_id = $user->p_uid;
        //定义统计请求的地址：
        // $url = "http://h5.xileyougame.com/tongji/tongji_game_login/{$user_id}/{$game_id}/{$access_token}";
        // $this->Curl_model->curl_get($url);

        //如果这块功能实现完成， 需要把 allugame.com 的 controllers/game.php 的第153行注释掉，这块是现在使用的游戏登录统计。

        $openKey = $this->cache->get($openId . '_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId . $noice . $game->app_key);
        $game_url = $game->game_login_url;
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "allu login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        log_message('debug', $this->platform . '  order: ' . json_encode($_POST));
        $app_id = $this->input->get_post('app_id');
        $open_id = $this->input->get_post('open_id');
        $trade_no = $this->input->get_post('trade_no');
        $money = $this->input->get_post('total_fee');
        $time = $this->input->get_post('time');
        $sign = $this->input->get_post('sign');
        $ext = json_decode($this->input->get_post('ext'));
        $ext = $ext->ext;

        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        log_message('debug', $this->platform.' my money '.$money.' '.$game_order->money);

        $key = $this->Game_model->get_key($game_id, 'appkey');
        // $my_sign = md5("ext=$ext" . "money=$money" . "orderId=$orderId" . "time=$time" . "userId=$userId" . "$key");

        if (intval($money)!= $game_order->money) {
            return false;
        }

        return $ext;
    }

    public function notify_ok()
    {
        echo '{"message":"ok"}';
    }

    public function notify_error()
    {
        echo '{"message":"fail"}';
    }
    public function sign_order($game_id = '')
    {
        $this->load->model('Game_model');
        $app_id = $this->Game_model->get_key($game_id, 'appId');
        $app_key = $this->Game_model->get_key($game_id, 'appKey');

        $gameInfo = $this->Game_model->get_by_game_id($game_id);
        // $game_login_url = $gameInfo->game_login_url;
        // 充值回调地址
        $game_login_url = "http://" . $_SERVER['HTTP_HOST'] . "/index.php/api/notify/$gameInfo->platform/$gameInfo->game_id";
        $platform = $gameInfo->platform;

        $open_id = $this->session->userdata('uid');

        $data = array(
            'appId' => $app_id,
            'openId' => $open_id,
            'platform' => $platform,
            'notifyUrl' => $game_login_url,
        );

        // echo $data;
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
    public function focus($game_id = '')
    {
        $gameid = $this->Game_model->get_key($game_id, 'gameid');
        return $gameid;
    }
    private function getUserInfo($ticket, $platform, $app_id, $app_key, $timeout = 1)
    {
        $params = array(
            'app_id' => $app_id,
            'platform' => $platform,
            'ticket' => $ticket,
            'time' => time(),
            'app_key' => $app_key,
        );
        $sign = $this->getSign($params,$app_key);
        $params['sign'] = $sign;
        $url = $this->host . $this->userInfoUrl . '?' . $this->getUrlParams($params);

        list($errno, $errmsg, $response) = $this->httpGet($url, $timeout);
        if ($errno != 0) {
            return array(
                'code' => $errno,
                'message' => $errmsg,
                'showErr' => 0,
                'currentTime' => $params['time'],
                'data' => array(),
            );
        }
        return json_decode($response, true);
    }
    private function getSign($params,$app_key)
    {
        if (!isset($params['time']))
        {
            $params['time'] = time();
        }
        $params['app_key'] = $app_key;
        ksort($params, SORT_NATURAL);
        $sign = '';
        foreach ($params as $k => $v) {
            $sign .= $k . $v;
        }
        unset($params['app_key']);
        return md5($sign);
    }
    private function getUrlParams($params)
    {
        $_data = array();
        foreach ($params as $k => $v) {
            $_data[] = $k . '=' . rawurlencode($v);
        }
        return implode('&', $_data);
    }
    private function httpGet($url, $timeout = 1)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
        if (defined('CURLOPT_CONNECTTIMEOUT_MS')) {
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 1000);
        } else {
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
        }
        if (defined('CURLOPT_TIMEOUT_MS')) {
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, $timeout * 1000);
        } else {
            curl_setopt($curl, CURLOPT_TIMEOUT, ceil($timeout));
        }
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $errno = 0;
        $errmsg = '';
        $response = curl_exec($curl);

        if ($response === false) {
            $errno = curl_errno($curl);
            $errmsg = curl_error($curl);
        }
        @curl_close($curl);
        return array($errno, $errmsg, $response);
    }

}
