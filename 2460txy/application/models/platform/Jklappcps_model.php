<?php

class Jklappcps_model extends CI_Model
{
    public $platform = 'Jklappcps';
    public function __construct()
    {
        parent::__construct();
        
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }
    
    public function login($game_id)
    {
        $uid = $this->input->get('account'); //uid用户唯一ID
        $password = $this->input->get('password');
        
        $condition = array(
            'account' => $uid,
            'password' => $password,
        );
        $this->load->model('Allu_user_model');
        $check = $this->Allu_user_model->get_one_by_condition($condition);
        if (!$check) {
            echo 'err user';
            return;
        }
        
        // if(!$this->cache->get($uid.'_token')){
        //     echo 'no token';
        //     exit;
        // }
        // if(!$this->session->userdata($uid.'_token')){
        //     echo 'no session';
        //     exit;
        // }
        $condition = array(
            'p_uid' => $uid,
            'platform' => $this->platform,
            'game_id'=>$game_id,
        );
        
        $user = $this->User_model->get_one_by_condition_array($condition);
        
        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $uid,
                'create_date' => time(),
                'game_id'=>$game_id,
            );
            
            $user_id = $this->User_model->add($user);
            if (!$user_id) {
                log_message('error', 'Login error user create fail');
                
                return false;
            }
            
