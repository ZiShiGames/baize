<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta id="viewport" name="viewport" content="target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0" />
        <title>新建渠道</title>
        <link href="/css/bootstrap.min.css" rel="stylesheet">
        <script src="/js/jquery.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
        <script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.js"></script>
        <script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.min.js"></script>
        <script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.ie8polyfill.js"></script>
        <script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.ie8polyfill.min.js"></script>
        <script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.widgets.helper.js"></script>
        <script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.widgets.helper.min.js"></script>
        <link href="http://cdn.amazeui.org/amazeui/2.7.2/css/amazeui.css" rel="stylesheet">
        <link href="http://cdn.amazeui.org/amazeui/2.7.2/css/amazeui.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <h1>新建游戏</h1>
            <form action="/index.php/admin/create_game" onsubmit="return validate_form()" method="post" enctype="multipart/form-data">
            <!-- <form action="/index.php" onsubmit="return validate_form()" method="post" enctype="multipart/form-data"> -->
              <div class="form-group">
                <label for="game_father_id">游戏名</label>
                <select class="form-control" name="game_father_id">
                    <?php foreach ($fathers as $one): ?>
                        <option value="<?php echo $one->game_father_id ?>"><?php echo $one->game_father_name ?></option>
                    <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="platform">渠道名</label>
                <select class="form-control" name="platform" id="platform" onchange="platform_key()" data-am-selected="{searchBox: 1}">
                    <?php foreach ($platforms as $one): ?>
                        <option value="<?php echo $one->platform ?>"><?php echo $one->platform_chinese ?></option>
                    <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="game_login_url">游戏登录地址</label>
                <input name="game_login_url" type="text" class="form-control" id="game_login_url" placeholder="game_login_url">
              </div>
              <div class="form-group">
                <label for="game_pay_nofity">游戏支付回调完整地址</label>
                <input name="game_pay_nofity" type="text" class="form-control" id="game_pay_nofity" placeholder="game_pay_nofity">
              </div>

              <div id="keys">
                  <?php foreach ($platform_key as $one): ?>

                    <div class="form-group">
                      <label for="<?php echo $one ?>"><?php echo $one ?></label>
                      <input name="<?php echo $one ?>" type="text" class="form-control" id="<?php echo $one ?>" placeholder="<?php echo $one ?>">
                    </div>
                  <?php endforeach; ?>
              </div>

              <button type="submit" class="btn btn-primary btn-lg">提交</button>
            </form>
        </div>

    </body>
    <script type="text/javascript">
        function validate_form() {
            console.log("validate_form");
            var re =/\s/g;
            var list = ["game_login_url", "game_pay_nofity"];
            for(var index = 0; index < list.length; index++){
                var field = $("#" + list[index]).val();
                if(re.test(field)){
                    alert('不要有空格！！！');
                    return false;
                }

                if(field == null || field == ""){
                    alert(list[index] + "为空");
                    return false;
                }
            }
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

        function platform_key() {
            var platform = $("#platform").val();
            console.log("platform " + platform);
            var url = "/index.php/admin/platform_keys?platform="+platform;
            var re =/\s/g;
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
