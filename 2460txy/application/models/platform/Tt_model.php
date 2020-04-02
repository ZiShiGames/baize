<?php

class Tt_model extends CI_Model
{
    public $platform = 'tt';
    public $key = 'F42BA6B8-A091-4AF0-87CC-418E4E76C9ED';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login()
    {
        $userId = $this->input->get('openid');
        $openkey = $this->input->get('token');
        $headimg = $this->input->get('headimg');
        $nickname = $this->input->get('nickname');

        if (!$userId || !$openkey || !$headimg || !$nickname) {
            return false;
        }

        $condition = array(
                    'p_uid' => $userId,
                    'platform' => $this->platform,
                );

        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                    'platform' => $this->platform,
                    'p_uid' => $userId,
                    'nickname' => $nickname,
                    'avatar' => $headimg,
                    'create_date' => time(),
                );
            $user_id = $this->User_model->add($user);
            if (!$user_id) {
                log_message('error', "Login error user create $content");

                return false;
            }

            $user['user_id'] = $user_id;
        }

        // generate random token and save it to cache
        $this->cache->save($user['user_id'].'_token', $openkey, 60 * 60 * 24);

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
                    'game_name' => '天团',
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
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign";
        log_message('debug', "allu login:$url");

        header("Location: $url");
        // if ($serverId>10000) {
        //     $game_url = $game->game_login_url;
        //     $serverId = $serverId - 10000;
        // } else {
        //     $game_url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/login/juhe';
        // }
        // $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        // log_message('debug', "allu login:$url");
        //
        // header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get('cpordernum');
        if (!$order_id) {
            return;
        }

        $money = $this->input->get_post('amount');

        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money) == $game_order->money) {
                    return $order_id;
                } else {
                    echo 1;
                    log_message('debug', $this->platform.' get_order_id money errory '.$game_order->money." != $money");
                }
            } else {
                echo 2;
                log_message('debug', $this->platform.' get_order_id error order not found by '.$order_id);
            }
        } else {
            echo 3;
            log_message('debug', $this->platform.' get_order_id error order_id or money null');
        }
    }

    public function notify_ok()
    {
        $res = array(
            'code' => 1000,
            'msg' => '成功',
            'sign' => strtoupper(md5('1000|'.$this->key)),
        );
        echo json_encode($res);
    }

    public function notify_error()
    {
        $res = array(
                'code' => 0,
                'msg' => '成功',
            );
        echo json_encode($res);
    }

    public function focus()
    {
        return false;
    }

    public function sign_order($game_id = '')
    {
        $openId = $this->input->get('openId');

        $data = $this->cache->get($openId.'_yyb_info');

        return $data;
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }
}
