<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {
    public function index(){
//         print_r($_SESSION);
        self::assign('data',$_SESSION);
        self::display();
    }
    
    public function hello(){
        //echo 'xx';
    }
    public function hello2(){
        //echo 'xx';
    }
}