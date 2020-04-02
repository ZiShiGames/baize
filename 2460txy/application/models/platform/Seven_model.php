<?php

class Seven_model extends CI_Model
{
    public $platform = 'seven';
    public $app_key = '148654992405';
    public $public_key = 'ebcb108a0c8130b8f1d2dbf9933ab692';
    public $pay_key = '';
    public $notify_key = '3fa17cec9586cf754b9c2d19fe67290d';

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        // $appkey = $this->input->get('appkey');
        // $time = $this->input->get('time');
        // $token = $this->input->get('token');
        $uid = $this->input->get('qqesuid');
        $ext = $this->input->get('ext');
        $this->session->set_userdata('ext',$ext);
        // $nickname = $this->input->get('nickname');
        // $sign = $this->input->get('sign');

        // if (!$appkey || !$time || !$token || !$uid || !$nickname) {
        //     return false;
        // }

        $condition = array(
                'p_uid' => $uid.'',
                'platform' => $this->platform,
            );
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                    'platform' => $this->platform,
                    'p_uid' => $uid,
                    // 'nickname' => $nickname,
                    // 'avatar' => '',
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
        $this->cache->save($user['user_id'].'_token', md5($user['user_id'].$user['platform'].time()), 60 * 60 * 24);

        return $user['user_id'];
    }

    public function game($platform, $game_id)
    {
        $openId = $this->input->get('openId');

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

        //执行统计请求
        $access_token = md5(substr(time(), 0, 8).'aoyouxi');

        $condition = array('user_id' => $openId);
        $user = $this->User_model->get_one_by_condition($condition);
        if (!$user) {
            echo 'error';

            return;
        }

        $user_id = $user->p_uid;
        //定义统计请求的地址：
        // $url = "http://h5.xileyougame.com/tongji/tongji_game_login/{$user_id}/{$game_id}/{$access_token}";
        // $this->Curl_model->curl_get($url);

        //如果这块功能实现完成， 需要把 allugame.com 的 controllers/game.php 的第153行注释掉，这块是现在使用的游戏登录统计。

        $openKey = $this->cache->get($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();

        $this->app_key = $this->Game_model->get_key($game_id, 'app_key');

        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "allu login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get_post('cp_order');
        $money = $this->input->get_post('fee');
        $sign = $this->input->post('sign');
        // if (!$order_id || !$sign) {
        //     return;
        // }
        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $gameid = $game_order->game_id;
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

    public function sign_order($game_id = '')
    {
        $order=$this->input->get('order');
        $cpgameid=$this->input->get('cpgameid');
        $qqesuid=$this->input->get('qqesuid');
        $channelid=$this->input->get('channelid');
        $channeluid=$this->input->get('channeluid');
        $goodsname=$this->input->get('goodsname');
        $fee=$this->input->get('fee');
        $ext=$this->input->get('ext');
        // $ext = $this->session->userdata('ext');

        $cpguid = $this->Game_model->get_key($game_id, 'pay_key');
        $key = $this->Game_model->get_key($game_id, 'notify_key');
        $timestamp=time();

        $_data = array(
            "order"=> $order,
            'cpgameid'=> $cpgameid,
            'qqesuid'=> $qqesuid,
            'channelid'=> $channelid,
            'channeluid'=> $channeluid,
            'cpguid'=> $cpguid,
            'goodsname'=> $goodsname,
            'fee'=> $fee,
            'ext'=> $ext,
            'timestamp'=> $timestamp,
        );

        $this->load->model('Common_model');


        $sign_str = $this->Common_model->sort_params($_data).'&'.$key;

        $sign_str = "channelid=$channelid&channeluid=$channeluid&cpgameid=$cpgameid&cpguid=$cpguid&ext=$ext&fee=$fee&goodsname=$goodsname&order=$order&qqesuid=$qqesuid&timestamp=$timestamp&$key";
        // echo $sign_str;
        $sign = md5($sign_str);
        // $sign = $this->sign($key,$_data);
        $data =array(
            'cpguid'=>$cpguid,
            'time'=>$timestamp,
            'sign'=>$sign,
        );

        return $data;
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
        $time = time();
        $uid = $user->p_uid;
        $sign = md5('appkey='.$this->app_key."&time=$time&uid=$uid&qqes");

        $url = 'http://www.7724.com/networkgame/getUserInfo?appkey='.$this->app_key."&time=$time&uid=$uid&sign=$sign";

        $content = $this->Curl_model->curl_get($url);

        log_message('debug', "allu focus $url '$content'");
        if ($content) {
            $obj = json_decode($content);
            if (isset($obj->isSubscribe) && $obj->isSubscribe == 1) {
                return 1;
            }
        }

        return 0;
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }

    public function create_role_report()
    {
        $this->load->model('Create_role_report_model');
        $user_ids = $this->input->get('user_ids');
        if ($user_ids) {
            $ids = explode(',', $user_ids);
            $condition = array('platform' => $this->platform);
            $where_in = array(
                'name' => 'p_uid',
                'values' => $ids,
            );
            $res = $this->Create_role_report_model->get_by_condition($condition, null, null, null, null, null, $where_in);
            if ($res) {
                echo json_encode($res);
            } else {
                echo json_encode(array());
            }

            return;
        }

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
        $this->load->model('Login_report_model');
        $user_ids = $this->input->get('user_ids');
        if ($user_ids) {
            $ids = explode(',', $user_ids);
            $condition = array('platform' => $this->platform);
            $where_in = array(
                    'name' => 'p_uid',
                    'values' => $ids,
                );
            $res = $this->Login_report_model->get_by_condition($condition, null, null, null, null, null, $where_in);
            if ($res) {
                echo json_encode($res);
            } else {
                echo json_encode(array());
            }

            return;
        }

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
        $this->load->model('Sign_report_model');
        $user_ids = $this->input->get('user_ids');
        if ($user_ids) {
            $ids = explode(',', $user_ids);
            $condition = array('platform' => $this->platform);
            $where_in = array(
                        'name' => 'p_uid',
                        'values' => $ids,
                    );
            $res = $this->Sign_report_model->get_by_condition($condition, null, null, null, null, null, $where_in);
            if ($res) {
                echo json_encode($res);
            } else {
                echo json_encode(array());
            }

            return;
        }

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
                $user['2460_user_id'] = $one->user_id;
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
    public function sign($signKey, $params)
    {
        ksort($params);
        $string = '';
        foreach ($params as $key => $value) {
            $string .= $key . '=' . $value . '&';
        }

        return md5($string . $signKey);
    }
}
