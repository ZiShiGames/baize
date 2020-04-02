<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Get_user_login_info extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $role = $this->session->userdata('role');
        if (!$role || $role != 'admin') {
            // $this->Output_model->json_print(-1, 'session error');
            //
            // exit;
        }
    }

    public function daily_income()
    {
        $date = $this->input->get('start');
        $to = $this->input->get('end');
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
            $next_date = $next_date + 60 * 60 * 24;
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
        if ($platform) {
            $condition['platform'] = $platform;
        }
        $where_in = array();
        if ($game_father_id) {
            $condition['game_father_id'] = $game_father_id;
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
        $this->load->model('Sign_report_model');
        $this->load->model('Login_report_model');
        $select = 'count(DISTINCT(user_id)) as count_userid';
        $days = $this->get_days($current_date, $next_date);
        $data = array();
        for ($i=0;$i<$days;$i++) {
            $current_date_up = $current_date+60*60*24*$i;
            $next_date_up = $current_date+($i+1)*60*60*24;
            $conditions = array(
                'create_date >= ' =>$current_date_up,
                'create_date <= ' => $next_date_up,
            );
            if ($platform) {
                $conditions['platform'] = $platform;
            }
            // echo $platform;
            $sql_platform ='';
            if ($platform) {
                $sql_platform.=" and platform = '$platform'";
            }
            $sql = "select count(c.`user_id`) as count_userid from `create_role_report`   as c
                        where c.`user_id` in (select s.`user_id`
                        from `sign_report` as s
                        where  s.`create_date` >= $current_date_up
                        and s.`create_date` <=  $next_date_up$sql_platform ) and c.`create_date` >=$current_date_up
                        and c.`create_date` <= $next_date_up$sql_platform ";
            $query = $this->db->query($sql);
            $sign_info = $this->Sign_report_model->get_info_by_condition($conditions, $select, null, null, null, null, null, null);
            $login_info = $this->Login_report_model->get_info_by_condition($conditions, $select, null, null, null, null, null, null);
            if ($sign_info[0]->count_userid==0) {
                $sign_info[0]->count_userid=1;
            }
            $data_by_day=array(
                'data'=>date("Y-m-d", $current_date_up),
                'sign_info' => $sign_info[0]->count_userid,
                'login_info' => $login_info[0]->count_userid,
                'new_player' => $query->result()[0]->count_userid,
                'chuangjuelv' => number_format($query->result()[0]->count_userid/$login_info[0]->count_userid, 3),
            );
            $data[$i]=$data_by_day;
        }
        $this->Output_model->json_print(0, 'ok', $data);
        return;
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
        return $current_date;
    }
    public function get_days($first_day, $second_day)
    {
        $days = ($second_day-$first_day)/(60*60*24);
        if ($first_day>$second_day) {
            $this->Output_model->json_print(-1, 'err', '第二天大于第一天');
        }
        return $days;
    }
    public function index()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
            'total'=>0,
        );

        $this->load->view('admin/info_tongji/user_info/get_login_info', $data);
    }
}
