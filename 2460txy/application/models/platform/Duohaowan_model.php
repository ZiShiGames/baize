<?php

class Duohaowan_model extends CI_Model
{
    public $platform = 'duohaowan';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('uid');

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
        $game_key = 'game2key';  //游戏key,从运营方获取来修改这里

        $data['amount']=$_GET['amount'];
        $data['channel_source']=$_GET['channel_source'];
        $data['game_appid']=$_GET['game_appid'];
        $data['out_trade_no']=$_GET['out_trade_no'];
        $data['payplatform2cp']=$_GET['payplatform2cp'];
        $data['trade_no']=$_GET['trade_no'];
        $trade_no=$_GET['trade_no'];
        $out_trade_no = $_GET['out_trade_no'];
        $condition = array('u_order_id' => $trade_no);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        $game_key = $this->Game_model->get_key($game_id, "key");
        ksort($data);//字典排序
        $sign = MD5(http_build_query($data).$game_key);
        if ($sign==$_GET['sign']) {
            if ($_GET['amount']==$game_order->money) {
                return $trade_no;
            } else {
                return json_encode(['status'=>'fail2']);
            }
        } else {
            return false;
        }



        // $orderId = $this->input->get_post('orderId');
        // $userId = $this->input->get_post('userId');
        // $goodsId = $this->input->get_post('goodsId');
        // $ext = $this->input->get_post('ext');
        // $time = $this->input->get_post('time');
        // $sign = $this->input->get_post('sign');
        //
        // $gameId=$this->Game_model->get_key($game_id, 'gameid');
        // $secret=$this->Game_model->get_key($game_id, 'secret');
        // $my_sign=md5("ext=$ext"."gameId=$gameId"."goodsId=$goodsId"."secret=$secret"."time=$time"."userId=$userId");
        //
        // if ($sign==$my_sign) {
        //     return $ext;
        // } else {
        //     return false;
        // }
    }

    public function notify_ok()
    {
        echo json_encode(['status'=>'success']);
    }

    public function notify_error()
    {
        echo json_encode(['status'=>'fail1']);
        ;
    }
    public function sign_order($game_id = '')
    {
        $spen_data=array();
        $pay_api = "http://www.duohw.cn/mobile.php/Game/paysdk/?";
        $game_key =$this->Game_model->get_key($game_id, 'key');
        $game_appid =$this->Game_model->get_key($game_id, 'appid');
        $spen_data['par']['amount']=$this->input->get('money');
        ;//金额，支付单位是分，所以需要乘以100.
        $spen_data['par']['channelExt']=$this->input->get('channelExt'); //登录时平台方返回
        $spen_data['par']['game_appid']=$game_appid;//game_appld 平台方获取
        $spen_data['par']['props_name']=urldecode($this->input->get('goodsName'));          //道具名称
        $spen_data['par']['trade_no']=$this->input->get('order_id');//cp订单号 cp生成
        $spen_data['par']['user_id']=$this->input->get('userId');//平台方用户id
        ksort($spen_data['par']);//字典排序
        $spen_data['par']['sign']       = MD5(urldecode(http_build_query($spen_data['par']).$game_key)); //key 平台方获取
        $spen_data['url']=$pay_api.http_build_query($spen_data['par']);//
        $data = array(
            'par'=>$spen_data['par'],
            'url'=>$spen_data['url'],
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
