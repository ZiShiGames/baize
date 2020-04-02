<?php

class Mini_login_log_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('mini_login_log');
    }
}
