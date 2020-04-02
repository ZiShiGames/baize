<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Platform_report extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $role = $this->session->userdata('role');
        if (!$role || $role != 'admin') {
            $this->Output_model->json_print(-1, 'session error');
            exit;
        }
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
        );

        $this->load->view('admin/info_tongji/platform_tongji', $data);
    }
}
