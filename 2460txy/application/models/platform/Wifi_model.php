<?php

class Wifi_model extends CI_Model
{
    public $platform = 'wifi';
    public $app_key;
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $token = $this->input->get('token');
        $this->app_key =$this->Game_model->get_key($game_id, 'KEY');
        $this->session->set_userdata('app_key', $this->app_key);
        $this->session->set_userdata('token', $token);
        $url = 'http://act1.lianwifi.com/h5/user/get_info';
        $data = array(
            'token' => $token,
        );
        $content = $this->Curl_model->curl_post($url, $data);

        if (!$content) {
            log_message('error', $this->platform." Login empty content $url");

            return false;
        }
        $response = json_decode($content);
        $condition = array(
            'p_uid' => $response->data->open_id,
            'platform' => $this->platform,
        );


        $open_id = $response->data->open_id;
        $game_id = $response->data->game_id;
        $this->session->set_userdata('game_id', $game_id);
        $this->session->set_userdata('open_id', $open_id);
        $code=  $response->code;
        $message = $response->message;
        $reserved = $response->data->reserved;
        $this->session->set_userdata('reserved', $reserved);

        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!isset($response->data->open_id)) {
            return;
        }
        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' =>$response->data->open_id,
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
        $order_id = $this->input->get_post('out_trade_no');
        $total_fee = $this->input->get_post('total_fee');
        //check sign

        $trade_status = $this->input->post('trade_status');
        if ($trade_status!='TRADE_SUCCESS') {
            return;
        } else {
            return $order_id;
        }
    }

    public function notify_ok()
    {
        echo 'success=Y';
    }

    public function notify_error()
    {
        $info = array(
            'errcode' => 1001,
            'errmsg' => '签名验证失败',
        );
        $error = json_encode($info);

        echo $error;
    }

    public function sign_order($game_id)
    {
        $token = $this->session->userdata('token');
        $reserved=$this->session->userdata('reserved');
        $game_id=$this->session->userdata('game_id');
        $open_id=$this->session->userdata('open_id');
        $_input_charset=$this->input->get('_input_charset');
        $out_trade_no=$this->input->get('out_trade_no');
        $total_fee=$this->input->get('total_fee');
        $subject=$this->input->get('subject');
        $param_openKey=$this->app_key;
        $sign_type ='md5';
        $sign_data = array(
            'game_id' =>$game_id ,
            'open_id' =>$open_id ,
            '_input_charset' =>$_input_charset ,
            'out_trade_no' =>$out_trade_no ,
            'total_fee' =>$total_fee ,
            'subject' =>$subject ,
            'sign_type' =>$sign_type ,
            'reserved' =>$reserved ,
        );
        $this->load->model('Common_model');
        $sign_a=$this->Common_model->sort_params($sign_data);
        // $sign_b=$param_openKey;
        $sign_b=md5($this->session->userdata('app_key'));
        $ture_sign=md5($sign_a.'&'.$sign_b);
        $true_sign_data = array(
            'game_id' =>$game_id ,
            'open_id' =>$open_id ,
            '_input_charset' =>$_input_charset ,
            'out_trade_no' =>$out_trade_no ,
            'total_fee' =>$total_fee ,
            'subject' =>$subject ,
            'sign_type' =>$sign_type ,
            'reserved' =>$reserved ,
            'sign' => $ture_sign,
        );
        $pay_url = 'http://act1.lianwifi.com/h5/order/create';
        $content = $this->Curl_model->curl_post($pay_url, $true_sign_data);
        $response = json_decode($content);
        $data = array(
            'token' => $token,
            'game_id'=>$game_id,
            'ture_sign'=>$ture_sign,
            'reserved'=>$reserved,
            'pay_url'=>$response->data->url,
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

    public function init($game_id)
    {
        $data = array(
            $game_id = $this->input->get('game_id'),
            $open_id = $this->input->get('open_id'),
            $_input_charset = $this->input->get('_input_charset'),
            $out_trade_no = $this->input->get('out_trade_no'),
            $total_fee = $this->input->get('total_fee'),
            $subject = $this->input->get('subject'),
            $sign = $this->input->get('sign'),
            $reserved = $this->input->get('reserved'),
            $sign_type = $this->input->get('sign_type'),
        );
        $url = 'http://act1.lianwifi.com/h5/order/create';
        $content = $this->Curl_model->curl_post($url, $data);
        if (!$content) {
            log_message('error', $this->platform." Login empty content $url");
            return false;
        }
        $response = json_decode($content);
        return $response;
    }
}
