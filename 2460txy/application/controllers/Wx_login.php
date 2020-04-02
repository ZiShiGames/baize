<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wx_login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Weixin_model');
    }

    public function index(){

        // 接入验证 首次接入是需要验证。之后可以关闭(注释掉)
        // $this->_weixin_valid('h5xly123654');
        // exit;
        // GwjjJbLyjDZQrlzTgLuSWiprqtB54uGdRIy7LxFXYXf
        // log_message('debug',json_encode($_GET));
        $weixin = $this->Weixin_model->receive_data();
        var_dump($weixin);
        return;
        if ($weixin->receive) {
            //log_message('debug', "Token request ".$weixin->receive->FromUserName);

            //收到事件
            if ($weixin->receive->MsgType == 'event') {
                //log_message('debug', "Token request event");

                //关注公众号事件
                if ($weixin->receive->Event == "subscribe") {
                    //获取微信用户信息
                    $weixin_user = $weixin->get_user_info($weixin->receive->FromUserName);
                    //检查表中是否有此用户记录
                    //如果有 不插入记录
                    //如果没有 将用户插入user表
                    $condition = array('openid' => $weixin_user->openid);
                    $user = $this->User_model->get_one_by_condition($condition);
                    if (!@$user->user_id) {
                        //如果是通过扫分销商的二维码进入的
                        if (strrpos($weixin->receive->EventKey, "qrscene_seller")!==false) {
                            $seller_id = str_replace("qrscene_seller", "", $weixin->receive->EventKey);
                            //检查seller状态
                            $condition = array('user_id'=> $seller_id);
                            $seller = $this->User_model->get_one_by_condition($condition);
                            if ($seller->is_seller and $seller->seller_status) {
                                $new_user['refer_by'] = $seller_id;
                            }
                        }
                        //插入用户
                        $new_user['openid'] = $weixin_user->openid;
                        $new_user['unionId'] = $weixin_user->unionid;
                        $new_user['nickname'] = $weixin_user->nickname;
                        $new_user['sex'] = $weixin_user->sex;
                        $new_user['language'] = $weixin_user->language;
                        $new_user['city'] = $weixin_user->city;
                        $new_user['province'] = $weixin_user->province;
                        $new_user['country'] = $weixin_user->country;
                        $new_user['headimgurl'] = $weixin_user->headimgurl;
                        $new_user['create_date'] = time();
                        $this->User_model->add($new_user);
                    }
                    // end

                    //订阅后的自动回复
                    $weixin->reply_text('玩游戏请点击【<a href="http://h5.zytxgame.com/index.php/LoginWeb">游戏平台</a>】
领礼包请点击【<a href="http://h5.zytxgame.com/index.php/LoginWeb">领取礼包</a>】

客服问题请加客服QQ群：

龙城霸业客服QQ群：252505042
机甲三国客服QQ群：615595794');
                }
            }

            //收到文字
            if ($weixin->receive->MsgType == 'text') {
                //log_message('debug', "Token request event");

                //用户发送的str
                $user_str = $weixin->receive->Content;
                if (in_array($user_str, array("礼包", "礼", "礼品", "大礼包"))) {
                    $weixin->reply_text('<a href="http://h5.zytxgame.com?gift=1#gift">礼包领取</a>');
                    exit;
                }
                if (in_array($user_str, array("龙城霸业", "龙城", "霸业", "龙" ,"霸" ,"决战沙城", "决战", "传奇"))) {
                    // $weixin->reply_text('【龙城霸业】
                    //                         是兄弟，龙城再聚首！
                    //                         群雄聚，激战挣天下！

                    //                         🔥点击进入游戏🔥
                    //                         无需下载点击即玩');
                    $weixin->reply_news();
                    exit;
                }

                $weixin->reply_text('玩游戏请点击【<a href="http://h5.zytxgame.com/index.php/LoginWeb">游戏平台</a>】
领礼包请点击【<a href="http://h5.zytxgame.com/index.php/LoginWeb">领取礼包</a>】

客服问题请加客服QQ群：

龙城霸业客服QQ群：252505042
机甲三国客服QQ群：615595794');
            }
        }
    }
    private function _weixin_valid($token)
    {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array($token, $timestamp, $nonce);
            // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            echo $echoStr;
        } else {
            return false;
        }
    }
}
