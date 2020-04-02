<?php

class Whale_model extends CI_Model
{
    public $app_key = '';
    public $platform = 'whale';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $ticket = $this->input->get('ticket');
        $timestamp =$this->input->get('timestamp');
        $key = $this->input->get('game_key');
        $game_key=$this->Game_model->get_key($game_id, 'gamekey');
        // $s_uid = $_GET['uid'];
        $game_secret=$this->Game_model->get_key($game_id, 'secret');
        $this->load->model('Common_model');
        $sign_str = 'game_key'.$game_key.'login_ticket'.$ticket.'timestamp'.$timestamp.$game_secret;
        $sign_a = sha1($sign_str, false);
        $sign=strtoupper($sign_a);
        $signature =$this->input->get('signature');
        $this->session->set_userdata('ticket', $ticket);
        $this->session->set_userdata('game_key', $key);
        log_message('debug', $this->platform.('sign is:'.$sign.' sign_str is:'.$sign_str.'sign_a is:'.$sign_a));
        $params = array(
               'login_ticket' => $ticket,
               'timestamp' =>$timestamp,
               'game_key'=>$game_key,
               'signature'=>$sign,
        );


        if (!$ticket || !$timestamp || !$game_key || !$sign) {
            return false;
        }
        $url = 'http://joyh5.com/jssdk/user/ticket/getuserinfo';
        $contents = $this->Curl_model->curl_post($url, $params);
        if (!$contents) {
            log_message('error', $this->platform." Login empty content $url");
            return false;
        }
        $response = json_decode($contents);
        if ($response && isset($response->error_code)) {
            log_message('error', $this->platform." Login auth error content $contents");

            return false;
        }
        log_message('debug', $this->platform.' content is :'.$contents);
        $p_uid = $response->content->user_uuid;
        // $this->session->set_userdata('uuid', $p_uid);
        // $this->cache->save('uuid', $p_uid, 60*60*24*7);
        // log_message('debug', $this->platform.' uuid is :'.$p_uid);
        $head_url =$response->content->head_url;
        $this->session->set_userdata('cp_uid',$p_uid);
        $condition = array(
                            'p_uid' => $p_uid,
                            'platform' => $this->platform,
                        );
        $user = $this->User_model->get_one_by_condition_array($condition);

        if (!$user) {
            if (!isset($p_uid)) {
                return;
            }
            $user = array(
                            'platform' => $this->platform,
                            'p_uid' => $p_uid,
                            'avatar' => $head_url,
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
        $order_id = $this->input->get_post('cp_order_id');
        $money = $this->input->get_post('order_amount');
        log_message('debug', $this->platform.' orderid is:'.$order_id.' and money is : '.$money);
        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money* 100) == $game_order->money) {
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
        $openid = $this->input->get('openid');
        $game_key = $this->Game_model->get_key($game_id, 'gamekey');
        log_message('debug', $this->platform.' gamekey is : '.$game_key);
        // $uuid = $this->session->userdata('uuid');
        // if (!$uuid) {
        //     $uuid = $this->cache->get('uuid');
        // }
        $condition = array(
            'user_id'=>$openid
        );
        $response = $this->User_model->get_one_by_condition($condition);
        // echo $this->db->last_query();
        // echo json_encode($response);
        $uuid=$response->p_uid;
        // echo $uuid;
        //
        $order_amount = $this->input->get('order_amount');
        $cp_order_id = $this->input->get('cp_order_id');
        $product_name = $this->input->get('product_name');
        $notify_url = $this->input->get('notify_url');
        $timestamp = $this->input->get('time');

        $game_secret=$this->Game_model->get_key($game_id, 'secret');
        $ticket = $this->session->userdata('ticket');

        $sign_str = 'cp_order_id'.$cp_order_id.'game_key'.$game_key.'notify_url'.$notify_url.'order_amount'.$order_amount
        .'product_name'.$product_name.'timestamp'.$timestamp.'user_uuid'.$uuid.$game_secret;
        log_message('debug', $this->platform.' order sign_str is :'.$sign_str);
        $sign_a = sha1($sign_str, false);
        $sign=strtoupper($sign_a);
        log_message('debug', $this->platform.' order sign is :'.$sign);

        $data = array(
            'game_key'=>$game_key,
            'uuid'=>$uuid,
            'sign'=>$sign,
        );
        return $data;
    }

    public function init($game_id)
    {
        $gamekey = $this->Game_model->get_key($game_id, 'gamekey');
        // $data = array('usertype' => $this->session->userdata('usertype'));

        return $data;
    }
    public function focus($game_id)
    {
        $gamekey = $this->Game_model->get_key($game_id, 'gamekey');
        $p_uid = $this->session->userdata('cp_uid');
        $data = array(
            'gamekey'=>$gamekey,
            'cp_uid'=>$p_uid,
        );
        return $data;
    }
    public function login_collect($data)
    {
    }
}
