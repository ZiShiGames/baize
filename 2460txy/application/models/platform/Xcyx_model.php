<?php

class Xcyx_model extends CI_Model
{
    public $platform = 'xcyx';
    public $key = '';
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $sdkappid = $this->input->get('sdkappid');//游戏名（发行方提供）
        $this->cache->save('sdkappid', $sdkappid);
        $useraccount = $this->input->get('useraccount');//帐号
        $time = $this->input->get('ts');//当前时间戳
        $pf = $this->input->get('pf');//渠道标识
        $sign = $this->input->get('sign');
        if (!$sdkappid || !$useraccount || !$time || !$pf) {
            return false;
        }

        //签名判断
        $data1 = array(
            'sdkappid' => $sdkappid,
            'useraccount' => $useraccount,
            'ts' => $time,
            'pf' => $pf,
        );
        $this->load->model('Common_model');
        $p_str = $this->Common_model->sort_params($data1);
        $pay_key= $this->Game_model->get_key($game_id, 'key');
        $my_sign = md5($p_str.$pay_key);
        if ($my_sign != $sign) {
            return false;
        }
        $url = 'http://www.xcvgame.cn/api/userinfo?sdkappid='.$sdkappid.'&useraccount='.$useraccount.'&ts='.$time.'&pf='.$pf.'&sign='.$sign;
        $content = $this->Curl_model->curl_get($url);
        if (!$content) {
            log_message('error', "One Login empty content $url");

            return false;
        }

        $response = json_decode($content, true);//ret: 等于0时候取data数据，大于0表示有错误，错误信息在msg
        if ($response['ret'] != 0) {
            log_message('error', $this->platform.' response '.$response['msg']);
            return false;
        }
        log_message('debug', $this->platform.' login debug '.$response['data']['useraccount'].' || '.$sdkappid.' || '.$useraccount.' || '.$time.' || '.$pf.' || '.$sign);
        $condition = array(
            'p_uid' => $response['data']['useraccount'],
            'platform' => $this->platform,
        );
        $this->cache->save('useraccount', $response['data']['useraccount']);
        $shareNumber = $response['data']['sharenumber'];
        $this->cache->save('shareNumber', $shareNumber);
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $response['data']['useraccount'],
                'nickname' => $response['data']['nickname'],
                'avatar' => $response['data']['headimgurl'],
                'create_date' => time(),
            );

            $user_id = $this->User_model->add($user);
            if (!$user_id) {
                log_message('error', "Login error user create $content");

                return false;
            }

            $user['user_id'] = $user_id;
        }

        $tempdata = array(
            'pf' => $pf,
            'useraccount' => $useraccount,
        );
        $this->cache->save('pf', $pf);
        $this->session->set_tempdata($tempdata, null, 3600 * 24 * 30);

        $this->cache->save($user['user_id'].'_token', md5($user['user_id'].$user['platform'].time()));

        return $user['user_id'];
    }

    public function focus($game_id)
    {
        $pf = $this->cache->get('pf');
        $user = $this->cache->get('useraccount');
        $appid = $this->cache->get('sdkappid');
        $shareNumber = $this->cache->get('shareNumber');
        $data = array(
                    'pf'=>$pf,
                    'useraccount'=>$user,
                    'sdkappid'=>$appid,
                    '$shareNumber'=>$shareNumber,
            );
        return $data;
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
        $openKey = $this->cache->get($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $server_name = $this->input->get('server_name');
        $pfid = $this->input->get('pfid');
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;

        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId&server_name=$server_name";
        log_message('debug', $this->platform." login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get('attach');
        $money = $this->input->get('money');

        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            //check sign
            $orderid = $this->input->get('orderid');
            $itemname = $this->input->get('itemname');
            $pf = $this->input->get('pf');
            $useraccount = $this->input->get('useraccount');
            $attach = $this->input->get('attach');
            $ts = $this->input->get('ts');
            $debug = $this->input->get('debug');
            $sign = $this->input->get('sign');
            $game_id = $game_order->game_id;
            $pay_key= $this->Game_model->get_key($game_id, 'key');
            $sign_array = array(
                'orderid'=>$orderid,
                'itemname'=>$itemname,
                'money'=>$money,
                'pf'=>$pf,
                'useraccount'=>$useraccount,
                'attach'=>$attach,
                'ts'=>$ts,
                'debug'=>$debug,
            );
            $this->load->model('Common_model');

            $p_str = $this->Common_model->sort_params($sign_array);
            $my_sign = md5($p_str.$pay_key);
            if ($my_sign==$sign) {
                // $this->load->model('Cp_game_order_model');
                // $check_payback = $this->Cp_game_order_model->get_one_by_condition($condition);
                // if (!$check_payback) {
                //     $order_back_info=array(
                //         'game_id'=>$game_order->game_id,
                //         'money'=> intval($money*100),
                //         'game_father_id'=>$game_order->game_father_id,
                //         'create_Date'=>time(),
                //         'cp_user_id'=>$useraccount,
                //         'u_order_id'=>$order_id,
                //         'cp_order_id'=>$orderid,
                //         'user_id'=>$game_order->user_id,
                //         'platform'=>$game_order->platform,
                //         'server_id'=>$game_order->ext,
                //     );
                //     if ($game_order->money!=intval($money*100)) {
                //         return;
                //     }
                    // $response = $this->Cp_game_order_model->add($order_back_info);
                    // if (!$response) {
                    //     log_message('debug', $this->platform.' pay back check is failed');
                    // }
                // }
                return $order_id;
            } else {
                return false;
            }
            //check done
        }
    }

    public function notify_ok()
    {
        echo 'ok';
    }

    public function notify_error()
    {
        echo 'failed';
    }

    public function sign_order($game_id = '')
    {
        $pf = $this->input->get('pf');
        $useraccount=$this->input->get('useraccount');
        $sdkappid=$this->input->get('sdkappid');
        $money = $this->input->get('money');
        $itemname=$this->input->get('itemname');
        $attach=$this->input->get('attach');
        $nickname = $this->input->get('nickname');
        $srv = $this->input->get('srv');
        $data = array(
            'sdkappid' => $sdkappid,
            'pf' => $pf,
            'useraccount' => $useraccount,
            'money' => $money,
            'itemname' => $itemname,
            'attach' => $attach,
            'serviceid' => $srv,
            'role' => $nickname,
        );
        if (isset($sdkappid)&&$sdkappid=='lcby') {
            $game_id='1018';
        } elseif (isset($sdkappid)&&$sdkappid=='jjsg') {
            $game_id='1032';
        }
        $pay_key = $this->Game_model->get_key($game_id, 'key');
        $this->load->model('Common_model');
        $p_str = $this->Common_model->sort_params($data);

        $sign = md5($p_str.$pay_key);
        $data = array(
            'sign' => $sign,
            'sdkappid' => $sdkappid,
            'useraccount' => $useraccount,
            'pf' => $pf,
            'nickname' => $nickname

        );

        return $data;
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
                $userList[$one->p_uid.''] = $user;
            }
            $data['userList'] = $userList;
            $response['data'] = $data;
        } else {
            $response = array(
                'success' => true ,
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
                $userList[$one->p_uid.''] = $user;
            }
            $data['userList'] = $userList;
            $response['data'] = $data;
        } else {
            $response = array(
                'success' => true ,
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

        $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/api/?m=player&fn=getserverlist&openId='.$openId;
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

        $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/9g/api/?m=player&fn=getserverlist&openId='.$openId;
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
}
