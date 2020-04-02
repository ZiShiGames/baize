<?php

class Ip_library_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('ip_library');
    }
}
