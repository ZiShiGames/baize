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
        <?php $this->load->view("admin/admin_sub_menu", ['platform'=>true]); ?>
    </div>

    <div class="col-md-11 myborder">

    <div class="container-fluid">
        <h4 class="alert-info" style="background-color:#ffffff">渠道管理</h4>
    <!-- <h1>首页 <small><a href="/index.php/admin/task_manage">交易</a></small> </h1> -->

    <!-- <div class="alert alert-info " role="alert">搜索</div> -->
    <div class="container">
        <div class="row">

                <div class="col-md-3">

                    <!-- <form class="navbar-form " role="search">
                        <label>显示结果 : </label>
                        <select id="page_count" class="form-control" onchange="on_data_refresh(1)">
                          <option value="10" selected="">每页显示10</option>
                          <option value="20">每页显示20</option>
                          <option value="30">每页显示30</option>
                          </select>
                    </form> -->
                </div>

                <div class="col-md-3">

                </div>
        </div>
    </div>
    <div class="alert alert-info" role="alert">游戏列表</div>
    <div id="user_list">
        <table style="border:1px solid black">
            <tr>
                <td>渠道中文名</td><td>渠道英文名</td><td>渠道中文名</td><td>渠道英文名</td><td>渠道中文名</td><td>渠道英文名</td><td>渠道中文名</td><td>渠道英文名</td>
            </tr>
            <?php
            // foreach ($platform_info as $key => $value) {
            //     # code...
            //     echo json_encode($value);
            // }
                foreach ($platform_info as $key => $value ) {
                    // echo var_export($value,true);
                    // echo json_encode($value[0][0]);
                    // echo json_encode($value[0][1]);
                    // echo json_encode($value[0][2]);
                    // echo '<hr>';
                        echo '<tr>
                        <td style="border:1px solid black; text-align:center;">'.$value[0][0]->platform_chinese.'</td>
                        <td style="border:1px solid black; text-align:center;">'.$value[0][0]->platform.'</td>';
                        if(isset($value[0][1])){
                            echo '<td style="border:1px solid black; text-align:center;">'.$value[0][1]->platform_chinese.'</td>
                            <td style="border:1px solid black; text-align:center;">'.$value[0][1]->platform.'</td>';
                            if(isset($value[0][2])){
                                echo '<td style="border:1px solid black; text-align:center;">'.$value[0][2]->platform_chinese.'</td>
                                <td style="border:1px solid black; text-align:center;">'.$value[0][2]->platform.'</td>';
                                if(isset($value[0][3])){
                                    echo '<td style="border:1px solid black; text-align:center;">'.$value[0][3]->platform_chinese.'</td>
                                    <td style="border:1px solid black; text-align:center;">'.$value[0][3]->platform.'</td></tr>';
                                }else{
                                        echo '<td></td></tr>';
                                }
                            }else{
                                    echo '<td></td><td></td></tr>';
                            }
                        }else{
                            echo '<td></td><td></td><td></td></tr>';
                        }




                }

            ?>

        </table>
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

$().ready(
    $('#myTabs a').click(function (e) {
        console.log("click");
          e.preventDefault()
          $(this).tab('show')
    })
);


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

function request_list(url) {

    myrequest({
      url: url,
      success: function (data) {
          console.log("done " + data);
          if(data.c == 0 ){
              $("#user_list").html(data.d);
          } else {
              alert(data.m);
          }
      },
      dataType: 'json'
    });
}

function on_data_refresh(page) {
    console.log("on_data_refresh");
    var status = $("#status").val();;
    var create_date_order = $("#create_date_order").val();;
    var page_count = $("#page_count").val();
    var game_father_id = $("#game_father_id").val();
    var url = "/index.php/admin/get_game_list?";
    // if(status != 10){
    //     url += "status="+ status + "&";
    // }
    // url += "create_date_order="+ create_date_order + "&";
    url += "page_count="+ page_count + "&";
    url += "page="+ page + "&";
    url += "game_father_id="+ game_father_id + "&";
    console.log("url:"+url);
    request_list(url);
}

function goto() {
    var page_input = $("#page_input").val();
    if(page_input == ""){
        alert("页数不能为空");
        return;
    }
    on_data_refresh(page_input);
}

function turn_off_game(game_id) {
    set_status(game_id,0);
}

function turn_on_game(game_id) {
    set_status(game_id,1);
}

function set_status(game_id,status) {

    var a=confirm("确定进行操作么?");
    if(!a){
        return;
    }


    var url = "/index.php/admin/set_game_status?status="+status+"&game_id="+game_id;
    myrequest({
      url: url,
      success: function (data) {
          console.log("done " + data);
          if(data.c == 0 ){
              $("#"+game_id+"_row").html(data.d);
          } else {
              alert(data.m);
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
