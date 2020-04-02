<?php

class Gamedog_model extends CI_Model
{
    public $app_key = '';
    public $platform = 'gamedog';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        // $s_uid = $_GET['uid'];
        $appid = $this->Game_model->get_key($game_id, 'APPID');
        $this->app_key = $this->Game_model->get_key($game_id, 'appkey');
        $token = $this->input->get('token');
        $this->session->set_userdata('token', $token);
        $timestamp = $this->input->get('timestamp');
        // $sign =$this->input->get('sign');
        // echo $sign;
        $channel = $this->input->get('channel');
        $this->session->set_userdata('channel', $channel);
        $data = array(
                "token" => $token,
                "timestamp" => $timestamp,
                "appid" => $appid,
                    );
        $sign = $this->sign($data, $this->app_key);
        if (!$appid || !$this->app_key || !$token|| !$timestamp|| !$sign) {
            return false;
        }
        $url = "http://sdk.h5.gamedog.cn/api/userinfo?appid=$appid&timestamp=$timestamp&token=$token&sign=$sign";
        $content = $this->Curl_model->curl_get($url);
        if (!$content) {
            log_message('error', $this->platform." Login empty content $url");
            return false;
        }
        $response = json_decode($content);
        if ($response && isset($response->error_code)) {
            log_message('error', $this->platform." Login auth error content $content");
            return false;
        }

        /*if ($response->code!=0) {
            echo 3;
            return false;
        }*/
        $p_uid = $response->data->openid;
        $condition = array(
                            'p_uid' => $p_uid,
                            'platform' => $this->platform,
                        );
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                            'platform' => $this->platform,
                            'p_uid' => $p_uid,
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
        //定义统计请求的地址：
        // $url = "http://h5.xileyougame.com/tongji/tongji_game_login/{$user_id}/{$game_id}/{$access_token}";
        // $this->Curl_model->curl_get($url);

        //如果这块功能实现完成， 需要把 allugame.com 的 controllers/game.php 的第153行注释掉，这块是现在使用的游戏登录统计。

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
        // $order_id = $this->input->get('order_id');
        // $amount = $this->input->get('amount');
        // $order_uid = $this->input->get('order_uid');
        // $source = $this->input->get('source');
        // $actual_amount = $this->input->get('actual_amount');
        $order_id = $this->input->get('param');
        // $signature = $this->input->get('signature');
        if (!$order_id) {
            return;
        }

        $money = $this->input->get_post('fee');

        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money) == $game_order->money) {
                    return $order_id;
                } else {
                    log_message('debug', $this->platform.' get_order_id money errory '.$game_order->money." != $money");
                }
            } else {
                log_message('debug', $this->platform.' get_order_id error order not found by '.$order_id);
            }
        } else {
            log_message('debug', $this->platform.' get_order_id error order_id or money null');
        }

        return $order_id;
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }
    public function sign_order($game_id)
    {
        $appid = $this->Game_model->get_key($game_id, 'APPID');
        $channel = $this->input->get('channel');
        $fee = $this->input->get('fee');
        $orderno = $this->input->get('orderno');
        $subject = $this->input->get('subject');
        $timestamp = $this->input->get('timestamp');
        $token = $this->input->get('token');
        $timestamp = time();
        $sign_data = array(
            "appid" => $appid,
            "channel" => $channel,
            "fee" => $fee,
            "orderno" => $orderno,
            "subject" => $subject,
            "timestamp" => $timestamp,
            "token" => $token,
            );
        $this->app_key = $this->Game_model->get_key($game_id, 'appkey');
        $sign = $this->sign($sign_data, $this->app_key);
        $data=array(
            'appid'=>$appid,
            'channel'=>$channel,
            'token'=>$token,
            'sign'=>$sign,
            'time'=>$timestamp,
        );
        return $data;
    }

    public function init($game_id)
    {
        $data = array('usertype' => $this->session->userdata('usertype'));

        return $data;
    }
    public function focus($game_id)
    {
        $words = $this->input->get('words');
        if (!$words) {
            return false;
        }

        $url = 'https://api.weibo.com/2/statuses/update.json';
        $game_url = $this->Game_model->get_key($game_id, 'game_url');
        $game = $this->Game_model->get_by_game_id($game_id);
        $data = array(

                'access_token' => $this->session->userdata('token'),
                'status' => $words.' 《'.$game->game_name.' 》'.$game_url,
            );
        $content = $this->Curl_model->curl_post($url, $data);
        if (!$content) {
            log_message('error', $this->platform." Login empty content $url");

            return false;
        }
        $response = json_decode($content);
        if ($response && isset($response->error_code)) {
            log_message('error', $this->platform." Login auth error content $content");

            return false;
        }

        return "success";
    }
    public function login_collect($data)
    {
    }
    public function sign($data, $secret)
    {
        ksort($data);
        foreach ($data as $k => $v) {
            $tmp[] = $k . '=' . $v;
        }
        $str = implode('&', $tmp) . $secret;
        return sha1($str);
    }
}
