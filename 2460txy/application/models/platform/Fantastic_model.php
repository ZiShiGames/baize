<?php

class Fantastic_model extends CI_Model
{
    public $platform = 'fantastic';

    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $uid = $this->input->get('userid');
        $account = $this->input->get('account');
        $token = $this->input->get('token');
        // $this->load->model('Curl_model');
        $url = "http://sdk.sh9130.com/user/token/?token=$token";
        $content = json_decode($this->Curl_model->curl_get($url));
        if ($content->state!=1) {
            log_message('debug', $this->platform.' log content '.json_encode($content).' '.$url);
            return;
        }




        $pkg = $this->input->get('9130_game_pkg');
        $this->session->set_userdata('9130_game_pkg', $pkg);
        $partner_id = $this->input->get('9130_partner_id');
        $this->session->set_userdata('9130_partner_id', $partner_id);
        if (!$uid || !$token) {
            log_message('error', 'information error');

            return false;
        }

        $condition = array(
            'p_uid' => $uid,
            'platform' => $this->platform,
        );

        $user = $this->User_model->get_one_by_condition_array($condition);

        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $uid,
                'create_date' => time(),
            );
            $user_id = $this->User_model->add($user);

            if (!$user_id) {
                log_message('error', 'Login error user create fail');

                return false;
            }
            $user['user_id'] = $user_id;
        }

        // generate random token and save it to cache
        $this->cache->save($user['user_id'].'_token', $token, 86400);

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
        $partner_id = $this->session->userdata('partner_id');

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
        $pkg = $this->session->userdata('9130_game_pkg');
        if (!$partner_id||$partner_id<100) {
            $partner_id=102;
        }
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        $spe_server_ids = array('8000');
        // $spe_server_ids = array('2','3','4','5','6','7','8','9','10','11','8000');
        if (in_array($serverId, $spe_server_ids)) {
            $game_url = 'http://lcby.gz.1251208707.clb.myqcloud.com/juhe/login/juhe';
        }
        // if ($serverId>=1&&$serverId<=32) {
            $game_url = 'https://lcby.gz.1251208707.clb.myqcloud.com/qimiao/login/serverlist';

        // }

        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&server_name=$server_name&pkg=$pkg&pf=$partner_id";
        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get_post('ext');
        $money = $this->input->get('money');
        $orderid = $this->input->get('order_id');
        $uid = $this->input->get('uid');
        $product_id = $this->input->get('product_id');
        $role_id = $this->input->get('role_id');
        $server_id = $this->input->get('server_id');
        $partner_id = $this->input->get('partner_id');
        $time = $this->input->get('time');
        $sign = $this->input->get('sign');
        $this->load->model('Game_order_model');
        $condition=array(
            'u_order_id'=>$order_id,
        );
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $my_game_id = $game_order->game_id;
        $my_key = $this->Game_model->get_key($my_game_id, 'key');
        $my_sign = md5("$orderid$uid$product_id$money$role_id$server_id$partner_id$order_id$time$my_key");
        if ($my_sign==$sign) {
            // $this->load->model('Cp_game_order_model');
            // $pay_back_check = $this->Cp_game_order_model->get_one_by_condition($condition);
            // if (!$pay_back_check) {
            //     $cp_order_info = array(
            //         'game_id'=>$game_order->game_id,
            //         'money'=>$money*100,
            //         'game_father_id'=>$game_order->game_father_id,
            //         'create_Date'=>$time,
            //         'role_id'=>$role_id,
            //         'cp_user_id'=>$uid,
            //         'u_order_id'=>$order_id,
            //         'cp_order_id'=>$orderid,
            //         'user_id'=>$game_order->user_id,
            //         'platform'=>$game_order->platform,
            //         'server_id'=>$game_order->ext,
            //     );
                if ($game_order->money!=intval($money*100)) {
                    return;
                }
                // $pay_back_response = $this->Cp_game_order_model->add($cp_order_info);
                // if (!$pay_back_response) {
                //     log_message('error', $this->platform.' pay back check is failed');
                // }
            // }
            return $order_id;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo '0';
    }

    public function notify_error()
    {
        echo '8';
    }

    public function sign_order($game_id = '')
    {
        $orderid = $this->input->get('orderid');
        $money = $this->input->get('money');
        $product = $this->input->get('product');
        $channel = $this->input->get('channel');
        $key = $this->Game_model->get_key($game_id, 'key');
        $sign = md5($orderid.$money.$product.$channel.$key);
        $data = array(
            'sign' => $sign,
        );

        return $data;
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
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }

    public function create_role($game_id)
    {
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

    public function init($game_id)
    {
        $client_id = $this->Game_model->get_key($game_id, 'client_id');
        $service = $this->Game_model->get_key($game_id, 'service');
        $redirect_uri = $this->Game_model->get_key($game_id, 'redirect_uri');
        $token = $this->input->get('token');
        $data = array(
            'client_id' => $client_id,
            'service' => $service,
            'redirect_uri' => $redirect_uri,
            'token' => $token,
        );
        log_message('debug', $this->platform.' login_init source service:'.$service.' redirect_uri:'.$redirect_uri.' token:'.$token.' client_id:'.$client_id);

        return $data;
    }
    public function focus($game_id)
    {
        $pf = $this->session->userdata('9130_partner_id');
        $data = array(
            'pf'=>$pf,
        );
        return $data;
    }
}
