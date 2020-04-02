<?php

class Baiduduoku_model extends CI_Model
{
    public $platform = 'baiduduoku';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'redis', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $token = $this->input->get('accessToken');
        $appid = $this->Game_model->get_key($game_id, 'appid');
        $SecretKey = $this->Game_model->get_key($game_id, 'secret');
        $Timestamp = time();
        $sign = MD5($appid.$Timestamp.$token.$SecretKey);
        $requery_url = "http://querysdkapi.iduoku.cn/query/h5cploginstatequery?AppID=$appid&Timestamp=$Timestamp&AccessToken=$token&Sign=$sign";
        $curl_data = array(
            'AppID' => $appid,
            'Timestamp' => $Timestamp,
            'AccessToken' => $token,
            'Sign' => $sign,
        );
        $response = json_decode($this->Curl_model->curl_get($requery_url));
        if (!isset($response->ResultCode)||$response->ResultCode!=0) {
            log_message('debug', $this->platform.' resulecode is '.$response->ResultCode);
            return;
        }
        $user_info = json_decode(base64_decode(urldecode($response->Content)));
        $user_id = $user_info->UID;
        if (!$user_id) {
            return false;
        }
        $this->cache->save($token.'UserName', $user_info->UserName,3600);
        $this->cache->save($token.'UID', $user_info->UID,3600);
        $this->cache->save($token.'AccessToken', $user_info->AccessToken,3600);
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

        log_message('debug',$this->platform.' notify post: '.json_encode($_POST) );
        $AppID = $this->input->get_post('AppID');
        $this->session->set_userdata('notify_appid',$AppID);
        $OrderSerial = $this->input->get_post('OrderSerial');
        $CooperatorOrderSerial = $this->input->get_post('CooperatorOrderSerial');
        $Sign = $this->input->get_post('Sign');
        $Content = $this->input->get_post('Content');
        $order_info = json_decode(base64_decode(urldecode($Content)));
        $condition = array('u_order_id' => $CooperatorOrderSerial);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        $secret=$this->Game_model->get_key($game_id, 'secret');
        $my_sign=md5("$AppID$OrderSerial$CooperatorOrderSerial$Content$secret");
        // echo $my_sign.' '.$Sign;
        if ($Sign==$my_sign) {
            return $CooperatorOrderSerial;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        $appid = $this->session->userdata('notify_appid');
        $sign = $this->session->userdata('notify_sign');
        echo '{AppID:"'.$appid.'",ResultCode:"1",ResultMsg:"success",Sign:"'.$sign.'",Content:""}';
    }

    public function notify_error()
    {
        $appid = $this->session->userdata('notify_appid');
        $sign = $this->session->userdata('notify_sign');
        echo '{AppID:"'.$appid.'",ResultCode:"2",ResultMsg:"error",Sign:"'.$sign.'",Content:""}';
    }
    public function sign_order($game_id = '')
    {
        $AppID = $this->Game_model->get_key($game_id, 'appid');
        $secretKey = $this->Game_model->get_key($game_id, 'secret');
        $OrderID = $this->input->get('order_id');
        $Product = $this->input->get('goodsName');
        $Money = $this->input->get('money');
        $token = $this->input->get('token');
        $UID = $this->cache->get($token.'UID');
        $Channel = '';
        $UserName = $this->cache->get($token.'UserName');
        $userId = $this->cache->get($token.'AccessToken');
        $Timestamp = time();

        $sign = md5($userId.$AppID.$secretKey.$Timestamp);

        $this->session->set_userdata('notify_sign',$sign);
        $data = array(
            'AppID'=>$AppID,
            'OrderID'=>$OrderID,
            'Product'=>$Product,
            'Money'=>$Money,
            'UID'=>$UID,
            'Channel'=>$Channel,
            'UserName'=>$UserName,
            'AccessToken'=>$userId,
            'Timestamp'=>$Timestamp,
            'Signature'=>$sign,

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
