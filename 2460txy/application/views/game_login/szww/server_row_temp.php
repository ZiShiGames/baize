<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class='items item ma cw fs3 <?php echo $hide ?>' id='game<?php echo $group ?>'
    style='height: 50px;z-index: 0;position: relative;line-height:50px;'
    onclick='jumpTo(<?php echo $server->status;?>,"<?php
        $furl = $url.'&serverId='.$server->id.'&server_name='.$server->name;
        if (isset($server->pfid)) {
            $furl .= '&pfid='.$server->pfid;
        }
        echo $furl;
    ?>",<?php echo $server->id ?>);' >
    <?php

    // jinb special
    if ($server->id >= 5001 && $server->id <= 6000) {
        echo $server->name;
    } else {
        echo $game_name.$server->name;
    }

     ?>
    <?php
        if ($server->status == 1) {
            $class = 'new';
        } elseif ($server->status == 2) {
            $class = 'hot';
        } elseif ($server->status == 3) {
            $class = 'off';
        } else {
            $class = '';
        }

     ?>
    <div class='<?php echo $class ?>'></div>
</div>
