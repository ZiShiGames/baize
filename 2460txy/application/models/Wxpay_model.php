<?php

class Wxpay_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $_PATH = $_SERVER['DOCUMENT_ROOT'] . '/lib';
        require_once "$_PATH/wxpay/WxPay.Api.php";
        require_once "$_PATH/wxpay/WxPay.Notify.php";
        require_once "$_PATH/wxpay/WxPay.Config.php";
        require_once "$_PATH/wxpay/log.php";
        date_default_timezone_set('Asia/Shanghai');
    }
    public function native_pay()
    {
        $_PATH = $_SERVER['DOCUMENT_ROOT'] . '/lib';
        require_once "$_PATH/wxpay/WxPay.NativePay.php";
        //初始化日志
        // $logHandler= new CLogFileHandler("$_PATH/wxpay/logs/".date('Y-m-d').'.log');
        // $log = Log::Init($logHandler, 15);
        $notify = new NativePay();
        // $url1 = $notify->GetPrePayUrl("123456789");
        $order = $this->input->get('order_id');
        $money = $this->input->get('money');
        $subject = $this->input->get('subject');

        $input = new WxPayUnifiedOrder();
        // $input->SetOpenid('ofT-Q4phZor6yMZ3wcxI3ETIHCy4');
        $input->SetBody($subject);
        $input->SetAttach("$order");
        $input->SetOut_trade_no("$order");
        $input->SetTotal_fee($money);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 30000));
        $input->SetGoods_tag($subject);
        $input->SetNotify_url("http://h5pay.xileyougame.com/index.php/wxpay/notify?order=$order");
        $input->SetTrade_type("NATIVE");
        // $input->SetTrade_type("JSAPI");
        $input->SetProduct_id("123456789");

        $result = $notify->GetPayUrl($input);
        // echo json_encode($result);
        if (isset($result["code_url"]) && $result["code_url"]) {
            $url2 = $result["code_url"];
            $this->Output_model->json_print(0, 'ok', $result);
        } else {
            // echo json_encode($result);
            $this->Output_model->json_print(1, 'err');
        }
        // echo '<div onclick="payFun()" style="position: fixed;height: 100%;width: 100%;background: rgba(0, 0, 0, .8);z-index: 100;display: block;top: 0;left: 0;cursor: pointer;"></div><div style="margin-left: -260px;width: 520px;height: 520px;overflow: hidden;background: #fff;border-radius: 6px;position: absolute;left: 50%;top: 100px;    z-index: 999999;">
        // <img style="z-index:999;height:100%;width:100%;" alt="模式一扫码支付" src="http://localhost:8888/index.php/wxpay/showQrCode?data='.($url2).'" style="width:150px;height:150px;"/>
        // </div>';
    }
    public function jsapi_pay()
    {
        $_PATH = $_SERVER['DOCUMENT_ROOT'] . '/lib';
        require_once "$_PATH/wxpay/WxPay.JsApiPay.php";

        $openId = "ofT-Q4phZor6yMZ3wcxI3ETIHCy4";
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid();

        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody("test");
        $input->SetAttach("test");
        $input->SetOut_trade_no("sdkphp" . date("YmdHis"));
        $input->SetTotal_fee("1");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://paysdk.weixin.qq.com/notify.php");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $config = new WxPayConfig();
        $order = WxPayApi::unifiedOrder($config, $input);
        echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
        printf_info($order);
    }
    
    public function gameUnifiedorder($openId, $order_id, $product, $product_id, $appid, $money, $notify_url, $mchid, $app_secret, $app_key, $type = 'JSAPI')
    {
        $_PATH = $_SERVER['DOCUMENT_ROOT'] . '/lib';
        require_once "$_PATH/wxpay/WxPay.Data.php";
        //统一下单
        
        $config = new WxPayConfig();
        $input = new WxPayUnifiedOrder();
        $input->SetBody($product);
        $input->SetAppid($appid);
        $input->SetAttach($product);
        $input->SetOut_trade_no($order_id);
        $input->SetTotal_fee($money);
        $input->SetTime_start(date("YmdHis", time()));
        $input->SetTime_expire(date("YmdHis", time() + 6000));
        $input->SetGoods_tag($product);
        $input->SetNotify_url("$notify_url?order_id=$order_id");
        $input->SetTrade_type($type);
//         $input->SetOpenid($openId);
        $config->merchantId = $mchid;
        $config->key = $app_key;
        $config->appSecret = $app_secret;
        $config->appid = $appid;
        $input->SetMch_id($mchid);
        $input->SetProduct_id($product_id);
        log_message('debug', 'wx pay unifieorder config ' . json_encode($config));
        try {
            $result = WxPayApi::unifiedOrder($config, $input);
            if (!isset($result['return_code']) || $result['return_code'] != 'SUCCESS') {
                log_message('debug', 'wx pay unifieorder result ' . json_encode($result));
                return false;
            }
            $time = time();
            $key = $config->GetKey();
            $result = json_decode(json_encode($result));
            $sign_str = "appId=$appid&nonceStr=$result->nonce_str" . "&package=prepay_id=$result->prepay_id&signType=MD5&timeStamp=$time&key=$key";
            $paySign = MD5($sign_str);
            $result->paySign = strtoupper($paySign);
            $result->time = $time;
        } catch (Exception $e) {
            echo json_encode($e);
            return false;
        }
        return $result;
    }
    
    
    public function unifiedorder($openId, $order_id, $product, $product_id, $appid, $money, $notify_url, $mchid, $app_secret, $app_key, $type = 'JSAPI')
    {
        $_PATH = $_SERVER['DOCUMENT_ROOT'] . '/lib';
        require_once "$_PATH/wxpay/WxPay.Data.php";
        //统一下单

        $config = new WxPayConfig();
        $input = new WxPayUnifiedOrder();
        $input->SetBody($product);
        $input->SetAppid($appid);
        $input->SetAttach($product);
        $input->SetOut_trade_no($order_id);
        $input->SetTotal_fee($money);
        $input->SetTime_start(date("YmdHis", time()));
        $input->SetTime_expire(date("YmdHis", time() + 6000));
        $input->SetGoods_tag($product);
        $input->SetNotify_url("$notify_url?order_id=$order_id");
        $input->SetTrade_type($type);
        $input->SetOpenid($openId);
        $config->merchantId = $mchid;
        $config->key = $app_key;
        $config->appSecret = $app_secret;
        $config->appid = $appid;
        $input->SetMch_id($mchid);
        $input->SetProduct_id($product_id);
        log_message('debug', 'wx pay unifieorder config ' . json_encode($config));
        try {
            $result = WxPayApi::unifiedOrder($config, $input);
            if (!isset($result['return_code']) || $result['return_code'] != 'SUCCESS') {
                log_message('debug', 'wx pay unifieorder result ' . json_encode($result));
                return false;
            }
            $time = time();
            $key = $config->GetKey();
            $result = json_decode(json_encode($result));
            $sign_str = "appId=$appid&nonceStr=$result->nonce_str" . "&package=prepay_id=$result->prepay_id&signType=MD5&timeStamp=$time&key=$key";
            $paySign = MD5($sign_str);
            $result->paySign = strtoupper($paySign);
            $result->time = $time;
        } catch (Exception $e) {
            echo json_encode($e);
            return false;
        }
        return $result;
    }
    public function notify()
    {
        $msg = array();
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $msg = (array) simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        log_message('debug','wxpay '. json_encode($msg));
        $WxPayConfig = new WxPayConfig();
        if ($msg['return_code'] == 'SUCCESS') {
            // if ($msg['mch_id'] == $WxPayConfig->GetMerchantId()) {
            $this->load->model('Curl_model');
            $total_amount = $msg['total_fee'];
            $out_trade_no = $msg['out_trade_no'];
            $requery = "http://h5sdk.xileyougame.com/index.php/Check_order_api/Check_order?money=$total_amount&order=$out_trade_no";
            $response = $this->Curl_model->curl_get($requery);
            log_message('debug', "alipay notify $out_trade_no | $total_amount | $response");
            echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
            // } else {
            //     echo "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[参数格式校验错误]]></return_msg></xml>";
            // }
        } else {
            echo "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[参数格式校验错误]]></return_msg></xml>";
        }
    }
    public function showQrCode()
    {
        $_PATH = $_SERVER['DOCUMENT_ROOT'] . '/lib';
        require_once $_PATH . '/wxpay/phpqrcode/phpqrcode.php';
        $url = urldecode($_GET["data"]);
        if (substr($url, 0, 6) == "weixin") {
            QRcode::png($url);
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }
    private function xml_to_array($xml)
    {
        $array = (array) (simplexml_load_string($xml));
        foreach ($array as $key => $item) {
            $array[$key] = $this->struct_to_array((array) $item);
        }
        return $array;
    }
    public function struct_to_array($item)
    {
        if (!is_string($item)) {
            $item = (array) $item;
            foreach ($item as $key => $val) {
                $item[$key] = $this->struct_to_array($val);
            }
        }
        return $item;
    }
}
