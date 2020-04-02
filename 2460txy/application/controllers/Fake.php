<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fake extends CI_Controller
{
    public function index()
    {
        $this->load->view('fake_game');
    }

    public function notify()
    {
        echo 'SUCCESS';
    }
    public function xianxia()
    {
        $a = '{"cpOrderId":"g2460_30260124_1524133182_155","gameId":"368","goodsId":"1","goodsName":"60\u94bb\u77f3","money":"6.00","orderId":"201804191819426316","role":"1000017971670","server":"1","status":"success","time":"1524133204","uid":"1000017971670","userName":"test666","ext":"g2460_30260124_1524133182_155","signType":"md5","sign":"4eb686cf46dfdf298cbc85206e19ec88"}';
        echo $this->Curl_model->curl_post('http://h5sdk.zytxgame.com/index.php/api/notify/xianxiawangluo/1225', json_decode($a));
    }

    public function test()
    {
        $iipp=$_SERVER["REMOTE_ADDR"];
        echo $iipp;
    }
    public function test1()
    {
        $data = array(
            'startTime' => '1498579200',
            'pfrom_id' =>'207',
            'serverid'=>'8001'

        );
        echo json_encode($data);
        $this->load->model('Curl_model');
        $response = $this->Curl_model->curl_post('http://backstage.gz.1251208707.clb.myqcloud.com/Statis/payment/payhours?', $data);
        echo json_encode($response);
    }


// 以下代码切记 勿动！！！！！！
    //yyb登陆页
    public function yybapp_login()
    {
        $this->load->view('game_login/yybsdk_lc_login.php');
    }
    public function yybapp_jump_login()
    {
        $this->load->view('game_login/yybsdk_lc_jump_login.php');
    }
    public function https_jump_playpage($data = null)
    {
        $url = $this->input->get('url');
        $data = array('url'=>$url);
        $this->load->view('https_play_page.php', $data);
    }
    //将渠道每天登陆人数放入数据库
    public function testRedis()
    {
        // $this->load->driver('cache', array('adapter' => 'redis'));
        $this->cache->save("a", "aaa");
        echo $this->cache->get("a");
    }
    public function check_info()
    {
        echo phpinfo();
    }


// 以上代码切记 勿动！！！！！！
    public function fake11()
    {
        $this->load->model('Game_model');
        echo $this->Game_model->get_key('1018', 'key');
    }
    public function testview()
    {
        $this->load->view('game_login/szww/allu_szww_login.php');
    }
    public function testjs()
    {
        $this->load->model('Server_list_model');
        echo $this->Server_list_model->test_flush_cache();
    }
    public function getsql()
    {
        // 26 1506355200
        // - 86400
        $one = 1504195200;
        $two = $one+86400;
        for ($i=0;$i<30;$i++) {
            $sql = "SELECT sum(`money`) as money  from `game_order` where `platform` ='kemeng' and `status` = '2' and `create_date`  >= '$one' and `create_date` <= '$two' and `orderNo`  in ('250','102')";
            $one += 86400;
            $two = $one+86400;
            $query= $this->db->query($sql)->result();
            // echo json_encode($query);
            echo '<br/>';
        }
    }
    // public function test_curl()
    // {
    //     $this->load->model('Curl_model');
    //     $response = $this->Curl_model->curl_get('http://h5.allugame.com/index.php/AppMain/login');
    //     echo $response;
    //     echo '<br>' . '====' .'<br>';
    //     $my_curl = curl_init();
    //
    //     file_get_contents('https://user.qzone.qq.com/3184428657?_t_=0.3536150436454746');
    //     $str=file_get_contents('https://h5.qzone.qq.com/gamebar/rank?_wv=2097155&_bid=2132&_ws=1&type=fresh&qua=V1_IPH_QZ_7.5.1_1_APP_A&device_info=model%3DiPhone8%2C2%26os%3DiOS%2F11.1.2%26display%3D1242*2208%26ifa%3D0DDCB489-8D43-47E5-B935-02D4B156D5BA%26jailbroken%3D0%26idfa%3DC7919D20-7A59-4E0A-A050-93C1CA69A23A%26inreview%3D0%26appstate%3D0%26keyChainID%3DE39FF5FB-F6D7-45B5-AFA5-0E0C2A26B58E%26qidfa%3D410DEEB2-C68A-D763-8797-ABDED5455564%26qzpatch%3Dnil%26sharpP%3D1');
    //
    //     echo $str;
    // }

    public function wenxian_report($first_date, $last_date)
    {
        $first_date=strtotime($first_date);
        $last_date=strtotime($last_date);
        $this->load->model('Game_order_model');
        $condition = array(
            'game_father_id' => '20010',
            'status' => '2',
            'create_date >= ' => $first_date,
            'create_date <= ' => $last_date,
        );
        $this->db->select('SUM(`money`) as money ,`platform`,COUNT(DISTINCT(`user_id`)) as renshu ,ext , COUNT(`user_id`) as renci');
        $this->db->group_by(array('platform','ext'));
        $response = $this->Game_order_model->get_by_condition($condition);
        if ($response) {
            foreach ($response as $one) {
                echo '总人数: '.$one->renshu.'  总人次: '.$one->renci.'   总金额: '.$one->money/100 .' 渠道名: '.$one->platform.' 区服 '.$one->ext;
                echo '<br />';
            }
        } else {
            echo '没有数据';
            echo $first_date.' '.$last_date;
        }
    }
    public function longcheng_report($first_date, $last_date)
    {
        $first_date=strtotime($first_date);
        $last_date=strtotime($last_date);
        $this->load->model('Game_order_model');
        $condition = array(
            'game_father_id' => '20001',
            'status' => '2',
            'create_date >= ' => $first_date,
            'create_date <= ' => $last_date,
        );
        $this->db->select('SUM(`money`) as money ,`platform`,COUNT(DISTINCT(`user_id`)) as renshu ,ext , COUNT(`user_id`) as renci');
        $this->db->group_by(array('platform','ext'));
        $response = $this->Game_order_model->get_by_condition($condition);
        if ($response) {
            foreach ($response as $one) {
                echo '总人数: '.$one->renshu.'  总人次: '.$one->renci.'   总金额: '.$one->money/100 .' 渠道名: '.$one->platform.' 区服 '.$one->ext;
                echo '<br />';
            }
        } else {
            echo '没有数据';
            echo $first_date.' '.$last_date;
        }
    }


    public function test_redis($a)
    {
        $this->session->set_userdata('a', $a);
        echo $this->session->userdata('a');
        // redis数据上报
         // $this->load->driver('cache', array('adapter' => 'redis'));
         // echo $this->cache->is_supported();
         // $this->cache->save('1','1',3000);
         // $this->cache->redis->save('1','2' , 60*60*24);
         // echo $this->cache->redis->is_supported();
        // if ($this->cache->redis->is_supported()) {
        //     // echo 1;
        //     $today = date("Y-m-d", time());
        //     $redis_data = array(
        //          '1'=>$a
        //      );
        //     $this->cache->redis->save('1', $redis_data, 60*60*24);
        // }else {
        //     // echo 2;
        // }
        // echo json_encode($this->cache->redis->get('1'));
        // redis数据上报done
    }

    public function check_orderId_page()
    {
        $this->load->view('admin/info_tongji/admin_check_order_temp');
    }
    public function test_cache()
    {
        // 方法使用
        $this->load->library('GetMacAddr');
        $mac = $this->GetMacAddr->GetMacAddrr(PHP_OS);
        echo $mac;
        echo $mac->mac_addr;


        // $mac = new GetMacAddr(PHP_OS);
        // echo $mac->mac_addr;
        //
        // echo "<br />";
        // echo md5($mac->mac_addr);
    }
    public $return_array = array(); // 返回带有 MAC 地址的字串数组
       public $mac_addr;
    public function GetMacAddr($os_type)
    {
        switch (strtolower($os_type)) {
                       case "linux":
                               $this->forLinux();
                               break;
                       case "solaris":
                               break;
                       case "unix":
                               break;
                       case "aix":
                               break;
                       default:
                               $this->forWindows();
                               break;
               }

        $temp_array = array();
        foreach ($this->return_array as $value) {
            if (preg_match("/[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f]/i", $value, $temp_array)) {
                $this->mac_addr = $temp_array[0];
                break;
            }
        }
        unset($temp_array);
        echo $this->mac_addr;
        return $this->mac_addr;
    }

    public function forWindows()
    {
        @exec("ipconfig /all", $this->return_array);
        if ($this->return_array) {
            return $this->return_array;
        } else {
            $ipconfig = $_SERVER["WINDIR"]."system32ipconfig.exe";
            if (is_file($ipconfig)) {
                @exec($ipconfig."/all", $this->return_array);
            } else {
                @exec($_SERVER["WINDIR"]."systemipconfig.exe /all", $this->return_array);
            }
            return $this->return_array;
        }
    }

    public function forLinux()
    {
        @exec("ifconfig -a", $this->return_array);
        return $this->return_array;
    }

    public function baqi_check($game_father_id='')
    {
        $this->load->model('User_model');
        $uid = $this->input->get('uid');
        $sid = $this->input->get('sid');
        $key = $this->Game_model->get_key('1198', 'key');
        // $condition = array(
        //     'p_uid' =>  $uid,
        //
        // );
        // $this->db->select('user_id');
        // $_uid = $this->User_model->get_one_by_condition($condition);
        // echo ($_uid['user_id']);
        $this->load->model('Login_report_model');
        $userinfo_condition=array(
            'p_uid'=>$uid,
            // 'user_id'=>$_uid->user_id,
            'server_id'=>$sid,
        );
        if ($game_father_id) {
            $userinfo_condition['game_father_id']=$game_father_id;
        }
        $this->db->order_by('create_date', 'desc');
        $user_info = $this->Login_report_model->get_one_by_condition($userinfo_condition);
        // echo $this->db->last_query();
        // SELECT * FROM `login_report` WHERE `p_uid` = 'w087yx_826038' AND `server_id` = '4' AND `game_father_id` = '20010'
        // SELECT * FROM `login_report` WHERE `user_id` = '37661662' AND `server_id` = '4' AND `game_father_id` = '20009'
        if ($user_info) {
            echo '{"data": {"level": "'.$user_info->level.'","CE":"'.$user_info->power.'","name": "'.$user_info->server_id.'服 '.$user_info->nickname.'","server": "'.$user_info->server_id.'"},"status": 1}';
        } else {
            echo '无数据';
        }
    }
    public function check_pay()
    {
        $this->load->model('Game_order_model');
        $all_date = array(
            'create_date >= ' => date('Y-m-d', mktime(0, 0, 0, date('n'), 1, date('Y'))) ,
            'create_date >= ' => time(),
            'status' => 2,
            'game_father_id'=>20000,
        );
        $this->db->select(' sum(`money`) as money');
        $all_money = $this->Game_order_model->get_one_by_condition($all_date);
        echo $all_money->money;
    }
    public function testpost()
    {
        $date='{"appid":"1000041","data":"{"callbackInfo":"g2460_30381237_1524808468_651","cpOrder":"g2460_30381237_1524808468_651","goodsID":"1","money":"1","orderId":"M20180427135430284747689","roleID":"30381237","serverId":"1","uid":"44214"}","sign":"c7ea124644f9b2addc80fc0477024198"}';
        echo $date.'<br/>';
        $this->load->model('Curl_model');
        $r = $this->Curl_model->curl_post('http://h5sdk.zytxgame.com/index.php/api/notify/xiaowen/1240', json_decode($date));
        echo json_encode($r);
    }
    // public function login()
    // {
    //     $o = $this->input->get('openId');
    //     $this->cache->save($o.'_token', md5($user['user_id'].$user['platform'].time()), 86400);

    //     header("Location: http://119.29.203.165/index.php/enter/trun_to_game/allu/1157?openId=$o");
    // }
    public function testAliPay()
    {
        $data = array(
            'WIDout_trade_no'=>'g2460_37993055_1531787835_884',
            'WIDsubject'=>'元宝',
            'WIDtotal_amount'=>'10',
            'WIDbody'=>'123',

        );
        echo $this->Curl_model->curl_post('http://134.175.161.36/wappay/pay.php', $data);
        // $this->Curl_model->curl_post('http://134.175.161.36/wappay/pay.php', $data);
    }
}
