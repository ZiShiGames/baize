<?php

class Onedufu_model extends CI_Model
{
    public $platform = 'onedufu';
    public $SecretKey = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $appKey = $this->input->get('appKey'); //游戏ID
        $userToken = $this->input->get('userToken'); //用户凭证
        $state = ''; //cp的自定义参数
        $hlmy_gw = $this->input->get('hlmy_gw'); //1758平台自定义参数 获取到之后原样传递
        $hlmy_gp = $this->input->get('hlmy_gp'); //游戏适配参数 获取到之后原样传递
        $nonce = $this->input->get('nonce'); //随机串，不长于32位
        $timestamp = $this->input->get('timestamp'); //当前时间戳（秒）

        $sign = $this->input->get('sign'); //返回的签名字符串

        if (!$appKey || !$userToken || !$timestamp || !$nonce) {
            return false;
        }

        //签名判断
        $data1 = array(
            'appKey' => $appKey,
            'userToken' => $userToken,
            'state' => $state,
            'hlmy_gw' => $hlmy_gw,
            'hlmy_gp' => $hlmy_gp,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
        );

        $this->load->model('Common_model');
        $p_str = $this->Common_model->sort_params($data1);

        $SecretKey = $this->Game_model->get_key($game_id, 'SecretKey');
        $my_sign = md5($p_str . $SecretKey);
        if ($my_sign != $sign) {
            log_message('error', $this->platform . 'One sign error');

            // return false;
        }

        //请求用户数据的签名
        $data2 = array(
            'appKey' => $appKey,
            'userToken' => $userToken,
            'hlmy_gw' => $hlmy_gw,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
        );

        $p_str = $this->Common_model->sort_params($data2) . $SecretKey;
        log_message('debug', $this->platform . ' sign str ' . $p_str . ' ' . $SecretKey);
        $sign_for_info = md5($p_str);
        $url = 'http://api.1758.com/auth/v4.1/verifyUser.json';
        $data3 = array(
            'appKey' => $appKey,
            'userToken' => $userToken,
            'hlmy_gw' => $hlmy_gw,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
            'sign' => $sign_for_info,
        );
        //必须用post请求
        $content = $this->Curl_model->curl_post($url, $data3);
        if (!$content) {
            return false;
        }
        log_message('debug', $this->platform . ' ' . $content . ' ' . json_encode($data3));

        $response = json_decode($content, true);
        if (!$response['result']) {
            return false;
        }
        // if (!isset($response['data']['userInfo']['nickName'])) {
        //     return false;
        // }
        $condition = array(
            'p_uid' => $response['data']['userInfo']['gid'],
            'platform' => $this->platform,
        );

        $user = $this->User_model->get_one_by_condition_array($condition);

