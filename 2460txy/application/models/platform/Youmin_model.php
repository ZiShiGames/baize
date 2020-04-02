<?php

class Youmin_model extends CI_Model
{
    public $platform = 'youmin';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('user_id');
        $access_token = $this->input->get('access_token');
        $timestamp = $this->input->get('timestamp');
        $sign = $this->input->get('sign');

        $app_id = $this->Game_model->get_key($game_id, 'appid');
        $secret_key = $this->Game_model->get_key($game_id, 'secretkey');
        $mysign = md5("app_id=$app_id&secret_key=$secret_key&timestamp=$timestamp&user_id=$user_id");
        $response_url = "http://pay.gamersky.com/users/info?app_id=$app_id&user_id=$user_id&timestamp=$timestamp&sign=$mysign";
        $response = $this->Curl_model->curl_get($response_url);
        log_message('debug', $this->platform.' response is '.$response.' content is '.$response_url);
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
        log_message('debug', $this->platform.' '.json_encode($_POST));
        $app_id = $this->input->get_post('app_id');
        $user_id = $this->input->get_post('user_id');
        $bill_no = $this->input->get_post('bill_no');
        $price = $this->input->get_post('total_fee');
        $status = $this->input->get_post('status');
        $ext = $this->input->get_post('ext');
        $sign = $this->input->get_post('sign');
        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        $appid=$this->Game_model->get_key($game_id, 'appid');
        $secret=$this->Game_model->get_key($game_id, 'secretkey');
        $sign_str = "app_id=$app_id&bill_no=$bill_no&ext=$ext&secret_key=$secret&status=$status&total_fee=$price&user_id=$user_id";
        $my_sign=md5($sign_str);
        // if (intval($price*100)==$game_order->money) {
        //     return $ext;
        // } else {
        //     return false;
        // }

        if ($sign==$my_sign) {
            if (intval($price*100)==$game_order->money) {
                return $ext;
            }
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
        $money = $this->input->get('money');
        $serverid = $this->input->get('serverid');
        $userId = $this->input->get('userId');
        $order_id = $this->input->get('order_id');
        $nickname = $this->input->get('nickname');

        $appid=$this->Game_model->get_key($game_id, 'appid');
        $secretkey=$this->Game_model->get_key($game_id, 'secretkey');
        $time = time();
        $sign_str = "app_id=$appid&bill_no=$order_id&ext=$order_id&gameaccount=$nickname&secret_key=$secretkey&server_id=$serverid&total_fee=$money&user_id=$userId";
        $sign=md5($sign_str);
        //app_id=xxx&bill_no=XXX&ext=XXX&gameaccount=xxx&secret_key=xxx&serverid=xxx&total_fee=XXX&user_id=XXX
        //app_id=xxx&bill_no=XXX&ext=XXX&gameaccount=xxx&secret_key=xxx&server_id=xxx&total_fee=XXX&user_id=XXX)
        $data = array(
            'nickname'=>$nickname,
            'appid'=>$appid,
            'sign'=>$sign,
            'sign_str'=>$sign_str
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
        $gameid= $this->Game_model->get_key($game_id, 'gameid');
        return $gameid;
    }
}
