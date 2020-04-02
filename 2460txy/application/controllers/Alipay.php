<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alipay extends CI_Controller
{
    public $appId;

    //私钥文件路径
    public $rsaPrivateKeyFilePath;

    //私钥值
    public $rsaPrivateKey="MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCf2ITprqrcS6754sXAwpwea5t+TF5x5fwQ338RqrKMWRCHfl2gv67Sx4sI5TDn5//uKI7+sl3Sj5qEDq5OF/m5BT3Q3nZEx9dOJRLK5vEJtACbe3gjSD8+L/hOHDz2ju+4v1O8wWdTCMkAkrWQz20pGmhvOcV5r2vIujCu/UaCq8QYsWcyFYn69uwIZSKuOn7UV6Axd2SiyUAlWN02w3OcfpE5CqVO7njgvxODHE76liIcfOMVg+W6XgSLbNM0BmUgkV+r+avO3mtekOa+ysLc/TcXKos0cgw2nXnn9s8K2KXhcoG9LjuYX++pcPFxNBIo6K/E15AHeerTYeAS/ONJAgMBAAECggEBAJGSmbFMDnUkRA11dk2PrqiRrYG/QUAPiJlBQbMwNv1UW5ZaAiDUkP2LFtcaC4kYI1+c9mWEwadyevELgbjDYv0aheqDv0fyi+WyI8Q/wILquKbhMk9Hi7kx7LwOQYL2N+GT9UnxBQ188bmg4tQn5C1LzJKHFY41sT6UOqQTK7TyDJkTrLefqRFLX65CwDA2rigfwRCeqk73PNroz8btOU1T8dAaqZrB6I14pQCBsansiDVgOO4LkXMDslqcYSx8HJCVLng8R4OJ+UcOijU1rRBUqlvzu/ZoQn3zS2PJrESPIyK25xrQBWBTrdg5oWNz0IZ3MJS1ykLAPIzehXqZWxECgYEA81RyeSvzlOYOdvKz/6VC9R0GQZiUYbbcIrpbcZscVNFWdf78hmrFn5sSR812i0HTa2lehOHmsTXiBdO5M+JsmiqaJ1iIG8lWFkTdAmAHrvDrfIe6PVuRB1V3KP75eL7CZ2FIg8pNwvPIWOQmq1hm4wP/2m7PaEwzTstTmbk/dMUCgYEAqCs9qCJ7h5vnaTFAnMm5NdDxG0XGwSuC1GXxZ0S7g65xnSITmn7D7EElDuPyP6Eb9DGx5cBniWgUq9YawtEOlBp7ChC3w8aT/cBC2F2lt5jv+5cclF6OBPxeqEazDoeSZVpLbmZG3bzPA1fcxuEZ7SUxncdXiiCeWQeXm9VfRLUCgYBY61ott4b6uZs9knF+kVjHk8SugeBvWNBXDHzQJhuTTzBmTDjhsfDn41Yp0QZp2zM0RDwbGSYhSgx/jqBWOn7vU3QjvrF2XfvMhXuyG3+TSUz0o/DF7UkxQeUaoRpvKl6GQsGqD2qdPFLRUQZkhRiMCvEbVHUMqxPwzlKNkWmD9QKBgQCZ1nSgymqWs1gOMrAYvbBgOrNVb49SRqt4AYnEHmwrGfl09SdZvX0dMrrj1EJXUtpvmMZUlp4gZMYEK+hvLy9W7KKBoql4vr/C8Y40v/ZI7e8bCDFsyNLCXNt6tLI4KG0TqnY2l/lb/syhEk8039cHyW6KF0FNamlwqDFYrOhpwQKBgFBfqFT01vhBdRMhn5DHT2bvPdH+kToqESZ5XUVHRMIPD23s82rCXbNS9uOQJlJRzMhmAZ85B4lfVjjfSRnyadgtJkBb6hUMURSNdPV2yKqEjR3FPwQmL9k4l+U3qQVk4PAefxEy6qZxMHfDov5EJO6kqrAZZGMq4FVQHKRMnFqI";

    //网关
    public $gatewayUrl = "https://openapi.alipay.com/gateway.do";
    //返回数据格式
    public $format = "json";
    //api版本
    public $apiVersion = "1.0";

    // 表单提交字符集编码
    public $postCharset = "UTF-8";

    //使用文件读取文件格式，请只传递该值
    public $alipayPublicKey = null;

    //使用读取字符串格式，请只传递该值
    public $alipayrsaPublicKey;


    public $debugInfo = false;

    private $fileCharset = "UTF-8";

    private $RESPONSE_SUFFIX = "_response";

    private $ERROR_RESPONSE = "error_response";

    private $SIGN_NODE_NAME = "sign";


    //加密XML节点名称
    private $ENCRYPT_XML_NODE_NAME = "response_encrypted";

    private $needEncrypt = false;


    //签名类型
    public $signType = "RSA";


    //加密密钥和类型

    public $encryptKey;

    public $encryptType = "AES";

    protected $alipaySdkVersion = "alipay-sdk-php-20180705";
    public function __construct()
    {
        parent::__construct();
        // $_PATH = $_SERVER['DOCUMENT_ROOT'].'/lib';
        // require_once $_PATH.'/alipay/wappay/service/AlipayTradeService.php';
        // require_once $_PATH.'/alipay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
        // require $_PATH.'/alipay/config.php';
    }
    public function alipay()
    {
        $_PATH = $_SERVER['DOCUMENT_ROOT'].'/lib';
        require_once $_PATH.'/alipay/wappay/service/AlipayTradeService.php';
        require_once $_PATH.'/alipay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
        require $_PATH.'/alipay/config.php';


            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no = $this->input->get('order_id');

            //订单名称，必填
            $subject = $this->input->get('subject');
        ;

            //付款金额，必填
            $total_amount = $this->input->get('money');

            //商品描述，可空
            $body = $this->input->get('desc');

            //超时时间
            $timeout_express="1m";

        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);

        $payResponse = new AlipayTradeService($config);
        $result=$payResponse->wapPay($payRequestBuilder, $config['return_url'], $config['notify_url']);


        return ;
    }
    
    public function alipay2()
    {
        $_PATH = $_SERVER['DOCUMENT_ROOT'].'/lib';
        require_once $_PATH.'/alipay/wappay/service/AlipayTradeService.php';
        require_once $_PATH.'/alipay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
        require $_PATH.'/alipay/config.php';
        
       
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $this->input->get('order_id');
        
        $this->load->model('Game_order_model');
        $condition = array('u_order_id' => $out_trade_no);
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        //订单名称，必填
        $subject = $game_order->goodsName;
        ;
        
        //付款金额，必填
        $total_amount = round($game_order->money/100,2);
        
        //商品描述，可空
        $body = $this->input->get('desc');
        
        //超时时间
        $timeout_express="1m";
        
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);
        
        $payResponse = new AlipayTradeService($config);
        $result=$payResponse->wapPay($payRequestBuilder, $config['return_url'], $config['notify_url']);
        
        
        return ;
    }
    
    public function notify()
    {
        $_PATH = $_SERVER['DOCUMENT_ROOT'].'/lib';
        require $_PATH.'/alipay/config.php';


        $gmt_create=$this->input->get_post('gmt_create');
        $charset=$this->input->get_post('charset');
        $seller_email=$this->input->get_post('seller_email');
        $subject=$this->input->get_post('subject');
        $buyer_id=$this->input->get_post('buyer_id');
        $invoice_amount=$this->input->get_post('invoice_amount');
        $notify_id=$this->input->get_post('notify_id');
        $fund_bill_list=$this->input->get_post('fund_bill_list');
        $notify_type=$this->input->get_post('notify_type');
        $trade_status=$this->input->get_post('trade_status');
        $receipt_amount=$this->input->get_post('receipt_amount');
        $buyer_pay_amount=$this->input->get_post('buyer_pay_amount');
        $app_id=$this->input->get_post('app_id');
        $seller_id=$this->input->get_post('seller_id');
        $gmt_payment=$this->input->get_post('gmt_payment');
        $notify_time=$this->input->get_post('notify_time');
        $version=$this->input->get_post('version');
        $out_trade_no=$this->input->get_post('out_trade_no');
        $total_amount=$this->input->get_post('total_amount');
        $trade_no=$this->input->get_post('trade_no');
        $auth_app_id=$this->input->get_post('auth_app_id');
        $buyer_logon_id=$this->input->get_post('buyer_logon_id');
        $point_amount=$this->input->get_post('point_amount');

        $sign=$this->input->get_post('sign');
        $sign_type=$this->input->get_post('sign_type');

        $sign_data = array(
            'gmt_create'=>$gmt_create,
            'charset'=>$charset,
            'seller_email'=>$seller_email,
            'subject'=>$subject,
            'buyer_id'=>$buyer_id,
            'invoice_amount'=>$invoice_amount,
            'notify_id'=>$notify_id,
            'fund_bill_list'=>$fund_bill_list,
            'notify_type'=>$notify_type,
            'trade_status'=>$trade_status,
            'receipt_amount'=>$receipt_amount,
            'buyer_pay_amount'=>$buyer_pay_amount,
            'app_id'=>$app_id,
            'seller_id'=>$seller_id,
            'gmt_payment'=>$gmt_payment,
            'notify_time'=>$notify_time,
            'version'=>$version,
            'out_trade_no'=>$out_trade_no,
            'total_amount'=>$total_amount,
            'trade_no'=>$trade_no,
            'auth_app_id'=>$auth_app_id,
            'buyer_logon_id'=>$buyer_logon_id,
            'point_amount'=>$point_amount,
        );
        
        if ($trade_status=="TRADE_SUCCESS"||$trade_status=="TRADE_FINISHED") {
            if ($config['app_id']==$app_id) {
                $this->load->model('Curl_model');
                $total_amount = $total_amount*100;
                $requery = "http://api.baizegame.com/index.php/Check_order_api/Check_order?money=$total_amount&order=$out_trade_no";
                $response = $this->Curl_model->curl_get($requery);
                log_message('debug', "alipay notify $out_trade_no | $total_amount | $response");
                echo "success";
            } else {
                echo "fail";
            }
        } else {
            echo "fail";
        }
    }
    public function rsaSign($params, $signType = "RSA")
    {
        return $this->sign($this->getSignContent($params), $signType);
    }

    public function getSignContent($params)
    {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = $this->characet($v, $this->postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset($k, $v);
        return $stringToBeSigned;
    }
    protected function sign($data, $signType = "RSA")
    {
        if ($this->checkEmpty($this->rsaPrivateKeyFilePath)) {
            $priKey=$this->rsaPrivateKey;
            $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        } else {
            $priKey = file_get_contents($this->rsaPrivateKeyFilePath);
            $res = openssl_get_privatekey($priKey);
        }

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        if (!$this->checkEmpty($this->rsaPrivateKeyFilePath)) {
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }
    protected function checkEmpty($value)
    {
        if (!isset($value)) {
            return true;
        }
        if ($value === null) {
            return true;
        }
        if (trim($value) === "") {
            return true;
        }

        return false;
    }
    public function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = $this->fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }


        return $data;
    }
}
