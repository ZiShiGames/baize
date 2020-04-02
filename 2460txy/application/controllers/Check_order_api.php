<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Check_order_api extends CI_Controller
{
	public function Check_order()
    {
        $order = $this->input->get('order');
		$money = $this->input->get('money');
		$this->load->model('Game_order_model');
		$condition = array(
			'u_order_id'=>$order
		);
		$game_order = $this->Game_order_model->get_one_by_condition($condition);
		if($game_order){
			$url = "http://".$_SERVER['HTTP_HOST']."/index.php/api/notify/$game_order->platform/$game_order->game_id?order_id=$order&money=$money&sign=1";
			echo $this->Curl_model->curl_get($url);
		}else{
			echo 'fail';
		}
    }

    public function Appstore_check_order()
    {
        $order = $this->input->get('order');
        $money = $this->input->get('money');
        $sign = $this->input->get('sign');
        $md5_code=substr(sha1($this->input->get('order').md5($this->input->get('order').$this->input->get('money'))),8);
        if($sign!=substr($md5_code,0,-8)){
            exit('fail');
        }
        
        
        $this->load->model('Game_order_model');
        $condition = array(
            'u_order_id'=>$order,
            'money'=>$this->input->get('money')*100
        );
        $game_order = $this->Game_order_model->get_one_by_condition($condition);
        if($game_order){
            $url = "http://".$_SERVER['HTTP_HOST']."/index.php/api/notify/$game_order->platform/$game_order->game_id?order_id=$order&money=$money&sign=1&appstore=1";
            echo $this->Curl_model->curl_get($url);
        }else{
            echo 'fail';
        }
    }

}
