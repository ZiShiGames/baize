<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Out_api extends CI_Controller
{
    public function qisiqiqi_check_user()
    {
        $uid = $this->input->get('uid');
        $appid = $this->input->get('appid');
        $serverid = $this->input->get('serverid');
        $platform = $this->input->get('platform');
        $time = $this->input->get('time');
        $sign = $this->input->get('sign');
        $key = "509dbff544823b6f063cda4f1922d4";

        $mysign = md5("$uid$appid$serverid$platform$time$key");

        if ($mysign == $sign) {
            $this->load->model("User_model");
            $condition = array(
                'p_uid' => $uid,
            );
            if ($this->User_model->get_one_by_condition($condition)) {
                echo "1";
                exit;
            } else {
                echo "-1";
                exit;
            }
        } else {
            echo "-2";
            exit;
        }

        // md5(uid+appid+serverid+platform+time+key)
        //
    }

    public function ybbw_api()
    {
        $openid = $this->input->get('openid');
        $skey = $this->input->get('skey');
        $time = $this->input->get('time');
        $sign = $this->input->get('sign');
        $this->load->model('Login_report_model');
        $this->load->model('User_model');
        $condition = array(
            'p_uid' => $openid,
        );
        $user = $this->User_model->get_by_condition($condition)[0];
        // echo json_encode($user);
        if ($user) {
            $condition = array(
                'user_id' => $user->user_id,
                'platform' => 'ybbw',
                'server_id' => $skey,
            );
            // $this->load->library('console_log');

            // $user_info = $this->Login_report_model->get_by_condition($condition);
            // $this->console_log->log($user_info);
            $this->db->order_by('create_date', "desc");
            $user_info = $this->Login_report_model->get_by_condition($condition)[0];
            // $this->console_log->log($user_info);
            if ($user_info) {
                $data = array(
                    "roleid" => $user_info->cproleid,
                    "rolename" => $user_info->nickname,
                    "level" => $user_info->level,
                    "attack" => $user_info->power,
                    "vip" => 0,
                    "updated_at" => date("Y-m-d H:i:s", $user_info->create_date),

                );
                $this->load->model('Create_role_report_model');
                $create_info = $this->Create_role_report_model->get_by_condition($condition)[0];
                if ($create_info) {
                    $data['created_at'] = date("Y-m-d H:i:s", $create_info->create_date);
                } else {
                    $data['created_at'] = $data['updated_at'];
                }

                $response = array('errno' => 0, 'data' => $data);
                echo json_encode($response);
            } else {
                echo '{"errno" : 1,"data" : []}';
            }
        } else {
            echo '{"errno" : 1,"data" : []}';
        }
    }

    public function baqi($game_father_id = '')
    {
        $this->load->model('User_model');
        $uid = $this->input->get('uid');
        $sid = $this->input->get('sid');
        $key = $this->Game_model->get_key('1381', 'key');
        $this->load->model('Login_report_model');
        $userinfo_condition = array(
            'p_uid' => $uid,
            'server_id' => $sid,
        );
        if ($game_father_id) {
            $userinfo_condition['game_father_id'] = $game_father_id;
        }
        $this->db->order_by('create_date', 'desc');
        $user_info = $this->Login_report_model->get_one_by_condition($userinfo_condition);
        if ($user_info) {
            echo '{"data": {"level": "' . $user_info->level . '","CE":"' . $user_info->power . '","name": "' . $user_info->server_id . '服 ' . $user_info->nickname . '","server": "' . $user_info->server_id . '"},"status": 1}';
        } else {
            echo '无数据';
        }
    }

    public function baqi_cm($game_father_id = '')
    {
        $this->load->model('User_model');
        $uid = $this->input->get('uid');
        $sid = $this->input->get('sid');
        $key = $this->Game_model->get_key('1429', 'key');
        $this->load->model('Login_report_model');
        $userinfo_condition = array(
            'p_uid' => $uid,
            'server_id' => $sid,
        );
        if ($game_father_id) {
            $userinfo_condition['game_father_id'] = $game_father_id;
        }
        $this->db->order_by('create_date', 'desc');
        $user_info = $this->Login_report_model->get_one_by_condition($userinfo_condition);
        if ($user_info) {
            echo '{"data": {"level": "' . $user_info->level . '","CE":"' . $user_info->power . '","name": "' . $user_info->server_id . '服 ' . $user_info->nickname . '","server": "' . $user_info->server_id . '"},"status": 1}';
        } else {
            echo '无数据';
        }
    }

    public function lh_game_server_api()
    {
        $this->load->model('Login_report_model');
        $condition = array(
            'game_father_id' => 20006,
            'server_id <= ' => 500,
        );

        $this->db->select(' distinct(`server_id`) ');

        $request = $this->Login_report_model->get_by_condition($condition);
        if($request){
            $max_server = $request[array_search(max($request),$request)]->server_id;
            $ser_arr = array();
            $temp = array();
            for ($i = 1; $i <= $max_server; $i++) {
                $temp[$i]['serv_id'] = $i;
                $temp[$i]['serv_name'] = $i;
            }
            $request_data = array(
                "error_code" => 0 ,
                "serv" => $temp,
            );
            echo json_encode($request_data);
        }else{
            echo '{"error_code":1, "desc": "data null"}';
        }
    }

    public function lh_get_user_info(){
        $serv_id = $this->input->get('serv_id');;
        $usr_name = $this->input->get('usr_name');;

        $condition = array(
            'server_id' => $serv_id,
            'nickname' => $usr_name,
            'game_id' => 1411
        );
        $this->load->model('Create_role_report_model');
        $request = $this->Create_role_report_model->get_one_by_condition($condition);
        if($request){
            $request_data = array(
                "error_code" => 0 ,
                "usr_name" => $usr_name,
                "player_id" => $request->user_id
            );
            echo json_encode($request_data);
        }else{
            echo '{"error_code":1, "desc": "data null"}';
        }

    }

    public function lh_create_order($appId){
        $openId = $this->input->get('player_id');
        // $appId = $this->input->get('appId');
        $money = $this->input->get('money')*100;
        $orderNo = $this->input->get('orderNo');
        $ext = $this->input->get('ext');
        $pfid = $this->input->get('pfid');
        $data = $this->input->get('data');
        $goodsName = $this->input->get('goodsName');
        $platform = $this->input->get('platform');
        $cproleid = $this->input->get('cproleid');

        if (!$openId || !$appId || !$money || !$goodsName) {
            echo '参数不全';

            return;
        }
        // if ($pfid) {
        //     $ext .= '_'.$pfid;
        // }

        $game = $this->Game_model->get_by_game_id($appId);
        if (!$game) {
            return;
        }

        $u_order_id = rand(1, 1000).'_'.$openId.'_'.time().'_'.rand(1, 1000);

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
            'game_father_id'=>$game->game_father_id,
            'cproleid'=>$cproleid,
        );
    }
    public function panda_check_nickname(){
        // rid=28318249&server_id=S1&sign=90852a3c4819dbc28b20a02d300b2c9d&time=1322551365&gkey=snxlj" //[]中的接口名称，由游戏开发方决定，并告知我们
        $rid = $this->input->get('rid');
        $server_id = $this->input->get('server_id');
        $gkey = $this->input->get('gkey');
        $time = $this->input->get('time');
        $sign = $this->input->get('sign');
        $login_key = "qLmJZjRqgREFUcIhHX0Fml4BWs5m539e";
        $mysign = md5("$gkey$rid$server_id$time$login_key");
        if($sign != $mysign){
            return false;
        };
        $this->load->model('User_model');
        $condition = array(
            'p_uid' => $rid,
            'server_id'=>$server_id,

        );
        $this->load->model('Create_role_report_model');
        $requery = $this->Create_role_report_model->get_one_by_condition($condition);
        if($requery){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function get_data_by_mounth(){
        $date = $this->input->get('start');
        $url = "https://2460.lcby.xileyougame.com/index.php/Admin_backstage_report_api/get_data_by_mounth?start=$date&to=undefined&game_father_id=20000&platform=undefined";
        echo $this->Curl_model->curl_get($url);

    }
    public function get_data(){
        $date = $this->input->get('start');
        $to = $this->input->get('to');
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');
        $url = "http://backstage.allugame.com/index.php/Admin_backstage_report_api/get_data?start=$date&to=$to&game_father_id=$game_father_id&platform=$platform";
        echo $this->Curl_model->curl_get($url);
    }
}
