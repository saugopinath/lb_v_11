<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\Taluka;
use Redirect;
use Auth;
use Config;
use URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\getModelFunc;
use App\Models\DataSourceCommon;
use App\Helpers\Helper;
use App\Models\RejectRevertReason;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
class TrackApplicantController extends Controller
{

    public function __construct()
    {
      
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
    }
   


    function applicantTrack()
    {
        $application_type_text = "Track Applicant";
        $schemelist = collect([]);
        $scheme_id = $this->scheme_id;
        $errormsg = Config::get('constants.errormsg');
        if (date('m') > 3) {
            $year = date('Y') . "-" . (date('Y') + 1);
        } else {
            $year = (date('Y') - 1) . "-" . date('Y');
        }
        return view(
            'publicView/publicApplicationTrack',
            [
                'schemelist' => $schemelist,
                'scheme_id' => $scheme_id,
                'application_type_text' => $application_type_text,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'currentFinYear' => $year
            ]
        );
    }
    public function refereshCapcha(){
        return captcha_img('flat');
    } 
    public function ajaxApplicationTrack(Request $request)
    {
          try {
        $return_data=array();
        $return_status=0;
        $return_msg='';
        $ben_name='';
        $beneficiary_id='';
        $f_application_id='';
        $ben_status='';
        $bank_acc_validation_status='';
        $bank_account_no='';
        $bank_ifsc='';
        $accept_reject_info=array();
        $payment_data=array();
        $is_public = $request['is_public'];
        $applicant_id = $request['applicant_id'];
        $track_type = $request['trackType'];
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        if($is_public==1){
            $server_valiation=1;
            $rules = [
                'captcha' => 'required|captcha',
            ];
            $attributes = array();
            $messages = array();
            $attributes['captcha'] = 'captcha';
            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            if ($validator->passes()) {
                $server_valiation=1;
            }
            else{
                $error_msg = array();
                foreach ($validator->errors()->all() as $error) {
                array_push($error_msg, $error);
                }
                //dd( $error_msg);
            }
            $designation_id='Public';
            $scheme_id =  $this->scheme_id;
        } else{
            $server_valiation=1;
        }
        if( $server_valiation==1){
                $track_type = $request['trackType'];
                $district_code='';
                $getModelFunc = new getModelFunc();
                $acceptRejectTable = $getModelFunc->getTable($district_code, $this->source_type, 9);
                $personalTableDraft = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
                $personalTableMain = $getModelFunc->getTable($district_code, $this->source_type, 1);
                $bankTableMainDraft = $getModelFunc->getTable($district_code, $this->source_type, 4, 1);
                $bankTableMain = $getModelFunc->getTable($district_code, $this->source_type, 4);
                $aadharTable = $getModelFunc->getTable($district_code, $this->source_type, 2);
                $rejectTable = $getModelFunc->getTable($district_code, $this->source_type, 10);

                $faultyPersonalTableDraft = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1, 1);
                $faultyPersonalTableMain = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1);
                $faultybankTableMainDraft = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4, 1);
                $faultybankTableMain = $getModelFunc->getTableFaulty($district_code, $this->source_type, 4);

                $personal_model = new DataSourceCommon;
                $personal_model->setConnection('pgsql_appread');
                $personal_model->setTable('' . $personalTableMain);
                $personal_model_draft = new DataSourceCommon;
                $personal_model_draft->setConnection('pgsql_appread');
                $personal_model_draft->setTable('' . $personalTableDraft);
                $model_aadhar = new DataSourceCommon;
                $model_aadhar->setConnection('pgsql_appread');
                $model_aadhar->setTable('' . $aadharTable);
                $reject_model = new DataSourceCommon;
                $reject_model->setConnection('pgsql_appread');
                $reject_model->setTable('' . $rejectTable);
                $whereCon = "where 1=1";
                $condition = array();
                $s_application_id = '';
                $aadhar_search = 0;
                $s_application_id_in = array();
                $reject_found = 0;
               
