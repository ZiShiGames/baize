<?php

class Yueyouhuyu_model extends CI_Model
{
    public $platform = 'yueyouhuyu';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('user_id');



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
        if ($_POST) {
            log_message('debug', $this->platform.' my money '.json_encode($_POST));
        } elseif ($_GET) {
            log_message('debug', $this->platform.' my money '.json_encode($_GET));
        }

        $money = $this->input->get_post('amount');
        $custom_info = $this->input->get_post('trade_no');
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
        echo '{"status":"success"}';
    }

    public function notify_error()
    {
        echo '{"status":"fail"}';
        ;
    }
    public function sign_order($game_id = '')
    {
        // var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId + "?order_id=" + generate_order_id +
        //     "&money=" + readl_money + "&openId=" + orderData.openId + "&userId=" + userId + "&goodsName=" + param.goodsName +
        //     "&cp_game_appid=" + cp_game_appid + "&sdkloginmodel=" + sdkloginmodel + "&channelExt=" + channelExt;
        $data['amount'] = $this->input->get('money');
        $data['channelExt'] = $this->input->get('channelExt');
        $data['trade_no'] = $this->input->get('order_id');
        $data['game_appid'] = $this->input->get('cp_game_appid');
        $data['props_name'] = $this->input->get('goodsName');
        $data['user_id'] = $this->input->get('userId');
        $data['sdkloginmodel'] = $this->input->get('sdkloginmodel');
        $key = $this->Game_model->get_key($game_id,"CPkey");
        $sign = $this->signData($data,$key);

        $_data=array(
            'sign'=>$sign,
            'goodsName'=>$data['props_name']
        );

        return $_data;

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


        $key = $this->Game_model->get_key($game_id,"CPkey");

        // cp_user_id=" + cp_user_id + "&cp_game_appid=" + cp_game_appid + "&server_id=" +
        // srvid + "&roleid=" + cproleid +
        // "&rolename=" + name + "&level=" + level;
        $data['user_id'] = $this->input->get('cp_user_id');
        $data['game_appid'] = $this->input->get('cp_game_appid');
        $data['server_id'] = $this->input->get('server_id');
        $data['server_name'] = $this->input->get('server_id');
        $data['role_id'] = $this->input->get('roleid');
        $data['role_name'] = $this->input->get('rolename');
        $data['level'] = $this->input->get('level');
        $sign = $this->signData($data,$key);
        $j_data = array(
            'name' =>$data['role_name'],
            'sign' =>$sign,
        );
        return $j_data;
    }
    private function signData($data, $game_key)
    {
        ksort($data);
        foreach ($data as $k => $v) {
            $tmp[] = $k . '=' . $v;
        }
        $str = implode('&', $tmp) . $game_key;
        // echo $str.'<br>';

        return md5($str);
    }
}
