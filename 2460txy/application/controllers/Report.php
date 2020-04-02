<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Report extends CI_Controller
{
    public function index()
    {
        $this->load->model('Game_order_model');
        $result = $this->Game_order_model->report();
        if ($result) {
            $content = "订单号,钱(元),用户ID,渠道,时间\n";
            foreach ($result as $order) {
                $content .= $order->u_order_id;
                $content .=  ',';
                $content .=  ($order->money / 100);
                $content .=  ',';
                $content .=  $order->user_id;
                $content .=  ',';
                $content .=  $order->platform;
                $content .=  ',';
                $content .=  date('Y-m-d H:i:s', $order->create_date);
                $content .=  "\n";
            }
            file_put_contents('./debug/report.csv', $content);
            header('Location: /debug/report.csv');
        } else {
            echo 'no';
        }
    }

    public function daily_income()
    {
        $date = $this->input->get('date');
        $to = $this->input->get('to');
        $platform = $this->input->get('platform');
        $game_id = $this->input->get('game_id');

        if (!$date || !$platform || !$game_id) {
            $this->Output_model->json_print(1, 'params not enough');

            return;
        }

        $current_date = $this->str_to_zero_time($date);
        if (!$current_date) {
            $this->Output_model->json_print(1, 'date format wrong');

            return;
        }

        if (!$to) {
            $next_date = $current_date + 60 * 60 * 24;
        } else {
            $next_date = $this->str_to_zero_time($to);
            if (!$next_date) {
                $this->Output_model->json_print(1, 'to format wrong');

                return;
            }
        }

        if ($current_date >= $next_date) {
            $this->Output_model->json_print(1, 'date must < to');

            return;
        }

        $condition = array(
                    'create_date >= ' => $current_date,
                    'create_date <= ' => $next_date,
                    'platform' => $platform,
                    'game_id' => $game_id,
                    'status' => 2,
                );

        $this->load->model('Game_order_model');
        $orders = $this->Game_order_model->get_by_condition($condition);
        $total = 0;
        if ($orders) {
            foreach ($orders as $one) {
                $total += $one->money;
            }
        }

        $this->Output_model->json_print(0, 'ok', $total);
    }

    // date string to 00:00:00 of the give day
    public function str_to_zero_time($str)
    {
        $current_date = strtotime($str);
        if (!$current_date) {
            return false;
        }

        $current_date_str = date('Y-m-d', $current_date);
        $current_date = strtotime($current_date_str);

        return $current_date;
    }
}
