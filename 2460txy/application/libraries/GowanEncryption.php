<?php
class GowanEncryption
{
    private static $scramble1  = "! #$%&()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{|}~";  //定义ASCII码表 
    private static $scramble2  = "f^jAE]okIOzU[2&q1{3`h5w_794p@6s8?BgP>dFV=m D<TcS%Ze|r:lGK/uCy.Jx)HiQ!#$~(;Lt-R}Ma,NvW+Ynb*0X";  //定义ASCII码表  

    public static $errors = [] ;       //定义错误信息数组结构
    public static $adj = 1.75;  //校验值 
    public static $mod = 3;     //模式 
    /**
     * 解密
     */
    public static function decode ($str, $key = "cd2c770f632afd6f78e3fdd36ed6879c")
    {
        self::$errors = array();

        $fudgefactor = self::_convertKey($key);
        if (self::$errors) return;

        if (empty($str)) {
            self::$errors[] = 'No value has been supplied for decryption';
            return;
        } 

        $str = rawurldecode($str);

        $target = null;
        $factor2 = 0;

        for ($i = 0; $i < strlen($str); $i++) {
            $char2 = mb_substr($str, $i, 1);

            // 找出$char2在scramble2的位置
            $num2 = strpos(self::$scramble2, $char2);
            if ($num2 === false) {
                self::$errors[] = "str string contains an invalid character ($char2)";
                return;
            } 

            $adj     = self::_applyFudgeFactor($fudgefactor);

            $factor1 = $factor2 + $adj;                 
            $num1    = $num2 - round($factor1);         
            $num1    = self::_checkRange($num1);       
            $factor2 = $factor1 + $num2;                

            $char1 = mb_substr(self::$scramble1, $num1, 1);
            $target .= $char1;

        }
        return rtrim($target);

    } 

    /*
     *  加密
     */
    public static function encode ($str, $key = "cd2c770f632afd6f78e3fdd36ed6879c", $strlen = 50)
    {

        $fudgefactor = self::_convertKey($key);
        if (self::$errors) return;

        if (empty($str)) {
            self::$errors[] = 'No value has been supplied for encryption';
            return;
        } 

        $str = str_pad($str, $strlen);

        $target = null;
        $factor2 = 0;

        for ($i = 0; $i < strlen($str); $i++) {
            $char1 = mb_substr($str, $i, 1);

            // 找出$char1在scramble1的位置
            $num1 = strpos(self::$scramble1, $char1);
            if ($num1 === false) {
                self::$errors[] = "str string contains an invalid character ($char1)";
                return;
            }

            $adj     = self::_applyFudgeFactor($fudgefactor);

            $factor1 = $factor2 + $adj;             // accumulate in $factor1
            $num2    = round($factor1) + $num1;     // generate offset for $scramble2
            $num2    = self::_checkRange($num2);   // check range
            $factor2 = $factor1 + $num2;            // accumulate in $factor2

            $char2 = mb_substr(self::$scramble2, $num2, 1);
            $target .= $char2;

        }
        return rawurlencode( $target );
    } 


    
    private static function _applyFudgeFactor (&$fudgefactor)
    {
        $fudge = array_shift($fudgefactor);     // 从数组中提取第一个数
        $fudge = $fudge + self::$adj;           
        $fudgefactor[] = $fudge;                

        if (!empty(self::$mod)) {               
            if ($fudge % self::$mod == 0) {     
                $fudge = $fudge * -1;           
            } 
        }
        return $fudge;
    } 


    /*
     *  校验num在ASCII码表的位置
     */
    public static function _checkRange ($num)
    {
        $num = round($num);         

        $limit = strlen(self::$scramble1);

        while ($num >= $limit) {
            $num = $num - $limit;   
        } 
        while ($num < 0) {
            $num = $num + $limit;   
        } 

        return $num;
    }


    /*
     *  转换key为ASCII码表的值
     *  return array
     */
    public static function _convertKey ($key)
    {
        if (empty($key)) 
        {
            return;
        }

        $array[] = strlen($key);

        $tot = 0;
        for ($i = 0; $i < strlen($key); $i++) 
        {
            $char = mb_substr($key, $i, 1);
            $num  = strpos(self::$scramble1, $char);

            if ($num === false) {
                return;
            }

            $array[] = $num;       
            $tot = $tot + $num;    
        }
        $array[] = $tot;
        return $array;
    }
}