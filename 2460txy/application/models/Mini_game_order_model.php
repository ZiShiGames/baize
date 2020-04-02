<?php

class Mini_game_order_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('mini_game_order');
    }
}
