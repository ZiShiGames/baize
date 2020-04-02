<?php

class Shunwangyuzhou_model extends CI_Model
{
    public $app_key = '';
    public $platform = 'shunwangyuzhou';
    public $pay_type = '';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $g_uid = $this->input->get('guid');
        $condition = array(
            'p_uid' => $g_uid,
            'platform' => $this->platform,
        );
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $g_uid,
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
        $order_id = $this->input->get_post('other_data');
        $money = $this->input->get_post('money');

        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money * 100) == $game_order->money) {
                    return $order_id;
                } else {
                    log_message('debug', $this->platform . ' get_order_id money errory ' . $game_order->money . " != $money");
                }
            } else {
                log_message('debug', $this->platform . ' get_order_id error order not found by ' . $order_id);
            }
        } else {
            log_message('debug', $this->platform . ' get_order_id error order_id or money null');
        }
        return false;
    }

    public function focus($game_id)
    {
        $coins = $this->input->get('coins');
        $account = $this->input->get('guid');
        $itemId = $this->input->get('itemId');
        $money = $this->input->get('money');
        $orderId = $this->input->get('orderNo');
        $time = $this->input->get('time');
        $sign = $this->input->get('sign');
        $this->app_key = $this->Game_model->get_key($game_id, 'appkey');
        $sign_str = $coins . "|" . $account . "|" . $itemId . "|" . $money . "|" . $orderId . "|" . $time . "|" . $this->app_key;
        log_message('debug', $this->platform . ' sign_str is:' . $sign_str);
        $pay_sign = strtoupper(md5($sign_str));
        $report_info = '0';
        if ($pay_sign != $sign) {
            $this->Output_model->json_print(-1, 'sign error', '签名不匹配');
            return '-1';
        }
        // if($account!= $this->session->userdata('account') ){
        //     $this->Output_model->json_print(-2,'username error', '用户名不匹配');
        //     log_message('debug', $this->platform.' focus account is '.$account.' saved account is '.$this->session->userdata('account'));
        //     return;
        // }
        // if($orderId!= $this->session->userdata('orderId') ){
        //     $this->Output_model->json_print(-3,'order error', '订单号不匹配');
        //     return;
        // }
        if ($orderId == $this->cache->get('orderId')) {
            $this->Output_model->json_print(-2, 'order error', '订单已存在');
            return '-2';
        }
        if ($itemId == $this->cache->get('itemId')) {
            $this->Output_model->json_print(-3, 'order error', '平台订单已存在');
            return '-3';
        }
        if ($this->pay_type == 'true') {
            $this->Output_model->json_print(0, 'success', '支付成功');
            return '0';
        }
        $this->Output_model->json_print(-4, 'error', '支付失败');
        return '-4';
    }
    public function notify_ok()
    {
        echo '{"code": 1,"msg": "ok"}';
    }

    public function notify_error()
    {

        // $this->Output_model->json_print(0,'success', '支付成功');
        echo '{"code": 3,"msg": "ok"}';
    }
    public function sign_order($game_id = '')
    {
        $appid = $this->Game_model->get_key($game_id, 'appid');

        $order_id = $this->input->get("order_id");
        $money = $this->input->get("money");
        $openId = $this->input->get("openId");
        $userId = $this->input->get("userId");
        $goodsName = $this->input->get("goodsName");
        // $serverid = $this->input->get("serverid");
        $cproleid = $this->input->get("cproleid");
        $cpNickname = $this->input->get("cpNickname");
        $guid = $this->input->get("guid");
        $cpLevel = $this->input->get("cpLevel");

        $time = time();
        $data = array(
            'gameId' => $appid,
            'guid' => $guid,
            'orderNo' => $order_id,
            'rmb' => $money,
            // 'idx' => $serverid,
            'size' => 240,
            'time' => $time,
            'otherData' => $order_id,
        );
        $pay_key = $this->Game_model->get_key($game_id, 'paykey');
        $sign = md5(base64_encode(json_encode($data)).$pay_key);

        // log_message('debug', $this->platform . ' sign str is ' . $guid . $serveridx . $time . $money . $itemid . $this->app_key);
        $data = array(
            'gameid' => $appid,
            'data' => base64_encode(json_encode($data)),
            'sign' => $sign,
        );
        return $data;
    }

    public function init($game_id)
    {
        $data = array('usertype' => $this->session->userdata('usertype'));

        return $data;
    }

    public function login_collect($data)
    {
    }
}