                     // Application ID
                     if ($track_type == 1) {
                         $condition['application_id'] = $applicant_id;
                         $whereCon .= " and A.application_id=".$applicant_id;
                     } // Beneficiary ID
                     else if ($track_type == 2) {
                          $condition['beneficiary_id'] = $applicant_id;
                          $whereCon .= " and A.beneficiary_id=".$applicant_id ;
                     }  // Mobile Number
                     else if ($track_type == 3) {
                        $condition['mobile_no'] = $applicant_id;
                         $whereCon .= " and A.mobile_no='" . $applicant_id . "'";
                     } // Aadhaar Number
                     else if ($track_type == 4) {
                         $encrpt_aadhar = Crypt::encryptString($applicant_id);
                         $condition['encoded_aadhar'] = $encrpt_aadhar;
                         $aadhar_search=1;
                     } // Bank Account Number
                    else if ($track_type == 5) {
                          $condition['bank_code'] = $applicant_id;
                          $whereCon .= " and bank_code='" . $applicant_id . "'";
                    } // Sasthya Sathi Card Number
                    else if ($track_type == 6) {
                         $condition['ss_card_no'] = $applicant_id;
                         $whereCon .= " and A.ss_card_no='" . $applicant_id . "'";
                    }
                $query="select A.ben_fname,A.application_id,A.beneficiary_id,A.mobile_no,A.ss_card_no,B.bank_code as bank_code,B.bank_ifsc as bank_ifsc,0 as reject_found from
                lb_scheme.ben_personal_details as A 
                LEFT JOIN lb_scheme.ben_bank_details as B ON A.application_id=B.application_id
                LEFT JOIN lb_scheme.ben_aadhar_details as C ON A.application_id=C.application_id 
                 " . $whereCon . "
                UNION
                select A.ben_fname,A.application_id,0 as beneficiary_id,A.mobile_no,A.ss_card_no,B.bank_code as bank_code,B.bank_ifsc as bank_ifsc,0 as reject_found from
                lb_scheme.draft_ben_personal_details as A 
                LEFT JOIN lb_scheme.draft_ben_bank_details as B ON A.application_id=B.application_id
                LEFT JOIN lb_scheme.ben_aadhar_details as C ON A.application_id=C.application_id 
                 " . $whereCon . "
                UNION
                select A.ben_fname,A.application_id,A.beneficiary_id,A.mobile_no,A.ss_card_no,B.bank_code as bank_code,B.bank_ifsc as bank_ifsc,0 as reject_found from
                lb_scheme.faulty_ben_personal_details as A 
                LEFT JOIN lb_scheme.faulty_ben_bank_details as B ON A.application_id=B.application_id
                LEFT JOIN lb_scheme.ben_aadhar_details as C ON A.application_id=C.application_id 
                 " . $whereCon . "
                UNION
                select A.ben_fname,A.application_id,0 as beneficiary_id,A.mobile_no,A.ss_card_no,B.bank_code as bank_code,B.bank_ifsc as bank_ifsc,0 as reject_found from
                lb_scheme.faulty_draft_ben_personal_details as A 
                LEFT JOIN lb_scheme.faulty_draft_ben_bank_details as B ON A.application_id=B.application_id
                LEFT JOIN lb_scheme.ben_aadhar_details as C ON A.application_id=C.application_id 
                 " . $whereCon ." 
                UNION 
                select ben_fname,application_id,'0',mobile_no,ss_card_no,'' as bank_code,'' as bank_ifsc,1 as reject_found from lb_scheme.ben_reject_details as A
                 " . $whereCon . " limit 1";
                 //dd($query);
                $result = DB::connection('pgsql_appread')->select($query);
                if (!empty($result) && count($result) > 0) {
                    $return_status=1;
                    $ben_name= $result[0]->ben_fname; 
                    $beneficiary_id= $result[0]->beneficiary_id;  
                    $f_application_id= $result[0]->application_id;  
                    $bank_account_no= $result[0]->bank_code;  
                    $bank_ifsc= $result[0]->bank_ifsc;  
                     $query = DB::connection('pgsql_appread')->table("$acceptRejectTable as a")->select(
                        "a.op_type as op_type",
                        "a.designation_id as designation_id",
                        "a.created_at",
                        "a.created_by_level",
                        "a.created_by_dist_code",
                        "a.rejected_reverted_cause",
                        "a.created_by_local_body_code",
                        "a.application_id",
                        "b.mobile_no"
                    )->whereIn('op_type',['F','E','V','A','U','T','FE','FU','FR','FV','FA'])->where('application_id', $f_application_id);   
                    $query = $query->join('public.users as b', 'a.created_by', '=', 'b.id');
                    //dd($query->toSql());
                    $result_accept_reject_list = $query->orderBy('created_at')->get();
                    //dd( $result_accept_reject_list);
                    $arr = array();
                    $i = 0;
                    foreach ($result_accept_reject_list as $accept_reject_info_item) {
                             $arr[$i]['created_at'] = $accept_reject_info_item->created_at;
                           // $arr[$i]['mobile_no'] = $row->mobile_no;
                             $arr[$i]['mobile_no'] = '******' . substr($accept_reject_info_item->mobile_no, -4);

                            $location_description = '';
                            if ($accept_reject_info_item->designation_id == 'Operator') {
                                $arr[$i]['role_description'] = 'Operator:' . '******' . substr($accept_reject_info_item->mobile_no, -4);
                            } else if ($accept_reject_info_item->designation_id == 'Verifier') {
                                $arr[$i]['role_description'] = 'Verifier';
                            } else if ($accept_reject_info_item->designation_id == 'Approver') {
                                $arr[$i]['role_description'] = 'Approver';
                            } else {
                                $arr[$i]['role_description'] = 'Others';
                            }
                            $arr[$i]['action_description'] = '';
                            if (trim($accept_reject_info_item->op_type) == 'F') {
                                $arr[$i]['action_description'] = 'Temporary Saved';
                            } else if (trim($accept_reject_info_item->op_type) == 'E') {
                                $arr[$i]['action_description'] = 'Final Submitted';
                            } else if (trim($accept_reject_info_item->op_type) == 'V') {
                                $arr[$i]['action_description'] = 'Verified';
                            } else if (trim($accept_reject_info_item->op_type) == 'A') {
                                $arr[$i]['action_description'] = 'Approved';
                            } else if (trim($accept_reject_info_item->op_type) == 'U') {
                                $arr[$i]['action_description'] = 'Updated';
                            } else if (trim($accept_reject_info_item->op_type) == 'T') {
                                $cause_description = $this->causeDescription($accept_reject_info_item->rejected_reverted_cause);
                                $arr[$i]['action_description'] = 'Reverted for the reason ' . $cause_description;
                            }else if (trim($accept_reject_info_item->op_type) == 'FE') {
                                //$cause_description = $this->causeDescription($row->rejected_reverted_cause);
                                $arr[$i]['action_description'] = 'Faulty Entry';
                            } else if (trim($accept_reject_info_item->op_type) == 'FU') {
                                //$cause_description = $this->causeDescription($row->rejected_reverted_cause);
                                $arr[$i]['action_description'] = ' Move from Faulty to Normal';
                            } else if (trim($accept_reject_info_item->op_type) == 'FR') {
                                //$cause_description = $this->causeDescription($row->rejected_reverted_cause);
                                $cause_description = $this->causeDescription($row->rejected_reverted_cause);
                                $arr[$i]['action_description'] = 'Rejected for the reason ' . $cause_description;
                            } else if (trim($accept_reject_info_item->op_type) == 'FV') {
                                $arr[$i]['action_description'] = 'Verified';
                            } else if (trim($accept_reject_info_item->op_type) == 'FA') {
                                $arr[$i]['action_description'] = 'Approved';
                            }
                            if ($accept_reject_info_item->created_by_level == 'District') {
                                $district_arr = District::where('district_code', $accept_reject_info_item->created_by_dist_code)->first();
                                if (!empty($district_arr))
                                    $location_description = $district_arr->district_name;
                                $mapping_level = 'District Officer';
                            } else if ($accept_reject_info_item->created_by_level == 'Subdiv') {
                                $sdo_arr = SubDistrict::where('sub_district_code', $accept_reject_info_item->created_by_local_body_code)->first();
                                if (!empty($sdo_arr))
                                    $location_description = $sdo_arr->sub_district_name;
                                $mapping_level = 'Sub Division Officer';
                            } else if ($accept_reject_info_item->created_by_level == 'Block') {
                                $block_arr = Taluka::where('block_code', $accept_reject_info_item->created_by_local_body_code)->first();
                                if (!empty($block_arr))
                                    $location_description = $block_arr->block_name;

                                $mapping_level = 'Block Development Officer';
                            } else if ($accept_reject_info_item->mapping_level == 'State') {
                                $location_description = 'West Bengal';
                                $mapping_level = 'State Level Officer';
                            } else if ($accept_reject_info_item->mapping_level == 'Department') {
                                $location_description = 'West Bengal';
                                $mapping_level = 'State Level Officer';
                            }
                            $arr[$i]['location_description'] = $location_description;
                            $arr[$i]['mapping_level'] = $mapping_level;
                            $i++;
                     }
                    
                     $accept_reject_info=$arr;
                      //dd($accept_reject_info);
                     
                }
                else{
                    $return_status=0;
                    $return_msg='No data Found';
                }
        }     
             
             
       else{
        $return_status=0;
        $return_msg='Invalid captcha';
      }
    }
     catch (\Exception $e) {
           dd($e);
        }
       return response()->json(
        [
            'ben_name' => $ben_name, 
            'beneficiary_id' => $beneficiary_id, 
            'f_application_id' => $f_application_id, 
            'ben_status' => $ben_status, 
            'bank_acc_validation_status' => $bank_acc_validation_status, 
            'bank_account_no' => $bank_account_no, 
            'bank_ifsc' => $bank_ifsc, 
            'accept_reject_info' => $accept_reject_info, 
            'payment_data' => $payment_data, 
            'return_status' => $return_status, 
            'return_msg' => $return_msg,
            'return_data' => $return_data
        ]);
    }
   public function getFinYearWisePaymentDetailsInTrackApplication(Request $request)
    {
        $fin_year = $request->fin_year;
        $ben_id = $request->ben_id;
        $ben_status=0;
        $ben_status_msg='';
        $bank_acc_validation_status='';
        $bank_acc_validation_msg='';
        $payment_stop_msg='';
        $acc_validation_error_msg='';
        $bank_account_no='';
        $bank_ifsc='';
        $payment_data=array();
        $return_data=array();
        $return_status=0;
        $return_msg='';
        $getModelFunc = new getModelFunc();
        $schemaname = $getModelFunc->getSchemaDetails($fin_year);
        if ($fin_year == '2021-2022') {
            $schemaname = 'payment';
        } else if ($fin_year == '2022-2023') {
            $schemaname = 'payment';
        }else if ($fin_year == '2023-2024') {
            $schemaname = $getModelFunc->getSchemaDetails($fin_year);
        }
        $schemanameCur = $getModelFunc->getSchemaDetails();
        $schemaname='payment';
        $benStatusObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $ben_id)->first();
        $benTransObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_transaction_details')->where('fin_year', $fin_year)->where('ben_id', $ben_id)->first();
        $failedBenStatusObj = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $ben_id)->whereIn('edited_status', [0, 1])->first();

        $paymentDetails = '';

        if (!empty($benStatusObj) && !empty($benTransObj)) {
            // New For Showing Validation Error (Date : 16/12/2021)
           // $paymentDetails .= '<hr>';
           // $paymentDetails .= '<h5 class="text-success"><b>Bank Account Status : ' . Config::get('globalconstants.acc_validated.' . $benStatusObj->acc_validated) . '</b></h5>';
            $bank_acc_validation_msg= Config::get('globalconstants.acc_validated.' . $benStatusObj->acc_validated);
            // if ($benStatusObj->acc_validated == 3 && $failedBenStatusObj->failed_type == 1) {
            //     $paymentDetails .= '<h5 class="text-warning"><b>Account validation failed, pending at Verifier end.<b></h5>';
            // } elseif ($benStatusObj->acc_validated == 3 && $failedBenStatusObj->failed_type == 3) {
            //     $paymentDetails .= '<h5 class="text-warning"><b>Name Validation Matching Score : '.$failedBenStatusObj->matching_score.'%<b></h5>';
            // }
            if ($benStatusObj->acc_validated == 3) {
                if ($failedBenStatusObj->failed_type == 1 && $failedBenStatusObj->edited_status == 0) {
                    $bank_acc_validation_msg='Account validation failed, pending at Verifier end.';
                    //$paymentDetails .= '<h5 class="text-warning"><b>Account validation failed, pending at Verifier end.<b></h5>';
                } elseif ($failedBenStatusObj->failed_type == 3 && ($failedBenStatusObj->edited_status == 0 || $failedBenStatusObj->edited_status == 1)) {
                    //$paymentDetails .= '<h5 class="text-warning"><b>Name Validation Matching Score : '.$failedBenStatusObj->matching_score.'%<b></h5>';
                     $bank_acc_validation_msg='Name Validation Matching Score:'.$failedBenStatusObj->matching_score."%";
                } elseif ($failedBenStatusObj->failed_type == 1 && $failedBenStatusObj->edited_status == 1) {
                     $bank_acc_validation_msg='Account validation failed, pending at Approver end.';
                    //$paymentDetails .= '<h5 class="text-warning"><b>Account validation failed, pending at Approver end.<b></h5>';
                }
            }
            if ($benStatusObj->ben_status == 1) {
                 $ben_status=1;
                 $ben_status_msg=Config::get('globalconstants.ben_status.' . $benStatusObj->ben_status);
               // $paymentDetails .= '<h5 class="text-success"><b>Beneficiary Status : ' . Config::get('globalconstants.ben_status.' . $benStatusObj->ben_status) . '</b></h5>';
            }
            else {
                 $ben_status=0;
                 $ben_status_msg=Config::get('globalconstants.ben_status.' . $benStatusObj->ben_status);
                // $paymentDetails .= '<h5 class="text-warning"><b>Beneficiary Status : ' . Config::get('globalconstants.ben_status.' . $benStatusObj->ben_status) . '</b></h5>';
            }
            
            //$paymentDetails .= '<h5 class="text-primary"><b>Bank A/c No : ' . $this->maskString(trim($benStatusObj->last_accno),2,3) . ', IFSC : ' . $this->maskString($benStatusObj->last_ifsc, 4, 2). '</b></h5>';
            $bank_account_no=$this->maskString(trim($benStatusObj->last_accno),2,3);
            $bank_ifsc=$this->maskString($benStatusObj->last_ifsc, 4, 2);
            $current_yymm = date('ym');
            if ($benStatusObj->end_yymm <= $current_yymm) {
                 $payment_stop_msg=Config::get('constants.month_list.' . substr($benStatusObj->end_yymm, 2, 2)) . ' - 20' . substr($benStatusObj->end_yymm, 0, 2) . ' beneficiary payment will be stopped';
                 //$paymentDetails .= '<h5 class="text-primary"><b>From ' . Config::get('constants.month_list.' . substr($benStatusObj->end_yymm, 2, 2)) . ' - 20' . substr($benStatusObj->end_yymm, 0, 2) . ' beneficiary payment will be stopped';
            }
            $failedObj = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $ben_id)->where('failed_type', 1)->where('legacy_validation_failed',false)->orderBy('id', 'DESC')->first();
            $viewError = '';
            if (isset($failedObj)) {
                if ($failedObj->edited_status == 0) {
                    if ($failedObj->pmt_mode == 1) {
                        if ($failedObj->status_code == 'NA') {
                            $viewError = $failedObj->remarks;
                        } else {
                            $viewError = Config::get('bandhancode.bandhan_response_code.' . $failedObj->status_code);
                        }
                    } else {
                        $viewError = Config::get('bandhancode.sbi_response_code.' . $failedObj->status_code);
                    }
                }
                if ($viewError != '') {
                   // $paymentDetails .= '<h5 class="text-danger"><b>Account Validation Error : ' . $viewError . '</b></h5>';
                    $acc_validation_error_msg='Account Validation Error:'.$viewError;
                }
                if ($failedObj->edited_status == 1) {
                     $acc_validation_error_msg='Account validation error edited from the verifier end';
                    //$paymentDetails .= '<h5 class="text-danger"><b>Account validation error edited from the verifier end</b></h5>';
                }
                if ($failedObj->edited_status == 2 && $benStatusObj->acc_validated == '0') {
                     $acc_validation_error_msg='Account validation error edited but Validation lot creation pending';
                     //$paymentDetails .= '<h5 class="text-danger"><b>Account validation error edited but Validation lot creation pending</b></h5>';
                }
            }
            // New end

           /* $paymentDetails .= '<table class="table table-bordered table-condensed table-striped" id="paymentTable" cellspacing="0" style="font-size: 14px;" width="100%">  
                  <thead>
                    <tr role="row">
                      <th>Month</th>
                      <th>Payment Status</th>
                    </tr>
                  </thead>
                  <tbody>';*/
            if ($fin_year == '2021-2022') {
                $startmonth = 9;
            } else {
                $startmonth = 4;
            }
            $endmonth = 3;
            $finalendmonth = 0;
            $loopcount = '';
            if ($endmonth == 1 || $endmonth == 2 || $endmonth == 3) {
                $finalendmonth += $endmonth;
                $loopcount = (12 + $finalendmonth);
            } else {

                $loopcount = ($endmonth - $startmonth) + 4;
            }

            $count = $startmonth;
            $flag = 0;
            $k=0;
            for ($i = $startmonth; $i <= $loopcount; $i++) {
                if ($i == 13) {
                    $count = 1;
                }
                $getMonthColumn = Helper::getMonthColumn($count);
                $lot_status = $getMonthColumn['lot_status'];
                $lot_column = $getMonthColumn['lot_column'];
                $lot_type = $getMonthColumn['lot_type'];
                // echo $benStatusObj->$lot_status;die();
                if ($benTransObj->$lot_status == 'G' || $benTransObj->$lot_status == 'P' || $benTransObj->$lot_status == 'S' || $benTransObj->$lot_status == 'F' || $benTransObj->$lot_status == 'H') {
                    //$lot_no = $benTransObj->$lot_column;
                    $lotStatus = $benTransObj->$lot_status;
                    $lot_month = Config::get('constants.monthval.' . $count);
                    $payment_data[$k]['Month']= $lot_month;
                     /*$paymentDetails .= '  
                        <tr>    
                            <td>' . $lot_month . '</td>';*/
                    if ($lotStatus == 'S' || $lotStatus == 'F') {
                        //$paymentDetails .= '<td>' . Config::get('globalconstants.lot_status.' . $lotStatus) . '</td>';
                           $payment_data[$k]['PaymentStatus']= Config::get('globalconstants.lot_status.' . $lotStatus);
                    } else {
                        // $paymentDetails .= '<td>Payment Under Process</td>';
                         $payment_data[$k]['PaymentStatus']='Payment Under Process';
                    }

                   // $paymentDetails .= '</tr>';
                    $flag = 1;
                    $k++;
                } else {
                }
                $count++;
            }
            if ($flag == 0) {
                // $paymentDetails .= '<tr><td colspan="2">Payment process yet to start.</td></tr>';
                $return_status=0;
                $return_msg='Payment process yet to start.';
            }
            else{
                $return_status=1;
            }
            //$paymentDetails .= '</tbody></table>';
        }
        else {
            $return_status=0;
            $return_msg='No payment record found.';
            /*$paymentDetails .= '<table class="table table-bordered table-condensed table-striped" id="paymentTable" cellspacing="0" style="font-size: 14px;" width="100%">  
              <thead>
                <tr role="row">
                  <th>Month</th>
                  <th>Payment Status</th>
                </tr>
              </thead>
              <tbody>';
            $paymentDetails .= '<tr><td colspan="2">No payment record found.</td></tr>';
            $paymentDetails .= '</tbody></table>';*/
        }
        //return $paymentDetails;
        return response()->json(
        [
            'ben_status' => $ben_status, 
            'ben_status_msg' => $ben_status_msg,
            'bank_acc_validation_msg' => $bank_acc_validation_msg, 
            'bank_account_no' => $bank_account_no, 
            'bank_ifsc' => $bank_ifsc, 
            'payment_data' => $payment_data, 
            'payment_stop_msg'=> $payment_stop_msg, 
            'acc_validation_error_msg'=> $acc_validation_error_msg, 
            'return_status' => $return_status, 
            'return_msg' => $return_msg,
            'return_data' => $return_data
        ]);
    }
    
     function maskString($inputString, $first_show, $last_show) {
        if (strlen($inputString) <= ($first_show+$last_show)) {
            return $inputString;
        } else {
            $firstTwo = substr($inputString, 0, $first_show);
            $lastTwo = substr($inputString, -$last_show);
            $masked = str_repeat('x', strlen($inputString) - ($first_show+$last_show));
            return $firstTwo . $masked . $lastTwo;
        }
    }

    
}
