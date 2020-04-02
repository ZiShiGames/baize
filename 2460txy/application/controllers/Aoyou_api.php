<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Aoyou_api extends CI_Controller
{
    public function get_login_url()
    {
        $userid = $this->input->get('userid');
        echo '{"ReturnCode":0,"ReturnMessage":"成功","ReturnData":"http://h5sdk.zytxgame.com/index.php/enter/play/aoyoupingtai/1123?userid='.$userid.'"}';
    }
}
