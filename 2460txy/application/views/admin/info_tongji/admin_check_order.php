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
                    var orderid = $("#user_order_id").val();
                    // if(!/^g+\d+_+\d+_+\d+_+\d+$/.test(orderid)){
                    //     alert("订单号不合法");
                    //     return;
					// }else{
					// 	console.log("orderid " + orderid);
					// 	console.log("订单号合法");
						var url = "/index.php/Admin/check_orderId_api?user_order_id=" + orderid;
					// }
					$.get(
                        url,
                        {},
                        function (response,status,xhr) {
							if(response.c==0){
								var data = response.d.info;
								console.log(data);
								$("#first_row").after(
									"<tr class = 'info_table'>"
									+"<td>"+ response.d.date+"</td>"
									+"<td>"+ data['user_id'] +"</td>"
									+"<td>"+ data['u_order_id'] +"</td>"
									+"<td>"+ data['platform']+"</td>"
									+"<td>"+ data['money']+"</td>"
									+"<td>"+ data['ext']+"</td>"
									+"<td>"+  response.d.status +"</td>"
									+"</tr>"
								);
							}else{
								alert(response.m);
							}
                        },
                        "json"
                    );
				});
				$("#cp_search").click(function(){
                    var orderid = $("#cp_user_order_id").val();
                    var url = "/index.php/Admin/cp_check_orderId_api?user_order_id=" + orderid;
					$.get(
                        url,
                        {},
                        function (response,status,xhr) {
							if(response.c==0){
								var data = response.d.info;
								console.log(data);
								$("#cp_first_row").after(
									"<tr class = 'info_table'>"
									+"<td>"+ response.d.date+"</td>"
									+"<td>"+ data['user_id'] +"</td>"
									+"<td>"+ data['cp_user_id'] +"</td>"
									+"<td>"+ data['u_order_id']+"</td>"
									+"<td>"+ data['cp_order_id']+"</td>"
									+"<td>"+ data['platform']+"</td>"
									+"<td>"+ data['money'] +"</td>"
									+"<td>"+ data['ext']+"</td>"
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
		  <?php $this->load->view('admin/admin_sub_menu', ['check_orderid' => true]); ?>
		</div>
	<!--右侧边导航栏 end-->

		<div class="col-md-11 myborder">
			<div class="container-fluid">
				 <h4 class="alert-info" style="background-color:#ffffff">订单查询</h4>
			</div>
			<!--搜索条件 start-->
			<br/>
			<br/>
			<div class="alert alert-info " role="alert">
			</div>
			<div class="row">
				<div class="col-md-6">
				2460订单号：
				<input type='text' id='user_order_id'></input>
				<button type="button" id="search">搜索</button>
				</div>
			</div>
			<br/>
			<br/>
            <div id="all_income">

				<table border="1" cellspacing="0" cellpadding="0" style="width:100%;height:auto;color:#000;background-color:#fff;">
					<tr id= 'first_row'>
						<th style="padding:5px;width:auto;">交易时间</th>
						<th style="padding:5px;width:auto;">用户id</th>
						<th style="padding:5px;width:auto;">订单id</th>
						<th style="padding:5px;width:auto;">渠道</th>
						<th style="padding:5px;width:auto;">金额（分）</th>
						<th style="padding:5px;width:auto;">区服</th>
						<th style="padding:5px;width:auto;">交易状态</th>
					</tr>
				</table>
            </div>
			<!--搜索条件 end-->

			<!--数据显示 start-->
			<br/>
			<div class="alert alert-info " role="alert">
			</div>
			<!-- <div class="row">
				<div class="col-md-6">
				CP订单号：
				<input type='text' id='cp_user_order_id'></input>
				<button type="button" id="cp_search">搜索</button>
				</div>
			</div>
			<br/>
			<br/>
			<div>
				<table border="1" cellspacing="0" cellpadding="0" style="width:100%;height:auto;color:#000;background-color:#fff;">
					<tr id= 'cp_first_row'>
						<th style="padding:5px;width:auto;">交易时间</th>
						<th style="padding:5px;width:auto;">2460用户id</th>
						<th style="padding:5px;width:auto;">CP用户id</th>
						<th style="padding:5px;width:auto;">2460订单id</th>
						<th style="padding:5px;width:auto;">CP订单id</th>
						<th style="padding:5px;width:auto;">渠道</th>
						<th style="padding:5px;width:auto;">金额（分）</th>
						<th style="padding:5px;width:auto;">区服</th>
					</tr>
				</table>
			</div> -->
			<!--数据显示 end-->
		</div>
	</div>
	</body>
</html>
