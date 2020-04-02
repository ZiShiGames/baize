<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_game_switch extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    // 白名单IP
    private function get_whitelistIp_arr()
    {
        $whitelistIp_arr = [
            '58.62.93.104',//公司ip
            '14.152.49.171',//君海ip
            '112.96.133.124',
            '113.87.180.43',
            '112.96.118.58',
            '113.89.239.91',
            '113.109.108.223',
            '112.96.66.109',
        ];
        return $whitelistIp_arr;
    }
    
    private function get_gameAppid(){
        $gameAppid_arr = [
            'wx231934e3b64a7c5a',//定江山
//             'wx48d352ff9c36a3ae',//英雄训练师-分包
        ];
        return $gameAppid_arr;
    }
    
    private function get_coverCity_Ip(){
        $coverCity_arr = [
            'wxa1cf43ffe4e341f5',//小精灵球宝可萌
            'wx411d75d0fbfc32fc',//神奇小精灵球
            'wx95e08b66eca6c2d5',//凡人升仙传
            'wx7422447a32051d74',//萌宠驯宠记
            'wx14bcbba351b6b672',//萌宠驯宠记
            'wx3c632d04481a3d38',//神奇宝可萌
            'wx4db47921cffb6ed1',//海盗贼王历险记
            'wx2f37c8aea0353c5e',//精灵道馆
//            'wxb12040fa41ff010a',//萌宠精灵球
            'wxb3a5a41c448cd56c',//梦幻萌宠
            'wxdbc194faa7400f99',//萌宠道馆官方站
        ];
        return $coverCity_arr;
    }
    
    public function add_Ip(){
        $addIp = $this->input->get('ip');
        $this->load->model('Ip_library_model');
        $ipList = $this->Ip_library_model->get_one_by_condition(array('ip'=>$addIp));
        if(!$ipList){
            if($addIp){
                $data = array(
                    'ip' => $addIp,
                );
                $res = $this->Ip_library_model->add($data);
                if ($res){
                    echo "添加成功！";
                }else{
                    echo "添加失败,请重新尝试！";
                }
            }else{
                echo "IP不能为空！";
            }
        }else if($ipList){
            echo "IP已存在数据库,无需重复添加！";
        }else{
            echo "参数错误！";
        }
    }
    
    
    public function minigame_switch()
    {
        
        $appid = $this->input->get('appid');
        $game_appid = $this->get_gameAppid();
        if (in_array($appid,$game_appid)) {
            $this->Output_model->json_print(0, 'off');
            exit();
        }
        
        /**
         txdgame.baiyoukeji168.cn
         h5api.guoziyx.com
         app.guoziyx.com
         login.guoziyx.com
         pay.guoziyx.com
         game.guoziyx.com
         cdn.guoziyx.com
         api.baizegame.com
         */
        // http://h5api.guoziyx.com/Login/index?gameId=463109&channel=228601
        // INSERT INTO `mini_programs` (`mini_id`, `mini_name`, `mini_appid`, `mini_key`, `game_url`, `notify_url`, `pay_type`, `mchid`, `app_secret`, `app_key`) VALUES (NULL, '果壳1_小精灵', 'wx576cd9788a2a9c91', '4720412dcbd0f07545d86dba45f53f48', 'loginurl', 'notifyurl', 'JSAPI', '0', '0', '0')
        if(!is_dir('GameCache'))//创建缓存文件存放的目录GameCache即第一步配置的目录
        {
            mkdir('GameCache',0777,true);
            chmod('GameCache',0777);//保证linux下文件有权限
        }
        $this->db->cache_on(600);//开启缓存,且1小时过期。
        //获取minigame表数据
        $this->db->select('mini_name, mini_appid, reserve, navigateAppid, path');
        $query = $this->db->get('mini_programs');
        $this->db->select('ip');
        $ipList = $this->db->get('ip_library');
//         $condition['appid'] = $appid;
//         $condition['create_date'] = array(array('egt',strtotime(date('Y-m-d',time()) .'-3 day')),array('lt',strtotime(date('Y-m-d',time())).'+1 day'));
//         $this->load->model('Mini_login_log_model');
//         $Login_log = $this->Mini_login_log_model->get_one_by_condition($condition);
        $this->db->cache_off();
        //遍历获取当前小程序信息
        foreach ($query->result_array as $key => $value) {
            if ($appid==$value['mini_appid']) {
                $gameList['mini_name'] = $value['mini_name'];
                $gameList['mini_appid'] = $value['mini_appid'];
                $gameList['reserve'] = $value['reserve'];
                $gameList['navigateAppid'] = $value['navigateAppid'];
                $gameList['path'] = $value['path'];
            }
        }
        //遍历login日志中p_uid信息
//         foreach ($Login_log->result_array as $k => $v){
//             if
//         }
        //转换minigame备用字段为数组
        $reserve = json_decode($gameList['reserve'],true);
        $advice = array(
            'game_name' => $gameList['mini_name'],
            // 'game_icon' => 'https://api.baizegame.com/img/share/enhjYXNk.png',
        );
        //获取转发图文
        $share_info = $this->get_share_info($appid);
        $data = array(
            'advice_info' => $advice,
            'shareMsg' => $share_info,
            'navigateAppid'=>$gameList['navigateAppid'],
            'path'=>$gameList['path'],
        );
        
        // off 是壳  on 是游戏
        if ($this->get_login_area()) { //为真则非需要屏蔽的IP直接进入游戏
            
//             if (in_array($this->input->ip_address(), $whitelistIp)) {
//                 $this->Output_model->json_print(0, $reserve['switchIp'],$data);
//             }else{
                $this->Output_model->json_print(0, $reserve['switch'],$data);
//             }
        } else {
            foreach ($ipList->result_array as $kk=>$vv){
                if($this->input->ip_address()==$vv['ip']){
                    $res = '1';
                }
            }
            $coverAppid = $this->get_coverCity_Ip();
            if($res=='1'){
                $this->Output_model->json_print(0, $reserve['switchIp'],$data);
            }else if(in_array($appid, $coverAppid)){
                $city=self::pconline($this->input->ip_address());
                if((strpos($city,'深圳') !==false)){
                    $this->Output_model->json_print(0, 'off',$data);
                }else{
                    $this->Output_model->json_print(0, $reserve['switch'],$data);
                }
            }else{
                $this->Output_model->json_print(0, 'off',$data);
            }
        }
        
    }
    public function upAccess_token(){
        $platform_model = 'Wxminigame_model';
        $this->load->model('platform/' . $platform_model);
        $access_token = $this->$platform_model->requet_token($this->input->get('game_id'));
        if($access_token){
            return 'true';
        }else{
            return 'false';
        }
    }
    public function mipay(){
        $game_id = $this->input->get('game_id');
        $orderNo = $this->input->get('orderNo');
        $user_id = $this->input->get('user_id');
        $money = $this->input->get('money');
        $query_mipay = $this->mipay_query($game_id,$user_id); //查询玩家米大师账号余额
        $json_query_mipay = json_decode($query_mipay,true);
        
        if ($json_query_mipay['errcode']=='0'){
            $order_money = round($money/10);
            if ($json_query_mipay['balance']>=$order_money){
                $pay_mipay = $this->mipay_pay($game_id,$orderNo);
                if($pay_mipay=='0'){
                    echo 'true';die;
                }else{
                    echo 'false';die;
                }
            }else if($json_query_mipay['balance']=='0'){
                echo 'true';die;
            }
        }else if($json_query_mipay['errcode']=='40001'){ //若access_token过期，则重新获取token再次调用查询接口
            $access_token = $this->upAccess_token($game_id);
            if($access_token=='true'){
                $query_mipay = $this->mipay_query($game_id,$user_id); //查询玩家米大师账号余额
                $json_query_mipay = json_decode($query_mipay,true);
                if($json_query_mipay['errcode']=='0'){
                    $order_money = round($money/10);
                    if ($json_query_mipay['balance']>=$order_money){
                        $pay_mipay = $this->mipay_pay($game_id,$orderNo);
                        if($pay_mipay=='0'){
                            echo 'true';die;
                        }else{
                            echo 'false';die;
                        }
                    }else if($json_query_mipay['balance']=='0'){
                        echo 'true';die;
                    }
                }
            }
        }
        
    }
    public function mipay_query($game_id,$user_id){
        $platform_model = 'Wxminigame_model';
        $this->load->model('platform/' . $platform_model);
        $access_token = $this->$platform_model->get_token($game_id);
        $mipay_query = $this->$platform_model->get_query_pay($game_id,$access_token,$user_id);
        return $mipay_query;
    }
    
    public function mipay_pay($game_id,$orderNo){
        $platform_model = 'Wxminigame_model';
        $this->load->model('platform/' . $platform_model);
        $access_token = $this->$platform_model->get_token($game_id);
        $midas_pay = $this->$platform_model->pay_midas($orderNo,$game_id,$access_token);
        return $midas_pay;
    }
    public function testgame(){
        $out_trade_no = $this->input->get('order_sn');
        if(!$out_trade_no){
            echo "请输出订单号！";exit;
        }
        $out_trade_no = $this->input->get('order_sn');
        $this->load->model('Common_model');
        $this->Common_model->gameNotify($out_trade_no);

        
    }
    public function get_login_area()
    {    
        
//         log_message('debug', "CIcache ".json_encode($reserve));
//         $city=self::baidu_gps($this->input->ip_address());
        $city=self::pconline($this->input->ip_address());
//         $city=self::ip_api($this->input->ip_address());
        
//         $this->minigameTest();
        log_message('debug', "city ".$city.' '.$this->input->ip_address());
        
        if ($city) {
            if ((strpos($city,'上海') !==false)||(strpos($city,'深圳') !==false) || (strpos($city,'广州') !==false) || (strpos($city,'佛山') !==false) || (strpos($city,'东莞') !==false)) {
                    return false;
             };
        };
        return true;
    }
    
    
    public function sort_params($params)
    {
        if (!$params || gettype($params) != 'array') {
            return false;
        }
        
        $keys = array_keys($params);
        sort($keys);
        $pair = '';
        $index = 0;
        foreach ($keys as $key) {
            if ($index != 0) {
                $pair .= '&';
            }
            $pair .= "$key=".$params["$key"];
            
            ++$index;
        }
        
        return $pair;
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
    
    private function ip_api($user_ip){
        $ip='http://ip-api.com/json/'.$this->input->ip_address().'?lang=zh-CN';
        $content = json_decode($this->Curl_model->curl_get($ip));
        return $content->city;
    }
    
    private function baidu_gps($user_ip){
        $this->load->driver('cache', array('adapter' => 'redis'));
        if(!$this->cache->redis->is_supported()){
            log_message('debug', "baidu gps redis error 初始化失败默认返回false");
            return false;
        };
        
        if($this->cache->redis->get('baidu_gps'.md5($user_ip))){
            log_message('debug', "baidu gps redis succ ".$user_ip);
            return $this->cache->redis->get('baidu_gps'.md5($user_ip));
        }
        
        $ak='g0QL1BLIs9uIeGu17mxkzmg5WqcKvdan';
        $sk='ouSNLBVMIqzIHuRMtVbwZ4nkVO4GGFHM';
        $uri = '/location/ip';
        $querystring_arrays = array (
            'ip' => $user_ip,
            'ak' => $ak
        );
        
        $querystring = http_build_query($querystring_arrays);
        $sn=md5(urlencode($uri.'?'.$querystring.$sk));
        
        $url = "http://api.map.baidu.com".$uri."?".$querystring."&sn=".$sn;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if(curl_errno($ch)) {
            log_message('debug', "baidu gps error ".'CURL ERROR Code: '.curl_errno($ch).', reason: '.curl_error($ch));
        }
        curl_close($ch);
        $info = @json_decode($output, true);
        if($info['status'] == "0"){
            $addr_info = $info['content']['address_detail']['province'].' '.$info['content']['address_detail']['city'];
        }else{
            log_message('debug', "baidu gps error ".@json_encode($info));
            return false;
        }
        $this->cache->redis->save('baidu_gps'.md5($user_ip), $addr_info, 3600);
        return $addr_info;
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

    private function get_share_info($appid = null)
    {
        if (!$appid) {
            return false;
        }
        
        switch ($appid) {
            case ($appid == 'wx576cd9788a2a9c91' || $appid == 'wx1e736935ed9c9ba3' || $appid == 'wx0d85ccf6e166b152' || $appid == 'wx95e08b66eca6c2d5'):
                $pokemon_share_info = array(
                array(
                'title' => '修炼渡劫，装备全靠打，仙侠我只玩这款！',
                'imageUrl' => 'https://api.baizegame.com/img/share/YXNk.jpg',
                'path' => '/pages/game/game?channel=allu',
                ),
//                 array(
//                 'title' => '注册送坐骑，1天200级，战力轻松破千万！',
//                 'imageUrl' => 'https://api.baizegame.com/img/share/YWRhc2R6.jpg',
//                 'path' => '/pages/game/game?channel=allu',
//                 ),
                array(
                'title' => '无vip，极品宝宝上线送，快来一起玩吧！',
                'imageUrl' => 'https://api.baizegame.com/img/share/enhj.jpg',
                'path' => '/pages/game/game?channel=allu',
                ),
                );
                break;
            case ($appid == 'wx49cda2492f8feedb' || $appid == 'wx6422ec3e91d4dc1e'||$appid=='wxd5c814262e2adf8c'||$appid=='wx35d670d46e24054d'||$appid=='wxb12040fa41ff010a'):
                $pokemon_share_info = array(
                array(
                'title' => '我的第一只宝可梦竟然是鲤鱼王？快来看看你的吧！',
                'imageUrl' => 'https://api.baizegame.com/img/share/zYilA2y3.png',
                'path' => '/pages/game/game?channel=allu',
                ),
                array(
                'title' => '神奇宝贝新玩法，上线就送皮神、VIP！',
                'imageUrl' => 'https://api.baizegame.com/img/share/bYWvTf0A.png',
                'path' => '/pages/game/game?channel=allu',
                ),
                array(
                'title' => '口袋妖怪复刻版，上线就送御三家！',
                'imageUrl' => 'https://api.baizegame.com/img/share/8oqD79kN.png',
                'path' => '/pages/game/game?channel=allu',
                ),
                array(
                'title' => '挂了一个晚上终于抓到超梦！我才是最强训练师！',
                'imageUrl' => 'https://api.baizegame.com/img/share/S3czsQ2u.png',
                'path' => '/pages/game/game?channel=allu',
                ),
                array(
                'title' => '试玩3分钟就抓到皮神，轻松吊打全服神兽！',
                'imageUrl' => 'https://api.baizegame.com/img/share/oH1XFieK.png',
                'path' => '/pages/game/game?channel=allu',
                ),
                array(
                'title' => '注册送VIP，试玩3分钟就抓到神兽超梦！',
                'imageUrl' => 'https://api.baizegame.com/img/share/8SSonsWT.png',
                'path' => '/pages/game/game?channel=allu',
                ),
                );
                break;
            case ($appid == 'wxe421768ac88eb26f' || $appid == 'wxef93db96e3d80021' || $appid == 'wx8f98ba4853f7a1f0' || $appid == 'wxe15bc168663764e7' || $appid == 'wx04f82beabac00f73'|| $appid == 'wx0b59618311028aca'|| $appid == 'wx93622ae9f4eac32c'):
                $pokemon_share_info = array(
                array(
                'title' => '少年三国全新体验，无vip，天天送元宝！',
                'imageUrl' => 'https://api.baizegame.com/img/share/UYaPwQ.jpg',
                'path' => '/pages/game/game?channel=allu',
                ),
                array(
                'title' => '经典回合，Q萌三国，上线送极品武将！',
                'imageUrl' => 'https://api.baizegame.com/img/share/UYaPqC.jpg',
                'path' => '/pages/game/game?channel=allu',
                ),
                array(
                'title' => '开局任选三国红颜，战力轻松涨99w！',
                'imageUrl' => 'https://api.baizegame.com/img/share/UYaPoL.jpg',
                'path' => '/pages/game/game?channel=allu',
                ),
                );
                break;
            default:
//                 $pokemon_share_info = array(
//                 array(
//                 'title' => '去吧小精灵0',
//                 'imageUrl' => 'http://h5sdk-xly.xileyougame.com/img/qr3.jpg',
//                 'path' => '/pages/game/game?channel=allu',
//                 ),
//                 array(
//                 'title' => '去吧小精灵1',
//                 'imageUrl' => 'http://h5sdk-xly.xileyougame.com/img/qr3.jpg',
//                 'path' => '/pages/game/game?channel=allu',
//                 ),
//                 );
//                 break;
        }
        return $pokemon_share_info;
    }

    public function index()
    {
        echo 'no page';
    }

    // 小程序切换开关 shipingkaqiandun

    public function change_game_switch_all()
    {
        $type = $this->input->get('type');
        if (!$type) {
            echo 'no type';
            exit;
        }
        switch ($type) {
            case 'web_one':
                echo 'g0';
                break;
            default:
                echo 'orther type';
                exit;
                break;
        }
    }

    public function game_switch()
    {
        $myfile = fopen('1.txt', 'r+');
        echo fgets($myfile);
        // 刷新缓存
        $this->flush_game_map();

        fclose($myfile);
    }

    private function flush_game_map()
    {
        $this->Curl_model->curl_get($_SERVER['SERVER_ADDR'] . "/index.php/trigger/game_flush_all");
    }
    
    public function cp_notify($order)
    {
        if ($_POST['status']=='1'){
            $mini_game = $this->Mini_programs_model->get_one_by_condition(array('mini_appid' => $_POST['mini_appid']));
            $key = $mini_game->mini_key;
            
            $sign_str = "order_id=".$_POST['cp_order_id']."&money=".$_POST['money']."&product=".$_POST['product']."&cp_role_id=".$_POST['cp_role_id'];
            $sign = md5($sign_str . $key);
            if (strpos($mini_game->notify_url, '?') !== false) {
                $notify_url = $mini_game->notify_url . '&' . $sign_str . '&sign=' . $sign . '&appid=' . $_POST['mini_appid'] . '&channel=' . $_POST['channel'];
            }else{
                $notify_url = $mini_game->notify_url . '?' . $sign_str . '&sign=' . $sign . '&appid=' . $_POST['mini_appid'] . '&channel=' . $_POST['channel'];
            }
            log_message('debug', 'mini game sign str ' . $sign_str . $key);
            $content = $this->Curl_model->curl_get($notify_url);
            log_message('debug', 'mini game notify ' . $notify_url . ' | ' . $content);
            if ($content) {
                if ($content == 'success') {
                    $this->Mini_game_order_model->update(array('status' => 2), array('u_order_id' => $_POST['u_order_id']));
                    return true;
                }else{
                    return false;
                }
            }
        }
    }

}
