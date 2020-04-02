<?php

class Zhongwenonline_model extends CI_Model
{
    public $platform = 'zhongwenonline';
    public $key = '';

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $gameid = $this->input->get('gameId');
        $userId = $this->input->get('userName');
        $time = $this->input->get('time');
        $sign = $this->input->get('sign');

        $condition = array(
            'p_uid' => $userId,
            'platform' => $this->platform,
        );
        // echo  $response->data->gouzaiId;

        $user = $this->User_model->get_one_by_condition_array($condition);

        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $userId,
                'create_date' => time(),
            );

            $userId = $this->User_model->add($user);

            if (!$userId) {
                log_message('error', 'Login error user create fail');

                return false;
            }

            $user['user_id'] = $userId;
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
        log_message('debug', "five login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $orderId = $this->input->post('orderId');
        $userId = $this->input->post('userName');
        $money = $this->input->post('money');
        $ext = $this->input->post('ext');
        $time = $this->input->post('time');
        $sign = $this->input->post('sign');
        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');

        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (intval($money*100)!=$game_order->money) {
            return;
        }
        $game_id = $game_order->game_id;
        $key = $this->Game_model->get_key($game_id, 'key');
        $data = array(
            'userName' => $userId,
            'orderId' => $orderId,
            'ext' => $ext,
            'money' => $money,
            'time' => $time,
        );
        $mysign = $this->createSign($data, $key);
        if ($mysign == $sign) {
            log_message('debug', $this->platform.' check money '.$money);
            return $ext;
        } else {
            return;
        }
    }

    public function notify_ok()
    {
        echo '{"code":0,"msg":"success","data":[]}';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }

    public function sign_order($game_id)
    {
        $orderId = $this->input->get('order_id');
        $money = $this->input->get('money');
        $userId = $this->input->get('userId');
        $extInfo = $this->input->get('ext');
        $time = time();
        $key = $this->Game_model->get_key($game_id, 'key');
        $gameId= $this->Game_model->get_key($game_id, 'gameId');
        $data = array(
            'source' => '17k',
            'userName' => $userId,
            'gameId' => $gameId,
            'money' => $money,
            'orderId' => $orderId,
            'ext' => $extInfo,
            'time' => $time,
        );
        $sign = $this->createSign($data, $key);
        // $this->load->model('Common_model');
        // $pairs = $this->Common_model->sort_params($data);
        //
        // $key = $this->Game_model->get_key($game_id, 'key');
        // $sign = md5($pairs.$key);

        $data = array(
            'sign' => $sign,
            'time'=>$time,
            'gameid'=>$gameId,
        );

        return $data;
    }

    public function create_role_report()
    {
        $date = $this->input->get('date');
        if (!$date) {
            $today_date_str = date('Y-m-d', time());
        } else {
            $today_date_str = $date;
        }
        $from_date = strtotime($today_date_str);

        $page = $this->input->get('page');
        if (!$page) {
            $page = 1;
        }

        $limit = $this->input->get('limit');
        if (!$limit) {
            $limit = 100;
        }

        $condition = array(
            'platform' => $this->platform,
            'create_date >= ' => $from_date,
        );
        $this->load->model('Create_role_report_model');
        $reports = $this->Create_role_report_model->get_report($this->platform, $from_date, ($page - 1) * $limit, $limit);

        if ($reports) {
            $all = $this->Create_role_report_model->get_report($this->platform, $from_date);
            $response = array(
                'success' => true,
                'message' => '成功',
            );
            $data = array(
                'total' => count($reports),
                'totalPage' => ceil(count($all) / $limit),
            );
            $userList = array();

            foreach ($reports as $one) {
                $user = array();
                $user['roleName'] = $one->nickname;
                $user['roleLevel'] = 1;
                $user['createTime'] = date('Y-m-d H:i:s', $one->create_date);
                $user['serverId'] = $one->server_id;
                $userList[$one->p_uid.''] = $user;
            }
            $data['userList'] = $userList;
            $response['data'] = $data;
        } else {
            $response = array(
                'success' => true ,
                'message' => '成功',
            );
        }
        echo json_encode($response);
    }

    public function login_report($value = '')
    {
        $date = $this->input->get('date');
        if (!$date) {
            $today_date_str = date('Y-m-d', time());
        } else {
            $today_date_str = $date;
        }
        $from_date = strtotime($today_date_str);

        $page = $this->input->get('page');
        if (!$page) {
            $page = 1;
        }

        $limit = $this->input->get('limit');
        if (!$limit) {
            $limit = 100;
        }

        $this->load->model('Login_report_model');
        $reports = $this->Login_report_model->get_report($this->platform, $from_date, ($page - 1) * $limit, $limit);

        if ($reports) {
            $all = $this->Login_report_model->get_report($this->platform, $from_date);
            $response = array(
                'success' => true,
                'message' => '成功',
            );
            $data = array(
                'total' => count($reports),
                'totalPage' => ceil(count($all) / $limit),
            );
            $userList = array();

            foreach ($reports as $one) {
                $user = array();
                $user['roleName'] = $one->nickname;
                $user['roleLevel'] = $one->level;
                $user['createTime'] = date('Y-m-d H:i:s', $one->create_date);
                $user['serverId'] = $one->server_id;
                $userList[$one->p_uid.''] = $user;
            }
            $data['userList'] = $userList;
            $response['data'] = $data;
        } else {
            $response = array(
                'success' => true ,
                'message' => '成功',
            );
        }
        echo json_encode($response);
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }
    public function createSign($p, $k)
    {
        ksort($p);
        $str='';
        foreach ($p as $key => $value) {
            $str .= $key.'='.$value;
        }
        return md5($str.$k);
    }
}
