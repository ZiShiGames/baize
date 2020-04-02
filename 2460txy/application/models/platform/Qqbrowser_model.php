<?php

class Qqbrowser_model extends CI_Model
{
    public $platform = 'qqbrowser';
    public $APP_ID = '1575756617';
    public $AppKey = 'jGcEvbYvSwxSCzwW';
    public $AppDataKey = 'PChNYzDusqruWsms';

    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $user_id = $this->input->get('userId');
        $avatarUrl = $this->input->get('avatarUrl');
        $nickName = $this->input->get('nickName');

        // $isSubscribe = $this->input->get('isSubscribe');
        // $isShowSubscribe = $this->input->get('isShowSubscribe');
        // $shareCode = $this->input->get('shareCode');
        // $friendCode = $this->input->get('friendCode');
        // $channel = $this->input->get('channel');
        // $time = $this->input->get('time');
        // $sign = $this->input->get('sign');

        $this->session->set_userdata('uid', $user_id);
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
        //定义统计请求的地址：
        // $url = "http://h5.xileyougame.com/tongji/tongji_game_login/{$user_id}/{$game_id}/{$access_token}";
        // $this->Curl_model->curl_get($url);

        //如果这块功能实现完成， 需要把 allugame.com 的 controllers/game.php 的第153行注释掉，这块是现在使用的游戏登录统计。

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
        $data = $this->input->get('data');
        $reqsig = $this->input->get('reqsig');
        $this->load->library('cryption');
        $data = urlencode($data);
        // $sign_data = $this->cryption->UrlEncode($data,$reqsig);
        $info = $this->cryption->GetPlainData($data, $this->AppDataKey);
        $a = explode("&", $info);
        $b = explode("=", $a[0]);
        $c = explode("=", $a[7]);
        $money = $b[1];
        $order_id = $c[1];
        $this->load->model('Game_order_model');
        // echo $order_id;
        // echo '<br/>';
        // echo $money;
        if ($order_id && $money) {
            $condition = array('u_order_id' => $order_id);
            $this->load->model('Game_order_model');

            $game_order = $this->Game_order_model->get_one_by_condition($condition);
            if ($game_order) {
                if (intval($money*10) == $game_order->money) {
                    return $order_id;
                } else {
                    log_message('debug', $this->platform.' money errory '.$game_order->money." != $money");
                }
            }
        }



        return;
        // $userId = $this->input->get('userId');
        // if ($sign == $my_sign) {
        //     return $ext;
        // } else {
        //     return false;
        // }
    }

    public function notify_ok()
    {
        $reqsig = $this->input->get('reqsig');
        // echo $reqsig;
        $result = array();
        $respObj = array(
            'ret' => 0,
            'time' => time(),
            'nonce' => rand(),
            );
        $dataJson = json_encode($respObj);

        $result['data'] = $this->cryption->GetCipherData($dataJson, $this->AppDataKey);
        $queryMap2 = array(
            // 'c' => 'Qqweb',
            // 'm' => 'inquiry',
            'data' => $result['data'],
            'reqsig' => $reqsig, );
        $result['rspsig'] = $this->cryption->GetDataSig('/index.php/api/notify/qqbrowser/1126', 'GET', $queryMap2, $this->AppKey);
        echo json_encode($result);;
    }

    public function notify_error()
    {
        $reqsig = $this->input->get('reqsig');
        // echo $reqsig;
        $result = array();
        $respObj = array(
            'ret' => 1,
            'time' => time(),
            'nonce' => rand(),
            );
        $dataJson = json_encode($respObj);

        $result['data'] = $this->cryption->GetCipherData($dataJson, $this->AppDataKey);
        $queryMap2 = array(
            // 'c' => 'Qqweb',
            // 'm' => 'inquiry',
            'data' => $result['data'],
            'reqsig' => $reqsig, );
        $result['rspsig'] = $this->cryption->GetDataSig('/index.php/api/notify/qqbrowser/1126', 'GET', $queryMap2, $this->AppKey);
        echo json_encode($result);;
    }
    public function sign_order($game_id = '')
    {
        $sig = $this->input->get('sig');
        if ($sig) {
            if ($sig == 1) {
                return $this->GetAppSig();
            } elseif ($sig == 2) {
                return $this->GetPaySig();
            }
        } else {
        }
    }

    // 用来做批货url
    public function server_name()
    {
        $data = $this->input->get('data');
        $data = urlencode($data); // GetPlainData urldecode one more time , so it must be encode here.

        $reqsig = $this->input->get('reqsig');

        $result = array();

        $this->load->library('cryption');

        $map = $this->cryption->GetPlainData($data, $this->AppDataKey);


        $a = explode("&", $map);
        $b = explode("=", $a[1]);
        // print_r($b[1]);
        // print_r(explode("&",$map));
        // echo $map;
        // echo '<br/>';
        // echo json_encode($map);
        //
        // echo "<br/>";
        // echo "reqsig $reqsig <br/>";
        // echo  $a->payitem;
        // echo $map->payitem;
        $respObj = array(
                        'ret' => 0,
                        'time' => time(),
                        'nonce' => rand(),
                        'payamount' => $b[1]/10, );
                        // 'payamount' => 1, );

        $dataJson = json_encode($respObj);

        $result['data'] = $this->cryption->GetCipherData($dataJson, $this->AppDataKey);
        // echo json_encode($result['data']);

        $queryMap2 = array(
                        // 'c' => 'Qqweb',
                        // 'm' => 'inquiry',
                        'data' => $result['data'],
                        'reqsig' => $reqsig, );

        $result['rspsig'] = $this->cryption->GetDataSig('/index.php/api/server_name/qqbrowser/1126', 'GET', $queryMap2, $this->AppKey);
        echo json_encode($result);
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
    public function focus($game_id = '')
    {
        $gameid = $this->Game_model->get_key($game_id, 'gameid');

        return $gameid;
    }

    public function GetAppSig()
    {
        $this->load->library('cryption');
        $time = time();

        $nonce = 'longcheng';

        $src = $this->APP_ID.'_'.$time.'_'.$nonce;

        $appSigReponse = $this->cryption->GetAppSig($this->APP_ID, $time, $nonce, $this->AppKey, $this->AppDataKey);
        $appsigDataResponse = $this->cryption->GetCipherData($src, $this->AppDataKey);
        $data = array(
                'appSig' => $appSigReponse,
                'appsigData' => $appsigDataResponse,
            );
        if ($appSigReponse) {
            // $this->Output_model->json_print(0, 'ok', $data);
            return $data;
        } else {
            $this->Output_model->json_print(1, 'err');

            return false;
        }
    }

    public function GetPaySig()
    {
        $this->load->library('cryption');

        $customMeta = $this->input->get('customMeta');
        $payInfo = $this->input->get('payInfo');
        $payItem = $this->input->get('payItem');
        $qbopenid = $this->input->get('qbopenid');
        $qbopenkey = $this->input->get('qbopenkey');
        $reqTime = $this->input->get('reqTime');

        $data = array(
                'appid' => $this->APP_ID,
                'paysig' => '',
                'appsig' => '',
                'customMeta' => $customMeta,
                'payInfo' => $payInfo,
                'payItem' => $payItem,
                'qbopenid' => $qbopenid,
                'qbopenkey' => $qbopenkey,
                'reqTime' => $reqTime,
            );

        $paysig = $this->cryption->GetDataSig('/', 'POST', $data, $this->AppKey);

        $res = array('paysig' => $paysig);

        return $res;
    }
}
