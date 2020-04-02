<?php

class Bais_model extends CI_Model
{
    public $platform = 'bais';

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login()
    {
        $p_uid = $this->input->get('open_id');
        $app_key = $this->input->get('app_key');
        $systemname = $this->input->get('systemname');
        $timestamp = $this->input->get('timestamp');
        $nonce = $this->input->get('nonce');
        $ticket = $this->input->get('ticket');
        $signature = $this->input->get('signature');
        $login_type = $this->input->get('login_type');
        $host = $this->input->get('host');
        $game_url = $this->input->get('game_url');

        if (!$ticket) {
            return false;
        }

        $url = "http://open.mobo168.com/services/OpenData.ashx?action=userinfo&ticket=$ticket";
        $content = $this->Curl_model->curl_get($url);
        if (!$content) {
            log_message('error', "Bais Login empty content $url");

            return false;
        }
        log_message('debug', "Bais Login content $content");
        $p_user_info = json_decode($content);
        if ($p_user_info && $p_user_info->code != 0) {
            log_message('error', "Bais Login error $content");

            return false;
        }

        $condition = array(
            'p_uid' => $p_uid,
            'platform' => $this->platform,
        );

        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $p_uid,
                'nickname' => $p_user_info->nickname,
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
        $this->cache->save($user['user_id'] . '_token', md5($user['user_id'] . $user['platform'] . time()), 86400);

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
        $openKey = $this->cache->get($openId . '_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId . $noice . $game->app_key);
        $game_url = $game->game_login_url;
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "qunhei login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get('game_orderno');
        if ($order_id) {
            return $order_id;
        }

        $money = $this->input->get('total_fee');

        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money * 100) == $game_order->money) {
                    return $order_id;
                } else {
                    log_message('debug', $this->platform . ' money errory ' . $game_order->money . " != $money");
                }
            }
        }

        return false;
    }

    public function notify_ok()
    {
        echo '0';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }

    public function sign_order($game_id = '')
    {
        $open_id = $this->input->get('open_id');
        $total_fee = $this->input->get('total_fee');
        $game_orderno = $this->input->get('game_orderno');
        $subject = $this->input->get('subject');
        if (!$open_id || !$total_fee || !$game_orderno || !$subject) {
            return false;
        }

        $AppKey = $this->Game_model->get_key($game_id, 'AppKey');
        $Game_Secret = $this->Game_model->get_key($game_id, 'Game_Secret');

        $timestamp = time();
        $nonce = md5($timestamp);
        $game_id_arr = array(1357, 1487); //game_id

        if (in_array($game_id, $game_id_arr)) {
            $notify_url = 'http://h5sdk-xly.xileyougame.com/index.php/api/notify/' . $this->platform . '/' . $game_id;
        } else {
            $notify_url = 'http://h5sdk.zytxgame.com/index.php/api/notify/' . $this->platform . '/' . $game_id;
        };

        $sign_data = array(
            'app_key' => $AppKey,
            'open_id' => $open_id,
            'total_fee' => $total_fee,
            'game_orderno' => $game_orderno,
            'subject' => $subject,
            'notify_url' => $notify_url,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
        );

        $this->load->model('Common_model');
        $pairs = $this->Common_model->sort_params($sign_data);

        $sign = md5($pairs . '&' . $Game_Secret);
        log_message('debug', "Bais pairs $pairs sign $sign");

        $data = array(
            'sign' => $sign,
            'app_key' => $AppKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'notify_url' => $notify_url,
        );

        return $data;
    }

    public function init($game_id)
    {
        $AppKey = $this->Game_model->get_key($game_id, 'AppKey');
        $data = array('AppKey' => $AppKey);

        return $data;
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
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
                $userList[$one->p_uid . ''] = $user;
            }
            $data['userList'] = $userList;
            $response['data'] = $data;
        } else {
            $response = array(
                'success' => true,
                'message' => '成功',
            );
        }
        echo json_encode($response);
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
                $userList[$one->p_uid . ''] = $user;
            }
            $data['userList'] = $userList;
            $response['data'] = $data;
        } else {
            $response = array(
                'success' => true,
                'message' => '成功',
            );
        }
        echo json_encode($response);
    }
}
