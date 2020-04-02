<?php

class Tangdaoya_model extends CI_Model
{
    public $platform = 'tangdaoya';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('tdyid');

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
                log_message('error', $this->platform . " Login error user create $content");

                return false;
            }
            $user['user_id'] = $user_id;
        }

        $this->cache->save($user['user_id'] . '_token', md5($user['user_id'] . $user['platform'] . time()), 86400);

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
        $access_token = md5(substr(time(), 0, 8) . 'aoyouxi');

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

        $openKey = $this->cache->get($openId . '_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId . $noice . $game->app_key);
        $game_url = $game->game_login_url;
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "allu login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get_post('order_id');
        $status = $this->input->get_post('trade_status'); // 交易状态。ORDER_NOT_EXIST 订单不存在;NON_PAYMENT 未支付;SUCCESS 支付成功;FAILURE 支付失败
        $money = $this->input->get_post('trade_amount');
        $ext = $this->input->get_post('data');

        if ($status != 'SUCCESS' || $order_id != $ext) {
            return false;
        }

        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);

        if (intval($money) != $game_order->money) {
            return false;
        }
        return $ext;
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
        $tdyid = $this->input->get_post('tdyid');
        $tdytoken = $this->input->get_post('tdytoken');
        $order_id = $this->input->get_post('order_id');
        $order_name = $this->input->get_post('order_name');
        $amount = $this->input->get_post('amount');
        $data = $order_id; // 透传参数
        $ts = time().'000';
        $appid = $this->Game_model->get_key($game_id, 'appid');
        $appkey = $this->Game_model->get_key($game_id, 'appkey');

        $requery_url = "https://youxiapi.tangdaoya.com/exchange/v1/trade/prepayment";
        $header = array(
            "Content-Type" => "application/x-www-form-urlencoded;charset=UTF-8",
        );

        $sign = md5($appid . $order_id . $ts . $appkey);
        $data = array(
            'appid' => $appid,
            'tdyid' => $tdyid,
            'tdytoken' => $tdytoken,
            'order_id' => $order_id,
            'order_name' => $order_name,
            'amount' => $amount,
            'ts' => $ts,
            'sign' => $sign,
            'data' => $data,
        );

        $content = $this->Curl_model->curl_post($requery_url, $data, $header);
        if (!$content) {
            $this->Output_model->json_print(2, 'no content from ' . $requery_url);
            log_message('error', $this->platform . ' no content from ' . $requery_url);
            exit;
        }
        $response = json_decode($content);
        if ($response && $response->code != 0 && $response->msg != 'OK') {
            $this->Output_model->json_print(2, 'pay err ' . json_encode($response));
            log_message('error', $this->platform . ' sign_order pay err ' . json_encode($response));
            exit;
        }

        // echo $data;
        return $response->prepay_id;
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
    public function focus($game_id = '')
    {
        $gameid = $this->Game_model->get_key($game_id, 'gameid');
        return $gameid;
    }
}
