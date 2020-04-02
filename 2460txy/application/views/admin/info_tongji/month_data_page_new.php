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
    var game_father_id = $("#game_father_id").val();
    console.log("start " + start);
    console.log("end " + end);
    console.log("platform " + platform);
    if(start == ""){
      alert("起始日期不能为空");
      return;
    }else if (game_father_id=="") {
      alert("游戏名不能为空");
      return;
    }
    var url = "/index.php/Admin_report_api/get_data_by_month?start=" + start + "&to="  +end +"&game_father_id="+game_father_id +"&platform="+platform ;
    $.get(
      url,
    {},
    function (response,status,xhr) {
      if (response.c == '-1') {
        alert('错误：'+response.m);
        return;
      }
      var data = new Array();
      if(response.c == 0){
        data = response.d.info;
        data=data.sort();
        var index = 0;
        var t_arpu=0;
        var t_arppu=0;
        $('#total_money').empty();
        $('#total_money').append((response.d.total_money).toFixed(0));
        $(".info_table").remove();
        var dataOrder = data.sort(
          function(a, b)
          {
            return (a.money - b.money);
          }
        );
        $.each(dataOrder,
          function(index, value)
          {
            var total = parseInt(value.money);
            // data[0]['total']+=total;
            var login = parseInt(value.login);
            var renshu = parseInt(value.renshu);
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
            var background_color;
            if(index%2==0){
              background_color = '#eee';
            }else{
              background_color = '#fff';
            }
            $("#first_row").after(
              "<tr class = 'info_table' style='background-color:"+background_color+"'>"+"<td>"+ value.date_time +"</td>"
              +"<td>"+ value.platform_chinese_name +"</td>"
              +"<td>"+ parseInt(value.login).toFixed(0) +"</td>"
              +"<td>"+ parseInt(value.createrole).toFixed(0) +"</td>"
              +"<td>"+parseInt(value.renshu).toFixed(0)+"</td>"
              +"<td>"+ parseInt(value.cishu).toFixed(0)+"</td>"
              +"<td>"+ parseInt(value.fufeilv).toFixed(0) +"%</td>"
              +"<td>"+ parseInt(arppu).toFixed(2) +"</td>"
              +"<td>"+ parseInt(arpu).toFixed(2)  +"</td>"
              +"<td>"+ parseInt(value.money).toFixed(0) +"</td>"
              +"</tr>"
            );
          }
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
    <!-- <div class="col-md-1"> -->
      <?php // $this->load->view('admin/admin_sub_menu', ['month_data' => true]);?>
    <!-- </div> -->
  <!--右侧边导航栏 end-->

    <div class="col-md-11 myborder">
      <!-- <div class="container-fluid">
         <h4 class="alert-info" style="background-color:#ffffff">月度总结</h4>
      </div> -->
      <!--搜索条件 start-->
      <br/>
      <br/>
      <div class="alert alert-info " role="alert">
        <form action="">
          <span>选择月份：</span><input type="month" value="" id="start" name="start" style="width:150px;height:25px;">
          <span>游戏：</span>
           <select style="width:100px;height:25px;" id="game_father_id">
           <option value="">全部</option>
<?php  foreach ($game_fathers as $one) {
?>
            <option value="<?php echo $one->game_father_id;?>"><?php echo $one->game_father_name;?></option>
<?php
}?>
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
      <h4 id='total_money'></h4>
        <table border="1" cellspacing="0" cellpadding="0" style="border:double;width:100%;height:auto;color:#000;background-color:#fff;">
          <tr id= "first_row" style="border-bottom:double">
            <th style="padding:5px;width:auto;">日期</th>
            <th style="padding:5px;">渠道</th>
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
