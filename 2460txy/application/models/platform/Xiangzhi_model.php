<?php

class Xiangzhi_model extends CI_Model
{
    public $app_key = '';
    public $platform = 'xiangzhi_model';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('user_id');
        $channelExt = $this->input->get('channelExt');
        $email = $this->input->get('email');
        $game_appid = $this->input->get('game_appid');
        $new_time = $this->input->get('new_time');
        $loginplatform2cp = $this->input->get('loginplatform2cp');
        $sdklogindomain = $this->input->get('sdklogindomain');
        $sdkloginmodel = $this->input->get('sdkloginmodel');
        $sign =$this->input->get('sign');
        $this->session->set_userdata('user_id', $user_id);
        $this->session->set_userdata('channelExt', $channelExt);
        $this->session->set_userdata('email', $email);
        $this->session->set_userdata('game_appid', $game_appid);
        $this->session->set_userdata('new_time', $new_time);
        $this->session->set_userdata('loginplatform2cp', $loginplatform2cp);
        $this->session->set_userdata('sdklogindomain', $sdklogindomain);
        $this->session->set_userdata('sdkloginmodel', $sdkloginmodel);
        $this->session->set_userdata('sign', $sign);

        // if ($user_id) {
        //     $this->focus();
        // }

        // $gameid = $this->Game_model->get_key($game_id,'appid');
        // $login_key = $this->Game_model->get_key($game_id,'key');
        log_message('debug', $this->platform.' ');
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
        $orderId = $this->input->get_post('trade_no');
        $orderMoney = $this->input->get_post('amount');
        $condition = array('u_order_id' => $orderId);
        $this->load->model('Game_order_model');         //无平台预留字段 orderid与2460 不匹配
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if ($game_order) {
            if (intval($orderMoney) == $game_order->money) {
                log_message('debug', $this->platform.' ordermoney: '.$orderMoney.' game_order :'.$orderId);
                return $orderId;
            } else {
                log_message('error', $this->platform.' money not match');
                return false;
            }
        } else {
            log_message('error', $this->platform.' game_order is null');
            return false;
        }

        return false;
    }

    public function notify_ok()
    {
        echo '{"status":"success"}';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }
    public function sign_order($game_id = '')
    {
        $gameid = $this->Game_model->get_key($game_id, 'appid');
        $uid = $this->input->get('uid');
        $goodsName = $this->input->get('goodsName');
        $money = $this->input->get('money');
        $cpOrderId = $this->input->get('cpOrderId');
        $ext = $this->input->get('ext');
        $pay_key = $this->Game_model->get_key($game_id, 'key');
        $this->load->model('Common_model');
        $sign_data=array(
                'amount'=>$money,
                'channelExt'=>$ext,
                'game_appid'=>$gameid,
                'props_name'=>$goodsName,
                'trade_no'=>$cpOrderId,
                'user_id'=>$uid,

        );
        $sign_str =  $this->Common_model->sort_params($sign_data).$pay_key;

        $sign = md5($sign_str);
        log_message('debug', $this->platform.' sign str is '.$sign_str.' and sign is '.$sign);
        // $pay_key ='d8da3e03a2bc50b271e5f86579190aa5';
        // $sign_str = "cpOrderId=$cpOrderId&gameId=$gameid&goodsId=$goodsId&goodsName=$goodsName&money=$money&role=$role&server=$server&time=$time&uid=$uid&key=$pay_key";
        // echo $pay_key;  &ext=$ext

        // log_message('debug', $this->platform.' pay sign str is '.$sign_str.' sign is '.$sign);
        $data = array(
             'sign' => $sign,
             'gameid' => $gameid,
        );

        return $data;
    }

    public function init($game_id)
    {
        $data = array('usertype' => $this->session->userdata('usertype'));

        return $data;
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }
}
