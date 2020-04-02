<?php

class Four_model extends CI_Model
{
    public $platform = 'four';
    public $secret_key = '';
    public $appid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $open_id = $this->input->get('open_id');//用户id
        $access_token = $this->input->get('access_token');//用户登录口令
        $channel = $this->input->get('channel');//用户渠道
        $is_favorite = $this->input->get('is_favorite');//是否收藏（0：未收藏，1：已收藏）

        if (!$open_id || !$access_token || !$channel) {
            return false;
        }

        $this->secret_key = $this->Game_model->get_key($game_id, 'secret_key');
        $this->appid = $this->Game_model->get_key($game_id, 'appid');

        $condition = array(
            'p_uid' => $open_id,
            'platform' => $this->platform,
        );
        $user = $this->User_model->get_one_by_condition_array($condition);

        if (!$user) {
            //请求用户信息
            $my_sign = md5('access_token='.$access_token.'&open_id='.$open_id.'&secret_key='.$this->secret_key);
            $url = 'http://passport.4177.com/game/user/info?open_id='.$open_id.'&access_token='.$access_token.'&sign='.$my_sign;
            $content = $this->Curl_model->curl_get($url);
            if (!$content) {
                log_message('error', "Four Login empty content $url");

                return false;
            }

            $response = json_decode($content, true);
            if ($response['code'] != 200) {
                log_message('error', 'Four Login info request fails');

                return false;
            }

            $user = array(
                'platform' => $this->platform,
                'p_uid' => $open_id,
                'nickname' => $response['data']['nickname'],
                'avatar' => $response['data']['avatar'],
                'create_date' => time(),
            );

            $user_id = $this->User_model->add($user);
            if (!$user_id) {
                log_message('error', "Login error user create $content");

                return false;
            }

            $user['user_id'] = $user_id;
        }

        $this->cache->save($user['user_id'].'_token', md5($user['user_id'].$user['platform'].time()), 86400);

        $tempdata = array(
            'open_id' => $open_id,
            'channel' => $channel,
            'access_token' => $access_token,
        );
        $this->cache->save('Four_info_'.$user['user_id'], $tempdata, 3600 * 24);

