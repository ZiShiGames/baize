<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{
    private $CACHE_DRIVER = '';
    //531商户号
//    public $pay_appid = 'wxfe535376cd95ff9e';
//    public $pay_key = 'cd502521f75fe8c359bcd3f3d1deec0f';
//    public $mchid = '1535960531';
//    public $apikey = 'Bre0ly3bxa6jz7d4l9rwfuyui6g4ft7Z';
//    public $mchid_type = '531';

//    //301商户号
//    public $pay_appid = 'wx9fa1399a3b13f5bc';
//    public $pay_key = '6bf08a25bf10a060b51bd720b0b4c2e8';
//    public $mchid = '1572077301';
//    public $apikey = '314504ac90b4876581b43b278099956e';
//    public $mchid_type = '301';

//    //701商户号
    public $pay_appid = 'wx375234cb72d3b9bd';
    public $pay_key = '53b5edd4e9c38f6fdc95ab936ccbbf5f';
    public $mchid = '1560006701';
    public $apikey = 'mdy0y4htai4kbxw1uke526nh17g3ybbt';
    public $mchid_type = '701';

    public function __construct()
    {
        parent::__construct();
        // header('Access-Control-Allow-Origin:*');

        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,x-token");
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
            exit;
        }
        $this->load->driver('cache', array('adapter' => 'redis', 'backup' => 'apc'));
        ($this->cache->redis->is_supported()) ? $this->CACHE_DRIVER = 'redis' : '';
        ($this->cache->apc->is_supported()) ? $this->CACHE_DRIVER = 'apc' : '';

    }

    public function index()
    {
        // if ($_SERVER['HTTP_HOST']!='admin.allugame.com' && $_SERVER['HTTP_HOST']!='h5sdk.zytxgame.com'&& $_SERVER['HTTP_HOST']!='h5sdk-xly.zytxgame.com') {
        if ($_SERVER['HTTP_HOST'] == 'h5sdk-xly.xileyougame.com' && $this->Curl_model->curl_get("http://h5sdk-xly-admin.xileyougame.com")) {
            header("Location: http://h5sdk-xly-admin.xileyougame.com");
            //     return;
        } else {
            $this->load->view('admin/admin_login');
        }
    }
    public function admin_login()
    {
        $user = $this->input->post('user');
        $password = $this->input->post('password');
        // echo md5($password.$this->ADMIN_SALT);
        if (!$user || !$password) {
            $this->Output_model->json_print(1, 'user or password empty');

            return;
        }
        $this->load->model('Admin_user_model');
        $condition = array(
            'admin_user_name' => $user,
        );
        $admin_info = $this->Admin_user_model->get_one_by_condition($condition, null, null, null, null, null, null);
        if (!$admin_info) {
            $this->Output_model->json_print(-1, 'user not found');
        }
        if (md5($password . $this->Admin_user_model->ADMIN_SALT) == $admin_info->admin_user_password) {
            $this->session->set_userdata('role', $admin_info);
            $this->Output_model->json_print(0, 'ok');
        } else {
            // echo md5($password.$this->Admin_user_model->ADMIN_SALT);
            $this->Output_model->json_print(2, 'user or password error');
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
    }

    public function get_user_info()
    {
        $token = $this->input->get('token');

        if ($token && $this->CACHE_DRIVER) {
            $cache_driver = $this->CACHE_DRIVER;
            $data = $this->cache->$cache_driver->get($token);
            $this->Output_model->json_print(0, 'ok', $data);

        } else {
            $this->Output_model->json_print(1, 'err', $data);
        }

    }

    public function admin_login_json()
    {
        $request = json_decode(file_get_contents("php://input"));
        if (!isset($request->username) || !isset($request->password)) {
            $this->Output_model->json_print(1, 'user or password empty');
            exit;
        } else {
            $user = $request->username;
            $password = $request->password;
        }
        $this->load->model('Admin_user_model');
        $condition = array(
            'admin_user_name' => $user,
        );
        $admin_info = $this->Admin_user_model->get_one_by_condition($condition, null, null, null, null, null, null);
        if (!$admin_info) {
            $this->Output_model->json_print(-1, 'user not found');
        }
        if (md5($password . $this->Admin_user_model->ADMIN_SALT) == $admin_info->admin_user_password) {
            $token = md5($user . time());
            $data = array(
                'token' => $token,
                // 'admin_info'=>json_encode($this->session->userdata('role'))
                // 'reids' => $this->cache->redis->is_supported(),
            );
            if ($this->CACHE_DRIVER) {
                $cache_driver = $this->CACHE_DRIVER;
                $this->cache->$cache_driver->save($token, $admin_info, 86400);
                $this->Output_model->json_print(0, 'ok', $data);
            } else {
                $this->Output_model->json_print(-1, 'cache driver error');
            }

        } else {
            // echo md5($password.$this->Admin_user_model->ADMIN_SALT);
            $this->Output_model->json_print(2, 'user or password error');
        }
    }
    //获取用户对应商户号openid
    public function get_pay_openid(){
        $redirect_uri = urlencode('http://api.baizegame.com/wxpay_login.php');//接收code地址
        $pay_appid = $this->pay_appid;
        $response_type = 'code';
        $scope = 'snsapi_base';
        $state = $this->input->get('type');//json数据 [用户unionid和游戏id以及用户userid]
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$pay_appid.'&redirect_uri='.$redirect_uri.'&response_type='.$response_type.'&scope='.$scope.'&state='.$state.'#wechat_redirect';
        header("Location:".$url);
    }

    public function save_pay_openid(){
        $code = $this->input->get('code');
        $type = $this->input->get('type');
        if(!$type || !$code){exit;}

        $appid = $this->pay_appid;
        $key = $this->pay_key;
        $mchid_type = $this->mchid_type;

        $requery_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$key&code=$code&grant_type=authorization_code";
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
        //当前玩家对应服务号的openid以及unionid
        $openid = $response->openid;
        $this->load->model('Wxh5_useroid_model');
        $arr = json_decode(urldecode($type),true);
        $oid = $this->Wxh5_useroid_model->get_one_by_condition(array('user_id'=>$arr['user_id'],'pay_openid'=>$openid,'mchid_type'=>$mchid_type));
        if(!$oid) {
            $save = array(
                'user_id' => $arr['user_id'],
                'pay_openid' => $openid,
                'mchid_type' => $mchid_type,
            );
            $this->Wxh5_useroid_model->add($save);
        }
        $url = 'http://api.baizegame.com/index.php/enter/play/wxh5game/' . $arr['type'];
        if($openid){
            echo "<form style='display:none;' id='form1' name='form1' method='post' action='{$url}'>
                      <input name='uid' type='text' value='{$arr["user_id"]}' />
                  </form>
                  <script type='text/javascript'>function load_submit(){document.form1.submit()}load_submit();</script>";
        }


//        $url = 'http://api.baizegame.com/index.php/enter/play/wxh5game/' . $arr['type'] . '?uid=' . $arr['unionid'];
//        if ($openid) {
//            header("Location:" . $url);
//        }
    }

    public function wxgame(){
        $state = $this->input->get('type');//游戏id
        $redirect_uri = urlencode('http://api.baizegame.com/wxgame.php');//接收code地址
        if($state=='68'){
            $pay_appid = 'wx553178c50aa8f246'; //公众号appid
        }elseif($state=='69'){
            $pay_appid = 'wx112147f2ed8c7a55'; //公众号appid
        }else if($state=='70'){
            $pay_appid = 'wx8ad1820021c33a69'; //公众号appid
        }else if($state=='72'){
            $pay_appid = 'wx8ae293b9396a4b5d'; //公众号appid
        }else if($state=='73'){
            $pay_appid = 'wx8ae293b9396a4b5d'; //公众号appid
        }else if($state=='76'){
            $pay_appid = 'wx8ae293b9396a4b5d'; //公众号appid
        }
        $response_type = 'code';
        $scope = 'snsapi_userinfo';
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$pay_appid.'&redirect_uri='.$redirect_uri.'&response_type='.$response_type.'&scope='.$scope.'&state='.$state.'#wechat_redirect';
        header("Location:".$url);
    }

    public function gowxgame(){
        $code = $this->input->get('code');
        $type = $this->input->get('type');
        if(!$type || !$code){exit;}

        if ($type=='68'){ //英雄训练师
            $appid = 'wx553178c50aa8f246';//服务号appid [Happy玩游戏中心]
            $key = '606e61c6c58bc8e44cda7550abd81526';//服务号appsecret [Happy玩游戏中心]
            $game_id='6';
        }else if($type=='69'){ //口袋精灵王
            $appid = 'wx112147f2ed8c7a55';//服务号appid [紫狮]
            $key = '6defebfbf14bde2b40697389470c3e49';//服务号appsecret [紫狮]
            $game_id='40';
        }else if($type=='70'){ //小精灵宝可萌新版
            $appid = 'wx8ad1820021c33a69';//服务号appid [白泽网络科技]
            $key = '1912cf74a5548fd1f54f33354e305e8c';//服务号appsecret [白泽网络科技]
            $game_id='50';
        }else if($type=='72'){ //超梦超进化
            $appid = 'wx8ae293b9396a4b5d';//服务号appid [嗨玩游戏-炎龙服务号]
            $key = 'a5f1138ad8939022b66dcd8b317b5cb3';//服务号appsecret [嗨玩游戏-炎龙服务号]
            $game_id='62';
        }else if($type=='73'){ //萌宠新世代
            $appid = 'wx8ae293b9396a4b5d';//服务号appid [嗨玩游戏-炎龙服务号]
            $key = 'a5f1138ad8939022b66dcd8b317b5cb3';//服务号appsecret [嗨玩游戏-炎龙服务号]
            $game_id='71';
        }else if($type=='76'){ //梦幻大冒险
            $appid = 'wx8ae293b9396a4b5d';//服务号appid [嗨玩游戏-炎龙服务号]
            $key = 'a5f1138ad8939022b66dcd8b317b5cb3';//服务号appsecret [嗨玩游戏-炎龙服务号]
            $game_id='75';
        }
        $requery_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$key&code=$code&grant_type=authorization_code";
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
        //当前玩家对应服务号的openid以及unionid
        $openid = $response->openid;
        $access_token = $response->access_token;
        $unionidUrl = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $get_unionid = $this->Curl_model->curl_get($unionidUrl);

        $json_arr = json_decode($get_unionid);
        $this->load->model('User_model');
        $condtion = array('unionid'=>$json_arr->unionid,'platform'=>'wxminigame','game_id'=>$game_id);
        $userInfo = $this->User_model->get_one_by_condition($condtion);
//        $url = 'http://api.baizegame.com/index.php/enter/play/wxh5game/'.$type.'?uid='.$userInfo->user_id;
//        if($userInfo){
//            header("Location:".$url);
//        }
        $arr = array(
            'type'=>$type,
            'unionid'=>$userInfo->unionid,
            'user_id'=>$userInfo->user_id,
        );
        $jsonUser = urlencode(json_encode($arr));
        $url = 'http://api.baizegame.com/index.php/login/get_pay_openid?type='.$jsonUser;
        if($userInfo){
            header("Location:".$url);
        }else{
            if($game_id=='6'){
                header("Location:http://api.baizegame.com/wxgame_error.html");
            }else if($game_id=='40'){
                header("Location:http://api.baizegame.com/wxgame_error40.html");
            }else if($game_id=='50'){
                header("Location:http://api.baizegame.com/wxgame_error50.html");
            }else if($game_id=='62'){
                header("Location:http://api.baizegame.com/wxgame_error62.html");
            }else if($game_id=='71'){
                header("Location:http://api.baizegame.com/wxgame_error71.html");
            }else if($game_id=='75'){
                header("Location:http://api.baizegame.com/wxgame_error75.html");
            }
        }
    }

    //微信h5支付转换用户openid
    public function wxgame_pay(){
        $order_openid = $this->input->get('order_sn'); //订单号
        log_message('debug', 'test_h5pay:'.$order_openid);
        //查询订单数据
        $this->load->model('Game_order_model');
        $Condition = array('u_order_id'=>$order_openid);
        $game_order = $this->Game_order_model->get_by_condition($Condition,'1','0','order_id','desc','','');
        $newCondition = array('user_id'=>$game_order[0]->user_id,'game_id'=>$game_order[0]->game_id);
        $newGame_order = $this->Game_order_model->get_by_condition($newCondition,'1','0','order_id','desc','','');
        if($newGame_order[0]->u_order_id!=$game_order[0]->u_order_id){
            $order_repeat = '1';
        }

        $pay_appid = $this->pay_appid;
        $pay_key = $this->pay_key;
        $mchid = $this->mchid;
        $apikey = $this->apikey;
        $mchid_type = $this->mchid_type;
//        $code = $this->input->get('code');

//        $requery_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$pay_appid&secret=$pay_key&code=$code&grant_type=authorization_code";
//        $this->load->model('Curl_model');
//        $content = $this->Curl_model->curl_get($requery_url);
//        log_message('error', 'test_h5pay1:'.$content);
//        log_message('error', 'test_h5pay2:'.json_encode($game_order[0]));
//
//        if (!$content) {
//            $this->Output_model->json_print(2, 'no content from jscode2session');
//            log_message('error', 'jump_pay no content from jscode2session');
//            return;
//        }
//        $response = json_decode($content);
//        if ($response && isset($response->errcode)) {
//            $this->Output_model->json_print(3, 'jscode2session error ' . $response->errmsg);
//            log_message('error', 'jump_pay no content from jscode2session');
//            return;
//        }
        //当前玩家对应服务号的openid
//        $openid = $response->openid;
        $this->load->model('Wxh5_useroid_model');
        $user = $this->Wxh5_useroid_model->get_one_by_condition(array('user_id'=>$game_order[0]->user_id,'mchid_type'=>$mchid_type));
        $openid=$user->pay_openid;

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
            $game_order = $game_order[0];
            $this->load->model('Wxpay_model');
            $result = $this->Wxpay_model->unifiedorder($openid,$game_order->u_order_id,$game_order->goodsName,$game_order->cproleid,$pay_appid,$game_order->money,$gameNotify_url,$mchid,$pay_key,$apikey,'JSAPI');
            log_message('debug', 'test_h5pay3:'.$result);

            $url_parmas = http_build_query( array(
                'appId'=>$result->appid,
                'timeStamp'=>$result->time,
                'nonceStr'=>$result->nonce_str,
                'package'=>$result->prepay_id,
                'paySign'=>$result->paySign,
            ));
        }

        $resUrl = 'http://api.baizegame.com/wxh5pay.html?'.$url_parmas;
        $data = array(
            'pay_url' => $resUrl,
        );

        return $data;

    }

    private function get_host_url()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'] . '/';
        return $protocol . $domainName;
    }


    public function testDate(){
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_by_condition(array('game_id'=>'74','status'=>'2','report'=>'0'));
        if($game_order){
            foreach ($game_order as $v){
                if($v->platform_order_id){
                    $this->other_report($v,'weixinpay');
                }else{
                    $this->other_report($v,'alipay');
                }
            }
        }
    }
    //第三方订单上报
    public function other_report($parmas,$pay){
        $other_report=json_decode($parmas->other_report,true);
        //获取支付账号
        $this->load->model('User_model');
        $condition=array('user_id'=>$parmas->user_id);
        $user = $this->User_model->get_one_by_condition($condition);

        if (in_array($parmas->game_id, ['29', '39', '74']) && $parmas->report=='0'){
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
                    log_message('debug', "other_report notify log".$v['_deviceid'].$response);
                }
            }
        }
    }
}
