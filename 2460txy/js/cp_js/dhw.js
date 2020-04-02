var mycars=new Object();
$(function(){
    // 登录 iframe
    $("#dhwgame").append('<iframe id="jsurl_mainframe" frameborder="0" scrolling="yes" name="jsmain" src="" style="top:0px;height:100vh; display: none; visibility: inherit; width: 100vw;max-width:1000px; z-index: 1;overflow: visible;position:absolute;background-repeat:no-repeat;background-size:cover;"></iframe>');
    // 支付
    $("#dhwgame").append('<iframe id="payurl_mainframe" frameborder="0" scrolling="yes" name="jsmain" src="" style="top: 0px;height:100vh; display: none; visibility: inherit; width: 100vw;max-width:1000px; z-index: 1;overflow: visible;position:absolute;background-repeat:no-repeat;background-size:cover;"></iframe>');
    //接受运营方传过来的参数
    mycars.channelExt=getQueryString('channelExt');
    mycars.email=getQueryString('email');
    mycars.game_appid=getQueryString('game_appid');
    mycars.new_time=getQueryString('new_time');
    mycars.loginplatform2cp=getQueryString('loginplatform2cp');
    mycars.user_id=getQueryString('user_id');
    mycars.sdklogindomain=getQueryString('sdklogindomain');
    mycars.sdkloginmodel=getQueryString('sdkloginmodel');
    mycars.sign=getQueryString('sign');
    mycars.icon=getQueryString('icon');
    mycars.nickname=getQueryString('nickname');
    var ifr = document.querySelector('#jsurl_mainframe');
		$('#username').html(mycars.nickname);
		$('#logo').attr('src',mycars.icon);

    //true 电脑  false 手机 
    if(IsPC()){
        ifr.src="http://www.duohw.cn/media.php?s=/Game/singlegame.html";
    }else{
        ifr.src="http://www.duohw.cn/mobile.php?s=/Game/singlegame.html";
    }

    var  refff=document.referrer;

    //判断域名来源
    if(refff.match('/www.duohw.cn/')){

        //吊起登录sdk
        ifr.onload=function(){
            if (!mycars.user_id) {
                var userdata = JSON.stringify(mycars);
                ifr.style.display = "";
                loginparam = {"event": "login", "data": userdata, "status": 0};
                ifr.contentWindow.postMessage(loginparam, '*');
            }else{
                 //无登录逻辑则直接关闭sdk
                //var obj = document.getElementById("jsurl_mainframe");
                //obj.style.display= "none";

              //cp 自行 编写逻辑 后  关闭登录sdk
							login2game();
            }
        }

        window.addEventListener('message',function(e){
            console.log(e);
            // 支付sdk
            var payifr = document.querySelector('#payurl_mainframe');


            //判断运营商返回的数据
            if(e.data.event == 'login'&&e.data.status == 1){
                 // 登录sdk 
                var obj = document.getElementById("jsurl_mainframe");
                obj.style.display= "none";              
            }else if(e.data.event=="pay_result"&&e.data.status==-1){
                payifr.style.display= "none";
            }else if(e.data.event=="pay_result"&&e.data.status==1){
                payifr.style.display= "none";
            }else if(e.data.event=="game:shareSdk:callback"&&e.data.status==1){
		//分享成功
                alert("分享成功");
            }else if(e.data.event=="game:shareSdk:callback"&&e.data.status==0){
		//分享失败，分享被取消
                alert("分享失败");
            }



        });

    }



})
