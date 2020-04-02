<?php

class Threegame_model extends CI_Model
{
    public $platform = 'threegame';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('uid');
        $gameid = $this->input->get('gameid');
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
        log_message('debug', $this->platform.' get order '.json_encode($_POST));
        $status = $this->input->get_post('status');
        $cpOrderId = $this->input->get_post('cpOrderId');
        $orderId = $this->input->get_post('orderId');
        $uid = $this->input->get_post('uid');
        $userName = $this->input->get_post('userName');
        $money = $this->input->get_post('money');
        $gameId = $this->input->get_post('gameId');
        $goodsId = $this->input->get_post('goodsId');
        $goodsName = $this->input->get_post('goodsName');
        $server = $this->input->get_post('server');
        $role = $this->input->get_post('role');
        $time = $this->input->get_post('time');
        $ext = $this->input->get_post('ext');
        $sign = $this->input->get_post('sign');
        $signType = $this->input->get_post('signType');


        $condition = array('u_order_id' => $cpOrderId);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        $key=$this->Game_model->get_key($game_id, 'paykey');
        $my_sign=MD5("cpOrderId=$cpOrderId&gameId=$gameId&goodsId=$goodsId&goodsName=$goodsName&money=$money&orderId=$orderId&role=$role&server=$server&status=$status&time=$time&uid=$uid&userName=$userName&key=$key");
        // echo $my_sign;
        // echo '<br>'.$sign;
        if (intval($money*100)!=$game_order->money) {
            return;
        }
        if ($sign==$my_sign) {
            return $cpOrderId;
        } else {
            echo 'sign error';
            return false;
        }
    }

    public function notify_ok()
    {
        echo 'success';
    }

    public function notify_error()
    {
        echo 'fail';
        ;
    }
    public function sign_order($game_id = '')
    {
        $gameId = $this->input->get('gameid');
        $order_id = $this->input->get('order_id');
        $goodsId = $this->input->get('goodsId');
        $goodsName = $this->input->get('goodsName');
        $money = $this->input->get('money');
        $role = $this->input->get('role');
        $server = $this->input->get('server');
        $uid = $this->input->get('uid');

        $key=$this->Game_model->get_key($game_id, 'paykey');
        $time = time();
        $sign=MD5("cpOrderId=$order_id&gameId=$gameId&goodsId=$goodsId&goodsName=$goodsName&money=$money&role=$role&server=$server&time=$time&uid=$uid&key=$key");
        $data = array(
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
