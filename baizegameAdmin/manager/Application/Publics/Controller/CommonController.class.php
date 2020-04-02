<?php
namespace Publics\Controller;
use Think\Controller;
class CommonController extends Controller {
    
    public function _initialize() {
        self::login_is();
    }
    
    private function login_is(){
        header('Location: '.U('Index/index'));
    }
}