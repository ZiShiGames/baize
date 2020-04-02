<?php

class Leyou_model extends CI_Model
{
    public $platform = 'leyou';

    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $uuid = $this->input->get('uid');
        log_message('debug', $this->platform.' uuid is '.$uuid);

        if (!$uuid) {
            log_message('error', 'information error');

            return false;
        }

        $condition = array(
            'p_uid' => $uuid,
            'platform' => $this->platform,
        );

        $user = $this->User_model->get_one_by_condition_array($condition);

        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $uuid,
                'create_date' => time(),
            );
            $user_id = $this->User_model->add($user);

            if (!$user_id) {
                log_message('error', 'Login error user create fail');

                return false;
            }
            $user['user_id'] = $user_id;
        }

        // generate random token and save it to cache

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
        $pkg = "";
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        // $spe_server_ids = array('8000');
        $spe_server_ids = array('2','3','4','5','6','7','8','9','10','11','8000');
        if (in_array($serverId, $spe_server_ids)) {
            $game_url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/login/juhe';
        }

        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId&server_name=$server_name&pkg=$pkg";

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get_post('attach');
        $money = $this->input->get_post('amount');
        log_message('debug', $this->platform.' order is :'.$order_id);
        log_message('debug', $this->platform.' money is :'.$money);
        // $sign = $this->input->get('sign');
        // || !$sign
        if (!$order_id) {
            return;
        }

        //
        // if ($order_id && $money) {
        //     $condition = array('u_order_id' => $order_id);
        //     $this->load->model('Game_order_model');
        //
        //     $game_order = $this->Game_order_model->get_one_by_condition($condition);
        //     if ($game_order) {
        //         if (intval($money) == $game_order->money) {
        //             return $order_id;
        //         } else {
        //             log_message('debug', $this->platform.' money errory '.$game_order->money." != $money");
        //         }
        //     }
        // }

        return $order_id;
    }

    public function notify_ok()
    {
        $data = array(
            'ret' => 1,
            'msg' => '',
        );
        echo json_encode($data);
    }

    public function notify_error()
    {
        $data = array('ret' => 0,'msg' => '签名失败');
        echo json_encode($data);
    }

    public function sign_order($game_id = '')
    {
        $orderid = $this->input->get('orderid');
        $money = $this->input->get('money');
        $product = $this->input->get('product');
        $channel = $this->input->get('channel');
        $key = $this->Game_model->get_key($game_id, 'key');
        $sign = md5($orderid.$money.$product.$channel.$key);
        $data = array(
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

    public function create_role($game_id)
    {
    }

    public function init($game_id)
    {
        $client_id = $this->Game_model->get_key($game_id, 'client_id');
        $service = $this->Game_model->get_key($game_id, 'service');
        $redirect_uri = $this->Game_model->get_key($game_id, 'redirect_uri');
        $token = $this->input->get('token');
        $data = array(
            'client_id' => $client_id,
            'service' => $service,
            'redirect_uri' => $redirect_uri,
            'token' => $token,
        );
        log_message('debug', $this->platform.' login_init source service:'.$service.' redirect_uri:'.$redirect_uri.' token:'.$token.' client_id:'.$client_id);

        return $data;
    }
    public function focus($game_id)
    {
        $appid = $this->Game_model->get_key($game_id, 'appid');
        $appkey = $this->Game_model->get_key($game_id, 'appkey');
        $data = array(
            'appid' => $appid,
            'appkey' => $appkey,
        );
        return $data;
    }
}
