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
<script type="text/javascript">

    g2f.init(1001,function () {
        console.log("init done");
    });
    function charge() {
        console.log("charge");


        var addOrder = function () {
            console.log("addOrder");
             return { 
             orderNo:'11', 
             ext:'',
             //需要透传的参数 充值后的回调会回传 
             appId:1000, 
             openId:'33', 
             openKey:'112', 
             appUserName:'昵称', 
             subject:'元宝',
             actor_id:"1111"
             //道具名     }
        }

        var callback = function (result) {
            console.log("pay callback " + result);
        }

        var payItems = [{     id:1,     itemName:'10钻石',     icon:'/passa/img/gold.png',     desc:'10元=100钻石',     amount:100 },{     id:2,     itemName:'100钻石',     icon:'/passa/img/gold.png',     desc:'10元=100钻石',     amount:200 },{     id:3,     itemName:'200钻石',     icon:'/passa/img/gold.png',     desc:'10元=100钻石',     amount:300 },{     id:4,     itemName:'500钻石',     icon:'/passa/img/gold.png',     desc:'10元=100钻石',     amount:400 },{     id:5,     itemName:'1000钻石',     icon:'/passa/img/gold.png',     desc:'10元=100钻石',     amount:500 },{     id:6,     itemName:'2000钻石',     icon:'/passa/img/gold.png',     desc:'10元=100钻石',     amount:600 }]

        g2f.showRecharge(payItems, addOrder, callback);
    }
</script>
</head>
<body scroll='no' style='background-color:white'>
<input type="button" name="" value="充值" onclick="charge()">
</body>
</html>
