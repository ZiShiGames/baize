<?php

//echo phpinfo();
//die();

include 'cryption.php';
include 'demo.php';


//开发者配置信息
$appId = "test";
$appKey = "testtesttesttest";
$dataKey = "testtesttesttest";

//激活断言，并设置它为 quiet
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 1);

echo ("*** Tencent QBH5Game Server SDK Test - PHP ***<br />");

//生成应用签名
$time = "1431054282";
$nonce = "teeeeeee";
$targetSig = "joLIOEyEPAxeSW%2FEFa1kBjMF8uQ%3D";
$appsig = Cryption::GetAppSig($appId, $time, $nonce, $appKey, $dataKey);
echo("===GetAppSig:  " . $appsig . " - " . $targetSig . "<br />");
assert('$appsig==$targetSig', "GetAppSig NotEqual");


//加密数据
$rawData = "this is test Data我是中文0123456789";
$targetCipherData = "vtooUMTy%2Besed7IzlS1uN0jfdUAcfXmlNdoLx6KOqZH2IS8AslxGAktdQDdWRq%2Ba"; 
$cipherData = Cryption::GetCipherData($rawData, $dataKey);
echo("===GetCipherData:  " . $cipherData . " - " . $targetCipherData . "<br />");
assert('$cipherData==$targetCipherData', "GetCipherData NotEqual");


//数据签名
$uri = "http://cptest.cs0309.html5.qq.com/index?action=inquiry&data=$cipherData&reqsig=abcdefg&appsig=012456";
$method = "GET";
$queryMap = array("action" => "inquiry", "data" => $cipherData, "reqsig" => "abcdefg", "appsig" => "012456");
$targetDataSig = "Zwa0FvhCcgsEthm9x2S9ocZHS6k%3D";

$datasig = Cryption::GetDataSig($uri, $method, $queryMap, $appKey);
echo("===GetDataSig: " . $datasig . " - " . $targetDataSig . "<br />");
assert('$datasig==$targetDataSig', "GetDataSig NotEqual");


//数据解密
$plainData = Cryption::GetPlainData($cipherData, $dataKey);
echo("===GetPlainData: " . $plainData . " - " . $rawData . "<br />");
assert('$plainData==$rawData', "GetPlainData NotEqual");


//UrlEncode测试
echo "<br />";
$rawWord = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~";
$result = Cryption::UrlEncode($rawWord, 1);
echo "result:" . $result . "<br />";
assert($result == '%20%21%22%23%24%25%26%27%28%29%2A%2B%2C-.%2F0123456789%3A%3B%3C%3D%3E%3F%40ABCDEFGHIJKLMNOPQRSTUVWXYZ%5B%5C%5D%5E_%60abcdefghijklmnopqrstuvwxyz%7B%7C%7D%7E', "UrlEncode NotEqual");

$raw = urldecode($result);
assert($raw == $rawWord, "UrlEncode NotEqual");
echo " result:" . $raw . "<br />";


echo "<hr>";

