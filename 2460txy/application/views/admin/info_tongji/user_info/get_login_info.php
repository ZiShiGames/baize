<?php
defined('BASEPATH') or exit('No direct script access allowed');

?>
<html style="height: 100%;">
	<head>
		<title></title>
		<meta charset="utf-8">
		<meta id="viewport" name="viewport" content="target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0" />
		<link href="/css/bootstrap.min.css" rel="stylesheet">
		<link href="/css/admin.css" rel="stylesheet">
		<script type="text/javascript" src="/js/jquery-3.1.1.js"></script>
		<script type="text/javascript">
			$(function(){
				$("#search").click(function(){
                    console.log("click");
                    var start = $("#start").val();
                    var end = $("#end").val();
                    var platform = $("#platform").val();
                    var game_father_id = $("#game_father").val();
                    console.log("start " + start);
                    console.log("end " + end);
                    console.log("platform " + platform);
                    if(start == ""){
                        alert("起始日期不能为空");
                        return;
                    }
                    var url = "/index.php/Get_user_login_info/daily_income?start=" + start + "&end="  +end +"&game_father_id="+game_father_id +"&platform="+platform ;
                    $.get(
                        url,
                        {},
                        function (response,status,xhr) {
                            console.log("response " + JSON.stringify(response));
							var data = 0;
							var data = response.d;
                            if(response.c == 0){
								$(".info_table").remove();
								for(var i = data.length-1; i>=0 ;i--){
										$("#first_row").after(
											"<tr class = 'info_table'>"+"<td>"+ data[i]['data'] +"</td>"
											+"<td>"+ data[i]['login_info']+"</td>"
											+"<td>"+ data[i]['sign_info']+"</td>"
											+"<td>"+ data[i]['new_player']+"</td>"
											+"<td>"+ data[i]['chuangjuelv'].substring(0,6)*100+ "%" +"</td>"
											+"</tr>"
										);
								}
                            }
                        },
                        "json"
                    );
				});
			});
		</script>
	</head>
	<body>
	<div class="row">
	<!--右侧边导航栏 start-->
		<div class="col-md-1">
		  <?php $this->load->view('admin/admin_sub_menu', ['get_login_info' => true]); ?>
		</div>
	<!--右侧边导航栏 end-->

		<div class="col-md-11 myborder">
			<div class="container-fluid">
				 <h4 class="alert-info" style="background-color:#ffffff">用户登录分析</h4>
			</div>
			<!--搜索条件 start-->
			<br/>
			<br/>
			<div class="alert alert-info " role="alert">
				<form action="">
					<span>起始日期：</span><input type="date" value="" id="start" name="start" style="width:150px;height:25px;">
					<span>截止日期：</span><input type="date" value="" id="end" name="end" style="width:150px;height:25px;">
					<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
					<span>渠道：</span>
					<select style="width:100px;height:25px;" id="platform">
					  <option value="">全部</option>
					  <?php foreach ($platform_info as $one) {
    ?>
					  <option value="<?php echo $one->platform; ?>"><?php echo $one->platform_chinese; ?></option>
					  <?php
} ?>
					</select>
					<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
					<span>游戏</span>
                    <select style="width:100px;height:25px;" id="game_father">
					  <option value="">全部</option>
					  <?php foreach ($game_faters as $one) {
        ?>
					  <option value="<?php echo $one->game_father_id; ?>"><?php echo $one->game_father_name; ?></option>
					  <?php
    } ?>
					</select>

				</form>
			</div>
			<div class="row">
				<div class="col-md-6">
				<button type="button" id="search">搜索</button>
				</div>
			</div>
            <div id="all_income">
            </div>
			<!--搜索条件 end-->
			<!--数据显示 start-->
			<br/>
			<div class="alert alert-info " role="alert">
				<table border="1" cellspacing="0" cellpadding="0" style="width:100%;height:auto;color:#000;background-color:#fff;">
					<tr id= "first_row">
						<th style="padding:5px;width:130px">日期</th>
						<th style="padding:5px;">登陆人数</th>
						<th style="padding:5px;">进入游戏场景人数（含老玩家）</th>
						<th style="padding:5px;">玩家创角数(含老玩家)</th>
						<th style="padding:5px;">创角率</th>
					</tr>
				</table>
				<div style="display:none;">
					<button type="button" id="pre" >上一页</button>
					<button type="button" id="next" >下一页</button>
					<input type="number" name="num" value="" min="1" style="width:80px;">
					<button type="button" id="turn_to" >跳转</button>
				</div>
			</div>

			<!--数据显示 end-->
		</div>
	</div>
	</body>
</html>
