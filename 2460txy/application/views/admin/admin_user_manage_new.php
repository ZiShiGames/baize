<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<html style="height: 100%;">
<head>
    <meta id="viewport" name="viewport" content="target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0" />
<!-- <link href="//cdn.bootcss.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet"> -->
<link href="/css/bootstrap.min.css" rel="stylesheet">
<link href="/css/admin.css" rel="stylesheet">
</head>

<body>
    <?php
    // $data = array('name' => '用户管理', );
    // $this->load->view('admin_header',$data);
    ?>
  <div class="row">
    <!-- <div class="col-md-1"> -->

      <?php  // $this->load->view('admin/admin_sub_menu', ['user' => true]); ?>
    <!-- </div> -->

    <div class="col-md-11 myborder">

    <div class="container-fluid">
        <!-- <h4 class="alert-info" style="background-color:#ffffff">游戏管理</h4> -->
    <!-- <h1>首页 <small><a href="/index.php/admin/task_manage">交易</a></small> </h1> -->
    <br>
    <br>
    <div class="alert alert-info " role="alert">搜索</div>
    <div class="row">
        <div class="col-md-6">

            <form class="navbar-form navbar-left" role="search">
                <label>用户渠道ID : </label>
              <div class="form-group">
                <input id="p_uid" type="text" class="form-control" placeholder="">
              </div>
              <!-- <select class="form-control" name="" id="platform">
                  <option value="">无</option>
                  <option value="iqiyi">爱奇艺</option>
                  <option value="allu">奥游</option>
                  <option value="five">5543</option>
                  <option value="hiwan">嗨玩玩</option>
                  <option value="jinb">金榜</option>
                  <option value="nineg">9G</option>
                  <option value="qunhei">群黑</option>
                  <option value="seven">7742</option>
                  <option value="tt">天团</option>
                  <option value="yyb">应用宝</option>
              </select> -->
              <button type="button" class="btn btn-default" onclick="search()">搜索</button>

            </form>
        </div>
        <div class="col-md-6">
            <form class="navbar-form navbar-left" role="search">
              <label>用户昵称 : </label>
                <div class="form-group">
                  <input id="user_nickname" type="text" class="form-control" placeholder="">
                </div>
                <button type="button" class="btn btn-default" onclick="search()">搜索</button>
            </form>
        </div>
        <div class="col-md-6">
            <form class="navbar-form navbar-left" role="search">
              <label>2460 平台ID : </label>
                <div class="form-group">
                  <input id="user_id" type="text" class="form-control" placeholder="">
                </div>
                <button type="button" class="btn btn-default" onclick="search()">搜索</button>
            </form>
        </div>
    </div>
    <div class="container">

        <div class="row">
        </div>
    </div>
    <!-- <div class="alert alert-info" role="alert"></div> -->

    </div>

    <div class="container">
        <div class="alert alert-info" role="alert">用户信息:</div>

        <div class="row">
            <div class="" id="user_row">

            </div>
        </div>
        <div class="alert alert-info" role="alert">创角信息:</div>

        <div class="row">
            <div class="" id="create_role_row">

            </div>
        </div>
        <div class="alert alert-info" role="alert">订单信息:</div>

        <div class="row">
            <div class="" id="order_row">

            </div>
        </div>
        <div class="alert alert-info" role="alert">登录信息:</div>

        <div class="row">
            <div class="" id="login_row">

            </div>
        </div>
    </div>

  </div>
 </div>
</body>
<!-- <script src="//cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.2.0/js/tab.min.js"></script> -->

<script src="/js/jquery.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/tab.min.js"></script>
<script src="/js/a_common.js"></script>

