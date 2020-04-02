<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Api extends CI_Controller
{
    public function get_user()
    {
        $openId = $this->input->get('openId');
        $openKey = $this->input->get('openKey');
        if (!$openId || !$openKey) {
            $this->Output_model->json_print(1, 'error no openId or openKey');

            return;
        }

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $token = $this->cache->get($openId . '_token');
        if ($openKey != $token) {
            $this->Output_model->json_print(2, 'token error');

            return;
        }

        $condition = array('user_id' => $openId);
        $user = $this->User_model->get_one_by_condition($condition);
        if (!$user) {
            $this->Output_model->json_print(3, 'user not found');

            return;
        }
        $res = array(
            'nickName' => $user->nickname,
            'avatarUrl' => $user->avatar,
        );
        echo json_encode($res);
    }

    public function getAppInfo()
    {
        $appId = $this->input->get('appId');
        if (!$appId) {
            $this->Output_model->json_print(1, '');

            return;
        }

        $game = $this->Game_model->get_by_game_id($appId);
        if ($game) {
            unset($game->app_id);
            unset($game->app_key);
            unset($game->game_pay_nofity);

            $this->Output_model->json_print(0, '', $game);
        } else {
            $this->Output_model->json_print(2, '');
        }
    }

    public function createPay()
    {
        $openId = $this->input->get('openId');
        $openKey = $this->input->get('openKey');
        $appId = $this->input->get('appId');
        $money = $this->input->get('money');
        $orderNo = $this->input->get('orderNo');
        $ext = $this->input->get('ext');
        $pfid = $this->input->get('pfid');
        $data = $this->input->get('data');
        $goodsName = $this->input->get('goodsName');
        $platform = $this->input->get('platform');
        $cproleid = $this->input->get('cproleid');
        $current_role_level = $this->input->get('current_role_level');
        if (!$openId || !$appId || !$money || !$goodsName) {
            echo '参数不全';

            return;
        }
        if($platform=='gowanmemlzf'){
            log_message('debug', "gowanme cporder:" . $orderNo ."  cproleid:".$cproleid);
        }
        if($orderNo=='0' || !$orderNo){
            log_message('debug', "not cporder:" . $orderNo);
            echo '参数不全';
            return;
        }
        // if($ext >= 1000){
        //     echo '服务器id错误';
        //     exit;
        // }
        // if ($pfid) {
        //     $ext .= '_'.$pfid;
        // }

        $game = $this->Game_model->get_by_game_id($appId);
        if (!$game) {
            return;
        }
        if ($game->pay_status != 1) {
            $this->Output_model->json_print(2, 'close pay');
            exit;
        }

        $u_order_id = rand(1, 1000) . '_' . $openId . '_' . time() . '_' . rand(1, 1000);
        $condition = array(
            'user_id' => $openId,
        );
        $user = $this->User_model->get_one_by_condition($condition);
        $data = array(
            'u_order_id' => $u_order_id,
            'user_id' => $openId,
            'game_id' => $appId,
            'platform' => $game->platform,
            'money' => $money,
            'orderNo' => $orderNo,
            'ext' => $ext,
            'data' => $data,
            'goodsName' => $goodsName,
            'status' => 0,
            'create_date' => time(),
            'game_father_id' => $game->game_father_id,
            'cproleid' => $cproleid,
            'channel' => $user->channel?$user->channel:'allu',
        );
        if ($game->pay_game_id){
            $user_webType = $this->is_weixin();
            if ($user_webType=='2'){
                $data['goto_game']='1';//订单为h5链接订单
            }
        }else{
            $data['goto_game']='2';//订单为h5链接订单
        }
        $consumption = round($user->consumption/100);
        $this->load->model('Game_order_model');
        if($appId=='6' && $consumption<300 && ($data['channel']=='test001' || $data['channel']=='allu' || strpos($data['channel'],'WXMP') !== false)){
            $user_agent = $this->check_user_agent();
            if($user_agent!='ios'){
//                $query_game_id = $game->copy_game_id?$game->copy_game_id:$appId;
                $sql = 'SELECT SUM(money) FROM game_order WHERE user_id='.$openId.' AND game_id='.$appId.' AND platform='.'"'.$game->platform.'"'.' AND status=2 AND game_father_id='.$game->game_father_id.' AND cproleid='.$cproleid;
                $res = $this->db->query($sql)->row_array();
                $num = $res['SUM(money)'];
                //更新消费总额
                $this->load->model('User_model');
                $this->User_model->update(array('consumption'=>$num,'channel'=>$data['channel']),array('user_id'=>$openId,'platform'=>'wxminigame'));
            }
        }
        $id = $this->Game_order_model->add($data);
        if ($id) {
            $data = array('order_id' => $u_order_id);
            $data['current_role_level'] = $current_role_level;
//             $condition = array('user_id' => $openId);
//             $user = $this->User_model->get_one_by_condition($condition);
            if ($user) {
                $data['userId'] = $user->p_uid;
            }
            if($current_role_level<='32'){
                $data['is_pay'] = '1';
            }
            $this->Output_model->json_print(0, 'ok', $data);
        } else {
            $this->Output_model->json_print(1, 'error');
        }
    }

    public function notify($platform = false)
    {
        if (!$platform) {
            echo '?????';

            return;
        }
        if($this->input->get('errMsg')){
            log_message('debug', "mipay_error:" . $this->input->get('errMsg') . "mipay_errcode:" . $this->input->get('errCode'));
            $mipay_res = $this->input->get('errMsg').','.$this->input->get('errCode');
            return $mipay_res;
        }
        $platform_model = $platform . '_model';
        if ($this->load->model('platform/' . $platform_model)) {
            $order_id = $this->$platform_model->get_order_id();
            if (!$order_id) {
                $this->$platform_model->notify_error();

                return;
            }
        } else {
            $this->$platform_model->notify_error();

            return;
        }

        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');

        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if ($game_order) {

            // set the status to payed
            if ($game_order->status == $this->Game_order_model->START_STATUS) {
                $where = array('u_order_id' => $order_id);
                $data = array('status' => $this->Game_order_model->PAYED_STATUS);

                $this->Game_order_model->update($data, $where);

                $this->load->model('Common_model');
                $res = $this->Common_model->notify($order_id);
                if ($res) {
                    $this->$platform_model->notify_ok();
                }

                return;
            } elseif ($game_order->status == $this->Game_order_model->PAYED_STATUS) {
                $this->load->model('Common_model');
                $res = $this->Common_model->notify($order_id);
                if ($res) {
                    $this->$platform_model->notify_ok();
                }

                return;
            } else {
                $this->$platform_model->notify_ok();

                return;
            }
        }

        $this->$platform_model->notify_error();
    }
    public function sign_order($platform = false, $game_id = '')
    {
        //迁移游戏专用 用于转换game_id;
        $this->load->model('Game_model');
        $game = $this->Game_model->get_one_by_condition(array('game_id'=>$game_id));
        if ($game->pay_game_id && $this->input->get('goodsName')){
            $game_id = $game->pay_game_id;
            $platform = 'Wxh5game';
        }
        $platform_model = $platform . '_model';
        if ($this->load->model('platform/' . $platform_model)) {
            $sign = $this->$platform_model->sign_order($game_id);
            if ($sign) {
                $this->Output_model->json_print(0, 'ok', $sign);
            } else {
                $this->Output_model->json_print(2, 'sign error');
            }
        } else {
            $this->Output_model->json_print(1, 'no p');

            return;
        }
    }

    public function init($platform = false, $game_id = '')
    {
        $platform_model = $platform . '_model';

        if ($this->load->model('platform/' . $platform_model)) {
            $init_data = $this->$platform_model->init($game_id);
            if ($init_data) {
                $this->Output_model->json_print(0, 'ok', $init_data);
            } else {
                $this->Output_model->json_print(2, 'init error');
            }
        } else {
            $this->Output_model->json_print(1, 'no p');

            return;
        }
    }

    public function focus($platform = false, $game_id = '')
    {
        $platform_model = $platform . '_model';
        //迁移游戏专用 用于转换game_id;
        $this->load->model('Game_model');
        $game = $this->Game_model->get_one_by_condition(array('game_id'=>$game_id));
        if ($game->copy_game_id){
            $game_id = $game->copy_game_id;
        }
        if ($this->load->model('platform/' . $platform_model)) {
            $res = $this->$platform_model->focus($game_id);
            if ($res) {
                $this->Output_model->json_print(0, 'ok', $res);

            } else {
                $this->Output_model->json_print(2, 'focus error');
            }
        } else {
            $this->Output_model->json_print(1, 'no p');

            return;
        }
    }

    public function iqiyi_report()
    {
        $type = $this->input->get('type');
        if ($type == 1) {
            $this->create_role_report('iqiyi');
        } elseif ($type == 2) {
            $this->login_report('iqiyi');
        }
    }

    public function create_role_report($platform = false)
    {
        $platform_model = $platform . '_model';
        if ($this->load->model('platform/' . $platform_model)) {
            $res = $this->$platform_model->create_role_report();
        }
    }

    public function login_report($platform = false)
    {
        $platform_model = $platform . '_model';
        if ($this->load->model('platform/' . $platform_model)) {
            $res = $this->$platform_model->login_report();
        }
    }

    public function sign_report($platform = false)
    {
        $platform_model = $platform . '_model';
        if ($this->load->model('platform/' . $platform_model)) {
            $res = $this->$platform_model->sign_report();
        }
    }

    public function create_role($platform = false, $game_id = '')
    {
        $roleid = $this->input->get('roleid');
        $srvid = $this->input->get('srvid');
//         if ($srvid >= 1000) {
//             $this->Output_model->json_print(1, 'srv too big');
//             exit();
//         }
        // if($srvid >= 1000){
        //     exit();
        // }
        $nickname = $this->input->get('nickname');
        $cproleid = $this->input->get('cproleid');
        if (!$roleid || !$srvid || !$platform || !$nickname) {
            return;
        }
        $condition = array(
            'user_id' => $roleid,
        );
        $user = $this->User_model->get_one_by_condition($condition);
        if ($user) {
            //迁移游戏专用 用于转换game_id;
            $this->load->model('Game_model');
            $game = $this->Game_model->get_one_by_condition(array('game_id'=>$game_id));
            if ($game->copy_game_id){
                $game_id = $game->copy_game_id;
            }

            $game = $this->Game_model->get_by_game_id($game_id);
            $this->load->model('Create_role_report_model');
            $data = array(
                'platform' => $platform,
                'user_id' => $roleid,
                'p_uid' => $user->p_uid,
                'server_id' => $srvid,
                'nickname' => $nickname,
                'game_id' => $game_id,
                'cproleid' => $cproleid,
                'game_father_id' => $game->game_father_id,
                'create_date' => time(),
                'channel'=>$user->channel?$user->channel:'allu',
            );
            if($game_id=='46'){
                log_message('debug', 'tianxingdao create_role_get:  roleid:'.$roleid.'  srvid:'.$srvid.' nickname:'.$nickname.'  cproleid:'.$cproleid);
                log_message('debug', 'tianxingdao create_role_data:  '.json_encode($data));
            }
            $this->Create_role_report_model->add($data);

            //redis数据上报
            //  $this->load->driver('cache', array('adapter' => 'redis'));
            // if ($this->cache->redis->is_supported()) {
            //     $today = date("Y-m-d", time());
            //     $redis_data = array(
            //          $game_id=>'1',
            //      );
            //     if (!$this->cache->redis->get('CreateRole_count_'.$today)) {
            //         $this->cache->redis->save('CreateRole_count_'.$today, $redis_data, 60*60*24);
            //     } else {
            //         $createRole_count = $this->cache->redis->get('CreateRole_count_'.$today);
            //         if (!isset($createRole_count[$game_id])) {
            //             $createRole_count[$game_id]=1;
            //         } else {
            //             $count = $createRole_count[$game_id];
            //             $createRole_count[$game_id]=$count+1;
            //         }
            //         $count = $createRole_count[$game_id];
            //         $this->cache->redis->save('CreateRole_count_'.$today, $createRole_count, 60*60*24);
            //     }
            // }
            //redis数据上报done

            $platform_model = $platform . '_model';
            if ($this->load->model('platform/' . $platform_model)) {
                $res = $this->$platform_model->create_role_collect($data);
                $this->Output_model->json_print(0, 'ok');
            }
        }
    }

    public function login($platform = false, $game_id = '')
    {
        $roleid = $this->input->get('roleid');
        $srvid = $this->input->get('srvid');
        $level = $this->input->get('level');
        $nickname = $this->input->get('nickname');
        $power = $this->input->get('power');
        $currency = $this->input->get('currency');
        $cproleid = $this->input->get('cproleid');
//         if ($srvid >= 1000) {
//             $this->Output_model->json_print(1, 'srv too big');
//             exit();
//         }

        if (!$roleid || !$srvid || $level=="" || !$platform || $nickname=="") {
            
            log_message('debug', "junhai error:  roleid:".$roleid  ."  servid:".$srvid . "    level:".$level."   platform:".$platform."    nickname:".$nickname );
            echo '参数不全';
            return;
        }
        // if($srvid >= 1000){
        //     exit();
        // }
        
        $condition = array(
            'user_id' => $roleid,
        );
        $user = $this->User_model->get_one_by_condition($condition);
        // echo json_encode($user);
        if ($user) {
            //迁移游戏专用 用于转换game_id;
            $this->load->model('Game_model');
            $game = $this->Game_model->get_one_by_condition(array('game_id'=>$game_id));
            if ($game->copy_game_id){
                $game_id = $game->copy_game_id;
            }
            $game = $this->Game_model->get_by_game_id($game_id);


            $this->load->model('Login_report_model');
            $data = array(
                'platform' => $platform,
                'user_id' => $roleid,
                'p_uid' => $user->p_uid,
                'server_id' => $srvid,
                'nickname' => $nickname,
                'level' => $level,
                'game_id' => $game_id,
                'game_father_id' => $game->game_father_id,
                'power' => $power,
                'currency' => $currency,
                'cproleid' => $cproleid,
                'create_date' => time(),
                'channel' => $user->channel?$user->channel:'allu',
            );

            if ($game->pay_game_id){
                $user_webType = $this->is_weixin();

                if ($user_webType=='2'){
                    $data['login_type']='1';//登录接口为h5链接
                }
            }else{
                $data['login_type']='2';//登录接口为小游戏
            }

             $this->Login_report_model->add($data);
             //自动更新角色名字，防止改了角色名未及时记录。
//             $this->load->model('Create_role_report_model');
//             $this->Create_role_report_model->update(
//                 array('nickname' => $nickname),
//                 array(
//                     'p_uid' => $user->p_uid,
//                     'server_id'=>$srvid,
//                     'game_id'=>$game_id,
//                     'game_father_id'=>$game->game_father_id,
//                     'user_id'=>$roleid,
//                     'platform'=>$platform,
//                 )
//             );
            //redis数据上报
            // $this->load->driver('cache', array('adapter' => 'redis'));
            // if ($this->cache->redis->is_supported()) {
            //     $today = date("Y-m-d", time());
            //     $redis_data = array(
            //          $game_id=>'1',
            //      );
            //     if (!$this->cache->redis->get('Login_count_'.$today)) {
            //         $this->cache->redis->save('Login_count_'.$today, $redis_data, 60*60*24);
            //     } else {
            //         $loginCount = $this->cache->redis->get('Login_count_'.$today);
            //         if (!isset($loginCount[$game_id])) {
            //             $loginCount[$game_id]=1;
            //         } else {
            //             $count = $loginCount[$game_id];
            //             $loginCount[$game_id]=$count+1;
            //         }
            //         $count = $loginCount[$game_id];
            //         $this->cache->redis->save('Login_count_'.$today, $loginCount, 60*60*24);
            //     }
            // }
            //redis数据上报done

            $platform_model = $platform . '_model';
            if ($this->load->model('platform/' . $platform_model)) {
                $res = $this->$platform_model->login_collect($data);
                $this->Output_model->json_print(0, 'ok');
            }
        } else {
            $this->Output_model->json_print(1, '用户未找到');
        }
    }

    public function sign_collect($platform = false, $game_id = '')
    {
        $roleid = $this->input->get('roleid');
        $srvid = $this->input->get('srvid');
        $this->load->model('Sign_report_model');
        if (!$roleid || !$srvid || !$platform) {
            return;
        }
        if ($srvid >= 1000) {
            $this->Output_model->json_print(1, 'srv too big');
            exit();
        }
        $check_exist = array(
            'platform' => $platform,
            'user_id' => $roleid,
        );
        $requery = $this->Sign_report_model->get_one_by_condition($check_exist);
        if ($requery) {
            $this->Output_model->json_print(1, 'exist');
            exit();
        };
        $condition = array(
            'user_id' => $roleid,
        );
        $user = $this->User_model->get_one_by_condition($condition);
        if ($user) {
            //迁移游戏专用 用于转换game_id;
            $this->load->model('Game_model');
            $game = $this->Game_model->get_one_by_condition(array('game_id'=>$game_id));
            if ($game->copy_game_id){
                $game_id = $game->copy_game_id;
            }

            $game = $this->Game_model->get_by_game_id($game_id);
            $data = array(
                'platform' => $platform,
                'user_id' => $roleid,
                'p_uid' => $user->p_uid,
                'server_id' => $srvid,
                'game_id' => $game_id,
                'game_father_id' => $game->game_father_id,
                'create_date' => time(),
                'channel'=>$user->channel?$user->channel:'allu',
            );

            $this->Sign_report_model->add($data);
            $platform_model = $platform . '_model';

            //redis数据上报
            //  $this->load->driver('cache', array('adapter' => 'redis'));
            // if ($this->cache->redis->is_supported()) {
            //     $today = date("Y-m-d", time());
            //     $redis_data = array(
            //          $game_id=>'1',
            //      );
            //     if (!$this->cache->redis->get('SignCreate_count_'.$today)) {
            //         $this->cache->redis->save('SignCreate_count_'.$today, $redis_data, 60*60*24);
            //     } else {
            //         $loginCount = $this->cache->redis->get('SignCreate_count_'.$today);
            //         if (!isset($loginCount[$game_id])) {
            //             $loginCount[$game_id]=1;
            //         } else {
            //             $count = $loginCount[$game_id];
            //             $loginCount[$game_id]=$count+1;
            //         }
            //         $count = $loginCount[$game_id];
            //         $this->cache->redis->save('SignCreate_count_'.$today, $loginCount, 60*60*24);
            //     }
            // }
            //redis数据上报done

            // if ($this->load->model('platform/'.$platform_model)) {
            //     $res = $this->$platform_model->sign_collect($data);
            // }
            $this->Output_model->json_print(0, 'ok');
        }
    }

    public function server_name($platform = false)
    {
        $platform_model = $platform . '_model';
        if ($this->load->model('platform/' . $platform_model)) {
            $res = $this->$platform_model->server_name();
        }
    }

    public function post_unionid(){
        $json_arr = json_decode($GLOBALS["HTTP_RAW_POST_DATA"],true);
        $platform = $json_arr['platform'];
        $platform_model = $platform . '_model';
        log_message('debug', 'get_platform:' .json_encode($json_arr));

        if ($this->load->model('platform/' . $platform_model)) {
            $res = $this->$platform_model->post_unionid($json_arr);
        }
    }

    public function order_query($platform = false)
    {
        $platform_model = $platform . '_model';
        if ($this->load->model('platform/' . $platform_model)) {
            $res = $this->$platform_model->order_query();
        }
    }
    //将渠道每天登陆人数放入数据库
    // public function addLoginCount()
    // {
    //     $this->load->driver('CI_Redis');
    //     $this->load->model('Game_model');
    //     $this->load->model('Login_count_model');
    //     $gameList = $this->Game_model->get_by_condition();
    //     $platform_info = array();
    //     $today_unix = time()-60*60*24;
    //     $today = date("Y-m-d", time()-60*60*24);
    //     // $today = date("Y-m-d", time());
    //     foreach ($gameList as $one) {
    //         $gameid = $one->game_id;
    //         $times = $this->redis->mget($gameid.' '.$today);
    //         if ($times['0']) {
    //             $platform_info[$gameid]=str_replace("\"", "", $times['0']);
    //             $login_count = str_replace("\"", "", $times['0']);
    //             $condition = array(
    //                 'game_id' =>$gameid,
    //             );
    //             $game = $this->Game_model->get_one_by_condition($condition);
    //             $game = json_encode($game);
    //             $game = json_decode($game);
    //             $data = array(
    //                 'login_date' => $today_unix,
    //                 'game_id' => $gameid,
    //                 'login_count' => $login_count,
    //                 'platform' => $game->platform,
    //                 'game_father_id' => $game->game_father_id,
    //             );
    //             echo $this->Login_count_model->add($data);
    //         }
    //     }
    // }
    
    //登录验证码接口
    public function sms_code($PhoneNumbers){
        if(empty($PhoneNumbers))exit($this->Output_model->json_print(1, '号码为空'));
        $params = array ();
        
        $security = false;
      
        $accessKeyId = "LTAIuBZ7AZO05DVw";
        $accessKeySecret = "Ss3mwpkRBRApjuTEokEU05uzsBxszC";
        
        $params["PhoneNumbers"] = $PhoneNumbers;
        
        $params["SignName"] = "白泽游戏";
       
        $params["TemplateCode"] = "SMS_171565299";
        
        $this->load->driver('cache', array('adapter' => 'redis'));
        if(!$this->cache->redis->is_supported())exit($this->Output_model->json_print(1, 'open redis error'));
        
        if($this->cache->redis->get('phone_code_'.md5($PhoneNumbers)))exit($this->Output_model->json_print(1, '验证已发送'));
        
        $this->cache->redis->save('phone_code_'.md5($PhoneNumbers), rand(100000, 999999), 60);
        
        $params['TemplateParam'] = Array (
            "name" => '玩家',
            "code" => '验证码为：'.$this->cache->redis->get('phone_code_'.md5($PhoneNumbers)).'，您正在登录',
            );
         if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        
        $this->load->library('SignatureHelper','', 'SignatureHelper');

        $content = $this->SignatureHelper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            )),
            $security
            );
        log_message('debug', "sms request ".json_encode($content));
        if($content->Code!='OK'){
            $this->Output_model->json_print(1, '请稍后再试');
        }else{
            $this->Output_model->json_print(0, '发送成功');
        }
    }
    
    public function check_sms_code($PhoneNumbers,$code){
        $this->load->driver('cache', array('adapter' => 'redis'));
        if(!$this->cache->redis->is_supported())exit($this->Output_model->json_print(1, '请稍后再试'));
        if($this->cache->redis->get('phone_code_'.md5($PhoneNumbers))==$code){
            $this->load->model('Allu_user_model');
            $condition = array(
                'account' => $PhoneNumbers
            );
            $check = $this->Allu_user_model->get_one_by_condition($condition);
            if (!$check) {
                $condition['password'] = rand(100000, 999999);
                $condition['create_date'] = time();
                $response = $this->Allu_user_model->add($condition);
                if ($response) {
                    $this->Output_model->json_print(0, array('account'=>$PhoneNumbers,'password'=>$condition['password']));
                } else {
                    $this->Output_model->json_print(2, 'add error');
                }
            } else {
                $this->Output_model->json_print(0, array('account'=>$check->account,'password'=>$check->password));
            }
        }else{
            $this->Output_model->json_print(1, '验证码错误');
        }
        
    }
    //补全订单信息，采集用户信息
    public function complete_order_data(){
        if($_GET['_orderid']){
            $condition = array('u_order_id' => $_GET['_orderid']);
            $this->load->model('Game_order_model');
            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if($game_order){
                
                if($_GET['type']=='track'){
                    $data=array('track'=>array(
                        '_deviceid'=>$_GET['_deviceid'],
                        '_appkey'=>$_GET['_appkey'],
                        '_androidid'=>$_GET['_androidid'],
                        '_ip'=>$_GET['_ip']
                        )
                    );
                }
//                 if($_GET['type']=='baidu'){
//                     $data=array('baidu'=>array(
//                         '_deviceid'=>$_GET['_deviceid'],
//                         '_appkey'=>$_GET['_appkey']
//                     )
//                     );
//                 }
                $old_data=@json_decode($game_order->other_report,true);
                if($old_data){
                    $result=array_merge($old_data,$data);
                }else {
                    $result=$data;
                }
                
//                 $this->Output_model->json_print(0, $result);exit;
                if($result){
                    $where = array('order_id' => $game_order->order_id);
                    $data = array('other_report' => json_encode($result));
                    if ($this->Game_order_model->update($data, $where)){
                        $this->Output_model->json_print(0, 'ok');
                    }
                }
            }
        }
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
        return $data;
    }
    //判断用户是否为微信浏览器
    public function is_weixin()
    {
        $user_agent = $_SERVER['HTTP_REFERER'];
//         log_message('debug', 'mini ua1 : ' . json_encode($_SERVER));
        if (strpos($user_agent, 'baizegame') !== false) {
            // 非微信浏览器
            return '2';
        } else {
            // 微信浏览器
            return '1';
        }
    }

    //sdk短信通知接口
    public function SDK_sms_code(){
        $DATA = file_get_contents("php://input");
        log_message('debug', "sms request1 ".$DATA);

        $DATA = json_decode($DATA,true);
        //短信接口被通知人的电话
        $phoneArr = array(
            '13242737713',//张舒
            '15622114490',//邓伟洲
            '15920877195',//王钦民
            '15625591214',//刘思哲
            '18575612288',//王野
            '18520113276',//汪也
            '13450389416',//陈铭
            '13422177322',//福来
        );
        $PhoneNumbers = implode(',',$phoneArr);
//        $PhoneNumbers = '15625591214';
        $params = array ();

        $security = false;

        $accessKeyId = "LTAIuBZ7AZO05DVw";
        $accessKeySecret = "Ss3mwpkRBRApjuTEokEU05uzsBxszC";

        $params["PhoneNumbers"] = $PhoneNumbers;

        $params["SignName"] = "白泽游戏";

        $params["TemplateCode"] = "SMS_182681327";

        $params['TemplateParam'] = Array (
            "msg" => $DATA['content'],
        );

        $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);

        $this->load->library('SignatureHelper','', 'SignatureHelper');

        $content = $this->SignatureHelper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            )),
            $security
        );
        log_message('debug', "sms request ".json_encode($content));
    }

    public function test_min(){
        $DATA = file_get_contents("php://input");

        log_message('debug', "test request ".$DATA);
    }

}
