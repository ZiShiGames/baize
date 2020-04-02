<?php

class Wanwei_model extends CI_Model
{
    public $platform = 'wanwei';
    public $key = '';

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $username = $this->input->get('username');
        $logintime = $this->input->get('logintime');
        $token = $this->input->get('token');

        if (!$username || !$logintime || !$token) {
            return false;
        }

        $this->key = $this->Game_model->get_key($game_id, 'key');
        $v_token = md5("username=$username&logintime=$logintime&appkey=".$this->key);
        if ($v_token != $token) {
            return false;
        }

        $condition = array(
            'p_uid' => $username,
            'platform' => $this->platform,
        );

        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $username,
                'nickname' => $username,
                'avatar' => $username,
                'create_date' => time(),
            );
            $user_id = $this->User_model->add($user);
            if (!$user_id) {
                log_message('error', "Login error user create $content");

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
        $openKey = $this->cache->get($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "wanwei login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->post('remarks');

        $money = $this->input->get_post('amount');
        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');

        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (intval($money*100)!=$game_order->money) {
            return;
        }
        $game_id = $game_order->game_id;
        $orderid = $this->input->post('orderid');
        $username = $this->input->post('username');
        $productname = $this->input->post('productname');
        $roleid = $this->input->post('roleid');
        $serverid = $this->input->post('serverid');
        $appid = $this->input->post('appid');
        $paytime = $this->input->post('paytime');
        $token = $this->input->post('token');
        $game_appkey= $this->Game_model->get_key($game_id, 'key');

        $sign_str ="orderid=".$orderid."&username=" . urlencode($username) . "&productname=" .  urlencode($productname) . "&amount=" . urlencode($money) . "&roleid=" . urlencode($roleid) . "&serverid=" . urlencode($serverid) . "&appid=" . urlencode($appid) . "&paytime=" . urlencode($paytime)
        ."&remarks=" . urlencode($order_id) . "&appkey=" . $game_appkey;

        $sign_token=MD5($sign_str);
        if ($token==$sign_token) {
            return $order_id;
        } else {
            return;
        }
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
        $username = $this->input->get('username');
        $productname = $this->input->get('productname');
        $amount = $this->input->get('amount');
        $roleid = $this->input->get('roleid');
        $serverid = $this->input->get('serverid');
        $appid = $this->input->get('appid');
        $remarks = $this->input->get('remarks');

        // $data = array(
        //     'username' => $username,
        //     'productname' => $productname,
        //     'amount' => $amount,
        //     'roleid' => $roleid,
        //     'serverid' => $serverid,
        //     'appid' => $appid,
        //     'remartks' => $remartks,
        // );
        //
        // $this->load->model('Common_model');
        // $p_str = $this->Common_model->sort_params($data);

        // the order is fixed.
        $p_str = 'username='.urlencode($username);
        $p_str .= '&productname='.urlencode($productname);
        $p_str .= '&amount='.urlencode($amount);
        $p_str .= '&roleid='.urlencode($roleid);
        $p_str .= '&serverid='.urlencode($serverid);
        $p_str .= '&appid='.urlencode($appid);
        $p_str .= '&remarks='.urlencode($remarks);
        $this->key = $this->Game_model->get_key($game_id, 'key');
        $p_str .= '&appkey='.urlencode($this->key);
        $sign = md5($p_str);


        return $sign;
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }
}
