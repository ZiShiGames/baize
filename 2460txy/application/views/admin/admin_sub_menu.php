<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

      <ul class="nav nav-pills nav-stacked">
        <li <?php if (isset($game)) {
    echo 'class="active"';
} ?>><a href="/index.php/admin/game_manage">游戏管理</a></li>
<li <?php if (isset($platform)) {
    echo 'class="active"';
} ?>><a href="/index.php/admin/platform_manage">渠道管理</a></li>
        <li <?php if (isset($user)) {
    echo 'class="active"';
} ?>><a href="/index.php/admin/user_manage">用户管理</a></li>
		<li <?php if (isset($info)) {
    echo 'class="active"';
} ?>><a href="/index.php/Test_report/info_tongji">信息统计（通过创角）</a></li>

<li <?php if (isset($sign)) {
    echo 'class="active"';
} ?>><a href="/index.php/Test_report/sign_tongji">信息统计（通过注册）</a></li>
<li <?php if (isset($check_orderid)) {
    echo 'class="active"';
} ?>><a href="/index.php/admin/check_orderId_page">玩家订单查询</a></li>
<li <?php if (isset($income)) {
    echo 'class="active"';
} ?>><a href="/index.php/Admin_report">收入统计</a></li>
<li <?php if (isset($server)) {
    echo 'class="active"';
} ?>><a href="/index.php/Admin_report/turn_to_server_info_page">区服统计</a></li>
<li <?php if (isset($month_data)) {
    echo 'class="active"';
} ?>><a href="/index.php/Admin_login/turn_to_month_data_page">月总统计</a></li>

<li <?php if (isset($liucun)) {
    echo 'class="active"';
} ?>><a href="/index.php/Admin_login/turn_to_liucun_page">留存统计</a></li>
<li <?php if (isset($ltv)) {
    echo 'class="active"';
} ?>><a href="/index.php/Admin_login/turn_to_ltv_page">ltv统计</a></li>


<li <?php if (isset($admin_tool)) {
    echo 'class="active"';
} ?>><a href="/index.php/admin/admin_tool"  >管理员工具</a></li>

<li> <a href="/index.php/admin/fake"  > FAKE </a></li>
      </ul>
