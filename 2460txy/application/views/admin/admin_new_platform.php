<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta id="viewport" name="viewport" content="target-densitydpi=high-dpi,uc-fitscreen=yes,width=device-width, initial-scale=1.0" />
        <title>增加渠道</title>
        <link href="/css/bootstrap.min.css" rel="stylesheet">
        <script src="/js/jquery.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">
            <h1>增加渠道</h1>
            <form action="/index.php/admin/create_platform" onsubmit="return validate_form()" method="post" enctype="multipart/form-data">
              <div class="form-group">
                <label for="platform_chinese">渠道名</label>
                <input name="platform_chinese" type="text" class="form-control" id="platform_chinese" placeholder="">
              </div>

              <div class="form-group">
                <label for="platform">渠道拼音缩写</label>
                <input name="platform" type="text" class="form-control" id="platform" placeholder="">
              </div>

              <div class="form-group">
                <label for="platform_key">渠道关键信息(例: key, secret 等,用逗号分隔关键字段 - 实际输入例： appid,gameId,key)</label>
                <input name="platform_key" type="text" class="form-control" id="platform_key" placeholder="key,secret">
              </div>

              <button type="submit" class="btn btn-primary btn-lg">提交</button>
            </form>
        </div>

    </body>
    <script type="text/javascript">
        function validate_form() {
            console.log("validate_form");

            var list = ["platform_chinese", "platform", "platform_key"];
            for(var index = 0; index < list.length; index++){
                var field = $("#" + list[index]).val();
                if(field == null || field == ""){
                    alert(list[index] + "不能为空");
                    return false;
                }
            }
            return true;

        }

    </script>
</html>
