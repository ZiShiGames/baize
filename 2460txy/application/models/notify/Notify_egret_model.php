<?php

class Notify_egret_model extends CI_Model
{
    public $key = 'Dbv131FruI2vAlymxzUZc';
    public function notify($game_order)
    {
        if (!$game_order) {
            return false;
        }

        $data = array(
            'orderId' => $game_order->game_order_id,
            'userId' => $game_order->user_id,
            'money' => $game_order->money / 100,
            'ext' => $game_order->ext,
            'time' => time(),
        );

        $this->load->model('Common_model');
        $p_str = $this->Common_model->sort_params($data, '');

        $sign = md5($p_str.$this->key);

        $p_str = $this->Common_model->sort_params($data, '&');

        $notify = $game_order->notify.'?'.$p_str.'&sign='.$sign;
        log_message('debug', "egret notify $notify");

        $content = $this->Curl_model->curl_get($notify);
        log_message('debug', "egret notify $content");

        if ($content) {
            $res = json_decode($content);
            if ($res) {
                if ($res->code == 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
