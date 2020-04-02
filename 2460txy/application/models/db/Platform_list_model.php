<?php

class Platform_list_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('platform_server_list');
    }
}
