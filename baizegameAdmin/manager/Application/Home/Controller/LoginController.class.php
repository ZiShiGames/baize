<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
    
    public function index(){
        self::display(); 
    }
    
    public function lockscreen(){
        if (IS_POST){
            if($_POST['pwd']!=''&&$_SESSION['admin_info']['admin_user_password']==md5($_POST['pwd'].C('pwd_confuse'))){
                $_SESSION['login_time']=time();
                exit(self::success('欢迎回来'));
            }elseif(empty($_SESSION)){
                exit(self::error('未登陆 请刷新页面重新登录'));
            }else{
                exit(self::error('密码错误'));
            }
        }
        self::display();
    }
    
    public function login_out(){
        session_destroy();
        self::success('安全退出成功',U('Login/index'),3);
    }
    
    public function heartbeat(){
        if((time()-$_SESSION['login_time']>C('login_out_time'))&&$_SESSION['admin_info']){
            echo 'false';
        }else{
            echo 'true';
        }
    }
    
    public function auth(){
        if (IS_POST){
            $user=trim($_POST['user']);
            if(!empty($user)&&$_POST['pwd']!=''){
                $user_list=M('admin_user','','DB_CONFIG1')->where(array('admin_user_name'=>$user,'admin_user_password'=>md5($_POST['pwd'].C('pwd_confuse'))))->cache(60)->find();
                if (!empty($user_list)){
                    $_SESSION['login_time']=time();
                    $_SESSION['admin_info']=$user_list;
                    self::success('欢迎回来：'.$user_list['admin_user_name'],U('Index/index'),3);
                }else{
                    self::error('密码错误');
                }
            }else{
                self::error('必填字段不能为空!');
            }
        }else{
            self::error('访问的页面已丢失');
        }
        
    }
    
    
    
}