<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<html style="height: 100%;">
<head>
<link href="https://js.2460.xileyougame.com/js/admin_js/bootstrap.min.css" rel="stylesheet">
<meta id="viewport" name="viewport" content="target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0" />
<script>
function login() {
    var user = $("#user").val();
    var password = $("#password").val();
    if(user == "" || password==""){
        alert("用户名密码不能为空！");
        return;
    }

    $.post( "/index.php/Mengsansheng_admin_backstage/admin_login", { user: user, password: password },null,"json")
      .done(function( data ) {
        //   console.log("data " + data);
          if(data.c == 0 ){
              window.location.href="/index.php/Mengsansheng_admin_backstage/back_stage_page";
          } else {
              alert('password error');
          }
      });
}
</script>
</head>

<body style="height: 100%;background:#139bd8">
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
    <div class="container">
        <div align="center">
            <h1 style="color:#fff">2460聚合平台</h1>
            <br>
        </div>

        <div class="row" align="center">
            <div class="col-md-3">
            </div>
            <div class="jumbotron col-md-6">
                <input type="text" class="form-control" placeholder="用户名" id="user">
                <input type="password" class="form-control" placeholder="密码" id="password">
                <br/>
                <input class="btn btn-default" type="button" value="登录" onclick="login()">
            </div>
        </div>
    </div>
</body>
<script src="https://js.2460.xileyougame.com/js/admin_js/jquery.min.js"></script>
<script src="https://js.2460.xileyougame.com/js/admin_js/bootstrap.min.js"></script>
</html>
