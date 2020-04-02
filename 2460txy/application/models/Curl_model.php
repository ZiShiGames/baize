<?php

class Curl_model extends CI_Model
{
    // $header must be in array
    // with header name as key and header body as value
    public function curl_get($url, $header = null)
    {
        $my_curl = curl_init();
        curl_setopt($my_curl, CURLOPT_URL, $url);
        curl_setopt($my_curl, CURLOPT_RETURNTRANSFER, 1);

        if ($header) {
            $header_list = array();
            foreach ($header as $key => $value) {
                $header_list[] = "$key: $value";
            }
            curl_setopt($my_curl, CURLOPT_HTTPHEADER, $header_list);
        }

        $str = curl_exec($my_curl);
        curl_close($my_curl);

        return $str;
    }

    public function curl_post($url, $data, $header = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        if (gettype($data) == 'array' || gettype($data) == 'object') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->to_params($data));
        } elseif (gettype($data) == 'string') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        if ($header) {
            $header_list = array();
            foreach ($header as $key => $value) {
                $header_list[] = "$key: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header_list);
        }

        $str = curl_exec($ch);
        curl_close($ch);

        return $str;
    }

    public function to_params($input)
    {
        $index = 0;
        $pair = '';
        foreach ($input as $key => $value) {
            if ($index != 0) {
                $pair .= '&';
            }
            $pair .= "$key=".$value;
            ++$index;
        }

        return $pair;
    }
}
