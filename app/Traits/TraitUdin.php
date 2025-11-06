<?php
namespace App\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Helpers\DataEncrypt;
use App\User;
use App\Scheme;
use Illuminate\Support\Facades\Crypt;
use Auth;
trait TraitUdin{
    protected $userName = 'stage_wcdsw';
    protected $password = 'JYF3FHD';
    protected $encKey = 'F9HD3N';
    protected $curlErrorCode = '-11';
    protected $commonerrorCode = '-1';
    protected $commonSucessCode = '100';
    protected $commonerror = 'Server Error.. Please Try again.';
    public function authenticated($user_id)
    {
        $return_arr=array('code'=>'','message'=>'','httpstatus_code'=>'','x_api_token'=>'');
        $encData= $this->enCrypt($this->password,$this->encKey);
       
        $httpcode=NULL;$response_text=NULL;$message=NULL; $message=NULL;$tokenRefId=NULL;$token=NULL;
        $is_success=1;
        $post_url = 'https://udin.nltr.org/api/auth/genearte-auth-token';
        $curl = curl_init($post_url);
        $data = array("username" => $this->userName,"password" => $encData,"encKey" => $this->encKey);
        $data_string = json_encode($data);
        // dd($data_string);
        $headers = array(
            'Content-Type: application/json',
            // 'client-id: '.$this->clientID,
            // 'client-secret: '.$this->clientSecret,
        );
        header("Access-Control-Allow-Origin: *");
        curl_setopt($curl, CURLOPT_URL, $post_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $request_payload=array();
        $c_time=date('Y-m-d H:i:s');
        $server_ip = $_SERVER['SERVER_ADDR'];
        $request_payload['user_id']=$user_id;
        $request_payload['request_header']=json_encode($headers);
        $request_payload['op_time']=$c_time;
        $request_payload['action_by']=$user_id;
        $request_payload['ip_address']=$server_ip;
        $request_payload['request_body']=$data_string;
        $request_payload['purpose']=1;
       // dd($request_payload);
        $request_insert=DB::table('udin.udin_request')->insert($request_payload);
        if($request_insert){
            $respone_payload=array();
            $c_time=date('Y-m-d H:i:s');
            $server_ip = $_SERVER['SERVER_ADDR'];
            $respone_payload['user_id']=$user_id;
            $respone_payload['op_time']=$c_time;
            $respone_payload['action_by']=$user_id;
            $respone_payload['ip_address']=$server_ip;
            $respone_payload['purpose']=1;
            $post_response = curl_exec($curl);
            $respone_payload['response']=json_encode((array) json_decode($post_response));
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $respone_payload['httpcode']=$httpcode;
            //dd($respone_payload);
            $response_insert=DB::table('udin.udin_response')->insert($respone_payload);
            if($response_insert){
                if (curl_errno($curl)) {
                    $response_text = curl_error($curl);
                    $return_arr['code']=$this->curlErrorCode;
                    $return_arr['message']=$response_text;

                }else {
                    $post_response=json_decode($post_response);
                    $response_text=$post_response;
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $respone_payload['httpcode']=$httpcode;
                    
                    curl_close($curl);
                    

                    if($httpcode==200){
                        if($post_response->message=='You have loged in successfully'){
                            // dd('Success');
                            $token=$post_response->x_api_token;
                            $random_key=$post_response->random_key;
                            // dd($token);
                            // Cache::forever('Aadhar_validate_tokenRefId',$tokenRefId);
                            Cache::forever('udin_validate_token',$token); 
                            Cache::forever('udin_random_key',$random_key); 
                            $return_arr['code']=$this->commonSucessCode;
                            $return_arr['x_api_token']=$token;
                        }
                    } 
                    else {
                        $return_arr['code']=-100;
                        $return_arr['message']=$post_response->message;
                    }

                       
                     
                }
                
       }
       else{
        $return_arr['code']=$this->commonerrorCode;
        $return_arr['message']=$this->commonerror;

       }
    }
    else{
        $return_arr['code']=$this->commonerrorCode;
        $return_arr['message']=$this->commonerror;
    }
    return $return_arr;
       
    }
    
    public function aadhar_validate_udin($aadhar_no,$user_id)
    {
        $receive_data=$this->authenticated($user_id); 
        //dd($receive_data);
        if($receive_data['code']==$this->commonSucessCode){

        
        $udin_validate_token = Cache::get('udin_validate_token');
        $udin_random_key = Cache::get('udin_random_key');
        
        $aadhar_encData= $this->enCrypt($aadhar_no,$udin_random_key);
        $return_arr=array();
        $httpcode=NULL;$response_text=NULL;$message=NULL; $message=NULL;$code=NULL;$last_biometric=NULL; 
       
        $time=time();
        $post_url = 'https://udin.nltr.org/api/aadhaar/validate';
        $curl = curl_init($post_url);
        $headers = array(
                                    'Content-Type: application/json',
                                    'X-Api-Token:'.$udin_validate_token,
                                   
                        
        );
        //dd( $headers);
        $data = array("aadhaar" =>$aadhar_encData,"random" =>  $udin_random_key);
        $data_string = json_encode($data);
        //header("Access-Control-Allow-Origin: *");
        curl_setopt($curl, CURLOPT_URL, $post_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); //timeout in seconds
        $c_time=date('Y-m-d H:i:s');
        $server_ip = $_SERVER['SERVER_ADDR'];
        $request_payload['user_id']=$user_id;
        $request_payload['request_header']=json_encode($headers);
        $request_payload['op_time']=$c_time;
        $request_payload['action_by']=$user_id;
        $request_payload['ip_address']=$server_ip;
        $request_payload['request_body']=$data_string;
        $request_payload['purpose']=2;
        $request_insert=DB::table('udin.udin_request')->insert($request_payload);
        if($request_insert){
        $post_response = curl_exec($curl);
        $respone_payload=array();
        $c_time=date('Y-m-d H:i:s');
        $server_ip = $_SERVER['SERVER_ADDR'];
        $respone_payload['user_id']=$user_id;
        $respone_payload['op_time']=$c_time;
        $respone_payload['action_by']=$user_id;
        $respone_payload['ip_address']=$server_ip;
        $respone_payload['purpose']=2;
        $respone_payload['response']=json_encode((array) json_decode($post_response));

        if (curl_errno($curl)) {
            $return_arr['code']=$this->curlErrorCode;
            $return_arr['message']=$response_text;
        }
        else{
            ////dd( $curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $post_respons=json_decode($post_response);
        $respone_payload['httpcode']=$httpcode;
        $response_insert=DB::table('udin.udin_response')->insert($respone_payload);
        $otp_send_status=NULL;
        //dd($post_respons);
        if($httpcode==200){
            $otp_send_status=1;
            
            if($post_respons->code=='SUC_00000'){
                 //dd('Success');
                $trans_id=$post_respons->trans_id;
                $random_key=$post_respons->random_key;
                // dd($token);
                // Cache::forever('Aadhar_validate_tokenRefId',$tokenRefId);
                Cache::forever('udin_trans_id',$trans_id); 
                Cache::forever('udin_random_key',$random_key); 
                //$return_arr['status_code']=$this->commonSucessCode;
                $return_arr['trans_id']=$trans_id;
                $return_arr['udin_random_key']=$udin_random_key;
                $return_arr['message']=$post_respons->message;
                $validate_user_list=array();
                $user = User::where('id', $user_id)->where('is_active', 1)->first();

                $emp_row=DB::table('public.employees')->where('id',$user->emp_id)->first();
                if(!empty($emp_row) && $emp_row->full_name!='' && !is_null($emp_row->full_name)){
                    $validate_user_list['in_time_name_as_per_portal']=trim($emp_row->full_name);
                }
                $validate_user_list['user_id']=$user_id;
                $validate_user_list['ip_address']=$server_ip;
                $validate_user_list['action_by']=$user_id;
                $validate_user_list['op_time']=$c_time;
                $validate_user_list['encoded_aadhar']=Crypt::encryptString($aadhar_no);
                $validate_user_list['aadhar_hash']=md5($aadhar_no);
                $validate_user_list['otp_send_status']=$otp_send_status;
                $validate_user_list['x_api_token']=$udin_validate_token;
                $validate_user_list['in_time_mobile_no_as_per_portal']=$user->mobile_no;
                $user_validate_insert=DB::table('udin.validate_user_list')->insert($validate_user_list);
                if($user_validate_insert){
                    $return_arr['code']=$this->commonSucessCode;
                }
                else{
                    $return_arr['code']=$this->commonerrorCode;
                    $return_arr['message']=$this->commonerror;
                }
            }
            
            else{
                $return_arr['code']=-100;
                 $return_arr['message']=$post_respons->message;
            }
        }
        else{
            $return_arr['code']=-100;
            $return_arr['message']=$post_respons->message;
        }
    }
              
              
       }
   else{
    $return_arr['code']=$this->commonerrorCode;
    $return_arr['message']=$this->commonerror;
     }
        }
      
        return $return_arr; 
    }
    public function aadhar_otp_validate($otp,$user_id)
    {
        $user = User::where('id', $user_id)->where('is_active', 1)->first();
        $udin_validate_token = Cache::get('udin_validate_token');
        $udin_random_key = Cache::get('udin_random_key');
        $udin_trans_id = Cache::get('udin_trans_id');
        //$aadhar_encData= $this->enCrypt($aadhar_no,$udin_random_key);
        $return_arr=array();
        $httpcode=NULL;$response_text=NULL;$message=NULL; $message=NULL;$code=NULL;$last_biometric=NULL; 
       
        $time=time();
        $post_url = 'https://udin.nltr.org/api/aadhaar/validate-otp';
        $curl = curl_init($post_url);
        $headers = array(
                                    'Content-Type: application/json',
                                    'X-Api-Token:'.$udin_validate_token,
                                   
                        
        );
        //dd( $headers);
        $data = array("trans_id" =>$udin_trans_id,"otp" =>  $otp,"random" =>  $udin_random_key);
        $data_string = json_encode($data);
        //header("Access-Control-Allow-Origin: *");
        curl_setopt($curl, CURLOPT_URL, $post_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); //timeout in seconds
        $c_time=date('Y-m-d H:i:s');
        $server_ip = $_SERVER['SERVER_ADDR'];
        $request_payload['user_id']=$user_id;
        $request_payload['request_header']=json_encode($headers);
        $request_payload['op_time']=$c_time;
        $request_payload['action_by']=$user_id;
        $request_payload['ip_address']=$server_ip;
        $request_payload['request_body']=$data_string;
        $request_payload['purpose']=3;
        $request_insert=DB::table('udin.udin_request')->insert($request_payload);
        if($request_insert){
            //dd('ok');
                    $post_response = curl_exec($curl);
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $respone_payload=array();
                    $c_time=date('Y-m-d H:i:s');
                    $server_ip = $_SERVER['SERVER_ADDR'];
                    $respone_payload['user_id']=$user_id;
                    $respone_payload['op_time']=$c_time;
                    $respone_payload['action_by']=$user_id;
                    $respone_payload['ip_address']=$server_ip;
                    $respone_payload['purpose']=3;
                    $respone_payload['response']=json_encode((array) json_decode($post_response));
                    $post_respons=json_decode($post_response);
                    $respone_payload['httpcode']=$httpcode;
                    $response_insert=DB::table('udin.udin_response')->insert($respone_payload);
                    if($response_insert){
                    $otp_validation_status=NULL;
                    if (curl_errno($curl)) {
                        $return_arr['code']=$this->curlErrorCode;
                        $return_arr['message']=$response_text;
                    }
                    else{
                        ////dd( $curl);
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);
                    $post_respons=json_decode($post_response);
                      //dd($post_respons);
                    if($httpcode==200){
                        if($post_respons->error==false){
                            $return_arr['message']=$post_respons->message;
                            if($post_respons->code=='SUC_00000'){
                                $validate_user_list=array();
                                $validate_user_list['otp_validation_status']=1;
                                $validate_user_list['return_name_as_in_aadhar']=$post_respons->aadhaar_data->name;
                                //dd($post_respons->aadhaar_data->name);
                                $user_validate_update=DB::table('udin.validate_user_list')->where('x_api_token',$udin_validate_token)->where('user_id',$user_id)->update($validate_user_list);
                                if($user_validate_update){
                                    $emp_row=DB::table('public.employees')->where('id',$user->emp_id)->first();
                                    if(strtoupper(trim($post_respons->aadhaar_data->name))==strtoupper(trim($emp_row->full_name_as_in_aadhar))){
                                        $return_arr['code']=$this->commonSucessCode;
                                        $return_arr['message']=$post_respons->message;
                                    }
                                    else{
                                        $return_arr['code']=-50;
                                        $return_arr['message']='Name mismatch';
                                    }
                                }
                                else{
                                    $return_arr['code']=$this->commonerrorCode;
                                    $return_arr['message']=$this->commonerror;
                                }
                            }
                        }
                        else{
                            $return_arr['code']=-100;
                            $return_arr['message']=$post_respons->message;
                        }
                      

                       
                    }
                    else{
                        $return_arr['code']=-100;
                        $return_arr['message']=$post_respons->message;
                    }
                    }
                    
                }
                else{
                    $return_arr['code']=$this->commonerrorCode;
                    $return_arr['message']=$this->commonerror;
                    
                }
       }
       else{
        $return_arr['code']=$this->commonerrorCode;
    $return_arr['message']=$this->commonerror;
       }
       return $return_arr;
        
    }
    public function enCrypt($data, $key)
    {
      return bin2hex(openssl_encrypt($data, 'aes-256-ecb', $key, OPENSSL_RAW_DATA));
    }
        
}