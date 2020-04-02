<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Kefu extends CI_Controller
{
    private $appid = 'wx8e158a6521dbf6e6';//客服小程序[炎龙客服中心]appid
    private $appsecret = '9331a25e3af2ea5298577536a63d79a9';//客服小程序秘钥

//    private $appid = 'wxf0e56e35fe02d0d8';//客服小程序[炎龙客服]appid
//    private $appsecret = '5fb7bf8d58a2e2812a0ee57a214aab92';//客服小程序秘钥
//
//    private $appid = 'wx8e3528326f2d247b';//客服小程序[炎龙在线客服]appid
//    private $appsecret = '02dfd7240490c9be52ef8c06d9f8607a';//客服小程序秘钥
    //微信客服小程序登录
    public function Login()
    {
        $code = $this->input->get('code');
        $user_id = $this->input->get('user_id');
        $appid = $this->appid; //客服小程序1
        $key = $this->appsecret;//客服小程序1
        if ($appid && $code) {
            $this->load->model('Kefu_user_model');
            $this->load->model('Curl_model');
            $requery_url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$key&js_code=$code&grant_type=authorization_code";
            $content = $this->Curl_model->curl_get($requery_url);
            if (!$content) {
                $this->Output_model->json_print(2, 'no content from jscode2session');
                log_message('error', 'no content from jscode2session');
                return;
            }
            $response = json_decode($content);
            if ($response && isset($response->errcode)) {
                $this->Output_model->json_print(3, 'jscode2session error ' . $response->errmsg);
                log_message('error', 'no content from jscode2session');
                return;
            }
            $openid = $response->openid;
            $session_key = $response->session_key;
            $condition = array('open_id'=>$openid, 'appid' => $appid);
            $user = $this->Kefu_user_model->get_one_by_condition($condition);
            if (!$user) {
                $data = array(
                    'user_id' => $user_id,
                    'open_id' => $openid,
                    'appid' => $appid,
                    'create_date' => time(),
                );
                $this->Kefu_user_model->add($data);
                $msg['user_id'] = $data['user_id'];
                $msg['openid'] = $data['open_id'];
            } else {
                $msg['user_id'] = $user->user_id;
                $msg['openid'] = $user->open_id;
            }
            if($user->unionid){
                $msg['is_unionid'] = '1';
            }else{
                $msg['is_unionid'] = '2';
                $msg['session_key'] = $session_key;
            }
            $this->Output_model->json_print(1,'ok',$msg);
        } else {
            $this->Output_model->json_print(1, 'login error');
        }
    }
    //接收反馈表单
    public function feedbackForm(){
        if($_POST){
//            print_r($_POST);die;
            if(!$_POST['category_id'] || !$_POST['open_id'] || !$_POST['app_id'] || !$_POST['content'] || !$_POST['user_id'] || !$_POST['game_id']){
                $arr['msg'] = '3';
                $this->Output_model->json_print(1, 'form error',$arr);die;
            }
            if($_POST['role_id']=='undefined'){
                $_POST['role_id'] = '';
            }
            if($_POST['ext']=='undefined'){
                $_POST['ext'] = '';
            }
            if($_POST['role_name']=='undefined'){
                $_POST['role_name'] = '';
            }
            $data = array(
                'category_id'=>$_POST['category_id'],
                'open_id'=>$_POST['open_id'],
                'app_id'=>$_POST['app_id'],
                'content'=>json_encode($_POST['content'],true),
                'image_url'=>$_POST['image_url']?json_encode($_POST['image_url'],true):'',
                'contact'=>$_POST['contact']?$_POST['contact']:'',
                'user_id'=>$_POST['user_id'],
                'game_id'=>$_POST['game_id'],
                'role_id'=>$_POST['role_id']?$_POST['role_id']:'0',
                'ext'=>$_POST['ext']?$_POST['ext']:'0',
                'role_name'=>$_POST['role_name']?$_POST['role_name']:'',
                'create_date'=>time(),
            );

            $this->load->model('Kefu_feedback_model');
            $res = $this->Kefu_feedback_model->add($data);
//            log_message('debug', 'weixin_kefu:' .$this->db->last_query());
//            print_r($this->db->last_query());die;

            if ($res){
                $arr['msg'] = '1';
                $this->Output_model->json_print(1,'ok',$arr);
            }else{
                $arr['msg'] = '2';
                log_message('debug', 'weixin_kefu:' .$this->db->last_query());
                $this->Output_model->json_print(1,'error',$arr);
            }
        }
    }
    //获取用户反馈记录
    public function problems(){
        $open_id = $_GET['open_id'];
        $condition = array('open_id'=>$open_id);
        $this->load->model('Kefu_feedback_model');
        $this->load->model('Game_model');
        $data = $this->Kefu_feedback_model->get_by_condition($condition);
        foreach (array_reverse($data) as $k=>$v){
            $game = $this->Game_model->get_one_by_condition(array('game_id'=>$v->game_id));
            $arr[$k]=array(
                'Id'=>$v->id,//工单id
                'GameTitle'=> $game->game_name,//游戏名字
                'ErrorType'=> $this->getErrorname($v->category_id),//工单类型
                'DateTime'=> date('Y-m-d',$v->create_date),//反馈时间
                'Content'=>json_decode($v->content,true),//反馈内容
                'ImageUrl'=>json_decode($v->image_url,true),//图片地址
                'Contact'=>$v->contact,//联系方式
                'AppId'=>$v->appid,//小程序appid
                'Answer'=>json_decode($v->kefu_reply,true),//客服回复内容
                'UpdateTime'=>date('Y-m-d H:i:s',$v->kefu_create_date),//客服处理时间 yyyy-dmm-dd hh:ii:ss
                'Status'=>$v->status,//工单状态
            );
        }
        $this->Output_model->json_print(1,'ok',$arr);
    }

    public function getErrorname($value){
        $arr = array(
            '1'=>'登录异常',
            '2'=>'活动异常',
            '3'=>'充值异常',
            '4'=>'BUG反馈',
            '5'=>'游戏币/物品丢失',
            '6'=>'闪退/黑屏/白屏/卡顿',
            '7'=>'提示游戏内存不足',
            '8'=>'游戏建议',
            '9'=>'其他'
        );
        return $arr[$value];
    }

    //接收微信客服小程序图片，并生成链接
    public function uploadImg(){
        $uplad_tmp_name=$_FILES['file']['tmp_name'];
        $uplad_name =$_FILES['file']['name'];
        $image_url="";
        //图片目录
        $img_dir="wxkefuImg/";
        //……html显示上传界面
        /*图片上传处理*/
        //把图片传到服务器
        //初始化变量
        $date = time();
        $uploaded=0;
        $unuploaded=0;
        //上传文件路径
        $img_url="https://api01.baizegame.com/wxkefuImg/";

        //如果当前图片不为空
        if(!empty($uplad_name))
        {
//            $uptype = explode(".",$uplad_name);
            $newname = $_POST['openid'].'-'.time().'.jpg';
            //echo($newname);
            $uplad_name= $newname;
            //如果上传的文件没有在服务器上存在
            if(!file_exists($img_dir.$uplad_name))
            {
                //把图片文件从临时文件夹中转移到我们指定上传的目录中
                $file=$img_dir.$uplad_name;
                move_uploaded_file($uplad_tmp_name,$file);
                chmod($file,0777);
                $img_url1=array(
                    'url'=>$img_url.$newname,
                    'paths'=>$_POST['paths']
                );
                $uploaded++;
                $this->json(1,'success',$img_url1);
            }
        }
        $this->json(0,'error',$img_url1);
    }

    public function json($code,$message="",$data=array()){
        $result=array(
            'code'=>$code,
            'message'=>$message,
            'imgUrl'=>$data['url'],
            'paths'=>$data['paths']
        );
        //输出json
        echo json_encode($result,true);
        exit;
    }

    public function get_kefu(){
        $data['appid'] = $this->appid; //客服小程序appid
        $this->Output_model->json_print(0, 'ok',$data);
    }

    public function post_unionid(){
        $appid = $_POST['wx_appid'];
        $session_key = $_POST['session_key'];
        $iv = $_POST['iv'];
        $encryptedData = $_POST['encryptedData'];

        include_once "WxBizDataCrypt.php";

        $pc = new WXBizDataCrypt($appid, $session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        if ($errCode == 0) {
            log_message('debug', 'wx_unionid:' .$data);
            $data = json_decode($data,true);
            $condition = array(
                'appid'=>$appid,
                'open_id'=>$data['openId'],
            );
            $this->load->model('Kefu_user_model');
            $user = $this->Kefu_user_model->get_one_by_condition_array($condition);
            if (!$user->unionid){
                //在user表中添加该用户的unionid字段
                $this->Kefu_user_model->update(array('unionid'=>$data['unionId']),$condition);
            }
        } else {
            log_message('debug', 'error_wx_unionid:' .$errCode);
        }
    }

}
