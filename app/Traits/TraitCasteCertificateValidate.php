<?php

namespace App\Traits;
use App\getModelFunc;
use App\DataSourceCommon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
trait TraitCasteCertificateValidate {
 protected $clientID = 'ff2c12aacd4ceacbbecbaac9ab979007';
 protected $clientSecret = 'ccb028f2e6aa601248a5edd74176b062';
 public function validate_with_caste_certificate($caste_certificate_no,$ben_fullname){
    $httpcode=NULL;$response_text=NULL;$message=NULL; $code=NULL; $return_msg='';
   // dump($caste_certificate_no);
   // dd($ben_fullname);
    $is_update=0;
    $is_success=0;
    $match_found=0;
    $post_url = 'https://wbgw.napix.gov.in/wb/bcwd/certtificate_api/certdet';
    $curl = curl_init($post_url);
    $headers = array(
          'Content-Type: application/json',
          'client-id: '.$this->clientID,
          'client-secret: '.$this->clientSecret,

    );
    $data = array("certno" => $caste_certificate_no);
    $data_string = json_encode($data);
    header("Access-Control-Allow-Origin: *");
    curl_setopt($curl, CURLOPT_URL, $post_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); //timeout in seconds
    $post_response = curl_exec($curl);
    //dd($post_response);
    if (curl_errno($curl)) {
        $response_text = curl_error($curl);
        $match_found=0;
    }
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $response_text=json_encode($post_response);
    //dd($response_text);
    if($httpcode==200){
        $is_success=1;
        $post_response=json_decode($post_response);
        if(is_array( $post_response)){
            $ben_fullname = str_replace(' ', '', $ben_fullname);
            $appl_name = str_replace(' ', '', $post_response[0]->appl_name);
            if(strtoupper($appl_name)==strtoupper($ben_fullname)){
                 $match_found=1;     
            }
            if($match_found){
                $code =1;
                $return_msg='Caste Certificate is Valid';
    
            }
            else{
                $code =2;
                $return_msg='Name Mismatch';
    
            }
        }
        else{
        if($post_response->status=='Failure'){
            $code =5;
            $return_msg='Not Found';
        }
         }
        
      
        
    }
    else if($httpcode==404){
        
        $post_response=json_decode($post_response);        
        $status=$post_response->status;
        $message=$post_response->message;
        $return_status = 2;
        $return_msg='Caste Certificate Number Not Valid';
        $code =3;

    }else if($httpcode==401){
        $response_text=json_encode($post_response);
        $status=$response_text->httpMessage;
        $return_msg=$response_text->moreInformation;
        $code =4;
    }
    $return_arr['httpcode']= $httpcode;
    $return_arr['is_success']=$is_success;
    $return_arr['response_text']=$response_text;
    $return_arr['message']=$return_msg;
    $return_arr['code']=$code;
    $return_arr['match_found']=$match_found;
    return $return_arr;
   }
   public function casteInfoCheckInsert($distCode,$application_id,$ben_fullname,$ip,$caste_certificate_no,$blockCode,$user_id,$api_code){
    $getModelFunc = new getModelFunc();
    $pension_personal_model = new DataSourceCommon;
    $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
    // $pension_personal_model->setKeyName('application_id');
    // $pension_personal_model->setTable('' . $Table);


    if($api_code==1)
    {
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
        $pension_personal_model->setKeyName('application_id');
        $pension_personal_model->setTable('' . $Table);
    }else if($api_code==2){
        $Table = $getModelFunc->getTableFaulty($distCode, $this->source_type, 1, NULL);
        $pension_personal_model->setKeyName('application_id');
        $pension_personal_model->setTable('' . $Table);
    }
    else if($api_code==4){
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, NULL);
        $pension_personal_model->setKeyName('application_id');
        $pension_personal_model->setTable('' . $Table);
    }else if($api_code==3){
        $Table = $getModelFunc->getTableFaulty($distCode, $this->source_type, 11, NULL);
        $pension_personal_model->setKeyName('application_id');
        $pension_personal_model->setTable('' . $Table);
        //  dd($Table);
    }


    $ben_fullname=$ben_fullname;
    $session_lb_castecertificate=array();
    
    // dd('ok');
    $caste_validation_arr=array();
    $caste_validation_arr['caste_certificate_no']= trim($caste_certificate_no);
    $caste_validation_arr['application_id']= $application_id;
    $caste_validation_arr['m_type']=2;
    $caste_validation_arr['created_by_local_body_code']=$blockCode;
    $caste_validation_arr['api_hit_time']=date('Y-m-d H:i:s', time());
    $caste_validation_arr['loginid']=$user_id;
    $caste_validation_arr['ip_address']= $ip;
    $caste_response_validation_arr=$this->validate_with_caste_certificate($caste_certificate_no = trim($caste_certificate_no),$ben_fullname= $ben_fullname); 
    $caste_validation_arr['httpcode']= $caste_response_validation_arr['httpcode'];
    if($caste_response_validation_arr['is_success']==1){
        DB::beginTransaction();
    $pension_details=array();
    $c_time1=date('Y-m-d H:i:s', time());
    $caste_validation_arr['response_text']=$caste_response_validation_arr['response_text'];
    $caste_validation_arr['api_response_time']= $c_time1;
    // dd($caste_validation_arr);
    $insert=DB::table('lb_scheme.ben_caste_api_response_track')->insert($caste_validation_arr);
    $pension_details['caste_certificate_checked'] = 1;
    $pension_details['caste_certificate_check_lastdatetime'] =  $c_time1;
    $pension_details['caste_certificate_validation_message'] =  $caste_response_validation_arr['message'];
    $pension_details['caste_matched_with_certificate_no'] =  $caste_response_validation_arr['code'];
    $pension_details['action_by'] = Auth::user()->id;
    $pension_details['action_ip_address'] = request()->ip();
    $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
    if($caste_response_validation_arr['match_found']==1){
        $caste_is_error=0;
    }else{
        $caste_is_error=1;
    }
    $session_lb_castecertificate['is_error']=$caste_is_error;
    $session_lb_castecertificate['message']=$caste_response_validation_arr['message'];
    //dd($c_time1);
    $session_lb_castecertificate['lastdatetime']=date('d/m/Y',strtotime($c_time1)); 
    $update=$pension_personal_model->where('application_id', $application_id)->update($pension_details);
    if($update && $insert){
        DB::commit();
    $session_lb_castecertificate=$session_lb_castecertificate;
    }
    else{
        DB::rollback();
    $session_lb_castecertificate=array();
    }
    }
    return $session_lb_castecertificate;
    
    }
}