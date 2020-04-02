<?php

class Dazhan_model extends CI_Model
{
    public $platform = 'dazhan';
    // public $userId;
    // public $serId;
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $appid = $this->input->get('appid');
        $time = $this->input->get('time');
        $token = $this->input->get('token');
        $serverid = $this->input->get('serverid');
        $this->serId=$serverid;
        // $sign = $this->input->get('sign');
        $this->session->set_userdata('serverid', $serverid);
        $this->cache->save('serverid', $serverid, 60*60*24*7);
        $app_key = $this->Game_model->get_key($game_id, 'appkey');
        $this->load->model('Common_model');
        $sign_data = array(
            'appid'=>$appid,
            'time'=>$time,
            'token'=>$token,
        );
        $sign_str = $this->Common_model->sort_params($sign_data);
        $sign = md5($sign_str.$app_key);
        log_message('debug', $this->platform.' str '.$sign_str.' '.$sign);
        $url = "http://h5.wan855.cn/api/h5/user/getuserinfobytoken?appid=$appid&time=$time&token=$token&sign=$sign";
        $content = $this->Curl_model->curl_get($url);
        $response = json_decode($content);
        if (!$response) {
            log_message('error', 'response is null');
            return false;
        };
        $uid = $response->data->unionid;
        $this->session->set_userdata('uid', $uid);
        $this->cache->save('uid', $uid, 60*60*24*7);
        $this->userId=$uid;
        $condition = array(
            'p_uid' => $uid,
            'platform' => $this->platform,
        );
        // echo  $response->data->gouzaiId;

        $user = $this->User_model->get_one_by_condition_array($condition);

        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $uid,
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
        $thirdId = $this->input->get_post('remark');
        $orderMoney = $this->input->get_post('amount');

        if ($thirdId && $orderMoney) {
            $condition = array('u_order_id' => $thirdId);
            $this->load->model('Game_order_model');
            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            log_message('debug', $this->platform.' orderid '.$thirdId.' '.$orderMoney.' '.$game_order->money);
            if ($game_order) {
                if (intval($orderMoney*100) == $game_order->money) {
                    log_message('debug', $this->platform.' money is '.$orderMoney.' order_id is '.$thirdId);
                    return $thirdId;
                } else {
                    return false;
                    log_message('debug', $this->platform.' money errory '.$game_order->money." != $orderMoney");
                }
            }
        }

        return false;
        // return $thirdId;
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
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

    public function sign_order($game_id = '')
    {
        // $uid = $this->userId;
        // $sid = $this->serId;
        // $uid = $this->session->userdata('uid');
        $uid = $this->cache->get('uid');
        // $sid = $this->session->userdata('serverid');
        $sid = $this->cache->get('serverid');
        $appid = $this->Game_model->get_key($game_id, 'appid');
        // $uid = $this->input->get('uid');
        // $order = $this->input->get('orderid');
        $amount=$this->input->get('amount');
        $productname= $this->input->get('productname');

        $time = time();
        $oid = $this->input->get('oid');
        $role = $this->input->get('role');
        $app_key = $this->Game_model->get_key($game_id, 'appkey');
        $this->load->model('Common_model');
        $orderid = $this->input->get('orderid');
        $uid = $this->input->get('uid');
        $sign_data = array(
            'appid'=>$appid,
            'uid'=>$uid,
            'amount'=>$amount,
            'productname'=>$productname,
            'sid'=>$sid,
            'oid'=>$oid,
            'time'=>$time,
            'role'=>$role,
            'remark'=>$orderid,
        );
        $sign_str = $this->Common_model->sort_params($sign_data);
        $sign = md5($sign_str.$app_key);

        $url = "http://h5.wan855.cn/api/h5/pay/unifiedorder?appid=$appid&time=$time&uid=$uid&sign=$sign&amount=$amount&productname=$productname&sid=$sid&oid=$oid&role=$role&remark=$orderid";
        log_message('debug', $this->platform.' signstr is '.$sign_str.' sign is '.$sign.' url is '.$url);
        $content = $this->Curl_model->curl_get($url);
        $pay_data = json_decode($content);
        // echo json_encode($pay_data['data']);
        $data_pay = array();
        $data_pay= $pay_data->data;

        $data = array(
                'pay_info'=> json_encode($data_pay),
                'pay_infode'=> $data_pay,
                'uid' =>$uid,
        );
        // echo json_encode($content);
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
    public function focus($game_id = '')
    {
        $uid = $this->cache->get('uid');
        $data = array(
            'uid' => $uid,
        );
        $updata_url=$this->input->get('updata_url');
        if ($updata_url&&$updata_url!='undefined') {
            $time = $this->input->get('time');
            $rolename = $this->input->get('rolename');
            $sid = $this->input->get('sid');
            $unionid = $this->input->get('unionid');
            $level = $this->input->get('level');
            $updata_url ="$updata_url&time=$time&rolename=$rolename&sid=$sid&unionid=$unionid&level=$level";
            $response = $this->Curl_model->curl_get($updata_url);
            if ($response) {
                log_message('debug', $this->platform. ' updata is ok '.$response.' url = '.$updata_url);
                return;
            }
        }

        return $data;
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
