<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

      <ul class="nav nav-pills nav-stacked">
          <li><h4>数据查询</h4></li>
        <li <?php if (isset($data_info)) {
    echo 'class="active"';
} ?>><a href="/index.php/Admin_backstage/back_stage_page" >数据统计</a></li>
        <li <?php if (isset($mounth)) {
    echo 'class="active"';
} ?>><a href="/index.php/Admin_backstage/back_stage_mounth_page">月总结</a></li>
      </ul>
