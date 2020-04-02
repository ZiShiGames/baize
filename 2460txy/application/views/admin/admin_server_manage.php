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
        <?php $this->load->view("admin/admin_sub_menu", ['servers'=>true]); ?>
    </div>

    <div class="col-md-11 myborder">

    <div class="container-fluid">
        <h4 class="alert-info" style="background-color:#ffffff">服务器管理</h4>

    <br>
    <br>
    <div class="container">
        <div class="row">

                <div class="col-md-3">

                    <form class="navbar-form " role="search">
                        <label>显示结果 : </label>
                        <select id="page_count" class="form-control" onchange="on_data_refresh(1)">
                          <option value="10" selected="">每页显示10</option>
                          <option value="20">每页显示20</option>
                          <option value="30">每页显示30</option>
                          </select>
                    </form>
                </div>

                <div class="col-md-3">

                    <form class="navbar-form " role="search">
                        <label>游戏名 : </label>
                        <select id="game_father_id" class="form-control" onchange="on_data_refresh(1)">
                            <option value="juhe" selected="">无</option>
                            <?php foreach ($platform as $one): ?>
                                <option value="<?php echo $one->platform_server_name ?>"><?php echo $one->platform_chinese_name ?></option>
                            <?php endforeach; ?>
                          </select>
                    </form>
                    <label>新玩家导入 : </label>
                    <a onclick="turn_login_server(0)">登录页</a>
                    <a onclick="turn_game_server(1)">最新服务器</a>
                </div>
                <div style="  float: right;" >
                     <p>使用须知：<br>
                     聚合服1服id为8000，其他服id为2~ <br>
                     9g 1服id为1，其他服id为5002~ <br>
                     金榜 服务器id为 5001~ <br>
                     至于为什么这么麻烦，因为研发传过来就是这么麻烦。。我也是很绝望的 sad..<br>
                 </p>
             </div>
        </div>
    </div>
    <div class="alert alert-info" role="alert">游戏列表

    </div>
    <div id="user_list">
        <!-- <table border="1" cellspacing="0" cellpadding="0" style="width:100%;height:auto;color:#000;background-color:#fff;"> -->
            <!-- <tr> -->
                <!-- <th style="width:50%;">服务器名</th> -->
                <!-- <th style="width:50%;"> -->
                <!-- <a onclick="server_all_checked('server_turn',true)">全选</a>
                <a onclick="server_all_checked('server_turn',false)">全不选</a> -->
                <p> 当前渠道 :<?php echo  $plat?></p>
                <a onclick="server_all_turn_on()">开服</a>
                <a onclick="server_all_turn_off()">关服</a>
                输入关闭<input id='begin' type = 'text' style='width:25%;height:80px'></input>服至<input id='end' type = 'text' style='width:25%;height:80px'></input>服
                <!-- </th> -->
            <!-- </tr> -->
            <!-- <?php
// foreach ($servers->server_list as $sver) {
        ?>
    <tr>
        <td><?php // echo $sver->name?></td>
        <td>
            <a onclick="turn_off_server(<?php // echo $sver->id?>)">关服</a>
            <a onclick="turn_on_server(<?php //echo $sver->id?>)">开服</a>
            &nbsp;&nbsp;<input type = 'checkbox' name = 'server_turn' value = '<?php// echo $sver->id?>'>
        </td>
    </tr>

<?php
    // }
?> -->

        <!-- </table> -->

        <div id = 'server_list'>
            <?php
            // echo json_encode($servers->server_list);
            if (!$servers->server_list) {
                echo '暂无服务器';
                return;
            }
                foreach ($servers->server_list as $one) {
                    echo '服务器id:'.$one->id.'            服务器名字:'.$one->name;
                    echo '<br/>';
                };

             ?>?>
        </div>
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

$().ready(
    $('#myTabs a').click(function (e) {
        console.log("click");
          e.preventDefault()
          $(this).tab('show')
    })
);
function server_all_turn_on(){
    var a=confirm("确定进行操作么?");
    var status = 2;
    if(!a){
        return;
    }
    var allvalue = document.getElementsByName('server_turn');
    var begin = new Number($("#begin").val());
    var end = new Number($("#end").val());

    if(begin){
        if(!end){
            turn_on_server(begin);
        }else if(begin > end ){
            alert('结束服不能小于开始服');
            return;
        }else if(end){
            turn_server(begin,end,status);
            return;
        }
        return;
    }
}

