<?php

class Fanqie_model extends CI_Model
{
    public $platform = 'fanqie';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('openid');
        $token = $this->input->get('token');
        // 检验token
        $appid = "hr2be755ba636eb21926";
        $appkey = "5177ff6fb39a1268d53637bcf561ef33";

        $str = array(
            "appid" => $appid,
            "openid" => $user_id,
            "token" => $token,
        );
        $str = base64_encode(json_encode($str));

        $str1 = "appid=$appid&openid=$user_id&token=$token";
        $sign = md5($appkey . $str1 . $appkey);

        $data = array(
            "appid" => $appid,
            "openid" => $user_id,
            "token" => $token,
            "sign" => $sign,
            "str" => $str,
        );
        $url = "https://game.qianyuanclub.cn/v1/default/checkToken";
        $content = $this->Curl_model->curl_post($url, $data);

        log_message('error', $this->platform . " login token_check. curl_post data: " . json_encode($data));

        if (!$content) {
            log_message('error', $this->platform . " login token_check. empty content $url");
        }

        $response = json_decode($content);
        log_message('error', $this->platform . " login token_check. is_login: ".json_encode($response->data->is_login));

        if($response->data->is_login !== true) {
            return false;
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
        $data = $this->input->get_post('data');
        $response = json_decode(base64_decode($data));

        $ext = $response->cp_order_id;
        $money = $response->money;

        log_message('debug', $this->platform . ' get_order_id: ' . $ext . ' money: ' . $money);
        log_message('debug', $this->platform . ' get_order_id: ' . json_encode($response));

        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);

        if ($money != $game_order->money) {
            return false;
        }

        return $ext;
    }

    public function notify_ok()
    {
        echo 'success';
    }

    public function notify_error()
    {
        echo 'fail';

    }
    public function sign_order($game_id = '')
    {
        $account = $this->input->get('account');
        $amount = $this->input->get('amount');
        $description = $this->input->get('description');
        $game_order_id = $this->input->get('game_order_id');
        $name = $this->input->get('name');
        $openid = $this->input->get('openid');
        $unit_price = $this->input->get('unit_price');
        $token = $this->input->get('token');
        $time = time();
        $cpgame_id = $this->Game_model->get_key($game_id, 'gameId');
        $appkey = $this->Game_model->get_key($game_id, 'appkey');
        // $gameInfo = $this->Game_model->get_by_game_id($game_id);
        // $redirect_url = urlencode("http://h5sdk-xly.xileyougame.com/index.php/enter/play/$gameInfo->platform/$gameInfo->game_id");
        $redirect_url = urlencode('#');

        $str1 = "account=$account&amount=$amount&created_at=$time&description=$description&game_id=$cpgame_id&game_order_id=$game_order_id&name=$name&openid=$openid&redirect_url=$redirect_url&token=$token&unit_price=$unit_price";

        $sign = md5($appkey . $str1 . $appkey);
        log_message('error', $this->platform . ". str1: $str1 sign: $sign");

        $data = array(
            "account" => $account, // 商品数量
            "amount" => $amount, // 交易总金额
            "created_at" => $time,
            "description" => $description, // 描述
            "game_id" => $cpgame_id,
            "game_order_id" => $game_order_id,
            "name" => $name,
            "openid" => $openid,
            "redirect_url" => $redirect_url,
            "sign" => $sign,
            "token" => $token,
            "unit_price" => $unit_price,
        );

        $data = base64_encode(json_encode($data));

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
    public function focus($game_id = '')
    {
        $gameid = $this->Game_model->get_key($game_id, 'gameId');
        return $gameid;
    }
}