        //如果没有用户信息，那么我们通过接口获取
        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $response['data']['userInfo']['gid'],
                'nickname' => $response['data']['userInfo']['nickName'],
                'avatar' => $response['data']['userInfo']['avatar'],
                'create_date' => time(),
            );

            $user_id = $this->User_model->add($user);
            if (!$user_id) {
                log_message('error', $this->platform . "Login error user create $content");

                return false;
            }

            $user['user_id'] = $user_id;
        }

        $this->cache->save($user['user_id'] . '_token', md5($user['user_id'] . $user['platform'] . time()), 86400);

        $tempdata = array(
            'gid' => $user['p_uid'],
            'appKey' => $appKey,
            'hlmy_gw' => $hlmy_gw,
        );

        log_message('error', $this->platform . "1758 ps 1 '" . $user['p_uid'] . "' '$appKey' '$hlmy_gw'");
        $this->session->set_userdata('userInfo', $tempdata);
        $this->session->set_userdata($userToken, $user['p_uid']);
        $this->cache->save('1758_info_' . $user['user_id'], $tempdata, 3600 * 24);
        $this->session->set_userdata('1758_info_' . $user['user_id'], $tempdata, 3600 * 24);
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
        $server_name = $this->input->get('server_name');
        $pfid = $this->input->get('pfid');
        $noice = time();
        $sign = md5($openId . $noice . $game->app_key);
        $game_url = $game->game_login_url;
        $spe_server_ids = array('2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '8000');
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
        $orderInfo = $this->input->post('orderInfo');
        // $appKey = $this->input->post('appKey');
        //$appKey = $this->session->tempdata('appKey');//    游戏id
        // $gid = $this->input->post('gid');
        // $itemCode = $this->input->post('itemCode');
        // $buyAmount = $this->input->post('buyAmount');
        // $status = $this->input->post('status');
        $sign = $this->input->post('sign');
        //  $orderInfo = $this->input->post('orderInfo');//不参与签名

//        $data = array(
        //            'appKey' => $appKey,
        //            'gid' => $gid,
        //            'orderId' => $orderId,
        //            'itemCode' => $itemCode,
        //            'buyAmount' => $buyAmount,
        //            'status' => $status,
        //        );
        //
        //        $this->load->model('Common_model');
        //        $p_str = $this->Common_model->sort_params($data);
        //        $my_sign = md5($p_str.$this->SecretKey);
        //
        //        if($my_sign != $sign){
        //            log_message('error', 'sign error');
        //            return false;
        //        }
        //        if (!$orderInfo || !$sign) {
        //            return;
        //        }
        // pay back door
        // if($this->input->get('order_id')){
        //     return $this->input->get('order_id');
        // }
        $orderInfo_obj = json_decode($orderInfo);
        $order_id = $orderInfo_obj->txId;

        $money = $orderInfo_obj->totalFee;

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
    }

    public function notify_ok()
    {
        echo 'success';
    }

    public function notify_error()
    {
        echo 'failed';
    }

    public function sign_order($game_id = '')
    {
        // 1395

        // 梦回西游
        $money_to_code = array(
            6 => 117702,
            30 => 117703,
            60 => 117704,
            98 => 117705,
            198 => 117706,
            328 => 117707,
            648 => 117708,
            1000 => 117709,
            2000 => 117710,
            8 => 117711,
            18 => 117712,
            40 => 117713,
            50 => 117714,
            12 => 117715,
            88 => 117716,
            108 => 117717,
            188 => 117718,
            25 => 117719,
        );

        //订单处理
        $money = $this->input->get('money');
        $txId = $this->input->get('txId'); //订单号 , 可为空
        $openId = $this->input->get('openId'); //订单号 , 可为空
        $goodsName = urldecode($this->input->get('goodsName')); // 商品名称

        // $info = $this->cache->get('1758_info_'.$openId);
        $info = $this->session->userdata('1758_info_' . $openId);
        $gid = $this->input->get('gid'); //$info['gid'];//1758用户的gid
        $appKey = $this->input->get('appKey'); //$info['appKey'];//1758用户的gid
        $hlmy_gw = $this->input->get('hlmy_gw'); //$info['hlmy_gw'];//1758用户的gid

        //分享关注使用。。。。。。。。。。。。。。。。。。。
        $init_data = $this->input->get('init_data');
        if ($init_data == 'initData') {
            $data_init = array(
                'gid' => $gid,
                'appKey' => $appKey,
                'hlmy_gw' => $hlmy_gw,
            );

            return $data_init;
        }
        //分享关注使用。。。。。。。。。。。。。。。。。。。

        log_message('error', "1758 ps 2 '$gid' '$appKey' '$hlmy_gw' '$openId'");

        $itemCode = 0; //道具编号（不同道具对应的 计费代码）
        $state = '';
        $nonce = md5(time()); //随机串，不长于32位
        $timestamp = time();

        //查找 对应 计费代码
        foreach ($money_to_code as $key => $val) {
            if ($key == intval($money)) {
                $itemCode = $val; //道具编号
            }
        }

        $data = array(
            'gid' => $gid,
            'appKey' => $appKey,
            'hlmy_gw' => $hlmy_gw,
            'itemCode' => $itemCode,
            'money' => $money,
            'txId' => $txId,
            'state' => $state,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
        );

        $this->load->model('Common_model');
        $p_str = $this->Common_model->sort_params($data);

        $this->SecretKey = $this->Game_model->get_key($game_id, 'SecretKey');
        $sign = md5($p_str . $this->SecretKey);

        $data = array(
            'gid' => $gid,
            'appKey' => $appKey,
            'hlmy_gw' => $hlmy_gw,
            'itemCode' => $itemCode,
            'money' => $money,
            'txId' => $txId,
            'state' => $state,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
            'sign' => $sign,
        );

        $url = 'http://api.1758.com/pay/v4.1/unifiedorder.json';
        $content = $this->Curl_model->curl_post($url, $data);
        log_message('debug', '1758 data ' . json_encode($data));
        $response = json_decode($content, true);
        log_message('debug', '1758 content' . json_encode($response));

        log_message('error', '1758 unifiedorder ok ' . $response['errorcode'] . ' ' . $content . ' p ' . json_encode($data));
        if ($response['result'] == 1) {
            $deal_data = array(
                'gid' => $gid,
                'appKey' => $appKey,
                'hlmy_gw' => $hlmy_gw,
                'sign' => $response['data']['paySafecode'],
            );

            return $deal_data;
        } else {
            return false;
        }
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

        $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/api/?m=player&fn=getserverlist&openId=' . $openId;
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

        $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/9g/api/?m=player&fn=getserverlist&openId=' . $openId;
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
    public function focus($game_id = '')
    {
        $appKey = $this->input->get('appKey'); //游戏ID
        $userToken = $this->input->get('userToken'); //用户凭证
        $state = ''; //cp的自定义参数
        $hlmy_gw = $this->input->get('hlmy_gw'); //1758平台自定义参数 获取到之后原样传递
        $hlmy_gp = $this->input->get('hlmy_gp'); //游戏适配参数 获取到之后原样传递
        $nonce = $this->input->get('nonce'); //随机串，不长于32位
        $timestamp = $this->input->get('timestamp'); //当前时间戳（秒）

        $sign = $this->input->get('sign'); //返回的签名字符串

        if (!$appKey || !$userToken || !$timestamp || !$nonce) {
            return false;
        }

        //签名判断
        $data1 = array(
            'appKey' => $appKey,
            'userToken' => $userToken,
            'state' => $state,
            'hlmy_gw' => $hlmy_gw,
            'hlmy_gp' => $hlmy_gp,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
        );

        $this->load->model('Common_model');
        $p_str = $this->Common_model->sort_params($data1);

        $SecretKey = $this->Game_model->get_key($game_id, 'SecretKey');
        $my_sign = md5($p_str . $SecretKey);
        if ($my_sign != $sign) {
            log_message('error', $this->platform . 'One sign error');

            // return false;
        }

        //请求用户数据的签名
        $data2 = array(
            'appKey' => $appKey,
            'userToken' => $userToken,
            'hlmy_gw' => $hlmy_gw,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
        );

        $p_str = $this->Common_model->sort_params($data2) . $SecretKey;
        $sign_for_info = md5($p_str);
        $url = 'http://api.1758.com/auth/v4.1/verifyUser.json';
        $data3 = array(
            'appKey' => $appKey,
            'userToken' => $userToken,
            'hlmy_gw' => $hlmy_gw,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
            'sign' => $sign_for_info,
        );
        //必须用post请求
        $content = $this->Curl_model->curl_post($url, $data3);
        if (!$content) {
            return false;
        }

        $response = json_decode($content, true);
        if (!$response['result']) {
            return false;
        }
        if (!isset($response['data']['userInfo']['nickName'])) {
            return false;
        }

        return $response['data']['userInfo']['gid'];
    }
}
