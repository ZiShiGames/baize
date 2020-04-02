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
    <?php if ($game->game_father_id == 20002): ?>
        <meta name="screen-orientation" content="landscape" />
        <meta name="tencent-x5-page-direction" content="landscape" />
    <?php else: ?>
        <meta name="screen-orientation" content="portrait" />
        <meta name="tencent-x5-page-direction" content="portrait" />
    <?php endif;?>


	<meta name="browsermode" content="application"/>
	<meta name="full-screen" content="yes" />

	<!-- <script src='http://h5sdk.zytxgame.com/js/g2b_loader.js'></script> -->
    <link rel="stylesheet" href="/css/style.css">
    <script src='/js/g2b_loader.js?v=1'></script>
	<script src='//h5.188wan.com/sdk/load/open-sdk'></script>
    <script src="/js/PV_count/h_<?php echo $game->game_father_id ?>.js" charset="utf-8"></script>
	<style type="text/css">
		html{height:100%;background-color:black;}

		.cgr{color:#5aff00;}
		.cy{color:#fff000;}
		.cw{color:white;}
		.bs1{
			text-shadow: -1px -1px 1px black, 1px 1px 1px black,1px -1px 1px black,-1px 1px 1px black;
		}
		.fs1{font-size:18px;}
		.fs2{font-size:30px;}
		.fs3{font-size:20px;}
		.fl{float:left;}
		.clearb{clear:both;}
		.ma{margin:auto;}
		.bb{-webkit-box-sizing:border-box;}
		.bd{
			background:rgba(0,0,0,0.75);
			border:2px solid rgba(0,0,0,0.75);
			border-radius:15px;
			font-size:15px;
		}
		.bd:before{
			content:'';
			display:block;
			width:100%;
			height:100%;
			border:5px solid #dca53c;
			position:absolute;
			pointer-events:none;
			top:0px;left:0px;
			border-radius:15px;
			box-sizing:border-box;
			-webkit-box-sizing:border-box;
		}
		.tc{text-align:center;}
		.pa{position:absolute;left:0px;top:0px;width:100%;height:100%;z-index:2;}
		.pr{position:relative;}
		.text{
			font-size:20px;
			font-family:"SourceHanSansCN-Normal";
			color:white;
			position:absolute;
			top:120px;
			width:200%;
			left:-50%;
			text-align:center;
		}
		.highLight{
			-webkit-box-shadow: 0px 0px 50px #2f9aff;
		}
		.highLight:after{
			content: '';
			width: 42px;
			height: 43px;
			position:absolute;
			top:60px;
			left:60px;
		}

	</style>
	<script type="text/javascript">

        // var wanSdk = new wanGame();
        // window.onresize(function(){
        //     //code
        //     console.log('window resize');

        //     let _frameWidth = document.documentElement.clientWidth||window.innerWidth;
        //     let _frameHeight = document.documentElement.clientHeight||window.innerHeight;
        //     document.getElementById('gameDiv').style.width = (_frameWidth>_frameHeight?_frameHeight*0.6:_frameWidth)+'px';
        //     document.getElementById('gameDiv').style.height = _frameHeight+'px';
        // }):
		// wanOpenSdk.ready(function() {

        // });
        // testonresize(){
        //     console.log('test window resize');

        //     let _frameWidth = document.documentElement.clientWidth||window.innerWidth;
        //     let _frameHeight = document.documentElement.clientHeight||window.innerHeight;
        //     document.getElementById('gameDiv').style.width = (_frameWidth>_frameHeight?_frameHeight*0.6:_frameWidth)+'px';
        //     document.getElementById('gameDiv').style.height = _frameHeight+'px';
        // }
		// wanSdk.ready(function(){
		// 	// 后续所有接口都应该在ready中调用
		// 	wanSdk.gameReady({process:false});
		// });
		var gameAppId = <?php echo $appId ?>;
		var infos = {  "<?php echo $passId ?>":"{\"passType\":\"QHSER\"}"}
		var passIds = Object.keys(infos);
		var passId = passIds[0];
		var datas = JSON.parse(infos[passId]);
		var passType = datas.passType;
		var checkcookie;
		var userAuth = {};
		var cookiePath = '';

		var frameWidth ;
		var frameHeight ;
		var passTypeFuncMap;
		function closePayWindow(){
			document.getElementById('items').innerHTML='';
			document.getElementById('recharge').style.display = 'none';
		}

		function clear(){
			window.scrollTo(0, 1);
			var viewportmeta = document.querySelector('meta[name="viewport"]');
			var ua = navigator.userAgent;
		    if (viewportmeta) {
				if(ua.indexOf("iPhone")>-1 || ua.indexOf("iPad")>-1){
					viewportmeta.content = 'width=device-width, initial-scale=1.0, width=device-width, minimum-scale=1.0, maximum-scale=1.0';
				}
		    }
		}
		window.addEventListener('load', function(){
			//设置背景操作
			document.getElementById('loader').style.display = 'block';
			setTimeout(function(){
				debugtolog('wwww'+document.documentElement.clientWidth+'hhhh'+document.documentElement.clientHeight +"   innerW"+window.innerWidth+"   innerH"+window.innerHeight);
				showPreScreen();
				switch(passId){
                    case "tt":
                        console.log("tt");
                        g2b.loadScript("http://res.tth5.com/SuperSDK/JS/supersdk.min.js?v=20160907", function() {
                            superSDK.ready(
                                function () {

                                    superSDK.doLogin().success(function (status, userdata) {
                                        console.log("user " + JSON.stringify(userdata));
                                        userdata['appId'] = gameAppId;
                                        userdata['passId'] = passId;
                                        g2b.login(userdata);
                                    })
                                    .error(function (status, msg){

                                    });
                                }
                            );
                        });
                        break;
                    case "mtyx":
                        g2b.loadScript("http://h5.91wan.com/js/7wanwansdk.js", function() {

                            quwanwansdk.getLoginInfo({
                        		game_id: 2530,
                        		server_id: 0,
                        		callFunc: function (response) {
                        			//获取平台相关信息
                        			// response.uid			//（平台uid）
                        			// response.user_name	//（平台user_name ）
                        			// response.img			//（平台img）
                        			// response.nick_name	//（平台nick_name）
                        			// response.sid			//（平台sid ）
                                    response['appId'] = gameAppId;
                                    response['passId'] = passId;
                                    g2b.login(response);
                        		}
                        	});
                        });
                        break;
                    case "qqb":
                        var sdk = window.sdk = window.browser.x5gameplayer;
                            //配置SDK属性,向SDK注册登录的回调
                            sdk.config && sdk.config({
                                loginCallBack: loginCallBack
                            });

                        break;
					default:
					//	document.getElementById('loader').style.display = 'none';
						var param = g2b.getParameters();
                        console.log("gameAppId " + gameAppId + " passId " + passId) ;
						param['appId'] = gameAppId;
						param['passId'] = passId;
						g2b.login(param);
						break;
				}

			},1);

			document.body.style.background = ''
		}, true);

        function loginCallBack(rspObj) {
            console.log("loginCallBack");
            //登录成功
            if (rspObj && rspObj.result === 0) {

            }
        }

		function loadScript(url, callback) {
			var script = document.createElement("script");
			script.type = "text/javascript";

			script.onload = function() {
				callback();
			};
			script.onerror = function() {
				script.parentNode.removeChild(script);
				setTimeout(function() {
					loadScript(url, callback);
				}, 1000);
			};
			script.src = url;
			document.getElementsByTagName("head")[0].appendChild(script);
		}

		function showPreScreen(){
            console.log("showPreScreen");
			//适配 存储宽高  2016年9月19日17:00:03 改为放到load事件内获取
			frameWidth = document.documentElement.clientWidth||window.innerWidth;
			frameHeight = document.documentElement.clientHeight||window.innerHeight;
			debugtolog('pre  wwww'+document.documentElement.clientWidth+'hhhh'+document.documentElement.clientHeight +"   innerW"+window.innerWidth+"   innerH"+window.innerHeight);
			window.scrollTo(0, 1);
			var orien = getOrientation();
			var w,h;
			h = frameHeight;
			w = frameWidth;

			//简单适配 游戏div和充值div
			document.getElementById('gameDiv').style.width = (frameWidth>frameHeight?frameHeight*0.6:frameWidth)+'px';
			document.getElementById('gameDiv').style.height = frameHeight+'px';
			document.getElementById('recharge').style.height = frameHeight+'px';
			document.getElementById('recharge').style.width = (frameWidth>frameHeight?frameHeight*0.6:frameWidth)+'px';
			var ratio = h / w;
			var maxRatio = 1.77917;
			var minRatio = 1.7;
			var _uniScale = Math.min(h/800, w/480);
			var _topY,_leftX;

			var _scaleY = h / 800;
			var _scaleX = w / 480;
				_scaleY = _uniScale;
				_scaleX = _uniScale;
				_topY = (h - _uniScale * 800) / 2;
				_leftX = (w - _uniScale * 480) / 2;



			var style = document.createElement("style");
			style.type = "text/css";
			style.innerHTML = '#gbox{left:'+_leftX+'px;top:'+_topY+'px;transform-origin:0px 0px;-ms-transform-origin:0px 0px;-webkit-transform-origin:0px 0px; -moz-transform-origin:0px 0px;-o-transform-origin:0px 0px;-webkit-transform:scale('+_scaleX+','+_scaleY+');-o-transform:scale('+_scaleX+','+_scaleY+');-moz-transform:scale('+_scaleX+','+_scaleY+');}\n';
			document.getElementsByTagName("head")[0].appendChild(style);


			var gbox = document.getElementById("gbox");
			if(orien != 'portrait')
				gbox.style.display = 'none';
			else
				gbox.style.display = 'block';
		}

        function getOrientation(){
            var ua = navigator.userAgent;
            if(ua.match(/qzone/i))
                return 'portrait';
            var orien;
            switch(window.orientation) {
                case 90: case -90:
                  orien = 'landscape';
                break;
                default:
                  orien = 'portrait';
              }
            return orien;
        }
		function debugtolog(log){
			var debug_log = document.getElementById('debug-log');
			var logs = debug_log.innerHTML;
			logs += ('<br><br>'+ log+"  "+new Date());
			debug_log.innerHTML = logs;
		}
		function closedebug(){
			var debug_log = document.getElementById('debug-log');
			debug_log.style.display = 'none';
		}
		function showdebug(){
			var debug_log = document.getElementById('debug-log');
			debug_log.style.display = 'block';
		}

		function jumpTo(url){

			window.location.href=url;
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
			var exp=new Date();
			exp.setTime(exp.getTime()-1);
			var cval=getCookie(name);

			setCookie(name,cval,exp.toGMTString(),cookiePath);
	    }


	</script>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>登录</title>
</head>
<!-- onresize='testonresize()' -->
<body scroll='no' style='background-color:white'  >
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
			 <svg viewBox="0 0 120 120" style='width:50px;height:50px;position:absolute;margin:auto;top:0;left:0;bottom:0;right:0' version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
      <g id="circle" class="g-circles g-circles--v3">
          <circle id="12" transform="translate(35, 16.698730) rotate(-35) translate(-35, -16.698730) " cx="35" cy="16.6987298" r="10"></circle>
          <circle id="11" transform="translate(16.698730, 35) rotate(-60) translate(-16.698730, -35) " cx="16.6987298" cy="35" r="10"></circle>
          <circle id="10" transform="translate(10, 60) rotate(-90) translate(-10, -60) " cx="10" cy="60" r="10"></circle>
          <circle id="9" transform="translate(16.698730, 85) rotate(-120) translate(-16.698730, -85) " cx="16.6987298" cy="85" r="10"></circle>
          <circle id="8" transform="translate(35, 103.301270) rotate(-150) translate(-35, -103.301270) " cx="35" cy="103.30127" r="10"></circle>
          <circle id="7" cx="60" cy="110" r="10"></circle>
          <circle id="6" transform="translate(85, 103.301270) rotate(-30) translate(-85, -103.301270) " cx="85" cy="103.30127" r="10"></circle>
          <circle id="5" transform="translate(103.301270, 85) rotate(-60) translate(-103.301270, -85) " cx="103.30127" cy="85" r="10"></circle>
          <circle id="4" transform="translate(110, 60) rotate(-90) translate(-110, -60) " cx="110" cy="60" r="10"></circle>
          <circle id="3" transform="translate(103.301270, 35) rotate(-120) translate(-103.301270, -35) " cx="103.30127" cy="35" r="10"></circle>
          <circle id="2" transform="translate(85, 16.698730) rotate(-150) translate(-85, -16.698730) " cx="85" cy="16.6987298" r="10"></circle>
          <circle id="1" cx="60" cy="10" r="10"></circle>
      </g>
  </svg>
		  </div>
	<div id='getValue' style='display:none' value = ""></div>
	<script type='text/javascript'>
	</script>

	  <div style='width:10px;height:10px;position:absolute;top:0px;left:0px;z-index:999' onclick='showdebug()'>

	</div>
	<!-- 游戏界面 -->
	<div id='gameDiv' style='position: absolute;margin:auto; top: 0px; left: 0px;right:0px;display:none;background-color:white'>

	<div id='entering' style=''>

	</div>
	</div>
	<!-- log -->
	<div id='debug-log' onclick='closedebug()' style='display:none;text-align:left;overflow:scroll;opacity: 0.9;z-index:999;width:100%;height:100%;position:absolute;top:0px;left:0px;background-color:black;color:green'>

	</div>
	<!-- 充值界面 -->
	<div id='recharge' style='background-color:#343232;color: #f1dc05;display:none;position:absolute;margin:auto;top:0px;left:0px;right:0px;z-index:999;flex-direction:column;flex:1'>
		 <div id="title" class="topbg"  style="top:0px;"></div>
   		 <div id="items" align="center" style="height:80%;overflow: scroll;position: relative;" >
    	 </div>
    <div onclick="closePayWindow()" id="" class="titlebg"  style="bottom:0px;position:absolute"><span style='position:relative;margin:auto;top:35%'>返回</span></div>

	<div id="loading" style='pointer-events: none;position:absolute;margin:auto;top:0px;left:0px;bottom:0px;right:0px;display:none'>
			 <svg viewBox="0 0 120 120" style='width:50px;height:50px;position:absolute;margin:auto;top:0;left:0;bottom:0;right:0' version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
      <g id="circle" class="g-circles g-circles--v3">
          <circle id="12" transform="translate(35, 16.698730) rotate(-35) translate(-35, -16.698730) " cx="35" cy="16.6987298" r="10"></circle>
          <circle id="11" transform="translate(16.698730, 35) rotate(-60) translate(-16.698730, -35) " cx="16.6987298" cy="35" r="10"></circle>
          <circle id="10" transform="translate(10, 60) rotate(-90) translate(-10, -60) " cx="10" cy="60" r="10"></circle>
          <circle id="9" transform="translate(16.698730, 85) rotate(-120) translate(-16.698730, -85) " cx="16.6987298" cy="85" r="10"></circle>
          <circle id="8" transform="translate(35, 103.301270) rotate(-150) translate(-35, -103.301270) " cx="35" cy="103.30127" r="10"></circle>
          <circle id="7" cx="60" cy="110" r="10"></circle>
          <circle id="6" transform="translate(85, 103.301270) rotate(-30) translate(-85, -103.301270) " cx="85" cy="103.30127" r="10"></circle>
          <circle id="5" transform="translate(103.301270, 85) rotate(-60) translate(-103.301270, -85) " cx="103.30127" cy="85" r="10"></circle>
          <circle id="4" transform="translate(110, 60) rotate(-90) translate(-110, -60) " cx="110" cy="60" r="10"></circle>
          <circle id="3" transform="translate(103.301270, 35) rotate(-120) translate(-103.301270, -35) " cx="103.30127" cy="35" r="10"></circle>
          <circle id="2" transform="translate(85, 16.698730) rotate(-150) translate(-85, -16.698730) " cx="85" cy="16.6987298" r="10"></circle>
          <circle id="1" cx="60" cy="10" r="10"></circle>
      </g>
  </svg>
		  </div>

	</div>
</body>
</html>
