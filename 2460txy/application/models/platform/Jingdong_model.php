<?php

class Jingdong_model extends CI_Model
{
    public $platform = 'jingdong';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $channelExt = $this->input->get('channelExt');
        $game_appid = $this->input->get('game_appid');
        $loginplatform2cp = $this->input->get('loginplatform2cp');
        $nickname = $this->input->get('nickname');
        $user_id = $this->input->get('user_id');
        $sdkloginmodel = $this->input->get('sdkloginmodel');
        $sign = $this->input->get('sign');

        $this->session->set_userdata('uid', $user_id);
        $this->session->set_userdata('channelExt', $channelExt);
        $this->session->set_userdata('sdkloginmodel', $sdkloginmodel);
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
        log_message('debug', json_encode($_GET));
        $amount = $this->input->get_post('amount');
        $channel_source = $this->input->get_post('channel_source');
        $payplatform2cp = $this->input->get_post('payplatform2cp');
        $game_appid = $this->input->get_post('game_appid');
        $out_trade_no = $this->input->get_post('out_trade_no');
        $trade_no = $this->input->get_post('trade_no');
        $sign = $this->input->get_post('sign');
        $ext = $trade_no;

        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        $key=$this->Game_model->get_key($game_id, 'gamekey');

        $sign_str = "amount=$amount&channel_source=$channel_source&game_appid=$game_appid&out_trade_no=$out_trade_no&payplatform2cp=$payplatform2cp&trade_no=$trade_no$key";
        $my_sign = md5($sign_str);
        log_message('debug', $this->platform."$sign_str $my_sign $sign");

        if($amount!=$game_order->money){
            return false;
        }

        return $ext;
    }

    public function notify_ok()
    {
        echo '{"status":"success"}';
    }

    public function notify_error()
    {
        echo '{"status":"fail"}';
    }
    public function sign_order($game_id = '')
    {
        $game_appid=$this->Game_model->get_key($game_id, 'appid');
        $key=$this->Game_model->get_key($game_id, 'gamekey');

        $props_name = $this->input->get('props_name');
        $trade_no = $this->input->get('trade_no');
        $amount = $this->input->get('amount');

        $channelExt = $this->session->userdata('channelExt');
        $user_id = $this->session->userdata('uid');
        $sdkloginmodel = $this->session->userdata('sdkloginmodel');

        $_data = array(
            'amount' => $amount,
            'channelExt' => $channelExt,
            'game_appid' => $game_appid,
            'props_name' => $props_name,
            'trade_no' => $trade_no,
            'user_id' => $user_id,
            'sdkloginmodel' => $sdkloginmodel
        );

        $sign= $this->signData($_data, $key);

        $data = array(
            'channelExt' => $channelExt,
            'game_appid' => $game_appid,
            'user_id' => $user_id,
            'sdkloginmodel' => $sdkloginmodel,
            'sign' => $sign
        );

        return $data;
    }

    private function signData($data, $game_key) {
        ksort($data);
        foreach ($data as $k => $v) {
            $tmp[] = $k . '=' . $v;
        }
        $str = implode('&', $tmp) . $game_key;
        return md5($str);
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
        $game_appid=$this->Game_model->get_key($game_id, 'appid');
        $key=$this->Game_model->get_key($game_id, 'gamekey');

        // 角色创建完成后
        $server_id = $this->input->get('srvid');
        $server_name = $server_id;
        $role_id = $this->input->get('role_id');
        $role_name = $this->input->get('role_name');
        $level = $this->input->get('level');

        $user_id = $this->session->userdata('uid');

        $data = array(
            'user_id' => $user_id,
            'game_appid' => $game_appid,
            'server_id' => $server_id,
            'server_name' => $server_name,
            'role_id' => $role_id,
            'role_name' => $role_name,
            'level' => $level
        );

        $sign = $this->signData($data, $key);

        $reportData = array(
            'user_id' => $user_id,
            'game_appid' => $game_appid,
            'sign' => $sign
        );

        return $reportData;

    }
}
