<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wx_minigame extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'redis'));
        $this->load->model('Wxpay_model');
        $this->load->model('Mini_programs_model');
        $this->load->model('Mini_user_model');
        $this->load->model('Mini_game_order_model');
        if (!$this->cache->redis->is_supported()) {
            echo 'no redis';
            exit;
        }
    }
    public $appid = 'wx0fb2e2c1fd1db8c7';
    public $key = 'c88d8ea1531e98fdac17c0e66aab5326';
    // public $game_url = 'http://h7s0.fengzhangame.net/index2.php';
    public $game_url = 'https://wxh5-poke.fengzhangame.net/h5/start.php';
    public $notify_url = 'http://h5pay.xileyougame.com/index.php/wxpay/notify';
    public $pay_type = 'JSAPI';

    //531商户号
//    public $pay_appid = 'wxfe535376cd95ff9e';
//    public $pay_key = 'cd502521f75fe8c359bcd3f3d1deec0f';
//    public $mchid = '1535960531';
//    public $apikey = 'Bre0ly3bxa6jz7d4l9rwfuyui6g4ft7Z';

//    //301商户号
//    public $pay_appid = 'wx9fa1399a3b13f5bc';
//    public $pay_key = '6bf08a25bf10a060b51bd720b0b4c2e8';
//    public $mchid = '1572077301';
//    public $apikey = '314504ac90b4876581b43b278099956e';

//    //701商户号
    public $pay_appid = 'wx375234cb72d3b9bd';
    public $pay_key = '53b5edd4e9c38f6fdc95ab936ccbbf5f';
    public $mchid = '1560006701';
    public $apikey = 'mdy0y4htai4kbxw1uke526nh17g3ybbt';

    // 果壳小程序APPID组
    private function get_guoke_arr()
    {
        $guoke_arr = [
//             'wx9aed6e13a014caad', // 水果卡路里
//             'wx0fb2e2c1fd1db8c7', // 精灵N次方
//             'wx2b7fecca4b308096', // 涂鸦
            'wx576cd9788a2a9c91', //太古仙穹
            'wx1e736935ed9c9ba3',//纵剑仙界-青云神剑
            'wx49cda2492f8feedb',//超级小精灵go
            'wx6422ec3e91d4dc1e',//口袋精灵王
//             'wxd5c814262e2adf8c',//神奇精灵球
            'wxe421768ac88eb26f',//少年龙将传
            'wxef93db96e3d80021',//梦幻名将传
            'wx8f98ba4853f7a1f0',//爆萌小将
            'wxe15bc168663764e7',//御龙天下
            'wx04f82beabac00f73',//无双名将传
            'wx9253847260b00540',//灵域飞仙
            'wxc63c2c145dc2f62d',//六道剑尊
            'wxc68605872807dc4a',//六界剑仙
            'wx95e08b66eca6c2d5',//凡人升仙传
            'wx357839436e435516',//六道飞仙传
            'wxde51f3c55b61fe81',//逍遥飞仙
            'wx0d85ccf6e166b152',//御剑青云诀
            'wx0b59618311028aca', //无双群英传
            'wx93622ae9f4eac32c', //梦幻神兵
//             'wx2f37c8aea0353c5e', //精灵道馆
            'wxb12040fa41ff010a', //萌宠精灵球
            'wxb3a5a41c448cd56c',//梦幻萌宠
            'wx738e2ac8b0dfcfc6',//梦幻群雄传
//             'wxdbc194faa7400f99',//爆萌精灵球
            'wx35d670d46e24054d',//萌宠小精灵球
            'wx2f37c8aea0353c5e',//精灵道馆
            'wxb3a5a41c448cd56c',//梦幻萌宠
            'wxdbc194faa7400f99',//萌宠道馆官方站
            'wxbc22af6a65f82f36',//梦幻群英传
        ];
        return $guoke_arr;
    }
    //新版客服支付列表
    private  function get_payment_type(){
        $peyment_arr = [
            'wxe421768ac88eb26f', //少年龙将传
            'wx8f98ba4853f7a1f0',//爆萌小将
            'wx0b59618311028aca',//无双群英传
            'wxbc22af6a65f82f36',//梦幻群英传
            'wx738e2ac8b0dfcfc6',//梦幻群雄传
            'wx1e736935ed9c9ba3',//青云神剑
//             'wx2f37c8aea0353c5e', //精灵道馆
            'wxb12040fa41ff010a', //萌宠精灵球
//             'wxb3a5a41c448cd56c',//梦幻// 
            'wxdbc194faa7400f99',//萌宠小精灵球
//             'wx35d670d46e24054d',//梦幻训练师
            'wx2f37c8aea0353c5e',//精灵道馆
            'wxb3a5a41c448cd56c',//梦幻萌宠
            'wxdbc194faa7400f99',//萌宠道馆官方站
            
        ];
        return $peyment_arr;
    }
    
    //启用第三方H5支付游戏列表，可不绑定商户号进行支付。
    private function new_payType(){
        $peyType_arr = [
            'wx418df45c4b05ac3c',//定江山
            'wx48d352ff9c36a3ae',//口袋精灵王
        ];
        return $peyType_arr;
    }
    
    public function mini_game_login()
    {
        $appid = $this->input->get('appid');
        $code = $this->input->get('code');
        $channel = $this->input->get('channel');
        $where = array('channel'=>$channel,'appid'=>$appid);
        $this->load->model('Mini_game_channel_model');
        $mini_channel = $this->Mini_game_channel_model->get_one_by_condition($where);
        if (!$mini_channel){
            $add = array(
                'appid'=>$appid,
                'channel'=>$channel,
            );
            $this->Mini_game_channel_model->add($add);
        }
        $form_user_id = $this->input->get('form_user_id');
        // log_message('debug', 'mini ua : ' . ($_SERVER['HTTP_USER_AGENT']));
        $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $appid));
        $key = $mini_game->mini_key;
        if ($appid && $code && $channel) {
            $this->load->model('User_model');
            $requery_url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$key&js_code=$code&grant_type=authorization_code";
            $content = $this->Curl_model->curl_get($requery_url);
            if (!$content) {
                $this->Output_model->json_print(2, 'no content from jscode2session');
                log_message('error', 'no content from jscode2session');
                return;
            }
            $response = json_decode($content);
            if ($response && isset($response->errcode)) {
                $this->Output_model->json_print(3, 'jscode2session error ' . $response->errmsg);
                log_message('error', 'no content from jscode2session');
                return;
            }
            $openid = $response->openid;
            $session_key = $response->session_key;
            $condition = array('p_uid' => $openid, 'appid' => $appid);
            $user = $this->Mini_user_model->get_one_by_condition($condition);
            if ($user && !$user->unionid && $response->unionid){
                $add['unionid'] = $response->unionid;
                $this->Mini_user_model->update($add);
            }
            $program = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $appid));
            if (!$user) {
                $data = array(
                    'p_uid' => $openid,
                    'appid' => $appid,
                    'program_name' => $program->mini_name,
                    'create_date' => time(),
                    'channel' => $channel,
                    
                );
                $user_id = $this->Mini_user_model->add($data);
            } else {
                $user_id = $user->mini_user_id;
                $channel = $user->channel;
            }
            //登录日志
            $login_log_data=array(
                'mini_user_id'=>$user_id,
                'p_uid'=>$openid,
                'mini_id'=>$program->mini_id,
                'program_name'=>$program->mini_name,
                'appid'=>$appid,
                'create_date'=>time(),
                'channel'=>$channel,
                'ip'=>self::get_real_ip()
            );
            
            $login_id=$this->db->insert('mini_login_log',$login_log_data);
            //log_message('debug', 'mini game login_id ' . $login_id);
            // $user_id = $openid;
            // user info
            $data = array(
                'user_id' => $user_id,
                'openid' => $openid,
                'session_key' => $session_key,
            );
            if ($this->cache->redis->save($code, json_encode($data), 86400)) {
                log_message('debug', 'mini game login ' . $data);
                $this->Output_model->json_print(1,'ok', $data);
            }
        } else {
            $this->Output_model->json_print(1, 'login error');
        }
    }

    public function login()
    {
        $appid = $this->input->get('appid');
        $code = $this->input->get('code');
        $channel = $this->input->get('channel');
        $where = array('channel'=>$channel,'appid'=>$appid);
        $this->load->model('Mini_game_channel_model');
        $mini_channel = $this->Mini_game_channel_model->get_one_by_condition($where);
        if (!$mini_channel){
            $add = array(
                'appid'=>$appid,
                'channel'=>$channel,
            );
            $this->Mini_game_channel_model->add($add);
        }
        $form_user_id = $this->input->get('form_user_id');
        // log_message('debug', 'mini ua : ' . ($_SERVER['HTTP_USER_AGENT']));
        $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $appid));
        $key = $mini_game->mini_key;
        if ($appid && $code && $channel) {
            $this->load->model('User_model');
            $requery_url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$key&js_code=$code&grant_type=authorization_code";
            $content = $this->Curl_model->curl_get($requery_url);
            if (!$content) {
                $this->Output_model->json_print(2, 'no content from jscode2session');
                log_message('error', 'no content from jscode2session');
                return;
            }
            $response = json_decode($content);
            if ($response && isset($response->errcode)) {
                $this->Output_model->json_print(3, 'jscode2session error ' . $response->errmsg);
                log_message('error', 'no content from jscode2session');
                return;
            }
            $openid = $response->openid;
            $session_key = $response->session_key;
            $condition = array('p_uid' => $openid, 'appid' => $appid);
            $user = $this->Mini_user_model->get_one_by_condition($condition);
            if ($user && !$user->unionid && $response->unionid){
                $add['unionid'] = $response->unionid;
                $this->Mini_user_model->update($add);
            }
            $program = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $appid));
            if (!$user) {
                $data = array(
                    'p_uid' => $openid,
                    'appid' => $appid,
                    'program_name' => $program->mini_name,
                    'create_date' => time(),
                    'unionid'=>$response->unionid,
                    'channel' => $channel,

                );
                $user_id = $this->Mini_user_model->add($data);
            } else {
                $user_id = $user->mini_user_id;
                $channel = $user->channel;
            }
            //登录日志
            $login_log_data=array(
                'mini_user_id'=>$user_id,
                'p_uid'=>$openid,
                'mini_id'=>$program->mini_id,
                'program_name'=>$program->mini_name,
                'appid'=>$appid,
                'create_date'=>time(),
                'channel'=>$channel,
                'ip'=>self::get_real_ip()
            );
                
            $login_id=$this->db->insert('mini_login_log',$login_log_data);
            //log_message('debug', 'mini game login_id ' . $login_id); 
            // $user_id = $openid;
            // user info
            $data = array(
                'user_id' => $user_id,
                'openid' => $openid,
                'session_key' => $session_key,
            );
            if ($this->cache->redis->save($code, json_encode($data), 86400)) {
                $time = time();
                $nonce = md5($user_id . $time . $key);
                $sign = md5($user_id . $nonce . $time . $key);
                //$login_game_url = $program->game_url . "?user_id=$user_id&nonce=$nonce&time=$time&sign=$sign&appid=$program->mini_appid&open_id=$openid&channel=$channel";
                if (strpos($program->game_url, '?') !== false) {
                    $login_game_url = $program->game_url . "&user_id=$user_id&nonce=$nonce&time=$time&sign=$sign&appid=$program->mini_appid&open_id=$openid&channel=$channel";
                } else {
                    $login_game_url = $program->game_url . "?user_id=$user_id&nonce=$nonce&time=$time&sign=$sign&appid=$program->mini_appid&open_id=$openid&channel=$channel";
                };
                
                
                if ($form_user_id) {
                    $login_game_url = $login_game_url . '&from_user_id=' . $form_user_id;
                }
                log_message('debug', 'mini game login ' . $login_game_url);
                header("Location: $login_game_url");
            }
        } else {
            $this->Output_model->json_print(1, 'login error');
        }
    }
    public function get_user_info()
    {
        $code = $this->input->get('code');
        if (!$code) {
            $this->Output_model->json_print(1, 'no code');
        }
        $openid = json_decode($this->cache->redis->get($code));
        if ($openid) {
            $this->Output_model->json_print(0, 'ok', $openid);
        } else {
            $this->Output_model->json_print(1, 'no open_id');
        }
    }
    public function pay()
    {
        $open_id = $this->input->get('open_id');
        $appid = $this->input->get('appid');
        $order_id = $this->input->get('order_id');
        $product = $this->input->get('product');
        $product_id = $this->input->get('product_id');
        $money = $this->input->get('money');
        $type = $this->input->get('type');
        $user_id = $this->input->get('user_id');
        $cp_role_id = $this->input->get('cp_role_id');
        $channel = $this->input->get('channel');
        $server_id = $this->input->get('server_id');
        $host = $this->get_host_url();

        if (!$type) {
            $type = 'JSAPI';
        };
        if (!$open_id || !$appid || !$product || !$product_id || !$money || !$order_id || !$cp_role_id || !$user_id) {
            $this->Output_model->json_print(1, 'params error');
            exit;
        };

        $u_order_id = substr(md5(rand(1, 1000) . '_' . $open_id . '_' . time()), 8, 24);
        $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $appid));
        if (!$mini_game) {
            $this->Output_model->json_print(1, 'appid error');
            exit;
        }
        $data = array(
            'u_order_id' => $u_order_id,
            'open_id' => $open_id,
            'user_id' => $user_id,
            'cp_order_id' => $order_id,
            'mini_appid' => $appid,
            'money' => $money,
            'product' => $product,
            'status' => 0,
            'create_date' => time(),
            'cp_role_id' => $cp_role_id,
            'mini_name' => $mini_game->mini_name,
            'channel' => $channel,
            'server_id' => $server_id,
            'pay_type' => '1',//1为商户号收款
        );
        $this->Mini_game_order_model->add($data);
        $reserve = json_decode($mini_game->reserve,true);
        //用户当前等级
        $role_level = $this->input->get('current_role_level');
