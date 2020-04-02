<?php

class Jiuxiangwan_model extends CI_Model
{
    public $platform = 'jiuxiangwan';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('uid');
        $token = $this->input->get('token');
        $this->cache->save('uid', $user_id, 3600*24*7);
        $this->cache->save('token', $token, 3600*24*7);
        // $this->session->set_userdata('uid', $user_id);
        // $this->session->set_userdata('token', $token);
        $appid = $this->Game_model->get_key($game_id, 'appid');
        $appkey = $this->Game_model->get_key($game_id, 'appkey');
        $sign = md5('appid='.$appid.'&appkey='.$appkey.'&token='.$token.'&uid='.$user_id);
        $request = 'http://www.jxw123.com/user/info?uid='.$user_id.'&token='.$token.'&appid='.$appid.'&sign='.$sign.'';

        $response = $this->Curl_model->curl_get($request);
        $response = json_decode($response);
        if ($response->code!=200) {
            return;
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
        $orderId = $this->input->get_post('orderid');
        $money = $this->input->get_post('money');
        $status = $this->input->get_post('status');
        if ($status!=10000) {
            return;
        }
        $uid = $this->input->get_post('uid');
        $sign = $this->input->get_post('sign');
        $condition = array('u_order_id' => $orderId);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if ($game_order->money!=$money) {
            return;
        }
        $game_id = $game_order->game_id;
        $gameId=$this->Game_model->get_key($game_id, 'appid');
        $secret=$this->Game_model->get_key($game_id, 'appkey');
        $my_sign=md5('appid='.$gameId.'&appkey='.$secret.'&money='.$money.'&orderid='.$orderId.'&status='.$status.'&uid='.$uid);

        if ($sign==$my_sign) {
            return $orderId;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo 'success';
    }

    public function notify_error()
    {
        echo 'fail';
        ;
    }
    public function sign_order($game_id = '')
    {
        $amount = $this->input->get('amount');
        $uid = $this->input->get('uid');
        $order_id = $this->input->get('order_id');
        $gameId=$this->Game_model->get_key($game_id, 'appid');
        $secret=$this->Game_model->get_key($game_id, 'appkey');
        $goodsName=$this->input->get('goodsName');
        $sign=MD5('appid='.$gameId.'&appkey='.$secret.'&money='.$amount.'&orderid='.$order_id.'&product='.$goodsName.'&uid='.$uid);
        $data = array(
            'appid'=>$gameId,
            'sign'=>$sign,
        //     'app_id'=>$response->app_id,
        //     'timestamp'=>$response->timestamp,
        //     'nonce_str'=>$response->nonce_str,
        //     'package'=>$response->package,
        //     'sign_type'=>$response->sign_type,
        //     'pay_sign'=>$response->pay_sign,
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
        $uid = $this->cache->get('uid');
        $token = $this->cache->get('token');
        // $uid = $this->session->userdata('uid');
        // $token = $this->session->userdata('token');
        $data = array(
            'uid'=>$uid,
            'token'=>$token,
        );
        return $data;
    }
}
