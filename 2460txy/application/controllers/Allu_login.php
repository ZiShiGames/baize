<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Allu_login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // if ($_SERVER['HTTP_HOST']!='backstage.allugame.com') {
        //     exit;
        // }
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function index()
    {
    }
    public function login(){
        $account = $this->input->get('account');
        $password = $this->input->get('password');
        $this->load->model('Allu_user_model');
        $condition = array(
            'account' => $account,
            'password' => $password
        );
        $check = $this->Allu_user_model->get_one_by_condition($condition);
        if($check){
            $this->cache->save($account.'_token',md5(time().$account.$password),30);
            // $this->session->set_userdata($account.'_token',md5(time().$account.$password));
            $this->Output_model->json_print(0, 'ok', $check);
        }else{
            $this->Output_model->json_print(3, 'userinfo error');
        }
    }

    public function wxh5_login(){
        $account = $this->input->get('account');
        $this->load->model('User_model');
        $condition = array(
            'unionid' => $account,
        );
        $check = $this->User_model->get_one_by_condition($condition);
        if($check){
            $check = array(
                'account'=>$check->p_uid,
            );
            $this->cache->save($account.'_token',md5(time().$account),30);
            // $this->session->set_userdata($account.'_token',md5(time().$account.$password));
            $this->Output_model->json_print(0, 'ok', $check);
        }else{
            $this->Output_model->json_print(3, 'userinfo error');
        }
    }

    public function sign_create()
    {
        $account = $this->input->get('account');
        $password = $this->input->get('password');
        $this->load->model('Allu_user_model');
        $condition = array(
            'account' => $account
        );
        $check = $this->Allu_user_model->get_one_by_condition($condition);
        if (!$check) {
            $condition['password'] = $password;
            $condition['create_date'] = time();
            $response = $this->Allu_user_model->add($condition);
            if ($response) {
                $this->Output_model->json_print(0, 'ok', $response);
            } else {
                $this->Output_model->json_print(2, 'add error');
            }
        } else {
            $this->Output_model->json_print(1, '用户已存在');
        }
    }
    public function one_key_sign(){
        $account = 'G'.rand(1, 10).time().rand(1, 10);
        $password = rand(100000, 999999);
        $this->load->model('Allu_user_model');
        $condition = array(
            'account' => $account
        );
        $check = $this->Allu_user_model->get_one_by_condition($condition);
        if (!$check) {
            $condition['password'] = $password;
            $condition['create_date'] = time();
            $response = $this->Allu_user_model->add($condition);
            if ($response) {
                $this->Output_model->json_print(0, 'ok', $condition);
            } else {
                $this->Output_model->json_print(2, 'add error');
            }
        } else {
            $this->Output_model->json_print(1, 'user exists');
        }

    }
}
