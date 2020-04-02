<?php

class Baidu_model extends CI_Model
{
    public $platform = 'baidu';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login($game_id)
    {
        $code = $this->input->get('code');


        if (isset($code)&&$code) {
            $url = urlencode("http://h5sdk.zytxgame.com/index.php/enter/play/baidu/1117");
            $api_key = $this->Game_model->get_key($game_id, 'APIKey');
            $SecretKey = $this->Game_model->get_key($game_id, 'SecretKey');
            $response = "https://openapi.baidu.com/oauth/2.0/token?grant_type=authorization_code&code=$code&client_id=$api_key&client_secret=$SecretKey&redirect_uri=$url";
            log_message('debug', $this->platform.' response '.$response);
            // echo $response;
            // return;
            $request = $this->Curl_model->curl_get($response);
            $request=json_decode($request);
            $access_token=$request->access_token;
        } else {
            $access_token = $this->input->get('accessToken');
        }


        if (isset($access_token)&&$access_token) {
            $this->session->set_userdata('access_token', $access_token);
            log_message('debug', $this->platform.' request is '.json_encode($request));
            $userresponse="https://openapi.baidu.com/rest/2.0/passport/users/getLoggedInUser?access_token=$access_token";
            log_message('debug', $this->platform.' userresponse '.$userresponse);
            $userrequest = $this->Curl_model->curl_get($userresponse);
            log_message('debug', $this->platform.' userrequest '.$userrequest);
            $userrequest = json_decode($userrequest);

            $user_id = $userrequest->uid;
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
        }else{
            log_message('debug',$this->platform.' access_token is null ');
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
        $order_id = $this->input->get('order_id');
        $sign = $this->input->get('sign');
        $money = $this->input->get('money');
        if (!$order_id || !$sign) {
            return;
        }
        $this->load->model('Game_order_model');
        $condition=array('u_order_id'=>$order_id);
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (!$game_order) {
            return;
        }
        if ($game_order->money!= intval($money)) {
            return;
        }

        return $order_id;
    }

    public function notify_ok()
    {
        echo '{errcode: 0,errmsg: ""}';
    }

    public function notify_error()
    {
        echo 'fail';
        ;
    }
    public function sign_order($game_id = '')
    {
        $order_id = $this->input->get('order_id');
        $money = $this->input->get('money');
        $tdou_num = $money;
        $scene_id='20000197';
        $goods_num =1;
        $goods_pic = "https://imgsa.baidu.com/forum/w%3D580/sign=32a86264a064034f0fcdc20e9fc27980/056c25176d224f4a59f7dfaa02f790529922d1b2.jpg";
        $goods_name = '元宝';
        $goods_unit='个';
        $goods_duration ='永久';
        $goods_user_level=0;
        $pay_type=0;
        $ext = $order_id;
        $access_token = $this->session->userdata('access_token');
        $open_id = $this->session->userdata('uid');
        $tb_timestamp = time();
        // $key = $this->Game_model->get_key($game_id, 'SecretKey');
        $key ='0f565f30f700ad47';

        // //get user info
        // $tb_sign = MD5("open_id=$open_id&tb_timestamp=$tb_timestamp&$key");
        // $url = "https://openapi.baidu.com/rest/2.0/tieba/v1/mall/getUserInfo";
        // $post_data = array(
        //     'access_token'=>$access_token,
        //     'open_id'=>$open_id,
        //     'tb_timestamp'=>$tb_timestamp,
        //     'tb_sign'=>$tb_sign,
        // );
        // $response = $this->Curl_model->curl_post($url, $post_data);
        // echo $response;


        $url = "https://openapi.baidu.com/rest/2.0/tieba/v1/mall/genThirdOrder";
        $sign_data = array(
            'open_id'=>$open_id,
            'tdou_num'=>$tdou_num,
            'scene_id'=>$scene_id,
            'goods_num'=>$goods_num,
            'goods_pic'=>urlencode($goods_pic),
            'goods_name'=> urlencode($goods_name) ,
            'goods_unit'=>urlencode($goods_unit),
            'goods_duration'=>urlencode($goods_duration),
            'goods_user_level'=>$goods_user_level,
            'pay_type'=>$pay_type,
            'tb_timestamp'=>$tb_timestamp,
        );
        $this->load->model('Common_model');
        $sign_str = $this->Common_model->sort_params($sign_data).'&'.$key;
        // $sign_str = "access_token=$access_token&goods_duration=$goods_duration&goods_name=$goods_name&goods_num=$goods_num&goods_pic=$goods_pic&goods_unit=$goods_unit&goods_user_level=$goods_user_level&open_id=$order_id&pay_type=$pay_type&scene_id=$scene_id&tb_timestamp=$tb_timestamp&tdou_num=$tdou_num&$key";
        $sign_str = "goods_duration=$goods_duration&goods_name=$goods_name&goods_num=$goods_num&goods_pic=$goods_pic&goods_unit=$goods_unit&goods_user_level=$goods_user_level&open_id=$open_id&pay_type=$pay_type&scene_id=$scene_id&tb_timestamp=$tb_timestamp&tdou_num=$tdou_num&$key";
        // log_message('debug', $this->platform.' sign str is '.$sign_str);
        $tb_sign = MD5($sign_str);
        $post_data = array(
            'access_token'=>$access_token,
            'open_id'=>$open_id,
            'tdou_num'=>$tdou_num,
            'scene_id'=>$scene_id,
            'goods_num'=>$goods_num,
            'goods_pic'=>urlencode($goods_pic),
            'goods_name'=> urlencode($goods_name) ,
            'goods_unit'=>urlencode($goods_unit),
            'goods_duration'=>urlencode($goods_duration),
            'goods_user_level'=>$goods_user_level,
            'pay_type'=>$pay_type,
            'ext'=>$ext,
            'tb_timestamp'=>$tb_timestamp,
            'tb_sign'=>$tb_sign,
        );
        $response = json_decode($this->Curl_model->curl_post($url, $post_data));
        if (isset($response->error_code)) {
            return;
        } elseif (isset($response->data)) {
            $baidu_order_id = $response->data->order_id;
            $pay_data = array(
                'open_id'=>$open_id,
                'order_id'=>$baidu_order_id,
                'tdou_num'=>$tdou_num,
                'scene_id'=>$scene_id,
                'goods_num'=>$goods_num,
                'goods_price'=>$tdou_num,
                'goods_pic'=>$goods_pic,
                'goods_name'=>$goods_name,
                'goods_unit'=>$goods_unit,
                'goods_duration'=>0,
                'goods_user_level'=>$goods_user_level,
                'pay_type'=>$pay_type,
                'tb_timestamp'=>$tb_timestamp,
                'from'=>'百度游戏',
            );
            return $pay_data;
        }
        // $game_id = 1000;
        // $order_id = $this->input->get('order_id');
        // $money = $this->input->get('money');
        // $openId = $this->input->get('openId');
        // $userId = $this->input->get('userId');
        // $goodsName = $this->input->get('goodsName');
        //
        // $game = $this->Game_model->get_by_game_id($game_id);
        //
        //
        // $url = 'http://ipay.iapppay.com:9999/payapi/order';
        // $transdata = array();
        // $transdata['appid'] = '3016045877';
        // $transdata['waresid'] = 1;
        // $transdata['cporderid'] = ''.$order_id;
        // $transdata['price'] = (float) $money;
        // $transdata['currency'] = 'RMB';
        // $transdata['appuserid'] = "$openId";
        // $transdata['notifyurl'] = 'http://h5sdk.zytxgame.com/index.php/notify/aibei';
        //
        // $appkey = 'MIICXgIBAAKBgQDfzaW7XwgoXRTYJYCQP+cmXGWa/PGNcdsGBSgi4MC6wb189DXyH/fVvfTF1gIiIAKgrzR0Qzld/Ch+1VBL9osUGY19u3tvSHUZ/F4VCTi93JUV9P9r/xIewmEkAy2lXajbWDqBYRjs4Crk3Nh7vVzyCed4VidnQj4CZzkEyeoymQIDAQABAoGBAJNfxEcCaUjLIrLC30oeCoTES1QoRJgz5VqtgqSVA9T3R2RFHFD4pCboE4tDRdxa3+AX/56fteMh7Ti4F0wuaZ3zh6VIcyze5Y+yNVTqC6dPFemw1rzi7nJfv36v4xQEDQExsWB14fSijXfVyEjGrF2tM6cC924MLUdfE8dSDVhBAkEA72Xa7injV6WSg76FYlCxautMFsbSSvXSw2ggh9s5kwNZipoiVkxJYnHDLL7ZHUoVbGQ7cXOqp2GvXi5bp5Gt/QJBAO9S7zqbPNHU1C9MTiSdA1cIh4IzrbI9b9wIwSRrhpXktfDrE9pbQH7Tbn/cVLudbjNAWQTi5tfGKkreKB1oC80CQQCvQdfWx1+hyJrMS+wGH6Di70MS4ZcOPYyAdXhrPPiXQbqJl3FP0CVhJnuGBGmZ4aRxZ6eE1PK3+vGRd0quEB5ZAkBK2YRqrvyhn8/RDyttdhICWW+QQDt2AJMInVBS5LJOFR72P3+RDnMod1Ya9T0nBIDf1KNCzPhnydmWWs7vC4iNAkEAkj8AnVHVwY0zMIjQkPkUCEIdpHOwJdSmemJ1M8LrR6Yx+L8t05pjwk5nFfVdK2JMvxd5rf3HYAxame6t8OrWhQ==';
        // $platpkey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDMOrNazkgSWxPDy5wLIBrH+Vh1pVtE+duqkPCxsUO7SFwh/QcMr9qsZtdv4krNGSWZnkE+I6xlvqrs2YB5juKExGHk1KZv6jN95fEZAo2eZnf4neE7lEt+x8xncnhptRM2AJn9bNO/AkPbsQAv6t6ylLXErltRcRZnrYhcjzGxGQIDAQAB';
        //
        // $reqData = $this->composeReq($transdata, $appkey);
        //
        // $this->load->model('Curl_model');
        // $content = $this->Curl_model->curl_post($url, $reqData);
        // if ($content) {
        //     $ps = $this->convertUrlQuery($content);
        //     $res = json_decode(urldecode($ps['transdata']));
        //
        //         // echo "url :$url <br/>";
        //         // echo "reqData :$reqData <br/>";
        //         // echo 'content : ';
        //         // echo urldecode($content);
        //         // echo '<br>';
        //
        //         if (!isset($res->code)) {
        //             $transid = $res->transid;
        //             $h5data = array(
        //                 'tid' => $transid,
        //                 'app' => '3016045877',
        //                 'url_r' => 'http://h5sdk.zytxgame.com/index.php/enter/play/baidu/1117',
        //                 'url_h' => 'http://h5sdk.zytxgame.com/index.php/enter/play/baidu/1117',
        //             );
        //
        //             $reqData = $this->h5composeReq($h5data, $appkey);
        //             $pay_url = 'https://web.iapppay.com/pay/gateway?'.$reqData;
        //             $data = array(
        //                 'pay_url'=>$pay_url,
        //             );
        //             return $data;
        //         } else {
        //             log_message('debug', $this->platform." sign_order $url response error $content");
        //         }
        // }
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
    public function order_query()
    {
        $this->load->model('Game_order_model');
        $user_ids = $this->input->get('user_ids');
        if ($user_ids) {
            $ids = explode(',', $user_ids);
            $condition = array('platform' => $this->platform);
            $where_in = array(
                        'name' => 'p_uid',
                        'values' => $ids,
                    );

            $users = $this->User_model->get_by_condition($condition, null, null, null, null, null, $where_in);
            if ($users) {
                $ids = array();
                foreach ($users as $one) {
                    $ids[] = $one->user_id;
                }

                $where_in = array(
                            'name' => 'user_id',
                            'values' => $ids,
                        );

                $res = $this->Game_order_model->get_by_condition($condition, null, null, null, null, null, $where_in);
                if ($res) {
                    foreach ($res as $order) {
                        foreach ($users as $one_user) {
                            if ($order->user_id == $one_user->user_id) {
                                $order->p_uid = $one_user->p_uid;
                            }
                        }
                    }
                    echo json_encode($res);
                } else {
                    echo json_encode(array());
                }
            }

            return;
        }
    }
}
