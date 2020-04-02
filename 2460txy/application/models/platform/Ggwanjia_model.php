<?php

class Ggwanjia_model extends CI_Model
{
    public $platform = 'ggwanjia';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $token = $this->input->get('token');
        $cpgameid = $this->Game_model->get_key($game_id, 'GAMEID');
        $appkey = $this->Game_model->get_key($game_id, 'APPKEY');
        $data = array(
            'gameToken' => $token,
        );
        $sign_str = 'gameToken=' . $token . $appkey;
        $sign = md5($sign_str);

        $url = "https://ly.ggzhushou.cn/api/v3/cp/verify_token";

        $post_data = array(
            'gameId' => $cpgameid,
            'data' => ($data),
            'sign' => $sign,
        );
        // $request = $this->Curl_model->curl_post($url, json_encode($post_data));
        $request = json_decode($this->Curl_model->curl_post($url, json_encode($post_data)));
        log_message('debug', $this->platform . ' login ' . json_encode($post_data));
        if ($request->rc != 0) {
            var_dump($request);
            return false;
        }else if(isset($request->data->accountId)){
            $user_id = $request->data->accountId;

        }else{
            echo json_encode($post_data);
            echo '<br>';
            echo json_encode($request);
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
        $request = file_get_contents("php://input");
        log_message('debug', $this->platform . ' order ' . $request.' POST '.json_encode($_POST));
        // log_message('debug', $this->platform . ' order ' . ($request));
        // $array = parse_str(json_encode($request));

        $request = json_decode($request);


        $orderId = $request->data->cpOrderId;
        $ext = $orderId;
        $money = $request->data->orderPrice;

        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;

        if ($money  == $game_order->money) {
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
        echo 'fail';

    }
    public function sign_order($game_id = '')
    {
        $amount = $this->input->get('amount');
        $server = $this->input->get('server');
        $uid = $this->input->get('uid');
        $order_id = $this->input->get('order_id');
        $gameId = $this->Game_model->get_key($game_id, 'gameid');
        $secret = $this->Game_model->get_key($game_id, 'secret');
        $time = time();
        $sign = md5("ext=$order_id" . "gameId=$gameId" . "goodsId=$amount" . "secret=$secret" . "time=$time" . "userId=$uid");
        $post_data = array(
            'gameId' => $gameId,
            'goodsId' => $amount,
            'goodsName' => '元宝',
            'userId' => $uid,
            'money' => $amount,
            'ext' => $order_id,
            'time' => $time,
            'sign' => $sign,
        );
        $response = $this->Curl_model->curl_post('http://wx.hortor.net/pay/partner', $post_data);
        $response = json_decode($response);
        $data = array(
            'order_id' => $response->order_id,
            'appid' => $gameId,
            'time' => $time,
            //     'app_id'=>$response->app_id,
            //     'timestamp'=>$response->timestamp,
            //     'nonce_str'=>$response->nonce_str,
            //     'package'=>$response->package,
            //     'sign_type'=>$response->sign_type,
            //     'pay_sign'=>$response->pay_sign,
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
        $gameid = $this->Game_model->get_key($game_id, 'gameid');
        return $gameid;
    }
}
