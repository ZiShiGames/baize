<?php

class One_model extends CI_Model
{
    public $platform = 'one';
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
        // 龙城
        if ($game_id == 1019) {

            //游戏商品价格（元） 和 计费代码 对应数组(临时处理办法，相当于将商品名称、价格、计费代码 存放到数据库中又对应取出。)
            $money_to_code = array(
                10 => 108491,
                20 => 108492,
                50 => 108493,
                100 => 108494,
                200 => 108495,
                500 => 108496,
                1000 => 108497,
                2000 => 108498,
                28 => 108499,
            );
            // 超梦的逆袭（游戏名 ）
        } elseif ($game_id == 1426) {
            $money_to_code = array(
                6 => 115001,
                30 => 115002,
                100 => 115003,
                500 => 115004,
                2000 => 115005,
            );
            // 机甲三国
        } elseif ($game_id == 1040) {
            $money_to_code = array(
                6 => 108875,
                28 => 108876,
                98 => 108877,
                198 => 108878,
                328 => 108879,
                648 => 108880,
                1000 => 108919,
                2000 => 108920,
                30 => 108921,
                88 => 108922,
                1 => 109011,
                10 => 109012,
            );
        } elseif ($game_id == 1168) {
            $money_to_code = array(
                6 => 112088,
                30 => 112089,
                50 => 112090,
                128 => 112091,
                288 => 112092,
            );
        } elseif ($game_id == 1169) {
            $money_to_code = array(
                1 => 112081,
                10 => 112082,
                47 => 112083,
                90 => 112084,
                255 => 112085,
                480 => 112086,
                750 => 112087,
            );
        } elseif ($game_id == 1360) {
            // $goodsName
            $money_to_code = array(
                10 => 113712,
                20 => 113713,
                30 => 113714,
                50 => 113715,
                100 => 113716,
                200 => 113717,
                300 => 113718,
                400 => 113719,
                500 => 113720,
                600 => 113721,
                800 => 113722,
                1000 => 113723,
                1200 => 113724,
                1500 => 113725,
                1800 => 113726,
                2000 => 113727,
                2500 => 113728,
                3000 => 113729,
                10 => 113730,
                18 => 113731,
                25 => 113732,
                48 => 113733,
                68 => 113734,
                98 => 113735,
                188 => 113736,
                288 => 113737,
                50 => 113820,
                648 => 113819,
            );
            // 梦回西游
        } elseif ($game_id == 1393) {
            $money_to_code = array(
                6 => 117335,
                30 => 117336,
                60 => 117337,
                98 => 117338,
                198 => 117339,
                328 => 117340,
                648 => 117341,
                8 => 117342,
                18 => 117343,
                40 => 117344,
                50 => 117345,
                12 => 117346,
                88 => 117347,
                108 => 117348,
                188 => 117349,
                25 => 117350,
                1000 => 117351,
                2000 => 117352,
            );
            // 天域世界
        } elseif ($game_id == 1394) {
            $money_to_code = array(
                6 => 117353,
                30 => 117354,
                98 => 117355,
                128 => 117356,
                198 => 117357,
                328 => 117358,
                648 => 117359,
                1296 => 117360,
                2592 => 117361,
                3888 => 117362,
                5184 => 117363,
                6480 => 117364,
                30 => 117365,
                88 => 117366,
                198 => 117367,
                648 => 117368,
                98 => 117369,
                28 => 117370,
                88 => 117371,
                18 => 117372,
                50 => 117373,
                88 => 117374,
                128 => 117375,
                188 => 117376,
                328 => 117377,
                648 => 117378,
                988 => 117379,
                1388 => 117380,
                1988 => 117381,
                6 => 117382,
                18 => 117383,
                30 => 117384,
                98 => 117385,
                168 => 117386,
                328 => 117387,
                648 => 117388,
                888 => 117389,
                1288 => 117390,
                1888 => 117391,
                188 => 117392,
                68 => 117393,
                98 => 117394,
            );
            // 不思议地下城
        } elseif ($game_id == 1488) {
            $money_to_code = array(
                6 => 119204,
                18 => 119205,
                30 => 119206,
                68 => 119207,
                128 => 119208,
                328 => 119209,
            );
        }

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

