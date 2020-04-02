<?php

class Heke_model extends CI_Model
{
    public $app_key = '';
    public $platform = 'heke';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $s_uid = $this->input->get('uid');
        $userName = $this->input->get('userName');
        $time = $this->input->get('time');
        $avatar = $this->input->get('avatar');
        $userSex = $this->input->get('userSex');
        $gameid = $this->Game_model->get_key($game_id, 'appid');
        $login_key = $this->Game_model->get_key($game_id, 'key');
        $signType = 'H5';
        $sign_str = "gameId=$gameid&time=$time&uid=$s_uid&userName=$userName&key=$login_key";
        $sign = md5($sign_str);
        log_message('debug', $this->platform.' sign_str is : '.$sign_str.' sign is '.$sign);
        if (!$userName || !$s_uid || !$login_key || !$game_id) {
            return false;
        }

        $condition = array(
                            'p_uid' => $s_uid,
                            'platform' => $this->platform,
                        );
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                            'platform' => $this->platform,
                            'p_uid' => $s_uid,
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
        $orderId = $this->input->get_post('cpOrderId');
        $orderMoney = $this->input->get_post('money');
        $condition = array('u_order_id' => $orderId);
        $this->load->model('Game_order_model');         //无平台预留字段 orderid与2460 不匹配
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if ($game_order) {
            //check sign
            $status = $this->input->get_post('status');
            $order_Id = $this->input->get_post('orderId');
            $uid = $this->input->get_post('uid');
            $userName = $this->input->get_post('userName');
            $gameId = $this->input->get_post('gameId');
            $goodsId = $this->input->get_post('goodsId');
            $goodsName = $this->input->get_post('goodsName');
            $server = $this->input->get_post('server');
            $role = $this->input->get_post('role');
            $time = $this->input->get_post('time');
            $ext = $this->input->get_post('ext');
            $sign = $this->input->get_post('sign');
            $signType = $this->input->get_post('signType');
            $sign_array = array(
                    'cpOrderId'=>$orderId,
                    'money'=>$orderMoney,
                    'status'=>$status,
                    'orderId'=>$order_Id,
                    'uid'=>$uid,
                    'userName'=>$userName,
                    'gameId'=>$gameId,
                    'goodsId'=>$goodsId,
                    'goodsName'=>$goodsName,
                    'server'=>$server,
                    'role'=>$role,
                    'time'=>$time,
            );
            $this->load->model('Common_model');
            $sign_str = $this->Common_model->sort_params($sign_array);
            $my_game_id = $game_order->game_id;
            $key = $this->Game_model->get_key($my_game_id, 'gamekey');
            // $my_sign = md5("cpOrderId=$orderId&gameId=$gameId&goodsId=$goodsId&goodsName=$goodsName&money=$orderMoney&role=$role&server=$server&time=$time&uid=$uid&key=$key");
            $my_sign = md5($sign_str.'&key='.$key);
            if ($my_sign==$sign) {
                log_message('debug', 'sign_callback_check '.$this->platform.' is success');
            } else {
                log_message('debug', 'sign_callback_check '.$this->platform.' is FAILED '.$sign_str);
            }
            //check sign done

            if (intval($orderMoney*100) == $game_order->money) {
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
        echo 'success';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }
    public function sign_order($game_id = '')
    {
        $gameid = $this->Game_model->get_key($game_id, 'appid');
        $uid = $this->input->get('uid');
        $time = $this->input->get('time');
        $server = $this->input->get('server');
        $role = $this->input->get('role');
        $goodsId = $this->input->get('goodsId');
        $goodsName = $this->input->get('goodsName');
        $money = $this->input->get('money');
        $cpOrderId = $this->input->get('cpOrderId');
        $ext = $this->input->get('ext');
        $pay_key = $this->Game_model->get_key($game_id, 'gamekey');
        $sign_str = "cpOrderId=$cpOrderId&gameId=$gameid&goodsId=$goodsId&goodsName=$goodsName&money=$money&role=$role&server=$server&time=$time&uid=$uid&key=$pay_key";

        $sign = md5($sign_str);
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
    public function focus($game_id)
    {
    }
    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }
}
