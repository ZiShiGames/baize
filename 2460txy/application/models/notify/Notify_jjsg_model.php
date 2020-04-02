<?php

class Notify_jjsg_model extends CI_Model
{
    public function notify($game_order)
    {
        if (!$game_order) {
            return false;
        }
        $sign = md5($game_order->order_no.'ec032627fc264473cd115f1773780ba7');
        $notify = $game_order->notify;
        $notify .= '?order_id='.$game_order->order_no.'&sign='.$sign;
        if ($game_order->ext) {
            $notify .= '&ext='.$game_order->ext;
        }

        $content = $this->Curl_model->curl_get($notify);

        if ($content == 'SUCCESS') {
            return true;
        } else {
            return false;
        }
    }
}
