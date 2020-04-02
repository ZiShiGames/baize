<?php

class Hebao_model extends CI_Model
{
    public $platform = 'hebao';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $token = $this->input->get('token');
        $ps = $this->input->get('ps');
        $nonceStr = $this->input->get('nonceStr');
        $timeStamp = $this->input->get('timeStamp');
        $sign = $this->input->get('sign');

        $this->session->set_userdata('token', $token);

        $url = 'http://hb.xiaougame.com/mobile/getGameUserInfo';
        $data = array(
          'token' => $token,
          'nonceStr' => $nonceStr,
          'timeStamp' => $timeStamp,
          'sign' => $sign
        );
        $content = $this->Curl_model->curl_post($url, $data);
        if (!$content) {
            log_message('error', "Login empty content $url");
            return false;
        }
        $response = json_decode($content);
        if (!isset($response->ret)) {
            log_message('error', "Login error content $url $content");
            return false;
        }

        if ($response->ret == 0) {
          $user_id = $response->user->uid;

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
        } else {
            log_message('error', "Login error code $content ");
        }

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
        log_message('debug', json_encode($_POST));
        $token = $this->input->get_post('token');
        $oid = $this->input->get_post('oid');
        $orderId = $this->input->get_post('orderId');
        $plan = $this->input->get_post('plan');
        $money = $this->input->get_post('money');
        $first_pay = $this->input->get_post('first_pay');
        $nonceStr = $this->input->get_post('nonceStr');
        $timeStamp = $this->input->get_post('timeStamp');
        $sign = $this->input->get_post('sign');

        $ext = $orderId;
        $condition = array('u_order_id' => $ext);
        $this->load->model('Game_order_model');
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        $game_id = $game_order->game_id;

        $key=$this->Game_model->get_key($game_id, 'CPkey');
        $sign_str="first_pay=$first_pay&money=$money&nonceStr=$nonceStr&oid=$oid&orderId=$orderId&plan=$plan&timeStamp=$timeStamp&token=$token&key=$key";
        $my_sign =strtoupper(md5($sign_str));

        if(intval($money*100)!=$game_order->money){
            return false;
        }

        if ($sign==$my_sign) {
            return $ext;
        } else {
            return false;
        }
    }

    public function notify_ok()
    {
        echo '{"ret":0,"msg":"success"}';
    }
    public function notify_error()
    {
        echo '{"ret":1,"msg":"failed"}';
    }
    public function sign_order($game_id = '')
    {
        $key=$this->Game_model->get_key($game_id, 'CPkey');

        $token = $this->input->get('token');
        $orderId = $this->input->get('orderId');
        $plan = $this->input->get('plan');
        $money = $this->input->get('money');
        $ps = $this->input->get('ps');
        $first_pay = $this->input->get('first_pay');
        $timeStamp = time();
        $nonceStr = md5($timeStamp);

        $sign=strtoupper(md5("first_pay=$first_pay&money=$money&nonceStr=$nonceStr&orderId=$orderId&plan=$plan&ps=$ps&timeStamp=$timeStamp&token=$token&key=$key"));

        $data = array(
          'orderId' => $orderId,
          'money' => $money,
          'nonceStr' => $nonceStr,
          'timeStamp' => $timeStamp,
          'sign' => $sign
        );

        return $data;
    }

    public function init($game_id)
    {
        $data = array('usertype' => $this->session->userdata('usertype'));

        return $data;
    }
    public function create_role_collect($data)
    {
      // 角色创建完成后
      $game_id = $data['game_id'];
      $key = $this->Game_model->get_key($game_id, 'CPkey');
      $timeStamp = time();
      $nonceStr = md5($timeStamp);
      $result = "1"; // 创角成功
      $token = $this->session->userdata('token');
      $sign = strtoupper(md5("nonceStr=$nonceStr&result=$result&timeStamp=$timeStamp&token=$token&key=$key"));

      $role_data = array(
        'token' => $token,
        'result' => $result,
        'nonceStr' => $nonceStr,
        'timeStamp' => $timeStamp,
        'sign' => $sign
      );

      $url = 'http://hb.xiaougame.com/mobile/postRegResult';
      $content = $this->Curl_model->curl_post($url, $role_data);

      log_message('debug', "$this->platform create_role_report  : $content  ");

    }

    public function login_collect($data)
    {
    }
    public function focus($game_id='')
    {
        $gameid= $this->Game_model->get_key($game_id, 'gameid');
        return $gameid;
    }
}
