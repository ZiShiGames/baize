<?php
/**
 * Created by PhpStorm.
 * User: lidaguo
 * Date: 2018/4/16
 * Time: 下午1:57
 */

class Baizhou_model extends CI_Model
{
    public $platform = 'baizhou';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_name = $this->input->get('userName');
        $user_id = $this->input->get('userId');
        $head_img_url = $this->input->get('headimgurl');

//        if(isset($this->input->get('headimgurl'))){
//            $head_img_url = $this->input->get('headimgurl');
//        }else{
//            $head_img_url = '';
//        }

        $sign_ori = $this->input->get('sign');

        if (!$user_id) {
            return false;
        }
        //check sign    //$userId, $key, $nonce
        $baizhouKEY = $this->Game_model->get_key($game_id,'key');
        if($this->baizhou_login_sign($user_id, $baizhouKEY, $this->input->get('nonce')) != $sign_ori){
            return false;
        }



        $condition = array(
            'p_uid' => $user_id,
            'platform' => $this->platform
        );
        // echo  $response->data->gouzaiId;

        $user = $this->User_model->get_one_by_condition_array($condition);

        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $user_id,
                'create_date' => time(),
                //add new param
                'nickname' => $user_name,
                'avatar' => $head_img_url
            );

            $user_id = $this->User_model->add($user);

            if (!$user_id) {
                log_message('error', 'Login error user create fail');

                return false;
            }

            $user['user_id'] = $user_id;
        }

        // generate random token and save it to cache
        $this->cache->save($user['user_id'].'_token', md5($user['user_id'].$user['platform'].time()), 86400);

        return $user['user_id'];
    }
    //Baizhou's API:For check sign information.
    public function baizhou_login_sign($userId, $key, $nonce) {
        return md5('key=' . $key . '&nonce=' . $nonce . '&userId=' . $userId);
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

        $condition = array('user_id' => $openId);
        $user = $this->User_model->get_one_by_condition($condition);
        if (!$user) {
            echo 'error';

            return;
        }

        $user_id = $user->p_uid;

        $openKey = $this->cache->get($openId.'_token');
        $appId = $game_id;
        $serverId = $this->input->get('serverId');
        $noice = time();
        $sign = md5($openId.$noice.$game->app_key);
        $game_url = $game->game_login_url;
        if ($game_id == 1013) {
            $test_id = array();
            if (in_array($openId, $test_id)) {
                $game_url = 'https://h5xxcz.yiqibing.com/hegeh5';
            }
        }
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "allu login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
//        log_message("debug",$this->platform.' '.json_encode($_POST).' '.json_encode($_HTTP_POST_VARS));
        $orderId = $this->input->get_post('orderId');
        $userId = $this->input->get_post('userId');
        $money = $this->input->get_post('money');
        $ext = $this->input->get_post('ext');
        $time = $this->input->get_post('time');
        $sign = $this->input->get_post('sign');
        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;
        $gameId=$this->Game_model->get_key($game_id, 'gameid');
        //$secret=$this->Game_model->get_key($game_id, 'secret');
        $secret_key = $this->Game_model->get_key($game_id,'key');
        $my_sign=md5("ext=$ext&money=$money&orderId=$orderId&time=$time&userId=$userId&secret_key=$secret_key");

        if (intval($money)!=$game_order->money) {
            return;
        }
        if ($sign==$my_sign) {
            return $ext;
        } else {
            return false;
        }




//        $requry = file_get_contents("php://input");
//        $r = json_decode($requry);
//        $order_id = $r->ext;
//        $money =$r->recharge;
//        $condition = array('u_order_id' => $order_id);
//        $this->load->model('Game_order_model');
//        $game_order = $this->Game_order_model->get_one_by_condition($condition);
//        if (intval($money*100)!=$game_order->money) {
//            return;
//        }
//        //check sign
//        $UID = $r->UID;
//        $game_id = $r->game_id;
//        $orderid=$r->order_id;
//        $recharge=$r->recharge;
//        $recharge_time=$r->recharge_time;
//        $state=$r->state;
//        $ext=$r->ext;
//        $sign=$r->sign;
//        $my_game_id = $game_order->game_id;
//        $key = $this->Game_model->get_key($my_game_id, 'gamekey');
//        $my_sign = md5("$UID$game_id$orderid$recharge$recharge_time$state$ext$key");
//        $create_date = strtotime($recharge_time);
//        if ($sign==$my_sign) {
//            return $order_id;
//        } else {
//            return false;
//        }
        //check sign done
    }

    public function notify_ok()
    {
        echo '{ “code”: 0, “msg”: “success”}';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }

    public function sign_order($game_id = '')
    {
        $time = time();
        $tempdata = array(
            'platform' => $this->input->get('platform'),
            'userId' => $this->input->get('userId'),
            'gameId' => $this->input->get('gameId'),
            'money' => $this->input->get('money'),
            'goodsId' => $this->input->get('goodsId'),
            'goodsName' => $this->input->get('goodsName'),
            'orderId' => $this->input->get('orderId'),
            'gameUrl' => $this->input->get('gameUrl'),
            'payCallBackUrl' => $this->input->get('payCallBackUrl'),
            'time' => $time,//$this->input->get('time'),
            'ext' => $this->input->get('ext'),
        );
        $secret_key = $this->Game_model->get_key($game_id,'key');
        ksort($tempdata); // 正向排序
        $keystr = urldecode(http_build_query($tempdata)); // 连接字符串
        $keystr .= '&secret_key=' . $secret_key; // 拼接secret_key
//显示参数
//echo $keystr;

        $sign = md5($keystr);
        //log_message('debug', $this->platform.' sign_str is '.$sign_str.' sign '.$sign);
        $data = array(
            'sign' => $sign,
            'gameid' => $this->input->get('gameId'),
            'time' => $time
        );
        return $data;
    }



    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
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
}