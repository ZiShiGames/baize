<?php
if (isset($_GET['code'])){
	header('HTTP/1.1 302 Moved Permanently');
	if(preg_match('/[a-zA-Z]/',$_REQUEST['state'])){
		header('Location: http://api.baizegame.com/Wx_minigame/wechatOpenid?code='.$_REQUEST['code'].'&openid='.$_REQUEST['state']);
	}else{
		header('Location: http://api.baizegame.com/Wx_minigame/wechatGameOpenid?code='.$_REQUEST['code'].'&openid='.$_REQUEST['state']);
	}
}else{
    echo "NO CODE";
}
?>