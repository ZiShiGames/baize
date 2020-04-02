<?php

class Common_model extends CI_Model
{
    public function sort_params($params)
    {
        if (!$params || gettype($params) != 'array') {
            return false;
        }

        $keys = array_keys($params);
        sort($keys);
        $pair = '';
        $index = 0;
        foreach ($keys as $key) {
            if ($index != 0) {
                $pair .= '&';
            }
            $pair .= "$key=".$params["$key"];

            ++$index;
        }

        return $pair;
    }

    public function notify($order_id)
    {
        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');

        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (!$game_order) {
            return;
        }
        if ($game_order->status == $this->Game_order_model->PAYED_STATUS) {
            $game_id = $game_order->game_id;
            $game = $this->Game_model->get_by_game_id($game_id);
            if (!$game) {
                return false;
            }

            $p = array(

                    'actor_id' => $game_order->data,
                    'app_id' => $game_id,
                    'app_order_id' => $game_order->orderNo,
                    'app_user_id' => $game_order->user_id,
                    'ext' => $game_order->ext,
                    'order_id' => $game_order->u_order_id,
                    'payment_time' => time(),
                    'real_amount' => $game_order->money,

                );
            $p_str = $this->sort_params($p);

            $p_str_sign = $p_str.'&key='.$game->app_key;
            $sign = md5($p_str_sign);

            $game_pay_nofity = $game->game_pay_nofity;


            $p = array(
                    'actor_id' => urlencode($game_order->data),
                    'app_id' => $game_id,
                    'app_order_id' => $game_order->orderNo,
                    'app_user_id' => $game_order->user_id,
                    'ext' => $game_order->ext,
                    'order_id' => $game_order->u_order_id,
                    'payment_time' => time(),
                    'real_amount' => $game_order->money,

                );

            $p_str = $this->sort_params($p);

            $notify_url = $game_pay_nofity.'?'.$p_str."&sign=$sign";

            $content = $this->Curl_model->curl_get($notify_url);

            if ($game_order->platform == 'tt') {
                if ($content != 'success') {
                    $game_pay_nofity = 'http://pay.gz.1251208707.clb.myqcloud.com/juhe/payment';
                    $notify_url = $game_pay_nofity.'?'.$p_str."&sign=$sign";
                    $content = $this->Curl_model->curl_get($notify_url);
                }
            }

            log_message('debug', "notify url:$notify_url - res:'$content'");

            if ($content) {
                if ($content == 'success') {
                    $where = array('u_order_id' => $order_id);
                    $data = array('status' => $this->Game_order_model->NOTIFIED_STATUS);
                    $this->Game_order_model->update($data, $where);

                    //redis数据上报
                    //  $this->load->driver('cache', array('adapter' => 'redis'));
                    // if ($this->cache->redis->is_supported()) {
                    //     $today = date("Y-m-d", time());
                    //     $redis_data = array(
                    //          $game_id=>$game_order->money,
                    //      );
                    //     if (!$this->cache->redis->get('Gameorder_count_'.$today)) {
                    //         $this->cache->redis->save('Gameorder_count_'.$today, $redis_data, 60*60*24);
                    //     } else {
                    //         $gameOrder_count = $this->cache->redis->get('Gameorder_count_'.$today);
                    //         if (!isset($gameOrder_count[$game_id])) {
                    //             $gameOrder_count[$game_id]=$game_order->money;
                    //         } else {
                    //             $count = $gameOrder_count[$game_id];
                    //             $gameOrder_count[$game_id]=$count+$game_order->money;
                    //         }
                    //         $count = $gameOrder_count[$game_id];
                    //         $this->cache->redis->save('Gameorder_count_'.$today, $gameOrder_count, 60*60*24);
                    //     }
                    // }
                    //redis数据上报done
                    return true;
                } else {
                    log_message('error', "$order_id notify error content:'$content'");
                }
            }else{
                log_message('error',"$order_id cp server is no response");
            }
        } else {
            log_message('error', "$order_id notify no response");

            return false;
        }
    }
    
    public function gameNotify($order_id)
    {
        $condition = array('u_order_id' => $order_id);
        $this->load->model('Game_order_model');
        
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if (!$game_order) {
            return;
        }
        if ($game_order->status == '1') {
            $game_id = $game_order->game_id;
            $game = $this->Game_model->get_by_game_id($game_id);
            if (!$game) {
                return false;
            }
            
            $p = array(
                
                'actor_id' => $game_order->data,
                'app_id' => $game_id,
                'app_order_id' => $game_order->orderNo,
                'app_user_id' => $game_order->user_id,
                'ext' => $game_order->ext,
                'order_id' => $game_order->u_order_id,
                'payment_time' => time(),
                'real_amount' => $game_order->money,
                
            );
            $p_str = $this->sort_params($p);
            
            $p_str_sign = $p_str.'&key='.$game->app_key;
            $sign = md5($p_str_sign);
            
            $game_pay_nofity = $game->game_pay_nofity;
            
            
            $p = array(
                'actor_id' => urlencode($game_order->data),
                'app_id' => $game_id,
                'app_order_id' => $game_order->orderNo,
                'app_user_id' => $game_order->user_id,
                'ext' => $game_order->ext,
                'order_id' => $game_order->u_order_id,
                'payment_time' => time(),
                'real_amount' => $game_order->money,
                
            );
            
            $p_str = $this->sort_params($p);
            
            $notify_url = $game_pay_nofity.'?'.$p_str."&sign=$sign";
            
            $content = $this->Curl_model->curl_get($notify_url);
            
            if ($game_order->platform == 'tt') {
                if ($content != 'success') {
                    $game_pay_nofity = 'http://pay.gz.1251208707.clb.myqcloud.com/juhe/payment';
                    $notify_url = $game_pay_nofity.'?'.$p_str."&sign=$sign";
                    $content = $this->Curl_model->curl_get($notify_url);
                }
            }
            
            log_message('debug', "notify url:$notify_url - res:'$content'");
            
            if ($content) {
                if ($content == 'success') {
                    $where = array('u_order_id' => $order_id);
                    $data = array('status' => '2');
                    $this->Game_order_model->update($data, $where);
                    
                    //redis数据上报
                    //  $this->load->driver('cache', array('adapter' => 'redis'));
                    // if ($this->cache->redis->is_supported()) {
                    //     $today = date("Y-m-d", time());
                    //     $redis_data = array(
                    //          $game_id=>$game_order->money,
                    //      );
                    //     if (!$this->cache->redis->get('Gameorder_count_'.$today)) {
                    //         $this->cache->redis->save('Gameorder_count_'.$today, $redis_data, 60*60*24);
                    //     } else {
                    //         $gameOrder_count = $this->cache->redis->get('Gameorder_count_'.$today);
                    //         if (!isset($gameOrder_count[$game_id])) {
                    //             $gameOrder_count[$game_id]=$game_order->money;
                    //         } else {
                    //             $count = $gameOrder_count[$game_id];
                    //             $gameOrder_count[$game_id]=$count+$game_order->money;
                    //         }
                    //         $count = $gameOrder_count[$game_id];
                    //         $this->cache->redis->save('Gameorder_count_'.$today, $gameOrder_count, 60*60*24);
                    //     }
                    // }
                    //redis数据上报done
                    return true;
                } else {
                    log_message('error', "$order_id notify error content:'$content'");
                }
            }else{
                log_message('error',"$order_id cp server is no response");
            }
        } else {
            log_message('error', "$order_id notify no response");
            
            return false;
        }
    }
}
