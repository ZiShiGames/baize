<?php

class Allu_model extends CI_Model
{
    public $platform = 'allu';
    public function __construct()
    {
        parent::__construct();

        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function login()
    {
    }

    public function game($platform, $game_id)
    {
    }

    public function trun_to_game($game_id)
    {
    }

    // return order and do the sign varification
    public function get_order_id()
    {
    }

    public function notify_ok()
    {
        echo 'SUCCESS';
    }

    public function notify_error()
    {
        echo 'FAILED';
    }
}
