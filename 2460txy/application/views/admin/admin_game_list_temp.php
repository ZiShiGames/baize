<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
    <table class="table">
        <tr>
          <th >游戏ID</th>
          <th >游戏名</th>
          <th >渠道名</th>
          <th >登录地址</th>
          <th >支付通知地址</th>
          <th >时间</th>
          <th >操作</th>
        </tr>

        <?php
            if (!$list || count($list) == 0) {
                echo '<h4 class="alert-danger" style="background-color:#ffffff">没找到订单</h4>';
            } else {
                foreach ($list as $one): ?>
            <tr id="<?php echo $one->game_id.'_row';
                ?>">
                <?php $data = array('one' => $one);
                $this->load->view('admin/admin_game_list_row_temp', $data)?>

            <tr>
        <?php endforeach;
            }?>
     </table>
     <span>共 <?php echo $total_page ?> 页</span>
     <a href="#" onclick="on_data_refresh(1)">首页</a>
     <a href="#" onclick="on_data_refresh(<?php if ($page > 2) {
    echo $page - 1;
} else {
    echo 1;
};?>)">上一页</a>
     <a href="#" onclick="on_data_refresh(<?php if ($page < $total_page) {
    echo $page + 1;
} else {
    echo $total_page;
} ?>)">下一页</a>
     <a href="#" onclick="on_data_refresh(<?php echo $total_page; ?>)">尾页</a>
     第 <input class="btn-xs" type="text" style="width:40px" id="page_input" value="<?php echo $page; ?>"> 页
     <button type="button" class="btn btn-default btn-xs" onclick="goto()">确定</button>
