<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_backstage_api extends CI_Controller
{
    private $BACKSTAGE = 'backstage';
    private $CACHE_DRIVER = '';
    public function __construct()
    {
        parent::__construct();

        // 线上运行时请注释 header('Access-Control-Allow-Origin:*');
        // header('Access-Control-Allow-Origin:*');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept,x-token");

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
            exit;
        }
        if ($this->input->get('test') == 'yes') {
        } else {
            $this->load->driver('cache', array('adapter' => 'redis', 'backup' => 'apc'));
            ($this->cache->redis->is_supported()) ? $this->CACHE_DRIVER = 'redis' : '';
            ($this->cache->apc->is_supported()) ? $this->CACHE_DRIVER = 'apc' : '';
            if (!$this->CACHE_DRIVER) {
                $this->Output_model->json_print(1, 'no cache driver');
                log_message('debug', $this->BACKSTAGE . ' no cache driver');
                exit;
            }

            if (strtolower($_SERVER['REQUEST_METHOD']) == 'get' || strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
                if (!isset($_SERVER['HTTP_X_TOKEN'])) {
                    $this->Output_model->json_print(1, 'no token');
                    log_message('debug', $this->BACKSTAGE . ' x token miss');
                    exit;
                }
                $token = $_SERVER['HTTP_X_TOKEN'];
                $cache_driver = $this->CACHE_DRIVER;
                $admin_info = $this->cache->$cache_driver->get($token);
                if (!isset($admin_info) || !$admin_info) {
                    $this->Output_model->json_print(1, 'no admin info');
                    log_message('debug', $this->BACKSTAGE . ' no admin info' . ' xtoken: ' . $token . ' admin_info: ' . json_encode($admin_info));
                    exit;
                }

                if (isset($admin_info->admin_user_role) && $admin_info->roles) {
                    $admin_info->roles = json_decode($admin_info->roles);
                } else {
                    $this->Output_model->json_print(1, 'no roles');
                    log_message('debug', $this->BACKSTAGE . ' no roles');
                    exit;
                }

                if ($admin_info->roles[0] != 'admin' && $admin_info->roles[0] != 'game_father_manager' && $admin_info->roles[0] != 'game_manager') {
                    $this->Output_model->json_print(1, 'roles error', $admin_info->roles[0]);
                    log_message('debug', $this->BACKSTAGE . ' roles error');
                    exit;
                }
            }
        }

        $this->load->model('Create_role_report_model');
        $this->load->model('db/Platform_model');
        $this->load->model('db/Game_father_model');
        $this->load->model('db/Stage_hour_data_model');
        $this->load->model('Game_order_model');
        $this->load->model('Login_report_model');
        $this->load->model('Sign_report_model');
        $this->load->model('Admin_user_model');
        $this->load->model('db/Illegal_user_model');
        $this->load->model('Game_order_fuli_model');
        $this->load->model('User_exchange_model');
        $this->load->model('Common_model');
        // echo $this->db->last_query();

    }

    public function index()
    {
        // $this->Output_model->json_print('hi~');
        echo 'hi';
    }

    /*
     * game_manage
     * 游戏管理
     */
    public function get_game_list()
    {
        // $game_list
        // $this->db->select("game_id,game_name,game_father_id,platform,platform_key,game_login_url,game_pay_nofity,status,create_date");
        $this->db->order_by('create_date', 'desc'); // 倒序
        $game_list = $this->Game_model->get_by_condition();
        // 根据 名称 查找表B，并追加到表A
        foreach ($game_list as $one) {
            $condition = array(
                'platform' => $one->platform,
            );
            $one->chinese_name = $this->Platform_model->get_one_by_condition($condition)->platform_chinese;
        }
        // $game_father_list
        $this->db->select("game_father_id,game_father_name,status,create_date");
        $game_father_list = $this->Game_father_model->get_by_condition();

        // db search
        $data = array(
            'game_list' => $game_list,
            'game_father_list' => $game_father_list,
        );
        $this->Output_model->json_print(0, 'ok', $data);
    }

    public function set_game_status()
    {
        $game_id = $this->input->get('game_id');
        if (!$game_id) {
            $this->Output_model->json_print(1, 'game_id missing');
            return;
        }

        $status = $this->input->get('status');
        if (!isset($status)) {
            $this->Output_model->json_print(2, 'status missing');
            return;
        }
        $where = array('game_id' => $game_id);
        $data = array('status' => $status);
        if ($this->Game_model->update($data, $where)) {
            $condition = array('game_id' => $game_id);
            $game = $this->Game_model->get_one_by_condition($condition);
            // 刷新缓存
            $this->flush_game_map();
            $data = array('one' => $game);
            $this->Output_model->json_print(0, 'ok', $data);
        } else {
            $this->Output_model->json_print(3, 'update error');
        }
    }

    public function set_game_pay_status()
    {
        $game_id = $this->input->get('game_id');
        if (!$game_id) {
            $this->Output_model->json_print(1, 'game_id missing');
            return;
        }

        $status = $this->input->get('status');
        if (!isset($status)) {
            $this->Output_model->json_print(2, 'status missing');
            return;
        }
        $where = array('game_id' => $game_id);
        $data = array('pay_status' => $status);
        if ($this->Game_model->update($data, $where)) {
            $condition = array('game_id' => $game_id);
            $game = $this->Game_model->get_one_by_condition($condition);
            // 刷新缓存
            $this->flush_game_map();
            $data = array('one' => $game);
            $this->Output_model->json_print(0, 'ok', $data);
        } else {
            $this->Output_model->json_print(3, 'update error');
        }
    }

    public function create_game_father()
    {
        $game_name = $this->input->post('game_name');
        if (!$game_name) {
            $this->Output_model->json_print(2, 'err: game name null');
            return;
        }
        $app_id = 'g2460' . md5($game_name . time() . rand(0, 1000));
        $app_id = substr($app_id, 0, 18);
        $app_key = md5($game_name . time() . rand(0, 1000));

        $data = array(
            'game_father_name' => $game_name,
            'app_id' => $app_id,
            'app_key' => $app_key,
            'status' => 1,
            'create_date' => time(),
        );
        $game_id = $this->Game_father_model->add($data);
        if ($game_id) {
            $this->Output_model->json_print(0, 'ok', $data);
        } else {
            $this->Output_model->json_print(1, 'db err');
        }
    }

    public function create_platform()
    {
        $platform_chinese = $this->input->post('platform_chinese');
        $platform = $this->input->post('platform');
        $platform_key = $this->input->post('platform_key');

        $platform_chinese = str_replace(' ', '', $platform_chinese);
        $platform = str_replace(' ', '', $platform);
        $platform_key = str_replace(' ', '', $platform_key);

        if (!preg_match('/^[a-z]+$/', $platform)) {
            // echo '拼音缩写必须全部为小写字母';
            $this->Output_model->json_print(6, "err: platform pinyin name must be lowercase letters", $platform);
            return;
        }

        if (!$platform_chinese || !$platform || !$platform_key) {
            // echo 'Error: 参数不足';
            $this->Output_model->json_print(5, "err: data null");
            return;
        }

        if (!preg_match('/^([0-9a-zA-Z,_@-])+$/', $platform_key)) {
            // echo 'key 必须为 a-z A-Z 逗号组成';
            $this->Output_model->json_print(4, "err: platform key must to be a-z a-z", $platform_key);
            return;
        }

        $condition = array('platform_chinese' => $platform_chinese);

        $res = $this->Platform_model->get_by_condition($condition);
        if ($res) {
            // echo "$platform_chinese 重复，不能成功添加";
            $this->Output_model->json_print(3, "err: platform name repeat", $platform_chinese);
            return;
        }

        $condition = array('platform' => $platform);

        $res = $this->Platform_model->get_by_condition($condition);
        if ($res) {
            // echo "$platform 重复，不能成功添加";
            $this->Output_model->json_print(2, "err: platform pinyin name repeat", $platform);
            return;
        }

        $data = array(
            'platform' => $platform,
            'platform_chinese' => $platform_chinese,
            'platform_key' => $platform_key,
            'create_date' => time(),
        );

        $platform_id = $this->Platform_model->add($data);

        if ($platform_id) {
            $this->Output_model->json_print(0, 'ok');
        } else {
            $this->Output_model->json_print(1, 'db err');
        }
    }

    public function new_game()
    {
        $game_fathers = $this->Game_father_model->get_by_condition();

        $platforms = $this->Platform_model->get_by_condition();

        $data = array(
            'fathers' => $game_fathers,
            'platforms' => $platforms,
            'platform_key' => ['key'], // allu keys
        );
        $this->Output_model->json_print(0, "ok", $data);
    }

    public function platform_keys()
    {
        $platform = $this->input->get('platform');
        if (!$platform) {
            $this->Output_model->json_print(1, 'no platform input');
            return;
        }
        $condition = array('platform' => $platform);
        $platform_obj = $this->Platform_model->get_one_by_condition($condition);
        if (!$platform_obj) {
            $this->Output_model->json_print(2, 'no platform found by '+$platform, $platform);
            return;
        }

        $this->Output_model->json_print(0, 'ok', $platform_obj->platform_key);
    }

    public function create_game()
    {
        // echo json_encode($_POST);
        $game_father_id = $this->input->post('game_father_id');
        $platform = $this->input->post('platform');
        $game_login_url = $this->input->post('game_login_url');
        $game_pay_nofity = $this->input->post('game_pay_nofity');
        if (!$game_father_id || !$platform || !$game_login_url || !$game_pay_nofity) {
            // echo 'game_father_id: '.$game_father_id.' platform: '.$platform.' game_login_url: '.$game_login_url.' game_pay_nofity: '.$game_pay_nofity;
            // echo 'Error: 参数不足';
            $outputData2 = array(
                'game_father_id' => $game_father_id,
                '$platform' => $platform,
                'game_login_url' => $game_login_url,
                'game_pay_nofity' => $game_pay_nofity,
            );
            $this->Output_model->json_print(6, 'err: data null', $outputData2);

            return;
        }

        $condition = array('game_father_id' => $game_father_id);
        $game_father = $this->Game_father_model->get_one_by_condition($condition);
        if (!$game_father) {
            // echo "$game_father_id 游戏没有找到";
            $this->Output_model->json_print(5, $game_father_id . " has no found by game_father db", $game_father_id);
            return;
        }

        $app_id = $game_father->app_id;
        $app_key = $game_father->app_key;

        $condition = array(
            'game_father_id' => $game_father->game_father_id,
            'platform' => $platform,
        );

        $game = $this->Game_model->get_one_by_condition($condition);
        if ($game) {
            // echo $platform.' 渠道已经添加 '.$game->game_name;
            $outputData = array(
                'platform' => $platform,
                'game_name' => $game->game_name,
            );
            $this->Output_model->json_print(4, $platform . " has been added " . $game->game_name, $outputData);
            return;
        }

        $condition = array(
            'platform' => $platform,
        );
        $platform_obj = $this->Platform_model->get_one_by_condition($condition);

        $pieces = explode(',', $platform_obj->platform_key);
        $platform_key = array();
        foreach ($pieces as $one) {
            if (preg_match('/\s/', $this->input->post($one))) {
                // echo '别再输入空格了！！！';
                $this->Output_model->json_print(3, $one . " has Spaces", $one);
                return;
            }

            $platform_key[$one] = $this->input->post($one);
            if (!$platform_key[$one]) {
                // echo "$one 不能为空";
                $this->Output_model->json_print(2, $one . " can't be empty", $one);

                return;
            }
        }

        $data = array(
            'game_name' => $game_father->game_father_name,
            'game_father_id' => $game_father->game_father_id,
            'platform' => $platform,
            'game_login_url' => $game_login_url,
            'game_pay_nofity' => $game_pay_nofity,
            'platform_key' => json_encode($platform_key),
            'app_id' => $app_id,
            'app_key' => $app_key,
            'create_date' => time(),
        );

        $game_id = $this->Game_model->add($data);
        if ($game_id) {
            $this->flush_game_map();
            // header('Location: /index.php/admin/game_manage');
            $this->Output_model->json_print(0, 'ok');
        } else {
            // echo '新建失败, 数据库错误';
            $this->Output_model->json_print(1, 'db err');
        }
    }

    /*
     * info_tongji
     * 收入统计
     */
    public function get_search_data()
    {
        $platform = $this->input->get('game_id');
        $game_father_id = $this->input->get('game_father_id');
        if (isset($platform) && $platform) {
            $platform_condition = array('platform' => $platform);
            $platforms = $this->Platform_model->get_by_condition($platform_condition);
            $game_ids = $this->Game_model->get_by_condition($platform_condition);
            $where_in = array();
            foreach ($game_ids as $one) {
                $where_in[] = $one->game_father_id;
            }
            $this->db->where_in('game_father_id', $where_in);
            $game_fathers = $this->Game_father_model->get_by_condition();
        } elseif (isset($game_father_id) && $game_father_id) {
            $platforms = $this->Platform_model->get_by_condition();
            $game_fathers = $this->Game_father_model->get_by_condition(array('game_father_id' => $game_father_id));
        } else {
            $platforms = $this->Platform_model->get_by_condition();
            $game_fathers = $this->Game_father_model->get_by_condition();
        }
        $data = array(
            'platform_info' => $platforms,
            'game_fathers' => $game_fathers,
            'total' => 0,
        );
        $this->Output_model->json_print(0, 'ok', $data);
    }

    public function check_info()
    {
        $begin_time = $this->input->get('start');
        $end_time = $this->input->get('to');
        $game_father_id = $this->input->get('game_father_id');
        $platform = $this->input->get('platform');

        if (!$game_father_id || !$begin_time) {
            $this->Output_model->json_print(2, 'data is null');
            exit;
        }

        $begin_time = $this->str_to_zero_time($begin_time);

        if (!$end_time) {
            $end_time = $begin_time + 60 * 60 * 24;
        } else {
            $end_time = $this->str_to_zero_time($end_time) + 60 * 60 * 24;
        }

        $condition = array(
            'create_date >=' => $begin_time,
            'create_date <' => $end_time,
            'game_father_id' => $game_father_id,
        );

        if ($platform) {
            $condition['platform'] = $platform;
        }

        $response = $this->Stage_hour_data_model->get_by_condition($condition);
        if ($response) {
            $response = array_reverse($response);
            $data = array(
                'info' => $response,
            );
            $this->Output_model->json_print(0, 'ok', $data);
        } else {
            $this->Output_model->json_print(1, 'db data is null');
        }
    }

    private function str_to_zero_time($str)
    {
        date_default_timezone_set('Asia/Shanghai');
        $current_date = strtotime($str);
        if (!$current_date) {
            return false;
        }

        $current_date_str = date('Y-m-d', $current_date);
        $current_date = strtotime($current_date_str);
        return $current_date;
    }

    //每小时插入静态数据
    public function every_hour_date($begin_hour, $end_hour)
    {
        $date = $this->input->get('data');
        ($date) ? $date : $date = date('Y-m-d', time());

        $_begin_hour = strtotime($date . " $begin_hour:0:0");
        $_end_hour = strtotime($date . " $end_hour:0:0");
        if ($_begin_hour == $_end_hour) {
            $_end_hour += 86400;
        }
        $games = $this->Game_model->get_by_condition();
        $game_fathers = $this->Game_father_model->get_by_condition();

        foreach ($games as $one) {
            $hour_insert_date = array(
                'platform' => '0',
                'platform_name' => '0',
                'cishu' => '0',
                'renshu' => '0',
                'login' => '0',
                'createrole' => '0',
                'sign_report' => '0',
                'fufeilv' => '0',
                'arpu' => '0',
                'arpuu' => '0',
                'create_date' => '0',
                'money' => '0',
                'begin_time' => '0',
                'end_time' => '0',
                'game_father_id' => '0',
            );
            $platform_condition = array(
                'platform' => $one->platform,
            );
            $_req = $this->Platform_model->get_one_by_condition($platform_condition);
            $hour_insert_date['platform'] = $one->platform;
            $hour_insert_date['platform_name'] = $_req->platform_chinese;
            $hour_insert_date['game_father_id'] = $one->game_father_id;
            $hour_insert_date['begin_time'] = $date . " $begin_hour:0:0";
            $hour_insert_date['end_time'] = $date . " $end_hour:0:0";
            $hour_insert_date['create_date'] = $_begin_hour;
            $login_info_condition = array(
                'create_date > ' => $_begin_hour,
                'create_date <= ' => $_end_hour,
                'platform ' => $one->platform,
                'game_father_id' => $one->game_father_id,
            );
            //统计登录
            $this->db->select('count(DISTINCT(user_id)) as login_count ,  platform ,game_father_id');
            $login_reqery = $this->Login_report_model->get_one_by_condition($login_info_condition);
            if ($login_reqery->login_count) {
                $hour_insert_date['login'] = $login_reqery->login_count;
            }
            //统计创角
            $this->db->select('count(DISTINCT(user_id)) as create_role_count ,  platform ,game_father_id');
            $create_role_requery = $this->Create_role_report_model->get_one_by_condition($login_info_condition);
            if ($create_role_requery->create_role_count) {
                $hour_insert_date['createrole'] = $create_role_requery->create_role_count;
            }
            //统计新增
            $this->db->select('count((user_id)) as sign_report ,  platform ,game_father_id');
            $sign_role_requery = $this->Sign_report_model->get_one_by_condition($login_info_condition);
            if ($sign_role_requery->sign_report) {
                $hour_insert_date['sign_report'] = $sign_role_requery->sign_report;
            }
            $order_info_condition = array(
                'create_date >= ' => $_begin_hour,
                'create_date <= ' => $_end_hour,
                'platform ' => $one->platform,
                'game_father_id' => $one->game_father_id,
                'status' => 2,
            );
            $this->db->select('platform,count(DISTINCT(`user_id`)) as renshu ,sum(`money`) as money ,count(`order_id`) as cishu ');
            $game_order_requery = $this->Game_order_model->get_one_by_condition($order_info_condition);
            if ($game_order_requery->cishu && $game_order_requery->renshu && $game_order_requery->money) {
                $hour_insert_date['cishu'] = $game_order_requery->cishu;
                $hour_insert_date['renshu'] = $game_order_requery->renshu;
                $hour_insert_date['money'] = $game_order_requery->money;
            }
            if ($hour_insert_date['login'] && $hour_insert_date['renshu'] && $hour_insert_date['money']) {
                $hour_insert_date['fufeilv'] = number_format($hour_insert_date['renshu'] / $hour_insert_date['login'], 2);
                $hour_insert_date['arpu'] = number_format($hour_insert_date['money'] / $hour_insert_date['login'] / 100, 2);
                $hour_insert_date['arpuu'] = number_format($hour_insert_date['money'] / $hour_insert_date['renshu'] / 100, 2);
            }

            //check info
            $check_info_condition = array(
                'platform' => $hour_insert_date['platform'],
                'begin_time' => $hour_insert_date['begin_time'],
                'game_father_id' => $hour_insert_date['game_father_id'],
            );

            $check_requery = $this->Stage_hour_data_model->get_one_by_condition($check_info_condition);
            if ($check_requery) {
                $response = $this->Stage_hour_data_model->update($hour_insert_date, $check_info_condition);
            } else {
                $response = $this->Stage_hour_data_model->add($hour_insert_date);
            }
        }
        $this->Output_model->json_print(0, 'ok');
    }

    /*
     * turn_to_server_info_page
     * 区服统计
     * 获取分服数据
     */
    public function get_server_info()
    {
        $select_date = $this->input->get('start');
        ($select_date) ? '' : $this->Output_model->json_print(1, 'data is null');
        $end_date = $this->input->get('end');
        $create_role_start_time = $this->str_to_zero_time($select_date);
        ($end_date) ? $create_role_end_time = $this->str_to_zero_time($end_date) : $create_role_end_time = $create_role_start_time + 86400;

        $game_father_id = $this->input->get('game_father_id');
        $platform = $this->input->get('platform');

        $this->load->model('login_report_model');
        $this->load->model('Game_order_model');

        $days = ($create_role_end_time - $create_role_start_time) / 86400;
        $this->db->select('max(`ext`) as maxserver');
        $_max_server = $this->Game_order_model->get_one_by_condition(array('game_father_id=' => $game_father_id));
        $max_server = $_max_server->maxserver;
        if ($max_server >= 1000) {
            $max_server = 1000;
        }
        $server_begin = 1;
        if ($game_father_id == '20014') {
            $server_begin = '100001';
        }
        $one_day_info = array();

        // echo $days;

        for ($_days = 0; $_days < $days; $_days++) {
            $_create_role_start_time = $create_role_start_time + (86400 * $_days);
            $_create_role_end_time = $_create_role_start_time + 86400;

            $condition = array(
                'create_date >= ' => $_create_role_start_time,
                'create_date <= ' => $_create_role_end_time,
            );
            ($game_father_id) ? $condition['game_father_id'] = $game_father_id : '';
            ($platform) ? $condition['platform'] = $platform : '';
            $game_order_condition = $game_login_createrole_condition = $condition;
            // $game_login_createrole_condition = $condition;

            for ($one = $server_begin; $one <= $max_server; $one++) {
                $this->db->select('COUNT(`order_id`) as cishu , COUNT(DISTINCT(`user_id`)) as renshu , SUM(`money`) as money ,ext');
                $game_order_condition['ext'] = $one;
                $game_order_condition['status'] = 2;
                $game_order_request = $this->Game_order_model->get_one_by_condition($game_order_condition);

                $game_login_createrole_condition['server_id'] = $one;
                $this->db->select('COUNT(DISTINCT(`cproleid`)) as zhuce');
                $create_role_request = $this->Create_role_report_model->get_one_by_condition($game_login_createrole_condition);

                $this->db->select('COUNT(DISTINCT(`cproleid`)) as denglu');
                $login_role_request = $this->login_report_model->get_one_by_condition($game_login_createrole_condition);

                $this->db->select(' sum(`money`) as money ,count(DISTINCT(`game_order`.`user_id`)) as xinfufeirenshu');
                $_join_on_str = "sign_report.user_id = game_order.user_id  and game_order.status = 2 and game_order.ext = '$one' and game_order.create_date >= '$_create_role_start_time' and game_order.create_date <= '$_create_role_end_time' ";
                //将 signreport userid 去重
                ($game_father_id) ? $_join_on_str .= "and game_order.game_father_id = '$game_father_id' " : '';
                ($platform) ? $_join_on_str .= " and game_order.platform = '$platform' " : '';

                $_join_table_str = "( select DISTINCT(`user_id`) as `user_id` from sign_report where sign_report.create_date >= '$_create_role_start_time' and sign_report.create_date <= '$_create_role_end_time'  ";
                ($game_father_id) ? $_join_table_str .= "and game_father_id = '$game_father_id' " : '';
                ($platform) ? $_join_table_str .= " and platform = '$platform' " : '';
                $_join_table_str .= ") as sign_report";

                $this->db->join($_join_table_str, $_join_on_str, "INNER");
                $new_user_pay_sum = $this->Game_order_model->get_one_by_condition("");

                // and `game_order`.`create_date` >= '1529510400' and `game_order`.`create_date` <= '1529596800'

                $this->db->select('COUNT(DISTINCT(`cproleid`)) as xinchuangjue ');
                $__join_on_str = "sign_report.user_id = create_role_report.user_id   and create_role_report.server_id = '$one' and create_role_report.create_date >= '$_create_role_start_time' and create_role_report.create_date <= '$_create_role_end_time'";
                ($game_father_id) ? $__join_on_str .= "and create_role_report.game_father_id = '$game_father_id'" : '';
                ($platform) ? $__join_on_str .= " and create_role_report.platform = '$platform'  " : '';

                $__join_table_str = "( select DISTINCT(`user_id`) as `user_id` from sign_report where create_date >= '$_create_role_start_time' and create_date <= '$_create_role_end_time'  ";
                ($game_father_id) ? $__join_table_str .= "and game_father_id = '$game_father_id' " : '';
                ($platform) ? $__join_table_str .= " and platform = '$platform' " : '';
                $__join_table_str .= ") as sign_report";
                $this->db->join($__join_table_str, $__join_on_str, 'INNER');
                $new_user_create_role_sum = $this->Create_role_report_model->get_one_by_condition("");

                $server_info = array(
                    'date' => date('Y-m-d', $_create_role_start_time),
                    'server_id' => $one,
                    'cishu' => $game_order_request->cishu,
                    'renshu' => $game_order_request->renshu,
                    'money' => $game_order_request->money,
                    'zhuce' => $create_role_request->zhuce,
                    'denglu' => $login_role_request->denglu,
                    'new_pay_user_sum' => $new_user_pay_sum->xinfufeirenshu,
                    'new_user_sum_money' => $new_user_pay_sum->money,
                    'new_user_sum_create_role' => $new_user_create_role_sum->xinchuangjue,
                );
                ($game_order_request->cishu) ? $server_info['cishu'] = $game_order_request->cishu : $server_info['cishu'] = 0;
                ($game_order_request->renshu) ? $server_info['renshu'] = $game_order_request->renshu : $server_info['renshu'] = 0;
                ($game_order_request->money) ? $server_info['money'] = $game_order_request->money : $server_info['money'] = 0;
                ($create_role_request->zhuce) ? $server_info['zhuce'] = $create_role_request->zhuce : $server_info['zhuce'] = 0;
                ($login_role_request->denglu) ? $server_info['denglu'] = $login_role_request->denglu : $server_info['denglu'] = 0;
                ($new_user_pay_sum->xinfufeirenshu) ? $server_info['new_pay_user_sum'] = $new_user_pay_sum->xinfufeirenshu : $server_info['new_pay_user_sum'] = 0;
                ($new_user_pay_sum->money) ? $server_info['new_user_sum_money'] = $new_user_pay_sum->money : $server_info['new_user_sum_money'] = 0;
                ($new_user_create_role_sum->xinchuangjue) ? $server_info['new_user_sum_create_role'] = $new_user_create_role_sum->xinchuangjue : $server_info['new_user_sum_create_role'] = 0;
                array_push($one_day_info, $server_info);
                // echo json_encode($server_info);
                // echo '<br>';
            }
        }
        $this->Output_model->json_print(0, 'ok', array_reverse($one_day_info));
    }

    /*
     * turn_to_month_data_page
     * 月总统计
     */
    public function get_game_father_data()
    {
        $game_father_id = $this->input->get('game_father_id');
        if (isset($game_father_id) && $game_father_id) {
            $game_fathers = $this->Game_father_model->get_by_condition(array('game_father_id' => $game_father_id));
        } else {
            $game_fathers = $this->Game_father_model->get_by_condition();
        }
        $data = array(
            'game_fathers' => $game_fathers,
        );
        $this->Output_model->json_print(0, 'ok', $data);
    }

    public function get_data_by_month()
    {
        $platform_info_by_day = array();
        $date = $this->input->get('start');
        $game_father_id = $this->input->get('game_father_id');
        if (!$date) {
            $this->Output_model->json_print(1, 'data is null');
            return;
        }
        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');
            return;
        }
        $total_money = 0;
        $this->load->model('platform_data_model');
        $this->load->model('Month_data_model');
        $platform = $this->Platform_model->get_by_condition();
        foreach ($platform as $one) {
            $platform = $one->platform;
            $condition = array(
                'create_date' => $current_date,
                'platform_name' => $platform,
                'game_father_id' => $game_father_id,
            );
            $platform_data = $this->Month_data_model->get_by_condition($condition)[0];
            // echo $this->db->last_query();
            $platform_chinese_name_condition = array(
                'platform' => $platform,
            );
            $this->db->select('platform_chinese');
            $platform_chinese_name = $this->Platform_model->get_one_by_condition($platform_chinese_name_condition);
            if (isset($platform_chinese_name) && $platform_data) {
                $platform_data->platform_chinese_name = $platform_chinese_name->platform_chinese;
            } elseif (!$platform_data) {
                continue;
            } else {
                $platform_data->platform_chinese_name = $platform;
            }
            array_push($platform_info_by_day, $platform_data);
            if (isset($platform_data->money)) {
                $total_money += $platform_data->money;
            }
        }

        $data = array(
            'info' => $platform_info_by_day,
            'total_money' => $total_money,
        );
        $this->Output_model->json_print(0, 'ok', $data);
    }

    /*
     * turn_to_liucun_page
     * 留存统计
     */

    //获取留存数据
    public function get_exist_info()
    {
        $select_date = $this->input->get('start');
        ($select_date) ? '' : $this->Output_model->json_print(2, 'data is null');
        $end_date = $this->input->get('to');
        $create_role_start_time = $this->str_to_zero_time($select_date);
        ($end_date) ? $create_role_end_time = $this->str_to_zero_time($end_date) : $create_role_end_time = $create_role_start_time + 86400;
        $days = ($create_role_end_time - $create_role_start_time) / 86400;
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');
        $server_id = $this->input->get('server_id');
        $all_data = array();
        $this->load->model('Sign_report_model');
        for ($_days = 0; $_days < $days; $_days++) {
            $_create_role_start_time = $create_role_start_time + (86400 * $_days);
            $create_role_end_time = $_create_role_start_time + 86400;
            $liucun_data = array();
            for ($i = 0; $i < 90; $i++) {
                $this->db->select('count(DISTINCT(login_report.cproleid)) as liucun');
                $this->db->from('create_role_report');
                $next_day_start = $create_role_end_time + (86400 * $i);
                $next_day_end = $create_role_end_time + 86400 + (86400 * $i);
                //拼接sql
                $_join_str = "`create_role_report`.`create_date` >='$_create_role_start_time' and `create_role_report`.`create_date` <= '$create_role_end_time' and `create_role_report`.`cproleid` = `login_report`.`cproleid` and  `create_role_report`.`cproleid` != 'undefined' and `login_report`.`create_date` >='$next_day_start' and `login_report`.`create_date` <= '$next_day_end'";
                if ($game_father_id) {
                    $_join_str .= " and `create_role_report`.`game_father_id` = '$game_father_id' ";
                }
                if ($platform) {
                    $_join_str .= " and `create_role_report`.`platform` = '$platform' ";
                }
                if ($server_id) {
                    $_join_str .= " and `create_role_report`.`server_id` = '$server_id' ";
                }
                $this->db->join('login_report', $_join_str, 'inner');
                $request = $this->db->get();
                $liucun = ($request->result()[0]);
                $date_num = $i + 1;
                $liucun_data['date' . $date_num] = $liucun->liucun;
            };
            //获取注册数和创角数
            $_response = $this->get_sign_in_and_create_role_info($_create_role_start_time, $create_role_end_time, $game_father_id, $platform, $server_id);
            $liucun_data['sign'] = $_response['sign_in'];
            $liucun_data['create_role'] = $_response['create_role'];
            //填写时间
            $_output_time = $this->str_to_zero_time($select_date) + (86400 * $_days);
            $liucun_data['date'] = date('Y-m-d', $this->str_to_zero_time($select_date) + (86400 * $_days));
            $all_data[$_days + 1] = $liucun_data;
        }
        if ($all_data) {
            $this->Output_model->json_print(0, 'ok', array_reverse($all_data));
        } else {
            $this->Output_model->json_print(1, 'db data is null');
        }
    }

    //获取注册数和创角数
    private function get_sign_in_and_create_role_info($start_time, $end_time, $game_father_id = null, $platform = null, $server_id = null)
    {
        $condition = array();
        $this->load->model('Sign_report_model');
        $condition['create_date >= '] = $start_time;
        $condition['create_date <= '] = $end_time;
        $game_father_id ? $condition['game_father_id'] = $game_father_id : '';
        $platform ? $condition['platform'] = $platform : '';
        $server_id ? $condition['server_id'] = $server_id : '';
        $this->db->select('count(DISTINCT(`user_id`)) as sign');
        $sign_in = $this->Sign_report_model->get_one_by_condition($condition)->sign;
        $this->db->select('count(DISTINCT(`cproleid`)) as create_role');
        $create_role = $this->Create_role_report_model->get_one_by_condition($condition)->create_role;
        $response = array(
            'sign_in' => $sign_in,
            'create_role' => $create_role,
        );
        return $response;
    }

    /*
     * turn_to_ltv_page
     * ltv统计
     */

    //获取ltv数据
    public function get_ltv_info()
    {
        $select_date = $this->input->get('start');
        ($select_date) ? '' : $this->Output_model->json_print(2, 'data is null');
        $end_date = $this->input->get('end');
        $create_role_start_time = $this->str_to_zero_time($select_date);
        ($end_date) ? $create_role_end_time = $this->str_to_zero_time($end_date) : $create_role_end_time = $create_role_start_time + 86400;
        $days = ($create_role_end_time - $create_role_start_time) / 86400;
        $platform = $this->input->get('platform');
        $game_father_id = $this->input->get('game_father_id');
        $server_id = $this->input->get('server_id');
        $all_data = array();
        $this->load->model('Sign_report_model');
        for ($_days = 0; $_days < $days; $_days++) {
            $_create_role_start_time = $create_role_start_time + (86400 * $_days);
            $create_role_end_time = $_create_role_start_time + 86400;
            $liucun_data = array();
            for ($i = 0; $i < 90; $i++) {
                $date_num = $i + 1;
                $next_day_start = $create_role_end_time + (86400 * $i);
                $next_day_end = $create_role_end_time + (86400 * $i);
                if ($next_day_end >= time() + 86400) {
                    $liucun_data['date' . $date_num] = 0;
                    continue;
                };
                $this->db->select('sum(`money`) as money');
                $this->db->from('create_role_report');
                //拼接sql
                $_join_str = "`game_order`.`status` ='2' and `create_role_report`.`create_date` >='$_create_role_start_time' and `create_role_report`.`create_date` <= '$create_role_end_time' and `create_role_report`.`cproleid` = `game_order`.`cproleid` and  `create_role_report`.`cproleid` != 'undefined' and `game_order`.`create_date` >='$_create_role_start_time' and `game_order`.`create_date` <= '$next_day_end'";
                if ($game_father_id) {
                    $_join_str .= " and `game_order`.`game_father_id` = '$game_father_id' ";
                }
                if ($platform) {
                    $_join_str .= " and `game_order`.`platform` = '$platform' ";
                }
                if ($server_id) {
                    $_join_str .= " and `game_order`.`ext` = '$server_id' ";
                }
                $this->db->join('game_order', $_join_str, 'inner');
                $request = $this->db->get();
                $liucun = ($request->result()[0]);
                if ($next_day_end > strtotime(date('Y-m-d', time())) + 86400) {
                    $liucun_data['date' . $date_num] = 0;
                } else {
                    $liucun_data['date' . $date_num] = $liucun->money / 100;
                }
            };
            //获取注册数和创角数
            $_response = $this->get_sign_in_and_create_role_info($_create_role_start_time, $create_role_end_time, $game_father_id, $platform, $server_id);
            $liucun_data['sign'] = $_response['sign_in'];
            $liucun_data['create_role'] = $_response['create_role'];
            //填写时间
            $_output_time = $this->str_to_zero_time($select_date) + (86400 * $_days);
            $liucun_data['date'] = date('Y-m-d', $this->str_to_zero_time($select_date) + (86400 * $_days));
            $all_data[$_days + 1] = $liucun_data;
        }
        if ($all_data) {
            $this->Output_model->json_print(0, 'ok', array_reverse($all_data));
        } else {
            $this->Output_model->json_print(1, 'db data is null');
        }
    }

    /*
     * check_orderId_page
     * 检查订单号
     * start
     */
    public function check_orderId_api()
    {
        $orderid = $this->input->get('user_order_id');
        if (!$orderid) {
            $this->Output_model->json_print(2, 'data is null');
            return;
        }
        $this->load->model('Game_order_model');
        $condition = array(
            'u_order_id' => $orderid,
        );
        $response = $this->Game_order_model->get_by_condition($condition);
        if (!$response) {
            $this->Output_model->json_print(1, 'db data is null');
            return;
        }
        // 查找渠道中文名
        $condition = array(
            'platform' => $response[0]->platform,
        );
        $this->db->select('platform_chinese');
        $platform_chinese_name = $this->Platform_model->get_by_condition($condition);
        if (isset($platform_chinese_name) && $response) {
            $response[0]->platform_chinese = $platform_chinese_name[0]->platform_chinese;
        } else {
            $response[0]->platform_chinese = $response[0]->platform;
        }

        $this->Output_model->json_print(0, 'ok', $response);
    }

    /*
     * user_controller
     * 玩家管理
     */

    // 查询玩家信息
    public function user_search()
    {
        /**
         * 查询：用户信息, 创角信息, 订单信息, 登录信息
         */
        $type = $this->input->get('type');
        // 判断
        if (!$type) {
            $this->Output_model->json_print(2, 'err: no type');
            exit;
        } else {
            switch ($type) {
                case 'p_uid':
                    $p_uid = $this->input->get('p_uid');
                    if (!$p_uid) {
                        $this->Output_model->json_print(1, 'no p_uid');
                        exit;
                    } else {
                        $this->user_search_uid('p_uid', $p_uid);
                    }
                    break;
                case 'user_id':
                    $user_id = $this->input->get('user_id');
                    if (!$user_id) {
                        $this->Output_model->json_print(1, 'no user_id');
                        exit;
                    } else {
                        $this->user_search_uid('user_id', $user_id);
                    }
                    break;
                case 'cproleid':
                    $cproleid = $this->input->get('cproleid');
                    if (!$cproleid) {
                        $this->Output_model->json_print(1, 'no cproleid');
                        exit;
                    } else {
                        $this->user_search_cproleid('cproleid', $cproleid);
                    }
                    break;
                case 'nickname':
                    $nickname = $this->input->get('nickname');
                    if (!$nickname) {
                        $this->Output_model->json_print(1, 'no nickname');
                        exit;
                    } else {
                        $this->user_search_nickname('nickname', $nickname);
                    }
                    break;
                default:
                    $this->Output_model->json_print(2, 'orther type: ' . $type);
                    exit;
                    break;
            }
        }
    }

    private function search_create_role($condition)
    {
        return $this->Create_role_report_model->get_by_condition($condition);
    }

    /**
     * 查询：创角信息
     * cproleid
     */
    private function user_search_cproleid($type, $val)
    {
        $condition = array(
            $type => $val,
        );

        /** Create_role_report_model 创角信息 **/
        $reports = $this->Create_role_report_model->get_by_condition($condition);
        log_message('debug', $this->BACKSTAGE . ' user_search_cproleid  ' . $type . ': ' . json_encode($val) . ' sql: ' . json_encode($this->db->last_query()));
        // 赋值
        $data = array();
        if (!$reports) {
            // $this->Output_model->json_print(3, 'db no cproleid ' . json_encode($val));
            $data['reports'] = array();
        } else {
            $data['reports'] = $this->get_parameter_to_reports($type, $val, $reports);
        }
        $data['orders'] = array(); /** 订单信息 **/
        $data['user'] = array(); /** 用户信息 **/
        $data['user'] = $reports;
        $data['login_report'] = array(); /** 登录信息 **/

        $this->Output_model->json_print(0, 'ok', $data);
    }

    /**
     * 查询：用户信息, 创角信息, 登录信息
     * nickname
     */
    private function user_search_nickname($type, $val)
    {
        $condition = array(
            $type => $val,
        );

        /** Create_role_report_model 创角信息 **/
        $reports = $this->Create_role_report_model->get_by_condition($condition);
        log_message('debug', $this->BACKSTAGE . ' user_search_nickname ' . $type . ': ' . json_encode($val) . ' sql: ' . json_encode($this->db->last_query()));

        // 赋值
        $data = array();
        if (!$reports) {
            // $this->Output_model->json_print(3, 'db no nickname ' . json_encode($val));
            $data['reports'] = array();
        } else {
            $data['reports'] = $this->get_parameter_to_reports($type, $val, $reports);
        }

        /** 订单信息 **/
        $data['orders'] = array();

        /** User_model 用户信息 **/
        $user_id_array = array();
        foreach ($reports as $one) {
            array_push($user_id_array, $one->user_id);
        }
        $this->db->where_in('user_id', $user_id_array);
        $user = $this->User_model->get_by_condition();
        log_message('debug', $this->BACKSTAGE . ' user_search_nickname sql ' . json_encode($this->db->last_query()));
        if (!$user) {
            $data['user'] = array();
        } else {
            $data['user'] = $user;
        }

        /** Login_report_model 登录信息 **/
        $this->db->order_by('create_date', 'desc');
        $login_report = $this->Login_report_model->get_by_condition($condition);
        log_message('debug', $this->BACKSTAGE . ' user_search_nickname  ' . $type . ': ' . json_encode($val) . ' sql: ' . json_encode($this->db->last_query()));
        if (!$login_report) {
            // $this->Output_model->json_print(3, 'db login_report_model no nickname');
            $data['login_report'] = array();
        } else {
            $login_array = array();
            $date = '';
            foreach ($login_report as $one) {
                if ($date != date('Y-m-d', $one->create_date)) {
                    array_push($login_array, $one);
                    $date = date('Y-m-d', $one->create_date);
                } else {
                    continue;
                }
            }
            $data['login_report'] = $login_array;
        }

        $this->Output_model->json_print(0, 'ok', $data);
    }

    /**
     * 查询：用户信息, 创角信息, 订单信息, 登录信息
     * user_id、p_uid
     */
    private function user_search_uid($type, $val)
    {
        $condition = array(
            $type => $val,
        );
        /** User_model 用户信息 **/
        $user = $this->User_model->get_one_by_condition($condition);
        log_message('debug', $this->BACKSTAGE . ' user_search_uid ' . $type . ': ' . json_encode($val) . ' sql: ' . json_encode($this->db->last_query()));
        if (!$user) {
            $this->Output_model->json_print(3, 'db user_model no ' . $type . ' ' . json_encode($val));
            exit;
        }
        // 赋值
        $user_arr = array($user);
        $data = array(
            'user' => $user_arr,
        );

        /** Create_role_report_model 创角信息 **/
        $reports = $this->search_create_role($condition);
        log_message('debug', $this->BACKSTAGE . ' user_search_uid sql ' . json_encode($this->db->last_query()));
        if (!$reports) {
            // $this->Output_model->json_print(3, 'db create_role_report_model no p_uid');
            $data['reports'] = array();
        } else {
            $data['reports'] = $this->get_parameter_to_reports($type, $val, $reports);
        }

        /** Game_order_model 订单信息 **/
        $condition = array(
            'user_id' => $user->user_id,
        );
        $orders = $this->Game_order_model->get_by_condition($condition);
        log_message('debug', $this->BACKSTAGE . ' user_search_uid user_id: ' . json_encode($user->user_id) . ' sql: ' . json_encode($this->db->last_query()));
        if (!$orders) {
            // $this->Output_model->json_print(3, 'db game_order_model no user_id');
            $data['orders'] = array();
        } else {
            $data['orders'] = $orders;
        }

        /** Login_report_model 登录信息 **/
        $this->db->order_by('create_date', 'desc');
        $login_report = $this->Login_report_model->get_by_condition($condition);
        log_message('debug', $this->BACKSTAGE . ' user_search_uid user_id: ' . json_encode($user->user_id) . ' sql: ' . json_encode($this->db->last_query()));
        if (!$login_report) {
            // $this->Output_model->json_print(3, 'db login_report_model no user_id');
            $data['login_report'] = array();
        } else {
            $login_array = array();
            $date = '';
            foreach ($login_report as $one) {
                if ($date != date('Y-m-d', $one->create_date)) {
                    array_push($login_array, $one);
                    $date = date('Y-m-d', $one->create_date);
                } else {
                    continue;
                }
            }
            $data['login_report'] = $login_array;
        }

        $this->Output_model->json_print(0, 'ok', $data);
    }

    /**
     * 查询：创角信息
     * 增加 game_father_name、illegal_status
     */
    private function get_parameter_to_reports($type, $val, $reports)
    {
        $game_fathers = $this->Game_father_model->get_by_condition();
        log_message('debug', $this->BACKSTAGE . ' user_search_cproleid ' . $type . ': ' . $val . ' sql: ' . json_encode($this->db->last_query()));
        foreach ($reports as $one) {
            // 封号查询
            $_condition = array(
                'user_id' => $one->user_id,
                'p_uid' => $one->p_uid,
                'platform' => $one->platform,
                'game_id' => $one->game_id,
                'game_father_id' => $one->game_father_id,
            );
            $requery = $this->Illegal_user_model->get_one_by_condition($_condition);
            if (!$requery) {
                $one->illegal_status = '0';
            } else {
                $one->illegal_status = $requery->status;
            }
            // 赋值 game_father_name
            foreach ($game_fathers as $two) {
                if ($one->game_father_id == $two->game_father_id) {
                    $one->game_father_name = $two->game_father_name;
                }
            }
        }
        return $reports;
    }

    /**
     * 对玩家进行封号处理
     * 判断
     */
    public function illegal_user($game_id, $user_id, $p_uid, $platform, $game_father_id)
    {
        $token = $_SERVER['HTTP_X_TOKEN'];
        $cache_driver = $this->CACHE_DRIVER;
        $admin_info = $this->cache->$cache_driver->get($token);
        if (!$game_id || !$user_id || !$p_uid || !$platform || !$game_father_id) {
            $this->Output_model->json_print(2, '参数不足');
            exit;
        }
        $my_roles = json_decode($admin_info->roles);
        foreach ($my_roles as $one) {
            if ($one === 'admin') {
                $this->change_user_to_illegal($game_id, $user_id, $p_uid, $platform, $game_father_id, $admin_info);
            }
        }
    }

    /**
     * 对玩家进行封号处理
     * 执行
     */
    private function change_user_to_illegal($game_id, $user_id, $p_uid, $platform, $game_father_id, $admin_info)
    {
        $done = false;
        $data = array(
            'user_id' => $user_id,
            'p_uid' => $p_uid,
            'platform' => $platform,
            'game_id' => $game_id,
            'create_date' => time(),
            'game_father_id' => $game_father_id,
            'performer_admin' => $admin_info->admin_user_name,
            'status' => 1,
        );

        ($this->input->get('cproleid')) ? $data['cproleid'] = $this->input->get('cproleid') : '';
        ($this->input->get('nickname')) ? $data['nickname'] = $this->input->get('nickname') : '';

        $condition = array(
            'user_id' => $user_id,
            'p_uid' => $p_uid,
            'platform' => $platform,
            'game_id' => $game_id,
            'game_father_id' => $game_father_id,
        );
        $requery = $this->Illegal_user_model->get_one_by_condition($condition);

        if (!$requery) {
            $this->Illegal_user_model->add($data);
            $done = true;
        } else {
            $this->Illegal_user_model->update(array('status' => 1), $condition);
            $done = true;
        }
        if ($done) {
            $illegal_user_map = $this->Illegal_user_model->get_by_condition();
            $cache_driver = $this->CACHE_DRIVER;
            $requery = $this->cache->$cache_driver->save('illegal_user_map', json_encode($illegal_user_map), 86400 * 30);
            if ($requery) {
                $this->Output_model->json_print(0, 'ok');
            } else {
                $this->Output_model->json_print(3, 'cache save error');
                log_message('debug', $this->BACKSTAGE . ' ' . date('Y-m-d', time()) . ' save cache error');
            }
        } else {
            $this->Output_model->json_print(2, 'sth err');
        }
    }

    /**
     * assign_account_permissions
     * 分配账号权限
     */

    private function get_admin_user()
    {
        // SELECT `admin_user_name` FROM `admin_user`
        $this->db->select('admin_user_name');
        $user_name = $this->Admin_user_model->get_by_condition();
        $user_name_array = array();
        if (!$user_name) {
            return false;
        }
        foreach ($user_name as $one) {
            array_push($user_name_array, $one->admin_user_name);
        }
        return $user_name_array;
    }

    private function get_game_father()
    {
        $this->db->select('game_father_id,game_father_name');
        $game_father_data = $this->Game_father_model->get_by_condition();
        if (!$game_father_data) {
            return false;
        }
        return $game_father_data;
    }

    private function get_platform()
    {
        $this->db->select('platform,platform_chinese');
        $platform_data = $this->Platform_model->get_by_condition();
        if (!$platform_data) {
            return false;
        }
        return $platform_data;
    }

    public function get_permissions()
    {
        $game_father_data = $this->get_game_father();
        $platform_data = $this->get_platform();

        if (!$game_father_data || !$platform_data) {
            $this->Output_model->json_print(1, 'db no data');
            exit;
        }

        $data = array(
            'game_father' => $game_father_data,
            'platform' => $platform_data,
        );

        $this->Output_model->json_print(0, 'ok', $data);
    }

    public function set_admin_user()
    {
        $admin_user_name = $this->input->get('admin_user_name');
        $admin_user_password = $this->input->get('admin_user_password');
        $roles = $this->input->get('roles');
        $name = $this->input->get('name');
        $avatar = $this->input->get('avatar');

        if (!$admin_user_name || !$admin_user_password || !$roles || !$name) {
            $this->Output_model->json_print(1, 'data is null');
            exit;
        }

        $user_name_array = $this->get_admin_user();

        foreach ($user_name_array as $one) {
            if ($admin_user_name == $one) {
                $this->Output_model->json_print(2, 'user name already exists');
                exit;
            }
        }

        if (!$avatar || $avatar == 'no') {
            $avatar = 'https://h5sdk.cdn.zytxgame.com/img/icon/girl.png';
        }

        $data = array(
            'admin_user_name' => $admin_user_name,
            'admin_user_password' => md5($admin_user_password . $this->Admin_user_model->ADMIN_SALT),
            'admin_user_role' => 'admin',
            'create_date' => time() . '',
            'roles' => $roles,
            'name' => $name,
            'avatar' => $avatar,
            'introduction' => '1',
            'includes' => 'admin',
        );

        $reports = $this->Admin_user_model->add($data);

        if ($reports !== 0) {
            $this->Output_model->json_print(0, 'ok', $reports);
            exit;
        } else {
            $this->Output_model->json_print(3, 'db err');
            exit;
        }
    }

    /**
     * index
     * 首页
     */
    public function get_past_days_all_data()
    {
        $dashboard_data = $this->cache->get('dashboard_data_' . date('Y-m-d', time()));
        if (!$dashboard_data) {
            $all_game_father = $this->Game_father_model->get_by_condition();
            $to_day = strtotime(date('Y-m-d', time()));
            $day = 6;
            $sum_money_data = array();
            foreach ($all_game_father as $one) {
                $game_money_data = array();
                $game_money_data['money'] = array();
                $game_money_data['cishu'] = array();
                $game_money_data['createrole'] = array();
                $game_money_data['login'] = array();
                do {
                    $to_day = $to_day - 86400;
                    $condition = array(
                        'create_date' => $to_day,
                        'game_father_id' => $one->game_father_id,
                    );
                    $request = $this->Stage_hour_data_model->get_by_condition($condition);
                    $money = 0;
                    $cishu = 0;
                    $createrole = 0;
                    $login = 0;

                    if ($request) {
                        foreach ($request as $one) {
                            ($one->money) ? $money += $one->money / 100 : '';
                            ($one->cishu) ? $cishu += $one->cishu : '';
                            ($one->createrole) ? $createrole += $one->createrole : '';
                            ($one->login) ? $login += $one->login : '';
                        }
                    }

                    array_push($game_money_data['money'], $money);
                    array_push($game_money_data['cishu'], $cishu);
                    array_push($game_money_data['createrole'], $createrole);
                    array_push($game_money_data['login'], $login);
                    $day--;
                } while ($day >= 0);

                $sum_money_data['data_' . $one->game_father_id] = $game_money_data;
                $day = 6;
                $to_day = strtotime(date('Y-m-d', time()));
            }
            $this->cache->save('dashboard_data_' . date('Y-m-d', time()), $sum_money_data, 60 * 60 * 24);
        } else {
            $sum_money_data = $dashboard_data;
        }
        $this->Output_model->json_print(0, 'ok', $sum_money_data);
    }

    /**
     * platform_createpay_control
     * 渠道充值审批
     */
    public function get_order_fuli_list()
    {
        $this->db->order_by('create_date', 'desc'); // 倒序
        $time = time() - (86400 * 60);
        $orders = $this->Game_order_fuli_model->get_by_condition(array('create_date >=' => $time));
        $this->Output_model->json_print(0, 'ok', $orders);
    }

    // 同意福利申请审批接口 agree_fuli_request
    public function change_platform_status()
    {
        $platform = $this->input->get_post('platform');
        $game_id = $this->input->get_post('game_id');
        $money = $this->input->get_post('money');
        $order_id = $this->input->get_post('order_id');
        // 根据 X_TOKEN 获取用户信息
        if ($this->CACHE_DRIVER) {
            $token = $_SERVER['HTTP_X_TOKEN'];
            if (!$token) {
                $this->Output_model->json_print(3, 'no token');
                exit;
            }
            $cache_driver = $this->CACHE_DRIVER;
            $admin_info = $this->cache->$cache_driver->get($token);
            if (!isset($admin_info) || !$admin_info) {
                $this->Output_model->json_print(2, 'no admin info');
                exit;
            } elseif (json_decode($admin_info->roles)[0] != 'admin') {
                $this->Output_model->json_print(2, 'not admin ');
                exit;
            }
        }
        $where = array('u_order_id' => $order_id);
        $data = array('status' => $this->Game_order_model->PAYED_STATUS);
        $this->Game_order_model->update($data, $where);
        $res = $this->Common_model->notify($order_id);
        if ($res == 'success') {
            $data = array(
                'handler_status' => 0,
                'handler_name' => $admin_info->name,
                'status' => $this->Game_order_fuli_model->NOTIFIED_STATUS,
                'set_time' => time(),
            );
            $where = array(
                'u_order_id' => $order_id,
            );
            $requery = $this->Game_order_fuli_model->update($data, $where);
            $this->Output_model->json_print(0, 'ok', $requery);
        } else {
            $this->Output_model->json_print(1, 'err', $res);
        }
    }

    /**
     * player_account_exchange
     * 玩家账号互换
     */
    public function get_platform_data()
    {
        $data = $this->get_platform();
        if (!$data) {
            $this->Output_model->json_print(1, 'db no data');
            exit;
        }
        $this->Output_model->json_print(0, 'ok', $data);
    }

    public function change_player_account()
    {
        $p_uid_old = $this->input->post('p_uid_A');
        $p_uid_new = $this->input->post('p_uid_B');
        $platform_old = $this->input->post('platform_A');
        $platform_new = $this->input->post('platform_B');

        if (!$p_uid_old || !$p_uid_new || !$platform_old || !$platform_new) {
            $this->Output_model->json_print(1, 'not data');
            exit;
        }
        // 获取用户信息
        $admin_info = $this->get_cache_admin_info();
        if (!in_array('superadmin', json_decode($admin_info->roles))) {
            $this->Output_model->json_print(2, 'not admin ');
            exit;
        }

        // 2.1 查找角色名1 是否存在
        $info_old = $this->get_account_info($platform_old, $p_uid_old);
        if (!$info_old) {
            $this->Output_model->json_print(1, 'db not user p_uid_A', $p_uid_old);
            exit;
        }
        // 2.2 查找旧角色名2 是否存在
        $info_new = $this->get_account_info($platform_new, $p_uid_new);
        if (!$info_new) {
            $this->Output_model->json_print(1, 'db not user p_uid_B', $p_uid_new);
            exit;
        }
        // 3. 执行玩家账号 platform p_uid 互换
        if (isset($info_old) && $info_old && isset($info_new) && $info_new) {
            // 执行
            $condition_one = array(
                'user_id' => $info_old->user_id,
                'platform_old' => $platform_old,
                'p_uid_old' => $p_uid_old,
                'platform_new' => $platform_new,
                'p_uid_new' => $p_uid_new,
            );
            $condition_two = array(
                'user_id' => $info_new->user_id,
                'platform_old' => $platform_new,
                'p_uid_old' => $p_uid_new,
                'platform_new' => $platform_old,
                'p_uid_new' => $p_uid_old,
            );
            $response_one = $this->update_account_info($condition_one);
            $response_two = $this->update_account_info($condition_two);
            if (isset($response_one) && $response_one && isset($response_two) && $response_two) {
                // 存log
                $conditions = array(
                    'user_id_A' => $response_one->user_id,
                    'platform_new' => $response_one->platform,
                    'p_uid_new' => $response_one->p_uid,
                    'user_id_B' => $response_two->user_id,
                    'platform_old' => $response_two->platform,
                    'p_uid_old' => $response_two->p_uid,
                    'handler_name' => $admin_info->name,
                    'create_date' => time(),
                );
                $exchange = $this->User_exchange_model->add($conditions);
                // 刷新缓存
                $this->flush_game_map();
                if ($exchange) {
                    $exchange_data = $conditions;
                } else {
                    $exchange_data = array();
                }
                // 输出
                $data = array(
                    'user_old' => $response_one,
                    'user_new' => $response_two,
                    'exchange' => $exchange_data,
                );
                $this->Output_model->json_print(0, 'ok', $data);
            } else {
                $this->Output_model->json_print(1, 'db err');
            }
        } else {
            $this->Output_model->json_print(1, 'err');
        }
    }

    // 检查用户是否存在 User 表
    private function get_account_info($platform, $p_uid)
    {
        $condition = array(
            'platform' => $platform,
            'p_uid' => $p_uid,
        );
        $response = $this->User_model->get_one_by_condition($condition);
        return $response;
    }

    // 检查用户是否存在 Create_role_report 表
    public function get_account_create_role_info()
    {
        $platform_A = $this->input->get_post('platform_A');
        $platform_B = $this->input->get_post('platform_B');
        $p_uid_A = $this->input->get_post('p_uid_A');
        $p_uid_B = $this->input->get_post('p_uid_B');

        if (!$p_uid_A || !$p_uid_B || !$platform_A || !$platform_B) {
            $this->Output_model->json_print(1, 'not data');
            exit;
        }
        // 获取用户信息
        $admin_info = $this->get_cache_admin_info();

        $user_A = $this->get_account_search_info($platform_A, $p_uid_A);
        $user_B = $this->get_account_search_info($platform_B, $p_uid_B);

        $data = array(
            'user_A' => $this->get_account_level_info($user_A),
            'user_B' => $this->get_account_level_info($user_B),
        );

        $this->Output_model->json_print(0, 'ok', $data);
    }

    // Create_role_report_model
    private function get_account_search_info($platform, $p_uid)
    {
        $condition = array(
            'platform' => $platform,
            'p_uid' => $p_uid,
        );
        $this->db->order_by('create_date', 'desc');
        $response = $this->search_create_role($condition);
        if ($response) {
            return $response;
        } else {
            $this->Output_model->json_print(1, 'get_account_search_info err');
            exit;
        }
    }

    // Login_report_model
    private function get_account_level_info($user_A)
    {
        $condition = array(
            'platform' => $user_A[0]->platform,
            'user_id' => $user_A[0]->user_id,
        );
        $this->db->order_by('create_date', 'desc');
        $response = $this->Login_report_model->get_one_by_condition($condition);
        if ($response) {
            return $response;
        } else {
            $this->Output_model->json_print(1, 'get_account_level_info err');
            exit;
        }
    }

    // 更新用户 User 表 platform、p_uid 信息
    private function update_account_info($condition)
    {
        $where = array(
            'user_id' => $condition['user_id'],
            'platform' => $condition['platform_old'],
            'p_uid' => $condition['p_uid_old'],
        );

        $data = array(
            'platform' => $condition['platform_new'],
            'p_uid' => $condition['p_uid_new'],
        );
        $response = $this->User_model->update($data, $where);
        if ($response) {
            $condition = array(
                'user_id' => $condition['user_id'],
            );
            $data = $this->User_model->get_one_by_condition($condition);
            if ($data) {
                return $data;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function get_cache_admin_info()
    {
        // 根据 X_TOKEN 获取用户信息
        $token = $_SERVER['HTTP_X_TOKEN'];
        if (!$token) {
            $this->Output_model->json_print(3, 'no token');
            exit;
        }
        $cache_driver = $this->CACHE_DRIVER;
        $admin_info = $this->cache->$cache_driver->get($token);
        if (!isset($admin_info) || !$admin_info) {
            $this->Output_model->json_print(2, 'no admin info');
            exit;
        } elseif (json_decode($admin_info->roles)[0] != 'admin') {
            $this->Output_model->json_print(2, 'not admin ');
            exit;
        }
        return $admin_info;
    }
    private function flush_game_map()
    {
        $this->Curl_model->curl_get($_SERVER['SERVER_ADDR'] . "/index.php/trigger/game_flush_all");
    }
}
