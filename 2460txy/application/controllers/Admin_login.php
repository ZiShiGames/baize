<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'redis'));
        // if ($_SERVER['HTTP_HOST']!='backstage.allugame.com') {
        //     exit;
        // }
    }

    public function index()
    {
        // if ($_SERVER['HTTP_HOST']!='backstage.allugame.com') {
        //     return;
        // } else {
        //     $this->load->view('admin/backstage_login');
        // }

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
        // $this->load->model('Admin_user_model');
        // $condition=array(
        //     'admin_user_name'=>$user,
        // );
        // $admin_info = $this->Admin_user_model->get_one_by_condition($condition, null, null, null, null, null, null);
        // if (!$admin_info) {
        //     $this->Output_model->json_print(-1, 'user not found');
        // }
        // if (md5($password.$this->Admin_user_model->ADMIN_SALT) == $admin_info->admin_user_password) {
        //     $this->session->set_userdata('role', $admin_info->admin_user_role);
        //     $this->Output_model->json_print(0, 'ok');
        // } else {
        //     echo md5($password.$this->Admin_user_model->ADMIN_SALT);
        //     $this->Output_model->json_print(2, 'user or password error');
        // }
        if ($user!='admin'||$password!='admin') {
            $this->Output_model->json_print(-1, 'error');
            return;
        } else {
            $this->session->set_userdata('role', $user);
            $this->Output_model->json_print(0, 'ok');
            return;
        }
    }
    //跳转至月数据页面
    public function turn_to_month_data_page(){
        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();
        $data = array(
            'game_fathers' => $game_faters,
        );
        $this->load->view('admin/info_tongji/month_data_page',$data);
    }

    public function turn_to_month_data_page_new(){
        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();
        $data = array(
            'game_fathers' => $game_faters,
        );
        $this->load->view('admin/info_tongji/month_data_page_new',$data);
    }
    //跳转至ltv数据页面
    public function turn_to_ltv_page()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );
        $this->load->view('admin/info_tongji/ltv_page', $data);
    }

    public function turn_to_ltv_page_new()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );
        $this->load->view('admin/info_tongji/ltv_page_new', $data);
    }



    //跳转至留存数据页面
    public function turn_to_liucun_page()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );
        $this->load->view('admin/info_tongji/liucun_page', $data);
    }

    public function turn_to_liucun_page_new()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );
        $this->load->view('admin/info_tongji/liucun_page_new', $data);
    }

    public function back_stage_page()
    {
        if ($this->session->userdata('role')!='admin') {
            return;
        } else {
            $this->load->model('db/Platform_model');
            $platforms = $this->Platform_model->get_by_condition();


            $this->load->model('db/Game_father_model');
            $game_faters = $this->Game_father_model->get_by_condition();

            $data = array(
                'platform_info' => $platforms,
                'game_faters' => $game_faters,
                'total'=>0,
            );

            $this->load->view('admin/info_tongji/back_stage_page', $data);
        }
    }
    public function back_stage_mounth_page()
    {
        if ($this->session->userdata('role')!='admin') {
            return;
        } else {
            $this->load->model('db/Platform_model');
            $platforms = $this->Platform_model->get_by_condition();


            $this->load->model('db/Game_father_model');
            $game_faters = $this->Game_father_model->get_by_condition();

            $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
            'total'=>0,
        );
            $this->load->view('admin/info_tongji/back_stage_mounth_page', $data);
        }
    }





    public function back_stage_page_old()
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

        $this->load->view('admin/info_tongji/back_stage_page_old', $data);
    }
    public function back_stage_mounth_page_old()
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
        $this->load->view('admin/info_tongji/back_stage_mounth_page_old', $data);
    }


    public function logout()
    {
        $this->session->sess_destroy();
    }
}
