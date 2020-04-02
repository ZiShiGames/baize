<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        //||$role->admin_user_role=='customerService'
        parent::__construct();
        $role = $this->session->userdata('role');
        // if ($role) {
        //     if ($role->admin_user_role == 'admin') {
        //     } else {
        //         echo json_encode($role);
        //         $this->Output_model->json_print(-1, 'no authority');
        //         exit;
        //     }
        // } else {
        //     echo 'no role';
        //     exit;
        // }
    }

    public function index()
    {
        echo 'ok';
    }

    //对玩家进行封号处理
    public function illegal_user($game_id, $user_id, $p_uid, $platform, $game_father_id)
    {
        $role = $this->session->userdata('role');
        if ($role->admin_user_role !='admin') {
            echo '没有权限';
            exit;
        }
        if (!$game_id||!$user_id||!$p_uid||!$platform||!$game_father_id) {
            $this->Output_model->json_print(1, '参数不足');
            return;
        }
        $this->load->model('db/Illegal_user_model');

        $data = array(
            'user_id'=>$user_id,
            'p_uid'=>$p_uid,
            'platform'=>$platform,
            'game_id'=>$game_id,
            'create_date'=>time(),
            'game_father_id'=>$game_father_id,
            'performer_admin'=>$this->session->userdata('role')->admin_user_name,
            'status'=>1,
        );

        ($this->input->get('cproleid'))?$data['cproleid']=$this->input->get('cproleid'):'';
        ($this->input->get('nickname'))?$data['nickname']=$this->input->get('nickname'):'';

        $condition = array(
            'user_id'=>$user_id,
            'p_uid'=>$p_uid,
            'platform'=>$platform,
            'game_id'=>$game_id,
            'game_father_id'=>$game_father_id,
            'status'=>1,
        );
        $requery=$this->Illegal_user_model->get_one_by_condition($condition);
        if (!$requery) {
            $this->Illegal_user_model->add($data);
            $this->Output_model->json_print(0, 'sueecss');
        } else {
            // $this->Illegal_user_model->update($data,$condition);
            $this->Output_model->json_print(2, 'error');
        }
    }


    //2460后台直接登录玩家账号
    public function player_login($game_id)
    {
        $role = $this->session->userdata('role');
        // if ($role->admin_user_role !='admin') {
        //     echo '没有权限';
        //     exit;
        // }
        $game = $this->Game_model->get_by_game_id($game_id);
        if (!$game) {
            $this->Output_model->json_print(-2, '');

            return;
        }

        $this->cache->get('user_id');

        $openId = $this->input->get('openId');
        if (!$openId) {
            echo 'error';

            return;
        }
        $condition = array('user_id' => $openId);

        $user = $this->User_model->get_one_by_condition($condition);
        if (!$user) {
            echo 'user not found';

            return;
        }

        // $openKey = $this->session->userdata('role')->admin_user_name;
        // if (!$openKey) {
            $openKey='lc';
        // }
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $server_name = $this->input->get('server_name');
        $pfid = $this->input->get('pfid');
        $noice = time();
        $nickname = urlencode($user->nickname);
        $avatar = urlencode($user->avatar);

        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;

        $this->load->model('db/Admin_player_login_model');

        $admin_player_login_data = array(
            'user_id'=>$openId,
            'platform'=>$game->platform,
            'game_father_id'=>$game->game_father_id,
            'game_id'=>$game_id,
            'performer_admin'=>'lc',
            'create_date'=>time(),
        );
        $this->Admin_player_login_model->add($admin_player_login_data);
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId&server_name=$server_name&nickname=$nickname&avatar=$avatar";
        if($game->game_father_id = 20020){
            $url = $url.'&sdkType=xileyou&channel=xileyou';
        }
        log_message('debug', "admin login:$url");
        $data = array(
            'url'=>$url,
            'passId' => $game->platform,
            'appId' => $game_id,
        );
        $this->load->view('admin/admin_player_login', $data);
    }
    public function new_game_father_page()
    {
        $this->load->view('admin/admin_new_game_father');
    }

    public function create_game_father()
    {
        $game_name = $this->input->post('game_name');
        if (!$game_name) {
            echo 'Error: 参数不足';

            return;
        }

        $app_id = 'g2460'.md5($game_name.time().rand(0, 1000));
        $app_id = substr($app_id, 0, 18);
        $app_key = md5($game_name.time().rand(0, 1000));

        $data = array(
                    'game_father_name' => $game_name,
                    'app_id' => $app_id,
                    'app_key' => $app_key,
                    'status' => 1,
                    'create_date' => time(),
                );

        $this->load->model('db/Game_father_model');
        $game_id = $this->Game_father_model->add($data);
        if ($game_id) {
            header('Location: /index.php/admin/game_manage');
        } else {
            echo '新建失败, 数据库错误';
        }
    }

    public function new_game_page()
    {
        $this->load->model('db/Game_father_model');
        $this->load->model('db/Platform_model');
        $game_fathers = $this->Game_father_model->get_by_condition();

        $platforms = $this->Platform_model->get_by_condition();

        $data = array(
            'fathers' => $game_fathers,
            'platforms' => $platforms,
            'platform_key' => ['key'], // allu keys
        );
        $this->load->view('admin/admin_new_game', $data);
    }

    public function create_game()
    {
        // echo json_encode($_POST);
        $game_father_id = $this->input->post('game_father_id');
        $platform = $this->input->post('platform');
        $game_login_url = $this->input->post('game_login_url');
        $game_pay_nofity = $this->input->post('game_pay_nofity');
        if (!$game_father_id || !$platform || !$game_login_url || !$game_pay_nofity) {
            echo 'game_father_id: '.$game_father_id.' platform: '.$platform.' game_login_url: '.$game_login_url.' game_pay_nofity: '.$game_pay_nofity;
            echo 'Error: 参数不足';

            return;
        }


        $this->load->model('db/Game_father_model');
        $condition = array('game_father_id' => $game_father_id);
        $game_father = $this->Game_father_model->get_one_by_condition($condition);
        if (!$game_father) {
            echo "$game_father_id 游戏没有找到";

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
            echo $platform.' 渠道已经添加《'.$game->game_name.'》';

            return;
        }

        $this->load->model('db/Platform_model');
        $condition = array(
            'platform' => $platform,
        );
        $platform_obj = $this->Platform_model->get_one_by_condition($condition);

        $pieces = explode(',', $platform_obj->platform_key);
        $platform_key = array();
        foreach ($pieces as $one) {
            if (preg_match('/\s/', $this->input->post($one))) {
                echo '别再输入空格了！！！';
                return;
            }

            $platform_key[$one] = $this->input->post($one);
            if (!$platform_key[$one]) {
                echo "$one 不能为空";

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
            $this->Game_model->flush_cache();
            header('Location: /index.php/admin/game_manage');
        } else {
            echo '新建失败, 数据库错误';
        }
    }

    public function platform_keys()
    {
        $platform = $this->input->get('platform');
        if (!$platform) {
            $this->Output_model->json_print(1, 'no platform input');

            return;
        }
        $condition = array('platform' => $platform);
        $this->load->model('db/Platform_model');
        $platform_obj = $this->Platform_model->get_one_by_condition($condition);
        if (!$platform_obj) {
            $this->Output_model->json_print(2, 'no platform found by ' + $platform);

            return;
        }

        $this->Output_model->json_print(0, 'ok', $platform_obj->platform_key);
    }

    public function new_platform_page()
    {
        $this->load->view('admin/admin_new_platform');
    }

    public function create_platform()
    {
        $platform_chinese = $this->input->post('platform_chinese');
        $platform = $this->input->post('platform');
        $platform_key = $this->input->post('platform_key');

        $platform_chinese = str_replace(' ', '', $platform_chinese);
        $platform = str_replace(' ', '', $platform);
        $platform_key = str_replace(' ', '', $platform_key);

        if (!$platform_chinese || !$platform || !$platform_key) {
            echo 'Error: 参数不足';

            return;
        }

        if (!preg_match('/^[a-z]+$/', $platform)) {
            echo '拼音缩写必须全部为小写字母';

            return;
        }

        if (!preg_match('/^([a-zA-Z]|,)+$/', $platform_key)) {
            echo 'key 必须为 a-z A-Z 逗号组成';

            return;
        }


        $this->load->model('db/Platform_model');

        $condition = array('platform_chinese' => $platform_chinese);

        $res = $this->Platform_model->get_by_condition($condition);
        if ($res) {
            echo "$platform_chinese 重复，不能成功添加";

            return;
        }

        $condition = array('platform' => $platform);

        $res = $this->Platform_model->get_by_condition($condition);
        if ($res) {
            echo "$platform 重复，不能成功添加";

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
            header('Location: /index.php/admin/game_manage');
        } else {
            echo '新建失败, 数据库错误';
        }
    }

    public function edit_game()
    {
        $game_id = $this->input->get('game_id');
        if (!$game_id) {
            echo 'game_id needed';

            return;
        }

        $condition = array('game_id' => $game_id);

        $game = $this->Game_model->get_one_by_condition($condition);
        if (!$game) {
            echo "$game_id 没有对应游戏";

            return;
        }

        $this->load->view('admin/admin_edit_game', $game);
    }

    public function update_game()
    {
        $game_id = $this->input->post('game_id');
        if (!$game_id) {
            echo 'Error: game_id needed';

            return;
        }

        $condition = array('game_id' => $game_id);
        $game = $this->Game_model->get_one_by_condition($condition);
        if (!$game) {
            echo "Error: game not found by id $game_id";

            return;
        }

        $p_map = array('game_name', 'game_summary', 'game_desc', 'game_url', 'game_pay_url', 'priority');

        $data = array();

        foreach ($p_map as $p) {
            $p_str = $this->input->post($p);
            if ($p_str) {
                $data[$p] = $p_str;
            }
        }

        $game_icon = $this->input->post('game_icon');
        // if ($game_icon) {
            $game_icon = $this->do_upload('game_icon', './img/icon');

        if ($game_icon) {
            $data['game_icon'] = $game_icon;
        }
        // }

        $game_banner = $this->input->post('game_banner');
        // if ($game_banner) {
            $game_banner = $this->do_upload('game_banner', './img/banner');

        if ($game_banner) {
            $data['game_banner'] = $game_banner;
        }
        // }

        $label_map = array();

        $label_key = array('hot','recommend','elite','coupon','news','first');
        foreach ($label_key as $key) {
            $value = $this->input->post($key);
            if ($value) {
                $label_map[$key] = 1;
            }
        }

        $data['game_label'] = json_encode($label_map);

        if (count($data) > 0) {
            if ($this->Game_model->update_by_game_id($game_id, $data)) {
                header('Location: /index.php/admin/game_manage');
            } else {
                echo '数据库操作失败';
            }
        } else {
            echo 'No data Commit';
        }
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

            $data = array('one' => $game);

            $view = $this->load->view('admin/admin_game_list_row_temp', $data, true);
            $this->Output_model->json_print(0, 'ok', $view);
        } else {
            $this->Output_model->json_print(3, 'update error');
        }
    }

    public function game_manage()
    {
        $data = array();
        $data['list'] = $this->get_game_list(true);
        $this->load->model('db/Game_father_model');
        $fathers = $this->Game_father_model->get_by_condition();
        $data['fathers'] = $fathers;
        $this->load->view('admin/admin_game_manage', $data);
    }

    public function game_manage_new()
    {
        $data = array();
        $data['list'] = $this->get_game_list(true);
        $this->load->model('db/Game_father_model');
        $fathers = $this->Game_father_model->get_by_condition();
        $data['fathers'] = $fathers;
        $this->load->view('admin/admin_game_manage_new', $data);
    }

    public function get_game_list($echo = false)
    {
        $page = $this->input->get('page');
        if (!$page) {
            $page = 1;
        }

        $page_count = $this->input->get('page_count');
        if (!$page_count) {
            $page_count = 10;
        }
        $condition = array();

        $game_father_id = $this->input->get('game_father_id');
        if ($game_father_id && $game_father_id !== '0') {
            $condition['game_father_id'] = $game_father_id;
        }

        $create_date_order = $this->input->get('create_date');
        if (!$create_date_order || $create_date_order === '0') {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        $data['list'] = $this->Game_model->get_by_condition($condition, ($page - 1) * $page_count, $page_count, 'create_date', $order);
        $count = 0;
        $total_page = 0;
        if ($data['list']) {
            $list_for_count = $this->Game_model->get_by_condition($condition);
            $count = count($list_for_count);
            $total_page = ceil($count / $page_count);

            foreach ($data['list'] as $one) {
                $this->load->model('db/Platform_model');
                $condition = array('platform' => $one->platform);
                $platform = $this->Platform_model->get_one_by_condition($condition);
                if ($platform) {
                    $one->platform_chinese = $platform->platform_chinese;
                } else {
                    $one->platform_chinese = '未找到 '.$one->platform;
                }
            }
        }

        $data['page'] = $page;
        $data['total_page'] = $total_page;
        $data['page_count'] = $page_count;

        if ($echo) {
            return $this->load->view('admin/admin_game_list_temp', $data, true);
        } else {
            $this->Output_model->json_print(0, 'ok', $this->load->view('admin/admin_game_list_temp', $data, true));
        }
    }

    public function user_manage()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );
        $this->load->view('admin/admin_user_manage', $data);
    }

    public function user_manage_new()
    {
        $this->load->model('db/Platform_model');
        $platforms = $this->Platform_model->get_by_condition();


        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();

        $data = array(
            'platform_info' => $platforms,
            'game_faters' => $game_faters,
        );
        $this->load->view('admin/admin_user_manage_new', $data);
    }

    public function get_user_list($echo = false)
    {
        $page = $this->input->get('page');
        if (!$page) {
            $page = 1;
        }

        $page_count = $this->input->get('page_count');
        if (!$page_count) {
            $page_count = 10;
        }
        $condition = array();

        $status = $this->input->get('status');
        if ($status || $status === '0') {
            $condition['status'] = $status;
        }

        $create_date_order = $this->input->get('create_date');
        if (!$create_date_order || $create_date_order === '0') {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        $data['list'] = $this->User_model->get_by_condition($condition, ($page - 1) * $page_count, $page_count, 'create_date', $order);
        $count = 0;
        $total_page = 0;
        if ($data['list']) {
            $list_for_count = $this->User_model->get_by_condition($condition);
            $count = count($list_for_count);
            $total_page = ceil($count / $page_count);
        }

        $data['page'] = $page;
        $data['total_page'] = $total_page;
        $data['page_count'] = $page_count;

        if ($echo) {
            return $this->load->view('admin_user_list_temp', $data, true);
        } else {
            $this->Output_model->json_print(0, 'ok', $this->load->view('admin_user_list_temp', $data, true));
        }
    }

    private function do_upload($field_name, $path)
    {
        $config['upload_path'] = $path;
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = 0;
        $config['max_width'] = 1024;
        $config['max_height'] = 512;

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload($field_name)) {
            // echo "$field_name ".$this->upload->display_errors();

            return false;
        }

        return $this->upload->data('file_name');
    }

    public function user_search()
    {
        $p_uid = $this->input->get('p_uid');
        $platform = $this->input->get('platform');
        $user_id = $this->input->get('user_id');
        $user_nickname = $this->input->get('nickname');
        if (!$p_uid && !$user_id && !$user_nickname) {
            $this->Output_model->json_print(1, 'error');
            return;
        }
        $condition = array();
        if ($p_uid) {
            $condition['p_uid'] = $p_uid;
            $user = $this->User_model->get_one_by_condition($condition);
        } elseif ($user_id) {
            $condition['user_id'] = $user_id;
            $user = $this->User_model->get_one_by_condition($condition);
        } else {
            $this->load->model('Create_role_report_model');
            $this->db->like('nickname', $user_nickname);
            $this->db->select('user_id');
            $user_id = $this->Create_role_report_model->get_by_condition();
            $user_id_array = array();
            if (!$user_id) {
                $this->Output_model->json_print(3, 'no users');
                return;
            }
            foreach ($user_id as $one) {
                array_push($user_id_array, $one->user_id);
            }
            $this->db->where_in('user_id', $user_id_array);
            $user = $this->User_model->get_by_condition();
            $data = array(
                'user' => $user,
            );
            $this->Output_model->json_print(0, 'ok', $data);
            // echo json_encode($user);
            return;
        }
        if (!$user) {
            $this->Output_model->json_print(2, 'error');

            return;
        }

        $data = array(
            'user' => $user,
        );

        $this->load->model('Create_role_report_model');
        $reports = $this->Create_role_report_model->get_by_condition($condition);

        $this->load->model('db/Game_father_model');
        $game_faters = $this->Game_father_model->get_by_condition();

        foreach ($reports as $one) {
            foreach ($game_faters as $two) {
                if ($one->game_father_id==$two->game_father_id) {
                    $one->game_father_name=$two->game_father_name;
                }
            }
        }
        if ($reports) {
            $data['reports'] = $reports;
        } else {
            $data['reports'] = array();
        }

        $condition = array(
            'user_id' => $user->user_id,
        );

        $this->load->model('Game_order_model');

        $orders = $this->Game_order_model->get_by_condition($condition);
        if ($orders) {
            $data['orders'] = $orders;
        } else {
            $data['orders'] = array();
        }

        $this->load->model('login_report_model');
        $this->db->order_by('create_date', 'desc');
        // $this->db->limit(200);

        $login_report = $this->login_report_model->get_by_condition($condition);
        // echo $this->db->last_query();

        if ($login_report) {
            $login_array = array();
            $date = '';
            foreach ($login_report as $one) {
                if ($date != date('Y-m-d', $one->create_date)) {
                    array_push($login_array, $one);
                    $date = date('Y-m-d', $one->create_date);
                } else {
                    continue;
                }
                // code...
            }
            $data['login_report'] = $login_array;
        } else {
            $data['login_report'] = array();
        }

        $this->Output_model->json_print(0, 'ok', $data);
    }

    // //服务器api
    // public function get_server_list()
    // {
    //     $role = $this->session->userdata('role');
    //     if ($role) {
    //         if ($role == 'admin') {
    //         } else {
    //             $this->Output_model->json_print(-1, 'no authority');
    //             exit;
    //         }
    //     } else {
    //         exit;
    //     }
    //     $plat = $this->input->get('platform_id');
    //     if (!$plat) {
    //         $this->Output_model->json_print(-1, '渠道名为空', $data);
    //         return;
    //     }
    //     $this->load->model('Platform_server_list_model');
    //     $condition= array(
    //         'platform_server_name'=>$plat,
    //     );
    //     $url = $this->Platform_server_list_model->get_one_by_condition($condition)->platform_server_list_url;
    //     $content = $this->Curl_model->curl_get($url);
    //     if ($content) {
    //         $servers = json_decode($content);
    //     } else {
    //         log_message('error', 'cant get server list');
    //         return;
    //     }
    //     $servers = json_decode($content);
    //     $data['servers'] =$servers;
    //     $this->load->model('db/Platform_list_model');
    //     $platform = $this->Platform_list_model->get_by_condition();
    //     $data['platform'] = $platform;
    //     $data['plat'] = $plat;
    //     $this->load->view('admin/admin_server_manage', $data);
    //     // $this->Output_model->json_print(0, 'ok', $servers);
    // }
    //
    //
    // public function server_manage()
    // {
    //     $role = $this->session->userdata('role');
    //     if ($role) {
    //         if ($role == 'admin') {
    //         } else {
    //             $this->Output_model->json_print(-1, 'no authority');
    //             exit;
    //         }
    //     } else {
    //         exit;
    //     }
    //     $data = array();
    //     $data['plat']='juhe';
    //     $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/api/?m=player&fn=getserverlist&openId=1';
    //     $content = $this->Curl_model->curl_get($url);
    //     if ($content) {
    //         $servers = json_decode($content);
    //     } else {
    //         log_message('error', 'cant get server list');
    //         return;
    //     }
    //     $servers = json_decode($content);
    //     $data['servers'] =$servers;
    //     $this->load->model('db/Platform_list_model');
    //     $platform = $this->Platform_list_model->get_by_condition();
    //     $data['platform'] = $platform;
    //     $this->load->view('admin/admin_server_manage', $data);
    // }
    public function set_server_status()
    {
        $server_id = $this->input->get('server_id');
        $status = $this->input->get('status');
        $platform = $this->input->get('platform');
        if (!$platform) {
            $platform='juhe';
        }
        $data = array(
            'platform' =>$platform,
            'status' =>$status,
            'server_id' =>$server_id,
        );


        // $server = $this->db->replace('server_list', $data);
        // echo $server;
        $this->load->model('Server_model');
        $server = $this->Server_model->get_by_server_id($server_id, $platform);
        if (!$server) {
            $server = $this->Server_model->insert_server($server_id, $status, $platform);
            echo $server;
            return;
        } elseif ($server) {
            $server = $this->Server_model->update_server($server_id, $status, $platform);
            echo $server;
            return;
        }
    }
    public function set_list_status()
    {
        $begin = $this->input->get('begin');
        $end = $this->input->get('end');
        if (!$end) {
            $end=$begin;
        }
        $status = $this->input->get('status');
        $platform=$this->input->get('platform');
        $this->load->model('Server_model');
        $count = 0;
        for ($i=$begin; $i<=$end; $i++) {
            $condition = array(
                'server_id' => $i,
                'platform'=>$platform
            );
            $server = $this->Server_model->get_one_by_condition($condition);
            if (!$server) {
                $this->Server_model->insert_server($i, $status, $platform);
                $count+=1;
            } elseif ($server) {
                $this->Server_model->update_server($i, $status, $platform);
                $count+=1;
            }
        }
        if ($count!=0) {
            $data = array(
                'platform'=>$platform,
                'count'=>$count
            );
            $this->Output_model->json_print('0', 'ok', $data);
        } else {
            $this->Output_model->json_print('-1', 'error');
        }
    }

