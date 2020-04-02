<?php

class Platform_data_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('platform_data');
        $this->load->driver('cache', array('adapter' => 'file'));
    }
}
