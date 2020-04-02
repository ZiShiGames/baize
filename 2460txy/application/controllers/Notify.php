<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Notify extends CI_Controller
{
    //爱贝支付公钥和支付结果查询接口start
    public $platpkey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC7jYUq3QDoAy63+CmVUd0dWPQbA0OjDHGH67vHSQK+fb/zGLJpdDklIUYw0q7zkxWgZ/tRWtVAdEdtrSb5veDS2LFcK0OFTjCqwptwzYUlpfgUivZN6+ARGl40UY0P8qNv/z60F2+QjO//gmnRwBVGI5dvaT3C6XLD11TSC72uVQIDAQAB';
    public $queryResultUrl = 'http://ipay.iapppay.com:9999/payapi/queryresult';
    //爱贝支付公钥和支付结果查询接口end

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Game_order_model');
    }


    public function egret()
    {
        echo 'OK';
    }


    //aibei pay start
    public function aibei()
    {
        log_message('debug', 'notify aibei '.json_encode($_POST));
        $string = $_POST;//接收post请求数据
        if ($string == null) {
            return;
        } else {
            $transdata = $string['transdata'];
            if (stripos('%22', $transdata)) { //判断接收到的数据是否做过 Urldecode处理，如果没有处理则对数据进行Urldecode处理
                $string = array_map('urldecode', $string);
            }
            $respData = 'transdata='.$string['transdata'].'&sign='.$string['sign'].'&signtype='.$string['signtype'];//把数据组装成验签函数要求的参数格式
            //$base_fun = $this->load->model("aibei_pay/Base_model");
            if (json_decode($transdata)->appid=='3023154925') {
                $this->platpkey='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC7jYUq3QDoAy63+CmVUd0dWPQbA0OjDHGH67vHSQK+fb/zGLJpdDklIUYw0q7zkxWgZ/tRWtVAdEdtrSb5veDS2LFcK0OFTjCqwptwzYUlpfgUivZN6+ARGl40UY0P8qNv/z60F2+QjO//gmnRwBVGI5dvaT3C6XLD11TSC72uVQIDAQAB';
                log_message('debug', 'aibei platpkey '.$this->platpkey);
            }
            if (!($this->parseResp($respData, $this->platpkey, $respJson))) {
                //验签失败
                //echo 'failed'."\n";
                log_message('error', 'aibei sign failed');
                return;
            } else {
                //验签成功
                //echo 'success'."\n";
                //以下是 验签通过之后 对数据的解析。
                log_message('error', 'aibei pass sign');
                $transdata = $string['transdata'];
                log_message('debug', "aibei transdata $transdata");
                $arr = json_decode($transdata);
                $appid = $arr->appid;
                $appuserid = $arr->appuserid;
                $order_id = $arr->cporderid;
                $game_order_id = $arr->cporderid;
                log_message('debug', "aibei order_id '$order_id' '$game_order_id'");
                $money = $arr->paytype;
                $result = $arr->result;
                $transid = $arr->transid;
                $transtime = $arr->transtime;
                $waresid = $arr->waresid;

                if ($this->notify_order($game_order_id)) {
                    log_message('error', 'notify ok');
                    echo 'OK';
                } else {
                    log_message('error', 'notify error');
                    echo 'FAIL';
                }
            }
        }
    }

    public function aibeiersiliuling()
    {
        log_message('debug', 'notify aibei '.json_encode($_POST));
        $string = $_POST;//接收post请求数据
        if ($string == null) {
            return;
        } else {
            $transdata = $string['transdata'];
            if (stripos('%22', $transdata)) { //判断接收到的数据是否做过 Urldecode处理，如果没有处理则对数据进行Urldecode处理
                $string = array_map('urldecode', $string);
            }
            $respData = 'transdata='.$string['transdata'].'&sign='.$string['sign'].'&signtype='.$string['signtype'];//把数据组装成验签函数要求的参数格式
            //$base_fun = $this->load->model("aibei_pay/Base_model");
            if (json_decode($transdata)->appid=='3023154925') {
                $appkey = 'MIICXAIBAAKBgQDP70O6behAxBv8EzNWi8Sh0Viqz2bx1ZWwKWweflCVeWR7nunEyUCDdOSJjkuOyqr4WZPehu1XlhwomGQniOxFizr74C6iG0dBknWlaQ8IJ8/e3TPulnsvaGE4N7PDmMNY/A2LyKaHrXJQe8ygg/hC2zrmwljiLeRDMmf/oA4p2wIDAQABAoGAbHsIW6fhZoCplO4zd3B9ympcluiTbZGfgYNqy9Hcms71NGVo0miohqyiWn1pP/rODbk2Iv9DrdE7qZYvkkyl7oRqnat8VkAkxFEOHLYesmndVVjTpp/fzT0lU1yX+Fus5xpg6A22kmbQOVA7yywdGSc0HqPLQbi0qJcjhkRtYpkCQQD9y/4Co/qtXki7ynKVQwOQrXGG1q1G1shsWMu0F4ErItQg4R/PsnEjK6mkboW+QDi10oF39blBVzrJ02NERz9dAkEA0b1ahzCgyrzzUHCW3TSaz/ciNy/dCVzcHAl/jhcW/yvzN7O+shDn75zSm+E6PgvMcrZv/4WoX72u9WqttuxSlwJAAMVIzStATJx3rhJMTMW6UgskyBsIxalLTIDshWx42O+vIzZryU6qZ0fvqO8o+s3pHiw4dmvJlzgzln9M0t1AhQJAdHDLcYJtwiBkdKQNHYG28P4i4MCR1kDXcjlTt27aNZAQ1zvTAsif+0b1JdVEoG2sc4MVaqapc327RESVbJiGwQJBAPXPCRH0SHcmk2x3R53M70QLWw4IK49aMj9G72+0AwCPR6hZQx4pLhy+kF4nPnZ7K/3V4P9LQmVg4P9TQz1c50Q=';
                $Aplatpkey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC7jYUq3QDoAy63+CmVUd0dWPQbA0OjDHGH67vHSQK+fb/zGLJpdDklIUYw0q7zkxWgZ/tRWtVAdEdtrSb5veDS2LFcK0OFTjCqwptwzYUlpfgUivZN6+ARGl40UY0P8qNv/z60F2+QjO//gmnRwBVGI5dvaT3C6XLD11TSC72uVQIDAQAB';
                $this->platpkey=$Aplatpkey;
                log_message('debug', 'aibei platpkey '.$this->platpkey);
            }
            if (!($this->parseResp($respData, $this->platpkey, $respJson))) {
                //验签失败
                //echo 'failed'."\n";
                log_message('error', 'aibei sign failed');
                return;
            } else {
                //验签成功
                //echo 'success'."\n";
                //以下是 验签通过之后 对数据的解析。
                log_message('error', 'aibei pass sign');
                $transdata = $string['transdata'];
                log_message('debug', "aibei transdata $transdata");
                $arr = json_decode($transdata);
                $appid = $arr->appid;
                $appuserid = $arr->appuserid;
                $order_id = $arr->cporderid;
                $game_order_id = $arr->cporderid;
                log_message('debug', "aibei order_id '$order_id' '$game_order_id'");
                $money = $arr->paytype;
                $result = $arr->result;
                $transid = $arr->transid;
                $transtime = $arr->transtime;
                $waresid = $arr->waresid;

                if ($this->notify_order($game_order_id)) {
                    log_message('error', 'notify ok');
                    echo 'OK';
                } else {
                    log_message('error', 'notify error');
                    echo 'FAIL';
                }
            }
        }
    }

    //爱贝支付函数
    /**格式化公钥
     * $pubKey PKCS#1格式的公钥串
     * return pem格式公钥， 可以保存为.pem文件
     */
    public function formatPubKey($pubKey)
    {
        $fKey = "-----BEGIN PUBLIC KEY-----\n";
        $len = strlen($pubKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey.substr($pubKey, $i, 64)."\n";
            $i += 64;
        }
        $fKey .= '-----END PUBLIC KEY-----';

        return $fKey;
    }
    private function notify_order($order_id)
    {
        log_message('debug', "notify_order '$order_id'");
        $condition = array(
                        'u_order_id' => $order_id,
                    );

        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        log_message('debug', 'notify_order get game '.json_encode($game_order));
        if ($game_order) {
            if ($game_order->status == $this->Game_order_model->START_STATUS) {
                $where = array('u_order_id' => $order_id);
                $data = array('status' => $this->Game_order_model->PAYED_STATUS);

                $this->Game_order_model->update($data, $where);




                $this->load->model('Common_model');
                $res = $this->Common_model->notify($order_id);
                if ($res) {
                    echo 'SUCCESS';
                }

                return;
            } elseif ($game_order->status == $this->Game_order_model->PAYED_STATUS) {
                $this->load->model('Common_model');
                $res = $this->Common_model->notify($order_id);
                if ($res) {
                    echo 'SUCCESS';
                }

                return;
            } else {
                echo 'SUCCESS';

                return;
            }
        }
        echo 'FAILED';
        return;
    }

    /**RSA验签
     * $data待签名数据
     * $sign需要验签的签名
     * $pubKey爱贝公钥
     * 验签用爱贝公钥，摘要算法为MD5
     * return 验签是否通过 bool值
     */
    public function verify($data, $sign, $pubKey)
    {
        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);

        //调用openssl内置方法验签，返回bool值
        $result = (bool) openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //返回资源是否成功
        return $result;
    }

    /**
     * 解析response报文
     * $content  收到的response报文
     * $pkey     爱贝平台公钥，用于验签
     * $respJson 返回解析后的json报文
     * return    解析成功TRUE，失败FALSE.
     */
    public function parseResp($content, $pkey, &$respJson)
    {
        $arr = array_map(create_function('$v', 'return explode("=", $v);'), explode('&', $content));
        foreach ($arr as $value) {
            $resp[($value[0])] = $value[1];
        }

        //解析transdata
        if (array_key_exists('transdata', $resp)) {
            $respJson = json_decode($resp['transdata']);
        } else {
            return false;
        }

        //验证签名，失败应答报文没有sign，跳过验签
        if (array_key_exists('sign', $resp)) {
            //校验签名
            $pkey = $this->formatPubKey($pkey);

            return $this->verify($resp['transdata'], $resp['sign'], $pkey);
        } elseif (array_key_exists('errmsg', $respJson)) {
            return false;
        }

        return true;
    }
    //aibei pay end
}
