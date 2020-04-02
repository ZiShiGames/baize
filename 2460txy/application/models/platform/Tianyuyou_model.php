<?php

class Tianyuyou_model extends CI_Model
{
    public $platform = 'tianyuyou';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $app_id = $this->input->get('app_id');
        $mem_id = $this->input->get('mem_id');
        $user_token = $this->input->get('token');
        $requery_url = 'http://api.tianyuyou.com/notice/gamecp/checkusertoken.php';
        $this->load->model('Curl_model');
        $appkey = $this->Game_model->get_key($game_id, 'appkey');
        $sign_str = "app_id=$app_id&mem_id=$mem_id&user_token=$user_token&app_key=$appkey";
        $sign = md5($sign_str);
        $requery_data = array(
            "app_id" => $app_id,
            "mem_id" => $mem_id,
            "user_token" => $user_token,
            "sign" => $sign,
        );
        $requery_data='{"app_id":"'.$app_id.'","mem_id":"'.$mem_id.'","user_token": "'.$user_token.'","sign":"'.$sign.'"}';
        $request = json_decode($this->Curl_model->curl_post($requery_url, $requery_data));
        if (isset($request->status)&&$request->status==1) {
            $user_id = $mem_id;
        } else {
            echo $this->platform.' login request is '.json_encode($request).' sign '.$sign_str;
            return;
        }

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
        $urldata = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '';

        $success = "SUCCESS";
        $fail = "FAILURE";

        // 缺少参数
        if (empty($urldata)) {
            exit($fail);
        }

        $urldata = get_object_vars(json_decode($urldata));
        $order_id = isset($urldata['order_id']) ? $urldata['order_id'] : '';
        $mem_id = isset($urldata['mem_id']) ? $urldata['mem_id'] : '';
        $app_id = isset($urldata['app_id']) ? intval($urldata['app_id']) : 0;
        $money = isset($urldata['money']) ? $urldata['money'] : 0.00;
        $order_status = isset($urldata['order_status']) ? $urldata['order_status'] : '';
        $paytime = isset($urldata['paytime']) ? intval($urldata['paytime']) : 0;
        $attach = isset($urldata['attach']) ? $urldata['attach'] : ''; //CP扩展参数
        $sign = isset($urldata['sign']) ? $urldata['sign'] : ''; // 签名


        if (empty($urldata) || empty($order_id) || empty($mem_id) || empty($app_id) || empty($money)
            || empty($order_status) || empty($paytime) || empty($attach) || empty($sign)) {
            exit($fail);
        }

        $condition = array('u_order_id' => $attach);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        $my_money = $game_order->money;
        if ($my_money!=$money*100) {
            return;
        }
        $app_key=$this->Game_model->get_key($game_id, 'appkey');
        $paramstr = "order_id=".$order_id."&mem_id=".$mem_id."&app_id=".$app_id."&money=".$money."&order_status=".$order_status."&paytime=".$paytime."&attach=".$attach."&app_key=".$app_key;
        $verrifysign = md5($paramstr);

        if (0 == strcasecmp($verrifysign, $sign)) {
            return $attach;
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
        $app_id = $this->input->get('app_id');
        $mem_id = $this->input->get('mem_id');
        $token = $this->input->get('token');
        $income_amount = $this->input->get('income_amount');
        $roleid = $this->input->get('roleid');
        $serverid = $this->input->get('serverid');
        $productname = $this->input->get('productname');
        $attach = $this->input->get('attach');
        $notice_url = "http://h5sdk.zytxgame.com/index.php/api/notify/tianyuyou/$game_id";
        $datetime = date('Y-m-d h:i:s', time());
        $app_key=$this->Game_model->get_key($game_id, 'appkey');
        $this->load->model('Common_model');
        $sort_data = array(
            'app_id' => $app_id,
            'mem_id' => $mem_id,
            'token' => $token,
            'income_amount' => $income_amount,
            'roleid' => $roleid,
            'serverid' => $serverid,
            'productname' => $productname,
            'attach' => $attach,
            'notice_url' => $notice_url,
            'datetime' => $datetime,
        );
        $sign_str = $this->Common_model->sort_params($sort_data);
        $sign_str .= "&app_key=$app_key";
        $sign = md5($sign_str);
        $post_data = array(
            'app_id' => $app_id,
            'mem_id' => $mem_id,
            'token' => $token,
            'income_amount' => $income_amount,
            'roleid' => $roleid,
            'serverid' => $serverid,
            'productname' => $productname,
            'attach' => $attach,
            'notice_url' => $notice_url,
            'datetime' => $datetime,
            'sign'=>strtolower($sign),
        );
        $response = $this->Curl_model->curl_post('http://api.tianyuyou.com/notice/gamecp/createorder.php', json_encode($post_data));
        $response = json_decode($response);
        if ($response->status ==1) {
            $data = array(
                'order_id'=>$response->result->order_no,
                'mem_id'=>$response->result->mem_id,
                'income_amount'=>$response->result->income_amount,
            );
            return $data;
        } else {
            return;
        }
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
        $app_id = $this->input->get('app_id');
        $mem_id = $this->input->get('mem_id');
        $token = $this->input->get('token');
        $rolename = $this->input->get('rolename');
        $roleid = $this->input->get('roleid');
        $zoneid = $this->input->get('zoneid');
        $rolelevel = $this->input->get('rolelevel');
        $balance = '0.00';
        $vip = 0;
        $partyname = '无帮派';
        $rolectime = 0;
        $rolelevelimtime = 0;

        $app_key=$this->Game_model->get_key($game_id, 'appkey');
        $this->load->model('Common_model');
        $sort_data = array(
            'app_id' => $app_id,
            'mem_id' => $mem_id,
            'token' => $token,
            'rolename' => $rolename,
            'roleid' => $roleid,
            'zoneid' => $zoneid,
            'rolelevel' => $rolelevel,
            'zonename' => $zoneid,
            'balance' => $balance,
            'vip' => $vip,
            'partyname' => $partyname,
            'rolectime' => $rolectime,
            'rolelevelimtime' => $rolelevelimtime,
        );
        $sign_str = $this->Common_model->sort_params($sort_data);
        $sign_str .= "&app_key=$app_key";
        $sort_data['sign'] = md5($sign_str);
        $params = json_encode($sort_data,JSON_UNESCAPED_UNICODE);
        $post_url = 'http://api.tianyuyou.com/notice/gamecp/gamememberinfo.php';
        echo $this->Curl_model->curl_post($post_url,$params);
    }
}
