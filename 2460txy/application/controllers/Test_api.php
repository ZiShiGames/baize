<?php
class Test_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function pay(){
        $this->load->view('test');
    }

    public function receive_data()
    {
        $this->load->model("Test_model");
        $reports = $this->Test_model->deal_data();
        if ($reports) {
            $response = array(
                'success' => true,
                'message' => '成功',
            );
            $data = array(
                'total' => count($reports),
            );
            $userList = array();

            foreach ($reports as $one) {
                $user = array();
                $user['roleLevel'] = 1;
                $user['createTime'] = date('Y-m-d H:i:s', $one->create_date);
                $user['serverId'] = $one->server_id;
                $user['2460_user_id'] = $one->user_id;
                $userList[$one->p_uid . ''] = $user;
            }
            $data['userList'] = $userList;
            $response['data'] = $data;
        } else {
            $response = array(
                'success' => true,
                'message' => '成功',
            );
        }

        echo json_encode($response);
    }

    public function receive_create_role()
    {
        $this->load->model("Test_model");
        $reports = $this->Test_model->deal_create_role_data();
        if ($reports) {
            $response = array(
                'success' => true,
                'message' => '成功',
            );
            $data = array(
                'total' => count($reports),
            );
            $userList = array();

            foreach ($reports as $one) {
                $user = array();
                $user['roleLevel'] = 1;
                $user['createTime'] = date('Y-m-d H:i:s', $one->create_date);
                $user['serverId'] = $one->server_id;
                $user['2460_user_id'] = $one->user_id;
                $userList[$one->p_uid . ''] = $user;
            }
            $data['userList'] = $userList;
            $response['data'] = $data;
        } else {
            $response = array(
                'success' => true,
                'message' => '成功',
            );
        }

        echo json_encode($response);
    }

    public function sign_report()
    {
        $user_ids = $this->input->post('user_ids');
        $platform = $this->input->post("platform");
        if ($user_ids) {
            $sql = "select * from sign_report where (platform = '" . $platform . "') and p_uid in (" . $user_ids . ")";
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $res = $query->result();
                echo json_encode($res);
            } else {
                echo json_encode(array('error' => "not found"));
            }
        }
    }

    public function create_role_report()
    {
        $user_ids = $this->input->post('user_ids');
        $platform = $this->input->post("platform");
        if ($user_ids) {
            $sql = "select * from create_role_report where (platform = '" . $platform . "') and p_uid in (" . $user_ids . ")";
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $res = $query->result();
                echo json_encode($res);
            } else {
                echo json_encode(array('error' => "not found"));
            }
        }
    }
    public function test_console_log()
    {
        // include "application/libraries/console_log.php";
        // Console_log::log(2);
        $this->load->library('console_log');
        $this->console_log->log(1);
    }
    public function test_virus()
    {
        header("content-Type: text/html; charset=gb2312");
        if (get_magic_quotes_gpc()) {
            foreach ($_POST as $k => $v) {
                $_POST[$k] = stripslashes($v);
            }
        }


    }
    public function check_share(){
        log_message('debug','share info '.json_encode($_GET));
    }

}