//         $role_level = '29';
        //限制等级支付
        if ($role_level<$reserve['limit_pay']) {
           $res = array(
               'is_pay'=> '1',
               'text'=>'暂时不支持充值',
           );
            $this->Output_model->json_print(0, 'ok', $res);
        }else{
            if($reserve['android_pay']=='1'){
                $notify_url = "$host/index.php/wx_minigame/notify";
                $result = $this->Wxpay_model->unifiedorder($open_id, $u_order_id, $product, $product_id, $appid, $money, $notify_url, $mini_game->mchid, $mini_game->app_secret, $mini_game->app_key, $type);
                log_message('debug', 'android_order' . json_encode($result));
                //         log_message('debug', 'android_order' . json_encode($u_order_id));
                if ($result) {
                    $result->pay_type = $this->pay_type;
                    log_message('debug', 'android_pay' . 'okkkkk');
                    $this->Output_model->json_print(0, 'ok', $result);
                } else {
                    log_message('debug', 'android_pay' . 'error');
                    $this->Output_model->json_print(1, 'result error');
                }
            }elseif($reserve['android_pay']=='2'){
                //判断是否属于新版客服支付
                $payment_type = $this->get_payment_type();
                if (in_array($appid, $payment_type) ) {
                    $result = array(
                        "android_pay" => $reserve['android_pay'],
                        "orderid"=>$u_order_id,//我方订单id
                        "appid"=>$appid,//游戏壳appid
                        "money"=>$money,//游戏币
                        "product"=>$product,//产品名词
                        "amount"=>sprintf("%.2f",$money/100),//支付金额
                        "kf_img"=>'http://api.baizegame.com/img/wechatGameImg/bzkf.jpeg',//小程序卡片图片地址
                    );
                }else{
                    if($appid=='wx0d85ccf6e166b152'){
                        $result = array(
                            "android_pay" => $reserve['android_pay'],
                            'kf_pay' => '1',//旧版客服支付标识
                            'service' => '1',
                        );
                    }else{
                        $result = array(
                            "android_pay" => $reserve['android_pay'],
                            'kf_pay' => '1',//旧版客服支付标识
                        );
                    }
                }
                
                $this->Output_model->json_print(0, 'ok', $result);
            }
        }
    }
    private function get_host_url()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'] . '/';
        return $protocol . $domainName;
    }

    /**
     * check user agent
     * 判断用户终端类型
     */
    public function check_user_agent()
    {
        //全部变成小写字母
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            $data = 'ios';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) {
            $data = 'android';
        } else {
            $data = 'other';
        }
        // log_message('debug', 'mini ua : ' . $data);
        $this->Output_model->json_print(0, 'ok', $data);
    }

    /**
     * 获取网页URL
     */
    private function get_web_url()
    {
        // http://localhost:8888/index.php/wx_minigame/get_web_url?params=value
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
    }

    public function notify()
    {
        $msg = array();
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $msg = (array) simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        log_message('debug', 'wxpay data info' . json_encode($msg));
        $WxPayConfig = new WxPayConfig();
        // echo $WxPayConfig->GetMerchantId();
        if ($msg['return_code'] == 'SUCCESS') {
            // if ($msg['mch_id'] == $WxPayConfig->GetMerchantId()) {
            $this->load->model('Curl_model');
            $total_amount = $msg['total_fee'];
            $out_trade_no = $msg['out_trade_no'];
            $order = $this->Mini_game_order_model->get_one_by_condition(array('u_order_id' => $out_trade_no));
            if (!$order) {
                log_message('debug', 'wx pay notify order error');
                $this->notify_err();
            }
            if ($total_amount != $order->money) {
                log_message('debug', 'wx pay notify money error ' . json_encode($msg));
                $this->notify_err();
            };
            log_message('debug', "wx pay notify $out_trade_no | $total_amount");
            $this->Mini_game_order_model->update(array('status' => 1,'platform_order_id'=>$msg['transaction_id']), array('u_order_id' => $out_trade_no));
            $this->cp_notify($order);
            $this->notify_ok();
            // } else {
            //     log_message('debug', 'wx pay mch_id error ' . json_encode($msg));
            //     $this->notify_err();
            // }
        } else {
            log_message('debug', 'wx pay return_code error ' . json_encode($msg));
            $this->notify_err();
        }
    }
    //小游戏支付回调
    public function gameNotify()
    {
        $msg = array();
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $msg = (array) simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        log_message('debug', 'wxpay gamedata info' . json_encode($msg));
        $WxPayConfig = new WxPayConfig();
        // echo $WxPayConfig->GetMerchantId();
        if ($msg['return_code'] == 'SUCCESS') {
            // if ($msg['mch_id'] == $WxPayConfig->GetMerchantId()) {
            $this->load->model('Curl_model');
            $total_amount = $msg['total_fee'];
            $out_trade_no = $msg['out_trade_no'];
            $this->load->model('Game_order_model');
            $order = $this->Game_order_model->get_one_by_condition(array('u_order_id' => $out_trade_no));
            if (!$order) {
                log_message('debug', 'wx pay notify order error');
                $this->notify_err();
            }
            if ($total_amount != $order->money) {
                log_message('debug', 'wx pay notify money error ' . json_encode($msg));
                $this->notify_err();
            };
            log_message('debug', "wx gamepay notify $out_trade_no | $total_amount");
            $this->Game_order_model->update(array('status' => 1,'pay_type' => '1','platform_order_id'=>$msg['transaction_id']), array('u_order_id' => $out_trade_no));
            $this->load->model('Common_model');
            $this->Common_model->gameNotify($out_trade_no);
            $this->notify_ok();
            // } else {
            //     log_message('debug', 'wx pay mch_id error ' . json_encode($msg));
            //     $this->notify_err();
            // }
        } else {
            log_message('debug', 'wx pay return_code error ' . json_encode($msg));
            $this->notify_err();
        }
    }

    public function jump_pay()
    {
        // 支付小程序发起支付 从数据库内通过订单号找到订单信息

        // get 参数
        $u_order_id = $this->input->get('order_id');
        $pay_appid = $this->input->get('appid');
        $code = $this->input->get('code');
        // 其他参数
        $type = $this->input->get('type');
        $host = $this->get_host_url();
        $notify_url = "$host/index.php/wx_minigame/notify";

        if (!$u_order_id || !$pay_appid || !$code) {
            $this->Output_model->json_print(1, 'params error');
            exit;
        }

        if (!$type) {
            $type = 'JSAPI';
        };

        /**
         * 获取发起支付的A小程序用户 订单信息
         */
        $mini_game_order = $this->Mini_game_order_model->get_one_by_condition(array('u_order_id' => $u_order_id));
        if (!$mini_game_order) {
            exit;
        }
        $channel = $mini_game_order->channel;
        $money = $mini_game_order->money;
        $product = $mini_game_order->product;
        $product_id = $mini_game_order->product;

        /**
         * 获取接收支付的B小程序 mchid app_secret app_key
         */
        $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $pay_appid));
        $pay_key = $mini_game->mini_key;
        $mchid = $mini_game->mchid;
        $app_secret = $mini_game->app_secret;
        $app_key = $mini_game->app_key;

        /**
         * 获取接收支付的B小程序用户 openid
         * 登录凭证校验
         * 通过 wx.login() 接口获得临时登录凭证 code 后传到开发者服务器调用此接口完成登录流程
         */
        $requery_url = "https://api.weixin.qq.com/sns/jscode2session?appid=$pay_appid&secret=$pay_key&js_code=$code&grant_type=authorization_code";
        $content = $this->Curl_model->curl_get($requery_url);
        if (!$content) {
            $this->Output_model->json_print(2, 'no content from jscode2session');
            log_message('error', 'jump_pay no content from jscode2session');
            return;
        }
        $response = json_decode($content);
        if ($response && isset($response->errcode)) {
            $this->Output_model->json_print(3, 'jscode2session error ' . $response->errmsg);
            log_message('error', 'jump_pay no content from jscode2session');
            return;
        }
        $openid = $response->openid;
        // $session_key = $response->session_key;

        /**
         * 登录上报 接收支付的B小程序用户 user_id
         */
        $condition = array('p_uid' => $openid, 'appid' => $pay_appid);
        $user = $this->Mini_user_model->get_one_by_condition($condition);

        if (!$user) {
            $data = array(
                'p_uid' => $openid,
                'appid' => $pay_appid,
                'program_name' => $mini_game->mini_name,
                'create_date' => time(),
                'channel' => $channel,
            );
            $user_id = $this->Mini_user_model->add($data);
        } else {
            $user_id = $user->mini_user_id;
            $channel = $user->channel;
        }

        /**
         * 实际发起支付
         */
        $result = $this->Wxpay_model->unifiedorder($openid, $u_order_id, $product, $product_id, $pay_appid, $money, $notify_url, $mchid, $app_secret, $app_key, $type);
        if ($result) {
            $result->pay_type = $this->pay_type;
            $result->money = $money;
            $this->Output_model->json_print(0, 'ok', $result);
        } else {
            $this->Output_model->json_print(1, 'result error');
        }

    }

    /**
     * 微信 JS-SDK 验证签名算法
     */
    public function miniprogram_init()
    {
        $url = $this->input->get('url');
        $app_id = $this->input->get('appid');
        // 发起支付A小程序 appid
        $guoke_arr = $this->get_guoke_arr();

        if (in_array($app_id, $guoke_arr)) {
//             $_app_id = 'wx17c8eab06395622a'; // 果壳公众号 appid
            $_app_id = 'wx956099b9a7394e81'; // 果壳公众号 appid
            $_app_key = '65291cdd689557e3c269faee06075f50'; // 果壳公众号 key
        } else {
            $this->Output_model->json_print(1, 'appid error');
            exit;
        };

        $access_token = $this->get_pay_miniprogram_token($_app_id,$_app_key);

        /**
         * 获取getticket
         */
        $jsapi_ticket = $this->wx_getticket($access_token);
        if (!$jsapi_ticket) {exit;}

        $nonce = md5(time());
        $wx_signature = $this->wx_getsignature($jsapi_ticket, $nonce, $url);
        $data['appid'] = $app_id;
        $data['timestamp'] = $wx_signature['timestamp']; // 时间戳
        $data['nonceStr'] = $wx_signature['noncestr']; // 随机字符串
        $data['signature'] = $wx_signature['signature']; // sign签名

        $this->Output_model->json_print(0, 'ok', $data);

    }

    public function jump_to_pay()
    {
        // 发起支付A小程序 appid
        $guoke_arr = $this->get_guoke_arr();
        // 保存订单号到数据库 并且返回小程序码

        // 保存订单信息到数据库
        $u_order_id = $this->save_mini_game_order();
        if (!$u_order_id) {exit;}

        // 根据APPID查找APPKEY
        $app_id = $this->input->get('appid');
        if (in_array($app_id, $guoke_arr)) {
//             $_app_id = 'wx17c8eab06395622a'; // 接收支付B小程序 appid
            $_app_id = 'wx956099b9a7394e81'; // 接收支付B小程序 appid
        } else {
            $this->Output_model->json_print(1, 'appid error');
            exit;
        };
        $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $_app_id));
        $_app_key = $mini_game->mini_key;
        
        //获取小程序壳的备用字段
        $mini_programs = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $app_id));
        $reserve = json_decode($mini_programs->reserve,true);
        
        /**
         * 1.获取小程序access_token
         */
        $access_token = $this->get_pay_miniprogram_token($_app_id, $_app_key);
        if (!$access_token) {exit;}
        // $this->cache->save($app_id . '_token', $access_token, 86400);

        /**
         * 2.获取生成的小程序码，永久有效，数量暂无限制
         */
        //用户当前等级
        $role_level = $this->input->get('current_role_level');
        log_message('debug', 'wxlevel: ' . $role_level. '限制等级:' . $reserve['limit_pay']);
        //         $role_level = '29';
        if ($role_level<$reserve['limit_pay']) {
            $data = array(
                'is_pay'=> '1',
                'text'=>'暂时不支持充值',
            );
        }else{
            if ($reserve['ios_pay']=='2'){
                //判断是否属于新版客服支付
                $payment_type = $this->get_payment_type();
                if (in_array($app_id, $payment_type) ) {
                    $data = array(
                        "ios_pay" => $reserve['ios_pay'],
                        "orderid"=>$u_order_id,//我方订单id
                        "appid"=>$app_id,//游戏壳appid
                        "money"=>$this->input->get('money'),//游戏币
                        "product"=>$this->input->get('product'),//产品名词
                        "amount"=>sprintf("%.2f",$this->input->get('money')/100),//支付金额
                        "kf_img"=>'http://api.baizegame.com/img/wechatGameImg/bzkf.jpeg',//小程序卡片图片地址
                    );
                }else{
                    if($app_id=='wx0d85ccf6e166b152'){
                        //旧版客服支付
                        $data = array(
                            "ios_pay" => $reserve['ios_pay'],
                            'kf_pay' => '1',//旧版客服支付标识
                            'service'=>'1',//被封支付，调用壳的联系客服页面。
                        );
                    }else{
                        //旧版客服支付
                        $data = array(
                            "ios_pay" => $reserve['ios_pay'],
                            'kf_pay' => '1',//旧版客服支付标识
                        );
                    }
                }
            }else{
                //支付壳二维码支付
                $scene = $u_order_id;
                $page = 'pages/jumpPay/jumpPay';
                $qrcode = $this->get_pay_miniprogram_qrcode($access_token, $scene, $page);
                $data = array(
                    "qrcode" => $qrcode,
                );
            }
        }      
        $this->Output_model->json_print(0, 'ok', $data);

        
    }
    /**
     * 微信小程序客服消息通知地址[小程序]
     * */
    public function kf_getWechattoken(){     //校验服务器地址URL
        if (isset($_GET['echostr'])) {
            $this->kfValid();
        }else{
            $this->kfresponseMsg();
        }
    }
    
    /**
     * 微信小程序客服消息通知地址[小程序]
     * */
    public function getWechattoken(){     //校验服务器地址URL
        if (isset($_GET['echostr'])) {
            $this->valid();
        }else{
            $this->responseMsg();
        }
    }
    
    /**
     * 微信小程序客服消息通知地址[小游戏]
     * */
    public function getGameWechattoken(){     //校验服务器地址URL
        if (isset($_GET['echostr'])) {
            $this->gameValid();
        }else{
            $this->gameresponseMsg();
        }
    }
    
    /**
     * 微信公众号客服消息通知地址[公众号]
     * */
    public function getMpWechattoken(){     //校验服务器地址URL
        if (isset($_GET['echostr'])) {
            $this->mpValid();
        }else{
            $this->mpresponseMsg();
        }
    }

    /**
     * 微信公众号客服消息通知地址[Happy玩游戏中心公众号]
     * */
    public function get_QY_MpWechattoken(){     //校验服务器地址URL
        if (isset($_GET['echostr'])) {
            $this->NewmpValid();
        }else{
            $this->NewmpresponseMsg();
        }
    }
    
    public function mpValid()
    {
        $echoStr = $_GET["echostr"];
        if($this->mpcheckSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }else{
            echo $echoStr.'+++'.'bztest';
            exit;
        }
    }

    public function NewmpValid()
    {
        $echoStr = $_GET["echostr"];
        if($this->NewmpcheckSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }else{
            echo $echoStr.'+++'.'bztest';
            exit;
        }
    }
    
    public function gameValid()
    {
        $echoStr = $_GET["echostr"];
        if($this->gamecheckSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }else{
            echo $echoStr.'+++'.'bztest';
            exit;
        }
    }

    public function kfValid()
    {
        $echoStr = $_GET["echostr"];
        if($this->kfcheckSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }else{
            echo $echoStr.'+++'.'bztest';
            exit;
        }
    }
    
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }else{
            echo $echoStr.'+++'.'bztest';
            exit;
        }
    }

    /*
     *校验微信消息通知Token
     **/
    private function NewmpcheckSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = 'bztest';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    /*
     *校验微信消息通知Token
     **/
    private function mpcheckSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        
        $token = 'bztest';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     *校验微信消息通知Token
     **/
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        
        $token = 'bztest';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    /*
    *校验微信消息通知Token
    **/
    private function kfcheckSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = 'bztest';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    
    /*
     *校验微信消息通知Token
     **/
    private function gamecheckSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        
        $token = 'bztest';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 微信客服消息通知处理
     * */
    public function mpresponseMsg()
    {
//        echo 'success';die;
        $string = file_get_contents('php://input');//接收post请求数据
        log_message('debug', 'wxmp_log' . json_encode($this->xml_to_array($string)));
        $wxmpArr = $this->xml_to_array($string);//xml转数组
        //公众号参数
        $appid = 'wxf39e45dee925e9e7';//白泽游戏中心
        $appkey= '48687ce1a310b90e5f754f23a6507397';
        $access_token = $this->get_pay_miniprogram_token($appid,$appkey);
        log_message('debug', 'wxmp_accesstoken_log:'  . $access_token);
        if($wxmpArr['MsgType']=='event'){
            if ($wxmpArr['Event']=='subscribe'){
                $resMsg = array(
                    'access_token'=>$access_token,
                    'openid'=>$wxmpArr['FromUserName'],
                    'wxmp_id'=>$wxmpArr['ToUserName'],
                    'content'=>'需要领礼包，请直接在公众号内回复：礼包，按照引导进行回复即可，如果需要联系客服，请直接在公众号内回复：客服。',
                );
                echo $this->wechatNews($resMsg);die;
            }
        }
        if($wxmpArr['Content']=='礼包'){
            $resMsg = array(
                'access_token'=>$access_token,
                'openid'=>$wxmpArr['FromUserName'],
                'wxmp_id'=>$wxmpArr['ToUserName'],
                'content'=>'请问您想领取哪款游戏的礼包？
回复格式：【游戏名称-礼包名称】
例：英雄训练师-关注礼包',
            );
            echo $this->wechatNews($resMsg);die;
        }else if($wxmpArr['Content']=='客服'){
            $resMsg = array(
                'access_token'=>$access_token,
                'openid'=>$wxmpArr['FromUserName'],
                'wxmp_id'=>$wxmpArr['ToUserName'],
                'content'=>'请复制下面的链接到浏览器内打开，即可咨询客服，客服咨询链接：https://www14.71baomu.net/code/client/11efebbd505b317bb625d60fe7fd75eb7/1',
            );
            echo $this->wechatNews($resMsg);die;
        }
        if (strpos($wxmpArr['Content'],'-') !== false){
            $gameName = explode('-',$wxmpArr['Content']);
            if($gameName[0]=='英雄训练师'){
                $gameId = '1';
            }else if($gameName[0]=='口袋精灵王'){
                $gameId = '2';
            }else if($gameName[0]=='小精灵宝可萌新版'){
                $gameId = '3';
            }elseif($gameName[0]=='幻剑仙道'){
                $gameId = '4';
            }elseif($gameName[0]=='小小攻城战'){
                $gameId = '5';
            }elseif($gameName[0]=='梦幻超进化'){
                $gameId = '6';
            }elseif(preg_replace('# #','',strtoupper($gameName[0]))=='我是大将军ONLINE'){
                $gameId = '12';
            }else{
                echo 'success';die;
            }
            if ($gameId){
                if ($gameName[1]=='关注礼包' && $gameId!='12'){
                    $cdkType = $gameId.'001';
                }else if($gameName[1]=='关注礼包' && $gameId=='12'){
                    $cdkType = $gameId.'01';
                }else if($gameName[1]=='认证礼包' && $gameId=='12'){
                    $cdkType = $gameId.'02';
                }else if($gameName[1]=='补偿礼包'){
                    $cdkType = $gameId.'002';
                }else if(strtoupper($gameName[1])=='VIP礼包'){
                    $cdkType = $gameId.'003';
                }else if($gameName[1]=='圣诞礼包'){
                    $cdkType = $gameId.'007';
                }
            }else{
                echo 'success';die;
            }
            $res = $this->wxmpCdk($wxmpArr['FromUserName'],$cdkType);//获取cdk
            if ($res=='1'){
                $resMsg = array(
                    'access_token'=>$access_token,
                    'openid'=>$wxmpArr['FromUserName'],
                    'wxmp_id'=>$wxmpArr['ToUserName'],
                    'content'=>'您好，该礼包目前暂无库存，建议先领取其他类型的礼包。',
                );
                echo $this->wechatNews($resMsg);die;
            }
        }else{
            echo 'success';die;
        }
        if ($res){
            $resMsg = array(
                'access_token'=>$access_token,
                'openid'=>$wxmpArr['FromUserName'],
                'wxmp_id'=>$wxmpArr['ToUserName'],
                'content'=>'您好，这是您的礼包码：'.$res,
            );
            echo $this->wechatNews($resMsg);die;
        }else{
            $resMsg = array(
                'access_token'=>$access_token,
                'openid'=>$wxmpArr['FromUserName'],
                'wxmp_id'=>$wxmpArr['ToUserName'],
                'content'=>'非常抱歉，您已获取过该游戏的礼包码了。',
            );
            echo $this->wechatNews($resMsg);die;
        }
        
    }

    /**
     * 微信客服消息通知处理 [Happy玩游戏中心]
     * */
    public function NewmpresponseMsg()
    {
//        echo 'success';die;
        $string = file_get_contents('php://input');//接收post请求数据
        log_message('debug', 'new_wxmp_log' . json_encode($this->xml_to_array($string)));
        $wxmpArr = $this->xml_to_array($string);//xml转数组
        if ($wxmpArr['ToUserName']=='gh_bb161eef8a41'){
            //公众号参数
            $appid = 'wx553178c50aa8f246';//Happy玩游戏中心
            $appkey= '606e61c6c58bc8e44cda7550abd81526';
            //关注回复
            $content = '欢迎关注英雄训练师公众号，点击下方菜单栏，领取福利礼包，也可以在下方菜单栏直接进入游戏游玩。';
            //自动回复
            $autoContent = '点击下方菜单栏领取礼包或游玩游戏，如有游戏问题咨询游戏客服公众号：白泽游戏中心。';
        }elseif($wxmpArr['ToUserName']=='gh_938fc82f7260'){
            //公众号参数
            $appid = 'wx112147f2ed8c7a55';//紫狮游戏
            $appkey= '6defebfbf14bde2b40697389470c3e49';
            //关注回复
            $content = '欢迎关注口袋精灵王公众号，点击下方菜单栏，领取福利礼包，也可以在下方菜单栏直接进入游戏游玩。';
            //自动回复
            $autoContent = '点击下方菜单栏领取礼包或游玩游戏，如有游戏问题咨询游戏客服公众号：白泽游戏中心。';
        }elseif($wxmpArr['ToUserName']=='gh_a494dd65a2a5'){
            //公众号参数
            $appid = 'wx8ad1820021c33a69';//Go玩游戏中心
            $appkey= '1912cf74a5548fd1f54f33354e305e8c';
            //关注回复
            $content = '欢迎关注小精灵宝可萌新版公众号，点击下方菜单栏，领取福利礼包，也可以在下方菜单栏直接进入游戏游玩。';
            //自动回复
            $autoContent = '点击下方菜单栏领取礼包或游玩游戏，如有游戏问题咨询游戏客服公众号：白泽游戏中心。';
        }elseif($wxmpArr['ToUserName']=='gh_dc5a836a3eac'){
            //公众号参数
            $appid = 'wx8ae293b9396a4b5d';//嗨玩游戏 - 炎龙服务号
            $appkey= 'a5f1138ad8939022b66dcd8b317b5cb3';
            //关注回复
//            $content = '欢迎关注梦幻超进化公众号，点击下方菜单栏，领取福利礼包，也可以在下方菜单栏直接进入游戏游玩。';
            $content = 'Oe13WL8CeBDTHdtfEuG0ow9fDYITgb2bTyVVHTrwQ3M';
            //自动回复
            $autoContent = '点击下方菜单栏领取礼包或游玩游戏，如有游戏问题咨询游戏客服公众号：白泽游戏中心。';
        }
        $access_token = $this->get_pay_miniprogram_token($appid,$appkey);
        log_message('debug', $appid.'  new_wxmp_accesstoken_log:'  . $access_token);

        if($wxmpArr['MsgType']=='event'){
            if ($wxmpArr['Event']=='subscribe'){
                $resMsg = array(
                    'access_token'=>$access_token,
                    'openid'=>$wxmpArr['FromUserName'],
                    'wxmp_id'=>$wxmpArr['ToUserName'],
                    'content'=>$content,
                );
                if($wxmpArr['ToUserName']=='gh_dc5a836a3eac'){
                    echo $this->wechatNewsImg($resMsg);die;
                }else{
                    echo $this->wechatNews($resMsg);die;
                }

            }
        }

        if($wxmpArr['Content']=='客服'){
            $resMsg = array(
                'access_token'=>$access_token,
                'openid'=>$wxmpArr['FromUserName'],
                'wxmp_id'=>$wxmpArr['ToUserName'],
                'content'=>'请复制下面的链接到浏览器内打开，即可咨询客服，客服咨询链接：https://www14.71baomu.net/code/client/11efebbd505b317bb625d60fe7fd75eb7/1',
            );
            echo $this->wechatNews($resMsg);die;
        }

        if($wxmpArr['EventKey']){
            if($wxmpArr['EventKey']=='7003' || $wxmpArr['EventKey']=='8002' || $wxmpArr['EventKey']=='9003'){
                $goodName = '新春礼包';
            }elseif($wxmpArr['EventKey']=='7002' || $wxmpArr['EventKey']=='8001'){
                $goodName = '1月礼包';
            }elseif($wxmpArr['EventKey']=='9002'){
                $goodName = '2月礼包';
            }elseif($wxmpArr['EventKey']=='7001' || $wxmpArr['EventKey']=='1008' || $wxmpArr['EventKey']=='9001' || $wxmpArr['EventKey']=='1101'){
                $goodName = '关注礼包';
            }else if($wxmpArr['EventKey']=='1102'){
                $goodName = '新手礼包';
            }else if($wxmpArr['EventKey']=='7004' || $wxmpArr['EventKey']=='8003' || $wxmpArr['EventKey']=='9004' || $wxmpArr['EventKey']=='1103'){
                $goodName = '3月礼包';
            }
            $res = $this->wxmpCdk($wxmpArr['FromUserName'],$wxmpArr['EventKey']);//获取cdk
            if ($res=='1'){
                $resMsg = array(
                    'access_token'=>$access_token,
                    'openid'=>$wxmpArr['FromUserName'],
                    'wxmp_id'=>$wxmpArr['ToUserName'],
                    'content'=>'您好，该礼包目前暂无库存，建议先领取其他类型的礼包。',
                );
                echo $this->wechatNews($resMsg);die;
            }
        }else{ //任意回复触发自动回复内容 ↓↓↓
            $resMsg = array(
                'access_token'=>$access_token,
                'openid'=>$wxmpArr['FromUserName'],
                'wxmp_id'=>$wxmpArr['ToUserName'],
                'content'=>$autoContent,
            );
            echo $this->wechatNews($resMsg);die;
        }
        if ($res){
            $resMsg = array(
                'access_token'=>$access_token,
                'openid'=>$wxmpArr['FromUserName'],
                'wxmp_id'=>$wxmpArr['ToUserName'],
                'content'=>$res,
            );
            $api = $this->wechatNews($resMsg);
            echo $api;
            if ($api){
                $arr = array(
                    'openid'=>$wxmpArr['FromUserName'],
                    'appid'=>$appid,
                    'appkey'=>$appkey,
                    'token'=>$access_token,
                    'content'=>'您好，这是您的'.$goodName.'，礼包码:'.$res.'。在游戏内领取即可。关注公众号不迷路，也可以从公众号直接进入游戏游玩噢~',
                );
                $this->kf_push_sdk($arr);die;
            }
        }else{
            $resMsg = array(
                'access_token'=>$access_token,
                'openid'=>$wxmpArr['FromUserName'],
                'wxmp_id'=>$wxmpArr['ToUserName'],
                'content'=>'非常抱歉，您已获取过该游戏的礼包码了。',
            );
            echo $this->wechatNews($resMsg);die;
        }

    }

    public function kf_push_sdk($arr){
        $requery_url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$arr['token'];
        $json = array(
            'touser'=>$arr['openid'],
            'msgtype'=>'text',
            'text'=>array(
                'content'=>$arr['content'],
            ),
        );
        $this->load->model('Curl_model');
        $res = $this->Curl_model->curl_post($requery_url,json_encode($json,JSON_UNESCAPED_UNICODE));
        $json_arr = json_decode($res,true);
        if ($json_arr['errcode']=='40001'){
            $token = $this->get_accessToken($arr['appid'], $arr['appkey']);
            $arr['token'] = $token;
            $this->kf_push_sdk($arr);
        }else{
            echo 'success';
        }
    }

    //获取礼包码cdk
    public function wxmpCdk($openid,$type){
        $this->load->model('Wxmp_cdk_model');
        $cdkCondition = array('status'=>'0','type'=>$type);
        $getCdk = $this->Wxmp_cdk_model->get_one_by_condition($cdkCondition);
        if (!$getCdk){
            return '1';
        }
        $this->load->model('Wxmp_cdk_user_model');
        $queryCdk = $this->Wxmp_cdk_user_model->get_one_by_condition(array('sign'=>md5($openid.$type)));
        if(!$queryCdk){
            $data = array(
                'openid'=>$openid,
                'cdk_type'=>$type,
                'create_date'=>time(),
                'consume_cdk'=>$getCdk->cdk,
                'sign'=>md5($openid.$type),
            );
            $user_cdk = $this->Wxmp_cdk_user_model->add($data);
            if($user_cdk){
                $this->Wxmp_cdk_model->update(array('status'=>'1'),array('cdk'=>$getCdk->cdk,'status'=>'0','type'=>$type));
                return $getCdk->cdk;
            }else{
                log_message('error', 'user_cdk:'.$openid .'已获取过该游戏礼包码');
                return false;
            }
        }else{
            log_message('error', 'user_cdk:'.$openid .'已获取过该游戏礼包码');
            return false;
        }
    }
    
    //发送微信消息给用户
    public function wechatNews($resMsg){
        $requery_url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$resMsg['access_token'];
        $arrayStr = array(
            'ToUserName' => $resMsg['openid'],
            'FromUserName' => $resMsg['wxmp_id'],
            'CreateTime' => time(),
            'MsgType' => 'text',
            'Content' => $resMsg['content']
        );
        $xmlStr = $this->array_to_xml($arrayStr);
        log_message('debug', 'wxmp_news_log:' . json_encode($arrayStr));
        return $xmlStr;

    }

    //发送微信图片消息给用户
    public function wechatNewsImg($resMsg){
        $arrayStr = array(
            'ToUserName' => $resMsg['openid'],
            'FromUserName' => $resMsg['wxmp_id'],
            'CreateTime' => time(),
            'MsgType' => 'image',
            'Image' => array(
                'MediaId' => $resMsg['content'],
            )
        );
        $xmlStr = $this->array_to_xml($arrayStr);
        log_message('debug', 'wxmp_newsimg_log:' . json_encode($arrayStr));
        return $xmlStr;
    }

    /**
     * 微信客服消息通知处理
     * */
    public function kfresponseMsg()
    {
        $string = file_get_contents('php://input');//接收post请求数据
        $resMsg = json_decode($string,true);
        $resMsg = json_encode($resMsg,true);

        log_message('debug', 'game_kf_msg' . $resMsg);
        $resMsg = json_decode($resMsg,true);

        if ($resMsg['MsgType']!='event'){
            $arrayStr = array(
                'MsgType'=>'transfer_customer_service',
                'FromUserName'=>$resMsg['ToUserName'],
                'ToUserName'=>$resMsg['FromUserName'],
                'CreateTime'=>$resMsg['CreateTime'],
            );
//            $xmlStr = json_encode($arrayStr);
            $xmlStr = $this->array_to_xml($arrayStr);
            log_message('debug', 'game_kf_msg2' . $xmlStr);

            return $xmlStr;
        }else{
            return 'success';
        }

    }

    /**
     * 微信客服消息通知处理
     * */
    public function responseMsg()
    {
        $string = file_get_contents('php://input');//接收post请求数据
        log_message('debug', 'wx_kf' . $string);
        $newString = json_decode($string,true);
        $this->load->model('Mini_game_order_model');
        $condition = array('open_id' => $newString['FromUserName']);
//         $condition = array('open_id' => 'oYYXI5bqz8zlqc6z8uDNlnUO0YQI');
        $game_order = $this->Mini_game_order_model->get_by_condition($condition,'1','0','mini_game_order_id','desc','','');
        $game_order = $game_order[0];
        log_message('debug', 'wx_order' . json_encode($game_order));
        if($game_order->status=='2'){
            exit;
        }
//         $this->load->model('Mini_programs_model');
//         $newCondition = array('mini_appid' => $game_order->mini_appid);
//         $programs= $this->Mini_programs_model->get_one_by_condition($newCondition);
        $data = $this->jump_kf_pay($game_order);
    }
    
    /**
     * 微信客服消息通知处理[小游戏]
     * */
    public function gameresponseMsg(){
        $string = file_get_contents('php://input');//接收post请求数据
        log_message('debug', 'wx_game_kf' . $string);
        $newString = json_decode($string,true);
        $this->load->model('User_model');
        $condition = array('p_uid' => $newString['FromUserName']);
        //         $condition = array('open_id' => 'oYYXI5bqz8zlqc6z8uDNlnUO0YQI');
        $userList = $this->User_model->get_by_condition($condition);
        $userList = $userList[0];
        $this->load->model('Game_order_model');
        $newCondition = array('user_id'=>$userList->user_id);
        $game_order = $this->Game_order_model->get_by_condition($newCondition,'1','0','order_id','desc','','');
        $game_order = $game_order[0];
        log_message('debug', 'wx_game_order' . json_encode($game_order));
        if($game_order->status=='2'){
            exit;
        }
        $data = $this->jump_gamekf_pay($game_order,$userList);
    }
    //小程序客服支付[不绑定商户号]
    public function wechatOpenid(){

        $pay_appid = $this->pay_appid;
        $pay_key = $this->pay_key;
        $mchid = $this->mchid;
        $apikey = $this->apikey;


        $code = $this->input->get('code');
        $order_openid = $this->input->get('openid');
        $requery_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$pay_appid&secret=$pay_key&code=$code&grant_type=authorization_code";
        $content = $this->Curl_model->curl_get($requery_url);
        
        if (!$content) {
            $this->Output_model->json_print(2, 'no content from jscode2session');
            log_message('error', 'jump_pay no content from jscode2session');
            return;
        }
        $response = json_decode($content);
        if ($response && isset($response->errcode)) {
            $this->Output_model->json_print(3, 'jscode2session error ' . $response->errmsg);
            log_message('error', 'jump_pay no content from jscode2session');
            return;
        }
        //当前玩家对应服务号的openid
        $openid = $response->openid;
        
        //查询订单数据
        $this->load->model('Mini_game_order_model');
        $Condition = array('u_order_id'=>$order_openid);
        $game_order = $this->Mini_game_order_model->get_by_condition($Condition,'1','0','mini_game_order_id','desc','','');
        $newCondition = array('open_id'=>$game_order[0]->open_id);
        $newGame_order = $this->Mini_game_order_model->get_by_condition($newCondition,'1','0','mini_game_order_id','desc','','');
        if($newGame_order[0]->u_order_id!=$game_order[0]->u_order_id){
            $order_repeat = '1';
        }
        //获取订单状态
        $is_status = $game_order[0]->status;
        if($is_status=='2' || $is_status=='1'){
            //用户支付完成后，再次点击链接，则提示该订单已支付成功。
            $url_parmas = http_build_query( array(
                'error'=>'2',
            ));
        }elseif($order_repeat){
            //最后一张单为已支付状态，用户点击历史未失效订单支付时，则提示订单无效。
            $url_parmas = http_build_query( array(
                'error'=>'1',
            ));
        }elseif($game_order[0]){
            //回调地址
            $host = $this->get_host_url();
            $gameNotify_url = "$host/index.php/wx_minigame/notify";
            $game_order = $game_order[0];
            //531
//             $result = $this->Wxpay_model->unifiedorder($openid,$game_order->u_order_id,$game_order->product,$game_order->cp_role_id,$pay_appid,$game_order->money,$gameNotify_url,'1535960531','cd502521f75fe8c359bcd3f3d1deec0f','Bre0ly3bxa6jz7d4l9rwfuyui6g4ft7Z','JSAPI');
            //701
            $result = $this->Wxpay_model->unifiedorder($openid,$game_order->u_order_id,$game_order->product,$game_order->cp_role_id,$pay_appid,$game_order->money,$gameNotify_url,$mchid,$pay_key,$apikey,'JSAPI');
//             log_message('error', 'gamePay_log:openid='.$openid.'&order_id='.$game_order->u_order_id.'&product='.$game_order->product.'&role_id='.$game_order->cp_role_id.'&appid='.$pay_appid.'&moeny='.$game_order->money.'&url='.$gameNotify_url.'&mchid=1549572751&appsecret=53b5edd4e9c38f6fdc95ab936ccbbf5f&apikey=84982d40kjwLMjPZICcsmAXcSXvWerQf&pay_type=JSPAI');

            $url_parmas = http_build_query( array(
                'appId'=>$result->appid,
                'timeStamp'=>$result->time,
                'nonceStr'=>$result->nonce_str,
                'package'=>$result->prepay_id,
                'paySign'=>$result->paySign,
            ));
        }
        log_message('error', 'gamePay_log:'.json_encode($url_parmas));
        header('Location:http://api.baizegame.com/wechat_pay_test.html?'.$url_parmas);
    }
    //小游戏客服支付[不绑定商户号]
    public function wechatGameOpenid(){
        $order_openid = $this->input->get('openid'); //订单号
        //查询订单数据
        $this->load->model('Game_order_model');
        $Condition = array('u_order_id'=>$order_openid);
        $game_order = $this->Game_order_model->get_by_condition($Condition,'1','0','order_id','desc','','');
        $newCondition = array('user_id'=>$game_order[0]->user_id);
        $newGame_order = $this->Game_order_model->get_by_condition($newCondition,'1','0','order_id','desc','','');
        if($newGame_order[0]->u_order_id!=$game_order[0]->u_order_id){
            $order_repeat = '1';
        }
        
        if($game_order[0]->game_id=='52' || $game_order[0]->game_id=='59' || $game_order[0]->game_id=='58'){
            $pay_appid = 'wxb1868f696ca3e266';//服务号appid
            $pay_key = 'd8690fa6e5434ecee9d304b31d510de4';//服务号秘钥
            $mchid = '1501412051';
            $apikey = 'c7d0535c87daf66175d41a61a9e38a43';
        }else{
            $pay_appid = $this->pay_appid;
            $pay_key = $this->pay_key;
            $mchid = $this->mchid;
            $apikey = $this->apikey;
            //2020.准备切换
//             $pay_appid = 'wx9fa1399a3b13f5bc';//服务号appid
//             $pay_key = '6bf08a25bf10a060b51bd720b0b4c2e8';//服务号秘钥
//             $mchid = '1572077301';
//             $apikey = '314504ac90b4876581b43b278099956e';//商户号秘钥

//            $pay_appid = 'wx375234cb72d3b9bd';//服务号appid
//            $pay_key = '53b5edd4e9c38f6fdc95ab936ccbbf5f';//服务号秘钥
//            $mchid = '1560006701';
//            $apikey = 'mdy0y4htai4kbxw1uke526nh17g3ybbt';//商户号秘钥
        }
        $code = $this->input->get('code');
        
        $requery_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$pay_appid&secret=$pay_key&code=$code&grant_type=authorization_code";
        $content = $this->Curl_model->curl_get($requery_url);
        if ($this->input->ip_address()=='113.67.156.147'){
            log_message('error', 'error_pay_test:'.$content. '   code:'.$code. '  appid:'.$pay_appid .'   pay_key:'.$pay_key);
        }
        if (!$content) {
            $this->Output_model->json_print(2, 'no content from jscode2session');
            log_message('error', 'jump_pay no content from jscode2session');
            return;
        }
        $response = json_decode($content);
        if ($response && isset($response->errcode)) {
            $this->Output_model->json_print(3, 'jscode2session error ' . $response->errmsg);
            log_message('error', 'jump_pay no content from jscode2session');
            return;
        }
        //当前玩家对应服务号的openid
        $openid = $response->openid;
        
        //回调地址
        $host = $this->get_host_url();
        $is_status = $game_order[0]->status;
        if($is_status=='2' || $is_status == '1'){
            //用户支付完成后，再次点击链接，则提示该订单已支付成功。
            $url_parmas = http_build_query( array(
                'error'=>'2',
            ));
        }elseif($order_repeat){
            //最后一张单为已支付状态，用户点击历史未失效订单支付时，则提示订单无效。
            $url_parmas = http_build_query( array(
                'error'=>'1',
            ));
        }elseif($game_order[0]){
            //小游戏订单参数
            $gameNotify_url = "$host/index.php/wx_minigame/gameNotify";
            //             $this->load->model('Game_order_model');
            //             $newCondition = array('user_id'=>$userList->user_id);
            //             $game_order = $this->Game_order_model->get_by_condition($newCondition,'1','0','order_id','desc','','');
            $game_order = $game_order[0];
            
            $result = $this->Wxpay_model->unifiedorder($openid,$game_order->u_order_id,$game_order->goodsName,$game_order->cproleid,$pay_appid,$game_order->money,$gameNotify_url,$mchid,$pay_key,$apikey,'JSAPI');
            $url_parmas = http_build_query( array(
                'appId'=>$result->appid,
                'timeStamp'=>$result->time,
                'nonceStr'=>$result->nonce_str,
                'package'=>$result->prepay_id,
                'paySign'=>$result->paySign,
            ));
        }
        
//         print_r($url_parmas);die;
        if($game_order->game_id=='52' || $game_order->game_id=='59' || $game_order->game_id=='58'){
            header('Location:https://api.baizegame.com/wechat_pay_test.html?'.$url_parmas);
        }else{
            header('Location:http://api.baizegame.com/wechat_pay_test.html?'.$url_parmas);
        }
//         header('Location:http://api.baizegame.com/wechat_pay.html?'.$url_parmas);
    }
    
    public function jump_gamekf_pay($post,$user){
        //获取游戏列表
        $gameOrder = $this->Game_model->get_one_by_condition(array('game_id' => $post->game_id));
        $gameJson = json_decode($gameOrder->platform_key,true);
        $appid = $gameJson['appId'];
        $appkey = $gameJson['appSecret'];
        $host = $this->get_host_url();
        //支付回调地址
        $gameNotify_url = "$host/index.php/wx_minigame/gameNotify";
        
//         $this->load->model('Game_order_model');
//         $order = $this->Game_order_model->get_one_by_condition(array('u_order_id' => '483_381315_1568169148_712'));
        
//         log_message('debug', 'wx_order' . json_encode($order));
        /**
         * 1.获取小游戏access_token
         */
        $access_token = $this->get_pay_miniprogram_token($appid, $appkey);
        log_message('debug', 'wxgame_token:' . json_encode($access_token) );
        if (!$access_token) {exit;}
//         $game_appid = $this->new_payType();
//         if(in_array($appid, $game_appid)){
            $redirect_uri = urlencode('http://api.baizegame.com/gamecode.php');
            if($post->game_id=='52' || $post->game_id=='59' || $post->game_id=='58'){
                $pay_appid = 'wxb1868f696ca3e266'; 
            }else{
                 $pay_appid = $this->pay_appid;
            }
            $response_type = 'code';
            $scope = 'snsapi_base';
            $state = $post->u_order_id;
            $data = array(
                'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$pay_appid.'&response_type='.$response_type.'&scope='.$scope.'&state='.$state.'&redirect_uri='.$redirect_uri.'#wechat_redirect',
                'money' => $post->money/100,
            );

//         }else{
//             $result = $this->Wxpay_model->unifiedorder($user->p_uid,$post->u_order_id,$post->goodsName,$post->cproleid,$appid,$post->money,$gameNotify_url,'1535960531','cd502521f75fe8c359bcd3f3d1deec0f','Bre0ly3bxa6jz7d4l9rwfuyui6g4ft7Z','JSAPI');
//             $url_parmas = http_build_query( array(
//                 'appId'=>$result->appid,
//                 'timeStamp'=>$result->time,
//                 'nonceStr'=>$result->nonce_str,
//                 'package'=>$result->prepay_id,
//                 'paySign'=>$result->paySign,
//             ));
//             $data = array(
//                 'url' => 'http://api.baizegame.com/wechat_pay.html?'.$url_parmas,
//                 'money' => $post->money/100,
//             );
//             log_message('debug', 'wx_game_pay' . $url_parmas);
//             log_message('debug', 'wx_game_pay1' . json_encode($result));
//         }
        
        //         $result = $this->Wxpay_model->unifiedorder('onJdp5BfuWVJxoOY2hcvpY3zQQAI',time().rand(1000,9999),'元宝',time().rand(100,999),'wx956099b9a7394e81','1',$notify_url,'1535960531','cd502521f75fe8c359bcd3f3d1deec0f','Bre0ly3bxa6jz7d4l9rwfuyui6g4ft7Z','JSAPI');
        
        
        //         $url = $data = '<a href="" data-miniprogram-appid="wx956099b9a7394e81" data-miniprogram-path="pages/jumpPay/jumpPay?scene='.$post->u_order_id.'">点击跳转支付</a>';
        $this->wx_customerService($access_token,$data,$user->p_uid,$appid,$appkey);
        
    }
    
    public function jump_kf_pay($post)
    {
        $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $post->mini_appid));
