<?php

class Fiveone_model extends CI_Model
{
    public $platform = 'fiveone';
    public $key = '';

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $userId = $this->input->get('uid');
        $platform = $this->input->get('platform');
        $gameid = $this->input->get('gameid');
        $this->session->set_userdata('gameid', $gameid);
        $this->cache->save('gameid', $gameid);
        $fcm = $this->input->get('fcm');
        $time = $this->input->get('time');
        $sign = $this->input->get('sign');
        if (!$userId) {
            log_message('error', $this->platform.' userid is null');
            return false;
        }

        $this->key = $this->Game_model->get_key($game_id, 'appkey');
        $appId = $this->Game_model->get_key($game_id, 'gameid');
        $condition = array(
                            'p_uid' => $userId,
                            'platform' => $this->platform,
                        );
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                            'platform' => $this->platform,
                            'p_uid' => $userId,
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
        $openKey = $this->cache->get($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign";
        log_message('debug', "five login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get_post('ext');
        $money = $this->input->get_post('amount');
        $money = ceil($money);
        $money = intval($money);
        log_message('debug', $this->platform.' orderid is:'.$order_id.' and money is : '.$money);
        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');
            log_message('debug', $this->platform.' orderid and money is not null');
            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                log_message('debug', $this->platform.' game_order is not null');
                if ($money*100 == $game_order->money) {
                    log_message('debug', $this->platform.' return orderid '.$order_id);
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
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }

    public function sign_order($game_id)
    {
        // $gameid=$this->session->userdata('gameid');
        $gameid=$this->input->get('gameid');
        $userId=$this->input->get('uid');
        // $orderId = $this->input->get('orderId');
        // $subject = $this->input->get('subject');
        // $money = $this->input->get('money');
        // $userId = $this->input->get('userId');
        // $buyAmount = $this->input->get('buyAmount');
        // $extInfo = $this->input->get('extInfo');
        //
        // $appId = $this->Game_model->get_key($game_id, 'appId');
        //
        // $data = array(
        //     'appId' => $appId,
        //     'orderId' => $orderId,
        //     'subject' => urlencode($subject),
        //     'money' => $money,
        //     'userId' => $userId,
        //     'buyAmount' => $buyAmount,
        //     'extInfo' => $extInfo,
        // );
        //
        // $this->load->model('Common_model');
        // $pairs = $this->Common_model->sort_params($data);
        //
        // $key = $this->Game_model->get_key($game_id, 'key');
        // $sign = md5($pairs.$key);

        $data = array(
            'gameid' => $gameid,
            'userId'=>$userId,
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
    public function focus($game_id)
    {
        $lever = $this->input->get('rolelv');
        $nickname = $this->input->get('nickname');
        $serverid = $this->input->get('serverid');
        $time = time();
        $this->key = $this->Game_model->get_key($game_id, 'appkey');
        $sign_str = $serverid.$lever.$time.$this->key;
        $sign = md5($sign_str);
        $data = array(
            'time'=>$time,
            'sign'=>$sign,
            'nickname'=>$nickname,
            'rolelv'=>$lever,
            'serverid'=>$serverid,
            'round'=>0,
        );
        return $data;
    }
}