function server_all_turn_off(){
    var a=confirm("确定进行操作么?");
    var status = 3;
    if(!a){
        return;
    }
    var allvalue = document.getElementsByName('server_turn');
    var begin = new Number($("#begin").val());
    var end = new Number($("#end").val());
    if(begin){
        if(!end){
            turn_on_server(begin);
        }else if(begin > end ){
            alert('结束服不能小于开始服');
            return;
        }else if(end){
            turn_server(begin,end,status);
            return;
        }
        return;
    }
}
function turn_server(begin,end,status){

    var url ="/index.php/admin/set_list_status?begin="+begin+"&end="+end+"&status="+status+"&platform= <?php echo $plat?>";
    $.get(url,{},function (response) {if (response.c == '-1') {alert('错误：'+response.m);return;}if(response.c == 0){alert(response.m+': '+response.d.platform+' '+response.d.count);}},
        "json"

    );
}
function server_all_checked(name,boolValue){
    var allvalue = document.getElementsByName(name);
    for (var i = 0; i <allvalue.length; i++) {
        if (allvalue[i].type == "checkbox")
            allvalue[i].checked = boolValue;
    }

}

function turnOff(severId){
    window.location.href="/index.php/admin/set_server_status?server_id="+severId+"&status=3";
}

function turnOff(severId){
    window.location.href="/index.php/admin/set_server_status?server_id="+severId+"&status=2";
}

function new_game() {
    console.log("new_game");
    window.location.href="/index.php/admin/new_game_page";
}

function new_game_father() {
    window.location.href="/index.php/admin/new_game_father_page";
}

function new_platform() {
    window.location.href="/index.php/admin/new_platform_page";
}

function edit_game(game_id) {
    console.log("edit_game");
    window.location.href="/index.php/admin/edit_game?game_id="+game_id;
}

// function request_list(url) {
//
//     myrequest({
//       url: url,
//       success: function (data) {
//           console.log("done " + data);
//           if(data.c == 0 ){
//               $("#user_list").html(data.d);
//           } else {
//               alert(data.m);
//           }
//       },
//       dataType: 'json'
//     });
// }

function on_data_refresh(page) {
    console.log("on_data_refresh");
    var status = $("#status").val();;
    var create_date_order = $("#create_date_order").val();;
    var page_count = $("#page_count").val();
    var game_father_id = $("#game_father_id").val();
    var url = "/index.php/admin/get_server_list?";
    // if(status != 10){
    //     url += "status="+ status + "&";
    // }
    // url += "create_date_order="+ create_date_order + "&";
    url += "page_count="+ page_count + "&";
    url += "page="+ page + "&";
    url += "platform_id="+ game_father_id + "&";
    console.log("url:"+url);
    // request_list(url);
    location.href=url;
}

function goto() {
    var page_input = $("#page_input").val();
    if(page_input == ""){
        alert("页数不能为空");
        return;
    }
    on_data_refresh(page_input);
}
function turn_login_server(){
    set_status(1,0,'all');
}

function turn_game_server(){
    set_status(1,1,'all');
}

function turn_off_server(server_id) {
    var platform = ' <?php echo $plat?> ';
    set_status(server_id,3,platform);
}

function turn_on_server(server_id) {
    var platform = ' <?php echo $plat?> ';
    set_status(server_id,2,platform);
}

function set_status(server_id,status,platform) {

    // var a=confirm("确定进行操作么?");
    // if(!a){
    //     return;
    // }


    var url = "/index.php/admin/set_server_status?status="+status+"&server_id="+server_id+"&platform="+platform;
    myrequest({
      url: url,
      success: function (data) {
          console.log("done " + data);
          if(data.c == 0 ){
              $("#"+game_id+"_row").html(data.d);
          } else {
            //   alert(data.m);
          }
      },
      dataType: 'json'
    });
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
