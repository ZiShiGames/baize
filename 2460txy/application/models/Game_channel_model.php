<?php

class Game_channel_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('game_channel');
    }
}
