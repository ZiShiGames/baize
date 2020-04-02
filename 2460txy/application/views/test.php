<?php
defined('BASEPATH') or exit('No direct script access allowed');

// $_PATH = $_SERVER['DOCUMENT_ROOT'].'/lib';
// require_once "$_PATH/wxpay/WxPay.Config.php";
// $WxPayConfig = new WxPayConfig();
// $WxAppid = $WxPayConfig->GetAppId()

?>

<html style="height: 100%;">
<head>
    <meta id="viewport" name="viewport" content="target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0" />
<!-- <link href="//cdn.bootcss.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet"> -->
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/css/admin.css" rel="stylesheet">
</head>

<body>
<h1>fss2f</h1>
<button  onclick="pay()" >click me!</button>
</body>
<script>
    window.onerror = function (msg, url, lineNo, columnNo, error) {
    var string = msg.toLowerCase();
    var substring = "script error";
    if (string.indexOf(substring) > -1){
        alert('Script Error: See Browser Console for Detail');
    } else {
        var message = [
            'Message: ' + msg,
            'URL: ' + url,
            'Line: ' + lineNo,
            'Column: ' + columnNo,
            'Error object: ' + JSON.stringify(error)
        ].join(' - ');
        alert(message);
    }
    return false;
};
    var money = 100;
    var order_id = "sdfdsfagsgsg";
    var subject = 'sdfsdfdsf';
function onBridgeReady(){
    getDataXHR('/index.php/Wxpay/wxpay?money='+money+'&order_id='+order_id+'&subject='+subject,function(res){
        // alert(JSON.stringify(WeixinJSBridge));
        console.log(res)
        WeixinJSBridge.invoke('getBrandWCPayRequest', {
         "appId":res.d.appid,     //公众号名称，由商户传入
         "timeStamp":"<?php echo time();?>",         //时间戳，自1970年以来的秒数
         "nonceStr":res.d.nonce_str, //随机串
         "package":"prepay_id="+res.d.prepay_id,
         "signType":"MD5",         //微信签名方式：
         "paySign":res.d.sign //微信签名
      },
      function(res){
          alert(JSON.stringify(res));
          WeixinJSBridge.log(res);
        if(res.err_msg == "get_brand_wcpay_request:ok" ){
        // 使用以上方式判断前端返回,微信团队郑重提示：
        //res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
        }
        });

    // window.top.location.href = '/index.php/wxpay/showQrCode?data='+res.d.code_url;
    })

}
function pay(){
    console.log(1);
    // try {
        // WeixinJSBridge.on('menu:share:appmessage', function(argv){ alert("发送给好友"); });
        onBridgeReady();
    // } catch (error) {
        // alert(error);
    // }

//     if (typeof WeixinJSBridge == "undefined"){
//     if( document.addEventListener ){
//        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
//     }else if (document.attachEvent){
//        document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
//        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
//    }
//     }else{
//     onBridgeReady();
//     }
}

function getDataXHR (url, cb, param, contenttype) {
        var param = param || {};
        var type = param.type || "get";
        var data = param.data || null;
        try {
            var xhr = new XMLHttpRequest();
            xhr.open(type, url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    var responseData = JSON.parse(xhr.responseText);
                    if (responseData.c < 0) {
                        toastMsg(responseData.m);
                        return
                    }
                    if (xhr.responseText == "error") {
                        alert("请求" + url + "返回error");
                        return
                    }
                    cb && cb(responseData)
                }
            };
            if (contenttype) {
                try {
                    xhr.setRequestHeader("Content-Type", contenttype)
                } catch (e) {
                    alert(e)
                }
            }
            xhr.send(data)
        } catch (e) {
            console.error("xhr出错", e);
            return false
        }
    };
</script>
</html>
