<?php

class Orange_model extends CI_Model
{
    public $platform = 'orange';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        return;
        $user_id = $this->input->get('qqesuid');
        $this->session->set_userdata('qqesuid', $user_id);

        $loginType = $this->input->get('loginType');
        $this->session->set_userdata('loginType', $loginType);
        $channelid = $this->input->get('channelid');
        $this->session->set_userdata('channelid', $channelid);
        $channeluid = $this->input->get('channeluid');
        $this->session->set_userdata('channeluid', $channeluid);
        $qqesnickname = $this->input->get('qqesnickname');
        $this->session->set_userdata('qqesnickname', $qqesnickname);
        $qqesavatar = $this->input->get('qqesavatar');
        $this->session->set_userdata('qqesavatar', $qqesavatar);
        $cpgameid = $this->input->get('cpgameid');
        $this->session->set_userdata('cpgameid', $cpgameid);
        $ext = $this->input->get('ext');
        $this->session->set_userdata('ext', $ext);
        $qqestimestamp = $this->input->get('qqestimestamp');
        $this->session->set_userdata('qqestimestamp', $qqestimestamp);
        $sign = $this->input->get('sign');
        $this->session->set_userdata('sign', $sign);


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
        $order_id = $this->input->get_post('cp_order');
        $money = $this->input->get_post('fee');
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
        $user_id = $this->session->userdata('qqesuid');
        $channelid = $this->session->userdata('channelid');
        $channeluid = $this->session->userdata('channeluid');
        $qqesnickname = $this->session->userdata('qqesnickname');
        $qqesavatar = $this->session->userdata('qqesavatar');
        $cpgameid = $this->session->userdata('cpgameid');
        $ext = $this->session->userdata('ext');
        $qqestimestamp = $this->session->userdata('qqestimestamp');
        $sign = $this->session->userdata('sign');
        $order = $this->input->get('order');
        $goodsname = $this->input->get('goodsname');
        $fee = $this->input->get('fee');
        $appid = $this->Game_model->get_key($game_id, 'id');
        $key = $this->Game_model->get_key($game_id, 'key');
        $time = time();
        $sign_data = array(
            'order'=>$order,
            'cpgameid'=>$cpgameid,
            'qqesuid'=>$user_id,
            'channelid'=>$channelid,
            'channeluid'=>$channeluid,
            'cpguid'=>$appid,
            'goodsname'=>$goodsname,
            'fee'=>$fee,
            // 'ext'=>json_encode($ext),
            'ext'=>$ext,
            'timestamp'=>$time,
        );
        $this->load->model('Common_model');
        $sign_str = $this->Common_model->sort_params($sign_data);
        $sign_str=$sign_str.'&'.$key;
        // echo $sign_str;
        $sign = md5($sign_str);
        $data = array(
            'sign'=>$sign,
            'cpgameid'=>$cpgameid,
            'time'=>$time,
            'channeluid'=>$channeluid,
            'cpguid'=>$appid,
            'channelid'=>$channelid,
            'qqesuid'=>$user_id,
            'ext'=>$ext,
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
        $user_id = $this->session->userdata('qqesuid');
        $channelid = $this->session->userdata('channelid');
        $channeluid = $this->session->userdata('channeluid');
        $qqesnickname = $this->session->userdata('qqesnickname');
        $qqesavatar = $this->session->userdata('qqesavatar');
        $cpgameid = $this->session->userdata('cpgameid');
        $loginType = $this->session->userdata('loginType');
        $ext = $this->session->userdata('ext');
        $qqestimestamp = $this->session->userdata('qqestimestamp');
        $sign = $this->session->userdata('sign');
        $user_data = array(
            'qqesuid'=>$user_id,
            'channelid'=>$channelid,
            'channeluid'=>$channeluid,
            'qqesnickname'=>$qqesnickname,
            'qqesavatar'=>$qqesavatar,
            'loginType'=>$loginType,
            'cpgameid'=>$cpgameid,
            'ext'=>$ext,
            'qqestimestamp'=>$qqestimestamp,
            'sign'=>$sign,
        );

        $data = array(
            'user_data'=>json_encode($user_data),
        );
        return $data;
    }
}
