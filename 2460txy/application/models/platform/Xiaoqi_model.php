<?php

class Xiaoqi_model extends CI_Model
{
    public $platform = 'xiaoqi';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $game_key = $this->input->get('game_key');
        $stime = $this->input->get('stime');
        $ticket = $this->input->get('ticket');
        // $sign = $this->input->get('sign');
        $secret = $this->Game_model->get_key($game_id, 'Gamesecret');
        $signstr = "game_key=$game_key&login_stime=$stime&login_ticket=$ticket$secret";
        $sign = md5($signstr);
        $url ='https://hgame.x7sy.com/member/get_user_info?login_ticket='.$ticket.'&game_key='.$game_key.'&login_stime='.$stime.'&sign='.$sign;
        $response = $this->Curl_model->curl_get($url);
        $response=json_decode($response);
        $user_id = $response->userData->user_id;
        $this->session->set_userdata('uid', $user_id);
        if (!$user_id) {
            log_message('debug',$this->platform.' token timeout');
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
        $orderId = $this->input->get_post('game_orderid');
        $encryp_data = $this->input->get_post('encryp_data');
        $game_orderid = $this->input->get_post('game_orderid');
        // $game_price = $this->input->get_post('game_price');
        $game_price =intval($this->input->get_post('game_price'));

        $user_id = $this->input->get_post('user_id');
        $condition = array('u_order_id' => $orderId);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        // $game_id = $game_order->game_id;
        // $key=$this->Game_model->get_key($game_id, 'Gamekey');
        // $secret=$this->Game_model->get_key($game_id, 'Gamesecret');
        // $my_sign=md5("ext=$ext"."gameId=$gameId"."goodsId=$goodsId"."secret=$secret"."time=$time"."userId=$userId");

        if (intval($game_price*100) == $game_order->money) {
            return $orderId;
        } else {
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
        $amount = $this->input->get('amount');
        // $subject = urlencode($this->input->get('subject'));
        $subject = $this->input->get('subject');

        $server = $this->input->get('server');
        $uid = $this->input->get('uid');
        $order_id = $this->input->get('order_id');
        $key=$this->Game_model->get_key($game_id, 'Gamekey');
        $Gamesecret=$this->Game_model->get_key($game_id, 'Gamesecret');
        $time = time();
        $nowTime=time();
        $pay_obj=array(
            "description"    =>"",
            "extends_data"=>$order_id,
            "game_area"        =>"",
            "game_group"    =>"",
            "game_key"        =>$key,
            "game_level"    =>"",
            "game_orderid"=>$order_id,
            "game_price"    =>$amount.'.00',
            "game_role_id"=>"",
            "notify_id"        =>-1,
            "stime"                =>$nowTime,
            "subject"            =>$amount.'zs',
            "user_id"            =>$uid
        );
        $sign=$this->get_sign($pay_obj, $Gamesecret);
        // $sign=md5("description=&game_area=&game_group=&game_key=$key&game_level=&game_orderid=$order_id&game_price=$amount&game_role_id=&notify_id=-1&stime=$time&subject=$subject&user_id=$uid$Gamesecret");
        $pay_obj['pay_sign']=$sign;
        // echo $sign;


        // $sign=md5("encryp_data=5978ef48deccb5038083fb503d49cafd&extends_data=这是扩展字段&game_area=最强大的游戏 8 区&game_group=用户游戏所在服 &game_orderid=201803201714595415&game_price=0.01&game_role_id= 最 强 之 敌 --1000 级 &subject= 最 强 & 角 *& 色 @&user_id=79757&xiao7_goid=29667A569D7DDD19E800B1479713E3EAD0A80");
        $data = array(
            'gamekey'=>$key,
            'sign'=>$sign,
            'time'=>$time,
            'pay_obj'=>json_encode($pay_obj),
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
    public function get_sign($data, $game_secret)
    {
        // unset($data["sign"], $data["sign_data"], $data["role_sign"]);
        ksort($data, SORT_STRING);
        return md5($this->http_build_query_noencode($data).$game_secret);
    }
    public function http_build_query_noencode($query_arr)
    {
        // echo json_encode($query_arr);
        if (empty($query_arr)) {
            return "";
        }
        $return_arr=array_map(function ($key) use ($query_arr) {
            return $key."=".$query_arr[$key];
        }, array_keys($query_arr));
        return implode("&", $return_arr);
    }
}
