<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Server_api extends CI_Controller
{
    public function index()
    {
        $time = date("Y-m-d\TH:i:s").'Z';
        echo $time;
        return;
    }

    public function resilience_server()
    {
        $time = date("Y-m-d\TH:i:s").'Z'; // hours need sub 8
        $rand = rand(1, 10000);
        $rule = 'http://ess.aliyuncs.com/'.
        '?Action=ExecuteScalingRule'.
        '&ScalingRuleAri=ari:acs:ess:cn-qingdao:1968578503053569:scalingrule/ep4mkceKgvNjcBi6PIbFnIJu'.
        '&Format=json'.
        '&Version=2014-08-28'.
        '&Signature=    '.
        '&SignatureMethod=HMAC-SHA1'.
        '&Timestamp='.$time.
        '&AccessKeyId=LTAIgzpVccq9JvZ1'.
        '&SignatureVersion=1.0'.
        '&SignatureNonce='.$rand;
        $sign_array=array(
            'Action'=>'ExecuteScalingRule',
            'ScalingRuleAri'=>urlencode('ari:acs:ess:cn-qingdao:1968578503053569:scalingrule/ep4mkceKgvNjcBi6PIbFnIJu'),
            'Format'=>'json',
            'Version'=>urlencode('2014-08-28'),
            'SignatureMethod'=>'HMAC-SHA1',
            'Timestamp'=>$time,
            'AccessKeyId'=>'LTAIgzpVccq9JvZ1',
            'SignatureVersion'=>'1.0',
            'SignatureNonce'=>$rand,
        );
        $this->load->model('Common_model');
        $sign_str = $this->Common_model->sort_params($sign_array);
        $sign_str_b='get&%2F&'.urlencode($sign_str);
        $content = $this->Curl_model->curl_get($rule);
        echo $rule.'  .<br/>';
        echo $content.'  .<br/>';
        echo json_encode($content);
    }
}
