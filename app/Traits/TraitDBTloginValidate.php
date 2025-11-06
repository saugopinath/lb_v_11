<?php
namespace App\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Helpers\DataEncrypt;
use App\Scheme;
trait TraitDBTloginValidate{
    protected $dbtUserId = 'JSLB';
    protected $dbtPassword = 'N7UF!v942Q';
    public function authenticated()
    {
        // dd('Authenticate');
        // $loginCredential = $this->loginCredential($scheme_id);
        $return_arr=array();
        $httpcode=NULL;$response_text=NULL;$message=NULL; $message=NULL;$tokenRefId=NULL;$token=NULL;
        $is_success=1;
        // $post_url = 'http://dbt.wb.gov.in/backend/api/Auth/v1/ApiLogin'; 
        $post_url = 'https://dbt.wb.gov.in/backend/api/Auth/v1/ApiLogin';
        $curl = curl_init($post_url);
        $data = array("userId" => $this->dbtUserId,"password" => $this->dbtPassword);
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
        $post_response = curl_exec($curl);
        // $headers = $this->get_headers_from_curl_response($post_response);
        // dd($headers);

        if (curl_errno($curl)) {
            $response_text = curl_error($curl);
            // dd($response_text);
            $is_success=0;
        }else {
            $post_response=json_decode($post_response);
            // dd($post_response);
            $response_text=$post_response;
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if($httpcode==200){
                if($post_response->apiResponseStatus==1){
                    // dd('Success');
                    $token=$post_response->result;
                    // dd($token);
                    // Cache::forever('Aadhar_validate_tokenRefId',$tokenRefId);
                    Cache::forever('Dbt_validate_token',$token); 
                }
            }    
        }
    }
    public function send_to_dbt($scheme_id, $finYear, $month)
        {
            // $token = null;
            // dump($scheme_id); dump($finYear); dump($month); //die;
            $this->authenticated();
            $token = Cache::get('Dbt_validate_token');
            // dd($token);
            // if(empty($token)){
            //     // dd('empty');
            //     $this->authenticated();
            //     $token = Cache::get('Dbt_validate_token');
            // }
            if ($token) 
            {
                // dd($token);
                if ($month >= 4 AND $month <= 12) {
                    $year = $finYear.'-'.($finYear + 1);
                } else {
                    $year = ($finYear - 1).'-'.$finYear;
                }
                
                $schemeCode = DB::table('pds.master_scheme')->where('scheme_id', $scheme_id)->value('dbt_scheme_code');
    
                $getData = DB::table('dbt.dbtconsolidatedata')->select('SchemeCode AS DbtSchemeCode','FinYrCode AS finYrCode','BenefitType AS benefitType','ReportingMonth AS reportingMonth','FundTrnsferCash AS fundTrnsferCash','ExpenditureKind AS expenditureKind','NoTrnsCashElectronic AS noTrnsCashElectronic','AmntTrnsCashElectronic AS amntTrnsCashElectronic','NoTrnsCashOther AS noTrnsCashOther','AmntTrnsCashOther AS amntTrnsCashOther','TrnsAadharSeeded AS trnsAadharSeeded','QtyTransferedKind AS qtyTransferedKind','AadharTransKind AS aadharTransKind','NoDeDuplicated AS noDeDuplicated','NoGhost AS noGhost','OtherSavings AS otherSavings','SavingAmnt AS savingAmnt','Remarks AS remarks','FundCashElectronicApb AS fundCashElectronicApb','NoTrnsCashElectronicApb AS noTrnsCashElectronicApb','totalBenIncremental AS totalBenIncremental','benWithBankIncremental AS benWithBankIncremental','benDigitizedIncremental AS benDigitizedIncremental','benAadharSeededIncremental AS benAadharSeededIncremental','mobileCapturedIncremental AS mobileCapturedIncremental')->where('FinancialYear', $year)->where('ReportingMonth', $month)->where('SchemeCode', $schemeCode)->get();
                // dump($getData); die;
                // dump(addslashes(json_encode($getData)));
                // dump(json_decode(stripslashes(json_encode($getData[0]))));

                // die;

                if (count($getData) > 0) {
                    // dd('Success');
                    $return_arr=array();
                    $is_success=1;
                    $post_url = 'https://dbt.wb.gov.in/backend/api/DBTData/v1/SaveDbtDataApi';
                    $curl = curl_init($post_url);
                    $resultData = addslashes(json_encode($getData[0]));
                    // dd($json_encode);
                    $getKey= Config::get('constants.EncryptionKey');
                    // Convert base64 key to bytes
                    $key = base64_decode($getKey);
                    // dd($getKey);
                    // Extract IV (Initialization Vector) from the key
                    $iv = substr($key, 0, 16);
                    // Encrypt the data
                    $encrypted = openssl_encrypt(json_encode($getData[0]), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
                    // dd($encrypted);
                    // Convert encrypted bytes to base64 string
                    $encryptedBase64 = base64_encode($encrypted);

                    // dump($resultData);
                    // dump($getKey);
                    // dump($encryptedBase64);
                    // die;

                    // dd($encode_encrypted_data);
                    $data = array("schemeCode" => $schemeCode,"encrypted_data" => $encryptedBase64);
                    $data_string = json_encode($data, true);
                    // dd($data);
                    // dd($data_string);
                    //  dd($token);
                    $headers = array(
                        'Content-Type: application/json',
                        'Authorization: '.$token,
                        // 'client-secret: '.$this->clientSecret,
                    );
                    header("Access-Control-Allow-Origin: *");
                    curl_setopt($curl, CURLOPT_URL, $post_url);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    // curl_setopt($curl, CURLOPT_HEADER, 1);
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                    // dd($curl);
                    $post_response = curl_exec($curl);
                    // dd($post_response);
                    if (curl_errno($curl)) {
                        $response_text = curl_error($curl);
                        // dd($response_text);
                        $is_success=0;
                    }else {
                        $post_response=json_decode($post_response);
                        // dd($post_response);
                        $response_text=$post_response;
                        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        curl_close($curl);
                        if($httpcode==200){
                            if($post_response->apiResponseStatus==1){
                                // dd('Success');
                                $token=$post_response->result;
                                // dd($token);
                                // Cache::forever('Aadhar_validate_tokenRefId',$tokenRefId);
                                Cache::forever('Dbt_validate_token',$token); 
                            }
                        }    
                    }
                } 
                else {
                    dd('Data Not Found.');
                }
            } else {
                dd('Token Not Found.');
            }
        }
}