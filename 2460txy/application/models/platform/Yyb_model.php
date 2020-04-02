<?php

class Yyb_model extends CI_Model
{
    public $platform = 'yyb';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login()
    {
        $userId = $this->input->get('openid');
        $openkey = $this->input->get('openkey');
        $pf = $this->input->get('pf');
        $pfkey = $this->input->get('pfkey');
        $offerid = $this->input->get('offerid');
        if (!$userId || !$openkey || !$pf || !$pfkey || !$offerid) {
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
                    'nickname' => '',
                    'avatar' => '',
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
        $this->cache->save($user['user_id'].'_token', md5($user['user_id'].$user['platform'].time()), 86400);

        $data = array(
            'openid' => $userId,
            'openkey' => $openkey,
            'pf' => $pf,
            'pfkey' => $pfkey,
            'offerid' => $offerid,
        );
        $this->cache->save($user['user_id'].'_yyb_info', $data, 60 * 60 * 24);

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
        // return;
        $order_id = $this->input->get('order_id');
        $ret = $this->input->get('ret');
        if (!$order_id||$ret!=0) {
            return;
        }
        log_message('debug', 'yyb get_order_id '.json_encode($_GET));
        return $order_id;
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAILED';
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
