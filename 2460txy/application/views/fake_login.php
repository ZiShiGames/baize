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
	<meta name="tencent-x5-page-direction" content="portrait" />
	<meta name="browsermode" content="application"/>
	<meta name="full-screen" content="yes" />
	<meta name="screen-orientation" content="portrait" />
	<!-- <script src='http://h5sdk.zytxgame.com/js/g2b_loader.js'></script> -->
    <script src='/js/jssdk/g2f.js'></script>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>登录</title>
<style media="screen">
</style>
<script src='http://sdkv2.52wan.dkmol.net/www/js/aksdk.js'></script>


</head>
<body scroll='no' style='background-color:white'>
	<script type="text/javascript">
	alert(AKSDK.login(function (status, data) {
		document.getElementById('result').innerHTML = "status = " + status + " " + "data=" + JSON.stringify(data);
	}));


	</script>
<input type="button" name="" value="充值" onclick="charge()">
</body>
</html>
