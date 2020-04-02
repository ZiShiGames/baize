<?php

class Tn_model extends CI_Model
{
    public $platform = 'lbw';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $x_token = $this->input->get('x_token');
        if (!$x_token) {
            return false;
        }

        $url = 'http://h5.tianniuyouxi.com/getuser?x_token='.$x_token;
        $content = $this->Curl_model->curl_get($url);
        if ($content) {
            $response = json_decode($content);
            if ($response->code == 200) {
                $user_id = $response->data->id;
                $nickname = $response->data->nickname;
                $headimgurl = $response->data->headimgurl;

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
        $order_id = $this->input->get_post('out_order_id');
        if (!$order_id) {
            return;
        }
        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        //check sign
        $uid = $this->input->get_post('uid');
        $gameid = $this->input->get_post('gameid');
        $out_order_id = $this->input->get_post('out_order_id');
        $orderid = $this->input->get_post('order_id');
        $body= $this->input->get_post('body');
        $detail= $this->input->get_post('detail');
        $time = $this->input->get_post('time');
        $sign= $this->input->get_post('sign');
        $amount = $this->input->get_post('amount');
        $out_attach= $this->input->get_post('out_attach');
        $code= $this->input->get_post('code');
        $message = $this->input->get_post('message');
        $sign_array = array(
            'uid'=>$uid,
            'gameid'=>$gameid,
            'out_order_id'=>$out_order_id,
            'order_id'=>$orderid,
            'body'=>$body,
            'detail'=>$detail,
            'time'=>$time,
            'amount'=>$amount,
            'out_attach'=>$out_attach,
            'code'=>$code,
            'message'=>$message,
        );
        $this->load->model('Common_model');
        $sign_str_smil=$this->Common_model->sort_params($sign_array);
        $my_game_id = $game_order->game_id;
        // $key = $this->Game_model->get_key($my_game_id, 'key');
        $key = '08c82679bedf538ba52f92319f497ff2';
        $url_sign_str = urlencode($sign_str_smil.'&key='.$key);
        $my_sign_a = md5($url_sign_str);
        $my_sign = strtoupper($my_sign_a);
        if ($sign==$my_sign) {
            log_message('debug', 'sign_callback_check    TN '.$this->platform.' is success');
        } else {
            log_message('debug', 'sign_callback_check    TN '.$this->platform.' is FAILED '.$sign.'  '.$my_sign.' '.$sign_str_smil.' '.$key);
        }
        //check sign done
        $money = $this->input->get_post('amount');

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
    }

    public function notify_ok()
    {
        echo 'success';
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
        $uid = $this->input->get('uid');
        $amount = $this->input->get('amount');
        $generate_order_id = $this->input->get('generate_order_id');
        $body = $this->input->get('body');
        $detail = $this->input->get('detail');
        $out_attach = $this->input->get('out_attach');
        $time  = time();
        if ($game_id == 1183) {
            $gameid = '345';
        } elseif ($game_id == 1184) {
            $gameid = '346';
        }
        $gameid = $this->Game_model->get_key($game_id,'gameid');
        $gamekey = $this->Game_model->get_key($game_id,'key');
        // $gamekey = '08c82679bedf538ba52f92319f497ff2';
        $sign_data=array(
                'uid'=>$uid,
                'gameid'=>$gameid,
                'out_order_id'=>$generate_order_id,
                'body'=>$body,
                'detail'=>$detail,
                'amount'=>$amount,
                'out_attach'=>$out_attach,
                'time'=>$time,
        );
        $this->load->model('Common_model');
        $sign_str = $this->Common_model->sort_params($sign_data);
        $url_sign = urldecode($sign_str.'&key='.$gamekey);
        $sign_b = md5($url_sign);
        $sign = strtoupper($sign_b);
        log_message('debug', $this->platform.'tianniu sign str is '.$sign_str.' sign b '.$sign_b.'  url sign  '.$url_sign.' sign '.$sign.' gamekey is '.$gamekey);
        // echo $sign_str.'<br>';
        // echo $url_sign.'<br>';
        // echo $sign_b.'<br>';
        // echo $sign.'<br>';
        $data = array(
                'sign'=>$sign,
                'time'=>$time,
        );
        return $data;
    }
}
