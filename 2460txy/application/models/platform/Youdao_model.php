<?php

class Youdao_model extends CI_Model
{
    public $platform = 'youdao';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $gameid = $this->input->get('gameid');
        $channel = $this->input->get('channel');
        $user_id = $this->input->get('openid');
        $nickName = $this->input->get('nickname');
        $channel_id = $this->Game_model->get_key($game_id, 'id');
        $this->cache->save('channel', $channel, 3600*7*24);
        $this->cache->save('gameid', $gameid, 3600*7*24);
        $this->cache->save('user_id', $user_id, 3600*7*24);
        $this->cache->save('nickname', $nickName, 3600*7*24);
        // $key_url = 'http://wx.game.idian.cn/cps/wechat/api/getKey?identify='.$channel_id;
        // $contnet = $this->Curl_model->curl_get($key_url);
        // if (!$content) {
        //     log_message('error', $this->platform." Login empty content $key_url");
        //
        //     return false;
        // }
        // log_message('error', $this->platform." content is $content");

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
        $order_id = $this->input->get_post('out_trade_no');
        $money = $this->input->get_post('total_fee');
        log_message('debug', $this->platform.' orderid is:'.$order_id.' and money is : '.$money);
        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money) == $game_order->money) {
                    return $order_id;
                } else {
                    log_message('debug', $this->platform.' get_order_id money errory '.$game_order->money." != $money");
                }
            } else {
                log_message('debug', $this->platform.' get_order_id error order not found by '.$order_id);
            }
        } else {
            log_message('debug', $this->platform.' get_order_id error order_id or money null');
        }
        return false;
    }

    public function notify_ok()
    {
        echo '{"code":"success"}';
    }

    public function notify_error()
    {
        echo 'fail';
        ;
    }
    public function sign_order($game_id = '')
    {
        $identify = $this->Game_model->get_key($game_id, 'id');
        $key_url = 'http://wx.game.idian.cn/cps/wechat/api/getKey?identify='.$identify;
        $content = $this->Curl_model->curl_get($key_url);
        $content = json_decode($content);
        // log_message('debug', $this->platform.' content is '. $content);
        $identify_key = $content->key;
        log_message('debug', $this->platform.' content is '. $identify_key);
        $identify_id= $this->Game_model->get_key($game_id, 'id');
        $serect = $this->Game_model->get_key($game_id, 'key');
        $token = $this->getToken($identify_id, $serect);
        $data = array(
                'identify_id' => $identify_id,
                'identify_key' => $identify_key,
                'token' => $token,
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
        $channel = $this->cache->get('channel');
        $gameid = $this->cache->get('gameid');
        $user_id = $this->cache->get('user_id');
        $nickname = $this->cache->get('nickname');
        $data = array(
                'channel'=>$channel,
                'gameid'=>$gameid,
                'user_id'=>$user_id,
                'nickname'=>$nickname,
        );
        return $data;
    }
    public function getToken($identify, $serect)
    {
        log_message('debug', $this->platform.' get in token');
        $token = "";
        if (empty($identify)) {
            return $token;
        }
        if (empty($serect)) {
            return $token;
        }
        $minute = date("i", time());
        if ($minute < 29) {
            $sta = date("Y-m-d H:00");
            $end = date("Y-m-d H:30");
        } else {
            $sta = date("Y-m-d H:30", time());
            $end = date("Y-m-d H:00", time()+60*60);
        }
        //这里的~左右各有一个空格
        $strtime = $sta." ~ ".$end;
        log_message('debug', $this->platform.' get in token strtime'.$strtime);
        //这里的获取key路径请以上面文档提供的路径为准，以下仅供参考使用
        // $url = "http://wx.game.idian.cn/cps/wechat/api/getKey?identify=".$identify;
        // $content = $this->Curl_model->curl_get($url);
        // // $result = json_decode(NetUtility::request_get($url), true);
        // $result=json_decode($content);
        // log_message('debug', $this->platform.' get in token result'.$result);
        // if (!empty($result) && $result["code"] == "200") {
        //     $key = $result["key"];
        //     log_message('debug', $this->platform.' get in token in 200');
        // } else {
        //     log_message('debug', $this->platform.' get in token in return');
        //     return $token;
        // }

        $key_url = 'http://wx.game.idian.cn/cps/wechat/api/getKey?identify='.$identify;
        $content = $this->Curl_model->curl_get($key_url);
        $content = json_decode($content);
        $identify_key = $content->key;
        $str = $identify.";".$strtime.";".$identify_key.";".$serect;
        $token = strtolower(md5(base64_encode($str), false));
        log_message('debug', $this->platform.' get in token '.$token);
        return $token;
    }
}
