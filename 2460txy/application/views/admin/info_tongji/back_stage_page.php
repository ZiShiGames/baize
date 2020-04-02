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
                    // var game_father_id = $("#game_father").val();
					var game_father_id = 20000;
                    console.log("start " + start);
                    console.log("end " + end);
                    console.log("platform " + platform);
                    if(start == ""){
                        alert("起始日期不能为空");
                        return;
					}else if (platform=="") {
						alert("渠道名不能为空");
						return;
					}else if (game_father_id=="") {
						alert("游戏名不能为空");
						return;
                    }

                    var url = "/index.php/Admin_backstage_report_api/get_data?start=" + start + "&to="  +end +"&game_father_id="+game_father_id +"&platform="+platform ;
					// location.href=url;
					$.get(
                        url,
                        {},
                        function (response,status,xhr) {
							if (response.c == '-1') {
                            	alert('错误：'+response.m);
								return;
                            }
							// console.log(response.d.info);
							var data = new Array();

							// console.log(response.c);
							// console.log(data.length);
							// console.log(JSON.stringify(data));
							// console.log(data);
							// console.log(typeof(data));
                            if(response.c == 0){
								data = response.d.info;
								var index = 0;
								var t_arpu=0;
								var t_arppu=0;
								var t_total=0;
								var t_cishu=0;
								var t_scale=0;
								var t_cishu=0;
								// data[0]['total']=0;
								$(".info_table").remove();
								$.each(data,function(i,n){
									if(data[i]){
										index += 1;
										var total = parseInt(data[i]['total']);
										// data[0]['total']+=total;
										var login = parseInt(data[i]['login']);
										var renshu = parseInt(data[i]['renshu']);
										if(login==0){
											var arpu =0;
										}else{
											var arpu = total / login;
										}
										if(renshu==0){
											var arppu = 0;
										}else{
											var arppu =total/renshu;
										}

										t_arpu+=arpu;
										t_arppu+=arppu;
										// t_cishu+=parseInt(data[i]['cishu']);
										t_total+=parseInt(data[i]['total']);
										t_scale+=parseInt(data[i]['fufeilv']);
										t_cishu+=parseInt(data[i]['cishu']);
										var background_color;
										if(i%2==0){
											background_color = '#eee';
										}else{
											background_color = '#fff';
										}

										$("#first_row").after(
											"<tr class = 'info_table' style='background-color:"+background_color+";'>"+"<td>"+ data[i]['begin'] +"</td>"
											+"<td>"+ data[i]['platform_name']+"</td>"
											+"<td>"+ parseInt(data[i]['sign_report']).toFixed(0) +"</td>"
											+"<td>"+ parseInt(data[i]['login']).toFixed(0) +"</td>"
											+"<td>"+ parseInt(data[i]['createrole']).toFixed(0) +"</td>"
											+"<td>"+ parseInt(data[i]['renshu']).toFixed(0)+"</td>"
											+"<td>"+ parseInt(data[i]['cishu']).toFixed(0)+"</td>"
											+"<td>"+ parseInt(data[i]['fufeilv']).toFixed(0) +"%</td>"
											+"<td>"+ arppu.toFixed(2) +"</td>"
											+"<td>"+ arpu.toFixed(2)  +"</td>"
											+"<td>"+ parseInt(data[i]['total']).toFixed(0) +"</td>"
											+"</tr>"
										);

									}

								});

								$("#first_row").after(
									"<tr class = 'info_table' style='height:35px;'>"+"<td>总数</td>"
									+"<td>"+ data[1]['platform_name']+"</td>"
									+"<td>0</td>"
									+"<td>0</td>"
									+"<td>0</td>"
									+"<td>"+t_cishu+"</td>"
									+"<td>"+(t_scale/index).toFixed(2)+"</td>"
									+"<td>"+ (t_arppu/index).toFixed(2)  +"</td>"
									+"<td>"+ (t_arpu/index).toFixed(2) +"</td>"
									+"<td>"+t_total +"</td>"
									+"</tr>"
								);
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
		  <?php $this->load->view('admin/admin_backstage_menu', ['data_info' => true]);?>
		</div>
	<!--右侧边导航栏 end-->

		<div class="col-md-11 myborder">
			<div class="container-fluid">
				 <h4 class="alert-info" style="background-color:#ffffff">收入统计</h4>
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
					<!-- <span>游戏</span>
                    <select style="width:100px;height:25px;" id="game_father">
					  <option value="">全部</option>
					  <?php //foreach ($game_faters as $one) {
        ?>
					  <option value="<?php //echo $one->game_father_id;?>"><?php //echo $one->game_father_name;?></option>
					  <?php
    //}?>
					</select> -->

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
			<h4></h4>
				<table border="1" cellspacing="0" cellpadding="0" style="border:double;width:100%;height:auto;color:#000;background-color:#fff;">
					<tr id= "first_row" style="border-bottom:double">
						<th style="padding:5px;width:auto;">日期</th>
						<th style="padding:5px;">渠道</th>
						<th style="padding:5px;">新增数</th>
						<th style="padding:5px;">登陆数</th>
						<th style="padding:5px;">创角数</th>
						<th style="padding:5px;">充值人数</th>
						<th style="padding:5px;">充值次数</th>
						<th style="padding:5px;">付费率</th>
						<th style="padding:5px;">人均付费(总额/充值人数)</th>
						<th style="padding:5px;">活跃付费(总额/登陆人数)</th>
						<th style="padding:5px;">总额</th>
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