//         $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => 'wx956099b9a7394e81'));
        $_app_key = $mini_game->mini_key;
        $host = $this->get_host_url();
        $notify_url = "$host/index.php/wx_minigame/notify";
        
        /**
         * 1.获取小程序access_token
         */
        $access_token = $this->get_pay_miniprogram_token($post->mini_appid, $_app_key);
        if (!$access_token) {exit;}
        // $this->cache->save($app_id . '_token', $access_token, 86400);
//         if ($post->mini_appid == 'wx738e2ac8b0dfcfc6'){
            $redirect_uri = urlencode('http://api.baizegame.com/gamecode.php');
            $pay_appid = $this->pay_appid;
            $response_type = 'code';
            $scope = 'snsapi_base';
            $state = $post->u_order_id;
            $data = array(
                'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$pay_appid.'&response_type='.$response_type.'&scope='.$scope.'&state='.$state.'&redirect_uri='.$redirect_uri.'#wechat_redirect',
                'money' => $post->money/100,
            );
//         }else{
//             $result = $this->Wxpay_model->unifiedorder($post->open_id,$post->u_order_id,$post->product,$post->cp_role_id,$post->mini_appid,$post->money,$notify_url,'1535960531','cd502521f75fe8c359bcd3f3d1deec0f','Bre0ly3bxa6jz7d4l9rwfuyui6g4ft7Z','JSAPI');
//             //         $result = $this->Wxpay_model->unifiedorder('onJdp5BfuWVJxoOY2hcvpY3zQQAI',time().rand(1000,9999),'元宝',time().rand(100,999),'wx956099b9a7394e81','1',$notify_url,'1535960531','cd502521f75fe8c359bcd3f3d1deec0f','Bre0ly3bxa6jz7d4l9rwfuyui6g4ft7Z','JSAPI');
//             $url_parmas = http_build_query( array(
//                 'appId'=>$result->appid,
//                 'timeStamp'=>$result->time,
//                 'nonceStr'=>$result->nonce_str,
//                 'package'=>$result->prepay_id,
//                 'paySign'=>$result->paySign,
//             ));
//             $data = array(
//                 'url' => 'http://api.baizegame.com/wechat_pay.html?'.$url_parmas,
//                 'money' => $post->money/100,
//             ); 
//         }
        
