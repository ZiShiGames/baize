<?php

/*
* model for maintain weixin token
*/
class Weixin_model extends CI_Model
{
    // public $appid = $this->config->item("appid");
    // public $secret = $this->config->item("secret");
    // public $noncestr = $this->config->item("noncestr");

    public $receive;
    private $access_token;

    public function __construct()
    {
        parent::__construct();

        // $this->appid = $this->config->item("appid");
        // $this->secret = $this->config->item("secret");
        // $this->noncestr = $this->config->item("noncestr");
        $this->appid = 'wx662e27dac12e4ac8';
        $this->secret = '8339211d30517d3b43f2bb8d09045bab';
        $this->noncestr = 'dsq0FXgaethLL8XcGRSeNjLKltZ8OPLMEVnLlgurWCT';

        // use file cache to store token
        $this->load->driver('cache', array('adapter' => 'file'));

        $this->load->model('Weixin_token_model');
        $this->load->model('Curl_model');

        $this->access_token = $this->Weixin_token_model->get_token();
    }

    /**
     * 获取从微信服务器收到的数据xml
     *
     * @return [type] [description]
     */
    public function receive_data()
    {
        //获取微信服务器发送(post)过来的数据(xml)
        $postStr = file_get_contents("php://input");
        // $postStr = $_GET;
        //调试 输出
        log_message('error', "Token request ".$postStr);

        //xml -> object
        if (!empty($postStr)){
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->receive = $postObj;
            return $this;
        }
    }

    /**
     * 获取用户信息 返回用户对象
     * @param  [type] $openid [description]
     * @return [type]         [description]
     */
    public function get_user_info($openid)
    {
        $access_token = $this->Weixin_token_model->get_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=". $access_token ."&openid=". $openid ."&lang=zh_CN";

        $res = $this->Curl_model->curl_get($url);
        $res = json_decode($res);
        return $res;
    }

    /**
     * 获取关注并登陆的二维码
     * @return  成功返回：二维码地址， 失败：返回 false
     */
    public function get_login_qrcode()
    {
        //请求ticket
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$this->Weixin_token_model->get_token();
        $scene_id = rand(1000000,40000000000);
        $data = '{"expire_seconds": 300, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
        $res = $this->Curl_model->curl_post($url, $data , null , true);
        $res = json_decode($res);

        //检查ticket获取情况
        if($res->ticket)
        {
            //返回二维码地址
            return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$res->ticket;
        }
        else {
            return false;
        }
    }


    public function get_seller_qrcode($user_id)
    {
        //检查用户
        if(!$user_id)
        {
            //用户id为空返回 false
            echo '用户不存在。';
            return false;
        }
        else {
            //检查用户是否是seller, 非seller不可以生成二维码。
            $this->load->model('User_model');
            $condition = array('user_id'=>$user_id);
            $user = $this->User_model->get_one_by_condition($condition);
            if(!@$user->is_seller){
                echo '此用户没有获取二维码的权限。';
                return false;
            }
        }
        //end


        //请求ticket
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$this->Weixin_token_model->get_token();
        //定义二维码的参数值
        $str_scene = 'seller' . $user_id;
        $data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$str_scene.'"}}}';
        $res = $this->Curl_model->curl_post($url, $data , null , true);
        $res = json_decode($res);

        //检查ticket获取情况
        if($res->ticket)
        {
            //返回二维码地址
            return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$res->ticket;
        }
        else {
            return false;
        }
    }

