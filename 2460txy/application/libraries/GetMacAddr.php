<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
**/
class GetMacAddr
{
    public $return_array = array(); // 返回带有 MAC 地址的字串数组
       public $mac_addr;

    public function GetMacAddrr($os_type)
    {
        switch (strtolower($os_type)) {
                       case "linux":
                               $this->forLinux();
                               break;
                       case "solaris":
                               break;
                       case "unix":
                               break;
                       case "aix":
                               break;
                       default:
                               $this->forWindows();
                               break;
               }

        $temp_array = array();
        foreach ($this->return_array as $value) {
            if (preg_match("/[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f]/i", $value, $temp_array)) {
                $this->mac_addr = $temp_array[0];
                break;
            }
        }
        unset($temp_array);
        return $this->mac_addr;
    }

    public function forWindows()
    {
        @exec("ipconfig /all", $this->return_array);
        if ($this->return_array) {
            return $this->return_array;
        } else {
            $ipconfig = $_SERVER["WINDIR"]."system32ipconfig.exe";
            if (is_file($ipconfig)) {
                @exec($ipconfig."/all", $this->return_array);
            } else {
                @exec($_SERVER["WINDIR"]."systemipconfig.exe /all", $this->return_array);
            }
            return $this->return_array;
        }
    }

    public function forLinux()
    {
        @exec("ifconfig -a", $this->return_array);
        return $this->return_array;
    }
}
