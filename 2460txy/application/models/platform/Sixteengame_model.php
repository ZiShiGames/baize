<?php

class Sixteengame_model extends CI_Model
{
    public $platform = 'sixteengame';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $access_token = $this->input->get('access_token');
        $key = $this->Game_model->get_key($game_id, 'CPkey');
        $partnerid = $this->Game_model->get_key($game_id, 'CPpartnerid');

        $sign_str = "access_token=$access_token&partnerid=$partnerid&key=$key";
        $sign = strtoupper(sha1($sign_str));

        $this->load->model('Curl_model');
        $params = array(
               'partnerid' => $partnerid,
               'access_token' =>$access_token,
               'sign'=>$sign
        );

        $url = 'http://gameapi.16you.com/userinfo/getuserinfo.html';
        $content = $this->Curl_model->curl_post($url, $params);

        if (!$content) {
            return;
        }

        log_message('debug', $this->platform.' login response is '.json_encode($content));

        $response = json_decode($content);
        if (isset($response->code)&&$response->code==0) {
            $user_id = $response->userid;
        }

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
        $trade_status = $this->input->get_post('trade_status');
        $game = $this->input->get_post('game');
        $partnerid = $this->input->get_post('partnerid');
        $userid = $this->input->get_post('userid');
        $total_fee = $this->input->get_post('total_fee');
        $transaction_id = $this->input->get_post('transaction_id');
        $out_trade_no = $this->input->get_post('out_trade_no');
        $product_id = $this->input->get_post('product_id');
        $attach = $this->input->get_post('attach');
        $pay_time = $this->input->get_post('pay_time');
        $timestamp = $this->input->get_post('timestamp');
        $sign = $this->input->get_post('sign');

        $ext = $out_trade_no; // 订单号

        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;

        $key=$this->Game_model->get_key($game_id, 'CPkey');

        $this->load->model("Common_model");
        $sort_data = array(
            "attach"=>$attach,
            "trade_status"=>$trade_status,
            "game"=>$game,
            "partnerid"=>$partnerid,
            "userid"=>$userid,
            "total_fee"=>$total_fee,
            "transaction_id"=>$transaction_id,
            "out_trade_no"=>$out_trade_no,
            "product_id"=>$product_id,
            "pay_time"=>$pay_time,
            "timestamp"=>$timestamp,
        );
        $sign_str = $this->Common_model->sort_params($sort_data)."&key=$key";


        // $sign_str = "attach=$attach&game=$game&out_trade_no=$out_trade_no&partnerid=$partnerid&pay_time=$pay_time&product_id=$product_id&timestamp=$timestamp&total_fee=$total_fee&trade_status=$trade_status&transaction_id=$transaction_id&userid=$userid&key=$key";

        $my_sign = strtoupper(sha1($sign_str));
        log_message('debug', $this->platform." $sign_str | $sign | $my_sign");

        if (intval($total_fee)!=$game_order->money) {
            return false;
        }

        if ($sign==$my_sign) {
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
        echo 'FAIL';
    }
    public function sign_order($game_id = '')
    {
        $key=$this->Game_model->get_key($game_id, 'CPkey');

        $out_trade_no = $this->input->get('out_trade_no');
        $product_id = $this->input->get('product_id');
        $total_fee = $this->input->get('total_fee');
        $body = $this->input->get('body');
        $detail = $this->input->get('detail');
        $attach = $this->input->get('attach');


        $sign = strtoupper(sha1("attach=$attach&body=$body&detail=$detail&out_trade_no=$out_trade_no&product_id=$product_id&total_fee=$total_fee&key=$key"));

        $data = array(
            'attach' => $attach,
            'body' => $body,
            'detail' => $detail,
            'out_trade_no' => $out_trade_no,
            'product_id' => $product_id,
            'total_fee' => $total_fee,
            'sign' => $sign
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
