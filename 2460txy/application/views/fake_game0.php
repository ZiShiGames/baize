<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta id="viewport" name="viewport" content="user-scalable=no,target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0, width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
	<meta name="apple-mobile-web-app-capable" content="yes" />

	<meta name="x5-fullscreen" content="true"/>
	<meta name="x5-page-mode" content="app"/>
	<meta name="tencent-x5-page-direction" content="landscape" />
    <meta name="screen-orientation" content="landscape" />
	<meta name="browsermode" content="application"/>
	<meta name="full-screen" content="yes" />

<script type="text/javascript">
var frameWidth = window.innerWidth;
var frameHeight =  window.innerHeight;
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
            // alert('updateOrientation ' + orien);
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

        window.addEventListener('orientationchange', updateOrientation, false);
        window.addEventListener('resize', showPreScreen, false);
        window.addEventListener('load', function() {
            // alert('load');
            updateOrientation();
            showPreScreen();

            document.getElementById("gameDiv").style.display = "block";
            document.getElementById("gameDiv").style.position = "";
            var gameFrame = createIframe("http://s1.fishing.combunet.com/freeGame/20170527143552/index.html?param=eyJvcGVuS2V5IjoiODU0YTZhYmQxNmFjNjViMTFmMTg3ZDhjY2Q2MjhmODIiLCJwb3J0IjoxMzAwMSwib3BlbklkIjoiMTQ2Mjg5MDUiLCJhcHBJZCI6IjEwNTEiLCJob3N0IjoiMjExLjE1OS4xNTQuNTMiLCJhY2NJZCI6IjE0NjI4OTA1Iiwic2VjcmV0IjoiZmI2ZGNhZGIxMDRkZTE2ZTZkNzRkZTVlMzFkYTM3YzAuTVRRMk1qZzVNRFY4TVRBd01EQXhOVFExZkRsOGZERTBPVFl3T0RReE1EWjgiLCJ1c2VySWQiOjEwMDAwMTU0NSwicGxhdGZvcm0iOjksInJldGNvZGUiOjIwMH0=", "gameFrame", document.getElementById("gameDiv"));
            document.body.removeChild(document.getElementById("gbox"));
        }, true);


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


        var createIframe = function(src, id, tgt) {
            console.log("src " + src);
            var ifm = document.createElement("iframe");
            ifm.scrolling = "no";
            // debugtolog(document.getElementById("recharge").getAttribute("style"));
            ifm.style.width = Math.ceil(frameWidth > frameHeight ? frameHeight * 0.6 : frameWidth) + "px";
            ifm.style.height = (window.frameHeight || window.innerHeight) + "px";
            ifm.style.width = "";
            ifm.style.height = "";
            ifm.style.margin = "auto";
            ifm.style.position = "absolute";
            ifm.style.top = "0";
            ifm.style.left = "0";
            ifm.style.backgroundColor = "white";
            ifm.id = id;
            ifm.frameborder = "no";
            ifm.style.border = "none";
            ifm.border = "0px";
            ifm.style.zIndex = 99;
            ifm.width = "100%";
            ifm.height = "100%";
            (tgt || (document.body)).appendChild(ifm);
            ifm.src = src;
            return ifm
        };



</script>
</head>
<body scroll='no' style='background-color:white'>

    <div id='gameDiv' style="position:absolute">

	</div>

<div id='gbox' style='position:relative;width:480px;height:800px;overflow:hidden;display:none;'>
</div>
</body>
</html>
