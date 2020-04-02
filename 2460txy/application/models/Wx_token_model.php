<?php

/*
* model for maintain weixin token
*/
class Wx_token_model extends CI_Model
{
    public $appid = 'wxcff9ff4bd25e6aec';
    public $secret = 'd93a03ad76701b6e564ad2d993b43c3a';
    // for jssdk sign
    public $noncestr = 'mimihuayuanTPz0wzccnW';

    public function __construct()
    {
        parent::__construct();

        // use file cache to store token
        $this->load->driver('cache', array('adapter' => 'file'));
    }

    public function get_token()
    {
        $access_token = $this->cache->get('access_token');
        log_message('error', "Token debug $access_token");
        if ($access_token) {
            return $access_token;
        } else {
            return $this->requet_token();
        }
    }

    private function requet_token()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->secret;
        $result = $this->Curl_model->curl_get($url);
        // echo $result;
        if ($result) {
            $token_json = json_decode($result);
            log_message('error', "Token request $result");
            if ($token_json) {
                $this->cache->save('access_token', $token_json->access_token, $token_json->expires_in - 1000);

                return $token_json->access_token;
            }
        }
    }
    // useless for now
    public function get_sign_data($url)
    {
        $jsapi_ticket_obj = array();

        $jsapi_ticket = $this->cache->get('jsapi_ticket');
        if (!$jsapi_ticket) {
            $token = $this->get_token();
            $ticket_url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$token.'&type=jsapi';
            $response = $this->Curl_model->curl_get($ticket_url);
            $json = json_decode($response);

            if ($json->errcode === 0) {
                $this->cache->save('jsapi_ticket', $json->ticket, $json->expires_in - 1000);
                $jsapi_ticket = $json->ticket;
            }
        }

        $jsapi_ticket_obj['jsapi_ticket'] = $jsapi_ticket;
        $jsapi_ticket_obj['noncestr'] = $this->noncestr;
        $jsapi_ticket_obj['timestamp'] = time();
        $jsapi_ticket_obj['url'] = $url;

        return $jsapi_ticket_obj;
    }

    public function sign($jsapi_ticket_obj)
    {
        $jsapi_ticket = $jsapi_ticket_obj['jsapi_ticket'];
        $noncestr = $jsapi_ticket_obj['noncestr'];
        $timestamp = $jsapi_ticket_obj['timestamp'];
        $url = $jsapi_ticket_obj['url'];

        $string = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$url";

        return sha1($string);
    }
}
