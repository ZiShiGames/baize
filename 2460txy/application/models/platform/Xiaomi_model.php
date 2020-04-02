<?php

class Xiaomi_model extends CI_Model
{
    public $platform = 'xiaomi';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('uid');
        $session = $this->input->get('session');

        $this->session->set_userdata('uid', $user_id);
        $this->session->set_userdata('session', $session);
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
        log_message('debug', $this->platform . '  order: ' . json_encode($_GET));
        $payFee = $this->input->get_post('payFee');
        $cpUserInfo = $this->input->get_post('cpUserInfo');
        $cpOrderId = $this->input->get_post('cpOrderId');
        $orderId = $this->input->get_post('orderId');
        $sign = $this->input->get_post('sign');
        $ext = $cpUserInfo;

        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        $key = $this->Game_model->get_key($game_id, 'AppSecret');

        log_message('debug', $this->platform . " sign: $sign . key: $key");

        if ($payFee != $game_order->money) {
            return false;
        }

        return $ext;
    }

    public function notify_ok()
    {
        echo '{"errcode":200,"errMsg":"success"}';
    }

    public function notify_error()
    {
        echo '{"errcode":200,"errMsg":"fail"}';
    }
    public function sign_order($game_id = '')
    {
        $appId = $this->Game_model->get_key($game_id, 'APPID');
        $key = $this->Game_model->get_key($game_id, 'APPKEY');

        $appAccountId = $this->input->get('appAccountId');
        $session = $this->input->get('session');
        $cpOrderId = $this->input->get('cpOrderId');
        $cpUserInfo = $cpOrderId;
        $money = $this->input->get('money');
        $displayName = $this->input->get('displayName');

        $_data = array(
            'appId' => $appId,
            'appAccountId' => $appAccountId,
            'session' => $session,
            'cpOrderId' => $cpOrderId,
            'cpUserInfo' => $cpUserInfo,
            'displayName' => $displayName,
            'feeValue' => $money,
        );

        $sign = $this->sign($_data, $key);

        $data = array(
            'appId' => $appId,
            'appAccountId' => $appAccountId,
            'appAccountId' => $appAccountId,
            'session' => $session,
            'sign' => $sign,
        );

        log_message('debug', $this->platform . ' sign_order response sign: ' . $sign);

        return $data;
    }

    /**
     * 计算hmac-sha1签名
     * @param array $params
     * @param type $secretKey
     * @return type
     */
    private function sign(array $params, $secretKey)
    {
        $sortString = $this->buildSortString($params); // 排序
        $signature = hash_hmac('sha1', $sortString, $secretKey, false);
        return $signature;
    }

    /**
     * 验证签名
     * @param array $params
     * @param type $signature
     * @param type $secretKey
     * @return type
     */
    private function verifySignature(array $params, $signature, $secretKey)
    {
        $tmpSign = $this->sign($params, $secretKey);
        return $signature == $tmpSign ? true : false;
    }

    /**
     * 构造排序字符串
     * @param array $params
     * @return string
     */
    private function buildSortString(array $params)
    {
        if (empty($params)) {
            return '';
        }

        ksort($params);

        $fields = array();

        foreach ($params as $key => $value) {
            $fields[] = $key . '=' . $value;
        }

        return implode('&', $fields);
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
        $game_appid = $this->Game_model->get_key($game_id, 'gameappid');
        $key = $this->Game_model->get_key($game_id, 'gamekey');

        // 角色创建完成后
        $user_id = $this->input->get('roleid');
        $server_id = $this->input->get('srvid');
        $server_name = $server_id;
        $role_id = $this->input->get('cproleid');
        $role_name = $this->input->get('nickname');
        $level = $this->input->get('level');

        $data = array(
            'user_id' => $user_id,
            'game_appid' => $game_appid,
            'server_id' => $server_id,
            'server_name' => $server_name,
            'role_id' => $role_id,
            'role_name' => $role_name,
            'level' => $level,
        );

        $sign = $this->signData($data, $key);

        $reportData = array(
            'game_appid' => $game_appid,
            'sign' => $sign,
        );

        return $reportData;

    }
}
