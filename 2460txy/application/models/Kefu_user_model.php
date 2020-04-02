<?php

class Kefu_user_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('kefu_user');
    }
}
