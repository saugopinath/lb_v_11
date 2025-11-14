<?php


namespace App\Helpers;

use App\LotGenerationFunctionMaster;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\SchemeCapacity;
use App\Scheme;

class DupCheck
{
    public static function getDupCheckBank($scheme_id,$bank_code){
        $serverip = 'http://172.25.152.163';
        if($scheme_id == 10){
            $post_url = $serverip.'/api/dupCheckBankOAP';
        }else if($scheme_id == 3){
            $post_url = $serverip.'/api/dupCheckBankBandhu';
        }else if($scheme_id == 1){
            $post_url = $serverip.'/api/dupCheckBankJohar';
        }
        $curl = curl_init($post_url);
        $headers = array(
        'Content-Type: application/json'
        );
        $data = array("bank_code" => $bank_code, "scheme_id" => $scheme_id);
        $data_string = json_encode($data);
            header("Access-Control-Allow-Origin: *");
            curl_setopt($curl, CURLOPT_URL, $post_url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            $post_response = curl_exec($curl);
            if ($post_response) {
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
                if ($httpcode == 200) {
                   $post_response_lb = json_decode($post_response);
                   $is_dup = $post_response_lb->is_dup;
                  if($is_dup == 1){
                    $beneficiary_id = $post_response_lb->beneficiary_id;
                    return $beneficiary_id;
                  }
                }
              }
    }
    public static function getDupCheckAadhar($scheme_id,$aadhar_no){
    $serverip = 'http://172.25.152.163';
    if($scheme_id == 10){
        $post_url = $serverip.'/api/dupCheckAadharOAP';
    }else if($scheme_id == 3){
        $post_url = $serverip.'/api/dupCheckAadharBandhu';
    }else if($scheme_id == 1){
        $post_url = $serverip.'/api/dupCheckAadharJohar';
    }
    // dd($post_url);
    $curl = curl_init($post_url);
    $headers = array(
    'Content-Type: application/json'
    );
    $data = array("aadhar_no" => $aadhar_no, "scheme_id" => $scheme_id);
    $data_string = json_encode($data);
        header("Access-Control-Allow-Origin: *");
        curl_setopt($curl, CURLOPT_URL, $post_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $post_response = curl_exec($curl);
        //  $beneficiary_id='2323323';
        // return $beneficiary_id;
        //  dd($post_response);
        if ($post_response) {
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($httpcode == 200) {
                $post_response_lb = json_decode($post_response);
                $is_dup = $post_response_lb->is_dup;
                if($is_dup == 1){
                    $beneficiary_id = $post_response_lb->beneficiary_id;
                    return $beneficiary_id;
                }
            }
        }
    }
    // public static function geDupCheckBankBandhu($scheme_id,$bank_code){
    //     $serverip = 'http://172.25.152.163';
    //     $post_url = $serverip.'/api/dupCheckBankBandhu';
    //         $curl = curl_init($post_url);
    //     $headers = array(
    //     'Content-Type: application/json'
    //     );
    //     $data = array("bank_code" => '765755875', "scheme_id" => '10');
    //     $data_string = json_encode($data);
    //         header("Access-Control-Allow-Origin: *");
    //         curl_setopt($curl, CURLOPT_URL, $post_url);
    //         curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    //         curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    //         $post_response = curl_exec($curl);
    //         dd($post_response);
    // }
    // public static function geDupCheckBankJohar($scheme_id,$bank_code){
    //     $serverip = 'http://172.25.152.163';
    //     $post_url = $serverip.'/api/dupCheckBankJohar';
    //         $curl = curl_init($post_url);
    //     $headers = array(
    //     'Content-Type: application/json'
    //     );
    //     $data = array("bank_code" => '765755875', "scheme_id" => '10');
    //     $data_string = json_encode($data);
    //         header("Access-Control-Allow-Origin: *");
    //         curl_setopt($curl, CURLOPT_URL, $post_url);
    //         curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    //         curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    //         $post_response = curl_exec($curl);
    //         dd($post_response);
    // }
}