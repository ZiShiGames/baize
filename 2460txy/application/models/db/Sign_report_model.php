<?php

class Sign_report_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->set_table('sign_report');
    }
}
