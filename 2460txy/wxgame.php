<?php
if (isset($_GET['code'])){
	header('HTTP/1.1 302 Moved Permanently');
    if($_REQUEST['state']){
		header('Location: http://api.baizegame.com/login/gowxgame?code='.$_REQUEST['code'].'&type='.$_REQUEST['state']);
	}else{
        echo "NO CODE";
    }
}else{
    echo "NO CODE";
}
?>