<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Trigger extends CI_Controller
{
    public $all_server_ips = array(

        //程序机
        '119.29.203.165',
        //sdk服务器
        '123.207.78.84',

        // 喜乐游程序机
        '111.230.170.85',

        // 喜乐游sdk服务器
        '193.112.222.144',
        '193.112.204.237',
        // '193.112.213.171',

        // 火冠
        '193.112.70.226',

        );

    public function __construct()
    {
        parent::__construct();
    }

    // trigger project to update through git,
    // make sure update.sh is added to crontab which does the update actually
    public function index()
    {
        system('echo 1 > ./img/git_update && chmod 777 ./img/git_update ');
        echo 'SUCCESS 等待10秒后 项目部署成功';
    }

    public function all()
    {
        foreach ($this->all_server_ips as $ip) {
            $url = "http://$ip/index.php/trigger";
            $content = $this->Curl_model->curl_get($url);
            if ($content && strpos($content, 'SUCCESS') !== false) {
                echo "SUCCESS: server $ip update trigger success , wait for 10s to confirm<br/>";
            }
        }
    }

    public function game_flush()
    {
        $this->load->model('Mini_programs_model');
        echo $this->Game_model->flush_cache();
        echo $this->Mini_programs_model->flush_cache();
        echo 'ok';
    }

    public function game_flush_all()
    {
        foreach ($this->all_server_ips as $ip) {
            $url = "http://$ip/index.php/trigger/game_flush";
            $content = $this->Curl_model->curl_get($url);
            // if ($content && strpos($content, 'ok') !== false) {
                echo "SUCCESS: server $ip game flush success<br/>";
            // }
        }
    }
}
