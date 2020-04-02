<?php

class Tianqi_model extends CI_Model
{
    public $platform = 'tianqi';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('mem_id');
        $this->session->set_userdata('mem_id', $user_id);
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
        // $request = file_get_contents("php://input");
        // if ($request) {
        //     log_message('debug', $this->platform.' my money '.$request);
        // }else{
        //
        // }
        log_message('debug', $this->platform.' my money '.json_encode($_POST));
        $money = $this->input->get_post('money');
        $custom_info = $this->input->get_post('attach');
        $condition = array('u_order_id' => $custom_info);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        log_message('debug', $this->platform.' my money '.$money.' '.$game_order->money);
        if ($money*100==$game_order->money) {
            return $custom_info;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAIL';
        ;
    }
    public function sign_order($game_id = '')
    {
        $order_id = $this->input->get('order_id');
        $money = $this->input->get('money');
        $cproleid = $this->input->get('cproleid');
        $cprolename = $this->input->get('cprolename');
        $srid = $this->input->get('srid');
        $goodsName = $this->input->get('goodsName');
        $openId = $this->input->get('openId');
        $channel_id=$this->Game_model->get_key($game_id, 'CPchannelld');
        $CPgameid=$this->Game_model->get_key($game_id, 'CPgameid');
        $key=$this->Game_model->get_key($game_id, 'CPsecretkey');

        $sign = md5($openId.$channel_id.$CPgameid.$order_id.$money.$srid.$key);

        $data = array(
            'goods_name'=>$goodsName,
            'account'=>$openId,
            'channel_id'=>$channel_id,
            'game_id'=>$CPgameid,
            'server_id'=>$srid,
            'game_order'=>$order_id,
            'money'=>$money,
            'role_id'=>$cproleid,
            'role_name'=>$cprolename,
            'sign'=>$sign,
        );
        $response = json_decode($this->Curl_model->curl_post('http://h5.zqgame.com/createOrder', $data));
        if (isset($response->status) && $response->status ==200) {
            return $response;
        } else {
            echo json_encode($response);
            return;
        }
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
        $url = "http://sdk.4cgame.com/api/character.php?h5=1";
        $_data = array();
        $_data['uid'] = $data['user_id'];
        $_data['nonce'] = '';
        $_data['time'] = $data['create_date'];
        $_data['serverid'] = $data['server_id'];
        $_data['server_name'] = $data['server_id'];
        $_data['roleid'] = $data['cproleid'];
        $_data['role_name'] = $data['nickname'];
        $_data['level'] = $data['level'];
        // $this->load->model("Common_model");
        // $sign_str = $this->Common_model->sort_params($_data);


        $CPappid = $this->Game_model->get_key($data['game_id'], 'CPappid');
        $CPappkey= $this->Game_model->get_key($data['game_id'], 'CPappkey');
        // $mem_id = $this->session->userdata('mem_id');
        $mem_id = $this->input->get('memid');
        // $sign_str = $CPappid.$mem_id.$_data['serverid'].$_data['serverid'].$_data['role_name'].$_data['roleid'].''.$_data['level'];
        $sign_str = "app_id=$CPappid&mem_id=$mem_id&server=".$_data['serverid']."&server_id=".$_data['serverid']."&role=".$_data['role_name']."&role_id=".$_data['roleid']."&guild=无&level=".$_data['level'];
        $this->load->model('Common_model');
        $sdata = array(
            'app_id' => $CPappid,
            'mem_id' => $mem_id,
            'server' => $_data['serverid'],
            'server_id' => $_data['serverid'],
            'role' => $_data['role_name'],
            'role_id' => $_data['roleid'],
            'guild' => "无",
            'level' => $_data['level'],
        );
        // $sign_str = $this->Common_model->sort_params($sdata);
        $sign=md5($sign_str);
        $d=array();
        $d['app_id']=$CPappid;
        $d['mem_id']=$mem_id;
        $d['server']=$_data['serverid'];
        $d['server_id']=$_data['serverid'];

        $d['role']=$_data['role_name'];
        $d['role_id']=$_data['roleid'];
        $d['guild']='无';
        $d['level']=$_data['level'];
        $d['sign']=$sign;

        // $response = $this->Curl_model->curl_post($url, json_encode($d, JSON_UNESCAPED_UNICODE));
        $response = $this->Curl_model->curl_post($url, $d);
        log_message('debug', $this->platform.' update '.$response.'  json '.json_encode($d, JSON_UNESCAPED_UNICODE).' sign str '.$sign_str);
    }
    public function focus($game_id='')
    {
        $gameid= $this->Game_model->get_key($game_id, 'gameid');
        return $gameid;
    }
    private function utf8_str_to_unicode($utf8_str)
    {
        $unicode = 0;
        $unicode = (ord($utf8_str[0]) & 0x1F) << 12;
        $unicode |= (ord($utf8_str[1]) & 0x3F) << 6;
        $unicode |= (ord($utf8_str[2]) & 0x3F);
        return dechex($unicode);
    }
}
