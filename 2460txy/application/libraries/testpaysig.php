<?php

include_once 'cryption.php';

$appKey = "0123456789012345";
$queryMap = array(
    "appsig" => "a", //签名测试
    "reqsig" => "b", //签名测试
    "paysig" => "c", //签名测试
    "urlchars" => " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~", //%20-%4e的字符
    "chinese" => "QQ开放平台", //中文测试
    "mixchars" => "中文里面有abc，还有数字987234及全角标点！，。。", //混字符测试
    "fullchar" => "。，！‘’；，、（）｛｝“”……￥", //全角测试
    "unicodechar" => "\u94bb\u77f3", //unicode测试
    "appid" => "7040504412",
    "qbopenid" => "n7k3toGbDrkteG9QalqI0mH_O0S5XdI8ZGUDZv6xcA_3Vr9xCcXBTQ",
    "qbopenkey" => "Ys4Lf4PYWDbiFuIFHG6ydOiFEwKlEIyr43mmRySbOTeZbc_C8vG6ucDyM4Qxe2uT4x0Ik24pkABmdgJ_mFXSNHyLGKzSdUrpIyo8nxUEzjCgo-miF5iSFNFf_k3R5ySCTkZQRgd-lWQ",
    "payItem" => "1:\u94bb\u77f3",
    "payInfo" => "\u94bb\u77f3",
    "reqTime" => 1445931350,
    "customMeta" => "2015102710033741459"
);

$paysig = Cryption::GetDataSig("/", "POST", $queryMap, $appKey);
echo "<br />签名结果(PHP)---：" . $paysig;


//请用以下签名结果进行比对：
//签名中间数据(PHP)--：POST&%2F&appid%3D7040504412%26chinese%3DQQ%E5%BC%80%E6%94%BE%E5%B9%B3%E5%8F%B0%26customMeta%3D2015102710033741459%26fullchar%3D%E3%80%82%EF%BC%8C%EF%BC%81%E2%80%98%E2%80%99%EF%BC%9B%EF%BC%8C%E3%80%81%EF%BC%88%EF%BC%89%EF%BD%9B%EF%BD%9D%E2%80%9C%E2%80%9D%E2%80%A6%E2%80%A6%EF%BF%A5%26mixchars%3D%E4%B8%AD%E6%96%87%E9%87%8C%E9%9D%A2%E6%9C%89abc%EF%BC%8C%E8%BF%98%E6%9C%89%E6%95%B0%E5%AD%97987234%E5%8F%8A%E5%85%A8%E8%A7%92%E6%A0%87%E7%82%B9%EF%BC%81%EF%BC%8C%E3%80%82%E3%80%82%26payInfo%3D%E9%92%BB%E7%9F%B3%26payItem%3D1%3A%E9%92%BB%E7%9F%B3%26qbopenid%3Dn7k3toGbDrkteG9QalqI0mH_O0S5XdI8ZGUDZv6xcA_3Vr9xCcXBTQ%26qbopenkey%3DYs4Lf4PYWDbiFuIFHG6ydOiFEwKlEIyr43mmRySbOTeZbc_C8vG6ucDyM4Qxe2uT4x0Ik24pkABmdgJ_mFXSNHyLGKzSdUrpIyo8nxUEzjCgo-miF5iSFNFf_k3R5ySCTkZQRgd-lWQ%26reqTime%3D1445931350%26unicodechar%3D%E9%92%BB%E7%9F%B3%26urlchars%3D%20%21%22%23%24%25%26%27%28%29%2A%2B%2C-.%2F0123456789%3A%3B%3C%3D%3E%3F%40ABCDEFGHIJKLMNOPQRSTUVWXYZ%5B%5C%5D%5E_%60abcdefghijklmnopqrstuvwxyz%7B%7C%7D%7E
//签名结果(PHP)--：pZgHxQyRzuq96kr52ni2JOpwA8U%3D
