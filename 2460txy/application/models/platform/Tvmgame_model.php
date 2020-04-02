<?php

class Tvmgame_model extends CI_Model
{
    public $platform = 'tvmgame';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('tvmid');
        $token = $this->input->get('token');
        $nickname = $this->input->get('nickname');
        $avatar = $this->input->get('avatar');

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
        log_message('debug', $this->platform.' get order '.$request);
        $request = json_decode($request);
        $orderId = $request->order_id;
        $ext = $request->order_id;
        $amount = $request->amount;
        log_message('debug', $this->platform.' order info  '.$orderId.' '.$ext.' '.$amount);
        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        // return $ext;
        if ($amount==$game_order->money) {
            return $ext;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo '{"status": 0}';
    }

    public function notify_error()
    {
        echo 'fail';
        ;
    }
    public function sign_order($game_id = '')
    {
        $tvmid = $this->input->get('tvmid');
        $token = $this->input->get('token');
        $order_id = $this->input->get('order_id');
        $amount = $this->input->get('amount');
        $description = $this->input->get('description');
        $callback = urldecode($this->input->get('cpcallback'));
        $redirect = urldecode($this->input->get('redirect'));
        $nonce = md5(time()+rand(0, 1000));
        ;
        $key=$this->Game_model->get_key($game_id, 'key');
        $secret=$this->Game_model->get_key($game_id, 'secret');
        $time=time();
        $sign_str = "$key$amount$callback$description$order_id$redirect$token$tvmid$nonce$time$secret";

        $sign = md5($sign_str);
        // $sign = md5("$key$amount$callback$description$order_id$redirect$token$tvmid$nonce$time$secret");


        $data = array(
            'sign'=>$sign,
            // 'sign_str'=>$sign_str,
            'time'=>$time,
            'key'=>$key,
            'nonce'=>$nonce,
            'description'=>$description
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
        $is_share = $this->input->get('isshare');
        if ($is_share) {
            $tvmid = $this->input->get('tvmid');
            $title = $this->input->get('title');
            $content = $this->input->get('content');
            $img = $this->input->get('img');
            $url = $this->input->get('url');
            $key=$this->Game_model->get_key($game_id, 'key');
            $secret=$this->Game_model->get_key($game_id, 'secret');
            $nonce = md5(time()+rand(0, 1000));
            $time=time();
            $sign_str = "$key$content$img$url$title$tvmid$nonce$time$secret";
            $sign = md5($sign_str);
            $data = array(
                'key' => $key,
                'time' => $time,
                'sign' => $sign,
                'nonce' => $nonce,
                'title' => $title,
                'desc'=>$content,
                'img'=>$img,
                'url'=>$url,
            );
            return $data;
        } else {
            $key=$this->Game_model->get_key($game_id, 'key');
            $secret=$this->Game_model->get_key($game_id, 'secret');
            $nonce = md5(time()+rand(0, 1000));
            $time=time();
            $tvmid = $this->input->get('tvmid');
            $token = $this->input->get('token');
            $game_role_id = $this->input->get('game_role_id');
            $role_name = $this->input->get('role_name');
            $role_create_time = date('Y-m-d', time());
            $sign_str = "$key$game_role_id$role_create_time$role_name$token$tvmid$nonce$time$secret";
            $sign = md5($sign_str);
            $data = array(
                'key' => $key,
                'time' => $time,
                'sign' => $sign,
                'nonce' => $nonce,
                'role_create_time' => $role_create_time,
                'role_name'=>$role_name,
                'game_role_id'=>$game_role_id,
            );

            return $data;
        }
    }
}
