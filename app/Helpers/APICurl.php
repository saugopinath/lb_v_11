<?php


namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class APICurl
{
    public static function callingAPI($api_url, $headers, $data_string)
    {   
        $curl = curl_init($api_url);
        header("Access-Control-Allow-Origin: *");
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        $errorCurl = curl_error($curl);

        curl_close($curl);
        $response=array('result'=>$result,'errorCurl'=>$errorCurl);
        return  $response;
    }

    public static function callingAPIForSR($api_url, $headers, $data_string)
    {   
        $curl = curl_init($api_url);
        // header("Access-Control-Allow-Origin: *");
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        $errorCurl = curl_error($curl);

        curl_close($curl);
        $response=array('result'=>$result,'errorCurl'=>$errorCurl);
        return  $response;
    }
}
