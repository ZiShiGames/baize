<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta id="viewport" name="viewport" content="target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0" />
        <title>编辑渠道游戏</title>
        <link href="/css/bootstrap.min.css" rel="stylesheet">
        <script src="/js/jquery.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">
            <h1>编辑渠道游戏</h1>
            <form action="/index.php/admin/create_game" onsubmit="return validate_form()" method="post" enctype="multipart/form-data">
              <div class="form-group">
                <label for="gane_name">游戏名</label>
                <input name="gane_name" type="text" class="form-control" id="gane_name" value="<?php echo $game_name ?>" readonly="true">
                <input name="game_father_id" type="text" class="form-control" id="game_father_id" value="<?php echo $game_father_id ?>" readonly="true">
              </div>
              <div class="form-group">
                <label for="platform">渠道名</label>
                <input name="platform" type="text" class="form-control" id="platform"  value="<?php echo $platform ?>" readonly="true">
              </div>

              <div class="form-group">
                <label for="game_login_url">游戏登录地址(研发提供)</label>
                <div class="input-group">
                    <input name="game_login_url" type="text" class="form-control" id="game_login_url"  value="<?php echo $game_login_url ?>" readonly="true">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="enable('game_login_url')">修改</button>
                    </span>
                </div>

              </div>
              <div class="form-group">
                <label for="game_pay_nofity">游戏支付回调完整地址(研发提供)</label>
                <div class="input-group">
                <input name="game_pay_nofity" type="text" class="form-control" id="game_pay_nofity"  value="<?php echo $game_pay_nofity ?>" readonly="true">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="button" onclick="enable('game_pay_nofity')">修改</button>
                </span>
                </div>
              </div>


                <div class="form-group">
                  <label for="game_login_url">游戏登录地址(提供给渠道)</label>
                      <input name="" type="text" class="form-control" id=""  value="<?php
                      // if ($game_father_id==20014) {
                      //     echo "http://xjl.fengzhangame.net/index.php/enter/play/$platform/$game_id";
                      // } elseif ($game_father_id==20017) {
                      //     echo "http://ldqmx.xileyougame.com/index.php/enter/play/$platform/$game_id";
                      // } else {
                          echo "http://".$_SERVER['HTTP_HOST']."/index.php/enter/play/$platform/$game_id";
                      // }
                      ?>" readonly="true">

                </div>
                <div class="form-group">
                  <label for="game_pay_nofity">游戏支付回调完整地址(提供给渠道)</label>
                  <input name="" type="text" class="form-control" id=""  value="<?php
                  // if ($game_father_id==20014) {
                  //     echo "http://xjl.fengzhangame.net/index.php/api/notify/$platform/$game_id";
                  // } elseif ($game_father_id==20017) {
                  //     echo "http://ldqmx.xileyougame.com/index.php/api/notify/$platform/$game_id";
                  // } else {
                      echo "http://".$_SERVER['HTTP_HOST']."/index.php/api/notify/$platform/$game_id";
                  // }
                  ?>" readonly="true">
                </div>

              <div id="keys">

                  <?php

                  if (isset($platform_key)) {
                      $keys = json_decode($platform_key);
                      if ($keys) {
                          foreach ($keys as $key => $value): ?>
                              <div class="form-group">
                                <label for="<?php echo $key ?>"><?php echo $key ?></label>
                                <div class="input-group">
                                <input name="<?php echo $key ?>" type="text" class="form-control" id="<?php echo $key ?>" value="<?php echo $value ?>" readonly="true">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="enable('<?php echo $key ?>')">修改</button>
                                </span>
                                </div>
                              </div>
                          <?php endforeach;
                      }
                  }?>

                  <?php
                  if (isset($platform_key)) {
                      $keys = json_decode($platform_key);
                      if ($keys) {
                          foreach ($keys as $key => $value) {
                              echo '<div class="form-group">';
                              echo '';
                              echo '</div>';
                          }
                      }
                  }
                  ?>
              </div>

              <button type="submit" class="btn btn-primary btn-lg">提交</button>
            </form>
        </div>

    </body>
    <script type="text/javascript">
        function validate_form() {
            console.log("validate_form");

            // var list = ["game_login_url", "game_pay_nofity"];
            // for(var index = 0; index < list.length; index++){
            //     var field = $("#" + list[index]).val();
            //     if(field == null || field == ""){
            //         alert(list[index] + "为空");
            //         return false;
            //     }
            // }
            return true;

        }

        function preview(x) {
            console.log("preview");
            if(!x || !x.value) return;
            var patn = /\.jpg$|\.jpeg$|\.png$/i;
            if(patn.test(x.value)){
                console.log("ok");
                img = new Image();
                img.onload = function () {
                    console.log(this.width + " " + this.height);
                };
                var _URL = window.URL || window.webkitURL;
                img.src = _URL.createObjectURL(x.files[0]);
                $("#"+x.id+"_preview").attr("src", img.src);

            } else {
                console.log("error");
            }
        }

        function enable(name) {
            console.log("enable " + $("#" + name).val());
            $("#" + name).removeAttr("readonly");
        }

        function platform_key() {
            var platform = $("#platform").val();
            console.log("platform " + platform);
            var url = "/index.php/admin/platform_keys?platform="+platform;

            $.ajax({
                url: url,
                success: function (data) {
                    if(data.c == -1 ){
                        alert('登录超时');
                        window.location.href = '/';
                        return;
                    } else if (data.c == 0) {
                        var keys = data.d.split(",");
                        $("#keys").html('');
                        var html = '';
                        for(var index = 0; index < keys.length; index++){
                            console.log("key : " + keys[index]);
                            html += '<div class="form-group">';
                            html += '<label for="'+keys[index]+'">'+keys[index]+'</label>'
                            html += '<input name="'+keys[index]+'" type="text" class="form-control" id="'+keys[index]+'" placeholder="'+keys[index]+'">';
                            html += '</div>';





                        }
                        $("#keys").html(html);
                    } else {
                        alert('错误:' + data.m);
                    }
                },
                dataType: 'json'
            }
            );


        }

    </script>
</html>
