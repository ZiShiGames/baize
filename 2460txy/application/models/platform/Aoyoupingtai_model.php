<?php

class Aoyoupingtai_model extends CI_Model
{
    public $platform = 'aoyoupingtai';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('userid');
        $isSubscribe = $this->input->get('isSubscribe');
        $isShowSubscribe = $this->input->get('isShowSubscribe');
        $shareCode = $this->input->get('shareCode');
        $friendCode = $this->input->get('friendCode');
        $channel = $this->input->get('channel');
        $time = $this->input->get('time');
        $sign = $this->input->get('sign');

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
        $userid = $this->input->get_post('userid');
        $appid = $this->input->get_post('appid');
        $serverid = $this->input->get_post('serverid');
        $orderid = $this->input->get_post('orderid');
        $ayorderid = $this->input->get_post('ayorderid');
        $paystatus = $this->input->get_post('paystatus');
        $money = $this->input->get_post('money');
        $gamemoney = $this->input->get_post('gamemoney');
        $attach = $this->input->get_post('attach');
        $timestamp = $this->input->get_post('timestamp');
        $sign = $this->input->get_post('sign');
        $condition = array('u_order_id' => $attach);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        $secret=$this->Game_model->get_key($game_id, 'appkey');
        $sign_str = "appid=$appid&attach=$attach&ayorderid=$ayorderid&gamemoney=$gamemoney&money=$money&orderid=$orderid&paystatus=$paystatus&serverid=$serverid&timestamp=$timestamp&userid=$userid$secret";
        $my_sign=md5($sign_str);
        log_message('debug', $this->platform.' notify sign str '.$sign_str.' sign '.$my_sign);
        if ($sign==strtoupper($my_sign)) {
            if ($money*100!=$game_order->money) {
                return;
            }
            return $attach;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo 'ok';
    }

    public function notify_error()
    {
        echo 'fail';
        ;
    }
    public function sign_order($game_id = '')
    {
        $action = $this->input->get('action');
        $userid = $this->input->get('userid');
        $appid = $this->input->get('appid');
        $serverid = $this->input->get('serverid');
        $orderid = $this->input->get('orderid');
        $money = $this->input->get('money');
        $gamemoney = $this->input->get('gamemoney');
        $attach = $this->input->get('attach');
        $secret=$this->Game_model->get_key($game_id, 'appkey');
        $time_stamp=date('YmdHis', time());
        $sign_str = "action=payment&appid=1&attach=$attach&gamemoney=$gamemoney&money=$money&notifyurl=http://h5sdk.zytxgame.com/index.php/api/notify/aoyoupingtai/1123&orderid=$orderid&serverid=$serverid&timestamp=$time_stamp&userid=$userid$secret";
        $sign=md5($sign_str);
        log_message('debug', $this->platform.' sign str '.$sign_str.' sign '.$sign);
        // echo $sign_str;


        $data = array(
            'notify'=>'http://h5sdk.zytxgame.com/index.php/api/notify/aoyoupingtai/1123',
            'timeStamp'=>$time_stamp,
            'sign'=>$sign,
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
