<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_backstage_report_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $role = $this->session->userdata('role');
        if ($_SERVER['HTTP_HOST']!='backstage.allugame.com') {
            exit;
        }
        // if (!$role || $role != 'admin') {
        //     $this->Output_model->json_print(-1, 'session error');
        //
        //     exit;
        // }
    }


    //每月首日插入静态数据
    public function insert_month_data()
    {
        $this->load->model('Month_data_model');
        $this->load->model('Stage_month_data_model');
        $this->load->model('fake_model');
        $_Month_data_model_condition_time = date('Y-m-d', strtotime('-1 month', time()));
        $last= strtotime("-1 month", time());
        $last_lastday = date("Y-m-01", $last);
        $_Month_data_model_condition=array(
            'date_time'=>$last_lastday
        );
        $requery = $this->Month_data_model->get_by_condition($_Month_data_model_condition);
        $scale = 100;
        foreach ($requery as $one) {
            if ($one->platform_name=='jinb'||$one->platform_name=='kemeng'||$one->platform_name=='xcyx'||$one->platform_name=='fantastic'||$one->platform_name=='yuewan') {
                $get_fake_condition=array(
                    'mounth '=>intval(date('m', $one->create_date)),
                    'platform'=>$one->platform_name,
                );
                $this->db->select('scale');
                $fake_requery = $this->fake_model->get_by_condition($get_fake_condition);
                if ($fake_requery) {
                    $scale=$fake_requery[0]->scale;
                } else {
                    $scale=100;
                }
                $one->cishu = $one->cishu*$scale/100;
                $one->renshu = $one->renshu*$scale/100;
                $one->login = $one->login*$scale/100;
                $one->createrole = $one->createrole*$scale/100;
                $one->money = $one->money*$scale/100;
                if ($one->login==0) {
                    $one->fufeilv=$one->renshu/1;
                } else {
                    $one->fufeilv=$one->renshu/$one->login*100;
                }
                // echo json_encode($one);
            }
            $a=array(
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
            $check_condition=array(
                'platform_name'=>$one->platform_name,
                'date_time'=>$one->date_time,
            );
            $check_request = $this->Stage_month_data_model->get_one_by_condition($check_condition);
            if ($check_request) {
                echo $one->platform_name.' '.$one->date_time.' is exists';
                echo '<br>';
                continue;
            } else {
                $insert_request = $this->Stage_month_data_model->add($a);
                echo json_encode($insert_request);
                echo '<br>';
                echo $scale;
                echo '<br>';
            }
            // $this->Stage_month_data_model->add($a);

            # code...
        }
        // echo $this->db->last_query();
    }
    public function insert_data()
    {
        $this->load->model('Fake_data_model');
        $this->load->model('Stage_fake_data_model');
        $this->load->model('fake_model');
        $requery = $this->Fake_data_model->get_by_condition();
        foreach ($requery as $one) {
            if ($one->platform_name=='jinb'||$one->platform_name=='kemeng'||$one->platform_name=='xcyx'||$one->platform_name=='fantastic'||$one->platform_name=='yuewan') {
                $get_fake_condition=array(
                    'mounth '=>intval(date('m', $one->create_date)),
                    'platform'=>$one->platform_name,
                );
                $this->db->select('scale');
                $fake_requery = $this->fake_model->get_by_condition($get_fake_condition);
                if ($fake_requery) {
                    $scale=$fake_requery[0]->scale;
                } else {
                    $scale=100;
                }
                $one->cishu = $one->cishu*$scale/100;
                $one->renshu = $one->renshu*$scale/100;
                $one->login = $one->login*$scale/100;
                $one->createrole = $one->createrole*$scale/100;
                $one->money = $one->money*$scale/100;
                if ($one->login==0) {
                    $one->fufeilv=$one->renshu/1;
                } else {
                    $one->fufeilv=$one->renshu/$one->login*100;
                }
                echo json_encode($one);
            }
            $this->Stage_fake_data_model->add($one);
        }
        // echo json_encode($requery);
    }
    public function get_data()
    {
        $this->load->model('Fake_data_model');
        $this->load->model('Stage_fake_data_model');
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
            $fake_data_request = $this->Stage_fake_data_model->get_by_condition($login_create_condition);
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
                // 'game_father_id'=>$game_father_id,
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
        $this->load->model('Fake_model');
        $this->load->model('platform_data_model');
        $this->load->model('Stage_month_data_model');
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
            $platform_data = $this->Stage_month_data_model->get_by_condition($condition)[0];
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

//every day insert data info to stage_fake
    public function insert_info()
    {
        $this->load->model('Fake_data_model');
        $this->load->model('Fake_model');
        $this->load->model('db/Platform_model');
        $this->load->model('Stage_fake_data_model');
        $platforms = $this->Platform_model->get_by_condition();
        $toDay = date('Y-m-d', strtotime('-1 days', time()));
        $nextDay = date('Y-m-d', strtotime('0 days', time()));
        $toDay = $this->str_to_zero_time($toDay);
        $nextDay = $this->str_to_zero_time($nextDay);
        $condition = array(
            'create_date >= '=>$toDay,
            'create_date <= '=>$nextDay,
        );
        $scale = 100;
        foreach ($platforms as $one) {
            $condition['platform_name']=$one->platform;
            $platform_info = $this->Fake_data_model->get_by_condition($condition)[0];
            if ($one->platform =='allu'||$one->platform =='alluapp'||$one->platform =='allutest'||$one->platform =='alluwd') {
                continue;
            }
            if ($one->platform == 'kemeng' || $one->platform =='xcyx'||$one->platform=='jinb'||$one->platform=='fantastic'||$one->platform=='yuewan') {
                $fake_select = ' scale ';
                $fake_condition=array(
                    'platform'=>$one->platform,
                    'mounth'=>date('m', strtotime('-1 days', time())),
                );
                $fake_response = $this->Fake_model->get_order_info($fake_select, $fake_condition, null, null, null, null, null, null);
                // echo $this->db->last_query();
                if (!$fake_response) {
                    $scale = 100;
                } else {
                    $scale = $fake_response[0]->scale;
                }
                $platform_info->cishu = $platform_info->cishu*$scale/100;
                $platform_info->renshu = $platform_info->renshu*$scale/100;
                $platform_info->login = $platform_info->login*$scale/100;
                $platform_info->createrole = $platform_info->createrole*$scale/100;
                $platform_info->money = $platform_info->money*$scale/100;
                if ($platform_info->login==0) {
                    $platform_info->fufeilv=$platform_info->renshu/1;
                } else {
                    $platform_info->fufeilv=$platform_info->renshu/$platform_info->login*100;
                }
            }
            $stage_condition=array(
                'platform_name'=>$one->platform,
                'create_date'=>$platform_info->create_date,
            );
            if ($this->Stage_fake_data_model->get_by_condition($stage_condition)) {
                echo $this->db->last_query();
            } else {
                echo json_encode($platform_info);
                $insert_info=array(
                    'platform_name'=>$platform_info->platform_name,
                    'cishu'=>$platform_info->cishu,
                    'renshu'=>$platform_info->renshu,
                    'login'=>$platform_info->login,
                    'createrole'=>$platform_info->createrole,
                    'fufeilv'=>$platform_info->fufeilv,
                    'create_date'=>$platform_info->create_date,
                    'money'=>$platform_info->money,
                    'date_time'=>$platform_info->date_time,
                );
                $this->Stage_fake_data_model->add($insert_info);
            }
        }
    }


    public function change_scale($platform, $begin, $end, $scale)
    {
        $get_info_condition=array(
            'platform_name'=>$platform,
            'create_date >= '=>$begin,
            'create_date <= '=>$end,
        );
        $this->load->model('fake_data_model');
        $this->load->model('Stage_fake_data_model');
        $response = $this->fake_data_model->get_by_condition($get_info_condition);
        $scale=$scale/100;
        foreach ($response as $one) {
            // echo json_encode($one);
            $_insert_info=array(
                'platform_name'=>$one->platform_name,
                'cishu'=>$one->cishu*$scale,
                'renshu'=>$one->renshu*$scale,
                'login'=>$one->login*$scale,
                'createrole'=>$one->createrole*$scale,
                'money'=>$one->money*$scale,
                'date_time'=>$one->date_time,
                'create_date'=>$one->create_date,
            );
            if ($one->login==0) {
                $_insert_info['fufeilv']=$one->renshu/1;
            } else {
                $_insert_info['fufeilv']=$one->renshu/$one->login*100;
            }
            echo json_encode($_insert_info);
            $this->Stage_fake_data_model->add($_insert_info);
        }
        // echo json_encode($response);
    }
    //删除所有每日数据后添加新数据
    public function insert_new_stage_fake_data($platform, $start_time, $end_time, $scale)
    {
        $get_info_condition=array(
            'platform_name'=>$platform,
            'create_date >= '=>$start_time-1,
            'create_date <= '=>$end_time+1,
        );
        $this->load->model('fake_data_model');
        $this->load->model('Stage_fake_data_model');
        $response = $this->fake_data_model->get_by_condition($get_info_condition);
        $scale=$scale/100;
        foreach ($response as $one) {
            // echo json_encode($one);
            $_insert_info=array(
                'platform_name'=>$one->platform_name,
                'cishu'=>$one->cishu*$scale,
                'renshu'=>$one->renshu*$scale,
                'login'=>$one->login*$scale,
                'createrole'=>$one->createrole*$scale,
                'money'=>$one->money*$scale,
                'date_time'=>$one->date_time,
                'create_date'=>$one->create_date,
            );
            if ($one->login==0) {
                $_insert_info['fufeilv']=$one->renshu/1;
            } else {
                $_insert_info['fufeilv']=$one->renshu/$one->login*100;
            }
            $check_insert_info_condition = array(
                'platform_name' => $one->platform_name,
                'date_time'=>$one->date_time,
                'create_date'=>$one->create_date,
            );
            $check_response = $this->Stage_fake_data_model->get_one_by_condition($check_insert_info_condition);
            if ($check_response) {
                // echo json_encode($check_response);
                echo $one->platform_name.' '.$one->data_time.' is exists';
                echo '<br>';

                continue;
            } else {
                echo json_encode($_insert_info);
                $this->Stage_fake_data_model->add($_insert_info);
                echo '<br>';
            }
        }
        // echo json_encode($response);
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
}
