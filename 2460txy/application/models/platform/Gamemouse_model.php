<?php

class Gamemouse_model extends CI_Model
{
    public $platform = 'gamemouse';
    public $key = '20170426175201';
    public $appid = '97E9869201766DF36';

    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('user_id');
        $game_appid = $this->input->get('game_appid');
        $email = $this->input->get('email');//运营方账号
        $new_time = $this->input->get('new_time');
        $channelExt = $this->input->get('channelExt');
        $sign = $this->input->get('sign');

        if (!$user_id || !$game_appid || !$new_time || !$sign) {
            return false;
        }

        $this->key = $this->Game_model->get_key($game_id, 'key');

        $my_sign = md5('user_id='.$user_id.'&game_appid='.$game_appid.'&email='.$email.'&new_time='.$new_time.'&channelExt='.$channelExt.'&key='.$this->key);
        if ($my_sign != $sign) {
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

            $user_id_res = $this->User_model->add($user);
            if (!$user_id_res) {
                log_message('error', 'Login error user create fail');

                return false;
            }

            $user['user_id'] = $user_id_res;
        }

        $this->cache->save($user['user_id'].'_token', md5($user['user_id'].$user['platform'].time()), 86400);

        $temp_data = array(
            'user_id' => $user_id,
            'channelExt' => $channelExt,
        );
        $this->cache->save('Jiuw_info_'.$user['user_id'], $temp_data, 3600 * 24);

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
        $server_name = $this->input->get('server_name');
        $pfid = $this->input->get('pfid');
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        $spe_server_ids = array('2','3','4','5','6','7','8','9','10','11','8000');
        if (in_array($serverId, $spe_server_ids)) {
            $game_url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/login/juhe';
        }

        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId&server_name=$server_name";
        log_message('debug', "nineg login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get('trade_no');
        $sign = $this->input->get('sign');

        $money = $this->input->get('amount');

        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            log_message('debug', $this->platform.' money errory '.$game_order->money." != $money");
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
        $success = array(
            'status' => 'success',
        );
        echo json_decode($success);
    }

    public function notify_error()
    {
        $fail = array(
            'status' => 'fail',
        );
        echo json_decode($fail);
    }

    public function sign_order($game_id = '')
    {
        $amount = $this->input->get('amount');
        $trade_no = $this->input->get('trade_no');
        $openId = $this->input->get('openId');
        $props_name = $this->input->get('props_name');

        if (!$amount || !$trade_no || !$openId || !$props_name) {
            return false;
        }
        $info = $this->cache->get('Jiuw_info_'.$openId);
        $user_id = $info['user_id'];
        $channelExt = $info['channelExt'];

        $this->key = $this->Game_model->get_key($game_id, 'key');
        $this->appid = $this->Game_model->get_key($game_id, 'appid');

        $sign = md5('game_appid='.$this->appid.'&trade_no='.$trade_no.'&props_name='.$props_name.'&amount='.$amount.'&user_id='.$user_id.'&channelExt='.$channelExt.'&key='.$this->key);

        $data = array(
            'game_appid' => $this->appid,
            'user_id' => $user_id,
            'channelExt' => $channelExt,
            'sign' => $sign,
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

    public function server_name()
    {
        $server_id = $this->input->get('server_id');
        $openId = $this->input->get('openId');
        if (!$openId || !$server_id) {
            $this->Output_model->json_print(1, 'error');

            return;
        }

        $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/api/?m=player&fn=getserverlist&openId='.$openId;
        $content = $this->Curl_model->curl_get($url);
        if ($content) {
            $servers = json_decode($content);
            foreach ($servers->server_list as $one) {
                if ($one->id == $server_id) {
                    $this->Output_model->json_print(0, 'ok', $one->name);

                    return;
                }
            }
        }

        $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/9g/api/?m=player&fn=getserverlist&openId='.$openId;
        $content = $this->Curl_model->curl_get($url);
        if ($content) {
            $servers = json_decode($content);
            foreach ($servers->server_list as $one) {
                if ($one->id == $server_id) {
                    $this->Output_model->json_print(0, 'ok', $one->name);

                    return;
                }
            }
        }
    }
}
