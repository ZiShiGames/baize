<?php

class Admin_player_login_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('admin_player_login');
    }
}
