<?php

class Ifeng_model extends CI_Model
{
    public $platform = 'ifeng';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $ticket = $this->input->get('ticket');
        $timestamp = $this->input->get('timestamp');
        $appid = $this->input->get('appId');
        $passid = $this->input->get('passId');
        $server_id = $this->Game_model->get_key($game_id, 'serverid');
        $gameid = $this->Game_model->get_key($game_id, 'gameid');
        $partnerid = $this->Game_model->get_key($game_id, 'partnerid');
        $channelid = $this->Game_model->get_key($game_id, 'channelid');
        $this->session->set_userdata('partnerid', $partnerid);
        $this->session->set_userdata('game_id', $gameid);
        $service = 'user.validate';
        $formart = 'json';
        $key = $this->Game_model->get_key($game_id, 'partnerkey');
        $tk = urlencode($ticket);
        $tkd = urldecode($ticket);
        $sign_str = $partnerid.$gameid.$server_id.$ticket.$key;
        $sign = strtoupper(MD5($sign_str));
        $response = "http://union.play.ifeng.com/mservice?service=$service&partner_id=$partnerid&game_id=$gameid&server_id=$server_id&ticket=$tk&sign=$sign&formart=$formart";
        $request = $this->Curl_model->curl_get($response);
        $request = json_decode($request);
        $user_id = $request->data->user_id;

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
        $partner_id = $this->input->post('partner_id');
        $game_id = $this->input->post('game_id');
        $server_id = $this->input->post('server_id');
        $bill_no = $this->input->post('bill_no');
        $price = $this->input->post('price');
        $user_id = $this->input->post('user_id');
        $trade_status = $this->input->post('trade_status');
        $partner_bill_no = $this->input->post('partner_bill_no');
        $extra = $this->input->post('extra');
        $sign = $this->input->post('sign');
        $condition = array('u_order_id' => $partner_bill_no);
        $this->load->model('Game_order_model');

        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (intval($price*100)!=$game_order->money) {
            return;
        }
        $gameid=$game_order->game_id;
        $key = $this->Game_model->get_key($gameid, 'partnerkey');
        $sign_str = $partner_id.$game_id.$server_id.$user_id.$bill_no.$price.$trade_status.$key;
        $my_sign = strtoupper(md5($sign_str));
        // $mysign = Upper(MD5(partner_id+game_id+server_id+user_id+b ill_no+price+trade_status+ key));


        if ($sign==$my_sign) {
            log_message('debug', $this->platform.' check money '.$price);
            return $partner_bill_no;
        } else {
            echo $sign.'  '.$my_sign;
            return false;
        }
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAILURE';
        ;
    }
    public function sign_order($game_id = '')
    {
        $gameid = $this->Game_model->get_key($game_id, 'gameid');
        $partnerid = $this->Game_model->get_key($game_id, 'partnerid');
        $data = array(
            'game_id'=>$gameid,
            'server_id'=>'1',
            'partner_id'=>$partnerid,
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
