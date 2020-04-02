<?php

class Mini_game_channel_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('mini_game_channel');
    }
}
