<?php

class Taren_model extends CI_Model
{
    public $platform = 'taren';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('uid');
        $this->cache->save('uid', $user_id, 3600*24*7);
        // $username = $this->input->get('username');
        $time = $this->input->get('time');
        $gameid = $this->input->get('appid');
        $is_adult = $this->input->get('is_adult');
        $recharge = $this->input->get('recharge');
        $sign = $this->input->get('sign');
        $key = $this->Game_model->get_key($game_id, 'appkey');
        $sign_str = $user_id.$gameid.$time.$is_adult.$recharge.$key;
        $check_sign = md5($sign_str);
        log_message('debug', $this->platform.' sign is '.$sign.' sign_str '.$sign_str.' check sign is '.$check_sign);

        $this->session->set_userdata('userid', $user_id);
        $this->session->set_userdata('gameid', $gameid);
        $this->cache->save('gameid', $gameid, 3600*24*7);
        $this->cache->save('recharge', $recharge, 3600*24*7);
        // if ($sign!=$check_sign) {
        //     return false;
        // }

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
        $order_id = $this->input->get_post('gameorderid');
        $money = $this->input->get_post('amount');
        log_message('debug', $this->platform.' orderid is:'.$order_id.' and money is : '.$money);
        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money* 100) == $game_order->money) {
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
        echo 'success';
    }

    public function notify_error()
    {
        echo 'fail';
        ;
    }
    public function sign_order($game_id = '')
    {
        $order_id = $this->input->get('order_id');
        $user_id= $this->input->get('userId');
        $money=$this->input->get('money');
        $ext=$this->input->get('appid');
        $appid = $this->Game_model->get_key($game_id, 'appid');
        $recharge = $this->cache->get('recharge');
        $time = time();
        $reportkey = $this->Game_model->get_key($game_id, 'reportkey');
        $productid = 1000;
        if ($money/28 != 1) {
            $productid=1001;
        }
        $sign_str = "appid=$appid&gsid=0&uid=$user_id&gameorderid=$order_id&productid=$productid&amount=$money&time=$time&key=$reportkey";
        $sign = md5($sign_str);
        log_message('debug', $this->platform.' sign_str is '.$sign_str.' sign '.$sign);
        $data = array(
             'sign' => $sign,
             'time'=>$time,
             'productid'=>$productid,
             'appid'=>$appid,
             'recharge'=>$recharge,
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
        $recharge = $this->cache->get('recharge');
        $uid = $this->cache->get('uid');
        $appid = $this->Game_model->get_key($game_id, 'appid');
        $report_key =  $this->Game_model->get_key($game_id, 'reportkey');
        $response_key =  $this->Game_model->get_key($game_id, 'responsekey');
        $this->cache->save('reportkey', $report_key, 3600*24*7);
        $this->cache->save('responsekey', $response_key, 3600*24*7);
        $data = array(
            'recharge'=>$recharge,
            'appid'=>$appid,
            'reportkey'=>$report_key,
            'responsekey'=>$response_key,
            'uid'=>$uid,
        );
        return $data;
    }
}
