<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{
    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     *
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function index()
    {
        // $this->load->view('welcome_message');
        echo CI_VERSION;
    }

    public function test($value='')
    {
        $this->session->set_userdata('token', '2.00JqgowBuCqF7Bacb47447c2j77AfC');
    }
    public function test1()
    {
        $this->load->view('test1');
    }
    public function test2($value = '')
    {
        $this->load->model('Wx_token_model');
        $this->load->helper('url');

        $jsapi_ticket_obj = $this->Wx_token_model->get_sign_data(current_url());
        if ($jsapi_ticket_obj) {
            $sign = $this->Wx_token_model->sign($jsapi_ticket_obj);
            $data['noncestr'] = $jsapi_ticket_obj['noncestr'];
            $data['timestamp'] = $jsapi_ticket_obj['timestamp'];
            $data['sign'] = $sign;
        }
    }

    public function new_login()
    {
        $url = 'http://h5sdk.zytxgame.com/index.php/api/login_report/allu?date='.$this->input->get('date');
        $content = $this->Curl_model->curl_get($url);
        $count = 0;
        if ($content) {
            $res = json_decode($content);
            if (isset($res->data)) {
                foreach ($res->data->userList as $id => $info) {
                    if ($info->roleLevel == 1 and $info->serverId = 17) {
                        echo "$id : ".$info->roleLevel.' '.$info->serverId;
                        echo '<br/>';
                        ++$count;
                    }
                }
            }
        }
        echo "$count";
    }
}
