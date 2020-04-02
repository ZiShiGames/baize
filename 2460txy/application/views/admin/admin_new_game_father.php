<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta id="viewport" name="viewport" content="target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0" />
        <title>新建游戏</title>
        <link href="/css/bootstrap.min.css" rel="stylesheet">
        <script src="/js/jquery.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">
            <h1>新建游戏</h1>
            <form action="/index.php/admin/create_game_father" onsubmit="return validate_form()" method="post" enctype="multipart/form-data">
              <div class="form-group">
                <label for="game_name">游戏名</label>
                <input name="game_name" type="text" class="form-control" id="game_name" placeholder="">
              </div>

              <button type="submit" class="btn btn-primary btn-lg">提交</button>
            </form>
        </div>

    </body>
    <script type="text/javascript">
        function validate_form() {
            console.log("validate_form");

            var list = ["game_name"];
            for(var index = 0; index < list.length; index++){
                var field = $("#" + list[index]).val();
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

    </script>
</html>
