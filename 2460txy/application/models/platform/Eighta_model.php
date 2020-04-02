<?php

class Eighta_model extends CI_Model
{
    public $platform = 'eighta';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('uid');
        $token = $this->input->get('token');
        $appid = $this->input->get('appid');
        $time = $this->input->get('time');
        $sign = $this->input->get('sign');

        $key = $this->Game_model->get_key($game_id, 'key');
        $my_sign = md5("$appid$user_id$token$time$key");
        if ($my_sign!=$sign) {
            return;
        }
        $this->cache->save('token', $token, 60*60*24);
        $this->cache->save('appid', $appid, 60*60*24);
        // $this->session->set_userdata('token', $token);
        // $this->session->set_userdata('appid', $appid);
        $this->session->set_userdata('uid', $user_id);
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
        $appid = $this->input->get_post('appid');
        $order_id = $this->input->get_post('order_id');
        $uid = $this->input->get_post('uid');
        $serverid = $this->input->get_post('serverid');
        $amount = $this->input->get_post('amount');
        $extra_orderno = $this->input->get_post('extra_orderno');
        $time = $this->input->get_post('time');
        $sign = $this->input->get_post('sign');
        $condition = array('u_order_id' => $extra_orderno);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (intval($amount*100)!=$game_order->money) {
            return;
        }
        $game_id = $game_order->game_id;
        $key = $this->Game_model->get_key($game_id, 'key');
        $my_sign = md5("$appid$order_id$uid$serverid$amount$extra_orderno$time$key");
        if ($sign==$my_sign) {
            return $extra_orderno;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo 'succ';
    }

    public function notify_error()
    {
        echo 'fail';
        ;
    }
    public function sign_order($game_id = '')
    {
        $amount = $this->input->get('amount');
        $server = $this->input->get('server');
        $uid = $this->input->get('uid');
        $order_id = $this->input->get('order_id');
        // $appid = $this->session->userdata('appid');
        // $token = $this->session->userdata('token');
        $appid = $this->cache->get('appid');
        $token = $this->cache->get('token');
        $time = time();
        $key = $this->Game_model->get_key($game_id, 'key');
        $sign = md5("$appid$uid$token$amount$server$order_id$time$key");
        $data = array(
            'time'=>$time,
            'sign'=>$sign,
            'appid'=>$appid,
            'token'=>$token,
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
    }
}
