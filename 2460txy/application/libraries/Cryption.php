<?php
defined('BASEPATH') or exit('No direct script access allowed');
/** 应用签名及请求响应数据签名类
 *  php需安装并开启Mcrypt模块（详见http://php.net/manual/en/book.mcrypt.php）
 *
 */

class Cryption
{
    private static $ignoredSigName = ["appsig", "reqsig", "paysig"];

    /** 生成请求或响应数据的签名
     *
     * @param string $uri 请求的Url
     * @param string $method 请求的方式，如GET,POST等
     * @param Object $queryMap 查询参数对象
     * @param string $appKey 应用密钥
     * @return string 返回请求或响应签名
     */
    public static function GetDataSig($uri, $method, $queryMap, $appKey)
    {
        $tuple = parse_url($uri);
        $pathname = $tuple["path"];
        $path = Cryption::UrlEncode($pathname, 1);
//var_dump($queryMap);
        ksort($queryMap);
//var_dump($queryMap);
        $kvs = array();

        foreach ($queryMap as $k => $v) {
            $isSig = 0;
            foreach (Cryption::$ignoredSigName as $sig) {
                if (strtolower($k) == strtolower($sig)) {
                    $isSig++;
                    continue;
                }
            }
            if ($isSig == 0) {
                $kvs[] = "$k=$v";
            }
        }
        $paras = implode("&", $kvs);
        $paras = Cryption::UrlEncode($paras, 1);
//echo $paras;
        $srcUrl = $method . "&" . $path . "&" . $paras;
        $srcSigKey = $appKey . "&";
        // echo "签名中间数据(PHP)---：" . $srcUrl . "<br />";
        $dataSig = Cryption::GetSig($srcUrl, $srcSigKey);
        return $dataSig;
    }

    /** 获取应用签名
     *
     * @param string $appId 应用Id
     * @param string $time  时间戳
     * @param string $nonce 随机串
     * @param string $appKey 应用密钥
     * @param string $dataKey 数据加密密钥
     * @return string 返回应用签名
     */
    public static function GetAppSig($appId, $time, $nonce, $appKey, $dataKey)
    {
        $src = $appId . "_" . $time . "_" . $nonce;
        $cipher = Cryption::GetCipherData($src, $dataKey);
        $appKey .= "&";
        $appsig = Cryption::GetSig($cipher, $appKey);
        return $appsig;
    }

    /** 获取签名
     *
     * @param string $rawData 需签名的数据
     * @param string $appKey 密钥
     * @return string 返回数据的签名
     */
    public static function GetSig($rawData, $appKey)
    {
        $sig = hash_hmac('sha1', $rawData, $appKey, true);
        $sig = base64_encode($sig);
        $sig = Cryption::UrlEncode($sig, 0);
        return $sig;
    }

    /** 获取加密密文
     *
     * @param string $rawData 需加密的明文数据
     * @param string $dataKey 密钥
     * @return  string 返回加密密文的Base64及UrlEncode后的串
     */
    public static function GetCipherData($rawData, $dataKey)
    {
        /* 已自动补齐结束符
          $blockSize = 16;
          $padding = $blockSize - (strlen($rawData) % $blockSize);
          if ($padding > 0)
          $rawData .= str_repeat("\0", $padding); //\0一定要双引号，否则自动pad的结束符没去掉 http://php.net/manual/en/function.mcrypt-decrypt.php
         */

        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $cipher = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $dataKey, $rawData, MCRYPT_MODE_ECB, $iv);

        $cipher = base64_encode($cipher);
        $cipher = Cryption::UrlEncode($cipher, 0);
        return $cipher;
    }

    /** 获取明文
     *
     * @param string $cipherData 需解密的明文
     * @param string $dataKey 密钥
     * @return string 返回解密后的明文数据（utf8格式）
     */
    public static function GetPlainData($cipherData, $dataKey)
    {
        $cipherData = urldecode($cipherData);
        $cipherData = base64_decode($cipherData);

        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $plain = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $dataKey, $cipherData, MCRYPT_MODE_ECB, $iv);
        $plain = rtrim($plain, "\0"); //\0一定要双引号，否则自动pad的结束符没去掉

        return $plain;
    }

    public static function UrlEncode($rawData, $isSignSrc)
    {
        //先将中文的unicode转换成utf-8的中文，再用urlencode将中文编码
        $rawData = preg_replace_callback("#\\\u([0-9a-f]+)#i", function ($r) {
            return iconv('UCS-2', 'UTF-8', pack('H4', $r[1]));
        }, $rawData);

        $result = rawurlencode($rawData);
        //echo $isSignSrc;
        if ($isSignSrc) {
            $result = str_replace("~", "%7E", $result, $i);
        }
        return $result;
    }
}