        // 天域世界
        if ($game_id == 1394) {
            // 判断相同金额的不同物品
            // 传递不同道具编号
            switch ($goodsName) {
                case '600仙钻':
                    $itemCode = '117353';
                    break;
                case '3000仙钻':
                    $itemCode = '117354';
                    break;
                case '9800仙钻':
                    $itemCode = '117355';
                    break;
                case '12800仙钻':
                    $itemCode = '117356';
                    break;
                case '19800仙钻':
                    $itemCode = '117357';
                    break;
                case '32800仙钻':
                    $itemCode = '117358';
                    break;
                case '64800仙钻':
                    $itemCode = '117359';
                    break;
                case '129600仙钻':
                    $itemCode = '117360';
                    break;
                case '259200仙钻':
                    $itemCode = '117361';
                    break;
                case '388800仙钻':
                    $itemCode = '117362';
                    break;
                case '518400仙钻':
                    $itemCode = '117363';
                    break;
                case '648000仙钻':
                    $itemCode = '117364';
                    break;
                case '强者基金1':
                    $itemCode = '117365';
                    break;
                case '强者基金2':
                    $itemCode = '117366';
                    break;
                case '强者基金3':
                    $itemCode = '117367';
                    break;
                case '强者基金4':
                    $itemCode = '117368';
                    break;
                case '绝世礼包':
                    $itemCode = '117369';
                    break;
                case '紫金':
                    $itemCode = '117370';
                    break;
                case '至尊':
                    $itemCode = '117371';
                    break;
                case '【一星狂欢包】':
                    $itemCode = '117372';
                    break;
                case '【二星狂欢包】':
                    $itemCode = '117373';
                    break;
                case '【三星狂欢包】':
                    $itemCode = '117374';
                    break;
                case '【四星狂欢包】':
                    $itemCode = '117375';
                    break;
                case '【五星狂欢包】':
                    $itemCode = '117376';
                    break;
                case '【六星狂欢包】':
                    $itemCode = '117377';
                    break;
                case '【七星狂欢包】':
                    $itemCode = '117378';
                    break;
                case '【八星狂欢包】':
                    $itemCode = '117379';
                    break;
                case '【九星狂欢包】':
                    $itemCode = '117380';
                    break;
                case '【十星狂欢包】':
                    $itemCode = '117381';
                    break;
                case '【特价礼包一】':
                    $itemCode = '117382';
                    break;
                case '【特价礼包二】':
                    $itemCode = '117383';
                    break;
                case '【特价礼包三】':
                    $itemCode = '117384';
                    break;
                case '【特价礼包四】':
                    $itemCode = '117385';
                    break;
                case '【特价礼包五】':
                    $itemCode = '117386';
                    break;
                case '【特价礼包六】':
                    $itemCode = '117387';
                    break;
                case '【特价礼包七】':
                    $itemCode = '117388';
                    break;
                case '【特价礼包八】':
                    $itemCode = '117389';
                    break;
                case '【特价礼包九】':
                    $itemCode = '117390';
                    break;
                case '【特价礼包十】':
                    $itemCode = '117391';
                    break;
                case '【活动礼包】':
                    $itemCode = '117392';
                    break;
                case '超值特惠礼包':
                    $itemCode = '117393';
                    break;
                case '超值惊喜礼包':
                    $itemCode = '117394';
                    break;
                default:
                    foreach ($money_to_code as $key => $val) {
                        if ($key == intval($money)) {
                            $itemCode = $val; //道具编号
                        }
                    }
                    break;
            }
            // 1758渠道
        } elseif ($game_id == 1360) {
            // 判断相同金额的不同物品
            // 传递不同道具编号
            switch ($goodsName) {
                case '1000元宝':
                    $itemCode = '113712';
                    break;
                case '2000元宝':
                    $itemCode = '113713';
                    break;
                case '3000元宝':
                    $itemCode = '113714';
                    break;
                case '5000元宝':
                    $itemCode = '113715';
                    break;
                case '10000元宝':
                    $itemCode = '113716';
                    break;
                case '20000元宝':
                    $itemCode = '113717';
                    break;
                case '30000元宝':
                    $itemCode = '113718';
                    break;
                case '40000元宝':
                    $itemCode = '113719';
                    break;
                case '50000元宝':
                    $itemCode = '113720';
                    break;
                case '60000元宝':
                    $itemCode = '113721';
                    break;
                case '80000元宝':
                    $itemCode = '113722';
                    break;
                case '100000元宝':
                    $itemCode = '113723';
                    break;
                case '120000元宝':
                    $itemCode = '113724';
                    break;
                case '150000元宝':
                    $itemCode = '113725';
                    break;
                case '180000元宝':
                    $itemCode = '113726';
                    break;
                case '200000元宝':
                    $itemCode = '113727';
                    break;
                case '250000元宝':
                    $itemCode = '113728';
                    break;
                case '300000元宝':
                    $itemCode = '113729';
                    break;
                case '10元礼包':
                    $itemCode = '113730';
                    break;
                case '周卡':
                    $itemCode = '113731';
                    break;
                case '月卡':
                    $itemCode = '113732';
                    break;
                case '1阶星灵':
                    $itemCode = '113733';
                    break;
                case '2阶星灵':
                    $itemCode = '113734';
                    break;
                case '3阶星灵':
                    $itemCode = '113735';
                    break;
                case '4阶星灵':
                    $itemCode = '113736';
                    break;
                case '5阶星灵':
                    $itemCode = '113737';
                    break;
                case '6阶星灵':
                    $itemCode = '113819';
                    break;
                case '一口价':
                    $itemCode = '113820';
                    break;
                default:
                    foreach ($money_to_code as $key => $val) {
                        if ($key == intval($money)) {
                            $itemCode = $val; //道具编号
                        }
                    }
                    break;
            }
        } else {

            //查找 对应 计费代码
            foreach ($money_to_code as $key => $val) {
                if ($key == intval($money)) {
                    $itemCode = $val; //道具编号
                }
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
