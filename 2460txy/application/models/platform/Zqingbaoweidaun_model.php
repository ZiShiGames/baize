<?php

class Zqingbaoweidaun_model extends CI_Model
{
    public $platform = 'zqingbaoweidaun';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $uid = $this->input->get('uid');
        $session = $this->input->get('session');
        $this->session->set_userdata('cpsession', $session);
        $CPplatform = $this->input->get('CPplatform');
        $this->session->set_userdata('CPplatform', $CPplatform);
        $key = $this->Game_model->get_key($game_id, 'CPappkey');
        $appid = $this->Game_model->get_key($game_id, 'CPappid');
        $url = "http://uni.notice.zqgame.com/$appid/$CPplatform/login_request";
        $this->load->model("Common_model");
        $data = array(
            'account'=>$uid,
            'session'=>$session,
            'ext'=>'123',
        );
        $sign_str = $this->Common_model->sort_params($data);
        $sign_str = $sign_str.$key;
        $sign = md5($sign_str);
        $data['sign']=$sign;
        $data['session']=urlencode($session);
        $response = json_decode($this->Curl_model->curl_post($url, $data));
        // echo json_encode($response);
        if ($response->status==200) {
            // echo $response
            if (isset($response->data->accid)) {
                $user_id = $response->data->accid;
                $this->session->set_userdata('accid', $response->data->accid);
                if (isset($response->data->account)) {
                    $this->session->set_userdata('account', $response->data->account);
                }
            } elseif (isset($response->data->account)) {
                $user_id = $response->data->account;
                $this->session->set_userdata('account', $response->data->account);
            } else {
                echo "_err";
            }
        } else {
            echo 'err';
            return;
        }


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
        $custom_info = $this->input->get_post('ext');
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
        echo '200';
    }

    public function notify_error()
    {
        echo 'fail';
        ;
    }
    public function sign_order($game_id = '')
    {
        $account = $this->session->userdata('account');
        $accid = $this->session->userdata('accid');
        $CPplatform = $this->session->userdata('CPplatform');
        $app_id = $this->Game_model->get_key($game_id, 'CPappid');
        $key = $this->Game_model->get_key($game_id, 'CPappkey');
        $order_id = $this->input->get('order_id');
        $money = $this->input->get('money');
        $notify_url = ("http://h5sdk-xly.xileyougame.com/index.php/api/notify/zqingbaoweidaun/$game_id");
        $srid = $this->input->get('srid');
        $cproleid = $this->input->get('cproleid');
        $cprolename = ($this->input->get('cprolename'));
        $goodsName = ($this->input->get('goodsName'));
        $data = array(
            'accid'=>$accid,
            'game_order'=>$order_id,
            'amount'=>$money,
            urlencode('notice_url')=>$notify_url,
            'server_id'=>$srid,
            'role_id'=>$cproleid,
            'role_name'=>$cprolename,
            'goods_id'=>$money,
            'goods_name'=>$goodsName,
            'goods_desc'=>$goodsName,
            'ext'=>$order_id,
        );
        if (isset($account)) {
            $data["account"] = $account;
        }
        $this->load->model('Common_model');
        $sign_str = $this->Common_model->sort_params($data);
        $_sign_str = "$sign_str$key";
        $sign = md5($_sign_str);
        $data['sign'] = $sign;

        // echo json_encode($data);

        $response = json_decode($this->Curl_model->curl_post("http://uni.notice.zqgame.com/$app_id/$CPplatform/create_order", $data));
        if (isset($response->status) && $response->status ==200) {
            $response->data->session=$this->session->userdata('cpsession');
            return $response->data;
        } else {
            // echo json_encode($response);
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
    }
    public function focus($game_id='')
    {
        $gameid= $this->Game_model->get_key($game_id, 'gameid');
        return $gameid;
    }
}
