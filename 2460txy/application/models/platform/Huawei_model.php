<?php

class Huawei_model extends CI_Model
{
    public $platform = 'huawei';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        if ($game_id==1189) {
            // return;
        }

        $user_id = $this->input->get('playerId');

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
        $request = file_get_contents("php://input");
        log_message('debug', $this->platform.' '.$_POST.' input '.$request);
        $money = $this->input->get_post('amount');
        $ext = $this->input->get_post('extReserved');
        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');         //无平台预留字段 orderid与2460 不匹配
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (intval($money*100)==$game_order->money) {
            return $ext;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo '{"result":0}';
    }

    public function notify_error()
    {
        echo '{"result":99}';
    }
    public function sign_order($game_id = '')
    {
        $amount = $this->input->get('money');
        $order_id = $this->input->get('order_id');
        $goodsname = $this->input->get('goodsname');
        $time = time().rand(1, 1000);
        $applicationID = $this->Game_model->get_key($game_id, 'appid');
        $payid = $this->Game_model->get_key($game_id, 'payid');
        $sign_str = "amount=$amount&applicationID=$applicationID&productDesc=zs&productName=zs&requestId=$time&userID=$payid";
        // echo $sign_str;
        $this->load->library('rsa');
        // $this->rsa->_get_key($this->Game_model->get_key($game_id,'pubkey'),$this->Game_model->get_key($game_id,'prikey'));
        // $this->rsa->_config['public_key']=$this->Game_model->get_key($game_id,'pubkey');
        $this->rsa->_config['public_key']='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxHwKEtFAsM2o1M4vM206qSh4HLStma07TOBPYSLb/mL00b2v8MvcXYuISK/PGCyRXLM2s43HyE0sxR6g8ODYXwckfkpPCGTRUwpI6n7XqZmga1pF0ibUDn1CmuEzPpYWr8ayYq93TRNoHYGjjPh0arhsifGZQD3cfZH0BfZ77lvZ5ij06npoWHxvByYnP0IWHccVswFxltZdD5P5MFUlZ6ipXt3hXwH+lBS5iJHMFevLQ6dK5PQVstYuvRG9MDKRjmrmpbsAhdkJGKUB0ls0g7AAyfbFqJUz3DRd6qSXNv/XVK55i1puynfdLkYyOe8lLrRD9qfV2xyIlZ/m2jlKMwIDAQAB';
        $this->rsa->_config['private_key']=$this->Game_model->get_key($game_id, 'prikey');
        // echo $this->rsa->_config['public_key'];
        $content=$sign_str;
        if ($game_id = '1189') {
            $filename = "/var/html/2460/key/payPrivateKey.pem";
        } elseif ($game_id = '1231') {
            $filename = "/var/html/2460/key/xxczPayPrivateKey.pem";
        }

        if (!file_exists($filename)) {
            echo "{\"result\" : 1 }";
            return;
        }
        $priKey = file_get_contents($filename);
        $openssl_private_key = openssl_get_privatekey($priKey);
        openssl_sign($content, $signature, $openssl_private_key, 'sha256WithRSAEncryption');
        $sign=base64_encode($signature);
        // echo $sign;
        // $sign = $this->rsa->publicDecrypt($sign_str);
        $data = array(
            'payId'=>$payid,
            'appId'=>$applicationID,
            'sign'=>$sign,
            'time'=>$time,
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