            $user['user_id'] = $user_id;
        } else {
        }
        $this->cache->save($user['user_id'] . '_token', md5($user['user_id'] . $user['platform'] . time()), 86400);
        
        return $user['user_id'];
    }
    
    public function sign_order($game_id = '')
    {
        
        $order_id = $this->input->get('order_id');
        $money = $this->input->get('money');
        $openId = $this->input->get('openId');
        $userId = $this->input->get('userId');
        $goodsName = $this->input->get('goodsName');
        //             $type = 'MWEB';
        //             $app_key='Bre0ly3bxa6jz7d4l9rwfuyui6g4ft7Z';
        //             $pay_appid='wx17c8eab06395622a';
        $notify_url=$url = "https://".$_SERVER['HTTP_HOST']."/index.php/api/notify/$this->platform";
        //             $mchid='1535960531';
        //             $app_secret='0dfbbd3b0b5dc79407d4bab465947681';
        
        $game = $this->Game_model->get_by_game_id($game_id);
        
        $this->load->model('Game_order_model');
        $condition = array('u_order_id' => $order_id);
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (!$game_order&&$game_order->u_order_id!=$order_id) {
            return;
        }
        //             $this->load->model('Wxpay_model');
        
        //             $result = $this->Wxpay_model->unifiedorder(null, $order_id, $goodsName, $goodsName, $pay_appid, $game_order->money, $notify_url, $mchid, $app_secret, $app_key, $type);
        
        
        //            $this->load->model('Curl_model');
        
        //            $response=$this->Curl_model->curl_get($result->mweb_url.'&redirect_url='.urlencode('http://api.baizegame.com'),array('Referer'=>'http://api.baizegame.com/',
        //                'CLIENT-IP'=>$_SERVER['REMOTE_ADDR'],
        //                'X-FORWARDED-FOR'=>$_SERVER['REMOTE_ADDR'],
        //            ));
        //            $domain=strstr($response, 'weixin://');
        //            $newa=substr($domain,0,stripos($domain,'"')-1);
        //            $domain=strstr($domain,'";',true);
        
        //             log_message('debug', "wxpay h5 log : ".$response);
        
        $url_parmas=http_build_query( array(
            'wx_pay_url'=>self::ipaynow('13','2',$notify_url,$game_order->money,$goodsName,$game_order->u_order_id),//$result->mweb_url,
            //$result->mweb_url,
            'goodsName'=>$goodsName,
            'money'=>sprintf("%.2f",$money),
            'order_id'=>$game_order->u_order_id,
            'ali_pay_url'=>"https://".$_SERVER['HTTP_HOST']."/index.php/alipay/alipay2?order_id=".$game_order->u_order_id,
        ));
        $data = array(
            'pay_url' => "//".$_SERVER['HTTP_HOST']."/h5pay.html?".$url_parmas,
        );
        return $data;
    }
    //现在支付封装
    private $ipaynow_data=array('appid'=>'156592706704113',
        'key'=>'84982d40kjwLMjPZICcsmAXcSXvWerQf',
        'notify_ok'=>'success=Y',
        'notify_error'=>'success=N'
    );
    private function ipaynow($payChannelType,$outputType,$notify_url,$money,$goods_name,$order_id){
        $req=array();
        $req["appId"]             = $this->ipaynow_data['appid'];
        $key                      =$this->ipaynow_data['key'];
        $TRADE_URL="https://pay.ipaynow.cn";//正式交易接口地址
        
        $req["mhtSignType"]       = 'MD5'; //商户签名方法
        
        $req["deviceType"]        = '0601';
        $req["frontNotifyUrl"]    = $notify_url;//前台通知地址
        $req["funcode"]           = 'WP001';//功能码
        $req["mhtCharset"]        = 'UTF-8';//编码
        $req["mhtCurrencyType"]   = '156';//货币类型 156 人民币
        $req["mhtOrderAmt"]       = $money;//商户订单交易金额
        $req["mhtOrderDetail"]    = $goods_name;//商户订单详情
        $req["mhtOrderName"]      = $goods_name;//商户商品名称
        $req["mhtOrderNo"]        = $order_id;//商户订单号 后期同一订单预下单两种支付思路1：（订单ID+支付类型）返回进行截取通知发货
        $req["mhtOrderStartTime"] = date("YmdHis");//商户订单开始时间
        $req["mhtOrderTimeOut"]   = '3600';//商户订单超时时间
        $req["mhtOrderType"]      = "01";//商户交易类型 01 普通消费
        $req["notifyUrl"]         = $notify_url;//后台通知地址
        $req["outputType"]        = $outputType;//   0 默认值    // 2  微信deeplink模式
        //①　outputType=0时，直接跳转微信支付页面，已进行页面封装；
        //②　outputType=2时，返回weixin://支付链接，需商户在前端使用html中的a标签调起支付；
        //③　outputType=5时，返回微信MWEB_URL= https://wx.tenpay...支付链接，商户通过mweb_url调起微信支付中间页，
        //    如需返回至指定页面，则可以在MWEB_URL后拼接上redirect_url参数，来指定回调页面
        $req["payChannelType"]    = $payChannelType; //12 支付宝  //13 微信 //20 银联  //25  手Q
        if($outputType!='2'&&$payChannelType=='13'){
            $info = file_get_contents('http://myip.ipip.net');
            $ipstr = explode('：',$info);
            $ip = explode(' ', $ipstr[1]);
            $req["consumerCreateIp"]  = $ip[0]; //微信必填// outputType=2时 无须上送该值
        }
        $req["version"]           = "1.0.0";
        ksort($req);
        $strs=$str=urldecode(http_build_query($req).'&');
        $str .= strtolower(md5($key));
        $req_str=strtolower(md5($str));
        $strs.='mhtSignature='.$req_str;
        //         log_message('debug', "ipaynow post data ".$strs.' '.$str);
        $content = $this->Curl_model->curl_post($TRADE_URL,$strs);
        @parse_str($content, $arr);
        
        //         var_dump($arr);
        if($arr['responseCode']=='A001'){
            return urldecode($arr['tn']);
        }else{
            return ;
            log_message('debug', "ipaynow error ".json_encode($arr).' '.json_encode($content));
        }
    }
    
    private function ipaynow_notify(){
        $testxml = file_get_contents("php://input");
        log_message('debug', "ipaynow post data 3".json_encode($testxml));
        @parse_str($testxml, $result);//json_decode($testxml, true);
        if($result['appId']==$this->ipaynow_data['appid']){
            if($result['transStatus']=='A001'&&$result['mhtOrderNo']){
                log_message('debug', "ipaynow post data ".json_encode($result));
                $this->load->model('Game_order_model');
                $condition = array('u_order_id' => $result['mhtOrderNo'],'money'=>$result['mhtOrderAmt']);
                $game_order = $this->Game_order_model->get_one_by_condition($condition);
                if (!$game_order) {
                    return false;
                }else{
                    $this->load->model('Game_order_model');
                    $this->Game_order_model->update(array('platform_order_id'=>$result['channelOrderNo']), array('u_order_id' => $result['mhtOrderNo']));
                    return $result['mhtOrderNo'];
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
        $ipaynow=self::ipaynow_notify();
//         if($ipaynow){
//             $condition = array('u_order_id' => $ipaynow);
//             $game_order = $this->Game_order_model->get_one_by_condition($condition);
//             self::other_report($game_order,'weixinpay');
//             return $ipaynow;
//         }
//         //获取返回的xml
//         $testxml = file_get_contents("php://input");
//         //将xml转化为json格式
//         $jsonxml = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));
//         //转成数组
//         $result = json_decode($jsonxml, true);
//         log_message('debug', "wx h5 pay ".$jsonxml);
        if($ipaynow){
            //如果成功返回了
//             if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
            $order_id = $ipaynow;
                if (!$order_id) {
                    return;
                }
                $this->load->model('Game_order_model');
                $condition = array('u_order_id' => $order_id);
                $game_order = $this->Game_order_model->get_one_by_condition($condition);
                if (!$game_order) {
                    return;
                }
                // if ($game_order->money!= intval($money)) {
                //     return;
                // }
//                 self::other_report($game_order,'weixinpay');
                return $order_id;
//             }
        }
        
        $order_id = $this->input->get('order_id');
        $sign = $this->input->get('sign');
        $money = $this->input->get('money');
        if (!$order_id || !$sign) {
            return;
        }
        $this->load->model('Game_order_model');
        $condition=array('u_order_id'=>$order_id);
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (!$game_order) {
            return;
        }
//         self::other_report($game_order,'alipay');
        return $order_id;
    }
    
    //第三方订单上报
    public function other_report($parmas,$pay){
        $other_report=json_decode($parmas->other_report,true);
        //获取支付账号
        $this->load->model('User_model');
        $condition=array('user_id'=>$parmas->user_id);
        $user = $this->User_model->get_one_by_condition($condition);
        
        if (in_array($parmas->game_id, ['29', '39']) && $parmas->report=='0'){
            foreach ($other_report as $k=>$v){
                if($k=='track'){
                    
                    $data=array(
                        'appid'=>$v['_appkey'],
                        'who'=>$user->p_uid
                    );
                    $data['context']=array(
                        '_deviceid'=>$v['_deviceid'],
                        '_transactionid'=>$parmas->u_order_id,
                        '_paymenttype'=>$pay,
                        '_currencytype'=>'CNY',
                        '_currencyamount'=>round($parmas->money/100,2),
                        "_ip"=>$v['_ip'],
                        "_tz"=>"+8"
                    );
                    
                    if($v['_androidid']){
                        $data['context']['_imei']=$v['_deviceid'];
                        $data['context']['_androidid']=$v['_androidid'];
                    }else{
                        $data['context']['_idfa']=$v['_deviceid'];
                    }
                    log_message('debug', "other_report ".json_encode($data));
                    $this->load->model('Curl_model');
                    $url='http://log.trackingio.com/receive/tkio/payment';
                    $response=$this->Curl_model->curl_post($url,json_encode($data));
                    $this->db->set('report','1');
                    $this->db->where('u_order_id',$parmas->u_order_id);
                    $this->db->update('game_order');
                    log_message('debug', "other_report notify log".$response);
                }
            }
        }
    }
    
    public function notify_ok()
    {
        echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
    }
    
    public function notify_error()
    {
        echo 'FAILED';
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
        
        $content = $this->Curl_model->curl_get($url);
        
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
        $this->Curl_model->curl_get($url);
    }
    
    public function create_role_collect($data)
    {
        //执行统计请求
        $access_token = md5(substr(time(), 0, 8) . 'aoyouxi');
        //定义统计请求的地址：
        $user_id = $data['p_uid'];
        $game_id = $data['game_id'];
        $url = "http://h5.allugame.com/tongji/tongji_create_role/{$user_id}/{$game_id}/{$access_token}";
        $this->Curl_model->curl_get($url);
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