        return $user['user_id'];
    }

    public function game($platform, $game_id)
    {
        $openId = $this->input->get('openId');
            // $frameHeight = $this->input->get('frameHeight');
            // $frameWidth = $this->input->get('frameWidth');

            $servers = array();

        $server1 = array(
                        'server_id' => 8003,
                        'server_name' => '1服',
                    );
        $servers[] = $server1;

        $game = $this->Game_model->get_by_game_id($game_id);
        if (!$game) {
            $this->Output_model->json_print(-2, '');

            return;
        }

        $game_name = $game->game_name;

        $url = "/index.php/enter/trun_to_game/$platform/$game_id?openId=$openId";

        $data = array(
                        'servers' => $servers,
                        'game_name' => $game_name,
                        'url' => $url,
                    );

        $this->load->view('game_login/allu_lc_login', $data);
    }

    public function trun_to_game($game_id)
    {
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
        $openKey = $this->cache->get($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $server_name = $this->input->get('server_name');
        $pfid = $this->input->get('pfid');
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        $spe_server_ids = array('2','3','4','5','6','7','8','9','10','11','8000');
        if (in_array($serverId, $spe_server_ids)) {
            $game_url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/login/juhe';
        }

        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId&server_name=$server_name";
        log_message('debug', "nineg login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->post('ext');
        $sign = $this->input->post('sign');
        if (!$sign) {
            return false;
        }

        $money = $this->input->post('price');

        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money * 10) == $game_order->money) {
                    return $order_id;
                } else {
                    log_message('debug', $this->platform.' money errory '.$game_order->money." != $money");
                }
            }
        }
    }

    public function notify_ok()
    {
        echo 'success';
    }

    public function notify_error()
    {
        echo 'fail';
    }

    public function sign_order($game_id)
    {
        $openid = $this->input->get('openid');
        $info = $this->cache->get('Four_info_'.$openid);
        $open_id = $info['open_id'];//4177的open_id
        $channel = $info['channel'];
        $access_token = $info['access_token'];
        $bill_no = $this->input->get('bill_no');
        $goods_name = $this->input->get('goods_name');
        $total_fee = $this->input->get('total_fee');
        $ext = $this->input->get('ext');

        if (!$bill_no || !$goods_name || !$total_fee) {
            return false;
        }

        $data = array(
            'open_id' => $open_id,
            'access_token' => $access_token,
            'bill_no' => $bill_no,
            'goods_name' => $goods_name,
            'total_fee' => $total_fee,
            'ext' => $ext,
        );
        $this->secret_key = $this->Game_model->get_key($game_id, 'secret_key');
        $this->appid = $this->Game_model->get_key($game_id, 'appid');

        //$this->load->model('Common_model');
        //$p_str = $this->Common_model->sort_params($data);
        $sign = md5('access_token='.$access_token.'&bill_no='.$bill_no.'&ext='.$ext.'&goods_name='.$goods_name.'&open_id='.$open_id.'&secret_key='.$this->secret_key.'&total_fee='.$total_fee);

        $deal_data = array(
            'app_id' => $this->appid,
            'open_id' => $open_id,
            'access_token' => $access_token,
            'channel' => $channel,
            'sign' => $sign,
        );

        return $deal_data;
    }

    public function create_role_report()
    {
        $date = $this->input->get('date');
        if (!$date) {
            $today_date_str = date('Y-m-d', time());
        } else {
            $today_date_str = $date;
        }
        $from_date = strtotime($today_date_str);

        $page = $this->input->get('page');
        if (!$page) {
            $page = 1;
        }

        $limit = $this->input->get('limit');
        if (!$limit) {
            $limit = 100;
        }

        $condition = array(
            'platform' => $this->platform,
            'create_date >= ' => $from_date,
        );
        $this->load->model('Create_role_report_model');
        $reports = $this->Create_role_report_model->get_report($this->platform, $from_date, ($page - 1) * $limit, $limit);

        if ($reports) {
            $all = $this->Create_role_report_model->get_report($this->platform, $from_date);
            $response = array(
                'success' => true,
                'message' => '成功',
            );
            $data = array(
                'total' => count($reports),
                'totalPage' => ceil(count($all) / $limit),
            );
            $userList = array();

            foreach ($reports as $one) {
                $user = array();
                $user['roleName'] = $one->nickname;
                $user['roleLevel'] = 1;
                $user['createTime'] = date('Y-m-d H:i:s', $one->create_date);
                $user['serverId'] = $one->server_id;
                $userList[$one->p_uid.''] = $user;
            }
            $data['userList'] = $userList;
            $response['data'] = $data;
        } else {
            $response = array(
                'success' => true ,
                'message' => '成功',
            );
        }
        echo json_encode($response);
        exit();
    }

    public function login_report($value = '')
    {
        $date = $this->input->get('date');
        if (!$date) {
            $today_date_str = date('Y-m-d', time());
        } else {
            $today_date_str = $date;
        }
        $from_date = strtotime($today_date_str);

        $page = $this->input->get('page');
        if (!$page) {
            $page = 1;
        }

        $limit = $this->input->get('limit');
        if (!$limit) {
            $limit = 100;
        }

        $this->load->model('Login_report_model');
        $reports = $this->Login_report_model->get_report($this->platform, $from_date, ($page - 1) * $limit, $limit);

        if ($reports) {
            $all = $this->Login_report_model->get_report($this->platform, $from_date);
            $response = array(
                'success' => true,
                'message' => '成功',
            );
            $data = array(
                'total' => count($reports),
                'totalPage' => ceil(count($all) / $limit),
            );
            $userList = array();

            foreach ($reports as $one) {
                $user = array();
                $user['roleName'] = $one->nickname;
                $user['roleLevel'] = $one->level;
                $user['createTime'] = date('Y-m-d H:i:s', $one->create_date);
                $user['serverId'] = $one->server_id;
                $userList[$one->p_uid.''] = $user;
            }
            $data['userList'] = $userList;
            $response['data'] = $data;
        } else {
            $response = array(
                'success' => true ,
                'message' => '成功',
            );
        }
        echo json_encode($response);
        exit();
    }

    public function login_collect($data)
    {
        $info = $this->cache->get('Four_info_'.$data['user_id']);
        $open_id = $info['open_id'];//4177的open_id
        $channel = $info['channel'];
        $access_token = $info['access_token'];
        $new_data = array(
            'user_id' => $data['user_id'],
            'p_uid' => $data['p_uid'],
            'channel' => $channel,
            'app_id' => '10382',
            'open_id' => $open_id,
        );
        $this->Output_model->json_print(0, 'ok', $new_data);
        exit();
    }
    public function create_role_collect($data)
    {
        $this->Output_model->json_print(0, 'ok', $data);

        exit();
    }

    public function server_name()
    {
        $server_id = $this->input->get('server_id');
        $openId = $this->input->get('openId');
        if (!$openId || !$server_id) {
            $this->Output_model->json_print(1, 'error');

            return;
        }

        $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/api/?m=player&fn=getserverlist&openId='.$openId;
        $content = $this->Curl_model->curl_get($url);
        if ($content) {
            $servers = json_decode($content);
            foreach ($servers->server_list as $one) {
                if ($one->id == $server_id) {
                    $this->Output_model->json_print(0, 'ok', $one->name);

                    return;
                }
            }
        }

        $url = 'http://lcby.gz.1251208707.clb.myqcloud.com/9g/api/?m=player&fn=getserverlist&openId='.$openId;
        $content = $this->Curl_model->curl_get($url);
        if ($content) {
            $servers = json_decode($content);
            foreach ($servers->server_list as $one) {
                if ($one->id == $server_id) {
                    $this->Output_model->json_print(0, 'ok', $one->name);

                    return;
                }
            }
        }
    }
}