    /**
     * 创建自定义菜单
     * @param  str $menu_data 自定义菜单内容
     * @return multi   创建成功返回 true, 失败时返回string
     *
     * $menu_data 示例：
     * {
        "button": [
            {
                "name": "❤推荐",
                "sub_button": [
                    {
                        "type": "view",
                        "name": "👑龙城霸业",
                        "url": "http://h5.zytxgame.com/index.php/game/redirect/20",
                        "sub_button": [ ]
                    }
                ]
            },
            {
                "type": "view",
                "name": "游戏中心",
                "url": "http://h5.zytxgame.com/",
                "sub_button": [ ]
            },
            {
                "name": "社区",
                "sub_button": [
                    {
                        "type": "view",
                        "name": "奥游社区",
                        "url": "http://buluo.qq.com/mobile/barindex.html?from=wechat&_bid=128&_wv=1027&bid=351897",
                        "sub_button": [ ]
                    },
                    {
                        "type": "view",
                        "name": "商务合作",
                        "url": "http://mp.weixin.qq.com/s/TVURzSfWXnMbxq7bC0TRaQ",
                        "sub_button": [ ]
                    }
                ]
            }
        ]
    }
     */
    public function create_menu($menu_data)
    {
        if(empty($menu_data)){
            return '数据不能为空';
        }
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->Weixin_token_model->get_token();
        $res = $this->Curl_model->curl_post($url, $menu_data, null, true);
        $res = json_decode($res);

        if($res->errmsg =='ok'){
            return true;
        }
        else {
            return $res->errmsg;
        }
    }

    /**
     * 获取现有微信目录
     * @return [type] [description]
     */
    public function get_menu()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$this->Weixin_token_model->get_token();
        $res = $this->Curl_model->curl_get($url);

        echo $res;
    }

    /**
     * 获取 定义在微信公众平台 中的自定义回复规则信息
     * @return [type] [description]
     */
    public function get_auto_reply_info()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/get_current_autoreply_info?access_token=".$this->Weixin_token_model->get_token();
        $res = $this->Curl_model->curl_get($url);

        echo $res;
        $res = json_decode($res);
        print_r($res);
    }

    /**
     * 被动回复文字信息
     * @return [type] [description]
     */
    public function reply_text($content)
    {
        $toopenid = $this->receive->FromUserName;
        $fromopenid = $this->receive->ToUserName;
        $xml = "<xml>
        <ToUserName><![CDATA[". $toopenid ."]]></ToUserName>
        <FromUserName><![CDATA[". $fromopenid ."]]></FromUserName>
        <CreateTime>". time() ."</CreateTime>
        <MsgType><![CDATA[text]]></MsgType>
        <Content><![CDATA[". $content ."]]></Content>
        </xml>";

        echo $xml;
    }


    /**
     * 被动回复图文消息
     *
     *  暂时这里写死，回头完善
     *
     * @return [type] [description]
     */
    public function reply_news()
    {
        $toopenid = $this->receive->FromUserName;
        $fromopenid = $this->receive->ToUserName;
        $xml = "<xml>
        <ToUserName><![CDATA[". $toopenid ."]]></ToUserName>
        <FromUserName><![CDATA[". $fromopenid ."]]></FromUserName>
        <CreateTime>". time() ."</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>1</ArticleCount>
        <Articles>
        <item>
        <Title><![CDATA[点击进入👉【龙城霸业】]]></Title>
        <Description><![CDATA[万千兄弟，龙城齐聚！]]></Description>
        <PicUrl><![CDATA[http://mmbiz.qpic.cn/mmbiz_jpg/REVH4e0kmNGHcbq6KCicPaWpkRQ89gCw2cgvqXtibpIcWfo63XAn1nNCyS18adP4OfaM9d2R32z7aoGb045su0sA/0?wx_fmt=jpeg]]></PicUrl>
        <Url><![CDATA[http://mp.weixin.qq.com/s?__biz=MzI4NjUyMjA2MQ==&mid=100000030&idx=1&sn=b0735b3ef21549b9a4181cb89b30baaa&chksm=6bdaeebc5cad67aa058bb5aad29b1e2d4bea0fb230a7dcf1f942c7134406147a8b18f79c7392#rd]]></Url>
        </item>
        </Articles>
        </xml>";

        echo $xml;
    }


}
