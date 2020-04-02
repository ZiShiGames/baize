<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_report_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $role = $this->session->userdata('role')->admin_user_role;
        // if (!$role) {
        //     $this->Output_model->json_print(-1, 'no role ');
        //     exit;
        // } else {
        //     if ($role != 'admin'&&$role!='customerService') {
        //         $this->Output_model->json_print(-1, 'session error');
        //         exit;
        //     }
        // }
    }

    //获取分服数据
    public function get_server_info()
    {
        $select_date = $this->input->get('start');
        ($select_date)?'':$this->Output_model->json_print(1, '参数不足');
        $end_date = $this->input->get('end');
        $create_role_start_time = $this->str_to_zero_time($select_date);
        ($end_date)?$create_role_end_time = $this->str_to_zero_time($end_date):$create_role_end_time=$create_role_start_time+86400;

        $game_father_id = $this->input->get('game_father_id');
        $platform = $this->input->get('platform');


        $this->load->model('Create_role_report_model');
        $this->load->model('login_report_model');
        $this->load->model('Game_order_model');

        $days = ($create_role_end_time - $create_role_start_time)/86400;
        $this->db->select('max(`ext`) as maxserver');
        $_max_server = $this->Game_order_model->get_one_by_condition(array('game_father_id=' => $game_father_id));
        $max_server=$_max_server->maxserver;
        if($max_server>= 1000){
            $max_server = 1000;
        }
        $server_begin = 1;
        if($game_father_id=='20014'){
            $server_begin='100001';
        }
        $one_day_info = array();

        // echo $days;

        for ($_days=0 ; $_days<$days;$_days++) {
            $_create_role_start_time = $create_role_start_time+(86400*$_days);
            $_create_role_end_time =$_create_role_start_time+86400;

            $condition=array(
                'create_date >= '=>$_create_role_start_time,
                'create_date <= '=>$_create_role_end_time,
            );
            ($game_father_id)?$condition['game_father_id']=$game_father_id:'';
            ($platform)?$condition['platform']=$platform:'';
            $game_order_condition = $game_login_createrole_condition = $condition;
            // $game_login_createrole_condition = $condition;

            for ($one = $server_begin ; $one<=$max_server;$one++) {
                $this->db->select('COUNT(`order_id`) as cishu , COUNT(DISTINCT(`user_id`)) as renshu , SUM(`money`) as money ,ext');
                $game_order_condition['ext']=$one;
                $game_order_condition['status']=2;
                $game_order_request = $this->Game_order_model->get_one_by_condition($game_order_condition);

                $game_login_createrole_condition['server_id']=$one;
                $this->db->select('COUNT(DISTINCT(`cproleid`)) as zhuce');
                $create_role_request = $this->Create_role_report_model->get_one_by_condition($game_login_createrole_condition);

                $this->db->select('COUNT(DISTINCT(`cproleid`)) as denglu');
                $login_role_request = $this->login_report_model->get_one_by_condition($game_login_createrole_condition);



                $this->db->select(' sum(`money`) as money ,count(DISTINCT(`game_order`.`user_id`)) as xinfufeirenshu');
                $_join_on_str = "sign_report.user_id = game_order.user_id  and game_order.status = 2 and game_order.ext = '$one' and game_order.create_date >= '$_create_role_start_time' and game_order.create_date <= '$_create_role_end_time' ";
                //将 signreport userid 去重
                ($game_father_id)?$_join_on_str .= "and game_order.game_father_id = '$game_father_id' ":'';
                ($platform)?$_join_on_str.=" and game_order.platform = '$platform' ":'';

                $_join_table_str = "( select DISTINCT(`user_id`) as `user_id` from sign_report where sign_report.create_date >= '$_create_role_start_time' and sign_report.create_date <= '$_create_role_end_time'  ";
                ($game_father_id)?$_join_table_str .= "and game_father_id = '$game_father_id' ":'';
                ($platform)?$_join_table_str.=" and platform = '$platform' ":'';
                $_join_table_str.=") as sign_report";

                $this->db->join($_join_table_str, $_join_on_str, "INNER");
                $new_user_pay_sum = $this->Game_order_model->get_one_by_condition("");


                // and `game_order`.`create_date` >= '1529510400' and `game_order`.`create_date` <= '1529596800'


                $this->db->select('COUNT(DISTINCT(`cproleid`)) as xinchuangjue ');
                $__join_on_str = "sign_report.user_id = create_role_report.user_id   and create_role_report.server_id = '$one' and create_role_report.create_date >= '$_create_role_start_time' and create_role_report.create_date <= '$_create_role_end_time'";
                ($game_father_id)? $__join_on_str.= "and create_role_report.game_father_id = '$game_father_id'":'';
                ($platform)?$__join_on_str.=" and create_role_report.platform = '$platform'  ":'';

                $__join_table_str = "( select DISTINCT(`user_id`) as `user_id` from sign_report where create_date >= '$_create_role_start_time' and create_date <= '$_create_role_end_time'  ";
                ($game_father_id)?$__join_table_str .= "and game_father_id = '$game_father_id' ":'';
                ($platform)?$__join_table_str.=" and platform = '$platform' ":'';
                $__join_table_str.=") as sign_report";
                $this->db->join($__join_table_str, $__join_on_str, 'INNER');
                $new_user_create_role_sum = $this->Create_role_report_model->get_one_by_condition("");

                $server_info = array(
                    'date'=>date('Y-m-d', $_create_role_start_time),
                    'server_id'=>$one,
                    'cishu'=>$game_order_request->cishu,
                    'renshu'=>$game_order_request->renshu,
                    'money'=>$game_order_request->money,
                    'zhuce'=>$create_role_request->zhuce,
                    'denglu'=>$login_role_request->denglu,
                    'new_pay_user_sum' => $new_user_pay_sum->xinfufeirenshu,
                    'new_user_sum_money'=>$new_user_pay_sum->money,
                    'new_user_sum_create_role'=>$new_user_create_role_sum->xinchuangjue,
                );
                ($game_order_request->cishu)?$server_info['cishu']=$game_order_request->cishu:$server_info['cishu']=0;
                ($game_order_request->renshu)?$server_info['renshu']=$game_order_request->renshu:$server_info['renshu']=0;
                ($game_order_request->money)?$server_info['money']=$game_order_request->money:$server_info['money']=0;
                ($create_role_request->zhuce)?$server_info['zhuce']=$create_role_request->zhuce:$server_info['zhuce']=0;
                ($login_role_request->denglu)?$server_info['denglu']=$login_role_request->denglu:$server_info['denglu']=0;
                ($new_user_pay_sum->xinfufeirenshu)?$server_info['new_pay_user_sum']=$new_user_pay_sum->xinfufeirenshu:$server_info['new_pay_user_sum']=0;
                ($new_user_pay_sum->money)?$server_info['new_user_sum_money']=$new_user_pay_sum->money:$server_info['new_user_sum_money']=0;
                ($new_user_create_role_sum->xinchuangjue)?$server_info['new_user_sum_create_role']=$new_user_create_role_sum->xinchuangjue:$server_info['new_user_sum_create_role']=0;
                array_push($one_day_info, $server_info);
                // echo json_encode($server_info);
                // echo '<br>';
            }
        }
        $this->Output_model->json_print(0, 'ok', array_reverse($one_day_info));
    }

    //获取ltv数据
    public function get_ltv_info()
    {
        $select_date = $this->input->get('start');
        ($select_date)?'':$this->Output_model->json_print(1, '参数不足');
        $end_date = $this->input->get('end');
        $create_role_start_time = $this->str_to_zero_time($select_date);
        ($end_date)?$create_role_end_time = $this->str_to_zero_time($end_date):$create_role_end_time=$create_role_start_time+86400;
        $days = ($create_role_end_time - $create_role_start_time)/86400;
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');
        $server_id = $this->input->get('server_id');
        $all_data = array();
        $this->load->model('Create_role_report_model');
        $this->load->model('Sign_report_model');
        for ($_days = 0 ; $_days<$days;$_days++) {
            $_create_role_start_time = $create_role_start_time+(86400*$_days);
            $create_role_end_time =$_create_role_start_time+86400;
            $liucun_data = array();
            for ($i = 0 ; $i < 90; $i ++) {
                $next_day_start = $create_role_end_time+(86400*$i);
                $next_day_end=$create_role_end_time+(86400*$i);
                if ($next_day_end >= time()+86400) {
                    $liucun_data[$i+1]= 0;
                    continue;
                };
                $this->db->select('sum(`money`) as money');
                $this->db->from('create_role_report');
                //拼接sql
                $_join_str = "`game_order`.`status` ='2' and `create_role_report`.`create_date` >='$_create_role_start_time' and `create_role_report`.`create_date` <= '$create_role_end_time' and `create_role_report`.`cproleid` = `game_order`.`cproleid` and  `create_role_report`.`cproleid` != 'undefined' and `game_order`.`create_date` >='$_create_role_start_time' and `game_order`.`create_date` <= '$next_day_end'";
                if ($game_father_id) {
                    $_join_str.=" and `game_order`.`game_father_id` = '$game_father_id' ";
                }
                if ($platform) {
                    $_join_str.=" and `game_order`.`platform` = '$platform' ";
                }
                if ($server_id) {
                    $_join_str.=" and `game_order`.`ext` = '$server_id' ";
                }
                $this->db->join('game_order', $_join_str, 'inner');
                $request = $this->db->get();
                $liucun = ($request->result()[0]);
                if ($next_day_end>strtotime(date('Y-m-d', time()))+86400) {
                    $liucun_data[$i+1]= 0;
                } else {
                    $liucun_data[$i+1]= $liucun->money/100;
                }
            };
            //获取注册数和创角数
            $_response = $this->get_sign_in_and_create_role_info($_create_role_start_time, $create_role_end_time, $game_father_id, $platform, $server_id);
            $liucun_data['sign'] = $_response['sign_in'];
            $liucun_data['create_role'] = $_response['create_role'];
            //填写时间
            $_output_time = $this->str_to_zero_time($select_date)+(86400*$_days);
            $liucun_data['date']= date('Y-m-d', $this->str_to_zero_time($select_date)+(86400*$_days));
            $all_data[$_days+1]=$liucun_data;
        }
        if ($all_data) {
            $this->Output_model->json_print(0, 'ok', array_reverse($all_data));
        } else {
            $this->Output_model->json_print(1, '没有数据,请检查查询参数');
        }
    }

    //获取注册数和创角数
    private function get_sign_in_and_create_role_info($start_time, $end_time, $game_father_id=null, $platform=null, $server_id=null)
    {
        $condition = array();
        $this->load->model('Create_role_report_model');
        $this->load->model('Sign_report_model');
        $condition['create_date >= ']=$start_time;
        $condition['create_date <= ']=$end_time;
        $game_father_id?$condition['game_father_id'] = $game_father_id:'';
        $platform?$condition['platform'] = $platform:'';
        $server_id?$condition['server_id'] = $server_id:'';
        $this->db->select('count(DISTINCT(`user_id`)) as sign');
        $sign_in =$this->Sign_report_model->get_one_by_condition($condition)->sign;
        $this->db->select('count(DISTINCT(`cproleid`)) as create_role');
        $create_role=$this->Create_role_report_model->get_one_by_condition($condition)->create_role;
        $response = array(
            'sign_in'=>$sign_in,
            'create_role'=>$create_role,
        );
        return $response;
    }

    //获取留存数据
    public function get_exist_info()
    {
        $select_date = $this->input->get('start');
        ($select_date)?'':$this->Output_model->json_print(1, '参数不足');
        $end_date = $this->input->get('end');
        $create_role_start_time = $this->str_to_zero_time($select_date);
        ($end_date)?$create_role_end_time = $this->str_to_zero_time($end_date):$create_role_end_time=$create_role_start_time+86400;
        $days = ($create_role_end_time - $create_role_start_time)/86400;
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');
        $server_id = $this->input->get('server_id');
        $all_data = array();
        $this->load->model('Create_role_report_model');
        $this->load->model('Sign_report_model');
        for ($_days = 0 ; $_days<$days;$_days++) {
            $_create_role_start_time = $create_role_start_time+(86400*$_days);
            $create_role_end_time =$_create_role_start_time+86400;
            $liucun_data = array();
            for ($i = 0 ; $i < 90; $i ++) {
                $this->db->select('count(DISTINCT(login_report.cproleid)) as liucun');
                $this->db->from('create_role_report');
                $next_day_start = $create_role_end_time+(86400*$i);
                $next_day_end=$create_role_end_time+86400+(86400*$i);
                //拼接sql
                $_join_str = "`create_role_report`.`create_date` >='$_create_role_start_time' and `create_role_report`.`create_date` <= '$create_role_end_time' and `create_role_report`.`cproleid` = `login_report`.`cproleid` and  `create_role_report`.`cproleid` != 'undefined' and `login_report`.`create_date` >='$next_day_start' and `login_report`.`create_date` <= '$next_day_end'";
                if ($game_father_id) {
                    $_join_str.=" and `create_role_report`.`game_father_id` = '$game_father_id' ";
                }
                if ($platform) {
                    $_join_str.=" and `create_role_report`.`platform` = '$platform' ";
                }
                if ($server_id) {
                    $_join_str.=" and `create_role_report`.`server_id` = '$server_id' ";
                }
                $this->db->join('login_report', $_join_str, 'inner');
                $request = $this->db->get();
                $liucun = ($request->result()[0]);
                $liucun_data[$i+1]= $liucun->liucun;
            };
            //获取注册数和创角数
            $_response = $this->get_sign_in_and_create_role_info($_create_role_start_time, $create_role_end_time, $game_father_id, $platform, $server_id);
            $liucun_data['sign'] = $_response['sign_in'];
            $liucun_data['create_role'] = $_response['create_role'];
            //填写时间
            $_output_time = $this->str_to_zero_time($select_date)+(86400*$_days);
            $liucun_data['date']= date('Y-m-d', $this->str_to_zero_time($select_date)+(86400*$_days));
            $all_data[$_days+1]=$liucun_data;
        }
        if ($all_data) {
            $this->Output_model->json_print(0, 'ok', array_reverse($all_data));
        } else {
            $this->Output_model->json_print(1, '没有数据,请检查查询参数');
        }
    }
    //获取数据 充值 登录 注册 ap
    public function check_info()
    {
        $begin_time = $this->input->get('start');
        $end_time = $this->input->get('to');
        $game_father_id = $this->input->get('game_father_id');
        $platform = $this->input->get('platform');
        $this->load->model('db/Stage_hour_data_model');
        if (!$game_father_id) {
            $this->Output_model->json_print(-1, 'game father id is null');
            exit();
        }
        $begin_time = $this->str_to_zero_time($begin_time);
        if (!$end_time) {
            $end_time = $begin_time+60*60*24;
        } else {
            $end_time = $this->str_to_zero_time($end_time)+60*60*24;
        }
        $condition = array(
            'create_date >=' =>$begin_time,
            'create_date <' =>$end_time,
            'game_father_id' =>$game_father_id,
        );
        if ($platform) {
            $condition['platform'] = $platform;
        }
        $response = $this->Stage_hour_data_model->get_by_condition($condition);
        // echo json_encode($response);
        if ($response) {
            $data = array(
                'info'=>$response,
            );
            $this->Output_model->json_print(0, 'ok', $data);
        } else {
            $this->Output_model->json_print(1, 'data is  null');
        }
    }
    //每小时插入静态数据
    public function every_hour_date($begin_hour, $end_hour)
    {
        $date=$this->input->get('data');
        ($date)?$date:$date = date('Y-m-d', time());

        $_begin_hour = strtotime($date." $begin_hour:0:0");
        $_end_hour = strtotime($date." $end_hour:0:0");
        if ($_begin_hour==$_end_hour) {
            $_end_hour += 86400;
        }
        $this->load->model('Game_order_model');
        $this->load->model('Create_role_report_model');
        $this->load->model('Login_report_model');
        $this->load->model('db/Stage_hour_data_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('db/Platform_model');
        $this->load->model('Sign_report_model');
        $games = $this->Game_model->get_by_condition();
        $game_fathers = $this->Game_father_model->get_by_condition();

        foreach ($games as $one) {
            $hour_insert_date = array(
                'platform' => '0',
                'platform_name' => '0',
                'cishu' => '0',
                'renshu' => '0',
                'login' => '0',
                'createrole' => '0',
                'sign_report' => '0',
                'fufeilv' => '0',
                'arpu'=>'0',
                'arpuu'=>'0',
                'create_date' => '0',
                'money' => '0',
                'begin_time' => '0',
                'end_time' => '0',
                'game_father_id'=>'0',
            );
            $platform_condition = array(
                'platform'=>$one->platform,
            );
            $_req = $this->Platform_model->get_one_by_condition($platform_condition);
            $hour_insert_date['platform']=$one->platform;
            $hour_insert_date['platform_name']=$_req->platform_chinese;
            $hour_insert_date['game_father_id']=$one->game_father_id;
            $hour_insert_date['begin_time'] = $date." $begin_hour:0:0";
            $hour_insert_date['end_time'] = $date." $end_hour:0:0";
            $hour_insert_date['create_date']=$_begin_hour;
            $login_info_condition = array(
                    'create_date > ' => $_begin_hour,
                    'create_date <= ' => $_end_hour,
                    'platform '=>$one->platform,
                    'game_father_id'=>$one->game_father_id
                );
            //统计登录
            $this->db->select('count(DISTINCT(user_id)) as login_count ,  platform ,game_father_id');
            $login_reqery = $this->Login_report_model->get_one_by_condition($login_info_condition);
            if ($login_reqery->login_count) {
                $hour_insert_date['login']=$login_reqery->login_count;
            }
            //统计创角
            $this->db->select('count(DISTINCT(user_id)) as create_role_count ,  platform ,game_father_id');
            $create_role_requery = $this->Create_role_report_model->get_one_by_condition($login_info_condition);
            if ($create_role_requery->create_role_count) {
                $hour_insert_date['createrole']=$create_role_requery->create_role_count;
            }
            //统计新增
            $this->db->select('count((user_id)) as sign_report ,  platform ,game_father_id');
            $sign_role_requery = $this->Sign_report_model->get_one_by_condition($login_info_condition);
            if ($sign_role_requery->sign_report) {
                $hour_insert_date['sign_report']=$sign_role_requery->sign_report;
            }
            $order_info_condition = array(
                    'create_date >= ' => $_begin_hour,
                    'create_date <= ' => $_end_hour,
                    'platform '=>$one->platform,
                    'game_father_id'=>$one->game_father_id,
                    'status'=>2
                );
            $this->db->select('platform,count(DISTINCT(`user_id`)) as renshu ,sum(`money`) as money ,count(`order_id`) as cishu ');
            $game_order_requery = $this->Game_order_model->get_one_by_condition($order_info_condition);
            if ($game_order_requery->cishu &&$game_order_requery->renshu &&$game_order_requery->money) {
                $hour_insert_date['cishu']=$game_order_requery->cishu;
                $hour_insert_date['renshu']=$game_order_requery->renshu;
                $hour_insert_date['money']=$game_order_requery->money;
            }
            if ($hour_insert_date['login']&&$hour_insert_date['renshu']&&$hour_insert_date['money']) {
                $hour_insert_date['fufeilv'] = number_format($hour_insert_date['renshu']/$hour_insert_date['login'], 2);
                $hour_insert_date['arpu'] = number_format($hour_insert_date['money']/$hour_insert_date['login']/100, 2);
                $hour_insert_date['arpuu'] = number_format($hour_insert_date['money']/$hour_insert_date['renshu']/100, 2);
            }


            //check info
            $check_info_condition = array(
                'platform'=>$hour_insert_date['platform'],
                'begin_time'=>$hour_insert_date['begin_time'],
                'game_father_id'=>$hour_insert_date['game_father_id'],
            );

            $check_requery = $this->Stage_hour_data_model->get_one_by_condition($check_info_condition);
            if ($check_requery) {
                $response = $this->Stage_hour_data_model->update($hour_insert_date, $check_info_condition);
                // echo 'update '.$response;
            } else {
                $response = $this->Stage_hour_data_model->add($hour_insert_date);
                // echo 'add '.$response;
            }

            // echo $login_reqery->login_count.'   '.$login_reqery->platform.' '.$login_reqery->game_father_id;
            // $response = $this->Stage_hour_data_model->add($hour_insert_date);
            // if ($response) {
            //     echo $hour_insert_date['platform'].'  '.$hour_insert_date['game_father_id'].' ok';
            // } else {
            //     echo json_encode($hour_insert_date);
            // }
            // $this->outp
            // echo '<br>';
        }
        $this->Output_model->json_print(0, 'ok');
    }

    //获取前一天注册玩家openid  每天凌晨2点执行
    public function get_everyday_create_role_openId()
    {
        $end_time = strtotime(date('Y-m-d', time()))-86400;
        $begin_time = $end_time-86400;
        $this->load->model('Create_role_report_model');
        $this->load->model('Login_report_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('db/Platform_model');
        $games = $this->Game_model->get_by_condition();
        $game_fathers = $this->Game_father_model->get_by_condition();

        $data =array();
        foreach ($games as $one) {
            $condition = array(
                'platform' => $one->platform,
                'create_date >= '=>$begin_time,
                'create_date <= '=>$end_time,

            );
            $this->db->select('  DISTINCT(`user_id`) as uid , platform ');

            $requery = $this->Create_role_report_model->get_by_condition($condition);
            echo $this->db->last_query();
            echo '<br>';
            // array_push($data[$one->platform],$requery->uid);
            echo json_encode($requery);
            echo '<br>';
            echo json_encode($data);
            return;
        }
    }
    public function daily_income()
    {
        $date = $this->input->get('start');
        $to = $this->input->get('to');
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');

        if (!$date) {
            $this->Output_model->json_print(1, 'params not enough');

            return;
        }

        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');

            return;
        }

        if (!$to) {
            $next_date = $current_date + 60 * 60 * 24;
        } else {
            $next_date = $this->str_to_zero_time($to);
            if (!$next_date) {
                $this->Output_model->json_print(1, 'to format wrong');
                return;
            }
        }
        if ($current_date >= $next_date) {
            $this->Output_model->json_print(1, 'date must < to');
            return;
        }
        $condition = array(
                    'create_date >= ' => $current_date,
                    'create_date <= ' => $next_date,
                );
        $where_in = array();
        if ($game_father_id) {
            $father_condition = array(
                'game_father_id' => $game_father_id,
            );
            $all_ids = array();
            $games = $this->Game_model->get_by_condition($father_condition);
            foreach ($games as $game) {
                $all_ids[] = $game->game_id;
            }
            if (count($all_ids) > 0) {
                $where_in = array(
                    'name' => 'game_id',
                    'values' => $all_ids,
                );
            }
        }
        if ($platform) {
            $condition['platform']=$platform;
        }
        $this->load->model('Platform_data_model');
        $this->load->model('Game_order_model');
        //查询订单详情
        $select = 'game_id,user_id,money ';
        $order = $this->Game_order_model->get_order_info($select, $condition, null, null, null, null, null, $where_in);

        //分渠道查询信息
        $platform_info = $this->Platform_data_model->get_by_condition($condition, null, null, null, null, null, $where_in);

        $platform_array=array();
        if (!$platform_info||!$order) {
            $this->Output_model->json_print('-1', 'no data');
            return;
        }
        foreach ($platform_info as $one) {
            $platform_array[$one->create_date]=$one;
            $platform_array[$one->create_date]->cishu = 0;
            $platform_array[$one->create_date]->renshu = 0;
        }
        $tamp_array=array();
        $data = array(
            'info'=> $platform_info,
        );
        $this->Output_model->json_print(0, 'ok', $data);
        return;
    }
    public function daily_income_old()
    {
        $date = $this->input->get('start');
        $to = $this->input->get('to');
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');

        if (!$date) {
            $this->Output_model->json_print(1, 'params not enough');

            return;
        }

        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');

            return;
        }

        if (!$to) {
            $next_date = $current_date + 60 * 60 * 24;
        } else {
            $next_date = $this->str_to_zero_time($to);
            if (!$next_date) {
                $this->Output_model->json_print(1, 'to format wrong');
                return;
            }
        }
        if ($current_date >= $next_date) {
            $this->Output_model->json_print(1, 'date must < to');
            return;
        }
        $condition = array(
                    'status' => 2,
                );
        if ($platform) {
            $condition['platform'] = $platform;
        }
        $where_in = array();
        if ($game_father_id) {
            $condition['game_father_id']=$game_father_id;
            $father_condition = array(
                'game_father_id' => $game_father_id,
            );
            $all_ids = array();
            $games = $this->Game_model->get_by_condition($father_condition);
            foreach ($games as $game) {
                $all_ids[] = $game->game_id;
            }
            if (count($all_ids) > 0) {
                $where_in = array(
                    'name' => 'game_id',
                    'values' => $all_ids,
                );
            }
        }
        $this->load->model('Create_role_report_model');
        $this->load->model('Game_order_model');

        $this->load->model('Sign_report_model');
        $this->load->model('Login_report_model');

        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $platforms = $this->Platform_model->get_by_condition();
        $game_faters = $this->Game_father_model->get_by_condition();
        $platform_info_by_day=array();
        //查询订单详情
        $done_time = $current_date;
        $index=1;
        do {
            $done_time +=86400;
            $select = 'platform,user_id,money ';
            $login_create_condition=array(
                'create_date >= '=>$current_date,
                'create_date <= '=>$done_time,
                'game_father_id'=>$game_father_id,
                'platform' => $platform,
            );
            $condition['create_date >= ']=$current_date;
            $condition['create_date <= ']=$done_time;
            $loginselect = 'count(DISTINCT(`user_id`)) as login ';
            $createroleselect = 'count(DISTINCT(`user_id`)) as createrole ';
            $this->Create_role_report_model->set_table='create_role_report';
            $create_role = $this->Create_role_report_model->get_createrole_info($createroleselect, $login_create_condition, null, null, null, null, null, null);
            $login = $this->Login_report_model->get_loginreport_info($loginselect, $login_create_condition, null, null, null, null, null, null);
            $condition['status']=2;
            $order = $this->Game_order_model->get_order_info($select, $condition, null, null, null, null, null, null);
            $total_orders_count = count($order);
            $total_user_count=0;
            $user_count=array();
            //统计充值次数数组声明
            $platform_order_count_array=array();
            //统计充值人数数组声明
            $platform_user_count_array=array();
            $tamp_array = array();
            if (!$order) {
                $platform_order_count_array[$platform]['cishu'] =0;
                $platform_order_count_array[$platform]['renshu'] =0;
                $platform_order_count_array[$platform]['total'] =0;
                $platform_order_count_array[$platform]['platform_name'] =$platform;
                $platform_order_count_array[$platform]['begin'] =date('Y-m-d', $current_date);
                $platform_order_count_array[$platform]['to'] =date('Y-m-d', $done_time);
                $platform_order_count_array[$platform]['login']=$login['0']->login;
                $platform_order_count_array[$platform]['createrole']=$create_role['0']->createrole;
                $platform_order_count_array[$platform]['arppu'] =0;
                $platform_order_count_array[$platform]['fufeilv'] =0;
                $platform_order_count_array[$platform]['arpu']=0;
            } else {
                foreach ($order as $one) {
                    //统计充值次数
                    if (!isset($platform_order_count_array[$one->platform])) {
                        $platform_order_count_array[$one->platform]['cishu'] = 1;
                    } else {
                        $platform_order_count_array[$one->platform]['cishu'] +=1;
                    }
                    //统计充值人数
                    if (!isset($tamp_array[$one->user_id])) {
                        $tamp_array[$one->user_id] = 1;
                        if (!isset($platform_order_count_array[$one->platform]['renshu'])) {
                            $platform_order_count_array[$one->platform]['renshu'] =1 ;
                        } else {
                            $platform_order_count_array[$one->platform]['renshu'] += 1;
                        }
                    }
                    //统计充值总额
                    if (!isset($platform_order_count_array[$one->platform]['total'])) {
                        $platform_order_count_array[$one->platform]['total']=0;
                        $platform_order_count_array[$one->platform]['total']=$one->money/100;
                    } else {
                        $platform_order_count_array[$one->platform]['total']+=$one->money/100;
                    }
                    //添加渠道名
                    if (!isset($platform_order_count_array[$one->platform]['platform_name'])) {
                        $platform_order_count_array[$one->platform]['platform_name']=$one->platform;
                    }
                    //添加开始时间
                    if (!isset($platform_order_count_array[$one->platform]['begin'])) {
                        $platform_order_count_array[$one->platform]['begin']=date('Y-m-d', $current_date);
                    }
                    //添加结束时间
                    if (!isset($platform_order_count_array[$one->platform]['to'])&&isset($done_time)) {
                        $platform_order_count_array[$one->platform]['to']=date('Y-m-d', $done_time);
                    }
                    $platform_order_count_array[$one->platform]['login']=$login['0']->login;
                    $platform_order_count_array[$one->platform]['createrole']=$create_role['0']->createrole;
                    if ($platform_order_count_array[$one->platform]['renshu']==0) {
                        $platform_order_count_array[$one->platform]['arppu']=0;
                    } else {
                        $platform_order_count_array[$one->platform]['arppu']=number_format($platform_order_count_array[$one->platform]['total']/$platform_order_count_array[$one->platform]['renshu'], 2);
                    }
                    if ($platform_order_count_array[$one->platform]['total']!=0&&$login['0']->login!=0) {
                        $platform_order_count_array[$one->platform]['arpu']=number_format($platform_order_count_array[$one->platform]['total']/$login['0']->login, 2);
                    } else {
                        $platform_order_count_array[$one->platform]['arpu']=0;
                    }
                    if ($login['0']->login==0) {
                        $platform_order_count_array[$one->platform]['fufeilv']=0;
                    } else {
                        $platform_order_count_array[$one->platform]['fufeilv']=number_format(($platform_order_count_array[$one->platform]['renshu']/$login['0']->login)*100, 2);
                    }
                }
            }
            if ($index==1) {
                $platform_info_by_day[0]=$platform_order_count_array[$platform];
            } else {
                $platform_info_by_day[0]['begin']='总计';
                $platform_info_by_day[0]['cishu']+=$platform_order_count_array[$platform]['cishu'];
                $platform_info_by_day[0]['total']+=$platform_order_count_array[$platform]['total'];
                $platform_info_by_day[0]['createrole']+=$platform_order_count_array[$platform]['createrole'];
                $platform_info_by_day[0]['to']+=date('Y-m-d', $next_date);
            }
            $platform_info_by_day[$index]=$platform_order_count_array[$platform];
            $current_date = $done_time;
            ++$index;
        } while ($done_time<=$next_date);
        $login_current_date = $this->str_to_zero_time($date);
        if ($to) {
            $login_next_date = $this->str_to_zero_time($to);
        } else {
            $login_next_date=$login_current_date+8660400;
        }

        $total_login_condition=array(
            'create_date >= '=>$login_current_date,
            'create_date <= '=>$login_next_date,
            'game_father_id'=>$game_father_id,
            'platform' => $platform,
        );
        $total_login_select=' count(DISTINCT(`user_id`)) as login ';
        $total_payedUser_select=' count(DISTINCT(`user_id`)) as renshu ';
        $platform_info_by_day[0]['login'] = $this->Login_report_model->get_loginreport_info($total_login_select, $total_login_condition, null, null, null, null, null, null)['0']->login;
        $total_login_condition['status']=2;
        $platform_info_by_day[0]['renshu']= $this->Game_order_model->get_order_info($total_payedUser_select, $total_login_condition, null, null, null, null, null, null)['0']->renshu;
        if ($platform_info_by_day[0]['login']==0) {
            $platform_info_by_day[0]['login']=1;
        }
        if ($platform_info_by_day[0]['login']==0) {
            $platform_info_by_day[0]['login']=1;
        }
        if ($platform_info_by_day[0]['total']!=0&&$platform_info_by_day[0]['login']!=0) {
            $platform_info_by_day[0]['arpu']=number_format($platform_info_by_day[0]['total']/$platform_info_by_day[0]['login'], 2);
        } else {
            $platform_info_by_day[0]['arpu']=0;
        }
        if ($platform_info_by_day[0]['renshu']==0) {
            $platform_info_by_day[0]['fufeilv']=0;
            $platform_info_by_day[0]['arppu']=0;
        } else {
            $platform_info_by_day[0]['fufeilv']=number_format(($platform_info_by_day[0]['renshu']/$platform_info_by_day[0]['login'])*100, 2);
            $platform_info_by_day[0]['arppu']=number_format($platform_info_by_day[0]['total']/$platform_info_by_day[0]['renshu'], 2);
        }
        //统计登录人数
        //条件
        $data=array(
            'info'=>$platform_info_by_day
        );
        $this->Output_model->json_print(0, 'ok', $data);
    }
    public function get_info()
    {
        $date = $this->input->get('start');
        $to = $this->input->get('to');
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');

        if (!$date) {
            $this->Output_model->json_print(1, 'params not enough');

            return;
        }

        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');

            return;
        }

        if (!$to) {
            $next_date = $current_date + 60 * 60 * 24;
        } else {
            $next_date = $this->str_to_zero_time($to);
            if (!$next_date) {
                $this->Output_model->json_print(1, 'to format wrong');
                return;
            }
        }
        if ($current_date >= $next_date) {
            $this->Output_model->json_print(1, 'date must < to');
            return;
        }
        $condition = array(
                    'status' => 2,
                );
        if ($platform) {
            $condition['platform'] = $platform;
        }
        $where_in = array();
        if ($game_father_id) {
            $condition['game_father_id']=$game_father_id;
            $father_condition = array(
                'game_father_id' => $game_father_id,
            );
            $all_ids = array();
            $games = $this->Game_model->get_by_condition($father_condition);
            foreach ($games as $game) {
                $all_ids[] = $game->game_id;
            }
            if (count($all_ids) > 0) {
                $where_in = array(
                    'name' => 'game_id',
                    'values' => $all_ids,
                );
            }
        }
        $this->load->model('Create_role_report_model');
        $this->load->model('Game_order_model');

        $this->load->model('Sign_report_model');
        $this->load->model('Login_report_model');

        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('Fake_model');
        $fake_condition = array(
            'platform' => $platform,
            'game_father_id' => $game_father_id,
        );
        $fake_select = ' scale ';
        $fake_response = $this->Fake_model->get_order_info($fake_select, $fake_condition, null, null, null, null, null, null);
        $scale = $fake_response[0]->scale/100;
        $platforms = $this->Platform_model->get_by_condition();
        $game_faters = $this->Game_father_model->get_by_condition();
        $platform_info_by_day=array();
        //查询订单详情
        $done_time = $current_date;
        $index=1;

        // if ($platform=='kemeng') {
        //     $data=array(
        //         'info'=>$platform_info_by_day
        //     );
        //     // $this->load->view('admin/info_tongji/income_tongji', $data);
        //     $this->Output_model->json_print(0, 'ok', $data);
        // } else {
            do {
                $done_time +=86400;
                $select = 'platform,user_id,money ';
                $login_create_condition=array(
                    'create_date >= '=>$current_date,
                    'create_date <= '=>$done_time,
                    'game_father_id'=>$game_father_id,
                    'platform' => $platform,
                );
                $condition['create_date >= ']=$current_date;
                $condition['create_date <= ']=$done_time;
                $loginselect = 'count(DISTINCT(`user_id`)) as login ';
                $createroleselect = 'count(DISTINCT(`user_id`)) as createrole ';
                $this->Create_role_report_model->set_table='create_role_report';
                $create_role = $this->Create_role_report_model->get_createrole_info($createroleselect, $login_create_condition, null, null, null, null, null, null);
                $login = $this->Login_report_model->get_loginreport_info($loginselect, $login_create_condition, null, null, null, null, null, null);
                $order = $this->Game_order_model->get_order_info($select, $condition, null, null, null, null, null, null);
                // echo json_encode($order);
                $total_orders_count = count($order);
                $total_user_count=0;
                $user_count=array();
                //统计充值次数数组声明
                $platform_order_count_array=array();
                //统计充值人数数组声明
                $platform_user_count_array=array();
                $tamp_array = array();
                if (!$order) {
                    // $this->Output_model->json_print(-1, 'no data');
                    // return;
                    $platform_order_count_array[$platform]['cishu'] =0;
                    $platform_order_count_array[$platform]['renshu'] =0;
                    $platform_order_count_array[$platform]['total'] =0;
                    $platform_order_count_array[$platform]['platform_name'] =$platform;
                    $platform_order_count_array[$platform]['begin'] =date('Y-m-d', $current_date);
                    $platform_order_count_array[$platform]['to'] =date('Y-m-d', $done_time);
                    $platform_order_count_array[$platform]['login']=$login['0']->login;
                    $platform_order_count_array[$platform]['createrole']=$create_role['0']->createrole;
                    $platform_order_count_array[$platform]['arppu'] =0;
                    $platform_order_count_array[$platform]['fufeilv'] =0;
                } else {
                    foreach ($order as $one) {
                        //统计充值次数
                        if (!isset($platform_order_count_array[$one->platform])) {
                            $platform_order_count_array[$one->platform]['cishu'] = 1;
                        } else {
                            $platform_order_count_array[$one->platform]['cishu'] +=1;
                        }

                        //统计充值人数
                        if (!isset($tamp_array[$one->user_id])) {
                            $tamp_array[$one->user_id] = 1;
                            if (!isset($platform_order_count_array[$one->platform]['renshu'])) {
                                $platform_order_count_array[$one->platform]['renshu'] =1 ;
                            } else {
                                $platform_order_count_array[$one->platform]['renshu'] += 1;
                            }
                        }

                        //统计充值总额
                        if (!isset($platform_order_count_array[$one->platform]['total'])) {
                            $platform_order_count_array[$one->platform]['total']=0;
                            $platform_order_count_array[$one->platform]['total']=number_format($one->money/100 * $scale, 0);
                        } else {
                            $platform_order_count_array[$one->platform]['total']+=number_format($one->money/100 * $scale, 0);
                        }
                        //添加渠道名
                        if (!isset($platform_order_count_array[$one->platform]['platform_name'])) {
                            $platform_order_count_array[$one->platform]['platform_name']=$one->platform;
                        }
                        //添加开始时间
                        if (!isset($platform_order_count_array[$one->platform]['begin'])) {
                            $platform_order_count_array[$one->platform]['begin']=date('Y-m-d', $current_date);
                        }
                        //添加结束时间
                        if (!isset($platform_order_count_array[$one->platform]['to'])&&isset($done_time)) {
                            $platform_order_count_array[$one->platform]['to']=date('Y-m-d', $done_time);
                        }
                        $platform_order_count_array[$one->platform]['login']=number_format($login['0']->login * $scale, 0);
                        $platform_order_count_array[$one->platform]['createrole']=number_format($create_role['0']->createrole * $scale, 0);
                        if ($platform_order_count_array[$one->platform]['renshu']==0) {
                            $platform_order_count_array[$one->platform]['renshu']=1;
                        }
                        $platform_order_count_array[$one->platform]['arppu']=number_format($platform_order_count_array[$one->platform]['total']/$platform_order_count_array[$one->platform]['renshu'], 2);
                        if ($login['0']->login==0) {
                            $login['0']->login=1;
                        }
                        $platform_order_count_array[$one->platform]['arpu']=number_format($platform_order_count_array[$one->platform]['total']/$login['0']->login, 2);
                        $platform_order_count_array[$one->platform]['fufeilv']=number_format(($platform_order_count_array[$one->platform]['renshu']/$login['0']->login)*100*$scale, 2);
                    }
                }

                $platform_order_count_array[$platform]['cishu']=number_format($platform_order_count_array[$platform]['cishu']*$scale, 0);
                $platform_order_count_array[$platform]['renshu']=number_format($platform_order_count_array[$platform]['renshu']*$scale, 0);
                if ($index==1) {
                    $platform_info_by_day[0]=$platform_order_count_array[$platform];
                    // echo $platform_info_by_day[0]['cishu'].' '.$platform_order_count_array[$one->platform]['cishu'].' ';
                } else {
                    // +=$platform_order_count_array[$one->platform]['login'];
                    $platform_info_by_day[0]['begin']='总计';
                    $platform_info_by_day[0]['cishu']+=$platform_order_count_array[$platform]['cishu'];
                    // echo $index.'<bt>';
                    // echo $platform_info_by_day[0]['cishu'].' '.$platform_order_count_array[$one->platform]['cishu'].' ';
                    $platform_info_by_day[0]['total']+=$platform_order_count_array[$platform]['total'];
                    $platform_info_by_day[0]['createrole']+=$platform_order_count_array[$platform]['createrole'] ;
                    $platform_info_by_day[0]['to']+=date('Y-m-d', $next_date);
                }
                $platform_info_by_day[$index]=$platform_order_count_array[$platform];
                $current_date = $done_time;
                ++$index;
            } while ($done_time<=$next_date);
        $login_current_date = $this->str_to_zero_time($date);
        $login_next_date = $this->str_to_zero_time($to);
        $total_login_condition=array(
                'create_date >= '=>$login_current_date,
                'create_date <= '=>$login_next_date,
                'game_father_id'=>$game_father_id,
                'platform' => $platform,
            );
        $total_login_select=' count(DISTINCT(`user_id`)) as login ';
        $total_payedUser_select=' count(DISTINCT(`user_id`)) as renshu ';
        $platform_info_by_day[0]['login'] = number_format($this->Login_report_model->get_loginreport_info($total_login_select, $total_login_condition, null, null, null, null, null, null)['0']->login * $scale, 0);
        $platform_info_by_day[0]['renshu']= number_format($this->Game_order_model->get_order_info($total_payedUser_select, $total_login_condition, null, null, null, null, null, null)['0']->renshu * $scale, 0);
        if ($platform_info_by_day[0]['renshu']==0) {
            $platform_info_by_day[0]['renshu']=1;
        }
        if ($platform_info_by_day[0]['login']==0) {
            $platform_info_by_day[0]['login']=1;
        }
        if ($platform_info_by_day[0]['login']==0) {
            $platform_info_by_day[0]['login']=1;
        }
        $platform_info_by_day[0]['arppu']=number_format(($platform_info_by_day[0]['total']/$platform_info_by_day[0]['renshu'])*$scale, 2) ;
        $platform_info_by_day[0]['arpu']=number_format(($platform_info_by_day[0]['total']/$platform_info_by_day[0]['login'])*$scale, 2) ;
        $platform_info_by_day[0]['fufeilv']=number_format(($platform_info_by_day[0]['renshu']/$platform_info_by_day[0]['login'])*100*$scale, 2);
        $data=array(
                'info'=>$platform_info_by_day
            );
        $this->Output_model->json_print(0, 'ok', $data);
    }
    public function get_data()
    {
        $this->load->model('Fake_data_model');
        $date = $this->input->get('start');
        $to = $this->input->get('to');
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');

        if (!$date) {
            $this->Output_model->json_print(1, 'params not enough');

            return;
        }

        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');

            return;
        }

        if (!$to) {
            $next_date = $current_date + 60 * 60 * 24;
        } else {
            $next_date = $this->str_to_zero_time($to);
            $nn =$next_date-2764800;
            if ($nn>=$current_date) {
                $this->Output_model->json_print(1, 'time too long');
                return;
            }
            if (!$next_date) {
                $this->Output_model->json_print(1, 'to format wrong');
                return;
            }
        }
        if (date('m', $current_date)!=date('m', $next_date)) {
            $this->Output_model->json_print(1, 'not same mounth');
            return;
        }
        if ($current_date >= $next_date) {
            $this->Output_model->json_print(1, 'date must < to');
            return;
        }
        $condition = array(
                    'status' => 2,
                );
        if ($platform) {
            $condition['platform'] = $platform;
        }
        $where_in = array();
        if ($game_father_id) {
            $condition['game_father_id']=$game_father_id;
            $father_condition = array(
                'game_father_id' => $game_father_id,
            );
            $all_ids = array();
            $games = $this->Game_model->get_by_condition($father_condition);
            foreach ($games as $game) {
                $all_ids[] = $game->game_id;
            }
            if (count($all_ids) > 0) {
                $where_in = array(
                    'name' => 'game_id',
                    'values' => $all_ids,
                );
            }
        }
        $this->load->model('Create_role_report_model');
        $this->load->model('Game_order_model');

        $this->load->model('Sign_report_model');
        $this->load->model('Login_report_model');

        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('Fake_model');
        $fake_condition = array(
            'platform' => $platform,
            // 'game_father_id' => $game_father_id,
        );
        $fake_select = ' scale ';
        if ($platform == 'kemeng' || $platform =='xcyx'||$platform=='jinb'||$platform=='fantastic'||$platform=='yuewan') {
            $fake_condition['mounth'] = date('m', $current_date);
            $fake_response = $this->Fake_model->get_order_info($fake_select, $fake_condition, null, null, null, null, null, null);
            // echo $this->db->last_query();
            if (!$fake_response) {
                $scale = 1;
            } else {
                $scale = $fake_response[0]->scale/100;
                // echo $scale;
            }
        } else {
            $scale = 1;
        }
        //
        $platforms = $this->Platform_model->get_by_condition();
        $game_faters = $this->Game_father_model->get_by_condition();
        $platform_info_by_day=array();
        //查询订单详情
        $done_time = $current_date;
        $index=1;
        do {
            $done_time +=86400;
            $login_create_condition=array(
                    'create_date >= '=>$current_date,
                    'create_date <= '=>$done_time,
                    // 'game_father_id'=>$game_father_id,
                    'platform_name' => $platform,
                );
            $fake_data_request = $this->Fake_data_model->get_by_condition($login_create_condition);
            //统计充值次数数组声明
            $platform_order_count_array=array();
            //统计充值人数数组声明
            // $platform_user_count_array=array();
            // $tamp_array = array();
            if (!$fake_data_request) {
                $platform_order_count_array[$platform]['cishu'] =0;
                $platform_order_count_array[$platform]['renshu'] =0;
                $platform_order_count_array[$platform]['total'] =0;
                $platform_order_count_array[$platform]['platform_name'] =$platform;
                $platform_order_count_array[$platform]['begin'] =date('Y-m-d', $current_date);
                $platform_order_count_array[$platform]['to'] =date('Y-m-d', $done_time);
                $platform_order_count_array[$platform]['login']=0;
                $platform_order_count_array[$platform]['createrole']=0;
                $platform_order_count_array[$platform]['arppu'] =0;
                $platform_order_count_array[$platform]['fufeilv'] =0;
            } else {
                //统计充值次数
                $platform_order_count_array[$platform]['cishu']=$fake_data_request[0]->cishu;
                //统计充值人数
                $platform_order_count_array[$platform]['renshu'] =$fake_data_request[0]->renshu* $scale;//111
                //统计充值总额
                $platform_order_count_array[$platform]['total']=$fake_data_request[0]->money * $scale;
                //添加渠道名
                if (!isset($platform_order_count_array[$platform]['platform_name'])) {
                    $platform_order_count_array[$platform]['platform_name']=$platform;
                }
                //添加开始时间
                if (!isset($platform_order_count_array[$platform]['begin'])) {
                    $platform_order_count_array[$platform]['begin']=date('Y-m-d', $current_date);
                }
                        //添加结束时间
                $platform_order_count_array[$platform]['login']=$fake_data_request['0']->login * $scale;
                $platform_order_count_array[$platform]['createrole']=$fake_data_request['0']->createrole * $scale;

                if ($fake_data_request['0']->login==0) {
                    $fake_data_request['0']->login=1;
                }
                if ($platform_order_count_array[$platform]['login']==0) {
                    $platform_order_count_array[$platform]['fufeilv']=($platform_order_count_array[$platform]['renshu']/1)*100;
                } else {
                    $platform_order_count_array[$platform]['fufeilv']=($platform_order_count_array[$platform]['renshu']/$platform_order_count_array[$platform]['login'])*100;
                }

                // }
            }

            $platform_order_count_array[$platform]['cishu']=$platform_order_count_array[$platform]['cishu']*$scale;
            $platform_order_count_array[$platform]['renshu']=$platform_order_count_array[$platform]['renshu']*$scale;
            $platform_info_by_day[$index]=$platform_order_count_array[$platform];
            $current_date = $done_time;
            ++$index;
        } while ($done_time<=$next_date);
        $login_current_date = $this->str_to_zero_time($date);
        $login_next_date = $this->str_to_zero_time($to);
        $total_login_condition=array(
                'create_date >= '=>$login_current_date,
                'create_date <= '=>$login_next_date,
                // 'game_father_id'=>$game_father_id,
                'platform_name' => $platform,
            );
        $data=array(
                'info'=>$platform_info_by_day
            );

        $this->Output_model->json_print(0, 'ok', $data);
    }
    public function get_data_by_month()
    {
        $platform_info_by_day=array();
        $date = $this->input->get('start');
        $game_father_id = $this->input->get('game_father_id');
        if (!$date) {
            $this->Output_model->json_print(1, 'params not enough');
            return;
        }
        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');
            return;
        }
        $total_money=0;
        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('platform_data_model');
        $this->load->model('Month_data_model');
        $platform = $this->Platform_model->get_by_condition();
        foreach ($platform as $one) {
            $platform = $one->platform;
            $condition = array(
                    'create_date'=>$current_date,
                    'platform_name'=>$platform,
                    'game_father_id'=>$game_father_id,
                );
            $platform_data = $this->Month_data_model->get_by_condition($condition)[0];
            // echo $this->db->last_query();
            $platform_chinese_name_condition=array(
                'platform'=>$platform,
            );
            $this->db->select('platform_chinese');
            $platform_chinese_name = $this->Platform_model->get_one_by_condition($platform_chinese_name_condition);
            if (isset($platform_chinese_name)&&$platform_data) {
                $platform_data->platform_chinese_name=$platform_chinese_name->platform_chinese;
            } elseif (!$platform_data) {
                continue;
            } else {
                $platform_data->platform_chinese_name=$platform;
            }
            array_push($platform_info_by_day, $platform_data);
            if (isset($platform_data->money)) {
                $total_money+=$platform_data->money;
            }
        }

        $data=array(
                'info'=>$platform_info_by_day,
                'total_money'=>$total_money,
            );
        $this->Output_model->json_print(0, 'ok', $data);
    }
    public function lcby_get_data()
    {
        $this->load->model('Fake_data_model');

        $date = $this->input->get('start');
        $to = $this->input->get('to');
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');

        if (!$date) {
            $this->Output_model->json_print(1, 'params not enough');

            return;
        }

        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');

            return;
        }

        if (!$to) {
            $next_date = $current_date + 60 * 60 * 24;
        } else {
            $next_date = $this->str_to_zero_time($to);
            $nn =$next_date-2764800;
            if ($nn>=$current_date) {
                $this->Output_model->json_print(1, 'time too long');
                return;
            }
            if (!$next_date) {
                $this->Output_model->json_print(1, 'to format wrong');
                return;
            }
        }
        if (date('m', $current_date)!=date('m', $next_date)) {
            $this->Output_model->json_print(1, 'not same mounth');
            return;
        }
        if ($current_date >= $next_date) {
            $this->Output_model->json_print(1, 'date must < to');
            return;
        }
        $condition = array(
                    'status' => 2,
                );
        if ($platform) {
            $condition['platform'] = $platform;
        }
        $where_in = array();
        if ($game_father_id) {
            $condition['game_father_id']=$game_father_id;
            $father_condition = array(
                'game_father_id' => $game_father_id,
            );
            $all_ids = array();
            $games = $this->Game_model->get_by_condition($father_condition);
            foreach ($games as $game) {
                $all_ids[] = $game->game_id;
            }
            if (count($all_ids) > 0) {
                $where_in = array(
                    'name' => 'game_id',
                    'values' => $all_ids,
                );
            }
        }
        $this->load->model('Create_role_report_model');
        $this->load->model('Game_order_model');

        $this->load->model('Sign_report_model');
        $this->load->model('Login_report_model');

        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('Fake_model');
        $fake_condition = array(
            'platform' => $platform,
        );
        $fake_select = ' scale ';
        $scale = 1;
        $platforms = $this->Platform_model->get_by_condition();
        $game_faters = $this->Game_father_model->get_by_condition();
        $platform_info_by_day=array();
        //查询订单详情
        $done_time = $current_date;
        $index=1;

        do {
            $done_time +=86400;
            $login_create_condition=array(
                    'create_date >= '=>$current_date,
                    'create_date <= '=>$done_time,
                    'platform_name' => $platform,
                );
            $fake_data_request = $this->Fake_data_model->get_by_condition($login_create_condition);
            //统计充值次数数组声明
            $platform_order_count_array=array();
            //统计充值人数数组声明
            if (!$fake_data_request) {
                $platform_order_count_array[$platform]['cishu'] =0;
                $platform_order_count_array[$platform]['renshu'] =0;
                $platform_order_count_array[$platform]['total'] =0;
                $platform_order_count_array[$platform]['platform_name'] =$platform;
                $platform_order_count_array[$platform]['begin'] =date('Y-m-d', $current_date);
                $platform_order_count_array[$platform]['to'] =date('Y-m-d', $done_time);
                $platform_order_count_array[$platform]['login']=0;
                $platform_order_count_array[$platform]['createrole']=0;
                $platform_order_count_array[$platform]['arppu'] =0;
                $platform_order_count_array[$platform]['fufeilv'] =0;
            } else {
                //统计充值次数
                $platform_order_count_array[$platform]['cishu']=$fake_data_request[0]->cishu;

                        //统计充值人数
                        $platform_order_count_array[$platform]['renshu'] =$fake_data_request[0]->renshu;

                        //统计充值总额
                        $platform_order_count_array[$platform]['total']=$fake_data_request[0]->money * $scale;
                        //添加渠道名
                        if (!isset($platform_order_count_array[$platform]['platform_name'])) {
                            $platform_order_count_array[$platform]['platform_name']=$platform;
                        }
                        //添加开始时间
                        if (!isset($platform_order_count_array[$platform]['begin'])) {
                            $platform_order_count_array[$platform]['begin']=date('Y-m-d', $current_date);
                        }
                        //添加结束时间
                        // if (!isset($platform_order_count_array[$one->platform]['to'])&&isset($done_time)) {
                        //     $platform_order_count_array[$one->platform]['to']=date('Y-m-d', $done_time);
                        // }
                $platform_order_count_array[$platform]['login']=$fake_data_request['0']->login * $scale;
                $platform_order_count_array[$platform]['createrole']=$fake_data_request['0']->createrole * $scale;

                if ($fake_data_request['0']->login==0) {
                    $fake_data_request['0']->login=1;
                }
                if ($platform_order_count_array[$platform]['login']==0) {
                    $platform_order_count_array[$platform]['fufeilv']=($platform_order_count_array[$platform]['renshu']/1)*100;
                } else {
                    $platform_order_count_array[$platform]['fufeilv']=($platform_order_count_array[$platform]['renshu']/$fake_data_request['0']->login)*100;
                }


                // }
            }

            $platform_order_count_array[$platform]['cishu']=$platform_order_count_array[$platform]['cishu']*$scale;
            $platform_order_count_array[$platform]['renshu']=$platform_order_count_array[$platform]['renshu']*$scale;
            $platform_info_by_day[$index]=$platform_order_count_array[$platform];
            $current_date = $done_time;
            ++$index;
        } while ($done_time<=$next_date);
        $login_current_date = $this->str_to_zero_time($date);
        $login_next_date = $this->str_to_zero_time($to);
        $total_login_condition=array(
                'create_date >= '=>$login_current_date,
                'create_date <= '=>$login_next_date,
                'platform_name' => $platform,
            );
        $data=array(
                'info'=>$platform_info_by_day
            );

            // $this->load->view('admin/info_tongji/income_tongji', $data);
            $this->Output_model->json_print(0, 'ok', $data);
            // $this->Output_model->json_print(0, 'ok', $platform_order_count_array);
        // }
    }
    public function lcby_get_data_by_mounth()
    {
        $platform_info_by_day=array();
        $date = $this->input->get('start');

        if (!$date) {
            $this->Output_model->json_print(1, 'params not enough');
            return;
        }
        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');
            return;
        }
        // echo $current_date;
        // return;
        $total_money=0;
        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('Fake_model');
        $this->load->model('platform_data_model');
        $this->load->model('Month_data_model');
        $platforms= $this->Platform_model->get_by_condition();
        foreach ($platforms as $one) {
            $platform = $one->platform;
            $fake_condition = array(
                'platform' => $platform,
            );
            $fake_select = ' scale ';
            // if ($platform == 'kemeng' || $platform =='xcyx'||$platform=='jinb'||$platform=='fantastic'||$platform=='yuewan') {
            //     $fake_condition['mounth'] = date('m', $current_date);
            //     $fake_response = $this->Fake_model->get_order_info($fake_select, $fake_condition, null, null, null, null, null, null);
            //     if (!$fake_response) {
            //         $scale=1;
            //     } else {
            //         $scale = $fake_response[0]->scale/100;
            //     }
            // } else {
            $scale=1;
            // }

            $condition = array(
                    'create_date'=>$current_date,
                    'platform_name'=>$platform,
                );
            // $this->db->group_by('platform');
            $platform_data = $this->Month_data_model->get_by_condition($condition)[0];
            // echo $this->db->last_query();
            // echo json_encode($platform_data);
            $platform_chinese_name_condition=array(
                'platform'=>$platform,
            );
            $this->db->select('platform_chinese');
            $platform_chinese_name = $this->Platform_model->get_one_by_condition($platform_chinese_name_condition);
            // echo $this->db->last_query().'<br>';
            // echo json_encode($platform_chinese_name->platform_chinese);
            if (isset($platform_chinese_name)&&$platform_data) {
                // $platform_data['platform_chinese_name']=$platform_chinese_name->platform_chinese;
                $platform_data->platform_chinese_name=$platform_chinese_name->platform_chinese;

                // echo $platform_chinese_name->platform_chinese.'<br>';
            } elseif (!$platform_data) {
                continue;
            } else {
                $platform_data->platform_chinese_name=$platform;
            }
            // echo json_encode($platform_chinese_name['platform_chinese']);
            // return;


            // if ($platform_data) {

            if ($platform=='jinb'||$platform=='kemeng'||$platform=='xcyx'||$platform=='fantastic'||$platform=='yuewan') {
                // echo $this->db->last_query().' <br>';
                // echo json_encode($platform_data);
                $platform_data->cishu=$platform_data->cishu*$scale;
                $platform_data->login=$platform_data->login*$scale;
                $platform_data->money=$platform_data->money*$scale;
                $platform_data->createrole=$platform_data->createrole*$scale;
            }


            array_push($platform_info_by_day, $platform_data);
            if (isset($platform_data->money)) {
                $total_money+=$platform_data->money;
            }
        }

        $data=array(
                'info'=>$platform_info_by_day,
                'total_money'=>$total_money,
            );
        $this->Output_model->json_print(0, 'ok', $data);
    }
    public function insert_info()
    {
        $nextDay=date('d', time());
        $toDay=$nextDay-1;
        $str_date = date('Y-m-', time());
        $toDay = $str_date.$toDay;
        $nextDay = $str_date.$nextDay;
        $toDay = $this->str_to_zero_time($toDay);
        $nextDay = $this->str_to_zero_time($nextDay);
        $condition = array(
                    'status' => 2,
                );
        $where_in = array();
        $game_father_id='20000';
        if ($game_father_id) {
            $condition['game_father_id']=$game_father_id;
            $father_condition = array(
                'game_father_id' => $game_father_id,
            );
            $all_ids = array();
            $games = $this->Game_model->get_by_condition($father_condition);
            foreach ($games as $game) {
                $all_ids[] = $game->game_id;
            }
            if (count($all_ids) > 0) {
                $where_in = array(
                    'name' => 'game_id',
                    'values' => $all_ids,
                );
            }
        }
        $this->load->model('Create_role_report_model');
        $this->load->model('Game_order_model');

        $this->load->model('Sign_report_model');
        $this->load->model('Login_report_model');

        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('Fake_model');
        $this->load->model('Fake_data_model');
        $fake_condition = array(
            'game_father_id' => $game_father_id,
        );
        $fake_select = ' scale ';
        $scale=1;
        $platforms = $this->Platform_model->get_by_condition();
        $game_faters = $this->Game_father_model->get_by_condition();
        $platform_info_by_day=array();
        //查询订单详情
        $select = 'platform,count(DISTINCT(`user_id`)) as renshu ,sum(`money`) as money ,count(`order_id`) as cishu ';
        $loginselect = 'count(DISTINCT(`user_id`)) as login ';
        $createroleselect = 'count(DISTINCT(`user_id`)) as createrole ';
        $index=1;
        foreach ($platforms as $one) {
            $platform = $one->platform;
            $login_create_condition=array(
                    'create_date >= '=>$toDay,
                    'create_date <= '=>$nextDay,
                    'game_father_id'=>$game_father_id,
                    'platform' => $platform,
                );
            $condition['create_date >= ']=$toDay;
            $condition['create_date <= ']=$nextDay;
            $condition['status ']=2;
            $condition['platform']=$platform;
            $create_role = $this->Create_role_report_model->get_createrole_info($createroleselect, $login_create_condition, null, null, null, null, null, null);
            // echo $this->db->last_query().'<br>';
            $login = $this->Login_report_model->get_loginreport_info($loginselect, $login_create_condition, null, null, null, null, null, null);
            $order = $this->Game_order_model->get_order_info($select, $condition, null, null, null, null, null, null);
            //统计充值次数数组声明
            $platform_order_count_array=array();
            //统计充值人数数组声明
            if (!$order) {
                $platform_order_count_array[$platform]['cishu'] =0;
                $platform_order_count_array[$platform]['renshu'] =0;
                $platform_order_count_array[$platform]['total'] =0;
                $platform_order_count_array[$platform]['platform_name'] =$platform;
                $platform_order_count_array[$platform]['begin'] =date('Y-m-d', $toDay);
                $platform_order_count_array[$platform]['to'] =date('Y-m-d', $done_time);
                $platform_order_count_array[$platform]['login']=$login['0']->login;
                $platform_order_count_array[$platform]['createrole']=$create_role['0']->createrole;
                $platform_order_count_array[$platform]['arppu'] =0;
                $platform_order_count_array[$platform]['fufeilv'] =0;
            } else {
                //统计充值次数
                $platform_order_count_array[$platform]['cishu']=$order[0]->cishu;
                // echo $platform_order_count_array[$platform]['cishu'].'<br>';
                //统计充值人数
                $platform_order_count_array[$platform]['renshu'] =$order[0]->renshu;
                //统计充值总额
                $platform_order_count_array[$platform]['total']=$order[0]->money/100 * $scale;
                //添加渠道名
                if (!isset($platform_order_count_array[$platform]['platform_name'])) {
                    $platform_order_count_array[$platform]['platform_name']=$platform;
                }
                //添加开始时间
                if (!isset($platform_order_count_array[$platform]['begin'])) {
                    $platform_order_count_array[$platform]['begin']=date('Y-m-d', $toDay);
                }
                //添加结束时间
                $platform_order_count_array[$platform]['login']=$login['0']->login * $scale;
                $platform_order_count_array[$platform]['createrole']=$create_role['0']->createrole * $scale;

                if ($login['0']->login==0) {
                    $login['0']->login=1;
                }
                $platform_order_count_array[$platform]['fufeilv']=($platform_order_count_array[$platform]['renshu']/$login['0']->login)*100;
            }

            $platform_order_count_array[$platform]['cishu']=$platform_order_count_array[$platform]['cishu']*$scale;
            $platform_order_count_array[$platform]['renshu']=$platform_order_count_array[$platform]['renshu']*$scale;
            $platform_info_by_day[$index]=$platform_order_count_array[$platform];
            $insert_info = array(
                'platform_name' =>$platform_info_by_day[$index]['platform_name'],
                'cishu' =>$platform_info_by_day[$index]['cishu'],
                'renshu' =>$platform_info_by_day[$index]['renshu'],
                'login' =>$platform_info_by_day[$index]['login'],
                'createrole' =>$platform_info_by_day[$index]['createrole'],
                'fufeilv' =>$platform_info_by_day[$index]['fufeilv'],
                'money' =>$platform_info_by_day[$index]['total'],
                'create_date' =>$this->str_to_zero_time($platform_info_by_day[$index]['begin']),
                'date_time' =>$platform_info_by_day[$index]['begin'],
            );
            // echo json_encode($insert_info);
            $check_info_condition = array(
                    'platform_name'=>$platform_info_by_day[$index]['platform_name'],
                    'create_date' =>$this->str_to_zero_time($platform_info_by_day[$index]['begin']),
            );
            if ($this->Fake_data_model->get_one_by_condition($check_info_condition)) {
                echo $this->db->last_query().'<br>';
                continue;
            } else {
                echo $this->Fake_data_model->add($insert_info).'<br>';
            }
            ++$index;
        }
    }
    public function insert_info_mounth()
    {
        $this->load->model('Fake_data_model');
        $date = $this->input->get('start');
        $to = $this->input->get('to');
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');

        if (!$date) {
            $this->Output_model->json_print(1, 'params not enough');

            return;
        }

        $current_date = $this->str_to_zero_time($date);
        // if (!$current_date) {
        //     $this->Output_model->json_print(1, 'date format wrong');
        //
        //     return;
        // }

        // if (!$to) {
        //     $next_date = $current_date + 60 * 60 * 24;
        // } else {
        //     $next_date = $this->str_to_zero_time($to);
        //     $nn =$next_date-2764800;
        //     if (!$next_date) {
        //         $this->Output_model->json_print(1, 'to format wrong');
        //         return;
        //     }
        // }
        // if ($current_date >= $next_date) {
        //     $this->Output_model->json_print(1, 'date must < to');
        //     return;
        // }

        $condition = array(
                    'status' => 2,
                );
        if ($platform) {
            $condition['platform'] = $platform;
        }
        $where_in = array();
        if ($game_father_id) {
            $condition['game_father_id']=$game_father_id;
            $father_condition = array(
                'game_father_id' => $game_father_id,
            );
            $all_ids = array();
            $games = $this->Game_model->get_by_condition($father_condition);
            foreach ($games as $game) {
                $all_ids[] = $game->game_id;
            }
            if (count($all_ids) > 0) {
                $where_in = array(
                    'name' => 'game_id',
                    'values' => $all_ids,
                );
            }
        }
        $this->load->model('Create_role_report_model');
        $this->load->model('Game_order_model');

        $this->load->model('Sign_report_model');
        $this->load->model('Login_report_model');

        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('Fake_model');
        $this->load->model('Month_data_model');
        $fake_condition = array(
            'platform' => $platform,
            'game_father_id' => $game_father_id,
        );
        $fake_select = ' scale ';
        $scale=1;
        $platforms = $this->Platform_model->get_by_condition();
        $game_faters = $this->Game_father_model->get_by_condition();
        $platform_info_by_day=array();
        //查询订单详情
        // $done_time = $current_date;
        $index=1;

        $to = $this->input->get('to');
        $bbgin = date('m', $current_date);
        for ($i=$bbgin; $i <=$to ; $i++) {
            // $mounth = date('m', $current_date);
            $mounth = $i;

            $big_mounth = array(1,3,5,7,8,10,12);
            $small_mounth=array(4,6,9,11);
            if (in_array($mounth, $big_mounth)) {
                $next_date = $current_date + 2678400;
            } elseif (in_array($mounth, $small_mounth)) {
                $next_date = $current_date + 2592000;
            } elseif ($mounth==2) {
                $next_date = $current_date + 2419200;
            } else {
                $this->Output_model->json_print(1, 'not much mounth');
            };
            $done_time=$next_date;
            $select = 'platform,count(DISTINCT(`user_id`)) as renshu ,sum(`money`) as money ,count(`order_id`) as cishu ';
            $loginselect = 'count(DISTINCT(`user_id`)) as login ';
            $createroleselect = 'count(DISTINCT(`user_id`)) as createrole ';
            // $done_time +=86400;
            $login_create_condition=array(
                        'create_date >= '=>$current_date,
                        'create_date <= '=>$done_time,
                        'game_father_id'=>$game_father_id,
                        'platform' => $platform,
                    );
            $condition['create_date >= ']=$current_date;
            $condition['create_date <= ']=$done_time;
            $condition['status ']=2;
                // $this->Create_role_report_model->set_table='create_role_report';
            $create_role = $this->Create_role_report_model->get_createrole_info($createroleselect, $login_create_condition, null, null, null, null, null, null);
            // echo $this->db->last_query();
            // echo '<br>';
            $login = $this->Login_report_model->get_loginreport_info($loginselect, $login_create_condition, null, null, null, null, null, null);
            $order = $this->Game_order_model->get_order_info($select, $condition, null, null, null, null, null, null);
                //统计充值次数数组声明
                $platform_order_count_array=array();
                //统计充值人数数组声明
                if (!$order) {
                    $platform_order_count_array[$platform]['cishu'] =0;
                    $platform_order_count_array[$platform]['renshu'] =0;
                    $platform_order_count_array[$platform]['total'] =0;
                    $platform_order_count_array[$platform]['platform_name'] =$platform;
                    $platform_order_count_array[$platform]['begin'] =date('Y-m-d', $current_date);
                    $platform_order_count_array[$platform]['to'] =date('Y-m-d', $done_time);
                    $platform_order_count_array[$platform]['login']=$login['0']->login;
                    $platform_order_count_array[$platform]['createrole']=$create_role['0']->createrole;
                    $platform_order_count_array[$platform]['arppu'] =0;
                    $platform_order_count_array[$platform]['fufeilv'] =0;
                } else {
                    //统计充值次数
                    $platform_order_count_array[$platform]['cishu']=$order[0]->cishu;

                            //统计充值人数
                            $platform_order_count_array[$platform]['renshu'] =$order[0]->renshu;

                            //统计充值总额
                            $platform_order_count_array[$platform]['total']=$order[0]->money/100 * $scale;
                            //添加渠道名
                            if (!isset($platform_order_count_array[$platform]['platform_name'])) {
                                $platform_order_count_array[$platform]['platform_name']=$platform;
                            }
                            //添加开始时间
                            if (!isset($platform_order_count_array[$platform]['begin'])) {
                                $platform_order_count_array[$platform]['begin']=date('Y-m-d', $current_date);
                            }
                            //添加结束时间
                            // if (!isset($platform_order_count_array[$one->platform]['to'])&&isset($done_time)) {
                            //     $platform_order_count_array[$one->platform]['to']=date('Y-m-d', $done_time);
                            // }
                    $platform_order_count_array[$platform]['login']=$login['0']->login * $scale;
                    $platform_order_count_array[$platform]['createrole']=$create_role['0']->createrole * $scale;

                    if ($login['0']->login==0) {
                        $login['0']->login=1;
                    }
                    $platform_order_count_array[$platform]['fufeilv']=($platform_order_count_array[$platform]['renshu']/$login['0']->login)*100;
                }

            $platform_order_count_array[$platform]['cishu']=$platform_order_count_array[$platform]['cishu']*$scale;
            $platform_order_count_array[$platform]['renshu']=$platform_order_count_array[$platform]['renshu']*$scale;
            $platform_info_by_day[$index]=$platform_order_count_array[$platform];
            // $current_date = $done_time;
            $insert_info = array(
                    'platform_name' =>$platform_info_by_day[$index]['platform_name'],
                    'cishu' =>$platform_info_by_day[$index]['cishu'],
                    'renshu' =>$platform_info_by_day[$index]['renshu'],
                    'login' =>$platform_info_by_day[$index]['login'],
                    'createrole' =>$platform_info_by_day[$index]['createrole'],
                    'fufeilv' =>$platform_info_by_day[$index]['fufeilv'],
                    'money' =>$platform_info_by_day[$index]['total'],
                    'create_date' =>$this->str_to_zero_time($platform_info_by_day[$index]['begin']),
                    'date_time' =>$platform_info_by_day[$index]['begin'],
                );
            echo json_encode($insert_info);
            $check_info_condition = array(
                        'platform_name'=>$platform_info_by_day[$index]['platform_name'],
                        'create_date' =>$this->str_to_zero_time($platform_info_by_day[$index]['begin']),
                );
            if ($this->Month_data_model->get_one_by_condition($check_info_condition)) {
                echo $platform_info_by_day[$index]['platform_name'].' '.$platform_info_by_day[$index]['begin'].' is in <br>';
                return;
            } else {
                echo $this->Month_data_model->add($insert_info);
                echo '<br>';
            }
            $current_date = $next_date;
        }


        ++$index;
    }
    public function insert_info_by_mounth($platform_en_name=null)   //every month 1th insert info
    {
        $this->load->model('Fake_data_model');
        $game_father_id =20000;

        $condition = array(
                    'status' => 2,
                );
        $where_in = array();
        $this->load->model('Create_role_report_model');
        $this->load->model('Game_order_model');
        $this->load->model('Sign_report_model');
        $this->load->model('Login_report_model');
        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('Fake_model');
        $this->load->model('Test_month_data_model');
        $this->load->model('Month_data_model');
        $scale=1;
        $platforms = $this->Platform_model->get_by_condition();
        $game_faters = $this->Game_father_model->get_by_condition();
        $platform_info_by_day=array();
        //查询订单详情
        // $done_time = $current_date;
        $index=1;
        $to = $this->input->get('to');
        $year = date('y', time());
        $mounth = date('m', time())-1;
        if ($mounth==0) {
            $mounth=12;
            $year-=1;
        }
        $date = $year.'-'.$mounth.'-'.'1';
        $current_date = $this->str_to_zero_time($date);
        $big_mounth = array(1,3,5,7,8,10,12);
        $small_mounth=array(4,6,9,11);
        if (in_array($mounth, $big_mounth)) {
            $next_date = $current_date + 2678400;
        } elseif (in_array($mounth, $small_mounth)) {
            $next_date = $current_date + 2592000;
        } elseif ($mounth==2) {
            $next_date = $current_date + 2419200;
        } else {
            $this->Output_model->json_print(1, 'not much mounth');
        };

        // foreach ($platforms as $one) {
            // $mounth = date('m', $current_date);
            // $platform=$one->platform;
        // $platform=$this->input->get('platform');
        if ($platform_en_name) {
            $platform=$platform_en_name;
        }

        $check_info_conditions=array(
                'platform_name'=>$platform,
                'create_date'=>$next_date
            );
            // if ($this->Test_month_data_model->get_one_by_condition($check_info_conditions)) {
            //     continue;
            // }

        $done_time=$next_date;
        $select = 'platform,count(DISTINCT(`user_id`)) as renshu ,sum(`money`) as money ,count(`order_id`) as cishu ';
        $loginselect = 'count(DISTINCT(`user_id`)) as login ';
        $createroleselect = 'count(DISTINCT(`user_id`)) as createrole ';
            // $done_time +=86400;
            $login_create_condition=array(
                        'create_date >= '=>$current_date,
                        'create_date <= '=>$done_time,
                        'game_father_id'=>$game_father_id,
                        'platform' => $platform,
                    );
        $condition['create_date >= ']=$current_date;
        $condition['create_date <= ']=$done_time;
        $condition['status ']=2;
        $condition['platform']=$platform;
        $condition['game_father_id']=$game_father_id;
                // $this->Create_role_report_model->set_table='create_role_report';
        $create_role = $this->Create_role_report_model->get_createrole_info($createroleselect, $login_create_condition, null, null, null, null, null, null);
            // echo $this->db->last_query();
            // echo '<br>';
        $login = $this->Login_report_model->get_loginreport_info($loginselect, $login_create_condition, null, null, null, null, null, null);
        $order = $this->Game_order_model->get_order_info($select, $condition, null, null, null, null, null, null);
                //统计充值次数数组声明
                $platform_order_count_array=array();
                //统计充值人数数组声明
                if (!$order) {
                    $platform_order_count_array[$platform]['cishu'] =0;
                    $platform_order_count_array[$platform]['renshu'] =0;
                    $platform_order_count_array[$platform]['total'] =0;
                    $platform_order_count_array[$platform]['platform_name'] =$platform;
                    $platform_order_count_array[$platform]['begin'] =date('Y-m-d', $current_date);
                    $platform_order_count_array[$platform]['to'] =date('Y-m-d', $done_time);
                    $platform_order_count_array[$platform]['login']=$login['0']->login;
                    $platform_order_count_array[$platform]['createrole']=$create_role['0']->createrole;
                    $platform_order_count_array[$platform]['arppu'] =0;
                    $platform_order_count_array[$platform]['fufeilv'] =0;
                } else {
                    //统计充值次数
                    $platform_order_count_array[$platform]['cishu']=$order[0]->cishu;

                    //统计充值人数
                    $platform_order_count_array[$platform]['renshu'] =$order[0]->renshu;

                    //统计充值总额
                    $platform_order_count_array[$platform]['total']=$order[0]->money/100 * $scale;
                    //添加渠道名
                    if (!isset($platform_order_count_array[$platform]['platform_name'])) {
                        $platform_order_count_array[$platform]['platform_name']=$platform;
                    }
                    //添加开始时间
                    if (!isset($platform_order_count_array[$platform]['begin'])) {
                        $platform_order_count_array[$platform]['begin']=date('Y-m-d', $current_date);
                    }
                    //添加结束时间
                    // if (!isset($platform_order_count_array[$one->platform]['to'])&&isset($done_time)) {
                    //     $platform_order_count_array[$one->platform]['to']=date('Y-m-d', $done_time);
                    // }
                    $platform_order_count_array[$platform]['login']=$login['0']->login * $scale;
                    $platform_order_count_array[$platform]['createrole']=$create_role['0']->createrole * $scale;

                    if ($login['0']->login==0) {
                        $login['0']->login=1;
                    }
                    $platform_order_count_array[$platform]['fufeilv']=($platform_order_count_array[$platform]['renshu']/$login['0']->login)*100;
                }

        $platform_order_count_array[$platform]['cishu']=$platform_order_count_array[$platform]['cishu']*$scale;
        $platform_order_count_array[$platform]['renshu']=$platform_order_count_array[$platform]['renshu']*$scale;
        $platform_info_by_day[$index]=$platform_order_count_array[$platform];
            // $current_date = $done_time;
            $insert_info = array(
                    'platform_name' =>$platform_info_by_day[$index]['platform_name'],
                    'cishu' =>$platform_info_by_day[$index]['cishu'],
                    'renshu' =>$platform_info_by_day[$index]['renshu'],
                    'login' =>$platform_info_by_day[$index]['login'],
                    'createrole' =>$platform_info_by_day[$index]['createrole'],
                    'fufeilv' =>$platform_info_by_day[$index]['fufeilv'],
                    'money' =>$platform_info_by_day[$index]['total'],
                    'create_date' =>$this->str_to_zero_time($platform_info_by_day[$index]['begin']),
                    'date_time' =>$platform_info_by_day[$index]['begin'],
                );
        echo json_encode($insert_info);
        $check_info_condition = array(
                        'platform_name'=>$platform_info_by_day[$index]['platform_name'],
                        'create_date' =>$this->str_to_zero_time($platform_info_by_day[$index]['begin']),
                );
        if ($this->Month_data_model->get_one_by_condition($check_info_condition)) {
            echo $platform_info_by_day[$index]['platform_name'].' '.$platform_info_by_day[$index]['begin'].' is in <br>';
            // continue;
        } else {
            echo $this->Month_data_model->add($insert_info);
            echo '<br>';
        }
        // }


        ++$index;
    }
    //每月首日获取所有渠道月总计数据接口
    public function month_data_insert()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();
        foreach ($platforms as $one) {
            $request = "http://admin.allugame.com/index.php/admin_report_api/insert_info_by_mounth/$one->platform";
            echo $request.'<br>';
        }
        // $this->insert_info_by_mounth();
    }
    public function mounthLTV()
    {
        $date = $this->input->get('start');
        $to = $this->input->get('to');
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');

        if (!$date) {
            $this->Output_model->json_print(1, 'params not enough');

            return;
        }

        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');

            return;
        }

        if (!$to) {
            $next_date = $current_date + 60 * 60 * 24;
        } else {
            $next_date = $this->str_to_zero_time($to);
            if (!$next_date) {
                $this->Output_model->json_print(1, 'to format wrong');
                return;
            }
        }
        if ($current_date >= $next_date) {
            $this->Output_model->json_print(1, 'date must < to');
            return;
        }
        $condition = array(
                    'status' => 2,
                );
        if ($platform) {
            $condition['platform'] = $platform;
        }
        $where_in = array();
        if ($game_father_id) {
            $condition['game_father_id']=$game_father_id;
            $father_condition = array(
                'game_father_id' => $game_father_id,
            );
            $all_ids = array();
            $games = $this->Game_model->get_by_condition($father_condition);
            foreach ($games as $game) {
                $all_ids[] = $game->game_id;
            }
            if (count($all_ids) > 0) {
                $where_in = array(
                    'name' => 'game_id',
                    'values' => $all_ids,
                );
            }
        }
        $this->load->model('Login_report_model');
    }
    //玩家用户留存
    public function user_retained()
    {
        $date = $this->input->get('start');
        $to = $this->input->get('to');
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');

        if (!$date) {
            $this->Output_model->json_print(1, 'params not enough');
            return;
        }

        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');
            return;
        }

        $next_date = $current_date + 60 * 60 * 24;
        if ($current_date > $next_date) {
            $this->Output_model->json_print(1, 'date must < to');
            return;
        }
        if ($platform) {
            $condition['platform'] = $platform;
        }
        $where_in = array();
        if ($game_father_id) {
            $condition['game_father_id']=$game_father_id;
            $father_condition = array(
            'game_father_id' => $game_father_id,
        );
            $all_ids = array();
            $games = $this->Game_model->get_by_condition($father_condition);
            foreach ($games as $game) {
                $all_ids[] = $game->game_id;
            }
            if (count($all_ids) > 0) {
                $where_in = array(
                'name' => 'game_id',
                'values' => $all_ids,
            );
            }
        }
        $this->load->model('Login_report_model');
        $this->load->model('User_model');
        $this->load->model('Create_role_report_model');
        $user_array = array();
        $user_select=' user_id ';
        $a_day = 86400;
        $condition = array(
        'create_date >= '=>$current_date+$a_day*0,
        'create_date <= '=>$current_date+$a_day*1,
        'platform'=>$platform,
        );
        $user_array = $this->User_model->get_by_select($user_select, $condition, null, null, null, null, null, null, null);
        $user_where =array();
        foreach ($user_array as $one) {
            array_push($user_where, $one->user_id);
        }
        $a_where = array(
        'name'=>'user_id',
        'values'=>$user_where
        );
        $all_info = array();
        array_push($all_info, $date);
        $count_user=count($user_array);
        array_push($all_info, $count_user);
        $create_role_select = ' count(DISTINCT(`create_role_report_id`)) as create_role';
        @$response = $this->Create_role_report_model->get_by_select($create_role_select, $tow_condition, null, null, null, null, null, $a_where, null);
        array_push($all_info, round(($response[0]->create_role/$count_user)*100, 2).'%');
        $_create_role = $response[0]->create_role;
        for ($i=0;$i<=6;$i++) {
            $login_select = ' count(DISTINCT(`user_id`)) as login';
            $tow_condition = array(
            'create_date >= '=>$current_date+$a_day*$i,
            'create_date <= '=>$current_date+$a_day*($i+1),
            'game_father_id'=>$game_father_id,
            'platform'=>$platform,
        );
            @$response = $this->Login_report_model->get_by_select($login_select, $tow_condition, null, null, null, null, null, $a_where, null);
            if ($_create_role) {
                array_push($all_info, round(($response[0]->login/$_create_role)*100, 2).'%');
            } else {
                array_push($all_info, 0);
            }
        }
        $fif_condition = array(
        'create_date >= '=>$current_date+$a_day*15,
        'create_date <= '=>$current_date+$a_day*16,
        'game_father_id'=>$game_father_id,
        'platform'=>$platform,
        );
        @$response = $this->Login_report_model->get_by_select($login_select, $fif_condition, null, null, null, null, null, $a_where, null);
        array_push($all_info, round(($response[0]->login/$_create_role)*100, 2).'%');
        $treeten_condition = array(
        'create_date >= '=>$current_date+$a_day*30,
        'create_date <= '=>$current_date+$a_day*31,
        'game_father_id'=>$game_father_id,
        'platform'=>$platform,
        );
        @$response = $this->Login_report_model->get_by_select($login_select, $treeten_condition, null, null, null, null, null, $a_where, null);
        array_push($all_info, round(($response[0]->login/$_create_role)*100, 2).'%');
        $sixten_condition = array(
        'create_date >= '=>$current_date+$a_day*60,
        'create_date <= '=>$current_date+$a_day*61,
        'game_father_id'=>$game_father_id,
        'platform'=>$platform,
        );
        @$response = $this->Login_report_model->get_by_select($login_select, $sixten_condition, null, null, null, null, null, $a_where, null);
        array_push($all_info, round(($response[0]->login/$_create_role)*100, 2).'%');
        $user_retained_info =array(
        'info'=>$all_info,
        'date'=>$date,
        );
        $this->Output_model->json_print(0, 'ok', $user_retained_info);
    }
    // date string to 00:00:00 of the give day
    public function str_to_zero_time($str)
    {
        $current_date = strtotime($str);
        if (!$current_date) {
            return false;
        }

        $current_date_str = date('Y-m-d', $current_date);
        $current_date = strtotime($current_date_str);
        // return $current_date-21600;
        return $current_date;
    }
}
