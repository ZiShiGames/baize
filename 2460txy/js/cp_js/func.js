//cp 自行编写逻辑
              
//登陆到游戏  关闭登录sdk ,根据自身需要修改             
function login2game() {              
                   $.ajax({
                         type: "POST",
                         url: 'user.php',
                         data: {
                         	user_id:mycars.user_id,
                            channelExt:mycars.channelExt,
                            email:mycars.email,
                            game_appid:mycars.game_appid,
                            new_time:mycars.new_time,
                            loginplatform2cp:mycars.loginplatform2cp,
                            user_id:mycars.user_id,
                            sdklogindomain:mycars.sdklogindomain,
                            sdkloginmodel:mycars.sdkloginmodel,
                            sign:mycars.sign,
                            icon:mycars.icon,
                            nickname:mycars.nickname
                         },
                         dataType: "json",
                         success: function (data) {
                             //关闭sdk
                             var obj = document.getElementById("jsurl_mainframe");
                             obj.style.display= "none";
                         },
                         error:function(){

                         }
                     });
}

//购买商品，商品id,根据自身需要修改
function buy(id) {
        money_=1
        var this_ = this;
        $.ajax({//金额
            type: "POST",
            url: 'buy.php',	//需要修改，修改为自己的
            data: {
                user_id:getQueryString('user_id'),	
                id: id,//商品id
                num:1,//购买数量
                //以上参数根据自己情况修改
                //平台方发送数据 原样返回
                channelExt:getQueryString('channelExt') 
            },
            dataType: "json",
            success: function (data) {
                    var paydata = JSON.stringify(data.data.spen_data);

                    var payifr = document.querySelector('#payurl_mainframe');
                    var uurl=data.url;
                    //判断当前模块
                    if(IsPC()){
                          payifr.src= uurl;
                    }else{
                          payifr.src= uurl.replace('media','mobile');
                    }
                    payifr.style.display= "";
                    payparam = {"event":"tonepay","data":paydata,"status":0};
                    payifr.contentWindow.postMessage(payparam, '*');
            }
        });

    }


//分享到好友、朋友圈时候加上该字符串，用户点击后进入游戏的时候再带上
function share(code,title,desc,imgUrl) {
	data = {"gamecode":code,"title":title,"desc":desc,"imgUrl":imgUrl};
                payparam = {"event":"share","data":data,"status":0};
                parent.postMessage(payparam, '*');
}

//以下是通用函数，不需要修改
      //获取url参数
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return decodeURI(r[2]);
        return null;
    }


    function IsPC() {
        var userAgentInfo = navigator.userAgent;
        var Agents = ["Android", "iPhone",
                    "SymbianOS", "Windows Phone",
                    "iPad", "iPod"];
        var flag = true;
        for (var v = 0; v < Agents.length; v++) {
            if (userAgentInfo.indexOf(Agents[v]) > 0) {
                flag = false;
                break;
            }
        }
        return flag;
    }