//         log_message('debug', 'wx_pay' . $url_parmas);
//         log_message('debug', 'wx_pay1' . json_encode($result));
        
//         $url = $data = '<a href="" data-miniprogram-appid="wx956099b9a7394e81" data-miniprogram-path="pages/jumpPay/jumpPay?scene='.$post->u_order_id.'">点击跳转支付</a>';
        $customerService = $this->wx_customerService($access_token,$data,$post->open_id,$post->mini_appid,$_app_key);
//         $customerService = $this->wx_customerService($access_token,$data,'onJdp5BfuWVJxoOY2hcvpY3zQQAI');
        // $this->Output_model->json_print(0, 'ok', $data);
    }
    
    /**
     *微信调用小程序客服
     */
    public function wx_customerService($access_token,$data,$openid,$appid,$key){
        
        $requery_url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
        if($appid=='wxa28dca571995613f' || $appid=='wx3c811f9999daaf5c' || $appid=='wxb200d225440b6e3a'){
            $jsonStr = array(
                'touser' => $openid,
                'msgtype' => 'link',
                'link' => array(
                    'title' => '点我充值',
                    'description'=>'点我充值'.$data['money'].'元',
                    'url'=>$data['url'],
                    'thumb_url'=>'https://s2.ax1x.com/2019/08/19/m1N80O.jpg',
                ),
            );
        }else{
            $jsonStr = array(
                'touser' => $openid,
                'msgtype' => 'link',
                'link' => array(
                    'title' => '点我充值',
                    'description'=>'点我充值'.$data['money'].'元。
如遇问题请联系公众号：白泽游戏中心',
                    'url'=>$data['url'],
                    'thumb_url'=>'https://s2.ax1x.com/2019/08/19/m1N80O.jpg',
                ),
            );
        }
        $jsonStr = json_encode($jsonStr,JSON_UNESCAPED_UNICODE);
        log_message('debug', 'wechat' . $jsonStr);
        $result = $this->http_post_json($requery_url, $jsonStr);
        $json_result = json_decode($result,ture);
        if($json_result['errcode']=='40001'){
            $token = $this->get_accessToken($appid, $key);
            $this->wx_customerService($token, $data, $openid, $appid, $key);
        }
        log_message('debug', 'coco' . $result);
        return $result;
        
    }

    /**
     * 微信接口 JS-SDK权限验证的签名算法
     */
    private function wx_getsignature($jsapi_ticket, $nonce, $url)
    {
        $noncestr = $nonce; // 随机字符串 采用了订单号
        $timestamp = time(); // 时间戳
        // $url = $this->get_web_url(); // 当前网页的URL带参数
        $url = urldecode($url);
        log_message('debug', 'url domain ' . $url);

        $string = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $data = array(
            "noncestr" => $noncestr,
            "jsapi_ticket" => $jsapi_ticket,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
        );

        return $data;
    }

    /**
     * 微信接口 获取getticket
     */
    private function wx_getticket($access_token)
    {

        $requery_url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi";
        $content = $this->Curl_model->curl_get($requery_url);
        if (!$content) {
            $this->Output_model->json_print(1, 'no getticket');
        }

        $response = json_decode($content);
        if ($response && $response->errmsg != "ok") {
            $this->Output_model->json_print(1, 'getticket error ' . $response->errmsg);
            return;
        }
        /**
         * 成功返回json
        {
        "errcode":0,
        "errmsg":"ok",
        "ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKd8-41ZO3MhKoyN5OfkWITDGgnr2fwJ0m9E8NYzWKVZvdVtaUgWvsdshFKA",
        "expires_in":7200
        }
         */

        return $response->ticket;

    }

    /**
     * 测试生成小程序二维码
     */
    public function cat_test_get_qrcode()
    {
        // 根据APPID查找APPKEY
        $app_id = 'wxd83a4607bdc1fd30'; // 接收支付B小程序
        $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $app_id));
        $app_key = $mini_game->mini_key;

        // 1.获取小程序access_token
        $access_token = $this->get_pay_miniprogram_token($app_id, $app_key);
        if (!$access_token) {
            return;
        }

        // 生成小程序码
        $scene = '6da44434db339a3c4ece2954'; // u_order_id 订单号
        $page = 'pages/index/index';
        $qrcode = $this->get_pay_miniprogram_qrcode($access_token, $scene, $page);

        $this->Output_model->json_print(0, 'ok', $qrcode);
    }

    /**
     * 保存订单信息到数据库
     */
    private function save_mini_game_order()
    {
        $open_id = $this->input->get('open_id');
        $appid = $this->input->get('appid');
        $order_id = $this->input->get('order_id');
        $product = $this->input->get('product');
        $product_id = $this->input->get('product_id');
        $money = $this->input->get('money');
        $type = $this->input->get('type');
        $user_id = $this->input->get('user_id');
        $cp_role_id = $this->input->get('cp_role_id');
        $channel = $this->input->get('channel');
        $server_id = $this->input->get('server_id');

        if (!$type) {
            $type = 'JSAPI';
        };
        if (!$open_id || !$appid || !$product || !$product_id || !$money || !$order_id || !$cp_role_id || !$user_id) {
            $this->Output_model->json_print(1, 'params error');
            exit;
        };

        $u_order_id = substr(md5(rand(1, 1000) . '_' . $open_id . '_' . time()), 8, 24);
        $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $appid));
        if (!$mini_game) {
            $this->Output_model->json_print(1, 'appid error');
            exit;
        }
        $data = array(
            'u_order_id' => $u_order_id,
            'open_id' => $open_id,
            'user_id' => $user_id,
            'cp_order_id' => $order_id,
            'mini_appid' => $appid,
            'money' => $money,
            'product' => $product,
            'status' => 0,
            'create_date' => time(),
            'cp_role_id' => $cp_role_id,
            'mini_name' => $mini_game->mini_name,
            'channel' => $channel,
            'server_id' => $server_id,
            'pay_type' => '1',//1为商户号收款
        );
        if ($this->Mini_game_order_model->add($data)) {
            return $u_order_id;
        } else {
            $this->Output_model->json_print(2, 'save u_order_id err');
        }
    }
    
