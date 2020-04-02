<?php

class Yybsdk_model extends CI_Model
{
    public $platform = 'yybsdk';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login()
    {
        $userId = $this->input->get('openid');
        $AccessToken = $this->input->get('AccessToken');
        $PayToken = $this->input->get('PayToken');
        $pf = $this->input->get('pf');
        $pfkey = $this->input->get('pfkey');
        if (!$userId || !$AccessToken || !$PayToken || !$pf || !$pfkey) {
            return false;
        }

        $condition = array(
                    'p_uid' => $userId,
                    'platform' => $this->platform,
                );

        $user = $this->User_model->get_one_by_condition_array($condition);
        if (!$user) {
            $user = array(
                    'platform' => $this->platform,
                    'p_uid' => $userId,
                    'nickname' => '',
                    'avatar' => '',
                    'create_date' => time(),
                );
            $user_id = $this->User_model->add($user);
            if (!$user_id) {
                log_message('error', "Login error user create $content");

                return false;
            }

            $user['user_id'] = $user_id;
        }

        // generate random token and save it to cache
        $this->cache->save($user['user_id'].'_token', md5($user['user_id'].$user['platform'].time()), 86400);

        $AccessToken = $this->input->get('AccessToken');
        $PayToken = $this->input->get('PayToken');
        $pf = $this->input->get('pf');
        $pfkey = $this->input->get('pfkey');
        $data = array(
            'openid' => $userId,
            'AccessToken' => $AccessToken,
            'PayToken' => $PayToken,
            'pf' => $pf,
            'pfkey' => $pfkey,
        );
        $this->cache->save($userId.'_yybsdk_info', $data, 60 * 60 * 24);

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
        $openid = $this->input->get('openid');
        $order_id = $this->input->get('orderid');
        $money = $this->input->get('money');
        $AccessToken = $this->input->get('AccessToken');
        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');

        $game_order = $this->Game_order_model->get_one_by_condition($condition);

        $xishu = 100;
        if ($order_id && $money && $AccessToken && $openid) {
            $xishu = 1;
            $info = $this->cache->get($openid.'_yybsdk_info');
            echo 'info '.json_encode($info);
            echo " $openid $order_id $money $AccessToken";
            log_message('debug', $this->platform.' info '.json_encode($info)." $openid $order_id $money $AccessToken");
            if ($info['AccessToken'] != $AccessToken) {
                log_message('debug', $this->platform.' AccessToken error');

                return;
            } elseif ($game_order&&!$AccessToken) {
                if (intval($money * $xishu) == $game_order->money) {
                    log_message('debug', $this->platform.' money is '.$money.' '.$game_order->money);
                    return $order_id;
                } else {
                    log_message('debug', 'yybsdk money errory '.$game_order->money." != $money");

                    return;
                }
            } else {
                return $order_id;
            }


            if ($game_order&&!$AccessToken) {
                if (intval($money * $xishu) == $game_order->money) {
                    log_message('debug', $this->platform.' money is '.$money.' '.$game_order->money);
                    return $order_id;
                } else {
                    log_message('debug', 'yybsdk money errory '.$game_order->money." != $money");

                    return;
                }
            }
        }
        // echo 'p not enough';
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }

    public function focus($game_id='')
    {
        $game_id = 1000;
        $order_id = $this->input->get('order_id');
        $money = $this->input->get('money');
        $openId = $this->input->get('openId');
        $userId = $this->input->get('userId');
        $goodsName = $this->input->get('goodsName');

        $game = $this->Game_model->get_by_game_id($game_id);


        $url = 'http://ipay.iapppay.com:9999/payapi/order';
        $transdata = array();
        $transdata['appid'] = '3012208265';
        $transdata['waresid'] = 1;
        $transdata['cporderid'] = ''.$order_id;
        $transdata['price'] = (float) $money;
        $transdata['currency'] = 'RMB';
        $transdata['appuserid'] = "$openId";
        $transdata['notifyurl'] = 'http://h5sdk.zytxgame.com/index.php/notify/aibei';

        $appkey = 'MIICXAIBAAKBgQCwV8rZ4GqWdfazJPJArHqUZKBq+eoAa4dCTt8yTBMhpvZtx6JJceQ8jaj7bNATUT7XybFJ5949wKbc3wuQXnujkfTtn86ggIuGr3m0iWzU7ngqqs3IZ3vV1cB0Jkvy53Emf5Jj+sqdk7DjT+4r1v2VgWMfvC7yKTUuT+dW/UyvtwIDAQABAoGAfN3Nj7WvA9eH1pZEy7LWIZmXVeic36tEXZmHxg/EREH7oQSJT8RLvuz4SQBl3ifbfeUdmp2K6uMtxJxTjei5Vo5jI5xIb95l93FJXf+rbYPhsi7HqCU8pk0M0VNRPEeuQildBjlics6WuktzKyc4tKiKmxSw2ZYj4wAlUkAUGCECQQDeG6r/DzmwT+4I1WWSqh/6Ny7fkWFNOfITKDJNP174pgdtAElKG9OPYCVsBtOo4vTJuhFKVwd5mDVlZxFBpOMpAkEAy0BgDmeqeXsU1QU0ZtDBpVxeBMCNNLAtvQn5DQ5NHhmn0X3AI74oOO9ChIPNB28WJXn4LD9OVMJEz+6DM5U33wJBAJ2atHPYse7SSO4rvq+b2KUMk05BMvJBs+y0ET2PQizeY1aNZXQY2r5aUzOchITKxzh9t9cwejVmND2ILU6PWkECQC8EHTQ31r9zMUZ1hcGi2KifzT/cKs3dUzc/b1UN0dj8pk1XgXLDMhq5ffGZa3wkvkK9DCNwIXaJ2dEfo0nzYpECQFSMMz3Y9c8aF3Hgx00z7Sckp3zkd3V4ccSzmwTa3QVfF6rXYuf4G/fFgMulEdn+231ivC7CA0OKuOa5woVQHaQ=';
        $platpkey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCZUXOspYzJhdckDp8eVGJa5SfNRWj22SjdnGh1/qugGvfE9Cbr/DwIHxguTVSDmBqjLNn8OVZ2cgGCxITApy4CoSZFraMcdQSBz/LBYKMij5IHZJoZPgXrvAyJZiysWaqfLlUSgUS6yF8TyoxzlHoZ759tn6w+xA0umQ2ELReleQIDAQAB';

        $reqData = $this->composeReq($transdata, $appkey);

        $this->load->model('Curl_model');
        $content = $this->Curl_model->curl_post($url, $reqData);
        if ($content) {
            $ps = $this->convertUrlQuery($content);
            $res = json_decode(urldecode($ps['transdata']));

            // echo "url :$url <br/>";
            // echo "reqData :$reqData <br/>";
            // echo 'content : ';
            // echo urldecode($content);
            // echo '<br>';

            if (!isset($res->code)) {
                $transid = $res->transid;
                $h5data = array(
                    'tid' => $transid,
                    'app' => '3012208265',
                    'url_r' => 'http://h5.xileyougame.com/index.php/game/redirect/41',
                    'url_h' => 'http://h5.xileyougame.com/index.php/game/redirect/41',
                );

                $reqData = $this->h5composeReq($h5data, $appkey);
                $pay_url = 'https://web.iapppay.com/pay/gateway?'.$reqData;

                return $pay_url;
            } else {
                log_message('debug', $this->platform." sign_order $url response error $content");
            }
        }
    }
    private function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }
    public function composeReq($reqJson, $vkey)
    {
        //获取待签名字符串
       $content = json_encode($reqJson);
       //格式化key，建议将格式化后的key保存，直接调用
       $vkey = $this->formatPriKey($vkey);

       //生成签名
       $sign = $this->sign($content, $vkey);

       //组装请求报文，目前签名方式只支持RSA这一种
       $reqData = 'transdata='.urlencode($content).'&sign='.urlencode($sign).'&signtype=RSA';

        return $reqData;
    }
    public function sign($data, $priKey)
    {
        //转换为openssl密钥
      $res = openssl_get_privatekey($priKey);

      //调用openssl内置签名方法，生成签名$sign
      openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);

      //释放资源
      openssl_free_key($res);

      //base64编码
      $sign = base64_encode($sign);

        return $sign;
    }
    public function h5composeReq($reqJson, $vkey)
    {
        //获取待签名字符串
        $content = json_encode($reqJson);
        //格式化key，建议将格式化后的key保存，直接调用
        $vkey = $this->formatPriKey($vkey);

        //生成签名
        $sign = $this->sign($content, $vkey);

        //组装请求报文，目前签名方式只支持RSA这一种
        $reqData = 'data='.urlencode($content).'&sign='.urlencode($sign).'&sign_type=RSA';

        return $reqData;
    }
    public function formatPriKey($priKey)
    {
        $fKey = "-----BEGIN RSA PRIVATE KEY-----\n";
        $len = strlen($priKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey.substr($priKey, $i, 64)."\n";
            $i += 64;
        }
        $fKey .= '-----END RSA PRIVATE KEY-----';

        return $fKey;
    }


    public function sign_order($game_id = '')
    {
        $openId = $this->input->get('openId');

        $data = $this->cache->get($openId.'_yyb_info');

        return $data;
    }

    public function login_collect($data)
    {
    }
    public function create_role_collect($data)
    {
    }
    public function init($game_id = '')
    {
        $user_id = $this->input->get('uid');
        $server_id = $this->input->get('server_id');
        $this->load->model('Game_order_model');
        log_message('debug', $this->platform.' payinfo is '.$user_id.' '.$server_id.' ');
        $content= array(
                'user_id'=>$user_id,
                'status'=>'2',
                // 'ext'=>$server_id,
        );
        $this->db->where($content);

        $request =  $this->Game_order_model->get_one_by_condition($content);

        if ($request) {
            $type = 'yyb';
            // $type = 'aibei';
        } else {
            $type = 'yyb';
        }
        log_message('debug', $this->platform.' pay type is '.$type);
        $this->session->set_userdata('pay_type', $type);
        $data = array(
                'pay_type'=>$type,
        );
        return $data;
    }
}
