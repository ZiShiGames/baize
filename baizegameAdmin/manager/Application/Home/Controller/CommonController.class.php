<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
    
    public function _initialize() {
        self::login_is();
    }
    
    private function login_is(){
        if((time()-$_SESSION['login_time']>C('login_out_time'))&&$_SESSION['admin_info']){
            exit(self::error('操作失败，登录超时。',U('Login/index')));
        }elseif((!$_SESSION['login_time']||!$_SESSION['admin_info'])&&(CONTROLLER_NAME!='Index'&&ACTION_NAME!='index')){
            exit(self::error('操作失败，请登录。',U('Login/index')));
        }elseif((CONTROLLER_NAME=='Index'&&ACTION_NAME=='index')&&(!$_SESSION['login_time']||!$_SESSION['admin_info'])){
            exit(header('Location: '.U('Login/index')));
        }
        $_SESSION['login_time']=time();
    }
}