/**
 *  获取小程序全局唯一后台接口调用凭据（access_token）
 */
private function get_pay_miniprogram_token($appid, $key)
{
    $cacheKey = "paytoken:{$appid}";
    $token = $this->cache->redis->get($cacheKey);
    
    if (!empty($token)) {
        return $token;
    }

    $access_token = $this->get_accessToken($appid,$key);
    return $access_token;
    
    
}

private function get_accessToken($appid,$key){
    $cacheKey = "paytoken:{$appid}";
    $requery_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$key";
    $content = $this->Curl_model->curl_get($requery_url);
    if (!$content) {
        $this->Output_model->json_print(2, 'get access_token err');
        log_message('error', 'no content from ' . $requery_url);
        exit;
    }
    
    $response = json_decode($content);
    if ($response && isset($response->errcode)) {
        $this->Output_model->json_print(2, 'get access_token err');
        log_message('error', "get_pay_miniprogram_token err. appid=$appid secret=$key:" . json_encode($response->errmsg));
        exit;
    }
    
    if (!$this->cache->redis->save($cacheKey, $response->access_token, 3600)) {
        log_message('error', "get_pay_miniprogram_token err. appid=$appid secret=$key, redis_save_err");
    }
    
    $this->cache->redis->save($cacheKey, $response->access_token, 3600);
    
    return $response->access_token;
}
    
