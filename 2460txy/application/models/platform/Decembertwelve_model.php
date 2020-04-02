<?php

class Decembertwelve_model extends CI_Model
{
    public $platform = 'decembertwelve';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login()
    {
        $uid = $this->input->get('uid');//uid用户唯一ID

        $condition = array(
            'p_uid' => $uid,
            'platform' => $this->platform,
        );

        $user = $this->User_model->get_one_by_condition_array($condition);

        if (!$user) {
            $user = array(
                'platform' => $this->platform,
                'p_uid' => $uid,
                'nickname' => $username,
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
                $game_url = 'http://122.152.194.83:8083/api';
            }
        }
        $url = "$game_url?openId=$openId&openKey=$openKey&noice=$noice&appId=$appId&sign=$sign&serverId=$serverId";
        log_message('debug', "allu login:$url");

        header("Location: $url");
    }

    // return order and do the sign varification
    public function get_order_id()
    {
        $order_id = $this->input->get('order_id');
        $sign = $this->input->get('sign');
        if (!$order_id || !$sign) {
            return;
        }

        return $order_id;
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }

    public function sign_order($game_id = '')
    {
        $order_id = $this->input->get('order_id');
        $money = $this->input->get('money');
        $openId = $this->input->get('openId');
        $userId = $this->input->get('userId');
        $goodsName = $this->input->get('goodsName');

        $game = $this->Game_model->get_by_game_id($game_id);

        $url = 'http://h5.allugame.com/index.php/api/app_order?';
        $url .= 'uid='.$userId;
        $url .= '&game_id='.$game_id;
        // $url .= '&game_id=20';
        $url .= '&orderNo='.$order_id;
        $url .= '&goodsName='.$goodsName;
        $url .= '&gameName='.$game->game_name;
        $url .= '&money='.intval($money * 100);
        $url .= '&notify='.'h5sdk.zytxgame.com/index.php/api/notify/decembertwelve/1124';
        $content = $this->Curl_model->curl_get($url);
        if (!$content) {
            log_message('debug', $this->platform." sign_order $url response null");

            return false;
        }
        $response = json_decode($content);
        if (!$response) {
            log_message('debug', $this->platform." sign_order $url response error $content");

            return false;
        }
        if ($response->c != 0) {
            log_message('debug', $this->platform." sign_order $url response error $content");

            return false;
        }

        $url = 'http://ipay.iapppay.com:9999/payapi/order';
        $transdata = array();
        $transdata['appid'] = '3012208265';
        $transdata['waresid'] = 1;
        $transdata['cporderid'] = ''.$response->d->order_id;
        $transdata['price'] = (float) $money;
        $transdata['currency'] = 'RMB';
        $transdata['appuserid'] = "$openId";
        $transdata['notifyurl'] = 'http://h5.allugame.com/index.php/notify/aibei';

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
                    'url_r' => 'http://h5.allugame.com/index.php/game/redirect/41',
                    'url_h' => 'http://h5.allugame.com/index.php/game/redirect/41',
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
