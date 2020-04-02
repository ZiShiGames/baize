<?php

class Gzyx_model extends CI_Model
{
    public $platform = 'gzyx';
    public $key = '4a3b4348c256c4c0c95bdedb2ca3ec66';
    public $p_game_id = 670306;

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $key = $this->input->get('key');
        $cur_channel = $this->input->get('cur_channel');
        if (!$key) {
            return false;
        }
        $this->session->set_userdata('cur_channel', $cur_channel);

        $url = 'http://h5api.guoziyx.com/Player/playerInfo';
        $nonce_str = md5(time());

        $this->key = $this->Game_model->get_key($game_id, 'key');
        $this->p_game_id = $this->Game_model->get_key($game_id, 'game_id');

        $data = array(
            'key' => $key,
            'game_id' => $this->p_game_id,
            'nonce_str' => $nonce_str,
        );

        $this->load->model('Common_model');
        $p_str = $this->Common_model->sort_params($data);
        $s_str = $p_str.'&secret='.$this->key;
        $sign = strtoupper(md5($s_str));
        $data['sign'] = $sign;

        $content = $this->Curl_model->curl_post($url, $data);

        if ($content) {
            $response = json_decode($content);
            if ($response->res_code == 0) {
                $user_id = $response->user_id;
                $nickname = $response->nickname;
                $headimgurl = $response->headimgurl;

                $condition = array(
                    'p_uid' => $user_id.'',
                    'platform' => $this->platform,
                );
                $user = $this->User_model->get_one_by_condition_array($condition);
                if (!$user) {
                    $user = array(
                            'platform' => $this->platform,
                            'p_uid' => $user_id,
                            'nickname' => $nickname,
                            'avatar' => $headimgurl,
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
        $order_id = $this->input->get_post('trade_id');
        if (!$order_id) {
            return;
        }

        $money = $this->input->get_post('money');
        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money) == $game_order->money) {
                    return $order_id;
                } else {
                    log_message('debug', $this->platform.' money errory '.$game_order->money." != $money");
                }
            }
        }
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAILED';
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
        $url = 'http://h5api.guoziyx.com/Player/createRole';

        if ($data['game_id']) {
            $game_id = $data['game_id'];
        } else {
            $game_id = 1016;
        }

        $this->key = $this->Game_model->get_key($game_id, 'key');
        $p_game_id = $this->Game_model->get_key($game_id, 'game_id');

        $post_data = array(
            'user_id' => $data['p_uid'],
            'role_id' => $data['user_id'],
            'role_name' => $data['nickname'],
            'op_ip' => $this->input->ip_address(),
            'cur_channel' => $this->session->userdata('cur_channel'),
            'game_server' => $data['server_id'],
            'op_time' => date('Ymdhisa', $data->create_date) ,
            'game_id' => $p_game_id,
            'nonce_str' => md5(time()),
        );

        $this->load->model('Common_model');

        $p_str = $this->Common_model->sort_params($post_data);
        $s_str = $p_str.'&secret='.$this->key;
        $sign = strtoupper(md5($s_str));
        $post_data['sign'] = $sign;
        $content = $this->Curl_model->curl_post($url, $post_data);
        log_message('debug', 'gzyx create_role_collect '.$content);
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
        $p = $this->input->get('p');
        if (!$p) {
            return false;
        }

        $data = json_decode($p);
        if (!$data) {
            return false;
        }

        $this->load->model('Common_model');
        $data->nonce_str = md5(time());

        $this->key = $this->Game_model->get_key($game_id, 'key');

        $p_str = $this->Common_model->sort_params((array) $data);
        $s_str = $p_str.'&secret='.$this->key;
        log_message('debug', 'gzyx s_str '.$s_str);
        $sign = strtoupper(md5($s_str));

        $data = array(
            'nonce_str' => $data->nonce_str,
            'sign' => $sign,
        );

        return $data;
    }
}
