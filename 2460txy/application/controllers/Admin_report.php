<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_report extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $role = $this->session->userdata('role');
        // if (!$role) {
        //     if ($role != 'admin'&&$role!='customerService') {
        //         $this->Output_model->json_print(-1, 'session error');
        //
        //         exit;
        //     }
        // } else {
        // }
        // if ($_SERVER['HTTP_HOST']!='h5sdk.zytxgame.com') {
        //     exit;
        // }
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

        $this->load->view('admin/info_tongji/income_tongji', $data);
    }
    public function inner_page_new()
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

        $this->load->view('admin/info_tongji/income_tongji_new', $data);
    }
    public function turn_to_server_info_page(){
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );

        $this->load->view('admin/info_tongji/server_tongji', $data);
    }
    public function turn_to_server_info_page_new(){
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );

        $this->load->view('admin/info_tongji/server_tongji_new', $data);
    }
}
