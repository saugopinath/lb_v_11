<?php

namespace App\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
trait TraitCMO {
    public function login(){
        $return_status=array();
        $post_url = 'http://172.20.52.16:443/cmosvc/user/generateotp/';
        $data = array("user_name" => "9559000099");
        $data_string = json_encode($data);
        
        $curl_otp = curl_init($post_url);
        curl_setopt($curl_otp, CURLOPT_URL, $post_url);
        curl_setopt($curl_otp, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_otp, CURLOPT_POST, 1);
        curl_setopt($curl_otp, CURLOPT_POSTFIELDS, $data_string);
        $headers = array(
                'Content-Type: application/json'
         );
        curl_setopt($curl_otp, CURLOPT_HTTPHEADER, $headers);
        $post_response = curl_exec($curl_otp);
        if ($post_response === false) {
            $error   = curl_errno($curl_otp);
            $message = curl_error($curl_otp);
            dump($error);dump($message);
            curl_close($curl_otp);
            $return_status['code']=-900;
        }
        $statusCode = curl_getinfo($curl_otp, CURLINFO_HTTP_CODE);
        curl_close($curl_otp);
        if($statusCode != 200) {
            dump($statusCode);dump($post_response);
            //error_log('STATUS CODE', $statusCode . ' ' . $post_response);
        }

      
        $cmo_otp_status=0;
    
        curl_close($curl_otp);
        $post_response = json_decode($post_response);
        if ($post_response->Exception == false && $post_response->Errors == null) {
            $cmo_otp_status=1; 
           
        } elseif (
            $post_response->Exception == true &&
            isset($post_response->Errors->Info[0]->Code) &&
            $post_response->Errors->Info[0]->Code == "IN043"
        ) {
            $cmo_otp_status=1; 
        } else {
            $return_status['msg']=$post_response->Errors->Info[0]->Message;
            $return_status['code']=-1;
        }
        if($cmo_otp_status==1){
            $httpcode=NULL;$response_text=NULL;$message=NULL; $message=NULL;$tokenRefId=NULL;$token=NULL;
            $post_url = 'http://172.20.52.16:443/cmosvc/user/login/';
            // $headers = array(
            //     'Content-Type: application/json'
            // );
            $data = array("user_name" =>"9559000099","otp" => "191232","login_as_position" => "14556");
            $data_string = json_encode($data);
            $curl_login = curl_init($post_url);
            curl_setopt($curl_login, CURLOPT_URL, $post_url);
            curl_setopt($curl_login, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_login, CURLOPT_POST, 1);
            curl_setopt($curl_login, CURLOPT_POSTFIELDS, $data_string);
            $headers = array(
                'Content-Type: application/json'
             );
            curl_setopt($curl_login, CURLOPT_HTTPHEADER, $headers);
            $errorCurl = curl_error($curl_login);
            $post_response = curl_exec($curl_login);
            //dump($post_response);
            if (curl_errno($curl_login)) {
                $response_text = curl_error($curl_login);
                $return_status['msg']=$response_text;
                $return_status['code']=-2;
            }
            else{
                $post_response = json_decode($post_response, true);
                $response_text = $post_response; 
                $httpcode = curl_getinfo($curl_login, CURLINFO_HTTP_CODE);
                curl_close($curl_login);
                if($httpcode==200){
                    if (isset($response_text['Token'])) {
                        $token = $response_text['Token'];
                        Cache::put('CMO_validate_token',$token); 
                        $return_status['code']=100;
                        $return_status['token']=$token;
                    }
                }  
                else{
                    $return_status['code']=-3;
                }  
           }
        }
        return $return_status;
       
    }
    public function submitATR($data){
        $return_arr=array();
        $token_found=0;
        $token_check_status_arr= $this->login();
        if($token_check_status_arr['code']==100){
            $token_found=1;
            $token=$token_check_status_arr['token'];
            $token_found=1;
          
        }
        if($token_found==1){
           // dump($token); dump($data);
            $post_url = 'http://172.20.52.16:443/cmosvc/shared/wcdpushgrievatr/';
            $curl = curl_init($post_url);
            $headers = array(
                'Content-Type: application/json',
                'Authorization:'.$token,
            );
            $json_data = json_encode($data);
            curl_setopt($curl, CURLOPT_URL, $post_url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); //timeout in seconds
            $post_response = curl_exec($curl);
           // dd($post_response);
            if ($post_response === false) {
                $response_text = curl_error($curl);
                $return_arr['code']=-100;
                $return_arr['msg']=$response_text;
              
            }
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            $post_response=json_decode($post_response);
            if ($post_response === null) {
                $return_arr['code']=-200;
                $return_arr['msg']='Invalid JSON response from server';
            }
            if (isset($post_response->Data->Message) && $post_response->Data->Code=='101') {
                $return_arr['code']=100;
                $return_arr['msg']=$post_response->Data->Message;
            } else {
                $return_arr['code']=-300;
                $return_arr['msg']='Invalid JSON response from server';
                
            }
        }
       
        return $return_arr;
       
    }
}