<?php

class Jinbangtishen_model extends CI_Model
{
    public $platform = 'jinbangtishen';
    public $partnerid = 'ccb59a570b1dc5125999747e3390c960';
    public $key = '25aa1951f661d09234f8b677d079f666';

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $access_token = $this->input->get('access_token');
        if (!$access_token) {
            return false;
        }

        $this->partnerid = $this->Game_model->get_key($game_id, 'partnerid');
        $this->key = $this->Game_model->get_key($game_id, 'key');

        $user_info_url = 'http://api.99kgames.com/vendor/api/userinfo';
        $params = array(
            'access_token' => $access_token,
            'partnerid' => $this->partnerid,
        );
        $this->load->model('Common_model');
        $pairs = $this->Common_model->sort_params($params);

        $sign = sha1($pairs.$this->key);

        $url = $user_info_url.'?'.$pairs."&sign=$sign";

        $content = $this->Curl_model->curl_get($url);

        log_message('debug', "jinb user $url '$content'");
        if (!$content) {
            log_message('debug', 'jinb user_info is null');
            return;
        }
        if ($content) {
            $response_obj = json_decode($content);

            if (isset($response_obj) && $response_obj->code == 0) {
                $condition = array(
                    'p_uid' => $response_obj->userid.'',
                    'platform' => $this->platform,
                );

                $user = $this->User_model->get_one_by_condition_array($condition);
                if (!$user) {
                    $province = '';
                    if (isset($response_obj->province)) {
                        $province = $response_obj->province;
                    }
                    $user = array(
                        'platform' => $this->platform,
                        'p_uid' => $response_obj->userid,
                        'nickname' => $response_obj->nickname,
                        'avatar' => $response_obj->avatar,
                        'sex' => $response_obj->sex,
                        'province' => $province,
                        // 'city' => $response_obj->city,
                        'comment' => 'invitor='.$response_obj->invitor,
                        'create_date' => time(),
                    );
                    if ($response_obj->city) {
                        $user['city']=$response_obj->cit;
                    }
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

        $spe_server_ids = array('8000');
        for ($index = 2; $index <= 123;++$index) {
            $spe_server_ids[] = "$index";
        }
        if (in_array($serverId, $spe_server_ids)) {
            $game_url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/login/juhe';
        }

        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "jinb login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification, called by api.php/notify
    public function get_order_id()
    {
        $order_id = $this->input->get_post('out_trade_no');
        $money = $this->input->get_post('total_fee');
        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');
        $this->load->model('Common_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        //check sign
        $trade_status = $this->input->post('trade_status');
        $game = $this->input->post('game');
        $partnerid = $this->input->post('partnerid');
        $userid = $this->input->post('userid');
        $total_fee = $this->input->post('total_fee');
        $transaction_id = $this->input->post('transaction_id');
        $out_trade_no = $this->input->post('out_trade_no');
        $product_id = $this->input->post('product_id');
        $attach = $this->input->post('attach');
        $pay_time = $this->input->post('pay_time');
        $timestamp = $this->input->post('timestamp');
        $sign_callback = $this->input->post('sign');
        $game_id = $game_order->game_id;
        $db_money = $game_order->money;
        if ($db_money!=intval($total_fee)) {
            return;
        }
        $this->partnerid = $this->Game_model->get_key($game_id, 'partnerid');
        $this->key = $this->Game_model->get_key($game_id, 'key');
        $sign_array = array(
            'trade_status'=>$trade_status,
            'game'=>$game,
            'partnerid'=>$partnerid,
            'userid'=>$userid,
            'total_fee'=>$total_fee,
            'transaction_id'=>$transaction_id,
            'out_trade_no'=>$out_trade_no,
            'product_id'=>$product_id,
            'attach'=>$attach,
            'pay_time'=>$pay_time,
            'timestamp'=>$timestamp,
        );
        $sign_str = $this->Common_model->sort_params($sign_array);
        $sign_server = sha1($sign_str.$this->key);
        if ($sign_callback == $sign_server) {
            // $this->load->model('Cp_game_order_model');
            // $check_payback = $this->Cp_game_order_model->get_one_by_condition($condition);
            // if (!$check_payback) {
            //     $order_back_info=array(
            //         'game_id'=>$game_order->game_id,
            //         'money'=>$total_fee,
            //         'game_father_id'=>$game_order->game_father_id,
            //         'create_Date'=>$timestamp,
            //         'cp_user_id'=>$userid,
            //         'u_order_id'=>$order_id,
            //         'cp_order_id'=>$transaction_id,
            //         'user_id'=>$game_order->user_id,
            //         'platform'=>$game_order->platform,
            //         'server_id'=>$game_order->ext,
            //     );
                // $response = $this->Cp_game_order_model->add($order_back_info);
                // if (!$response) {
                //     log_message('debug', $this->platform.' pay back check is failed');
                // }
            // }
            return $order_id;
        } else {
            return;
        }
        //check done
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        log_message('error', 'jinb notify:'.json_encode($_POST));

        echo 'FAILED';
    }

    public function sign_order($game_id = '')
    {
        $content = $this->input->get('content');
        if (!$content) {
            return false;
        }

        $params = json_decode($content);
        $data = array();
        foreach ($params as $key => $value) {
            $data[$key] = $value;
        }

        $this->load->model('Common_model');
        $pairs = $this->Common_model->sort_params($data);
        if ($pairs) {
            $this->key = $this->Game_model->get_key($game_id, 'key');
            $sign = sha1($pairs.$this->key);

            return $sign;
        } else {
            return false;
        }
    }

    public function focus()
    {
        $openid = $this->input->get('openid');
        if (!$openid) {
            return -1;
        }

        $condition = array('user_id' => $openid);
        $user = $this->User_model->get_one_by_condition($condition);
        if (!$user) {
            return -1;
        }
        $user_identify = $user->p_uid;

        $url = 'http://api.99kgames.com/vendor/api/subscribe';
        $params = array(
            'user_identify' => $user_identify,
            'partnerid' => $this->partnerid,
        );

        $this->load->model('Common_model');
        $pairs = $this->Common_model->sort_params($params);

        $sign = sha1($pairs.$this->key);

        $url = $url.'?'.$pairs."&sign=$sign";

        $content = $this->Curl_model->curl_get($url);

        log_message('debug', "jinb focus $url '$content'");
        if ($content) {
            $response_obj = json_decode($content);
            if (isset($response_obj) && $response_obj->code == 0) {
                return $response_obj->message;
            }
        }

        return -1;
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
}
