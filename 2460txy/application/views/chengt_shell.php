<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style='background-color:black;'>

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

    <script src="/js/h.js" charset="utf-8"></script>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>通行证管理系统</title>
</head>
<script src="//cdn.bootcss.com/jquery/3.1.1/jquery.min.js"></script>
<body style="padding: 0px 0px; margin: 0px; background: #666666;"  class='body'>
<iframe style="position: absolute; top: 0px; left: 0px;" frameborder="no" border="0px" marginwidth="0px" marginheight="0px" scrolling="auto" src="<?php echo $url ?>"  width="100%" height="100%"></iframe>
        <style type="text/css">
        .side-button {
            display: none;
            position: absolute;
            height: 40px;
            width: 40px;
            z-index: 10;
            background: #eee;
            color: #666;
            line-height: 40px;
            font-size: 24px;
            text-align: center;
            border-radius: 20px;
        }

        .body {
            background: #eee;
            overflow: hidden;
            z-index: -2;
        }
        .under-sidebar {
            position: absolute;
            z-index: 8;
            display: none;
        }
        .sidebar {
            display: none;
            position: absolute;
            width: 90px;
            height: 40px;
            background: #fff;
            overflow-y: scroll;
            z-index: 9;
            right: 0;
            line-height: 40px;
            border-radius: 20px;
            padding: 0 20px;
        }
        .sidebar div {
            padding: 15px;
        }
        .close-button {
            position: absolute;
            z-index: 9;
            display: none;
        }
        .headimg {
            float: left;
        }
        .nickname {
            float: left;
            line-height: 50px;
            font-size: 18px;
        }
        .headimg img {
            width: 50px;
            border-radius: 25px;
        }
        .qr-area {
            clear: both;
            background-color: #eee;
            text-align: center;
        }
        .qr-area span{
            display: block;
            padding-bottom: 10px;
        }
        .qr-area img {
            width: 70%;
        }
        .h1 {
            font-size: 22px;
        }
        .h4 {
            font-size: 16px;
            color: #1CB6F3;
        }
        .gifts div {
            padding: 0;
        }
        .gifts ul {
            padding: 0;
        }
        .gifts ul li {
            list-style: none;
        }
        .gifts ul li div {
            padding: 4px 0 ;
        }
        .gift-left {
            text-align: right;
            font-size: 12px;
            color: #999;
        }
        .description {
            clear: both;
            color: #999;
            font-size: 12px;
        }
        .title {
            float: left;
            width: 80%
        }
        .git-button {
            float: right;
            width: 20%;
            padding: 0px 3px;
            border-radius: 4px;
            background: #1CB6F3;
            color: white;
            text-align: center;
        }
        .red-point {
            color: red;
            font-size: 8px;
        }
        .be-user {
            clear: both;
            display: none;
        }
        .be-user a {
            color: black;
            text-decoration: none;
        }
        a {
            color: #000;
            text-decoration: none;
        }
        </style>
        <!-- 按钮 -->
        <!--<img src="/img/gift-button.png" class="side-button" width="90">-->
        <div class="side-button">≡</div>

        <img src="/img/cebianbg.png" height="88" class="close-button" />

        <div class="under-sidebar"></div>
        <!-- 边栏列表 -->
        <div class="sidebar">
            <!-- <span><a href="/index.php/AppMain">刷新</a></span> &nbsp; -->
            <span><a onclick ='logout()'>注销账号</a></span>
        </div>

    <script type="text/javascript">
    (function(window) {
        top.location.href = 'http://h5sdk.zytxgame.com/index.php/fake/https_jump_playpage?url=<?php echo urlencode($url)   ?>';
        // top.location.href = 'http://h5sdk.zytxgame.com/index.php/fake/https_jump_playpage?url=<?php echo  $url  ?>&appid=<?php echo $game_id ?>&passId=<?php echo $passId ?>&game_father_id=<?php echo $game_father_id?>';
    })(window);
    var frameWidth = window.location.getParameter('frameWidth') || window.innerWidth;
    var frameHeight = window.location.getParameter('frameHeight') || window.innerHeight;

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

    function updateOrientation() {
        var orien = getOrientation();
        clear();
        if (orien == 'portrait') {
            removeHint();
        }
        setTimeout(function() {

            orien = getOrientation();
            if (orien != 'portrait') {
                // showHint();

                removeHint();
                showPreScreen();

            } else {
                removeHint();
                showPreScreen();
            }
        }, 1000);
    }
    var getDataXHR = function(url, cb, param) {
        var param = param || {};
        var type = param.type || 'get';
        var data = param.data || null;
        try {
            var xhr = new XMLHttpRequest();
            xhr.open(type, url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.responseText == 'error') {
                        toastMsg("请求返回error");
                        //console.error('请求'+url+'返回error');
                        return;
                    }
                    cb && cb(xhr.responseText);
                }
            };
            xhr.send(data);
        } catch (e) {
            console.error('xhr出错', e);
            return false;
        }
    };
    //公告
    function showAnnounce() {
        var appid = window.location.getParameter('appId');
        var ann = document.getElementById('announce');
        ann.style.display = 'block';
    }

    function closeAnn() {
        var ann = document.getElementById('announce');
        ann.style.display = 'none';
    }


    function clear() {
        window.scrollTo(0, 1);
        var viewportmeta = document.querySelector('meta[name="viewport"]');
        var ua = navigator.userAgent;
        if (viewportmeta) {
            if (ua.indexOf("iPhone") > -1 || ua.indexOf("iPad") > -1) {
                viewportmeta.content = 'width=device-width, initial-scale=1.0, width=device-width, minimum-scale=1.0, maximum-scale=1.0';
            }
        }
    }

    function showHint() {
        removeHint();

        var hintHtml = "<div style='margin:15% auto 6% auto;background:url(http://opcdn.h5game.cn/nbsg/img/pass/portraitHint.png);background-size:100% 100%;width:" + (window.innerHeight / 640 * 324) + "px;height:" + window.innerHeight / 640 *
            204 + "px;'></div>";
        hintHtml += "<div style='text-align:center;color:#b0b0b0;font-size:20px;'>竖屏体验更佳</div>";

        var hintDiv = document.createElement("div");
        hintDiv.id = 'screenHint';
        hintDiv.style.position = 'absolute';
        hintDiv.style.left = '0px';
        hintDiv.style.top = '0px';
        hintDiv.style.width = window.innerWidth + 'px';
        hintDiv.style.height = window.innerHeight + 'px';
        hintDiv.style.zIndex = 999;
        hintDiv.style.backgroundColor = '#e0e0e0';

        hintDiv.innerHTML = hintHtml;
        document.body.appendChild(hintDiv);
    }

    function removeHint() {
        var hint = document.getElementById('screenHint');
        if (hint)
            document.body.removeChild(hint);
    }

    var isMQQ = navigator.userAgent.indexOf("MQQBrowser") > -1;
    //通信
    var postMessage = function(msg, d) {
        var data = {};
        data.identify = 'g2460';
        data.msg = msg;
        if (d)
            data.data = d;
        try {
            parent.window.postMessage(data, '*');
        } catch (e) {
            console.log(e);
        };
    }
    window.addEventListener('orientationchange', updateOrientation, false);
    window.addEventListener('resize', showPreScreen, false);
    window.addEventListener('load', function() {
        updateOrientation();
        showPreScreen();

        var openId = window.location.getParameter('openId');
        var appId = window.location.getParameter('appId')
        var shareInfo = {
            desc: "兄弟再聚，沙城争霸，无兄弟，不传奇，寻昔日兄弟，战热血沙城。",
            imgUrl: "h5.xileyougame.com/index.php/game/redirect/20",
            title: "龙城霸业",
            openId: openId,
            channel: appId,
        }
        postMessage('special_0', shareInfo);
        tagEvent(function(tag) {
            var items = document.getElementsByClassName("items");
            var id = tag.getAttribute('id');
            for (var i = 0; i < items.length; i++) {
                items[i].classList.add("hide");
                if (items[i].getAttribute("id") == "game" + id) {
                    items[i].classList.remove("hide");
                }
            }
            document.getElementById("scrollwrap").scrollTop = 0;
        });
    }, true);
    //页签事件
    function tagEvent(cb) {
        var tags = document.getElementsByClassName("tag");
        for (var i = 0; i < tags.length; i++) {
            tags[i].onclick = function(e) {
                document.getElementsByClassName("tagc")[0].classList.remove("tagc");
                this.classList.add("tagc");
                cb && cb(this);
            };
        }
    }

    function showPreScreen() {
        window.scrollTo(0, 1);
        var orien = getOrientation();
        var _scaleY = frameHeight / 800;
        var _scaleX = frameWidth / 480;

        var style = document.createElement("style");
        style.type = "text/css";
        style.innerHTML = '#gbox{transform-origin:0px 0px;-ms-transform-origin:0px 0px;-webkit-transform-origin:0px 0px; -moz-transform-origin:0px 0px;-o-transform-origin:0px 0px;-webkit-transform:scale(' + _scaleX + ',' + _scaleY +
            ');-o-transform:scale(' + _scaleX + ',' + _scaleY + ');-moz-transform:scale(' + _scaleX + ',' + _scaleY + ');}';
        document.getElementsByTagName("head")[0].appendChild(style);
        var gbox = document.getElementById("gbox");
        if (orien != 'portrait')
            gbox.style.display = 'none';
        else
            gbox.style.display = 'block';
    }

    function showAll() {
        var all = document.getElementById('all');
        all.style.display = 'block';
    }

    function closeAll() {
        var all = document.getElementById('all');
        all.style.display = 'none';
    }

    function jumpTo(st, url,sid) {
        if (st == 3) {

            closeAll();
            showAnnounce();
            // alert("该服务器维护中");
            return;
        }

        _hmt.push(['_trackEvent', "game_event", "login", "url", url]);
        if(sid ==8003){
            closeAll();

            showAnnounce();

            $(".anexit").click(function(){
                closeAll();
                window.location.href = url;
            });
        }else {
            setTimeout(function() {
                window.location.href = url;
            }, 0);
        }

    }

            </script>




</body>

</html>
