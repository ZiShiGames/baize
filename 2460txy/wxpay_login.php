<?php
if (isset($_GET['code'])){
	header('HTTP/1.1 302 Moved Permanently');
	if($_REQUEST['state']){
	    $url = 'http://api.baizegame.com/login/save_pay_openid?code='.$_REQUEST['code'].'&type='.$_REQUEST['state'];
        header("Location:".$url);
//	    $this->load->model('Curl_model');
//	    $res = $this->Curl_model->curl_get($url);
	}else{
        echo "NO CODE";
    }
}else{
    echo "NO CODE";
}
?>