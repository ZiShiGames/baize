<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta id="viewport" name="viewport" content="user-scalable=no,target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0, width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-capable" content="yes" />

    <meta name="x5-fullscreen" content="true" />
    <meta name="x5-page-mode" content="app" />
    
    <meta name="screen-orientation" content="portrait" />
    <meta name="tencent-x5-page-direction" content="portrait" />
  


    <meta name="browsermode" content="application" />
    <meta name="full-screen" content="yes" />

    <!-- <script src='http://h5sdk.zytxgame.com/js/g2b_loader.js'></script> -->
    <link rel="stylesheet" href="/css/style.css">
    <script src='/js/g2b_wxh5_loader.js?v=<?php echo time(); ?>'></script>
    <style type="text/css">
        html {
            height: 100%;
            background-color: black;
            background-size: 100% 100%;
        	overflow-y: hidden;
        	background-repeat:no-repeat;
        	<?php

            switch ($game->game_father_id) {
                case 2:
                    echo 'background-image: url("//api.baizegame.com/img/resource/4FzFLPR4DxXx5I6q.png");';
                    break;
                case 3:
                    echo 'background-image: url("https://baize-1258870178.cos.ap-guangzhou.myqcloud.com/imgs/xjl20190924.jpg");';
                    break;
                default:
                    break;

            }

      //  if($game->game_father_id == 2){
      //          echo 'background-image: url("//api.baizegame.com/img/resource/4FzFLPR4DxXx5I6q.png");';
      //      }else{
      //          /* echo 'background-image: url("/img/login/huawei/xjl1.png");'; */
      //      }
            

            ?>
        }

        .noselect {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .cgr {
            color: #5aff00;
        }

        .cy {
            color: #fff000;
        }

        .cw {
            color: white;
        }

        .bs1 {
            text-shadow: -1px -1px 1px black, 1px 1px 1px black, 1px -1px 1px black, -1px 1px 1px black;
        }

        .fs1 {
            font-size: 18px;
        }

        .fs2 {
            font-size: 30px;
        }

        .fs3 {
            font-size: 20px;
        }

        .fl {
            float: left;
        }

        .clearb {
            clear: both;
        }

        .ma {
            margin: auto;
        }

        .bb {
            -webkit-box-sizing: border-box;
        }

        .bd {
            background: rgba(0, 0, 0, 0.75);
            border: 2px solid rgba(0, 0, 0, 0.75);
            border-radius: 15px;
            font-size: 15px;
        }

        .bd:before {
            content: '';
            display: block;
            width: 100%;
            height: 100%;
            border: 5px solid #dca53c;
            position: absolute;
            pointer-events: none;
            top: 0px;
            left: 0px;
            border-radius: 15px;
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
        }

        .tc {
            text-align: center;
        }

        .pa {
            position: absolute;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 2;
        }

        .pr {
            position: relative;
        }

        .text {
            font-size: 20px;
            font-family: "SourceHanSansCN-Normal";
            color: white;
            position: absolute;
            top: 120px;
            width: 200%;
            left: -50%;
            text-align: center;
        }

        .highLight {
            -webkit-box-shadow: 0px 0px 50px #2f9aff;
        }

        .highLight:after {
            content: '';
            width: 42px;
            height: 43px;
            position: absolute;
            top: 60px;
            left: 60px;
        }

    </style>
    <script type="text/javascript">
        var gameAppId = <?php echo $appId ?>;
        var infos = {
            "<?php echo $passId ?>": "{\"passType\":\"QHSER\"}"
        }
        var passIds = Object.keys(infos);
        var passId = passIds[0];
        var datas = JSON.parse(infos[passId]);
        var passType = datas.passType;
        var checkcookie;
        var userAuth = {};
        var cookiePath = '';
        var account;
        var password;
        var user_id;
        var frameWidth;
        var frameHeight;
        var passTypeFuncMap;

        function closePayWindow() {
            document.getElementById('items').innerHTML = '';
            document.getElementById('recharge').style.display = 'none';
        }

        function clear() {
            window.scrollTo(0, 1);
            var viewportmeta = document.querySelector('meta[name="viewport"]');
            var ua = navigator.userAgent;
            if (viewportmeta) {
                if (ua.indexOf("iPhone") > -1 || ua.indexOf("iPad") > -1) {
                    viewportmeta.content =
                        'width=device-width, initial-scale=1.0, width=device-width, minimum-scale=1.0, maximum-scale=1.0';
                }
            }
        }
        window.addEventListener('load', function () {
            //设置背景操作
            document.getElementById('loader').style.display = 'block';
            setTimeout(function () {
                debugtolog('wwww' + document.documentElement.clientWidth + 'hhhh' + document.documentElement
                    .clientHeight + "   innerW" + window.innerWidth + "   innerH" + window.innerHeight
                );
                showPreScreen();
                // var param = g2b.getParameters();
                // console.log("gameAppId " + gameAppId + " passId " + passId) ;
                // param['appId'] = gameAppId;
                // param['passId'] = passId;
                // g2b.login(param);
//                 showLoginPage();
                // break;
            }, 1);

            document.body.style.background = ''
        }, true);

        // function sign_create(type) {
        //     if (type == 'login') {
        //         var account = document.getElementById('loginAccount').value;
        //         var password = document.getElementById('loginPassword').value;
        //         if (!account || !password) {
        //             alert('账号或密码为空');
        //         } else {
        //             var url = "/index.php/allu_login/login?account=" + account + "&password=" + password;
        //         }
        //
        //     } else if (type == 'sign') {
        //         var account = document.getElementById('signAccount').value;
        //         var password = document.getElementById('signPassword').value;
        //         var _password = document.getElementById('_signPassword').value;
        //         if(account.length <6){
        //             alert('用户名不得少于6位。');
        //             return;
        //         }
        //         if(password.length <6){
        //             alert('密码不得少于6位。');
        //             return;
        //         }
        //         if(password !== _password){
        //             alert('两次密码不一致,请检查后再次输入');
        //             return;
        //         }else{
        //             var url = "/index.php/allu_login/sign_create?account=" + account + "&password=" + password;
        //             console.log(url);
        //         }
        //     } else if (type == 'one_key') {
        //         var url = "/index.php/allu_login/one_key_sign";
        //     }
        //     var that = this;
        //
        //     this.g2b.getDataXHR(url, function (res) {
        //
        //             if (type == 'sign') {
        //                 if (res.c == 0) {
        //                     alert('注册成功,点击确定进入游戏。');
        //                     document.getElementById('signBox').style.display="none";
        //                     that.g2blogin(account,password);
        //                 } else if (res.c == 1) {
        //                     alert('用户已存在');
        //                 } else if (res.c == 3) {
        //                     alert('账户和密码错误');
        //                 }
        //                 } else if (type == 'one_key') {
        //                     if (res.c == 0) {
        //                         document.getElementById('loginAccount').value = res.d.account;
        //                         document.getElementById('loginPassword').value = res.d.password;
        //                         document.getElementById('hintBox').innerHTML = "请截图保存账号密码。";
        //                         document.getElementById('loginClick').style.backgroundColor = "grey";
        //                         document.getElementById('loginClick').onclick = '';
        //                         document.getElementById('loginClick').innerHTML = "登录(3)";
        //                         var timer = 3;
        //                         var _timer = setInterval(() => {
        //                             timer--;
        //                             document.getElementById('loginClick').innerHTML = "登录(" + timer + ")";
        //                             if (timer == 0) {
        //                                 document.getElementById('loginClick').innerHTML = "登录";
        //                                 clearInterval(_timer);
        //                                 document.getElementById('loginClick').style.backgroundColor =
        //                                     "#f9d337";
        //                                 document.getElementById('loginClick').onclick = function () {
        //                                     loginClick()
        //                                 };
        //                             }
        //                         }, 1000);
        //                     } else {
        //                         alert('网络异常,请稍后再试.');
        //                     }
        //
        //                 } else if (type == 'login') {
        //
        //                 }
        //
        //
        //     });
        //
        // }

        function loginClick(event) {
            var loginAccount = document.getElementById('loginAccount').value;
            if (!loginAccount) {
                alert('登录异常,请返回公众号重新进入游戏。')
            } else {
                var url = "/index.php/allu_login/wxh5_login?account=" + loginAccount;
            }
            this.g2b.getDataXHR(url, function (res) {
                if (res.c == 0) {
                    console.log(res.d);
                    g2blogin(res.d.account);
                } else if (res.c == 1) {
                    alert('登录异常,请返回公众号重新进入游戏。');
                }

            });


            console.log('点击登录');
        }
        function g2blogin(account){
                    localStorage.setItem('account', account);
                    var param = g2b.getParameters();
                    console.log("gameAppId " + gameAppId + " passId " + passId);
                    param['appId'] = gameAppId;
                    param['passId'] = passId;
                    param['account'] = account;
                    g2b.login(param);
                    console.log('okok');
        }

        function showLoginPage() {
            console.log("login");
            if (localStorage.account) {
                console.log(localStorage.account);
                document.getElementById('loginAccount').value = localStorage.account;
            }
        }



        function loginCallBack(rspObj) {
            console.log("loginCallBack");
            //登录成功
            if (rspObj && rspObj.result === 0) {

            }
        }

        function loadScript(url, callback) {
            var script = document.createElement("script");
            script.type = "text/javascript";

            script.onload = function () {
                callback();
            };
            script.onerror = function () {
                script.parentNode.removeChild(script);
                setTimeout(function () {
                    loadScript(url, callback);
                }, 1000);
            };
            script.src = url;
            document.getElementsByTagName("head")[0].appendChild(script);
        }


        function signIn(event) {
            event.style.color = '#fff';
            console.log('点击一键注册');
        }

        function signInHover(event, type) {
            switch (type) {
                case 0:
                    event.style.color = '#333';
                    event.style.backgroundColor = '#f9d337';
                    event.style.border = '1px solid #f9d337';
                    break;
                case 1:
                    event.style.color = '#333';
                    event.style.backgroundColor = '#fff';
                    event.style.border = '1px solid #333';
                    break;
                default:
                    console.log('error');
                    break;
            }
        }

        // css加伪元素
        function loadStyleString(css) {
            var style = document.createElement("style");
            style.type = "text/css";
            try {
                style.appendChild(document.createTextNode(css));
            } catch (ex) {
                style.styleSheet.cssText = css;
            }
            var head = document.getElementsByTagName('head')[0];
            head.appendChild(style);
        }

        // 关闭登录页面
        function signIn_hide() {
            var signInModal = document.getElementById('signInModal');
            document.getElementsByTagName("body")[0].removeChild(signInModal);
        }


        function showPreScreen() {
            console.log("showPreScreen");
            //适配 存储宽高  2016年9月19日17:00:03 改为放到load事件内获取
            frameWidth = document.documentElement.clientWidth || window.innerWidth || document.body.clientWidth;
            frameHeight = document.documentElement.clientHeight || window.innerHeight || document.body.clientHeight;
            debugtolog('pre  wwww' + document.documentElement.clientWidth + 'hhhh' + document.documentElement.clientHeight +
                "   innerW" + window.innerWidth + "   innerH" + window.innerHeight);
            window.scrollTo(0, 1);
            var orien = getOrientation();
            var w, h;
            h = frameHeight;
            w = frameWidth;

            //简单适配 游戏div和充值div
            document.getElementById('gameDiv').style.width = (frameWidth > frameHeight ? frameHeight * 0.6 : frameWidth) +
                'px';
            document.getElementById('gameDiv').style.height = frameHeight + 'px';
            document.getElementById('recharge').style.height = frameHeight + 'px';
            document.getElementById('recharge').style.width = (frameWidth > frameHeight ? frameHeight * 0.6 :
                frameWidth) + 'px';
            var ratio = h / w;
            var maxRatio = 1.77917;
            var minRatio = 1.7;
            var _uniScale = Math.min(h / 800, w / 480);
            var _topY, _leftX;

            var _scaleY = h / 800;
            var _scaleX = w / 480;
            _scaleY = _uniScale;
            _scaleX = _uniScale;
            _topY = (h - _uniScale * 800) / 2;
            _leftX = (w - _uniScale * 480) / 2;



            var style = document.createElement("style");
            style.type = "text/css";
            style.innerHTML = '#gbox{left:' + _leftX + 'px;top:' + _topY +
                'px;transform-origin:0px 0px;-ms-transform-origin:0px 0px;-webkit-transform-origin:0px 0px; -moz-transform-origin:0px 0px;-o-transform-origin:0px 0px;-webkit-transform:scale(' +
                _scaleX + ',' + _scaleY + ');-o-transform:scale(' + _scaleX + ',' + _scaleY + ');-moz-transform:scale(' +
                _scaleX + ',' + _scaleY + ');}\n';
            document.getElementsByTagName("head")[0].appendChild(style);


            var gbox = document.getElementById("gbox");
            if (orien != 'portrait')
                gbox.style.display = 'none';
            else
                gbox.style.display = 'block';
        }
        function signUp_show() {
                console.log("signUp");
                console.log("注册");
                document.getElementById("loginBox").style.display="none";
                document.getElementById("signBox").style.display="block";
                document.getElementById('signUpCloses').style.display = 'block';

                loadStyleString("#signUpCloses::before{content:'\\00D7';width:100%;height:100%;position:absolute;bottom: 10px;left: 0;}");

            }

        function getOrientation() {
            var ua = navigator.userAgent;
            if (ua.match(/qzone/i))
                return 'portrait';
            var orien;
            switch (window.orientation) {
                case 90:
                case -90:
                    orien = 'landscape';
                    break;
                default:
                    orien = 'portrait';
            }
            return orien;
        }

        function debugtolog(log) {
            var debug_log = document.getElementById('debug-log');
            var logs = debug_log.innerHTML;
            logs += ('<br><br>' + log + "  " + new Date());
            debug_log.innerHTML = logs;
        }
        function signUp_hide(){
            // document.getElementById('signBox').style.display="none";
            // document.getElementById('loginBox').style.display="block";
            document.getElementById('signBox').style.display="none";
            // document.getElementById('signBox').innerHTML = "";
            document.getElementById('loginBox').style.display="block";

        }

        function closedebug() {
            var debug_log = document.getElementById('debug-log');
            debug_log.style.display = 'none';
        }

        function showdebug() {
            var debug_log = document.getElementById('debug-log');
            debug_log.style.display = 'block';
        }

        function jumpTo(url) {

            window.location.href = url;
        }

        // utility function called by getCookie()
        function getCookieVal(offset) {
            var endstr = document.cookie.indexOf(";", offset);
            if (endstr == -1) {
                endstr = document.cookie.length;
            }
            return unescape(document.cookie.substring(offset, endstr));
        }

        // primary function to retrieve cookie by name
        function getCookie(name) {
            var arg = name + "=";
            var alen = arg.length;
            var clen = document.cookie.length;
            var i = 0;
            while (i < clen) {
                var j = i + alen;
                if (document.cookie.substring(i, j) == arg) {
                    return getCookieVal(j);
                }
                i = document.cookie.indexOf(" ", i) + 1;
                if (i == 0) break;
            }
            return null;
        }

        // store cookie value with optional details as needed
        function setCookie(name, value, expires, path, domain, secure) {
            document.cookie = name + "=" + escape(value) +
                ((expires) ? "; expires=" + expires : "") +
                ((path) ? "; path=" + path : "") +
                ((domain) ? "; domain=" + domain : "") +
                ((secure) ? "; secure" : "");
        }

        function deleteCookie(name) {
            var exp = new Date();
            exp.setTime(exp.getTime() - 1);
            var cval = getCookie(name);

            setCookie(name, cval, exp.toGMTString(), cookiePath);
        }


    </script>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>
        <?php echo $game->game_name ?>
    </title>
</head>

<body scroll='no' style='background-color:black'>

<!--<div id="loginBox" style="display: none; width:100%;height:100%;background-color:rgba(0,0,0,0.5);position: fixed;left:0;top:0;z-index:999999;" alt="遮罩">-->
<!--<div style="position:absolute;left:50%;top:20%;width:280px;height:240px;margin-left:-140px;background-color:#fff;overflow:hidden;border-radius:10px;" alt="弹框体">-->
<!--<div id="signInCloses" onclick="signIn_hide()" style="color: #5c6280;line-height: 3.5rem;text-align:center;height:40px;width: 40px;font-size:34px;position: absolute;top:0;right: 0; z-index: 999; cursor:pointer;opacity:0.5;" alt="关闭按钮"></div>-->
<!--<h6 style="font-size:20px;color:#333;margin:10px auto;width:100%;text-align:center;font-weight:normal;" alt="标题">-->
<!--登录-->
<!--</h6>-->
<!--<div alt="用户交互区">-->
<input id="loginAccount" type="hidden" value="{:$_GET['uid']}>"  >
<!--<div style="width:88%;height:auto;margin:0 auto;overflow:hidden;">-->
<!--<input id="loginPassword" style="float:left;font-size: 14px;width:64%;height:40px;line-height:40px;margin:0 auto;border:none;border-bottom: 1px solid #ddd;display:block;padding:4px 10px;box-sizing:border-box;outline:none;border-radius:0;" maxlength="20" value="" placeholder="密码" alt="输入密码">-->
<!--<button id="signIn" style="float:right;width: 30%;height:40px;line-height:40px;background-color:unset;border:1px solid #333;border-radius:4px;outline:none;cursor:pointer;" type="button" name="button" alt="注册按钮" onclick="sign_create('one_key')" onmouseover="signInHover(this,0)" onmouseout="signInHover(this,1)">-->
<!--一键注册-->
<!--</button>-->
<!--</div>-->
<!--<p id="hintBox" style="text-align: center;color: red;margin-top: 10px;">-->
<!--</p>-->
<!--</div>-->
<!--<div id="signClick" class="noselect" style="position:absolute;right:0;bottom:0px;width:50%;height:50px;line-height: 50px;background-color:#f9d337;color:#333;text-align:center;cursor:pointer;" alt="登录按钮" onclick="signUp_show()">-->
<!--注册-->
<!--</div>-->
<!--<div id="loginClick" class="noselect" style="position:absolute;left:0;bottom:0;width:50%;height:50px;line-height: 50px;background-color:#f9d337;color:#333;text-align:center;cursor:pointer;" alt="登录按钮" onclick="loginClick()">-->
<!--登录-->
<!--</div>-->
<!--</div>-->
<!--</div>-->
<!---->
<!-- <div id="signBox" style="width:100%;height:100%;background-color:rgba(0,0,0,0.5);position: fixed;left:0;top:0;z-index:999999;display:none" alt="遮罩">-->
<!-- <div style="position:absolute;left:50%;top:20%;width:280px;height:260px;margin-left:-140px;background-color:#fff;overflow:hidden;border-radius:10px;" alt="弹框体">-->
<!-- <div id="signUpCloses" onclick="signUp_hide()" style="color: #5c6280;line-height:3.5rem;text-align:center;height:40px;width: 40px;font-size:34px;position:absolute;top:0;right:0; z-index: 999; cursor:pointer;opacity:0.5;" alt="关闭按钮"></div>-->
<!-- <h6 style="font-size:20px;color:#333;margin:10px auto;width:100%;text-align:center;font-weight:normal;" alt="标题">-->
<!-- 注册-->
<!-- </h6>-->
<!-- <div alt="用户交互区">-->
<!-- <input id="signAccount" style="font-size:14px;width:88%;height:40px;line-height:40px;margin:0 auto;margin-bottom:10px;border:none;border-bottom: 1px solid #ddd;display:block;padding:4px 10px;box-sizing:border-box;outline:none;border-radius:0;" type="text" name="" value="" placeholder="账号(不少于6位)" alt="输入账号">-->
<!-- <div style="width:88%;height:auto;margin:0 auto;overflow:hidden;">-->
<!-- <input id="signPassword" style="float:left;font-size: 14px;width:100%;height:40px;line-height:40px;margin:0 auto;border:none;border-bottom: 1px solid #ddd;display:block;padding:4px 10px;box-sizing:border-box;outline:none;border-radius:0;" type="password" name="password" maxlength="20" value="" placeholder="密码(不少于6位)" alt="输入密码">-->
<!-- <input id="_signPassword" style="float:left;font-size: 14px;width:100%;height:40px;line-height:40px;margin:0 auto;border:none;border-bottom: 1px solid #ddd;display:block;padding:4px 10px;box-sizing:border-box;outline:none;border-radius:0;" type="password" name="password" maxlength="20" value="" placeholder="再次输入密码" alt="输入密码">-->
<!-- </div>-->
<!-- </div>-->
<!-- <div id="loginClick" style="position:absolute;left:0;bottom:0;width:100%;height:50px;line-height: 50px;background-color:#f9d337;color:#333;text-align:center;cursor:pointer;" alt="登录按钮" onclick="sign_create('sign')">-->
<!-- 注册-->
<!-- </div>-->
<!-- </div>-->
<!-- </div>-->


    <div id='gbox' style='position:relative;overflow:hidden;display:none;width:480px;height:800px'>

        <div id='jumping' class='pa' style='display:none;z-index:200;width:1242px;height:2208px;left:0px;top:0px;'>
            <div class='pa bd tc' style='width:672px;height:155px;left:285px;top:870px;color:white;font-size:52px;line-height:155px;'>
                进入游戏中，请稍候...
            </div>
        </div>

        <div id='loading1' class='pa' style='display:none;z-index:200;width:100%;left:0px;top:0px;'>
            <div class='pr bd tc' style='margin:auto;width:60%;top:40%;height:100px;line-height:100px;color:white;font-size:20px;'>
                加载中，请稍候...
            </div>
        </div>

    </div>
    <div id="loader" style='position:absolute;margin:auto;top:0px;left:0px;bottom:0px;right:0px;display:none'>
        <svg viewBox="0 0 120 120" style='display:none;width:50px;height:50px;position:absolute;margin:auto;top:0;left:0;bottom:0;right:0'
            version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <g id="circle" class="g-circles g-circles--v3">
                <circle id="12" transform="translate(35, 16.698730) rotate(-35) translate(-35, -16.698730) " cx="35" cy="16.6987298"
                    r="10"></circle>
                <circle id="11" transform="translate(16.698730, 35) rotate(-60) translate(-16.698730, -35) " cx="16.6987298"
                    cy="35" r="10"></circle>
                <circle id="10" transform="translate(10, 60) rotate(-90) translate(-10, -60) " cx="10" cy="60" r="10"></circle>
                <circle id="9" transform="translate(16.698730, 85) rotate(-120) translate(-16.698730, -85) " cx="16.6987298"
                    cy="85" r="10"></circle>
                <circle id="8" transform="translate(35, 103.301270) rotate(-150) translate(-35, -103.301270) " cx="35"
                    cy="103.30127" r="10"></circle>
                <circle id="7" cx="60" cy="110" r="10"></circle>
                <circle id="6" transform="translate(85, 103.301270) rotate(-30) translate(-85, -103.301270) " cx="85"
                    cy="103.30127" r="10"></circle>
                <circle id="5" transform="translate(103.301270, 85) rotate(-60) translate(-103.301270, -85) " cx="103.30127"
                    cy="85" r="10"></circle>
                <circle id="4" transform="translate(110, 60) rotate(-90) translate(-110, -60) " cx="110" cy="60" r="10"></circle>
                <circle id="3" transform="translate(103.301270, 35) rotate(-120) translate(-103.301270, -35) " cx="103.30127"
                    cy="35" r="10"></circle>
                <circle id="2" transform="translate(85, 16.698730) rotate(-150) translate(-85, -16.698730) " cx="85" cy="16.6987298"
                    r="10"></circle>
                <circle id="1" cx="60" cy="10" r="10"></circle>
            </g>
        </svg>
    </div>
    <div id='getValue' style='display:none' value=""></div>
    <script type='text/javascript'>
    </script>

   <!--  <div style='width:10px;height:10px;position:absolute;top:0px;left:0px;z-index:999' onclick='showdebug()'> -->

    </div>
    <!-- 游戏界面 -->
    <div id='gameDiv' style='position: absolute;margin:auto; top: 0px; left: 0px;right:0px;display:none;background-color:black'>

        <div id='entering' style=''>

        </div>
    </div>
    <!-- log -->
    <div id='debug-log' onclick='closedebug()' style='display:none;text-align:left;overflow:scroll;opacity: 0.9;z-index:999;width:100%;height:100%;position:absolute;top:0px;left:0px;background-color:black;color:green'>

    </div>
    <!-- 充值界面 -->
    <div id='recharge' style='background-color:#343232;color: #f1dc05;display:none;position:absolute;margin:auto;top:0px;left:0px;right:0px;z-index:999;flex-direction:column;flex:1'>
        <div id="title" class="topbg" style="top:0px;"></div>
        <div id="items" align="center" style="height:80%;overflow: scroll;position: relative;">
        </div>
        <div onclick="closePayWindow()" id="" class="titlebg" style="bottom:0px;position:absolute"><span style='position:relative;margin:auto;top:35%'>返回</span></div>

        <div id="loading" style='pointer-events: none;position:absolute;margin:auto;top:0px;left:0px;bottom:0px;right:0px;display:none'>
            <svg viewBox="0 0 120 120" style='width:50px;height:50px;position:absolute;margin:auto;top:0;left:0;bottom:0;right:0'
                version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <g id="circle" class="g-circles g-circles--v3">
                    <circle id="12" transform="translate(35, 16.698730) rotate(-35) translate(-35, -16.698730) " cx="35"
                        cy="16.6987298" r="10"></circle>
                    <circle id="11" transform="translate(16.698730, 35) rotate(-60) translate(-16.698730, -35) " cx="16.6987298"
                        cy="35" r="10"></circle>
                    <circle id="10" transform="translate(10, 60) rotate(-90) translate(-10, -60) " cx="10" cy="60" r="10"></circle>
                    <circle id="9" transform="translate(16.698730, 85) rotate(-120) translate(-16.698730, -85) " cx="16.6987298"
                        cy="85" r="10"></circle>
                    <circle id="8" transform="translate(35, 103.301270) rotate(-150) translate(-35, -103.301270) " cx="35"
                        cy="103.30127" r="10"></circle>
                    <circle id="7" cx="60" cy="110" r="10"></circle>
                    <circle id="6" transform="translate(85, 103.301270) rotate(-30) translate(-85, -103.301270) " cx="85"
                        cy="103.30127" r="10"></circle>
                    <circle id="5" transform="translate(103.301270, 85) rotate(-60) translate(-103.301270, -85) " cx="103.30127"
                        cy="85" r="10"></circle>
                    <circle id="4" transform="translate(110, 60) rotate(-90) translate(-110, -60) " cx="110" cy="60" r="10"></circle>
                    <circle id="3" transform="translate(103.301270, 35) rotate(-120) translate(-103.301270, -35) " cx="103.30127"
                        cy="35" r="10"></circle>
                    <circle id="2" transform="translate(85, 16.698730) rotate(-150) translate(-85, -16.698730) " cx="85"
                        cy="16.6987298" r="10"></circle>
                    <circle id="1" cx="60" cy="10" r="10"></circle>
                </g>
            </svg>
        </div>

    </div>
</body>

</html>