//     /**
//      * 判断微信用户场景值
//      */
//     public function scene()
//     {
//         $appid = $this->input->get('appid');
//         $user_id = $this->input->get('user_id');
//         $sceneVal = $this->input->get('sceneVal');
        
        
//     }

    /**
     * 获取生成二维码，永久有效，数量暂无限制
     * PHP发送Json对象数据
     *
     */
    private function game_get_pay_miniprogram_qrcode($access_token, $u_order_id, $url)
    {
        $requery_url = "https://api.weixin.qq.com/cgi-bin/shorturl?access_token=$access_token";
        $jsonStr = array(
            'action' => 'long2short',
            'long_url' => $url,
        );
        $jsonStr = json_encode($jsonStr);

        $result = $this->http_post_json($requery_url, $jsonStr);
        //拼接获取二维码api接口 get方法
        $imgUrl = "http://qr.liantu.com/api.php?text=".$result['short_url']."&w=200";
        log_message('debug', "test_h5_pay:".$imgUrl);
        $this->load->model('Curl_model');
        $res = $this->Curl_model->curl_get($imgUrl);
        log_message('debug', "test_h5_pay1:".$res);

//         log_message('debug', '小程序码 '. $result);
        // echo "<image src='".base64_encode($result)."'/>";
        // return base64_encode($result);

        // 保存图片到本地
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/img/wxOrder/" . $u_order_id . "_qrcode.png")) {
            // 将生成的二维码图片保存到本地
            $myfile = fopen($_SERVER['DOCUMENT_ROOT'] . "/img/wxOrder/" . $u_order_id . "_qrcode.png", "w");
            fwrite($myfile, $res);
            fclose($myfile);

            // 合并二维码图片和背景图片 并 保存到本地
            $qrcode = $u_order_id . "_qrcode";
            $this->get_images_merge($qrcode, $u_order_id);

            return $u_order_id;
        } else {
            // todo 需要前端告诉用户重新下单
            $this->Output_model->json_print(1, 'order already exist');
            exit;
        }
    }

    
    /**
     * 获取生成的小程序码，永久有效，数量暂无限制
     * PHP发送Json对象数据
     *
     */
    private function get_pay_miniprogram_qrcode($access_token, $u_order_id, $page)
    {
        $requery_url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$access_token";
        $jsonStr = array(
            'scene' => $u_order_id,
            'page' => $page,
            'width' => '430',
        );
        $jsonStr = json_encode($jsonStr);

        $result = $this->http_post_json($requery_url, $jsonStr);
        
         log_message('debug', $u_order_id.'小程序码 '. $result);
        // echo "<image src='".base64_encode($result)."'/>";
        // return base64_encode($result);

        // 保存图片到本地
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/img/wxOrder/" . $u_order_id . "_qrcode.png")) {
            // 将生成的二维码图片保存到本地
            $myfile = fopen($_SERVER['DOCUMENT_ROOT'] . "/img/wxOrder/" . $u_order_id . "_qrcode.png", "w");
            fwrite($myfile, $result);
            fclose($myfile);

            // 合并二维码图片和背景图片 并 保存到本地
            $qrcode = $u_order_id . "_qrcode";
            $this->get_images_merge($qrcode, $u_order_id);

            return $u_order_id;
        } else {
            // todo 需要前端告诉用户重新下单
            $this->Output_model->json_print(1, 'order already exist');
            exit;
        }
    }

    /**
     * 合并两张图片 并 保存到本地指定路径
     */
    private function get_images_merge($qrcode, $u_order_id)
    {
        $image_1 = $this->return_imgType($_SERVER['DOCUMENT_ROOT'] . "/img/wxOrder/bg.png");
        $image_2 = $this->return_imgType($_SERVER['DOCUMENT_ROOT'] . "/img/wxOrder/$qrcode.png");

        // 创建缩略图画板
        $image_3 = imageCreatetruecolor(imagesx($image_1), imagesy($image_1)); // ($width, $height)
        //创建颜色  透明
        $color = imagecolorallocate($image_3, 45, 171, 90); // 绿色(45, 171, 90)
        //这是把图片背景变成透明
        // imageColorTransparent($image_3, $color);

        imagefill($image_3, 0, 0, $color);

        // 复制图片一到真彩画布中（重新取样-获取透明图片）
        imagecopyresampled($image_3, $image_1, 0, 0, 0, 0, imagesx($image_1), imagesy($image_1), imagesx($image_1), imagesy($image_1));
        // 与图片二合成
        imagecopymerge($image_3, $image_2, 90, 300, 0, 0, imagesx($image_2), imagesy($image_2), 100);
        // 输出合成图片
        imagepng($image_3, $_SERVER['DOCUMENT_ROOT'] . "/img/wxOrder/" . $u_order_id . '.png');
    }

    /**
     * 判断图片类型
     */
    private function return_imgType($img)
    {
        $imgtype = getimagesize($img)['mime'];
        // var_dump($imgtype);
        switch ($imgtype) {
            case "image/png":
                return imagecreatefrompng($img);
                break;
            case "image/jpeg":
                return imagecreatefromjpeg($img);
                break;
            case "image/jpg":
                return imagecreatefromjpeg($img);
                break;
            case "image/gif":
                return imagecreatefromgif($img);
                break;
        }
    }

    /**
     * @param $url 请求url
     * @param $jsonStr 发送的json字符串
     * @return array
     */
    private function http_post_json($url, $jsonStr)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonStr))
        );

        $result = curl_exec($ch);
        return $result;
    }

    private function cp_notify($order)
    {
        $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $order->mini_appid));
        $key = $mini_game->mini_key;

        $sign_str = "order_id=$order->cp_order_id&money=$order->money&product=$order->product&cp_role_id=$order->cp_role_id";
        $sign = md5($sign_str . $key);
        if (strpos($mini_game->notify_url, '?') !== false) {
            $notify_url = $mini_game->notify_url . '&' . $sign_str . '&sign=' . $sign . '&appid=' . $order->mini_appid . '&channel=' . $order->channel;
        }else{
            $notify_url = $mini_game->notify_url . '?' . $sign_str . '&sign=' . $sign . '&appid=' . $order->mini_appid . '&channel=' . $order->channel;
        }
        log_message('debug', 'mini game sign str ' . $sign_str . $key);
        $content = $this->Curl_model->curl_get($notify_url);
        log_message('debug', 'mini game notify ' . $notify_url . ' | ' . $content);
        if ($content) {
            if ($content == 'success') {
                $this->Mini_game_order_model->update(array('status' => 2), array('u_order_id' => $order->u_order_id));
            }
        }

    }

    private function get_wx_order($appid, $mch_id, $nonce_str)
    {
        // https://api.mch.weixin.qq.com/pay/unifiedorder
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder?appid";
    }
    private function notify_err()
    {
        echo "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[参数格式校验错误]]></return_msg></xml>";
        exit;
    }
    private function notify_ok()
    {
        echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
        exit;
    }
    public function share_info()
    {
        echo 'ok';
    }
    
    private function get_real_ip()
    {
        $ip = false;
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {array_unshift($ips, $ip);
            $ip = false;}
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi("^(10|172\.16|192\.168)\.", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
    
    public function addClick(){
        $user_id = $this->input->get('user_id');
        $appid = $this->input->get('appid');
        $condition = array('user_id' => $user_id,'clickType'=>'1','mini_appid'=>$appid);
        $this->load->model('Mini_game_click_model');
        $user = $this->Mini_game_click_model->get_one_by_condition($condition);
        if(!$user){
            $this->load->model('Mini_user_model');
            $new_user = $this->Mini_user_model->get_one_by_condition(array('mini_user_id'=>$user_id,'appid'=>$appid));
            $data = array(
                'user_id'=>$user_id,
                'mini_appid'=>$appid,
                'clickType'=>'1',//点击状态 1为已点击
                'create_date'=>time(),//创建时间
                'channel'=>$new_user->channel,//玩家渠道标识
            );
            $this->db->insert('mini_game_click',$data);
        }
        log_message('debug', "click log:".json_encode($data));
        $this->Output_model->json_print(1,'ok', 'ok');
    }
    
    public function testRequest(){
        log_message('debug', "requesr log:".$this->input->get('test'));
    }

    /**
     *   将数组转换为xml
     *    @param array $data    要转换的数组
     *   @param bool $root     是否要根节点
     *   @return string         xml字符串
     *    @author Dragondean
     */
    public function array_to_xml($data, $root = true){
        $str="";
        if($root)$str .= "<xml>";
        foreach($data as $key => $val){
            if(is_array($val)){
                $child = $this->array_to_xml($val, false);
                $str .= "<$key>$child</$key>";
            }else{
                $str.= "<$key><![CDATA[$val]]></$key>";
            }
        }
        if($root)$str .= "</xml>";
        return $str;
    }
    /**
     * 将xml转为array
     * @param string $xml
     * return array
     */
    public function xml_to_array($xml){
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    public function weixinMsg(){
        $post_data = file_get_contents("php://input");
        log_message('debug', "requesr log1:".$post_data);
        $data = json_decode($post_data,true);
        $gameOrder = $this->Game_model->get_one_by_condition(array('game_id' => $data['game_id']));
        $gameJson = json_decode($gameOrder->platform_key,true);
        $appid = $gameJson['appId'];
        $appkey = $gameJson['appSecret'];
        /**
         * 1.获取小游戏access_token
         */
        $access_token = $this->get_pay_miniprogram_token($appid, $appkey);
        foreach ($data['p_uid'] as $k=>$v){
            $this->weixin_login_Msg($access_token,$data,$v['p_uid'],$appid,$appkey);
//            log_message('debug', "requesr log1:".$v['p_uid']);

        }

    }

    /**
     *调用小游戏客服推送消息
     */
    public function weixin_login_Msg($access_token,$data,$openid,$appid,$appkey){

        $requery_url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;

            $jsonStr = array(
                'touser' => $openid,
                'msgtype' => 'text',
                'text' => array(
                    'content' => '亲爱的玩家，您已经15个小时未登陆《'.$data['game_name'].'》游戏了噢。挂机经验和道具即将溢出，为了保证您的收益，建议您上线领取噢~',
                ),
            );
        $jsonStr = json_encode($jsonStr,JSON_UNESCAPED_UNICODE);
        $result = $this->http_post_json($requery_url, $jsonStr);
        $json_result = json_decode($result,ture);
//        log_message('debug', "requesr log:".$jsonStr);

        if($json_result['errcode']=='40001'){
            $token = $this->get_accessToken($appid, $appkey);
            $this->wx_customerService($token, $data, $openid, $appid, $appkey);
        }
        return $result;

    }
}
