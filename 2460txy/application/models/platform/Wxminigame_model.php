<?php

class Wxminigame_model extends CI_Model
{
    public $platform = 'wxminigame';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $this->load->model('User_model');
    }

    // 屏蔽地区-支付
    private function get_area_arr()
    {
        $area_arr = ['深圳', '广州', '佛山', '东莞'];
        return $area_arr;
    }

    //已封支付小游戏列表
    private function no_gamepay_list(){
        $gamepay_list = [
            'wx48d352ff9c36a3ae',//口袋精灵王
            'wx4060cc29ffcbc27d',//小精灵宝可萌新版
        ];
        return $gamepay_list;
    }

    //是否使用客服支付
    private function get_pay_type(){
        $appid_arr = [
            'wx418df45c4b05ac3c', //定江山
            'wx78a77c48e33b02ad',//英雄训练师
            'wx48d352ff9c36a3ae',//萌宠小精灵球
            'wx6aeacdfff7a8c0c2',//玄元修仙
            'wx5d33f37fe3eb9ad6',//战斗吧皮卡丘
            'wx9beb027f3dee933b',//精灵宝可萌新版
            'wx872c1a156b7a78c8',//口袋小精灵球
            'wx4060cc29ffcbc27d',//小精灵宝可萌新版
            'wxbf9ce259a75861a3',//梦幻名将
            'wx6d65c5f683dfc585',//梦幻战将
            'wxa28dca571995613f',//比卡丘小精灵
            'wxed270f224865f21f',//至劲-比卡比卡丘test
            'wx3c811f9999daaf5c',//至劲-可爱口袋王test
            'wxb200d225440b6e3a',//至劲-口袋比卡丘
            'wx9e6b5ed4a6da3b63',//口袋冒险新世代
            'wxe7f609400cbb571b',//梦幻超进化
            'wx364bfc6950a269d4',//萌宠新世代
            'wxa5f5dd385634a3da',//梦幻大冒险
        ];
        return $appid_arr;
    }

    //充值等级限制列表
    private function get_level(){
        $level_arr = [
            'wx78a77c48e33b02ad',//英雄训练师
            'wx48d352ff9c36a3ae',//萌宠小精灵球
            'wx6aeacdfff7a8c0c2',//玄元修仙
            'wx5d33f37fe3eb9ad6',//战斗吧皮卡丘
            'wx9beb027f3dee933b',//精灵宝可萌新版
            'wx872c1a156b7a78c8',//口袋小精灵球
            'wx4060cc29ffcbc27d',//小精灵宝可萌新版
            'wxbf9ce259a75861a3',//梦幻名将
            'wx6d65c5f683dfc585',//梦幻战将
            'wxa28dca571995613f',//比卡丘小精灵
            'wxed270f224865f21f',//至劲-比卡比卡丘test
            'wx3c811f9999daaf5c',//至劲-可爱口袋王test
            'wxb200d225440b6e3a',//至劲-口袋比卡丘
            'wx9e6b5ed4a6da3b63',//口袋冒险新世代
            'wxe7f609400cbb571b',//梦幻超进化
            'wx364bfc6950a269d4',//萌宠新世代
            'wxa5f5dd385634a3da',//梦幻大冒险
        ];
        return $level_arr;
    }

    //充值等级限制
    private function get_pay_level($appid,$equipment){
        switch ($appid) {
            case 'wx418df45c4b05ac3c':
                if ($equipment=='ios'){
                    $level = '16';//ios
                }else{
                    $level = '16';//android
                }
                break;
            case 'wx78a77c48e33b02ad': //英雄训练师
                if ($equipment=='ios'){
                    $level = '18';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wx48d352ff9c36a3ae':
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wx6aeacdfff7a8c0c2': //玄元修仙
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wx5d33f37fe3eb9ad6': //战斗吧皮卡丘
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wx9beb027f3dee933b': //精灵宝可萌新版
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wx872c1a156b7a78c8': //口袋小精灵球
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wx4060cc29ffcbc27d': //小精灵宝可萌新版
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wxbf9ce259a75861a3': //梦幻名将
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wx6d65c5f683dfc585': //梦幻战将
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wxa28dca571995613f': //比卡丘小精灵
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wxed270f224865f21f': //至劲-比卡比卡丘test
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wx3c811f9999daaf5c': //至劲-可爱口袋王test
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wxb200d225440b6e3a': //至劲-口袋比卡丘
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wx9e6b5ed4a6da3b63': //口袋冒险新世代
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wxe7f609400cbb571b': //梦幻超进化
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wx364bfc6950a269d4': //萌宠新世代
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            case 'wxa5f5dd385634a3da': //梦幻大冒险
                if ($equipment=='ios'){
                    $level = '0';//ios
                }else{
                    $level = '0';//android
                }
                break;
            default:
                # code...
                break;
        }
        return $level;
    }

    public function login($game_id,$channel,$reserve=null)
    {
        // 微信小游戏获取用户唯一ID
        $_data = $this->wx_login($game_id);
        $openid = $_data["openid"];
        $session_key = $_data["session_key"];
        if (!$openid) {
            return false;
        }
        $condition = array(
            'p_uid' => $openid,
            'platform' => $this->platform,
        );

        $user = $this->User_model->get_one_by_condition_array($condition);
        if ($user && !$user->unionid && $_data["unionid"]){
            $add['unionid'] = $_data["unionid"];
            $this->User_model->update($add);
        }
        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $openid,
                'create_date' => time(),
                'game_id' => $game_id,
                'unionid' => $_data["unionid"],
                'channel'=> $channel,
                'reserve'=> $reserve,
            );

            $user_id = $this->User_model->add($user);

            if (!$user_id) {
                log_message('error', 'Login error user create fail');

                return false;
            }

            $user['user_id'] = $user_id;
        }

        // generate random token and save it to cache
        $openKey = md5($user['user_id'] . $user['platform'] . time());
        $this->load->driver('cache', array('adapter' => 'redis'));
        if ($this->cache->redis->is_supported()) {
            $this->cache->redis->save($user['user_id'] . '_token', $openKey, 86400);
            $this->cache->redis->save($user['user_id'] . '_wxopenid', $openid, 60 * 60 * 24);
            $this->cache->redis->save($user['user_id'] . '_session_key', $session_key, 60 * 60 * 24);
        }
        //查询渠道标识表是否有该渠道标识
        $this->load->model('Game_channel_model');
        $where = array('platform'=>$this->platform,'game_id'=>$game_id,'channel'=>$channel);
        $is_channel = $this->Game_channel_model->get_one_by_condition_array($where);
        //渠道标识表无标识则add新渠道标识
        if(!$is_channel){
            $addChannel = array(
                'game_id'=>$game_id,
                'platform'=>$this->platform,
                'channel'=>$channel,
            );
            $this->Game_channel_model->add($addChannel);
        }
        $mipay_offerId = $this->Game_model->get_key($game_id, 'mipay_offerId');
        if(substr($user['channel'],0,4)=='WXMP'){
            $user['channel'] = 'WXMP';
        }else if(substr($user['channel'],0,2)=='ZX'){
            $user['channel'] = 'ZX';
        }else{
            $user['channel'] = $user['channel'];
        }
        //混包前加包名
        if($game_id=='71'){
            $user['channel'] = 'C_'.$user['channel'];
        }else if($game_id=='75'){
            $user['channel'] = 'I_'.$user['channel'];
        }
        $data = array(
            'user_id' => $user['user_id'],
            'openKey' => $openKey,
            'mipay_offerId' => $mipay_offerId,
            'channel' => $user['channel'],
            'session_key' => $session_key,
        );
        if (!$user['unionid'] && ($game_id=='6' || $game_id=='40' || $game_id=='50' || $game_id=='71' || $game_id=='62')){
            $data['is_unionid'] = '1';
        }

        return $data;
    }

    private function wx_login($game_id)
    {
        $code = $this->input->get('code');
        $wx_appid = $this->input->get('appid');

        if (!$code || !$wx_appid) {
            $this->Output_model->json_print(1, 'no parameter');
            exit;
        }

        $appid = $this->Game_model->get_key($game_id, 'appId');
        $key = $this->Game_model->get_key($game_id, 'appSecret');

        if ($wx_appid != $appid) {
            $this->Output_model->json_print(1, 'wx appid err');
            exit;
        }

        // 微信小游戏获取用户唯一ID
        $requery_url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$key&js_code=$code&grant_type=authorization_code";

        $content = $this->Curl_model->curl_get($requery_url);
        if (!$content) {
            $this->Output_model->json_print(2, 'no content from jscode2session');
            log_message('error', $this->platform . ' no content from jscode2session');
            exit;
        }
        $response = json_decode($content);
        log_message('error', $this->platform . '11test wxlogin'.$requery_url);
        log_message('error', $this->platform . '22test wxlogin'.$content);
        if ($response && isset($response->errcode)) {
            $this->Output_model->json_print(3, 'jscode2session error ' . $response->errmsg);
            log_message('error', $this->platform . ' no content from jscode2session');
            exit;
        }

        $openid = $response->openid;
        $session_key = $response->session_key;
        if ($game_id=='6' && $response->unionid){
            $json_arr = json_decode($content,true);
            $this->load->model('User_model');
            $this->User_model->update(array('unionid'=>$json_arr['unionid']),array('p_uid'=>$json_arr['openid'],'game_id'=>$game_id,'platform'=>'wxminigame'));
        }

        $data = array(
            "openid" => $openid,
            "session_key" => $session_key,
        );
        return $data;
    }

    public function post_unionid($array){
        $appid = $this->Game_model->get_key($array['game_id'], 'appId');
        if ($array['wx_appid'] != $appid) {
            $this->Output_model->json_print(1, 'wx appid err');
            exit;
        }
        include_once "WxBizDataCrypt.php";

        $pc = new WXBizDataCrypt($array['wx_appid'], $array['session_key']);
        $errCode = $pc->decryptData($array['encryptedData'], $array['iv'], $data );
        if ($errCode == 0) {
            log_message('debug', 'wx_unionid:' .$data);
            $data = json_decode($data,true);
            $condition = array(
                'game_id'=>$array['game_id'],
                'platform'=>$array['platform'],
                'p_uid'=>$data['openId'],
            );
            $this->load->model('User_model');
            $user = $this->User_model->get_one_by_condition_array($condition);

            if (!$user->unionid){
                //在user表中添加该用户的unionid字段
                $this->User_model->update(array('unionid'=>$data['unionId']),$condition);
            }
        } else {
            log_message('debug', 'error_wx_unionid:' .$errCode);
        }
    }


    public function sign_order($game_id = '')
    {
        $appid = $this->input->get('appid');
        $level = $this->input->get('level');//获取角色等级
        $orderNo = $this->input->get('orderNo');//获取游戏订单号
//        log_message('debug', 'is_user_id:' . $this->input->get('user_id'));
        $data = array();
        $supportArea = $this->get_login_area();
        //获取客服会话支付列表
        $pay_type = $this->get_pay_type();
        //获取已封支付游戏列表
        $no_pay = $this->no_gamepay_list();
        if (in_array($appid,$no_pay)){
            $error = '1';
        }
        //获取充值等级限制列表
        $level_limit = $this->get_level();
        $limitLevel = $this->get_pay_level($appid,$this->input->get('ios_or_android'));
        if(in_array($appid, $level_limit) && $level<$limitLevel){
            $data['is_level'] = '1';
            $data['text'] = '暂时不支持充值';
        }else{
            if ($supportArea && !$error) {
                //$appid
                if (in_array($appid, $pay_type)) {
                    if ($this->input->get('ios_or_android')=='android' && $appid =='wx78a77c48e33b02ad'){
                        $this->load->model('User_model');
                        $user = $this->User_model->get_one_by_condition_array(array('p_uid'=>$this->input->get('user_id'),'game_id'=>'6','platform'=>'wxminigame'));
                        $consumption = round($user['consumption']/100);
                        if ($consumption>300){
                            $data['supportArea'] = 'ok';
                            $data['is_pay'] = '1';
                            $data['title'] = '充值教程';
                            $data['content'] = '即将跳转官方【客服会话】充值给客服[回复充值]获取充值链接';
                            $data['confirmText'] = '前往充值';
                            $data['confirmColor'] = '#576B95';
                        }else{
                            $data['supportArea'] = 'ok';
                        }
                    }else{
                        $data['supportArea'] = 'ok';
                        $data['is_pay'] = '1';
                        $data['title'] = '充值教程';
                        $data['content'] = '即将跳转官方【客服会话】充值给客服[回复充值]获取充值链接';
                        $data['confirmText'] = '前往充值';
                        $data['confirmColor'] = '#576B95';
                    }
                }else{
                    $data['supportArea'] = 'ok';
                }
            } elseif($error=='1' && $orderNo) {
                $this->load->model('Game_order_model');
                $this->load->model('Game_model');
                $gameList = $this->Game_order_model->get_one_by_condition(array('u_order_id'=>$orderNo,'platform'=>'wxminigame'));
                $gameOrder = $this->Game_model->get_one_by_condition(array('game_id' => $gameList->game_id));
                $gameJson = json_decode($gameOrder->platform_key,true);
                $appid = $gameJson['appId'];
                $appkey = $gameJson['appSecret'];
                $redirect_uri = urlencode('http://api.baizegame.com/gamecode.php');
                if($gameOrder->game_id=='52' || $gameOrder->game_id=='58' || $gameOrder->game_id=='59'){
                    $pay_appid = 'wxb1868f696ca3e266';//至劲服务号appid
                }else{
//                    $pay_appid = 'wx9fa1399a3b13f5bc';//301
//                    $pay_appid = 'wxfe535376cd95ff9e';//531
                    $pay_appid = 'wx375234cb72d3b9bd';
                }
                $response_type = 'code';
                $scope = 'snsapi_base';
                $state = $orderNo;
                $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$pay_appid.'&response_type='.$response_type.'&scope='.$scope.'&state='.$state.'&redirect_uri='.$redirect_uri.'#wechat_redirect';
                $returnNo = $this->get_pay_game_qrcode($orderNo,$url,$gameOrder->game_id);
                if ($returnNo){
                    $data['is_pay'] = '2';
                    $data['supportArea'] = 'ok';
                    $data['qrcode'] = $returnNo;
                }else{
                    $data['is_level'] = '1';
                    $data['text'] = '生成订单失败,请重新选购!';
                    $data['supportArea'] = '生成订单失败,请重新选购!';
                }
            } else {
                $data['is_level'] = '1';
                $data['text'] = '暂时不支持充值';
                $data['supportArea'] = '暂时不支持充值';
            }
        }


        return $data;
    }


    /**
     * 获取生成二维码，永久有效，数量暂无限制
     * PHP发送Json对象数据
     *
     */
    private function get_pay_game_qrcode($u_order_id, $url ,$game_id)
    {
        $this->load->model('Curl_model');
        $requery_url = 'https://sohu.gg/api/?key=MLH0qH6vp0eL&url='.urlencode($url);
        $result = $this->Curl_model->curl_get($requery_url);
        log_message('debug', 'no_pay_wxgame_token:' . $requery_url );

        //拼接获取二维码api接口 get方法
        $imgUrl = "https://api.ooopn.com/qr/api.php?text=".$result."&size=430px";
        $res = $this->Curl_model->curl_get($imgUrl);
        log_message('debug', 'no_pay_wxgame_token1:' . $res );

        // 保存图片到本地
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/img/wxOrder/" . $u_order_id . "_qrcode.png")) {
            // 将生成的二维码图片保存到本地
            $myfile = fopen($_SERVER['DOCUMENT_ROOT'] . "/img/wxOrder/" . $u_order_id . "_qrcode.png", "w");
            fwrite($myfile, $res);
            fclose($myfile);

            // 合并二维码图片和背景图片 并 保存到本地
            $qrcode = $u_order_id . "_qrcode";
            $this->get_images_merge($qrcode, $u_order_id ,$game_id);

            return $u_order_id;
        } else {
            // todo需要前端告诉用户重新下单
            $this->Output_model->json_print(1, 'order already exist');
            exit;
        }
    }

    /**
     * 合并两张图片 并 保存到本地指定路径
     */
    private function get_images_merge($qrcode, $u_order_id , $game_id)
    {
        if ($game_id=='52' || $game_id=='58' || $game_id=='59'){
            $pngName = 'wxgame_zj';
        }else{
            $pngName = 'wxgame';
        }
        $image_1 = $this->return_imgType($_SERVER['DOCUMENT_ROOT'] . "/img/wxOrder/".$pngName.".png");
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

    private function get_login_area()
    {
//         $area_arr = $this->get_area_arr();

//         $taobao = "http://ip.taobao.com/service/getIpInfo.php?ip=";
//         $ip = $this->input->ip_address();
//         $content = json_decode($this->Curl_model->curl_get($taobao . $ip));
//         if ($content && isset($content)) {
//             if ($content->code == 0) {
//                 if (isset($content->data->city)) {
//                     $city = $content->data->city;
//                     if (in_array($city, $area_arr)) {
//                         return false;
//                     }
//                 }
//             }
//         }

//         return true;

//         $ip='http://ip-api.com/json/'.$this->input->ip_address().'?lang=zh-CN';
//         //         $ip = $this->input->ip_address();
//         $content = json_decode($this->Curl_model->curl_get($ip));
//         log_message('debug', "Wx mini pay city ".$content->city);
//         if ($content->city) {
//             //             if (isset($content->data->city)) {
//             //                 if ($content->data->city == '成都'||$content->data->city == '珠海'||$content->data->city == '北京'||$content->data->city == '上海'||$content->data->city == '深圳' || $content->data->city == '广州' || $content->data->city == '佛山' || $content->data->city == '东莞') {
//             if ((strpos($content->city,'上海') !==false)||(strpos($content->city,'深圳') !==false) || (strpos($content->city,'广州') !==false) || (strpos($content->city,'佛山') !==false) || (strpos($content->city,'东莞') !==false)) {
//                 return false;
//             };
//             //             };
//         };
//         //支付白名单
//         $whitelistIp = $this->get_whitelistIp_arr();
//         if (in_array($this->input->ip_address(), $whitelistIp)){
//             return true;
//         }

        //         $city=self::baidu_gps($this->input->ip_address());
//         $city=self::pconline($this->input->ip_address());
//         //         $city=self::ip_api($this->input->ip_address());


//         log_message('debug', "city ".$city.' '.$this->input->ip_address());

//         if ($city) {
//             if ((strpos($city,'上海') !==false)||(strpos($city,'深圳') !==false) || (strpos($city,'广州') !==false) || (strpos($city,'佛山') !==false) || (strpos($city,'东莞') !==false)) {
//                 return false;
//             };
//         };
        return true;
    }

    private function pconline($user_ip){
        $this->load->driver('cache', array('adapter' => 'redis'));
        if(!$this->cache->redis->is_supported()){
            log_message('debug', "pconline gps redis error 初始化失败默认返回false");
            return false;
        };
        if($this->cache->redis->get('pconline_gps'.md5($user_ip))){
            log_message('debug', "pconline gps redis succ ".$user_ip);
            return $this->cache->redis->get('pconline_gps'.md5($user_ip));
        }
        $ip="http://whois.pconline.com.cn/ipJson.jsp?ip=".$user_ip."&json=true";
        $content = trim($this->Curl_model->curl_get($ip));
        $encode = mb_detect_encoding($content, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        $str_encode = mb_convert_encoding($content, 'UTF-8', $encode);
        $content=json_decode($str_encode);
        //         log_message('debug', "city pconline6".$content->addr.' '.$ip);
        $this->cache->redis->save('pconline_gps'.md5($user_ip), $content->addr, 3600);
        return $content->addr;
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

        $this->load->driver('cache', array('adapter' => 'redis'));
        if ($this->cache->redis->is_supported()) {
            $openKey = $this->cache->redis->get($openId . '_token');
        }
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
        $game_url = 'https://lcby.gz.1251208707.clb.myqcloud.com/dkm/login/serverlist';
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId&pkg=lcby_bqws_AT&pf=102";
        log_message('debug', "allu login:$url");
        if ($game->game_father_id == 20006) {
            $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId&platform=$this->platform&platformId=$game_id";
        }
        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {

        $ext = $this->input->get('order_id');
        $money = $this->input->get('money');

        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);

        if ($game_order->status >= 1) {
            exit;
        }

        if ($money == $game_order->money) {

            // 米大师发货流程
            $game_id = $game_order->game_id;

            $access_token = $this->get_token($game_id);

            // 米大师-扣除游戏币接口
            $midas_pay = $this->get_midas_pay($game_id, $access_token);

            if ($midas_pay == '0') {
                $this->load->model('Game_order_model');
                $this->Game_order_model->update(array('pay_type' => '2'), array('u_order_id' => $ext));//填充当前支付类型
                return $ext;
            }else if($midas_pay =='40001'){
                $new_access_token = $this->requet_token($game_id);
                $new_midas_pay = $this->get_midas_pay($game_id, $new_access_token);
                if($new_midas_pay=='0'){
                    $this->load->model('Game_order_model');
                    $this->Game_order_model->update(array('pay_type' => '2'), array('u_order_id' => $ext));//填充当前支付类型
                    return $ext;
                }
            }

        }

        return false;
    }

    public function notify_ok()
    {
        echo 'success';
    }

    public function notify_error()
    {
        echo 'fail';
    }

    // 米大师-查询余额接口
    public function get_query_pay($game_id,$access_token,$userid)
    {
        log_message('error', $this->platform . " session_key request err");
        $servid = '1';
//         $money = $this->input->get('money')/10;
//         $order_id = $this->input->get('order_id');
        $user_id = $userid;

        $this->load->driver('cache', array('adapter' => 'redis'));
        if ($this->cache->redis->is_supported()) {
            $openId = $this->cache->redis->get($user_id . '_wxopenid');
            $session_key = $this->cache->redis->get($user_id . '_session_key');

            if (!$openId) {
                $this->Output_model->json_print(2, $this->platform . " $game_id midas_pay openId err");
                log_message('error', $this->platform . " Token request err");
                exit;
            }

            if (!$session_key) {
                $this->Output_model->json_print(2, $this->platform . " $game_id midas_pay session_key err");
                log_message('error', $this->platform . " session_key request err");
                exit;
            }
        }

        // $org_loc = "/cgi-bin/midas/sandbox/pay"; // 沙箱环境
        $org_loc = "/cgi-bin/midas/getbalance";
        $url = "https://api.weixin.qq.com$org_loc?access_token=$access_token";

        $appId = $this->Game_model->get_key($game_id, 'appId');
        $offerId = $this->Game_model->get_key($game_id, 'mipay_offerId');
        $pay_key = $this->Game_model->get_key($game_id, 'mipay_appKey');

        // 1.1 参与米大师签名请求参数
        $data = array(
            "openid" => $openId,
            "appid" => $appId,
            "offer_id" => $offerId,
            "ts" => time(),
            "zone_id" => $servid,
            "pf" => "android",
//             "amt" => $money,
//             "bill_no" => $order_id,
//             "app_remark" => $order_id,
        );
        // 1.2 对参与米大师签名的参数按照key=value的格式，并按照参数名ASCII字典序升序排序如下
        $strA = $this->signData_ksort($data);
        // 1.3拼接uri、method和米大师密钥
        $signTempA = "$strA&org_loc=$org_loc&method=POST&secret=$pay_key";
        // 1.4 把米大师密钥作为key，使用HMAC-SHA256得到签名
        $sig = hash_hmac("sha256", $signTempA, $pay_key);
        // 1.5 赋值
        $data['sig'] = $sig;
        $data['access_token'] = $access_token;

        // 2.1 对参与开平签名的参数按照key=value的格式，并按照参数名ASCII字典序升序排序如下
        $strB = $this->signData_ksort($data);

        // 2.2 拼接uri、method和session_key
        $signTempB = "$strB&org_loc=$org_loc&method=POST&session_key=$session_key";
        // 2.3 把session_key作为key，使用HMAC-SHA256得到签名
        $mp_sig = hash_hmac("sha256", $signTempB, $session_key);
        // 2.4 赋值
        $data['mp_sig'] = $mp_sig;
        $header = array(
            "Content-Type" => "application/json",
        );
        $response = $this->Curl_model->curl_post($url, json_encode($data), $header);

        log_message('error', $this->platform . " $game_id query_pay data: " . $this->signData_ksort($data) . " post response: " . $response);
        return $response;
    }

    public function pay_midas($order_sn,$game_id,$access_token){
        $this->load->model('Game_order_model');
        $condition = array('u_order_id'=>$order_sn);
        $res = $this->Game_order_model->get_one_by_condition($condition);
        $servid = '1';
        $money = $res->money/10;
        $order_id = $res->u_order_id;
        $user_id = $res->user_id;

        $this->load->driver('cache', array('adapter' => 'redis'));
        if ($this->cache->redis->is_supported()) {
            $openId = $this->cache->redis->get($user_id . '_wxopenid');
            $session_key = $this->cache->redis->get($user_id . '_session_key');

            if (!$openId) {
                $this->Output_model->json_print(2, $this->platform . " $game_id midas_pay openId err");
                log_message('error', $this->platform . " Token request err");
                exit;
            }

            if (!$session_key) {
                $this->Output_model->json_print(2, $this->platform . " $game_id midas_pay session_key err");
                log_message('error', $this->platform . " session_key request err");
                exit;
            }
        }

        // $org_loc = "/cgi-bin/midas/sandbox/pay"; // 沙箱环境
        $org_loc = "/cgi-bin/midas/pay";
        $url = "https://api.weixin.qq.com$org_loc?access_token=$access_token";

        $appId = $this->Game_model->get_key($game_id, 'appId');
        $offerId = $this->Game_model->get_key($game_id, 'mipay_offerId');
        $pay_key = $this->Game_model->get_key($game_id, 'mipay_appKey');

        // 1.1 参与米大师签名请求参数
        $data = array(
            "openid" => $openId,
            "appid" => $appId,
            "offer_id" => $offerId,
            "ts" => time(),
            "zone_id" => "1",
            "pf" => "android",
            "amt" => $money,
            "bill_no" => $order_id,
            "app_remark" => $order_id,
        );

        // 1.2 对参与米大师签名的参数按照key=value的格式，并按照参数名ASCII字典序升序排序如下
        $strA = $this->signData_ksort($data);
        // 1.3拼接uri、method和米大师密钥
        $signTempA = "$strA&org_loc=$org_loc&method=POST&secret=$pay_key";
        // 1.4 把米大师密钥作为key，使用HMAC-SHA256得到签名
        $sig = hash_hmac("sha256", $signTempA, $pay_key);
        // 1.5 赋值
        $data['sig'] = $sig;
        $data['access_token'] = $access_token;

        // 2.1 对参与开平签名的参数按照key=value的格式，并按照参数名ASCII字典序升序排序如下
        $strB = $this->signData_ksort($data);

        // 2.2 拼接uri、method和session_key
        $signTempB = "$strB&org_loc=$org_loc&method=POST&session_key=$session_key";
        // 2.3 把session_key作为key，使用HMAC-SHA256得到签名
        $mp_sig = hash_hmac("sha256", $signTempB, $session_key);
        // 2.4 赋值
        $data['mp_sig'] = $mp_sig;
        $header = array(
            "Content-Type" => "application/json",
        );
        $response = $this->Curl_model->curl_post($url, json_encode($data), $header);

        log_message('error', $this->platform . " $game_id midas_pay_test data: " . $this->signData_ksort($data) . ". post response: " . json_encode($response));

        $json = json_decode($response);

        if ($json->errcode === 0) {
            return $json->errcode;
        } else {
            if ($json->errcode == '40001'){
                return $json->errcode;
            }else{
                $this->Output_model->json_print(2, $this->platform . " $game_id midas_pay_test err: " . $json->errcode . " " . $json->errmsg);
                exit;
            }

        }
    }

    // 米大师-扣除游戏币接口
    private function get_midas_pay($game_id, $access_token)
    {
        $servid = $this->input->get('servid');
        $money = $this->input->get('money')/10;
        $order_id = $this->input->get('order_id');
        $user_id = $this->input->get('user_id');

        $this->load->driver('cache', array('adapter' => 'redis'));
        if ($this->cache->redis->is_supported()) {
            $openId = $this->cache->redis->get($user_id . '_wxopenid');
            $session_key = $this->cache->redis->get($user_id . '_session_key');

            if (!$openId) {
                $this->Output_model->json_print(2, $this->platform . " $game_id midas_pay openId err");
                log_message('error', $this->platform . " Token request err");
                exit;
            }

            if (!$session_key) {
                $this->Output_model->json_print(2, $this->platform . " $game_id midas_pay session_key err");
                log_message('error', $this->platform . " session_key request err");
                exit;
            }
        }

        // $org_loc = "/cgi-bin/midas/sandbox/pay"; // 沙箱环境
        $org_loc = "/cgi-bin/midas/pay";
        $url = "https://api.weixin.qq.com$org_loc?access_token=$access_token";

        $appId = $this->Game_model->get_key($game_id, 'appId');
        $offerId = $this->Game_model->get_key($game_id, 'mipay_offerId');
        $pay_key = $this->Game_model->get_key($game_id, 'mipay_appKey');

        // 1.1 参与米大师签名请求参数
        $data = array(
            "openid" => $openId,
            "appid" => $appId,
            "offer_id" => $offerId,
            "ts" => time(),
            "zone_id" => "1",
            "pf" => "android",
            "amt" => $money,
            "bill_no" => $order_id,
            "app_remark" => $order_id,
        );

        // 1.2 对参与米大师签名的参数按照key=value的格式，并按照参数名ASCII字典序升序排序如下
        $strA = $this->signData_ksort($data);
        // 1.3拼接uri、method和米大师密钥
        $signTempA = "$strA&org_loc=$org_loc&method=POST&secret=$pay_key";
        // 1.4 把米大师密钥作为key，使用HMAC-SHA256得到签名
        $sig = hash_hmac("sha256", $signTempA, $pay_key);
        // 1.5 赋值
        $data['sig'] = $sig;
        $data['access_token'] = $access_token;

        // 2.1 对参与开平签名的参数按照key=value的格式，并按照参数名ASCII字典序升序排序如下
        $strB = $this->signData_ksort($data);

        // 2.2 拼接uri、method和session_key
        $signTempB = "$strB&org_loc=$org_loc&method=POST&session_key=$session_key";
        // 2.3 把session_key作为key，使用HMAC-SHA256得到签名
        $mp_sig = hash_hmac("sha256", $signTempB, $session_key);
        // 2.4 赋值
        $data['mp_sig'] = $mp_sig;
        $header = array(
            "Content-Type" => "application/json",
        );
        $response = $this->Curl_model->curl_post($url, json_encode($data), $header);

        log_message('error', $this->platform . " $game_id midas_pay data: " . $this->signData_ksort($data) . ". post response: " . json_encode($response));

        $json = json_decode($response);

        if ($json->errcode === 0) {
            return $json->errcode;
        } else {
            if ($json->errcode == '40001'){
                return $json->errcode;
            }else{
                $this->Output_model->json_print(2, $this->platform . " $game_id midas_pay err: " . $json->errcode . " " . $json->errmsg);
                exit;
            }

        }
    }

    public function signData_ksort($data)
    {
        ksort($data);
        foreach ($data as $k => $v) {
            $tmp[] = $k . '=' . $v;
        }
        $str = implode('&', $tmp);
        return $str;
    }

    /**
     *  获取小程序全局唯一后台接口调用凭据（access_token）
     */
    public function get_token($game_id)
    {
        $this->load->driver('cache', array('adapter' => 'redis'));
        if ($this->cache->redis->is_supported()) {
            $access_token = $this->cache->redis->get('access_token'.$game_id);
        }
        log_message('error', $this->platform . " Token debug $access_token");
        if ($access_token) {
            return $access_token;
        } else {
            return $this->requet_token($game_id);
        }
    }

    public function requet_token($game_id)
    {
        $appId = $this->Game_model->get_key($game_id, 'appId');
        $appSecret = $this->Game_model->get_key($game_id, 'appSecret');

        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appId . '&secret=' . $appSecret;
        $result = $this->Curl_model->curl_get($url);
        if ($result) {
            $token_json = json_decode($result);
            if ($token_json) {
                $this->load->driver('cache', array('adapter' => 'redis'));
                if ($this->cache->redis->is_supported()) {
                    $this->cache->redis->save('access_token'.$game_id, $token_json->access_token, $token_json->expires_in - 2000);
                }

                return $token_json->access_token;
            }
        } else {
            $this->Output_model->json_print(2, $this->platform . ' get wx access_token err');
            log_message('error', $this->platform . " Token request err");
            exit;
        }
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

        $url = 'http://h5.xileyougame.com/index.php/api/focus?openid=' . $user->p_uid;

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
        $url = "http://h5.xileyougame.com/tongji/tongji_game_login/{$user_id}/{$game_id}/{$access_token}";
        $this->Curl_model->curl_get($url);
    }

    public function create_role_collect($data)
    {
        //执行统计请求
        $access_token = md5(substr(time(), 0, 8) . 'aoyouxi');
        //定义统计请求的地址：
        $user_id = $data['p_uid'];
        $game_id = $data['game_id'];
        $url = "http://h5.xileyougame.com/tongji/tongji_create_role/{$user_id}/{$game_id}/{$access_token}";
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

}
