<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cp_report_api extends CI_Controller
{
	public function create_role($platform = false, $game_id = '')
    {
        $roleid = $this->input->get('roleid');
        $srvid = $this->input->get('srvid');
        $nickname = $this->input->get('nickname');
        $cproleid = $this->input->get('cproleid');
        if (!$roleid || !$srvid || !$platform || !$nickname) {
            return;
        }
        $condition = array(
            'user_id' => $roleid,
        );
        $user = $this->User_model->get_one_by_condition($condition);
        if ($user) {
            $game = $this->Game_model->get_by_game_id($game_id);
            $this->load->model('Create_role_report_model');
            $data = array(
                'platform' => $platform,
                'user_id' => $roleid,
                'p_uid' => $user->p_uid,
                'server_id' => $srvid,
                'nickname' => $nickname,
                'game_id' => $game_id,
                'cproleid' => $cproleid,
                'game_father_id' => $game->game_father_id,
                'create_date' => time(),
            );
            $this->Create_role_report_model->add($data);
            $platform_model = $platform.'_model';
            if ($this->load->model('platform/'.$platform_model)) {
                $res = $this->$platform_model->create_role_collect($data);
                $this->Output_model->json_print(0, 'ok');
            }
        }
    }


	public function sign_collect($platform = false, $game_id = '')
    {
        $roleid = $this->input->get('roleid');
        $srvid = $this->input->get('srvid');
        if (!$roleid || !$srvid || !$platform) {
            return;
        }
        $condition = array(
                'user_id' => $roleid,
            );
        $user = $this->User_model->get_one_by_condition($condition);
        if ($user) {
            $this->load->model('Sign_report_model');
            $game = $this->Game_model->get_by_game_id($game_id);
            $data = array(
                    'platform' => $platform,
                    'user_id' => $roleid,
                    'p_uid' => $user->p_uid,
                    'server_id' => $srvid,
                    'game_id' => $game_id,
                    'game_father_id' => $game->game_father_id,
                    'create_date' => time(),
                );

            $this->Sign_report_model->add($data);
            $platform_model = $platform.'_model';
            $this->Output_model->json_print(0, 'ok');
        }
    }

	public function login($platform = false, $game_id = '')
    {
        $roleid = $this->input->get('roleid');
        $srvid = $this->input->get('srvid');
        $level = $this->input->get('level');
        $nickname = $this->input->get('nickname');
        $power = $this->input->get('power');
        $currency = $this->input->get('currency');

        $cproleid = $this->input->get('cproleid');
        if (!$roleid || !$srvid || !$level || !$platform || !$nickname) {
            echo '参数不全';
            return;
        }
        $condition = array(
            'user_id' => $roleid,
        );
        $user = $this->User_model->get_one_by_condition($condition);
        if ($user) {
            $game = $this->Game_model->get_by_game_id($game_id);

            $this->load->model('Login_report_model');
            $data = array(
                'platform' => $platform,
                'user_id' => $roleid,
                'p_uid' => $user->p_uid,
                'server_id' => $srvid,
                'nickname' => $nickname,
                'level' => $level,
                'game_id' => $game_id,
                'game_father_id' => $game->game_father_id,
                'power' => $power,
                'currency' => $currency,
                'cproleid' => $cproleid,
                'create_date' => time(),
            );
            $this->Login_report_model->add($data);
            $platform_model = $platform.'_model';
            if ($this->load->model('platform/'.$platform_model)) {
                $res = $this->$platform_model->login_collect($data);
                $this->Output_model->json_print(0, 'ok');
            }
        } else {
            $this->Output_model->json_print(1, '用户未找到');
        }
    }


	public function create_role_report($platform = false)
    {
        $platform_model = $platform.'_model';
        if ($this->load->model('platform/'.$platform_model)) {
            $res = $this->$platform_model->create_role_report();
        }
    }

    public function login_report($platform = false)
    {
        $platform_model = $platform.'_model';
        if ($this->load->model('platform/'.$platform_model)) {
            $res = $this->$platform_model->login_report();
        }
    }

    public function sign_report($platform = false)
    {
        $platform_model = $platform.'_model';
        if ($this->load->model('platform/'.$platform_model)) {
            $res = $this->$platform_model->sign_report();
        }
    }
}
