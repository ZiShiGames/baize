<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Get_platform_info extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $role = $this->session->userdata('role');
        // if (!$role || $role != 'admin') {
        //     $this->Output_model->json_print(-1, 'session error');

        //     exit;
        // }
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
        $this->load->model('db/Game_father_model');
        $this->load->model('Game_order_model');
        $this->load->model('Sign_report_model');
        $this->load->model('Login_report_model');
        $this->load->model('Create_role_report_model');
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();
        $game_faters = $this->Game_father_model->get_by_condition();
        $orders = $this->Game_order_model->get_by_condition($condition, null, null, null, null, null, null);
        echo $this->db->last_query();
        echo '<br/>';
        $sign_orders = $this->Sign_report_model->get_by_condition($condition, null, null, null, null, null, null);
        echo $this->db->last_query();
        echo '<br/>';
        // echo count($sign_orders);
        echo '<br/>';
        $login_orders = $this->Login_report_model->get_by_condition($condition, null, null, null, null, null, null);
        echo $this->db->last_query();
        echo '<br/>';
        // echo count($login_orders);
        echo '<br/>';
        $create_orders = $this->Create_role_report_model->get_by_condition($condition, null, null, null, null, null, null);
        echo $this->db->last_query();
        echo '<br/>';
        // echo count($create_orders);
        echo '<br/>';
        $total = 0;
        if ($orders) {
            foreach ($orders as $one) {
                $total += $one->money;
            }
        }
        $json_order ='';
        $platform_info = array();
        $platform_array = array();
        if (!$orders||!$sign_orders||!$login_orders||!$create_orders) {
            $this->Output_model->json_print(-1, 'err', '无数据');
            return;
        }
        foreach ($orders as $one) {
            if ($one->status == 2) {
                $platform_info['user_id'] = $one->user_id;
                $platform_info['game_id'] = $one->game_id;
                $platform_info['platform_info'] = $one->platform;
                $platform_info['money'] = $one->money;

                $platform_array[$one->platform]=$platform_info;
            }
        }
        $platform_sign_count = array();
        foreach ($sign_orders as $one) {
        }

        $data = array(
                'orders' => $platform_info,
                'total' => $total,
                'platform_info' => $platforms,
                'game_faters' => $game_faters,
        );
        if ($data['orders']) {
            $data['info_is_show'] = 'show';
        }
        $this->load->view('admin/info_tongji/platform_tongji', $data);
        // $this->Output_model->json_print(0, 'ok', $json_order);
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
}
