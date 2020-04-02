<?php

class Qunhei_model extends CI_Model
{
    public $platform = 'qunhei';
    public $charge_key = '';
    public $login_key = '5754ff38dcc4fd65e484ab6f40232dc1';

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login()
    {
        $username = $this->input->get('username');
        $headImgUrl = $this->input->get('uimg');
        $nickname = $this->input->get('nname');
        // if (!$username || !$nickname || !$headImgUrl) {
        //     return false;
        // }

        $p_uid = $username;
        $condition = array(
                        'p_uid' => $p_uid,
                        'platform' => $this->platform,
                    );

        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                            'platform' => $this->platform,
                            'p_uid' => $p_uid,
                            // 'nickname' => $nickname,
                            // 'avatar' => $headImgUrl,
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
        $this->session->set_userdata($user['user_id'].'_token', md5($user['user_id'].$user['platform'].time()), 86400);
        // log_message('debug', $this->platform.' token '.md5($user['user_id'].$user['platform'].time()).' saved '.$this->cache->get($user['user_id'].'_token').'  '.$user['user_id'].'_token');
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
        // $openKey = $this->cache->get($openId.'_token');
        $openKey = $this->session->userdata($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "qunhei login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get('ext');

        $money = $this->input->get_post('rmb');

        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (intval($money*100)!=$game_order->money) {
            return;
        }
        //check sign
        $serverid = $this->input->get_post('serverid');
        $orderno = $this->input->get_post('orderno');
        $username = $this->input->get_post('username');
        $addgold = $this->input->get_post('addgold');
        $rmb = $this->input->get_post('rmb');
        $ext = $this->input->get_post('ext');
        $paytime = $this->input->get_post('paytime');
        $ser_sign = $this->input->get_post('sign');
        $gameid = $game_order->game_id;
        $key = $this->Game_model->get_key($gameid, 'charge_key');
        $my_sign = MD5("$orderno$username$serverid$addgold$rmb$paytime$ext$key");
        if ($ser_sign==$my_sign) {
            return $order_id;
        } else {
            return false;
        }

        //check sign done
        return false;
    }

    public function notify_ok()
    {
        echo '1';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }

    public function sign_order($game_id = '')
    {
        $money = $this->input->get('money');
        $userId = $this->input->get('userId');
        $ext = $this->input->get('ext');
        if (!$money || !$userId || !$ext) {
            return false;
        }

        $this->charge_key = $this->Game_model->get_key($game_id, 'charge_key');
        $sign = md5($money.$userId.$ext.$this->charge_key);

        $gid = $this->Game_model->get_key($game_id, 'gid');

        $data = array(
            'sign' => $sign,
            'gid' => $gid,
        );

        return $data;
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }
}
