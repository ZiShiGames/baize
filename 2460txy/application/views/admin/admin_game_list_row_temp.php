<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<td><?php echo $one->game_id;
    ?></td>
<td><?php echo $one->game_name;
    ?></td>
<td><?php
    $this->load->model('db/Platform_model');
    $condition = array('platform' => $one->platform);
    $platform = $this->Platform_model->get_one_by_condition($condition);
    if ($platform) {
        echo $platform->platform_chinese.'('.$platform->platform.')';
    } else {
        echo '未找到 '.$one->platform;
    }
    ?></td>
<td><?php
// echo $one->game_login_url;

?></td>
<td><?php
//echo $one->game_pay_nofity;
?></td>
<td><?php echo date('Y-m-d H:i:s', $one->create_date);
?></td>
<td>

    <a type="button" class="btn btn-primary btn-xs" onclick="edit_game(<?php echo $one->game_id;?>)">编辑</a>

    <?php if ($one->status == 1) {
    ?>
        <button type="button" class="btn btn-danger btn-xs" onclick="turn_off_game(<?php echo $one->game_id;
    ?>)">禁用</button>
    <?php

} else {
    ?>
        <button type="button" class="btn btn-success btn-xs" onclick="turn_on_game(<?php echo $one->game_id;
    ?>)">启用</button>
    <?php

} ?>
</td>
