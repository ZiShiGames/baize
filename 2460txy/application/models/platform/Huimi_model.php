<?php

class Huimi_model extends CI_Model
{
    public $platform = 'huimi';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('uid');
        $this->session->set_userdata('huimiuid',$user_id);


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
        log_message('debug', $this->platform.' my money '.json_encode($_POST));
        $money = $this->input->get_post('money');
        $custom_info = $this->input->get_post('order');
        $pay_code = $this->input->get_post('pay_code');
        if ($pay_code!=1) {
            return false;
        }
        $condition = array('u_order_id' => $custom_info);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        log_message('debug', $this->platform.' my money '.$money.' '.$game_order->money);
        if ($money==$game_order->money) {
            return $custom_info;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo '1';
    }

    public function notify_error()
    {
        echo '0';
        ;
    }
    public function sign_order($game_id = '')
    {
        $order_id = $this->input->get('order_id');
        $money = $this->input->get('money');
        $openId = $this->input->get('openId');

        // $userId = $this->session->userdata('huimiuid');
        $userId = $this->input->get('userId');
        $goodsName = $this->input->get('goodsName');
        $serverid = $this->input->get('serverid');
        $cproleid = $this->input->get('cproleid');
        $cpNickname = $this->input->get('cpNickname');
        $cpLevel = $this->input->get('cpLevel');
        $cpext = $this->input->get('cpext');

        $data['uid'] = $userId;
        $data['ext'] = $cpext;
        $data['nonce'] = '';
        $data['time'] = time();
        $data['serverid'] = $serverid;
        $data['server_name'] = $serverid;
        $data['roleid'] = $cproleid;
        $data['role_name'] = $cpNickname;
        $data['money'] = $money;
        $data['propsname'] = $goodsName;
        $data['order'] = $order_id;
        $data['token'] = $order_id;
        $this->load->model('Common_model');
        $sign_str = $this->Common_model->sort_params($data);
        $key = $this->Game_model->get_key($game_id, 'CPgamekey');
        $CPgameid = $this->Game_model->get_key($game_id, 'CPgameid');
        $sign_str = $sign_str.$key;

        $sign = md5($sign_str);
        log_message('debug', $this->platform.' signorder signstr '.$sign_str.' sign '.$sign);
        $data['sign'] = $sign;
        $url = "http://api.sooyooj.com/index/pay/$CPgameid?".http_build_query($data);

        // $response = $this->Curl_model->curl_get($url);
        // log_message('debug', $this->platform.' signorder res '.$response);

        return $url;
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
        $_data = array();
        // $_data['uid'] = $data['user_id'];
        // $_data['uid'] = $this->session->userdata('huimiuid');
        $_data['uid'] = $this->input->get('huimiuid');
        $_data['nonce'] = '';
        $_data['time'] = $data['create_date'];
        $_data['serverid'] = $data['server_id'];
        $_data['server_name'] = $data['server_id'];
        $_data['roleid'] = $data['cproleid'];
        $_data['role_name'] = $data['nickname'];
        $_data['level'] = $data['level'];
        $this->load->model("Common_model");
        $sign_str = $this->Common_model->sort_params($_data);
        $CPgamekey = $this->Game_model->get_key($data['game_id'], 'CPgamekey');
        $sign_str = $sign_str.$CPgamekey;
        $sign=md5($sign_str);
        $_data['sign']=$sign;
        log_message('debug', $this->platform.' login sign str '.$sign_str.' sign '.$sign);
        if($data['game_id'] == 1432){
            // 汇米 超梦的逆袭
            $url = "http://api.sooyooj.com/index/role/51";
        }else{
            $url = "http://api.sooyooj.com/index/role/38";
        }
        $url = "$url?".http_build_query($_data);
        log_message('debug', $this->platform.' url '.$url);
        $this->Curl_model->curl_get($url);
    }
    public function focus($game_id='')
    {
        $gameid= $this->Game_model->get_key($game_id, 'gameid');
        return $gameid;
    }
}