<script>
function search() {

    var p_uid = $("#p_uid").val();

    // var platform = $("#platform").val();
    var user_id = $("#user_id").val();
    var user_nickname = $("#user_nickname").val();
    // console.log("platform " + platform);
    var map = {
        url:"/index.php/admin/user_search?p_uid="+p_uid+"&user_id="+user_id+"&nickname="+user_nickname,
        success:function (data) {
            console.log("success");
            if(data.c == 0){

                var html = "";
                for(var index= 0 ; index < data.d.user.length; index++){
                    console.log(data.d.user);
                html += "用户ID:" + data.d.user[index].user_id;
                html += " 平台ID:" + data.d.user[index].p_uid;
                html += " 平台昵称:" + data.d.user[index].nickname;
                html += " 渠道:" + data.d.user[index].platform;
                html += "<br/>";
                $("#user_row").html(html);
            };
                html = "";

                for(var index= 0 ; index < data.d.reports.length; index++){
                    html += "用户ID:" + data.d.reports[index].user_id;
                    html += " 平台ID:" + data.d.reports[index].p_uid;
                    html += " 游戏昵称:" + data.d.reports[index].nickname;
                    html += " 游戏服务器:" + data.d.reports[index].server_id;
                    html += " 时间:" + new Date(parseInt(data.d.reports[index].create_date) * 1000).toLocaleString();
                    html += " 游戏:" + data.d.reports[index].game_father_name;
                    html += "<a href='/index.php/admin/player_login/"+data.d.reports[index].game_id+"/?openId="+data.d.reports[index].user_id+"'>直接登录玩家账号</a>"
                    html += " 、";
                    html += "<a href='/index.php/admin/illegal_user/"+data.d.reports[index].game_id+"/"+data.d.reports[index].user_id+"/"+data.d.reports[index].p_uid+"/"+data.d.reports[index].platform+"/"+data.d.reports[index].game_father_id
                    +"/?cproleid="+data.d.reports[index].cproleid+"&nickname="+data.d.reports[index].nickname+"'>封号</a>"
                    html += "<br/>";
                }
                $("#create_role_row").html(html);



                html = "";

                for(var index= 0 ; index < data.d.orders.length; index++){
                    html += "用户ID:" + data.d.orders[index].user_id;
                    html += " 订单号:" + data.d.orders[index].u_order_id;
                    html += " 钱（分）:" + data.d.orders[index].money;
                    html += " 服务器ID:" + data.d.orders[index].ext;
                    html += " 状态:" ;
                    if(data.d.orders[index].status == 0){
                        html += "未支付";
                    } else if(data.d.orders[index].status == 1){
                        html += "已支付 但未通知";
                    } else if(data.d.orders[index].status == 2){
                        html += "已完成";
                    }
                    html += " 时间:" + new Date(parseInt(data.d.orders[index].create_date) * 1000).toLocaleString();
                    html += "<br/>";
                }
                $("#order_row").html(html);
                // $("#user_nickname").val()='';

                html = "";

                for(var index= 0 ; index < data.d.login_report.length; index++){
                	html += "用户ID:" + data.d.login_report[index].user_id;
                	html += " 平台ID:" + data.d.login_report[index].p_uid;
                	html += " 游戏昵称:" + data.d.login_report[index].nickname;
                	html += " 游戏服务器:" + data.d.login_report[index].server_id;
                	html += " 时间:" + new Date(parseInt(data.d.login_report[index].create_date) * 1000).toLocaleString();
                    html += " 等级:" + data.d.login_report[index].level;
                    html += " 战力:" + data.d.login_report[index].power;
                    html += " 元宝:" + data.d.login_report[index].currency;
                    html += " 服务器:" + data.d.login_report[index].server_id;
                	html += "<a href='/index.php/admin/player_login/"+data.d.login_report[index].game_id+"/?openId="+data.d.login_report[index].user_id+"'>直接登录玩家账号</a>"
                	html += " 、";
                	html += "<a href='/index.php/admin/illegal_user/"+data.d.login_report[index].game_id+"/"+data.d.login_report[index].user_id+"/"+data.d.login_report[index].p_uid+"/"+data.d.login_report[index].platform+"/"+data.d.login_report[index].game_father_id
                	+"/?cproleid="+data.d.login_report[index].cproleid+"&nickname="+data.d.login_report[index].nickname+"'>封号</a>"
                	html += "<br/>";
                }
                $("#login_row").html(html);
            } else if (data.c == 2 || data.c == 3){
                alert('未找到用户');
            }
        }
    };
    myrequest(map);
}


function myrequest(map) {
    $.ajax({
        url: map.url,
        success: function (data) {
            if(data.c == -1 ){
                alert('登录超时');
                window.location.href = '/';
                return;
            } else {
                map.success(data);
            }
        },
        dataType: 'json'
    }
    );
}



</script>
</html>
