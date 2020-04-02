<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Huawei_admin_backstage extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // if ($_SERVER['HTTP_HOST']!='adminstage.allugame.com') {
        //     exit;
        // }
    }

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

            for ($one = 1 ; $one<=$max_server;$one++) {

                $this->db->select('COUNT(`order_id`) as cishu , COUNT(DISTINCT(`user_id`)) as renshu , SUM(`money`) as money ,ext');
                $game_order_condition['ext']=$one;
                $game_order_condition['status']=2;
                $game_order_request = $this->Game_order_model->get_one_by_condition($game_order_condition);
                // echo json_encode($game_order_request);
                // echo '<br>';
                $game_login_createrole_condition['server_id']=$one;
                $this->db->select('COUNT(DISTINCT(`cproleid`)) as zhuce');
                $create_role_request = $this->Create_role_report_model->get_one_by_condition($game_login_createrole_condition);
                // echo json_encode($create_role_request);
                // echo '<br>';
                $this->db->select('COUNT(DISTINCT(`cproleid`)) as denglu');
                $login_role_request = $this->login_report_model->get_one_by_condition($game_login_createrole_condition);
                // echo json_encode($login_role_request);
                // echo '<br>';
                $server_info = array(
                    'date'=>date('Y-m-d',$_create_role_start_time),
                    'server_id'=>$one,
                    'cishu'=>$game_order_request->cishu,
                    'renshu'=>$game_order_request->renshu,
                    'money'=>$game_order_request->money,
                    'zhuce'=>$create_role_request->zhuce,
                    'denglu'=>$login_role_request->denglu,
                );
                ($game_order_request->cishu)?$server_info['cishu']=$game_order_request->cishu:$server_info['cishu']=0;
                ($game_order_request->renshu)?$server_info['renshu']=$game_order_request->renshu:$server_info['renshu']=0;
                ($game_order_request->money)?$server_info['money']=$game_order_request->money:$server_info['money']=0;
                ($create_role_request->zhuce)?$server_info['zhuce']=$create_role_request->zhuce:$server_info['zhuce']=0;
                ($login_role_request->denglu)?$server_info['denglu']=$login_role_request->denglu:$server_info['denglu']=0;
                array_push($one_day_info,$server_info);
                // echo json_encode($server_info);
                // echo '<br>';
            }
        }
        $this->Output_model->json_print(0,'ok',$one_day_info);
    }


    //跳转至server数据页面
    public function turn_to_server_page()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();

        $condition = array(
            'platform' => 'huawei',
        );
        $this->db->select('game_father_id,game_name as game_father_name');
        $game_faters = $this->Game_model->get_by_condition($condition);
        // echo $this->db->last_query();
        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );
        $this->load->view('admin/huawei/server_tongji', $data);
    }

    //跳转至ltv数据页面
    public function turn_to_ltv_page()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $condition = array(
            'platform' => 'huawei',
        );
        $this->db->select('game_father_id,game_name as game_father_name');
        $game_faters = $this->Game_model->get_by_condition($condition);

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );
        $this->load->view('admin/huawei/ltv_page', $data);
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
        $game_father_id = $this->input->get('game_father_id');;
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
            $this->Output_model->json_print(0, 'ok', $all_data);
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
            $this->Output_model->json_print(0, 'ok', $all_data);
        } else {
            $this->Output_model->json_print(1, '没有数据,请检查查询参数');
        }
    }

    //跳转至留存页面
    public function turn_to_liucun_page()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $condition = array(
            'platform' => 'huawei',
        );
        $this->db->select('game_father_id,game_name as game_father_name');
        $game_faters = $this->Game_model->get_by_condition($condition);

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );
        $this->load->view('admin/huawei/liucun_page', $data);
    }

    //获取数据 充值 登录 注册 ap
    public function check_info()
    {
        $begin_time = $this->input->get('start');
        $end_time = $this->input->get('to');
        $game_father_id = $this->input->get('game_father_id');;
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

    public function index()
    {
        $this->load->view('admin/huawei/backstage_login_page');
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
        if ($user!='huawei'||$password!='huawei123') {
            $this->Output_model->json_print(-1, 'error');
            return;
        } else {
            $this->session->set_userdata('role', 'huawei');
            $this->Output_model->json_print(0, 'ok');
            return;
        }
    }
    public function back_stage_page()
    {
        if ($this->session->userdata('role')!='huawei') {
            return;
        } else {
            $this->load->model('db/Platform_model');
            $platforms = $this->Platform_model->get_by_condition();


            $condition = array(
                'platform' => 'huawei',
            );
            $this->db->select('game_father_id,game_name as game_father_name');
            $game_faters = $this->Game_model->get_by_condition($condition);


            $data = array(
                'platform_info' => $platforms,
                'game_faters' => $game_faters,
                'total'=>0,
            );

            $this->load->view('admin/huawei/huawei_income', $data);
        }
    }

    public function get_data_by_mounth()
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
        $total_money=0;
        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('Tiny_month_data_model');
        $platforms= $this->Platform_model->get_by_condition();
        foreach ($platforms as $one) {
            $platform = $one->platform;
            $fake_condition = array(
                'platform' => $platform,
            );
            $condition = array(
                    'create_date'=>$current_date,
                    'platform_name'=>$platform,
                );
            $platform_data = $this->Tiny_month_data_model->get_by_condition($condition)[0];
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


    public function get_data()
    {
        $this->load->model('Fake_data_model');
        $this->load->model('Tiny_fake_data_model');
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
        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $fake_condition = array(
            'platform' => $platform,
        );
        $fake_select = ' scale ';
        $scale = 1;
        $platforms = $this->Platform_model->get_by_condition();
        $game_faters = $this->Game_father_model->get_by_condition();
        $platform_info_by_day=array();
        $done_time = $current_date;
        $index=1;
        do {
            $done_time +=86400;
            $login_create_condition=array(
                    'create_date >= '=>$current_date,
                    'create_date < '=>$done_time,
                    // 'game_father_id'=>$game_father_id,
                    'platform_name' => $platform,
                );
            $fake_data_request = $this->Tiny_fake_data_model->get_by_condition($login_create_condition);
            // echo $this->db->last_query();
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
                $platform_order_count_array[$platform]['renshu'] =$fake_data_request[0]->renshu;//111

                //统计充值总额
                $platform_order_count_array[$platform]['total']=$fake_data_request[0]->money ;
                //添加渠道名
                if (!isset($platform_order_count_array[$platform]['platform_name'])) {
                    $platform_order_count_array[$platform]['platform_name']=$platform;
                }
                //添加开始时间
                if (!isset($platform_order_count_array[$platform]['begin'])) {
                    $platform_order_count_array[$platform]['begin']=date('Y-m-d', $current_date);
                }
                $platform_order_count_array[$platform]['login']=$fake_data_request['0']->login ;
                $platform_order_count_array[$platform]['createrole']=$fake_data_request['0']->createrole ;
                if ($fake_data_request['0']->login==0) {
                    $fake_data_request['0']->login=1;
                }
                if ($platform_order_count_array[$platform]['login']==0) {
                    $platform_order_count_array[$platform]['fufeilv']=($platform_order_count_array[$platform]['renshu']/1)*100;
                } else {
                    $platform_order_count_array[$platform]['fufeilv']=($platform_order_count_array[$platform]['renshu']/$platform_order_count_array[$platform]['login'])*100;
                }
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
                'platform_name' => $platform,
            );
        $data=array(
                'info'=>$platform_info_by_day
            );

        $this->Output_model->json_print(0, 'ok', $data);
    }

    //每日插入数据
    public function insert_info()
    {
        $_where_in=array(
            'jinb','lbw','nineg','tn','iqiyi','hiwan','zwfy','sougouhfive','qunhei'
        );
        // $this->load->model('Tiny_fake_data_model');
        $this->load->model('fake_data_model');
        $this->db->where_in('platform_name', $_where_in);
        $toDay = date('Y-m-d', strtotime('-1 days', time()));
        $nextDay = date('Y-m-d', strtotime('0 days', time()));
        $toDay = $this->str_to_zero_time($toDay);
        $nextDay = $this->str_to_zero_time($nextDay);
        $condition = array(
            'create_date >= '=>$toDay,
            'create_date <= '=>$nextDay,
        );
        $request = $this->fake_data_model->get_by_condition($condition);
        $_insert_info = $this->_get_info($request, 'Tiny_fake_data_model');
    }



    //初始化数据
    public function insert_tiny_stage_info()
    {
        $_where_in=array(
            'jinb','lbw','nineg','tn','iqiyi','hiwan','zwfy','sougouhfive','qunhei'
        );
        $this->load->model('Tiny_fake_data_model');
        $this->load->model('fake_data_model');
        $this->db->where_in('platform_name', $_where_in);
        $request = $this->fake_data_model->get_by_condition();
        $_insert_info = $this->_get_info($request, 'Tiny_fake_data_model');
    }



    public function insert_tiny_month_stage_info()
    {
        $_where_in=array(
            'jinb','lbw','nineg','tn','iqiyi','hiwan','zwfy','sougouhfive','qunhei'
        );
        $this->load->model('Tiny_month_data_model');
        $this->load->model('month_data_model');
        $this->db->where_in('platform_name', $_where_in);
        $request = $this->month_data_model->get_by_condition();
        $_insert_info = $this->_get_info($request, 'Tiny_month_data_model');
    }




    //检查表中是否有数据 若没有 插入
    //金榜数据缩小100倍
    private function _get_info($request, $model)
    {
        $this->load->model($model);
        foreach ($request as $one) {
            if ($one->platform_name == 'jinb') {
                $one->cishu = $one->cishu/100;
                $one->renshu = $one->renshu/100;
                $one->login = $one->login/100;
                $one->money = $one->money/100;
                $one->createrole = $one->createrole/100;
            } else {
                $one->cishu = $one->cishu/10;
                $one->renshu = $one->renshu/10;
                $one->login = $one->login/10;
                $one->money = $one->money/10;
                $one->createrole = $one->createrole/10;
            }
            $_insert_info = array(
                'platform_name'=>$one->platform_name,
                'cishu'=>$one->cishu,
                'renshu'=>$one->renshu,
                'login'=>$one->login,
                'createrole'=>$one->createrole,
                'fufeilv'=>$one->fufeilv,
                'create_date'=>$one->create_date,
                'money'=>$one->money,
                'date_time'=>$one->date_time,
            );
            $_check_info_exists = $this->$model->get_one_by_condition($_insert_info);
            if ($_check_info_exists) {
                echo "is exists.";
                echo '<br />';
            } else {
                $_add = $this->$model->add($_insert_info);
                if ($_add) {
                    echo json_encode($_add);
                    echo '<br />';
                } else {
                    echo "error.";
                    echo '<br />';
                }
            }
        }
    }

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

    public function logout()
    {
        $this->session->sess_destroy();
    }
}
