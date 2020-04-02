<?php

class Jianguo_model extends CI_Model
{
    public $platform = 'jianguo';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login()
    {
        $token = $this->input->get('token');
        $user_id = $this->input->get('uid');
        $condition = array(
            'p_uid' => $user_id,
            'platform' => $this->platform,
        );
        // echo  $response->data->gouzaiId;

        $user = $this->User_model->get_one_by_condition_array($condition);

        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $user_id,
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
        $this->cache->save($user['user_id'] . '_token', md5($user['user_id'] . $user['platform'] . time()), 86400);

        return $user['user_id'];
    }

    public function sign_order($game_id = '')
    {
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

        $openKey = $this->cache->get($openId . '_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId . $noice . $game->app_key);
        $game_url = $game->game_login_url;
        if ($game_id == 1013) {
            $test_id = array();
            if (in_array($openId, $test_id)) {
                $game_url = 'http://122.152.194.83:8083/api';
            }
        }
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "allu login:$url");
        if ($game->game_father_id == 20006) {
            $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId&platform=$this->platform&platformId=$game_id";
        }
        if ($game->game_father_id == 20020) {
            $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId&platform=$this->platform&platformId=$game_id&sdkType=xileyou";
        }
        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        log_message('debug',$this->platform.' '.json_encode($_POST));
        $order_id = $this->input->get_post('ext');
        $money = $this->input->get_post('product_price');
        $status = $this->input->get_post('order_status');
        if ($status != 2) {
            return;
        }
        $this->load->model('Game_order_model');
        $condition = array('u_order_id' => $order_id);
        $game_order = $this->Game_order_model->get_one_by_condition($condition);

        if (!$game_order) {
            return;
        }
        if ($game_order->money != intval($money * 100)) {
            return;
        }

        return $order_id;
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAILURE';
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

        $url = 'http://h5.allugame.com/index.php/api/focus?openid=' . $user->p_uid;

        // $content = $this->Curl_model->curl_get($url);

        log_message('debug', "allu focus $url '$content'");

        return $content;
    }

    public function login_collect($data)
    {

        //执行统计请求
        $access_token = md5(substr(time(), 0, 8) . 'aoyouxi');
        //定义统计请求的地址：
        $user_id = $data['p_uid'];
        $game_id = $data['game_id'];
        $url = "http://h5.allugame.com/tongji/tongji_game_login/{$user_id}/{$game_id}/{$access_token}";
        // $this->Curl_model->curl_get($url);
    }

    public function create_role_collect($data)
    {
        //执行统计请求
        $access_token = md5(substr(time(), 0, 8) . 'aoyouxi');
        //定义统计请求的地址：
        $user_id = $data['p_uid'];
        $game_id = $data['game_id'];
        $url = "http://h5.allugame.com/tongji/tongji_create_role/{$user_id}/{$game_id}/{$access_token}";
        // $this->Curl_model->curl_get($url);
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

    public function order_query()
    {
        $this->load->model('Game_order_model');
        $user_ids = $this->input->get('user_ids');
        if ($user_ids) {
            $ids = explode(',', $user_ids);
            $condition = array('platform' => $this->platform);
            $where_in = array(
                'name' => 'p_uid',
                'values' => $ids,
            );

            $users = $this->User_model->get_by_condition($condition, null, null, null, null, null, $where_in);
            if ($users) {
                $ids = array();
                foreach ($users as $one) {
                    $ids[] = $one->user_id;
                }

                $where_in = array(
                    'name' => 'user_id',
                    'values' => $ids,
                );

                $res = $this->Game_order_model->get_by_condition($condition, null, null, null, null, null, $where_in);
                if ($res) {
                    foreach ($res as $order) {
                        foreach ($users as $one_user) {
                            if ($order->user_id == $one_user->user_id) {
                                $order->p_uid = $one_user->p_uid;
                            }
                        }
                    }
                    echo json_encode($res);
                } else {
                    echo json_encode(array());
                }
            }

            return;
        }
    }

    private function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }

    public function composeReq($reqJson, $vkey)
    {
        //获取待签名字符串
        $content = json_encode($reqJson);
        //格式化key，建议将格式化后的key保存，直接调用
        $vkey = $this->formatPriKey($vkey);

        //生成签名
        $sign = $this->sign($content, $vkey);

        //组装请求报文，目前签名方式只支持RSA这一种
        $reqData = 'transdata=' . urlencode($content) . '&sign=' . urlencode($sign) . '&signtype=RSA';

        return $reqData;
    }

    public function h5composeReq($reqJson, $vkey)
    {
        //获取待签名字符串
        $content = json_encode($reqJson);
        //格式化key，建议将格式化后的key保存，直接调用
        $vkey = $this->formatPriKey($vkey);

        //生成签名
        $sign = $this->sign($content, $vkey);

        //组装请求报文，目前签名方式只支持RSA这一种
        $reqData = 'data=' . urlencode($content) . '&sign=' . urlencode($sign) . '&sign_type=RSA';

        return $reqData;
    }

    public function formatPriKey($priKey)
    {
        $fKey = "-----BEGIN RSA PRIVATE KEY-----\n";
        $len = strlen($priKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($priKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= '-----END RSA PRIVATE KEY-----';

        return $fKey;
    }

    public function sign($data, $priKey)
    {
        //转换为openssl密钥
        $res = openssl_get_privatekey($priKey);

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //base64编码
        $sign = base64_encode($sign);

        return $sign;
    }
}
