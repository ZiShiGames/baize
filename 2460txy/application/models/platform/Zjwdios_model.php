<?php

class Zjwdios_model extends CI_Model
{
    public $platform = 'zjwdios';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('userId');
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
                            'game_id'=>$game_id,
                        );
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                            'platform' => $this->platform,
                            'p_uid' => $user_id,
                            'create_date' => time(),
                            'game_id'=>$game_id,
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
//         $orderId = $this->input->get_post('orderId');
//         $userId = $this->input->get_post('userId');
//         $goodsId = $this->input->get_post('goodsId');
        $ext = $this->input->get_post('ext');
        if($this->input->get_post('nonce')!=substr(md5(md5($ext).sha1($ext)),20)){
            return false;
        }
        //log_message('error', " Gowanme not  ".$this->input->get_post('ext').'   '.$this->input->get_post('nonce').'   '.substr(md5(md5($this->input->get_post('ext')).sha1($this->input->get_post('ext'))),20));
//         $time = $this->input->get_post('time');
//         $sign = $this->input->get_post('sign');
        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
//         $game_id = $game_order->game_id;
//         $gameId=$this->Game_model->get_key($game_id, 'gameId');
//         $secret=$this->Game_model->get_key($game_id, 'secret');
//         $my_sign=md5("ext=$ext"."gameId=$gameId"."goodsId=$goodsId"."secret=$secret"."time=$time"."userId=$userId");

        if ($game_order) {
            return $ext;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
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
        
      
        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        
        $gameId=$this->Game_model->get_key($game_id, 'gameId');
        $secret=$this->Game_model->get_key($game_id, 'secret');
        $time = time();
        $sign=md5("ext=$order_id"."gameId=$gameId"."goodsId=$amount"."secret=$secret"."time=$time"."userId=$uid");
        $post_data = array(
           'ext'=>$order_id,
            'nonce'=>substr(md5(md5($order_id).sha1($order_id)),20)
        );
//         log_message('error', " Gowanme not  ".$order_id.'   '.substr(md5(md5($order_id).sha1($order_id)),20));
        $url="http://api.baizegame.com/index.php/api/notify/zjwdios/$game_id?".http_build_query($post_data);
        $this->load->library('GowanEncryption');
        
        $post_data = array(
            'amount'=>$game_order->money,
            'cpProductId'=>$game_order->u_order_id,
            'productName'=>$game_order->goodsName,
            'chargeDesc'=>$game_order->goodsName,
            'callbackURL'=>GowanEncryption::encode($url),
            'serverId'=>$game_order->ext,
            'serverName'=>'""',
            'roleName'=>'""',
            'roleId'=>$game_order->cproleid,
            'rate'=>'""',
            'roleLevel'=>0,
            'sociaty'=>'""',
            'lastMoney'=>'""',
            'vipLevel'=>0,
            'cpOrderId'=>$game_order->u_order_id,
        );
       
        return $post_data;
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
        $gameid= $this->Game_model->get_key($game_id, 'GameId');
        return $gameid;
    }
}
