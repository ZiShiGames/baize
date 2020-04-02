<?php
namespace Publics\Controller;
use Think\Controller;
class IndexController extends Controller {
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