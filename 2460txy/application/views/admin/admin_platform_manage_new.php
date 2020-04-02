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

<body style="padding-left:30px;">
    <?php
    // $data = array('name' => '用户管理', );
    // $this->load->view('admin_header',$data);
    ?>
  <div class="row" style="padding-left:50rpx;">


    <div class="col-md-11 myborder">

        <div class="row">

                <div class="col-md-3">
                </div>

                <div class="col-md-3">

                </div>
        </div>
    </div>
    <div id="user_list">
        <table style="border:1px solid black">
            <tr>
                <?php 
                    for($i = 0 ; $i <= 3; $i++){
                        echo "<td style='border:1px solid black; text-align:center;'>渠道中文名</td>
                        <td style='border:1px solid black; text-align:center;'>渠道英文名</td>";
                    };
                ?>
            </tr>
            <?php
                foreach ($platform_info as $key => $value ) {
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
<script src="//cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.2.0/js/tab.min.js"></script>

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
