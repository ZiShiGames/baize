<?php

class Wanfeng_model extends CI_Model
{
    public $platform = 'wanfeng';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $token = $this->input->get('token');
        $cp_game_id = $this->Game_model->get_key($game_id, 'CPgameid');
        $cp_game_key = $this->Game_model->get_key($game_id, 'CPgamekey');

        $time = date("YmdHis", time());
        $sign_str = "gameId=$cp_game_id"."&timestamp=$time"."&key=$cp_game_key";
        $sign = strtoupper(md5($sign_str));

        $data = array(
            "gameId"=>$cp_game_id,
            "timestamp"=>$time,
            "sign"=>$sign
        );

        $header = array(
            "Content-Type"=> 'application/json',
            "Authorization"=>  $token,
            "App-Channel"=> '100000',
            "App-Version"=> '3.0.0.0'
        );

        $data = json_encode($data);

        $response = $this->Curl_model->curl_post('http://uic-api.beeplay123.com/uic/api/user/sdk/getUserInfo', $data, $header);
        if ($response) {
            $response = json_decode($response);
            if ($response->code == 200) {
                $user_id = $response->data->openId;
                $nickname = $response->data->nickname;
                $this->session->set_userdata('user_id', $user_id);
                $this->session->set_userdata('nickname', $nickname);
            } else {
                echo 'err';
                echo json_encode($response);
                echo json_decode($response);

                return false;
            }
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
        $request = file_get_contents("php://input");
        log_message('debug', $this->platform.' get order '.$request);
        log_message('debug', $this->platform.' my money '.json_encode($_POST));
        $request = json_decode($request);
        $payStatus = $request->payStatus;
        // $payStatus = $this->input->get_post('payStatus');
        // $platOrderno = $this->input->get_post('platOrderno');
        // $money = $this->input->get_post('price');
        // $sign = $this->input->get_post('sign');
        // $timestamp = $this->input->get_post('timestamp');
        if ($payStatus!= 8) {
            return false;
        }
        // $gameOrderno = $this->input->get_post('gameOrderno');
        $gameOrderno = $request->gameOrderno;
        $money = $request->price;

        $condition = array('u_order_id' => $gameOrderno);
        $this->load->model('Game_order_model');

        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        $key = $this->Game_model->get_key($game_id, 'CPgamekey');
        log_message('debug', $this->platform.' my money '.$money.' '.$game_order->money);
        if ($money*100==$game_order->money) {
            return $gameOrderno;
        } else {
            return false;
        }


        // $sign_str ="gameOrderno=$gameOrderno&payStatus=$payStatus&platOrderno=$platOrderno&price=$price&timestamp=$timestamp&key=$key";
        // $my_sign = strtoupper(md5($sign_str));
        // log_message('debug', "$sign_str $my_sign $sign");
        //
        // if ($sign==$my_sign) {
        //     return true;
        // } else {
        //     return false;
        // }
    }

    public function notify_ok()
    {
        echo '{"code":1,"message":"成功"}';
    }

    public function notify_error()
    {
        echo 'fail';
    }
    public function sign_order($game_id = '')
    {
        $time = date("YmdHis", time());
        $price = $this->input->get('price');
        $openId = $this->input->get('openId');
        $gameArea = $this->input->get('gameArea');
        $gameProp = $this->input->get('gameProp');
        $gameRoleId = $this->input->get('gameRoleId');
        $gameOrderno = $this->input->get('gameOrderno');
        $cp_game_id = $this->Game_model->get_key($game_id, 'CPgameid');
        $cp_game_key = $this->Game_model->get_key($game_id, 'CPgamekey');

        $nickname = $this->session->userdata('nickname');

        $sign = strtoupper(md5("gameId=$cp_game_id"."&gameOrderno=$gameOrderno"."&openId=$openId"."&price=$price"."&timestamp=$time"."&key=$cp_game_key"));

        $data = array(
            'price'=>$price,
            'gameId'=>$cp_game_id,
            'gameArea'=>$gameArea,
            'gameProp'=>$gameProp,
            'gameRoleId'=>$gameRoleId,
            'timestamp'=>$time,
            'sign'=>$sign
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
        $gameId= $this->Game_model->get_key($game_id, 'CPgameid');
        $openId = $this->session->userdata('user_id');

        $data = array(
            'gameId'=>$gameId,
            'openId'=>$openId
        );

        return $data;
    }
}
