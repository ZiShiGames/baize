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
					var url ="/index.php/mengsansheng_admin_backstage/get_ltv_info?start="+$("#start").val()+"&end="+$("#end").val()+"&platform="+$("#platform").val()+"&server_id="+$("#server_id").val()+"&game_father_id="+20018;
					$.get(
                        url,
                        {},
                        function (response,status,xhr) {
							if(response.c==0){
								$(".info_table").remove();
								var data = response.d;
								console.log(data);
								$.each(data,function(i,v){
									$("#first_row").after(
										"<tr class = 'info_table'>"
										+"<td>"+ v['date'] +"</td>"
										+"<td>"+ v['sign'] +"</td>"
										+"<td>"+ v['create_role']+"</td>"
										+"<td>"+ v['1']+"("+(v['1']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['2']+"("+(v['2']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['3']+"("+(v['3']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['4']+"("+(v['4']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['5']+"("+(v['5']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['6']+"("+(v['6']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['7']+"("+(v['7']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['8']+"("+(v['8']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['9']+"("+(v['9']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['10']+"("+(v['10']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['11']+"("+(v['11']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['12']+"("+(v['12']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['13']+"("+(v['13']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['14']+"("+(v['14']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['15']+"("+(v['15']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['16']+"("+(v['16']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['17']+"("+(v['17']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['18']+"("+(v['18']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['19']+"("+(v['19']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['20']+"("+(v['20']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['29']+"("+(v['29']/v['create_role']).toFixed(2)+")"+"</td>"
										+"<td>"+ v['89']+"("+(v['89']/v['create_role']).toFixed(2)+")"+"</td>"
										+"</tr>"
									);
								})

							}else{
								alert(response.m);
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
		  <?php $this->load->view("admin/mengsansheng/wenxian_sub_menu", ['ltv'=>true]); ?>
		</div>
	<!--右侧边导航栏 end-->

		<div class="col-md-11 myborder">
			<div class="container-fluid">
				 <h4 class="alert-info" style="background-color:#ffffff">ltv信息管理</h4>
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
					  <?php foreach($platform_info as $one){?>
					  <option value="<?php echo $one->platform;?>"><?php echo $one->platform_chinese;?></option>
					  <?php } ?>
					</select>
					<!-- <span>游戏：</span> -->
					<!-- <select style="width:100px;height:25px;" id="game_father_id">
					  <option value="">全部</option>
					  <?php// foreach($game_faters as $one){?>
					  <option value="<?php// echo $one->game_father_id;?>"><?php //echo $one->game_father_name;?></option>
					  <?php// } ?>
					</select> -->
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
			<div>
				<table border="1" cellspacing="0" cellpadding="0" style="border:double;width:100%;height:auto;color:#000;background-color:#fff;">
					<tr id='first_row'>
						<th>日期</th>
						<th>注册数</th>
						<th>创角数</th>
						<th>第1日</th>
						<th>第2日</th>
						<th>第3日</th>
						<th>第4日</th>
						<th>第5日</th>
						<th>第6日</th>
						<th>第7日</th>
						<th>第8日</th>
						<th>第9日</th>
						<th>第10日</th>
						<th>第11日</th>
						<th>第12日</th>
						<th>第13日</th>
						<th>第14日</th>
						<th>第15日</th>
						<th>第16日</th>
						<th>第17日</th>
						<th>第18日</th>
						<th>第19日</th>
						<th>第30日</th>
						<th>第60日</th>
						<th>第90日</th>
					</tr>
				</table>
			</div>

			<!--数据显示 end-->
		</div>
	</div>
	</body>
</html>