//admin backstatus toolpage
    public function admin_tool()
    {
        // $role = $this->session->userdata('role');
        // if ($role) {
        //     if ($role == 'admin') {
        //     } else {
        //         $this->Output_model->json_print(-1, 'no authority');
        //         exit;
        //     }
        // } else {
        //     exit;
        // }
        $this->load->view('admin/admin_backstage_tool.php');
    }
    public function addCustomerService()
    {
        $role = $this->session->userdata('role')->admin_user_role;
        if ($role) {
            if ($role == 'admin') {
            } else {
                $this->Output_model->json_print(-1, 'no authority');
                exit;
            }
        } else {
            exit;
        }
        $username = $this->input->get_post('CSname');
        $password  = $this->input->get_post('CSpassword');
        $role = 'customerService';
        $time = time();
        $this->load->model('Admin_user_model');
        $condition = array(
            'admin_user_name'=>$username,
        );
        $check  = $this->Admin_user_model->get_by_condition($condition, null, null, null, null, null, null);
        if ($check) {
            $this->Output_model->json_print(-3, 'same username');
            return;
        }
        $data = array(
            'admin_user_name'=>$username,
            'admin_user_password'=>md5($password.$this->Admin_user_model->ADMIN_SALT),
            'admin_user_role'=>$role,
            'create_date'=>$time,
        );
        $response = $this->Admin_user_model->add($data);
        if ($response) {
            $this->Output_model->json_print(0, 'ok');
        } else {
            $this->Output_model->json_print(-2, 'error');
        }
    }
    //查询订单状态
    public function check_orderId_page()
    {
        $this->load->view('admin/info_tongji/admin_check_order');
    }
    public function check_orderId_page_new()
    {
        $this->load->view('admin/info_tongji/admin_check_order_new');
    }

    public function check_orderId_api()
    {
        $orderid = $this->input->get('user_order_id');
        if (!$orderid) {
            $this->Output_model->json_print(-1, 'orderid is illegal');
            return;
        }
        $this->load->model('Game_order_model');
        $condition=array(
            'u_order_id'=>$orderid,
        );
        $response=$this->Game_order_model->get_by_condition($condition);
        if (!$response) {
            $this->Output_model->json_print(-2, 'no much order');
            return;
        }
        if ($response[0]->status==0) {
            $status='玩家未支付';
        } elseif ($response[0]->status==1) {
            $status='玩家已支付，未发货';
        } elseif ($response[0]->status==2) {
            $status='交易成功';
        } else {
            $status='未知错误';
        }
        $data=array(
            'info'=>$response[0],
            'date'=>date('Y-m-d', $response[0]->create_date),
            'status'=>$status,
        );
        $this->Output_model->json_print(0, 'ok', $data);
    }
    public function cp_check_orderId_api()
    {
        $orderid = $this->input->get('user_order_id');
        if (!$orderid) {
            $this->Output_model->json_print(-1, 'orderid is illegal');
            return;
        }
        $this->load->model('Cp_game_order_model');
        $condition=array(
            'cp_order_id'=>$orderid,
        );
        $response=$this->Cp_game_order_model->get_by_condition($condition);
        if (!$response) {
            $this->Output_model->json_print(-2, 'no much order');
            return;
        }
        $data=array(
            'info'=>$response[0],
            'date'=>date('Y-m-d', $response[0]->create_date),
        );
        $this->Output_model->json_print(0, 'ok', $data);
    }
    public function addSpecialServer()
    {
        $platform_name  = $this->input->get('platform_name');
        $platform_en_name  = $this->input->get('platform_en_name');
        $server_login_url  = $this->input->get('server_login_url');
        $this->load->model('Platform_server_list_model');
        $game_condition=array(
            'platform'=>$platform_en_name,
        );
        $game_response = $this->Game_model->get_one_by_condition($game_condition);
        if (!$game_response) {
            $this->Output_model->json_print(-2, 'no much platform');
            return;
        }
        $condition=array(
            'platform_chinese_name'=>$platform_name,
        );
        $cnname_response = $this->Platform_server_list_model->get_one_by_condition($condition);
        $condition=array(
            'platform_server_name'=>$platform_en_name,
        );
        $enname_response = $this->Platform_server_list_model->get_one_by_condition($condition);
        if ($cnname_response||$enname_response) {
            $this->Output_model->json_print(-2, 'repeat platform  ');
            return;
        }

        if (!$platform_name) {
            $this->Output_model->json_print(-2, 'platform name is null');
            return;
        }

        if (!$platform_en_name) {
            $this->Output_model->json_print(-2, 'platform_en_name name is null');
            return;
        }

        if (!$server_login_url) {
            $this->Output_model->json_print(-2, 'server_login_url name is null');
            return;
        }

        $data=array(
            'platform_chinese_name'=>$platform_name,
            'platform_server_name'=>$platform_name,
            'platform_server_list_url'=>$platform_name,
        );
        $response = $this->Platform_server_list_model->add($data);
        if ($response) {
            $this->Output_model->json_print(0, 'ok');
        } else {
            $this->Output_model->json_print(-1, 'error');
        }
    }
    public function fake()
    {
        $this->load->model('db/Game_father_model');
        $this->load->model('db/Platform_model');
        $game_fathers = $this->Game_father_model->get_by_condition();

        $platforms = $this->Platform_model->get_by_condition();

        $data = array(
            'game_faters' => $game_fathers,
            'platform_info' => $platforms,
        );
        $this->load->view('admin/info_tongji/fake', $data);
    }
    public function change_scale()                          //change scale
    {
        $this->load->model('Fake_model');
        $scale=$this->input->get('scale');
        $game_father=$this->input->get('game_father');
        $platform=$this->input->get('platform');
        $where = array(
            'platform'=>$platform,
            'game_father_id'=>$game_father,
        );
        $date = array(
            'scale'=>$scale,
        );
        $this->Fake_model->update($date, $where);
        echo $this->db->last_query();
    }
    public function lcby_report()
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

        $this->load->view('admin/info_tongji/lcby_report', $data);
    }
    public function lcby_month_report()
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
        $this->load->view('admin/info_tongji/lcby_month_report', $data);
    }

    public function server_list_flush()
    {
        $this->load->model('Server_model');
        $a = $this->Server_model->flush_cache();
        echo $a;
    }
    //玩家用户留存
    public function user_retained()
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
        $this->load->view('admin/info_tongji/user_retained.php', $data);
    }
    public function platform_manage()
    {
        $this->load->model('db/Platform_model');
        $this->db->select('platform_chinese,platform');
        $platforms = $this->Platform_model->get_by_condition();
        $all=array();
        for ($i=0; $i <count($platforms) ; $i+=4) {
            # code...
            // echo json_encode(array_slice($platforms,$i,2));
            $grout=array();
            array_push($grout, array_slice($platforms, $i, 4));
            array_push($all, $grout);
        }


        // foreach ($platforms as $key => $value) {
        //     $gloup = array();
        //     array_push($gloup, $value);
        // }
        //
        $data = array(
                'platform_info' => $all,
        );
        // echo json_encode($all);
        $this->load->view('admin/admin_platform_manage.php', $data);
    }

    public function platform_manage_new()
    {
        $this->load->model('db/Platform_model');
        $this->db->select('platform_chinese,platform');
        $platforms = $this->Platform_model->get_by_condition();
        $all=array();
        for ($i=0; $i <count($platforms) ; $i+=4) {
            # code...
            // echo json_encode(array_slice($platforms,$i,2));
            $grout=array();
            array_push($grout, array_slice($platforms, $i, 4));
            array_push($all, $grout);
        }


        // foreach ($platforms as $key => $value) {
        //     $gloup = array();
        //     array_push($gloup, $value);
        // }
        //
        $data = array(
                'platform_info' => $all,
        );
        // echo json_encode($all);
        $this->load->view('admin/admin_platform_manage_new.php', $data);
    }
}
