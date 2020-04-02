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
     $(document).ready(function(){
        $('.under-sidebar').on('click', function(event){
            sidebarHide();
        })
        $('.under-sidebar').on('ontouchstart', function(event){
            sidebarHide();
        })

    // 按钮控制类 start ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

        //处理边栏的展示
        function sidebarShow()
        {
            $('.side-button').html('﹥');
            $('.sidebar').css('top', ByTouch.button.style.top);
            $('.sidebar').fadeIn(100);
            //$('.close-button').fadeIn(1000);
            var cbLeft = $('.sidebar').width();
            var cbTop = ($('.sidebar').height() - $('.close-button').height() ) / 2;
            //$('.close-button').css({'top': cbTop + 'px', 'left': cbLeft + 'px'});

            $('.under-sidebar').css({'width':$(window).width()+'px', 'height':$(window).height()+'px'});
            $('.under-sidebar').show();
        }
        //处理边栏关闭
        function sidebarHide()
        {
            $('.side-button').html('≡');
            $('.sidebar').fadeOut(200);
            $('.close-button').fadeOut(200);
            $('.under-sidebar').hide();
        }

        //注册

        $('.close-button').on('click', sidebarHide);

        var ByTouch = {

            button : {},
            container : {},
            borderArea : {},
            hided: false,

            init : function (button1, container1)
            {
                ByTouch.button = button1;
                ByTouch.container = container1;
                ByTouch.borderArea = getContainerBorderArea(container1);
                ByTouch.button.addEventListener('touchstart', ByTouch.touchStart, false);
                ByTouch.button.addEventListener('touchend', ByTouch.touchEnd, false);
                //初始化按钮位置
                ByTouch.initButton();
                //注册按钮点击事件
                ByTouch.button.addEventListener('click', function(){
                    //处理边栏的展示
                    if(ByTouch.hided==false){
                        sidebarShow();
                        ByTouch.hided = true;
                    }else {
                        sidebarHide();
                        ByTouch.hided = false;
                    }

                    //隐身
                    //ByTouch.touchEnd();
                });
                // $('.rf').on('click', function() {
                //     location.reload();
                // })
            },

            touchStart : function (e)
            {
                //$('.info').html(ByTouch.button.pageX)
                ByTouch.button.addEventListener('touchmove', ByTouch.touchMove, false);
            },

            touchMove : function (e)
            {

                //防止冒泡
                e.preventDefault();
                var touch = e.changedTouches[0];
                ByTouch.hided = false;

                ByTouch.button.style.left = (touch.pageX - (ByTouch.button.clientWidth/2)) + 'px';
                ByTouch.button.style.top = (touch.pageY - (ByTouch.button.clientWidth/2)) + 'px';
                sidebarHide();
            },

            touchEnd : function (e)
            {
                var touch = e.changedTouches[0];
                ByTouch.button.style.left = (ByTouch.borderArea.right - ByTouch.button.clientWidth) + 'px';
                if(touch.pageY < (ByTouch.button.clientWidth/2)) var pageY = 0;
                if(touch.pageY > ByTouch.borderArea.bottom) var pageY = ByTouch.borderArea.bottom - ByTouch.button.clientWidth;
                ByTouch.button.style.top = pageY + 'px';
                ByTouch.hideHalfBody();
                console.log('touch end.');
            },

            //隐藏半身
            hideHalfBody : function()
            {
                return;
                setTimeout(function(){
                    if(ByTouch.hided == false)
                        ByTouch.button.style.left = (ByTouch.button.offsetLeft + (ByTouch.button.clientWidth/2)) + 'px';
                    ByTouch.hided = true;
                }, 2000);

            },

            //初始化按钮位置
            initButton : function()
            {
                //显示按钮
                ByTouch.button.style.display = 'block';
                //按钮初始位置 x轴 = 容器右边距 - 按钮宽度
                ByTouch.button.style.left = (ByTouch.borderArea.right - ByTouch.button.clientWidth) + 'px';
                //按钮初始位置 y轴 = (容器高度 - 按钮高度) / 2
                ByTouch.button.style.top = (ByTouch.borderArea.bottom - ByTouch.button.clientHeight) / 3 + 'px';
                //执行隐藏半身
                ByTouch.hideHalfBody();
            }

        }

        /**
         * 返回容器边缘位置
         * @param  {object} container1 容器
         * @return {object}            边缘位置对象
         */
        function getContainerBorderArea(container1)
        {
            var top = container1.offsetTop;
            var left = container1.offsetLeft;
            var bottom = top + container1.clientHeight;
            var right = left + container1.clientWidth;

          if(bottom == 0)
            bottom = container1.scrollHeight

            return {top:top, left:left, bottom:bottom, right:right}
        }

    // end +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



        //定义按钮
        var button1 = document.getElementsByClassName('side-button')[0];
        //定义容器
        var container1 = document.getElementsByClassName('body')[0];

        //执行触摸初始化
        ByTouch.init(button1, container1);


            }); // onready 结束
        function logout(){
            myOwnBri.exit();
            // myOwnBri.getNew();
            // window.location.href  ='http://img.2460.xileyougame.com/img/login/yybsdk_lc_login.php';
            window.location.href  ='http://h5sdk.zytxgame.com/index.php/fake/yybapp_jump_login';
        }
            </script>




</body>

</html>
