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
					location.href="/index.php/Test_report/info_tongji?start="+$("#start").val()+"&end="+$("#end").val()+"&platform="+$("#platform").val()+"&server_id="+$("#server_id").val();
				});
			});
		</script>
	</head>
	<body>
	<div class="row">
	<!--右侧边导航栏 start-->
		<!-- <div class="col-md-1"> -->
		  <?php //$this->load->view("admin/admin_sub_menu", ['info'=>true]); ?>
		<!-- </div> -->
	<!--右侧边导航栏 end-->

		<div class="col-md-11 myborder">
			<!-- <div class="container-fluid">
				 <h4 class="alert-info" style="background-color:#ffffff">用户信息管理</h4>
			</div> -->
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
					  <?php foreach($platform_info as $one){?>
					  <option value="<?php echo $one->platform;?>"><?php echo $one->platform_chinese;?></option>
					  <?php } ?>
					</select>
					<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
					<span>区服ID：</span>
					<input type="text" name="server_id" id="server_id" value="<?php echo @$server_id;?>" style="width:120px;height:25px;">
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
			<?php if(@$info_is_show == 'show'){ ?>
			<div class="alert alert-info " role="alert">
			<h4><?php echo "共 ".$create_num." 人";?></h4>
				<table border="1" cellspacing="0" cellpadding="0" style="width:100%;height:auto;color:#000;background-color:#fff;">
					<tr>
						<th style="padding:5px;width:130px">创角日期</th>
						<th style="padding:5px;">UID</th>
						<th style="padding:5px;">玩家角色名</th>
						<th style="padding:5px;">平台名称</th>
						<th style="padding:5px;">平台用户ID</th>
						<th style="padding:5px;">区服ID</th>
						<th style="padding:5px;width:100px">该时间段内充值金额</th>
						<th style="padding:5px;">该时间段内充值次数</th>
						<th style="padding:5px;">注册时间</th>
					</tr>
					<?php foreach($create_info as $create_one){ $money=0;$pay_num=0;?>
					<tr>
						<td style="padding:5px;"><?php echo date('Y-m-d',$create_one->create_date);?></td>
						<td style="padding:5px;"><?php echo $create_one->user_id;?></td>
						<td style="padding:5px;"><?php echo $create_one->nickname;?></td>
						<td style="padding:5px;"><?php echo $create_one->platform;?></td>
						<td style="padding:5px;"><?php echo $create_one->p_uid;?></td>
						<td style="padding:5px;"><?php echo $create_one->server_id;?></td>
						<td style="padding:5px;"><?php
							if(@$game_order_res){
								foreach($game_order_res as $game_order_one){
									if($create_one->user_id == $game_order_one->user_id){
										$money += $game_order_one->money;
										++$pay_num;
									}
								}
							}
							echo $money/100;
						?></td>
						<td style="padding:5px;"><?php echo $pay_num;?></td>
						<td style="padding:5px;"><?php
							foreach($sign_res as $sign_one){
								if($create_one->user_id == $sign_one->user_id){
									echo date("Y-m-d",$sign_one->create_date)."<br/>";
								}
							}
						?></td>
					</tr>
					<?php } ?>
				</table>
				<div style="display:none;">
					<button type="button" id="pre" >上一页</button>
					<button type="button" id="next" >下一页</button>
					<input type="number" name="num" value="" min="1" style="width:80px;">
					<button type="button" id="turn_to" >跳转</button>
				</div>
			</div>
			<?php } ?>

			<?php if(@$create_error_info){ echo $create_error_info; } ?>
			<!--数据显示 end-->
		</div>
	</div>
	</body>
</html>
