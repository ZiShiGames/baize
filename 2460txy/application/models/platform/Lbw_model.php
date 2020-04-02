<?php

class Lbw_model extends CI_Model
{
    public $platform = 'lbw';
    public $key = '';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $sign = $this->input->get('sign');
        $user_id = $this->input->get('uid');
        if (!$sign || !$user_id) {
            return false;
        }
        $condition = array(
                'p_uid' => $user_id.'',
                'platform' => $this->platform,
            );
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                    'platform' => $this->platform,
                    'p_uid' => $user_id,
                    'nickname' => '',
                    'avatar' => '',
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
        $direct = $this->input->get('direct');
        if ($direct) {
            return $user['user_id'].'&direct=1';
        } else {
            return $user['user_id'];
        }
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
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "allu login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        log_message('debug',$this->platform.' '.json_encode($_POST));
        $order_id = $this->input->get_post('orderId');
        $sign = $this->input->get_post('sign');
        $money = $this->input->get_post('total_fee');
        if (!$order_id || !$sign) {
            return;
        }
        //check sign
        $gid = $this->input->get_post('gid');
        $orderNum = $this->input->get_post('orderNum');
        $gold = $this->input->get_post('gold');
        $uid = $this->input->get_post('uid');
        $time = $this->input->get_post('time');
        $extend = $this->input->get_post('extend');
        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $this->load->model('Common_model');
        $sign_data = array(
            'gid' => $gid,
            'total_fee' => $money,
            'gold' => $gold,
            'uid' => $uid,
            'extend' => $extend,
            'orderNum' => $orderNum,
            'orderId' => $order_id,
            'time' => $time,
        );
        $game_id=$game_order->game_id;

        $p_str = $this->Common_model->sort_params($sign_data);

        $this->key = $this->Game_model->get_key($game_id, 'secret');

        ksort($sign_data);
        foreach ($sign_data as $k => $v) {
            $tmp[] = $k . '=' . $v;
        }
        $str = implode('&', $tmp) . $this->key;
        $my_sign=sha1($str);


        // $my_sign = sha1($p_str.$this->key);
        if ($my_sign==$sign) {
            log_message('debug', 'sign_callback_check '.$this->platform.' is success');
        } else {
            log_message('debug', 'sign_callback_check '.$this->platform.' is FAILED ');
        }
        //check done
        if ($order_id && $money) {
            if ($game_order) {
                if (intval($money * 100) == $game_order->money) {
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
        return;
    }

    public function notify_ok()
    {
        echo 'success';
    }

    public function notify_error()
    {
        echo 'failed';
    }

    public function focus()
    {
    }

    public function login_collect($data)
    {

        //执行统计请求
    }

    public function create_role_collect($data)
    {
        //执行统计请求
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
            $limit = 1000;
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
            $limit = 1000;
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

    public function sign_report($value = '')
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
            $limit = 1000;
        }

        $this->load->model('Sign_report_model');
        $reports = $this->Sign_report_model->get_report($this->platform, $from_date, ($page - 1) * $limit, $limit);

        if ($reports) {
            $all = $this->Sign_report_model->get_report($this->platform, $from_date);
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

    public function sign_order($game_id = '')
    {
        $orderId = $this->input->get('orderId');
        $total_fee = $this->input->get('total_fee');
        $gold = $this->input->get('gold');
        $uid = $this->input->get('uid');
        $serverNum = $this->input->get('serverNum');
        $playerName = $this->input->get('playerName');
        $time = $this->input->get('time');
        $diamond = $this->input->get('diamond');
        $playerId = $this->input->get('playerId');


        $gid = $this->Game_model->get_key($game_id, 'id');

        $data = array(
            'gid' => $gid,
            'orderId' => $orderId,
            'total_fee' => $total_fee,
            'gold' => $gold,
            'uid' => $uid,
            'serverNum' => $serverNum,
            'playerName' => $playerName,
            'time' => $time,
            'playerId' => $playerId,
            'diamond' => $diamond
        );

        $this->load->model('Common_model');
        $p_str = $this->Common_model->sort_params($data);

        $this->key = $this->Game_model->get_key($game_id, 'secret');
        $sign = sha1($p_str.$this->key);

        log_message('debug', "lbw sign '".$p_str.$this->key."' sign : $sign");

        $data = array(
            'sign' => $sign,
            'gid' => $gid,
        );

        return $data;
    }
}
