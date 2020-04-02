<?php

class Game_father_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('game_father');
    }
}
