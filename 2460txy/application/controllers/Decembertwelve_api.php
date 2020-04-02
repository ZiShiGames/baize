<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Decembertwelve_api extends CI_Controller
{
    public function insert_decembertwelve_info()
    {
        $this->load->model('Decembertwelve_model');
        $userid = $this->input->get('userid');
        $data = array(
            'user_id'=>$userid,
            'create_date'=>time(),
        );
        $response = $this->Decembertwelve_model->add($data);
        if ($response) {
            echo 'ok';
        } else {
            return;
        }
    }
}
