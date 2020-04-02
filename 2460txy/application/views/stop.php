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
    <meta name="tencent-x5-page-direction" content="portrait" />
    <meta name="browsermode" content="application" />
    <meta name="full-screen" content="yes" />
    <meta name="screen-orientation" content="portrait" />
    <script src="/js/h.js" charset="utf-8"></script>
    <style type="text/css">

        .exit {
            background: url(/passa/img/jzsc/exit.png);
            width: 62px;
            height: 82px;
            position: absolute;
            bottom: 110px;
            right: 10px;
            z-index: 1
        }

        .anexit {
            background: url(/passa/img/jzsc/exit.png);
            width: 62px;
            height: 82px;
            position: absolute;
            bottom: 45px;
            right: 10px;
            z-index: 1
        }

        .mainbg {
            background: url();
            width: 429px;
            height: 83px;
            position: relative;
            margin: auto;
            top: 530px
        }

        .item0 {
            background-image: url(http://h5sdk.zytxgame.com/img/login/itembg.png);
            background-size: 100% 100%;
            width: 250px;
            position: relative;
            margin-top: 5px;
        }

        .item {
            background-image: url(http://h5sdk.zytxgame.com/img/login/itembg.png);
            background-size: 100% 100%;
            width: 250px;
            position: relative;
            margin-top: 5px;
        }

        .item1 {
            background-image: url('/passa/img/jzsc/itemweihu.png');
            background-size: 100% 100%;
            width: 250px;
            position: relative;
            margin-top: 5px;
        }

        .hot {
            width: 60px;
            height: 60px;
            position: absolute;
            right: -5px;
            top: -5px;
            pointer-events: none;
            background: url(/passa/img/frames/hot.png) no-repeat 0 0;
            -webkit-animation: hot 1000ms steps(1) infinite 0s;
        }

        @-webkit-keyframes hot {
            0% {
                background-position: 0 0;
            }
            17% {
                background-position: -60px 0;
            }
            35% {
                background-position: -120px 0;
            }
            52% {
                background-position: -180px 0;
            }
            68% {
                background-position: -240px 0;
            }
            85% {
                background-position: -300px 0;
            }
            100% {
                background-position: -360px 0;
            }
        }

        .new {
            width: 60px;
            height: 60px;
            position: absolute;
            right: -5px;
            top: -5px;
            pointer-events: none;
            background: url(/passa/img/frames/new.png) no-repeat 0 0;
            -webkit-animation: hot 1000ms steps(1) infinite 0s;
        }

        @-webkit-keyframes hot {
            0% {
                background-position: 0 0;
            }
            17% {
                background-position: -60px 0;
            }
            35% {
                background-position: -120px 0;
            }
            52% {
                background-position: -180px 0;
            }
            68% {
                background-position: -240px 0;
            }
            85% {
                background-position: -300px 0;
            }
            100% {
                background-position: -360px 0;
            }
        }

        .enter {
            background: url(http://h5sdk.zytxgame.com/img/login/enter.png);
            width: 281px;
            height: 80px;
        }

        .more {
            background: url(/passa/img/jzsc/more.png);
        }

        .scrollbar {
            background: url(/passa/img/jzsc/scrollbar.png);
        }

        .choosebg {
            background: url(http://h5sdk.zytxgame.com/img/login/feiwanba/sverBg.jpg);
            top: 0px;
            left: 0px
        }

        .tag {
            background: url(http://h5sdk.zytxgame.com/img/login/tag.png);
            width: 140px;
            height: 42px;
            line-height: 41px;
            font-size: 20px;
            color: wheat;
            left: 5px;
            font-weight: bold;
        }

        .tagc {
            background: url(http://h5sdk.zytxgame.com/img/login/tagc.png);
            width: 140px;
            height: 42px;
        }

        .hide {
            display: none;
        }

        .tagbar {
            /*background: rgb(0, 0, 0);*/
            width: 160px;
            height: 520px;
            position: absolute;
            top: 100px;
            left: 15px;
            overflow: scroll;
        }
        /**隐藏滚动条*/

        ::-webkit-scrollbar {
            display: none;
        }

        .announcebg {
            background: url(/passa/img/jzsc/annbg.jpg);
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
        }

        .announce {
            background: url(/passa/img/jzsc/announce.png);
            width: 74px;
            height: 71px;
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

        .ma {
            margin: auto;
        }

        .nE {
            pointer-events: none;
        }

        .aE {
            pointer-events: auto;
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
    </style>

    <script type="text/javascript">
        (function(window) {
            var params = {};
            var loc = window.location;
            var strs = loc.search.substr(1).split("&");
            for (var i = 0; i < strs.length; i++) {
                var name_value = strs[i].split("="),
                    name = decodeURIComponent(name_value[0]),
                    value = decodeURIComponent(name_value[1]);
                if (name_value.length > 2) {
                    for (var j = 2; j < name_value.length; j++) {
                        value += ('=' + name_value[j]);
                    }
                }
                if (name) {
                    params[name] = value;
                }
            }

            loc.getParameter = function(name) {
                return (name in params) ? params[name] : null;
            };
            loc.getParameterNames = function() {
                var names = new Array();
                for (var name in params) {
                    names.push(name);
                }
                return names;
            };
            loc.getParameterValues = function(name) {
                return (name in params) ? params[name] : [];
            };
            loc.getParameterMap = function() {
                return params;
            };
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
                    showHint();
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

        function jumpTo(st, url) {
            if (st == 1) {
                alert("该服务器维护中");
                return;
            }

            _hmt.push(['_trackEvent', "game_event", "login", "url", url]);

            setTimeout(function() {
                window.location.href = url;
            }, 0);
        }
    </script>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>停机维护</title>
</head>

<body style='margin:0px;padding:0px;-webkit-tap-highlight-color: rgba(0,0,0,0);height:0px;'>
    <div id='gbox' style='position:relative;width:480px;height:800px;overflow:hidden;display:none;'>
        <div class='announce' onclick='showAnnounce()' style='position:absolute;top:21px;right:21px'></div>
        <div id="logout" class='logout' onclick='logout()' style='position:absolute;top:100px;right:21px;display:none'></div>
        <div style='' class='tc mainbg'>
        </div>

        <div id='announce' style='display:block' class='announcebg'>
            <div id='anntext' style='position:relative;margin:auto;width:85%;height:80%;padding:5px;top:10%;font-size:20px;overflow:scroll;color:white;'><?php echo  $announce; ?></div>
            <div class='anexit' onclick='closeAnn();'></div>
        </div>
    </div>
</body>

</html>
