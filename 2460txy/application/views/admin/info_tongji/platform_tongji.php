<?php
defined('BASEPATH') or exit('No direct script access allowed');

?>
<html style="height: 100%;">
	<head>
		<title></title>
		<meta charset="utf-8">
		<meta id="viewport" name="viewport" content="target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0" />
		<!-- <link href="//cdn.bootcss.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet"> -->
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
                    }else{
							location.href="/index.php/Get_platform_info/daily_income?start="+$("#start").val()+"&end="+$("#end").val()+"&platform="+$("#platform").val()+"&server_id="+$("#server_id").val()+"&game_father_id="+$("#game_father").val();
					}
					});
			});
		</script>
	</head>
	<body>
	<div class="row">
	<!--右侧边导航栏 start-->
		<div class="col-md-1">
		  <?php $this->load->view("admin/admin_sub_menu", ['plat_info'=>true]); ?>
		</div>
	<!--右侧边导航栏 end-->

		<div class="col-md-11 myborder">
			<div class="container-fluid">
				 <h4 class="alert-info" style="background-color:#ffffff">渠道统计</h4>
			</div>
			<!--搜索条件 start-->
			<br/>
			<br/>
			<div class="alert alert-info " role="alert">
				<form action="">
					<span>起始日期：</span><input type="date" value="<?php echo @$start;?>" id="start" name="start" style="width:150px;height:25px;">
					<span>截止日期：</span><input type="date" value="<?php echo @$end;?>" id="end" name="end" style="width:150px;height:25px;">
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
			<!--搜索条件 end-->

			<!--数据显示 start-->
			<br/>
			<?php if (@$info_is_show == 'show') {
    ?>
			<div class="alert alert-info " role="alert">
			<h4><?php $total = $total/100;
    echo "共 ".$total." 元"; ?></h4>
				<table border="1" cellspacing="0" cellpadding="0" style="width:100%;height:auto;color:#000;background-color:#fff;">
					<tr>
						<th style="padding:5px;width:130px">渠道名</th>
						<th style="padding:5px;">总注册数</th>
						<th style="padding:5px;">登录人数</th>
						<th style="padding:5px;">新账号创角人数</th>
						<th style="padding:5px;">充值人数</th>
						<th style="padding:5px;">总充值</th>
					</tr>
					<?php
					foreach ($orders as $one) {
        $money=0;
        $pay_num=0;
		 ?>
					<tr>
						<td style="padding:5px;"><?php echo $one['platform']?></td>
						<td style="padding:5px;"><?php echo $one['sign_count']?></td>
						<td style="padding:5px;"><?php echo $one['login_count']?></td>
						<td style="padding:5px;"><?php echo $one['create']?></td>
						<td style="padding:5px;"><?php echo $one['pay_count']?></td>
						<td style="padding:5px;"><?php echo $one['money']/100?></td>



					</tr>
					<?php

    } ?>
				</table>
				<div style="display:none;">
					<button type="button" id="pre" >上一页</button>
					<button type="button" id="next" >下一页</button>
					<input type="number" name="num" value="" min="1" style="width:80px;">
					<button type="button" id="turn_to" >跳转</button>
				</div>
			</div>
			<?php

} ?>

			<?php if (@$create_error_info) {
    echo $create_error_info;
} ?>
			<!--数据显示 end-->
		</div>
	</div>
	</body>
</html>
