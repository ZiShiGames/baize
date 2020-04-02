<?php

class Langaohuyu_model extends CI_Model
{
    public $platform = 'langaohuyu';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('userid');
        $this->session->set_userdata('userId', $user_id);

        if (!$user_id) {
            return false;
        }
        $condition = array(
                            'p_uid' => $user_id,
                            'platform' => $this->platform,
                        );
        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                            'platform' => $this->platform,
                            'p_uid' => $user_id,
                            'create_date' => time(),
                        );
            $user_id = $this->User_model->add($user);
            if (!$user_id) {
                log_message('error', $this->platform." Login error user create $content");

                return false;
            }
            $user['user_id'] = $user_id;
        }

        $this->cache->save($user['user_id'].'_token', md5($user['user_id'].$user['platform'].time()), 86400);

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

        //执行统计请求
        $access_token = md5(substr(time(), 0, 8).'aoyouxi');

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
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "allu login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        log_message('debug', $this->platform.' my money '.json_encode($_POST));

        // $request = file_get_contents("php://input");
        // if ($request) {
        //     log_message('debug', $this->platform.' get order '.$request);
        // }

        // $money = $this->input->get_post('money');
        // $request = json_decode($request);
        $custom_info = json_decode($_POST['data'])->goodsOrderNum;
        $result= json_decode($_POST['data'])->result;
        if ($result==1) {
            return $custom_info;

        }
        return false;
        // log_message('debug',$this->platform.' '.$custom_info);
        //
        // // $requestId = $this->getRandom(32);
        // $this->session->set_userdata('rsid',$responseid);
        // $this->session->set_userdata('req',$_responseid);
        // // $requestId = $this->input->get('req');
        // $requestId = $this->session->userdata('req');
        // $json_data = array(
        //     'goodsOrderNum'=>$custom_info
        // );
        // // $_responseid = $this->input->get('rsid');
        // $_responseid = $this->session->userdata('rsid');
        // $sign = $this->hash($_responseid, json_encode($json_data));
        // $data = array(
        //     'requestId'=>$requestId,
        //     'data'=>json_encode($json_data),
        //     'sign'=>$sign
        // );
        // log_message('debug',$this->platform.' data '.json_encode($data));
        // $response = json_decode($this->Curl_model->curl_post('http://pay.dbdna.com/payment/goodsOrder/getOrder', $data));
        // if (@!$response||!isset($response->data)) {
        //     log_message('debug', $this->platform.' 请求失败 '.$custom_info);
        //     return false;
        // } elseif (@!isset(json_decode(json_encode($response->data), true)[2])) {
        //     log_message('debug', $this->platform.' 未支付 '.$custom_info);
        //     return false;
        // }
        //
        // log_message('debug', $this->platform.' post info '.json_encode($response));
        //
        // $condition = array('u_order_id' => $custom_info);
        // $this->load->model('Game_order_model');
        // $game_order = $this->Game_order_model->get_one_by_condition($condition);
        // $game_id = $game_order->game_id;
        // log_message('debug', $this->platform.' my money '.$money.' '.$game_order->money);
        // $money =  $response->data->realPrice;
        // if ($money*100==$game_order->money) {
        //     return $custom_info;
        // } else {
        //     log_message('debug', $this->platform.' 金额不符 '.$custom_info);
        //     return false;
        // }
    }

    public function notify_ok()
    {
        echo '{"success":"true"}';
    }

    public function notify_error()
    {
        echo '{"success":"false"}';
        ;
    }
    public function sign_order($game_id = '')
    {
        $_responseid = $this->getRandom(32);
        $responseid = $this->Curl_model->curl_get("http://pay.dbdna.com/payment/getResponseId?requestId=".$_responseid);
        $order_id = $this->input->get('order_id');
        $app_id = $this->Game_model->get_key($game_id, 'CPappid');
        $userId = $this->session->userdata('userId');
        $money = $this->input->get('money');
        $key = $this->Game_model->get_key($game_id, 'CPappSecret');

        $json_data=array(
            'goodsOrderNum'=>$order_id,
            'orderName'=>$order_id,
            'appid'=>$app_id,
            'userId'=>$userId,
            'userName'=>$userId,
            'shopId'=>1,
            'shopName'=>'1号',
            'totalPrice'=>$money,
            'realPrice'=>$money
        );
        $_json_data = $json_data;
        // $_json_data['requestId']=$responseid;
        $data = array(
            'requestId'=>$_responseid,
            'data'=>json_encode($json_data),
            'sign'=>$this->hash($responseid, json_encode($json_data)),
        );


        $response = json_decode($this->Curl_model->curl_post("http://pay.dbdna.com/payment/goodsOrder/createGoodsOrder", $data));
        if (isset($response->success) && $response->success =='true') {
            // $callback = urlencode("http://h5sdk-xly.xileyougame.com/index.php/api/notify/langaohuyu/1358?order=$order_id&money=$money&rsid=$responseid&req=$_responseid");
            $callback = urlencode("http://h5sdk-xly.xileyougame.com/index.php/enter/play/langaohuyu/1358?userid=$userId");
            $this->session->set_userdata('rsid', $responseid);
            $this->session->set_userdata('req', $_responseid);
            $data =array(
                'pay'=>"http://pay.dbdna.com/payment/payOrder/pay.html?goodsOrderNum=$order_id&callBackUrl=$callback"
            );
            return $data;
        } else {
            echo json_encode($response);
            return;
        }
    }

    public function init($game_id)
    {
        $data = array('usertype' => $this->session->userdata('usertype'));

        return $data;
    }
    public function create_role_collect($data)
    {
    }

    public function login_collect($data)
    {
    }
    public function focus($game_id='')
    {
        $gameid= $this->Game_model->get_key($game_id, 'gameid');
        return $gameid;
    }
    private function getRandom($param)
    {
        $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for ($i=0;$i<$param;$i++) {
            $key .= $str{mt_rand(0, 32)};    //生成php随机数
        }
        return $key;
    }
    private function hash($salt, $a)
    {
        //echo $a;
        //$salt="kasdkajdsjajds"; //定义一个salt值，程序员规定下来的随机字符串
        $t=$salt.$a; //把密码和salt连接
        $b=md5($t); //执
        return $b; //返回散列
    }
}
