<?php

class Xiaowo_model extends CI_Model
{
    public $platform = 'xiaowo';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'redis'));
    }

    public function login($game_id)
    {
        $key = $this->Game_model->get_key($game_id, 'secretkey');

        $appid = $this->input->get('appid');
        $access_token = $this->input->get('access_token');
        $channelid = $this->input->get('channelid');
        $nonce = $this->input->get('nonce');
        $timestamp = $this->input->get('timestamp');
        $sign = $this->input->get('sign');
        $sign_confirm = MD5("access_token=$access_token&appid=$appid&channelid=$channelid&nonce=$nonce&timestamp=$timestamp$key");

        $url = 'http://h5game.wostore.cn/platformapi/game/getUserInfo?appid=' . $appid . '&channelid=' . $channelid . '&access_token=' . $access_token . '&nonce=' . $nonce . '&timestamp=' . $timestamp . '&sign=' . $sign_confirm;
        $content = $this->Curl_model->curl_get($url);
        // log_message('debug', $this->platform."、sign: $sign 、sign_confirm: $sign_confirm 、url: $url");

        $response = json_decode($content);
        if (!$response) {
            log_message('error', 'response is null');
            return false;
        };

        if ($response->respCode == 0) {
            $user_id = $response->data->userId;
            if ($this->cache->redis->is_supported()) {
                $this->cache->redis->save($access_token,$user_id,86400);
                // $admin_info = $this->cache->redis->get($token);
            }else{
                return false;
            }

        }else{
            return false;
        }

        $this->session->set_userdata('uid', $user_id);
        $this->session->set_userdata('channelid', $channelid);
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

        $res_xml = file_get_contents("php://input");
        log_message('debug',$this->platform.' '.json_encode($res_xml));
        libxml_disable_entity_loader(true); // 防止xml跨站攻击
        $ret = json_decode(json_encode(simplexml_load_string($res_xml, 'simpleXMLElement', LIBXML_NOCDATA)), true);
        $data = array();
        $ext = $ret['orderid'];
        $money = $ret['payfee'];
        // echo json_encode($ret);
        if ($ret['status'] != "00000") {
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
        echo '<?xml version="1.0" encoding="UTF-8"?><callbackRsp>1</callbackRsp>';
    }

    public function notify_error()
    {
        echo '<?xml version="1.0" encoding="UTF-8"?><callbackRsp>0</callbackRsp>';
    }

    public function sign_order($game_id = '')
    {
        $appid = $this->Game_model->get_key($game_id, 'APPID');
        $cpid = $this->Game_model->get_key($game_id, 'CPID');
        $key = $this->Game_model->get_key($game_id, 'secretkey');

        $cporderid = $this->input->get('cporderid');
        $feetype = $this->input->get('feetype');
        $payfee = $this->input->get('money');
        $consumecode = $this->input->get('consumecode');
        $consumename = $this->input->get('consumename');
        $channelid = $this->input->get('cpchannelid');
        $userID = $this->session->userdata('uid');
        $cptoken = $this->input->get('cptoken');

        if ($this->cache->redis->is_supported()) {
            $userID = $this->cache->redis->get($cptoken);
        }else{
            return false;
        }

        $_data = array(
            'cporderid' => $cporderid,
            'cpid' => $cpid,
            'feetype' => $feetype,
            'payfee' => $payfee,
            'channelid' => $channelid,
            'consumecode' => $consumecode,
            'consumename' => $consumename,
            'userID' => $userID
        );
        $sign_str = "channelid=$channelid&consumecode=$consumecode&consumename=$consumename&cpid=$cpid&cporderid=$cporderid&feetype=$feetype&payfee=$payfee&userID=$userID".$key;
        log_message('debug',$this->platform.' cp sign '.$sign_str);
        // echo $sign_str;

        $sign = md5($sign_str);

        $data = array(
            'cporderid' => $cporderid,
            'cpid' => $cpid,
            'feetype' => $feetype,
            'payfee' => $payfee,
            'channelid' => $channelid,
            'consumecode' => $consumecode,
            'consumename' => $consumename,
            'userID' => $userID,
            'appid' => $appid,
            'cpsign' => $sign
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

    public function focus($game_id = '')
    {
        $appId = $this->Game_model->get_key($game_id, 'APPID');
        $userId = $this->session->userdata('uid');

        $reportData = array(
            'appId' => $appId,
            'userId' => $userId,
        );
        return $reportData;
    }
}
