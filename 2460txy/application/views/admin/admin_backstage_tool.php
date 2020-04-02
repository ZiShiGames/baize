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
    <div class="col-md-1">
        <?php $this->load->view("admin/admin_sub_menu", ['admin_tool'=>true]); ?>
    </div>

    <div class="col-md-11 myborder">

    <div class="container-fluid">
        <h4 class="alert-info" style="background-color:#ffffff">管理员工具</h4>

    <br>
    <br>
    <div class="container">
    </div>
		<div class='addCustomerService' style=" width:35%;">
				<p>创建客服账号</p>
				<input name="CSname" type="text" class="form-control" id="CSname" placeholder="username">
				<input name="CSpassword" type="password" class="form-control" id="CSpassword" placeholder="password">
				<button onclick="AddCS()" class="">提交</button>
		</div>
    <div class = 'addNewSpecialServer' style=" width:35%;">
        <p>专服管理</p>
        <input name="platform_name" type="text" class="form-control" id="platform_name" placeholder="渠道中文名">
        <input name="platform_en_name" type="text" class="form-control" id="platform_en_name" placeholder="渠道英文名">
        <input name="server_login_url" type="text" class="form-control" id="server_login_url" placeholder="服务器列表链接（研发提供）">
        <button onclick="add_special_server()" class="">提交</button>
    </div>

    <div class = 'addNewSpecialServer' style=" width:35%;">

        <button onclick="check_power()" class="">更新</button>
    </div>

    </div>
  </div>
 </div>
</body>
 <script src="//cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.2.0/js/tab.min.js"></script>

<script src="/js/jquery.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/tab.min.js"></script>
<script src="/js/a_common.js"></script>
<script>
var check_power = function(){
    $.ajax({
		url: 'http://h5.allugame.com/index.php/SeparateProject/get_top_by_id_array',
		success: function () {
            alert(ok);
		},
		dataType: 'json'
	}
	);

}
$().ready(
    $('#myTabs a').click(function (e) {
        console.log("click");
          e.preventDefault()
          $(this).tab('show')
    })
);
function AddCS(){
	var username = $('#CSname').val();
	var password = $('#CSpassword').val();
	var url = '/index.php/admin/addCustomerService?CSname='+username+'&CSpassword='+password;
	$.ajax({
		url: url,
		success: function (data) {
			if(data.c == -1 ){
				alert('登录超时');
				window.location.href = '/';
				return;
			} else if (data.c == 0) {
				alert('添加成功');
			} else {
				alert('错误:' + data.m);
			}
		},
		dataType: 'json'
	}
	);
}
var add_special_server = function(){

    var platform_name = $('#platform_name').val();
	var platform_en_name = $('#platform_en_name').val();
    var server_login_url = $('#server_login_url').val();
    if(!platform_name||!platform_en_name||!server_login_url){
        alert('参数错误');
        return;
    }
	var url = '/index.php/admin/addSpecialServer?platform_name='+platform_name+'&platform_en_name='+platform_en_name+'&server_login_url='+server_login_url;
	$.ajax({
		url: url,
		success: function (data) {
			if(data.c == -1 ){
				alert('登录超时');
				window.location.href = '/';
				return;
			} else if (data.c == 0) {
				alert('添加成功');
			} else {
				alert('错误:' + data.m);
			}
		},
		dataType: 'json'
	}
	);
}


</script>
</html>
