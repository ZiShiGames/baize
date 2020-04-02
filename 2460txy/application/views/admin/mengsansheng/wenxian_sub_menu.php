<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

      <ul class="nav nav-pills nav-stacked">
        <!-- <li <?php// if (isset($user)) {
    //echo 'class="active"';
//} ?>><a href="/index.php/admin/user_manage">用户管理</a></li>
<li <?php// if (isset($check_orderid)) {
//    echo 'class="active"';
//} ?>><a href="/index.php/admin/check_orderId_page">玩家订单查询</a></li> -->
<li <?php if (isset($income)) {
    echo 'class="active"';
} ?>><a href="/index.php/Mengsansheng_admin_backstage/back_stage_page">收入统计</a></li>
<li <?php if (isset($liucun)) {
    echo 'class="active"';
} ?>><a href="/index.php/Mengsansheng_admin_backstage/turn_to_liucun_page">留存统计</a></li>
<li <?php if (isset($ltv)) {
    echo 'class="active"';
} ?>><a href="/index.php/Mengsansheng_admin_backstage/turn_to_ltv_page">ltv统计</a></li>
<li <?php if (isset($server)) {
    echo 'class="active"';
} ?>><a href="/index.php/Mengsansheng_admin_backstage/turn_to_server_page">区服统计</a></li>
      </ul>
