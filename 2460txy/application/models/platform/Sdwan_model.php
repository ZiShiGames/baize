<?php

class Sdwan_model extends CI_Model
{
    public $platform = 'sdwan';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('uid');
        $this->cache->save("uid", $user_id);
        $channel = $this->input->get('channel');
        $this->session->set_userdata('channel', $channel);
        $this->cache->save("channel", $channel, 3600*24*30);
        $openid = $this->input->get('openid');
        $this->cache->save("openid", $openid, 3600*24*30);
        $appid = $this->input->get('appid');
        $nick = $this->input->get('nick');
        $time = $this->input->get('time');
        $memo = $this->input->get('memo');
        $reurl = $this->input->get('reurl');
        $cburl = $this->input->get('cburl');
        $paydata = $this->input->get('paydata');
        $key = $this->Game_model->get_key($game_id, 'appkey');

        if (!$user_id) {
            return false;
        }

        $condition = array(
            'p_uid' => $user_id,
            'platform' => $this->platform,
        );
        // echo  $response->data->gouzaiId;

        $user = $this->User_model->get_one_by_condition_array($condition);

        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $user_id,
                'create_date' => time(),
            );

            $user_id = $this->User_model->add($user);

            if (!$user_id) {
                log_message('error', 'Login error user create fail');

                return false;
            }

            $user['user_id'] = $user_id;
        }

        // generate random token and save it to cache
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

        $condition = array('user_id' => $openId);
        $user = $this->User_model->get_one_by_condition($condition);
        if (!$user) {
            echo 'error';

            return;
        }

        $user_id = $user->p_uid;

        $openKey = $this->cache->get($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        if ($game_id == 1013) {
            $test_id = array();
            if (in_array($openId, $test_id)) {
                $game_url = 'http://122.152.194.83:8083/api';
            }
        }
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "allu login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $memo = $this->input->get_post('memo');
        $order_id = base64_decode($memo);
        $money =$this->input->get_post('amount');
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

        // return $order_id;
    }

    public function notify_ok()
    {
        echo 'success';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }

    public function sign_order($game_id = '')
    {
        $order_id = $this->input->get('order_id');
        $money = $this->input->get('money');
        $userId = $this->input->get("userId");
        $ext = $this->input->get('ext');
        $subject = $this->input->get('subject');
        $time = time();
        $gameid = $this->Game_model->get_key($game_id, 'appid');
        $gamekey = $this->Game_model->get_key($game_id, 'appkey');
        // $channel = $this->session->userdata('channel');
        $channel=$this->input->get('channel');
        $cpOrderId = "g2460u" . $userId . "m" . $money . "t" . $time;
        $memo=$this->input->get('memo');
        $wxopenid = $this->input->get('iswx');

        log_message('debug', $this->platform.' memo is '.$memo. ' and memo 64 is '.base64_encode($memo));

        $sign_data =array(
          'appId'=>$gameid,
          'accountId'=>$userId,
          'amount'=>$money,
          'call_back_url'=>"http://www.shandw.com/m/game/?gid=$gameid&channel=$channel",
          'merchant_url'=>"http://www.shandw.com/m/game/?gid=$gameid&channel=$channel",
          'subject'=>$subject,
          'channel'=>$channel,
          'timestamp'=>$time,
        //   'wxopenid'=>'',
          'cpOrderId'=>$cpOrderId,
        //   'memo'=>base64_encode($memo),

        );
        log_message('debug', $this->platform.' wxopid is '.$wxopenid.' and len is '.strlen($wxopenid));
        if ($wxopenid&&strlen($wxopenid)>=6) {
            $sign_data['wxopenid']=$wxopenid;
        }
        $this->load->model('Common_model');

        $sign_str= $this->Common_model->sort_params($sign_data);
        $sign = md5($sign_str.$gamekey);
        log_message('debug', $this->platform.' sign_str is '.$sign_str.' sign is '.$sign);
        $data = array(
            'gameid'=>$gameid,
            'sign'=>strtolower($sign),
            'key'=>$gamekey,
            'channel'=>$channel,
            'time'=>$time,
            'appid'=>$gameid,
            'uid'=>$userId,
            'memo'=>base64_encode($memo)
        );

        return $data;
    }



    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }
    public function focus($game_id)
    {
        $openid = $this->cache->get('openid');
        $channel = $this->cache->get('channel');
        // if (!$openid||!$channel) {
        //     log_message('error', $this->platform.' focus error '.$openid.' || '.$channel);
        // }
        $data = array(
                    'openid'=>$openid,
                    'channel'=>$channel,
            );
        return $data;
    }
}
