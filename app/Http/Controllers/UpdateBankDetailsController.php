<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Configduty;
use App\Models\getModelFunc;
use App\Models\District;
use App\Models\UrbanBody;
use App\Models\Assembly;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\GP;
use App\Models\SchemeDocMap;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;
use App\Models\DataSourceCommon;
use App\Models\BankDetails;
use Monolog\Handler\IFTTTHandler;
use App\Helpers\DupCheck;

class UpdateBankDetailsController extends Controller
{
  public function __construct()
  {
    set_time_limit(60);
    $this->middleware('auth');
  }
  /*
    Landing Page on the De-activated Beneficiary
  */
  public function index()
  {
    //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');

    if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Verifier' || Auth::user()->designation_id == 'Delegated Approver' || Auth::user()->designation_id == 'Delegated Verifier') {
      return view('bank-details-update/index');
    } else {
      return redirect('/')->with('success', 'Unauthorized');
    }
  }
  /*
    Line Listing Beneficiary List
  */
  public function getLineListBankEdit(Request $request)
  {
    if ($request->ajax()) {
      $getModelFunc = new getModelFunc();
      $schemaname = $getModelFunc->getSchemaDetails();
      $designation = Auth::user()->designation_id;
      $user_id = Auth::user()->id;
      $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
      $distCode = $dutyObj->district_code;
      if ($dutyObj->is_urban == 1) {
        $bodyCode = $dutyObj->urban_body_code;
      } else {
        $bodyCode = $dutyObj->taluka_code;
      }
      $beneficiary_id = $request->beneficiary_id;
      $application_id = $request->application_id;
      $ss_card_no = $request->ss_card_no;
      $query = '';
      if ($designation == 'Delegated Verifier' || $designation == 'Delegated Approver' || $designation == 'Approver' || $designation == 'Verifier') {
        if (!empty($beneficiary_id) || !empty($application_id) || !empty($ss_card_no)) {
          $query = "(select md.district_name, bl_div.block_subdiv_name, bp.ben_fname,bp.ben_mname, bp.ben_lname,bp.beneficiary_id, bp.mobile_no,bp.ss_card_no, bp.application_id, bp.next_level_role_id, bc.block_ulb_name,bc.gp_ward_name, bc.rural_urban_id, bb.bank_code,bb.bank_ifsc, bp.ds_phase,bp.dob,to_char(bp.dob + interval '60 year','yymm')::smallint as end_yymm from lb_scheme.ben_personal_details bp
            JOIN lb_scheme.ben_contact_details bc ON bp.beneficiary_id=bc.beneficiary_id
            JOIN lb_scheme.ben_bank_details bb ON bb.beneficiary_id=bp.beneficiary_id 
            JOIN public.m_district md ON md.district_code=bp.created_by_dist_code 
            JOIN (select block_code as block_subdiv_code,block_name as block_subdiv_name from public.m_block UNION ALL
              select sub_district_code as block_subdiv_code, sub_district_name as block_subdiv_name from public.m_sub_district
            ) bl_div ON bl_div.block_subdiv_code=bp.created_by_local_body_code
            where bp.next_level_role_id=0 and bp.created_by_dist_code=" . $distCode . " ";
          if ($designation == 'Verifier' || $designation == 'Delegated Verifier') {
            $query .= " and bp.created_by_local_body_code=" . $bodyCode;
          }
          if (!empty($beneficiary_id)) {
            $query .= " and bp.beneficiary_id=" . $beneficiary_id;
          }
          if (!empty($application_id)) {
            $query .= " and bp.application_id=" . $application_id;
          }
          if (!empty($ss_card_no)) {
            $query .= " and bp.ss_card_no='" . $ss_card_no . "'";
          }
          $query .= ') UNION ALL ';
          $query .= "(select md.district_name, bl_div.block_subdiv_name, bp.ben_fname,bp.ben_mname, bp.ben_lname,bp.beneficiary_id, bp.mobile_no,bp.ss_card_no, bp.application_id, bp.next_level_role_id, bc.block_ulb_name,bc.gp_ward_name,bc.rural_urban_id, bb.bank_code,bb.bank_ifsc, bp.ds_phase,bp.dob,to_char(bp.dob + interval '60 year','yymm')::smallint as end_yymm from lb_scheme.faulty_ben_personal_details bp
            JOIN lb_scheme.faulty_ben_contact_details bc ON bp.beneficiary_id=bc.beneficiary_id
            JOIN lb_scheme.faulty_ben_bank_details bb ON bb.beneficiary_id=bp.beneficiary_id 
            JOIN public.m_district md ON md.district_code=bp.created_by_dist_code 
            JOIN (select block_code as block_subdiv_code,block_name as block_subdiv_name from public.m_block UNION ALL
              select sub_district_code as block_subdiv_code, sub_district_name as block_subdiv_name from public.m_sub_district
            ) bl_div ON bl_div.block_subdiv_code=bp.created_by_local_body_code
            where bp.next_level_role_id=0 and bp.created_by_dist_code=" . $distCode . " ";
          if ($designation == 'Verifier' || $designation == 'Delegated Verifier') {
            $query .= " and bp.created_by_local_body_code=" . $bodyCode;
          }
          if (!empty($beneficiary_id)) {
            $query .= " and bp.beneficiary_id=" . $beneficiary_id;
          }
          if (!empty($application_id)) {
            $query .= " and bp.application_id=" . $application_id;
          }
          if (!empty($ss_card_no)) {
            $query .= " and bp.ss_card_no='" . $ss_card_no . "'";
          }
          $query .= ')';
          // print $query;die();
          $data = DB::connection('pgsql_appread')->select($query);
        } else {
          $data = collect([]);
        }
      } else {
        $data = collect([]);
      }

      return datatables()->of($data)
        // ->addIndexColumn()
        ->addColumn('action', function ($data) use ($schemaname) {
          $action = '';
          $aadhar_count = DB::connection('pgsql_appread')->table('lb_scheme.ben_aadhar_details')->where('beneficiary_id', $data->beneficiary_id)->where('application_id', $data->application_id)->where('is_dup', 1)->count();
          if ($aadhar_count > 0) {
            $action = '<h4><label class="label label-danger">Please De-duplicate Aadhar First</label></h4>';
          } else {
            if ($data->next_level_role_id == 0) {
              $benStatus = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->select('ben_status')->where('ben_id', $data->beneficiary_id)->first();
              if (!empty($benStatus)) {
                if ($benStatus->ben_status == 1) {
                  // $action = '<button class="btn btn-info btn-sm" name="ben_edit" class="ben_edit" value="'.$data->beneficiary_id.'" onclick="editFunction('.$data->beneficiary_id.');"><i class="fa fa-edit"></i> Edit</button>';
                  $action = '<div>
                  <select class="form-control" name="select_item_update" id="select_item_update_' . $data->beneficiary_id . '" required>
                    <option value="">---- Select ----</option>
                    <option value="bank">Update Bank Details</option>
                    <option value="mobile">Update Mobile Number</option>
                  </select>
                </div>
                <div align="center" style="margin-top:5px;">
                  <button type="button" class="btn btn-info btn-block btn-sm" name="ben_edit" class="ben_edit" value="' . $data->beneficiary_id . '" onclick="editFunction(' . $data->beneficiary_id . ');"><i class="fa fa-edit"></i> Edit</button>
                </div>';
                } elseif ($benStatus->ben_status == -97) {
                  $action = '<h5><label class="label label-danger">Bank A/c & IFSC Duplicate, please De-duplicate Bank Acc.</label></h5>';
                } elseif ($benStatus->ben_status == -98) {
                  $action = '<h5><label class="label label-danger">Bank A/c & IFSC Duplicate Reject</label></h5>';
                } elseif ($benStatus->ben_status == -99) {
                  $action = '<h5><label class="label label-danger">In-active Beneficiary</label></h5>';
                } else {
                  $action = '<h5><label class="label label-danger">In-active Beneficiary</label></h5>';
                }
              } else {
                if ($data->ds_phase == 4 || $data->ds_phase == 5) {
                  $action = '<p class="text-primary"><b>Account validation and payment<br> process has not been initiated.</b></p>';
                } else {
                  $action = '<h5><label class="label label-danger">Beneficiary is under migration process</label></h5>';
                }
              }
              $current_yymm = date('ym');
              if ($data->end_yymm <= $current_yymm) {
                $monthYear = Config::get('constants.month_list.' . substr($data->end_yymm, 2, 2)) . ' - 20' . substr($data->end_yymm, 0, 2);
                $action = '<p class="text-primary"><b>Beneficiary age exceeded <br>60 years on ' . $monthYear . '</b></p>';
              }
            } else {
              $action = '<h5><label class="label label-danger">In-active Beneficiary</label></h5>';
            }
          }
          return $action;
        })
        ->addColumn('beneficiary_id', function ($data) {
          return $data->beneficiary_id;
        })
        ->addColumn('name', function ($data) {
          return $data->ben_fname . ' ' . $data->ben_mname . ' ' . $data->ben_lname;
        })
        ->addColumn('ss_card_no', function ($data) {
          return $data->ss_card_no;
        })
        ->addColumn('application_id', function ($data) {
          return $data->application_id;
        })
        ->addColumn('address', function ($data) {
          $address = '';
          $address = 'District - ' . $data->district_name . '<br>';
          if ($data->rural_urban_id == 1) {
            $address .= 'Sub-division - ' . $data->block_subdiv_name . '<br>';
            $address .= 'Municipality - ' . $data->block_ulb_name . '<br>';
            $address .= 'Ward - ' . $data->gp_ward_name;
          } else {
            $address .= 'Block - ' . $data->block_ulb_name . '<br>';
            $address .= 'GP - ' . $data->gp_ward_name;
          }
          return $address;
        })
        ->addColumn('bank_info', function ($data) {
          $bank = '';
          $bank .= 'A/c No - ' . $data->bank_code . '<br>';
          $bank .= 'IFSC - ' . $data->bank_ifsc;
          return $bank;
        })
        ->rawColumns(['beneficiary_id', 'name', 'ss_card_no', 'application_id', 'address', 'bank_info', 'action'])
        ->make(true);
    }
  }
  /*
    Get Beneficiary Data 
  */
  public function getBenDataForBankUpdate(Request $request)
  {
    // dd($request->all());
    $statuscode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statuscode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statuscode);
    }
    try {
      $ben_id = $request->benid;
      $tableName = Helper::getTable($ben_id);
      $personalDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benTable'])->where('beneficiary_id', $ben_id)->first();
      $bankDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benBankTable'])->where('beneficiary_id', $ben_id)->first();

      $ben_name = $personalDetails->ben_fname . ' ' . $personalDetails->ben_mname . ' ' . $personalDetails->ben_lname;
      $benfather_name = $personalDetails->father_fname . ' ' . $personalDetails->father_mname . ' ' . $personalDetails->father_lname;
      $mobile_no = $personalDetails->mobile_no;
      $caste = $personalDetails->caste;
      $ben_id = $personalDetails->beneficiary_id;
      $dob = date('d-m-Y', strtotime($personalDetails->dob));
      $gender = $personalDetails->gender;
      $bank_name = $bankDetails->bank_name;
      $bank_ifsc = $bankDetails->bank_ifsc;
      $branch_name = $bankDetails->branch_name;
      $bank_code = $bankDetails->bank_code;
      $application_id = $personalDetails->application_id;
      // dd($personalDetails->tosql());
      $response = array(
        'personaldata' => $personalDetails,
        'ben_name' => $ben_name,
        'benfather_name' => $benfather_name,
        'mobile_no' => $mobile_no,
        'caste' => $caste,
        'dob' => $dob,
        'gender' => $gender,
        'bank_name' => $bank_name,
        'bank_ifsc' => $bank_ifsc,
        'branch_name' => $branch_name,
        'bank_code' => $bank_code,
        'ben_id' => $ben_id,
        'application_id' => $application_id
      );
      // dd($response);
    } catch (\Exception $e) {
      $response = array(
        'exception' => true,
        'exception_message' => $e->getMessage(),
      );
      $statuscode = 400;
    } finally {
      return response()->json($response, $statuscode);
    }
  }
  /*
    Update Bank Details for Approved Beneficiary
  */
  public function updateApprovedBenBankDetails(Request $request)
  {

    date_default_timezone_set('Asia/Kolkata');
    $statuscode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statuscode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statuscode);
    }
    try {
      // DB::beginTransaction();
      // dd('here');
      DB::connection('pgsql_appwrite')->beginTransaction();
      DB::connection('pgsql_payment')->beginTransaction();
      DB::connection('pgsql_encwrite')->beginTransaction();
      $this->validateInput($request);
      // dd('ok');
      $getModelFunc = new getModelFunc();
      // dd($getModelFunc);
      $schemaname = $getModelFunc->getSchemaDetails();
      $pension_details_encloser1 = new DataSourceCommon;
      $pension_details_encloser2 = new DataSourceCommon;
      // dd('here');
      $Table = $getModelFunc->getTable('', '', 6, 1);
      $Table2 = $getModelFunc->getTableFaulty('', '', 6, 1);
      $pension_details_encloser1->setConnection('pgsql_encwrite');
      $pension_details_encloser2->setConnection('pgsql_encwrite');
      $pension_details_encloser1->setTable('' . $Table);
      $pension_details_encloser2->setTable('' . $Table2);
      $beneficiary_id = $request->benId;
      $new_bank_ifsc = $request->bank_ifsc;
      $new_bank_name = $request->bank_name;
      $new_bank_account_number = $request->bank_account_number;
      $new_branch_name = $request->branch_name;
      $old_bank_ifsc = $request->old_bank_ifsc;
      $old_bank_accno = $request->old_bank_accno;
      $remarks = $request->remarks;

      $tableName = Helper::getTable($beneficiary_id);
      $currentyear = date('Y');
      $prevYear = date('Y') - 1;
      $nextyear = date('Y') + 1;
      $month = date('n');
      if ($month > 3) {
        $cur_fin_year = $currentyear . '-' . $nextyear;
      } else {
        $cur_fin_year = $prevYear . '-' . ($prevYear + 1);
      }
      // $benPaymentDuplicateAcCount = DB::connection('pgsql_payment')
      //   ->table($schemaname . '.ben_payment_details')
      //   ->whereRaw("trim(last_ifsc)=trim("."'".$new_bank_ifsc."'".")")->whereRaw("trim(last_accno)=trim("."'".$new_bank_account_number."'".")")
      //   ->whereIn('ben_status', [1,-97])->count('ben_id');
      // dd('ok');
      $duplicate_row = DB::connection('pgsql_appwrite')->select("select count(1) as cnt from lb_scheme.duplicate_bank_view where trim(bank_code)='" . $new_bank_account_number . "' and trim(bank_ifsc)='" . $new_bank_ifsc . "'");
      $benPaymentDuplicateAcCount = $duplicate_row[0]->cnt;
      $is_update_happens = 0;

      if (!empty($new_bank_account_number)) {
        // $DupCheckBankOap = DupCheck::getDupCheckBank(10,$new_bank_account_number);
        $DupCheckBankOap = 0;
        if (!empty($DupCheckBankOap)) {
          $is_update_happens = 0;
          $msg = 'Duplicate Bank Account Number present in Old Age Pension Scheme with Beneficiary ID- ' . $DupCheckBankOap . '';
          return $response = array(
            'status' => 2,
            'msg' => $msg,
            'type' => 'red',
            'icon' => 'fa fa-warning',
            'title' => 'Warning!'
          );
        }
        // $DupCheckBankJohar = DupCheck::getDupCheckBank(1,$new_bank_account_number);
        $DupCheckBankJohar = 0;
        if (!empty($DupCheckBankJohar)) {
          $is_update_happens = 0;
          $msg = 'Duplicate Bank Account Number present Jai Johar Pension Scheme with Beneficiary ID- ' . $DupCheckBankJohar . '';
          return $response = array(
            'status' => 2,
            'msg' => $msg,
            'type' => 'red',
            'icon' => 'fa fa-warning',
            'title' => 'Warning!'
          );
        }
        // $DupCheckBankBandhu = DupCheck::getDupCheckBank(3,$new_bank_account_number);
        $DupCheckBankBandhu = 0;
        // dump($DupCheckBankBandhu);
        if (!empty($DupCheckBankBandhu)) {
          $is_update_happens = 0;
          $msg = 'Duplicate Bank Account Number present Taposili Bandhu(for SC) Pension Scheme with Beneficiary ID- ' . $DupCheckBankBandhu . '';
          return $response = array(
            'status' => 2,
            'msg' => $msg,
            'type' => 'red',
            'icon' => 'fa fa-warning',
            'title' => 'Warning!'
          );
        }
      }
      if ($benPaymentDuplicateAcCount > 0) {
        $is_update_happens = 0;
        $msg = 'Bank A/c & IFSC already exist.';
        $response = array(
          'status' => 2,
          'msg' => $msg,
          'type' => 'red',
          'icon' => 'fa fa-warning',
          'title' => 'Warning!'
        );
      } else {
        // dd('ELSE');
        $benPaymentObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $beneficiary_id)->first();
        //        $query = DB::connection('pgsql_payment')
        //     ->table($schemaname . '.ben_payment_details')
        //     ->where('ben_id', $beneficiary_id);

        // dd($query->toSql(), $query->getBindings());
        $is_update_happens = 0;
        $acc_validate = $benPaymentObj->acc_validated;
        $ben_status = $benPaymentObj->ben_status;
        // dump($benPaymentObj);
        // dump($acc_validate);
        // dump($ben_status);
        // dump($benPaymentObj->payment_process);
        // dd(DB::connection('pgsql_payment')->getQueryLog());

        if (
          $benPaymentObj->payment_process == 0 &&
          ($acc_validate == '0' || $acc_validate == '2' || $acc_validate == '6' || $acc_validate == '3' || $acc_validate == '4') && $ben_status == 1
        ) {
          // dd('okk');
          // $is_update_happens = 1;
          $bank_details = BankDetails::where('is_active', 1)->where('ifsc', $new_bank_ifsc)->get(['bank', 'branch'])->first();
          // dd(!empty($bank_details));
          if (!empty($bank_details)) {
            if ((trim($bank_details->bank) == trim($new_bank_name)) && (trim($bank_details->branch) == trim($new_branch_name))) {
              $is_update_happens = 1;
            } else {
              $is_update_happens = 0;
              $response = array(
                'status' => 3,
                'msg' => 'Bank account name or bank branch name are not matched',
                'type' => 'red',
                'icon' => 'fa fa-warning',
                'title' => 'Not Match'
              );
            }
          } else {
            // dd('ok2');
            $is_update_happens = 0;
            $response = array(
              'status' => 4,
              'msg' => 'This ' . $new_bank_ifsc . ' IFSC is not registered in our system.',
              'type' => 'blue',
              'icon' => 'fa fa-info',
              'title' => 'IFSC Not Found'
            );
          }
        } else {
          $is_update_happens = 0;
          $msg = '';
          if ($ben_status != 1) {
            $msg = 'This beneficiary is inactive';
          } else {
            if ($acc_validate == '1') {
              $msg = 'This beneficiary is under validation process (pending validation response from bank end), please try after some days.';
            } /*else if ($acc_validate == '3') {
       $msg = 'This beneficiary Account/Name validation failed, please update bank details from verifier end.';
     } else if ($acc_validate == '4'){
       $msg = 'This beneficiary payment transaction failed, please update bank details from verifier end.';
     }*/ else {
              $msg = 'This beneficiary is under payment process, please try after some days.';
            }
          }
          $response = array(
            'status' => 5,
            'msg' => $msg,
            'type' => 'blue',
            'icon' => 'fa fa-warning',
            'title' => 'Warning!'
          );
        }
      }

      // dd($is_update_happens);

      // $benPaymentObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $beneficiary_id)->first();
      // $is_update_happens = 0;
      // $acc_validate = $benPaymentObj->acc_validated;
      // $ben_status = $benPaymentObj->ben_status;
      // if (($benPaymentObj->sep_lot_status == 'R' || $benPaymentObj->sep_lot_status == 'S') && 
      //   ($benPaymentObj->oct_lot_status == 'R' || $benPaymentObj->oct_lot_status == 'S') && 
      //   ($benPaymentObj->nov_lot_status == 'R' || $benPaymentObj->nov_lot_status == 'S') &&
      //   ($benPaymentObj->dec_lot_status == '' || $benPaymentObj->dec_lot_status == 'R' || $benPaymentObj->dec_lot_status == 'S') && 
      //   ($acc_validate == '0' || $acc_validate == '2' || $acc_validate == '6') && $ben_status == 1) {
      //   $is_update_happens = 1;
      // }
      // else {
      //   $is_update_happens = 0;
      //   $msg = '';
      //   if ($ben_status != 1) {
      //     $msg = 'This beneficiary is inactive';
      //   }
      //   else {
      //     if ($acc_validate == '1') {
      //       $msg = 'This beneficiary is under validation process, please try after some days.';
      //     } else {
      //       $msg = 'This beneficiary is under payment process, please try after some days.';
      //     }
      //   }
      //   $response = array(
      //     'status' => 5, 'msg' => $msg,
      //     'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Warning!'
      //   );
      // }
      // echo 1;die();
      // echo $is_update_happens;die();
      if ($is_update_happens == 1) {
        // if (($new_bank_account_number == $old_bank_accno) && ($new_bank_ifsc == $old_bank_ifsc)) {
        //   // echo $is_update_happens;
        //   $response = array(
        //     'status' => 6, 'msg' => 'Bank account number and ifsc same as previous one.',
        //     'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Required'
        //   );
        // } else {
        $bank_details = BankDetails::where('is_active', 1)->where('ifsc', $new_bank_ifsc)->get(['bank', 'branch'])->first();
        // dd($bank_details);
        // dd(!empty($bank_details));
        if (!empty($bank_details)) {
          /*-------------- Document Upload Section ----------------*/
          if (!empty($request->file('upload_bank_passbook'))) {
            $attributes = array();
            $pension_details = array();
            $query = DocumentType::select('id', 'doc_type', 'doc_name', 'doc_size_kb')->where('id', 10);
            $doc_arr = $query->first();
            // dd($doc_arr);
            $required = 'required';
            $rules['upload_bank_passbook'] = $required . '|mimes:' . $doc_arr->doc_type . '|max:' . $doc_arr->doc_size_kb . ',';
            $messages['upload_bank_passbook.max'] = "The file uploaded for " . $doc_arr->doc_name . " size must be less than :max KB";
            $messages['upload_bank_passbook.mimes'] = "The file uploaded for " . $doc_arr->doc_name . " must be of type " . $doc_arr->doc_type;
            $messages['upload_bank_passbook.required'] = "Document for " . $doc_arr->doc_name . " must be uploaded";
            //dd($rules);
            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            if ($validator->passes()) {
              $valid = 1;
            } else {
              $valid = 0;
              $return_msg = $validator->errors()->all();
              $return_status = 0;

              return $response = array(
                'status' => 7,
                'msg' => $return_msg,
                'type' => 'red',
                'icon' => 'fa fa-warning',
                'title' => 'Error'
              );
            }
            // dd($valid);
            if ($valid == 1) {
              $upload_bank_passbook = $request->file('upload_bank_passbook');
              $img_data = file_get_contents($upload_bank_passbook);
              $extension = $upload_bank_passbook->getClientOriginalExtension();
              $mime_type = $upload_bank_passbook->getMimeType();
              $base64 = base64_encode($img_data);

              $tableNameDoc = Helper::getTable('', $benPaymentObj->application_id);

              $insertIntoArchieve = "INSERT INTO lb_scheme.ben_attach_documents_arch(
                  application_id, beneficiary_id, document_type, attched_document, created_by_level, created_at, updated_at, deleted_at, created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,doc_status,action_by,action_ip_address,action_type)
                  select application_id, beneficiary_id, document_type, attched_document, created_by_level,created_at,updated_at, '" . date('Y-m-d H:i:s') . "', created_by, ip_address, document_extension, document_mime_type, created_by_dist_code, created_by_local_body_code,2,action_by,action_ip_address,action_type
                  from lb_scheme." . $tableNameDoc['benDocTable'] . " where application_id = " . $benPaymentObj->application_id . " and document_type = " . $doc_arr->id;
              $executeInsert = DB::connection('pgsql_encwrite')->statement($insertIntoArchieve);

              // $executeInsert=1;
              if ($executeInsert) {
                $pension_details['attched_document'] = $base64;
                $pension_details['document_extension'] = $extension;
                $pension_details['document_mime_type'] = $mime_type;
                $pension_details['updated_at'] = date('Y-m-d H:i:s');
                $pension_details['action_by'] = Auth::user()->id;
                $pension_details['action_ip_address'] = request()->ip();
                $pension_details['action_type'] = class_basename(request()->route()->getAction()['controller']);
                // print_r($pension_details);
                if ($benPaymentObj->faulty_status == false) {
                  // dd($doc_arr->id);
                  $docBankUpdate = $pension_details_encloser1->where('document_type', $doc_arr->id)
                    ->where('application_id', $benPaymentObj->application_id)->update($pension_details);
                } else {
                  // dd('2');
                  // dd($doc_arr->id);
                  $docBankUpdate = $pension_details_encloser2->where('document_type', $doc_arr->id)
                    ->where('application_id', $benPaymentObj->application_id)->update($pension_details);
                }
                // dd($docBankUpdate->toSql(), $docBankUpdate->getBindings());

              }

              // Others Updates
              $personalDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benTable'])->where('beneficiary_id', $beneficiary_id)->first();
              $bankDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benBankTable'])->where('beneficiary_id', $beneficiary_id)->first();
              $contactDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benContactTable'])->where('beneficiary_id', $beneficiary_id)->first();
              $updateBenDetails = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->first();
              // dump($acc_validate);
              if ($acc_validate == '3' || $acc_validate == '4') {
                $failedPaymentObj = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)->whereIn('edited_status', [0, 1])->orderBy('created_at', 'desc')->first();
              }


              // dump($bank_details->bank); dump($new_bank_name); dump($bank_details->branch); dd($new_branch_name);

              if ((trim($bank_details->bank) == trim($new_bank_name)) && (trim($bank_details->branch) == trim($new_branch_name))) {
                $duplicate_row = DB::connection('pgsql_appwrite')->select("select count(1) as cnt from lb_scheme.duplicate_bank_view where trim(bank_code)='" . $new_bank_account_number . "'  and application_id <> $personalDetails->application_id ");
                $accountCheck = $duplicate_row[0]->cnt;
                $dupBankTableCheck = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')
                  ->where('ben_id', $beneficiary_id)->where('ben_status', '-97')->count('ben_id');
                if ($accountCheck > 0) {
                  return $response = array(
                    'status' => 5,
                    'msg' => 'This bank account no is already registered.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Duplicate!'
                  );
                }

                $new_value = [];
                $old_value = [];
                $insert = [];
                $old_bank_name = $bankDetails->bank_name;
                $old_bank_acc_no = $bankDetails->bank_code;
                $old_branch_name = $bankDetails->branch_name;
                $old_bank_ifsc = $bankDetails->bank_ifsc;
                $old_fname = $personalDetails->ben_fname;
                $old_mname = $personalDetails->ben_mname;
                $old_lname = $personalDetails->ben_lname;
                if ($acc_validate == '3' || $acc_validate == '4') {
                  if ($failedPaymentObj->status_code == '-7') {
                    $old_value['ben_fname'] = trim($old_fname);
                    $old_value['ben_mname'] = trim($old_mname);
                    $old_value['ben_lname'] = trim($old_lname);
                  }
                }

                $new_value['bank_name'] = trim($new_bank_name);
                $new_value['branch_name'] = trim($new_branch_name);
                $new_value['bank_ifsc'] = trim($new_bank_ifsc);
                $new_value['bank_code'] = trim($new_bank_account_number);
                $old_value['bank_name'] = trim($old_bank_name);
                $old_value['branch_name'] = trim($old_branch_name);
                $old_value['bank_ifsc'] = trim($old_bank_ifsc);
                $old_value['bank_code'] = trim($old_bank_acc_no);
                if ($acc_validate == '3' || $acc_validate == '4') {
                  if ($failedPaymentObj->failed_type == '1') {
                    $bank_update_code = 35;
                  } else if ($failedPaymentObj->failed_type == '2') {
                    $bank_update_code = 36;
                  } else if ($failedPaymentObj->failed_type == '3') {
                    $bank_update_code = 37;
                  }
                }
                if ($acc_validate == '3' || $acc_validate == '4') {
                  $insert['failed_tbl_id'] = $failedPaymentObj->id;
                  $insert['pmt_mode'] = $failedPaymentObj->pmt_mode;
                  $insert['failed_type'] = $failedPaymentObj->failed_type;
                  $insert['legacy_validation_update'] = $failedPaymentObj->legacy_validation_failed;
                  $insert['next_level_role_id'] = 1;
                  $insert['update_code'] = $bank_update_code;
                }
                $insert['beneficiary_id'] = $beneficiary_id;
                $insert['user_id'] = Auth::user()->id;
                $insert['old_data'] = json_encode($old_value);
                $insert['new_data'] = json_encode($new_value);
                if ($acc_validate == '0' || $acc_validate == '2' || $acc_validate == '6') {
                  $insert['next_level_role_id'] = 0;
                }
                $insert['dist_code'] = $bankDetails->created_by_dist_code;
                $insert['local_body_code'] = $bankDetails->created_by_local_body_code;
                $insert['rural_urban_id'] = $contactDetails->rural_urban_id;
                $insert['block_ulb_code'] = $contactDetails->block_ulb_code;
                $insert['gp_ward_code'] = $contactDetails->gp_ward_code;
                $insert['created_at'] = date('Y-m-d H:i:s');

                $insert['ip_address'] = request()->ip();
                $insert['remarks'] = $remarks;
                if ($acc_validate == '0' || $acc_validate == '2' || $acc_validate == '6') {
                  $insert['update_code'] = 4;
                }
                if ($acc_validate == '3' || $acc_validate == '4') {
                  $condition = "";
                  if ($failedPaymentObj->failed_type == '2') {
                    $condition .= "  failed_type='2' AND edited_status IN(0, 1)";
                  } else if ($failedPaymentObj->failed_type == '1') {
                    $condition .= "  failed_type='1' AND edited_status IN(0, 1)";
                  } else if ($failedPaymentObj->failed_type == '3') {
                    $condition .= "  failed_type='3' AND edited_status IN(0, 1)";
                  }
                }

                // dd(($old_bank_accno === $new_bank_account_number) && ($old_bank_ifsc === $new_bank_ifsc));
                if (($old_bank_accno === $new_bank_account_number) && ($old_bank_ifsc === $new_bank_ifsc)) {
                  if ($dupBankTableCheck > 0) {
                    $insertDup = [
                      // 'failed_tbl_id' => $failed_table_id,
                      'beneficiary_id' => $beneficiary_id,
                      'user_id' => Auth::user()->id,
                      'old_data' => json_encode($old_value),
                      'new_data' => json_encode($new_value),
                      'next_level_role_id' => 0,
                      'dist_code' => $bankDetails->created_by_dist_code,
                      'local_body_code' => $bankDetails->created_by_local_body_code,
                      'rural_urban_id' => $contactDetails->rural_urban_id,
                      'block_ulb_code' => $contactDetails->block_ulb_code,
                      'gp_ward_code' => $contactDetails->gp_ward_code,
                      'created_at' => date('Y-m-d H:i:s'),
                      'pmt_mode' => $failedPaymentObj->pmt_mode,
                      'failed_type' => $failedPaymentObj->failed_type,
                      'update_code' => 200,
                      'ip_address' => request()->ip(),
                      'legacy_validation_update' => $failedPaymentObj->legacy_validation_failed
                    ];
                    $payment_dup_update = [
                      'ben_status' => 200,
                      'is_approved' => 1,
                    ];
                    $update_ben_update = [
                      'next_level_role_id' => 0,
                      'update_code' => 1,
                      'remarks' => 'Same bank details. Direct Approved',
                      'updated_at' => date('Y-m-d H:i:s'),
                      'old_data' => json_encode($old_value),
                      'new_data' => json_encode($new_value)
                    ];
                    $ben_details_update = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->insert($insertDup);
                    $ben_payment_bank_dup = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')
                      ->where('ben_id', $beneficiary_id)->where('ben_status', '-97')->update($payment_dup_update);
                    $ben_payment_update = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
                      ->where('ben_id', $beneficiary_id)->where('ben_status', '-97')->update(['ben_status' => 1]);
                  }
                  if ((!empty($updateBenDetails)) && $updateBenDetails->beneficiary_id == $beneficiary_id) {
                    $update_ben_track = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)
                      ->whereIn('update_code', [35, 36, 4])->update($update_ben_update);
                  } else {
                    $update_ben_track = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->insert($insert);
                  }


                  if ($acc_validate == 3 || $acc_validate == 4) {
                    $failed_update_payment = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->whereRaw($condition)->where('ben_id', $beneficiary_id)->update(['approve_edited_status' => '1', 'edited_status' => '2', 'updated_at' => date('Y-m-d H:i:s')]);
                  }

                  if ($acc_validate == '6' || $acc_validate == '0') {
                    $failed_update_payment = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)->whereIn('edited_status', [0, 1])->update(['edited_status' => '2', 'updated_at' => date('Y-m-d H:i:s')]);
                  } elseif ($acc_validate == '2') {
                    $failed_update_payment = 1;
                  }

                  $ben_payment_paymentServer_update = [
                    'acc_validated' => 0,
                    'updated_at' => date('Y-m-d H:i:s')
                  ];
                  $failed_tbl_id = array();
                  if ($failedPaymentObj->failed_type == '2' && $failed_update_payment) {

                    $failed_type_app_id = $failedPaymentObj->failed_type;
                    // dd($failed_type_app_id, $beneficiary_id );
                    if (in_array($failed_type_app_id, [2])) {
                      $final_update = DB::connection('pgsql_payment')->select("Select payment.failed_update_approved_ben(in_ben_id => ARRAY[" . $beneficiary_id . "],  in_failed_type_id => " . $failed_type_app_id . ")");
                      $fun_call = $final_update[0]->failed_update_approved_ben;
                      // dd($fun_call);

                    }
                  }
                  $update_personal_details = DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benTable'])
                    ->where('beneficiary_id', $beneficiary_id)->update(['action_by' => Auth::user()->id, 'action_ip_address' => request()->ip(), 'action_type' => class_basename(request()->route()->getAction()['controller']), 'status' => '1']);

                  $benPaymentUpdate = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
                    ->where('ben_id', $beneficiary_id)->update($ben_payment_paymentServer_update);

                  $response = array(
                    'status' => 4,
                    'msg' => 'Bank Details Updated Succesfully.',
                    'type' => 'green',
                    'icon' => 'fa fa-check',
                    'title' => 'Success'
                  );
                } else {
                  // dd($insert);
                  $update_ben_update_diff = [];
                  $ben_bank_Details_mainServer_update = [
                    'bank_code' => trim($new_bank_account_number),
                    'bank_name' => trim($new_bank_name),
                    'branch_name' => trim($new_branch_name),
                    'bank_ifsc' => trim($new_bank_ifsc),
                    'is_dup' => 0,
                    'action_by' => Auth::user()->id,
                    'action_ip_address' => request()->ip(),
                    'action_type' => class_basename(request()->route()->getAction()['controller'])
                  ];
                  $ben_payment_paymentServer_update = [
                    'last_accno' => $new_bank_account_number,
                    'last_ifsc' => $new_bank_ifsc,
                    'acc_validated' => 0,
                    'updated_at' => date('Y-m-d H:i:s')
                  ];
                  $update_ben_update_diff['updated_at'] = date('Y-m-d H:i:s');
                  $update_ben_update_diff['remarks'] = $remarks;
                  $update_ben_update_diff['old_data'] = json_encode($old_value);
                  $update_ben_update_diff['new_data'] = json_encode($new_value);
                  if ($acc_validate == '3' || $acc_validate == '4') {
                    $update_ben_update_diff['update_code'] = 1;
                    $update_ben_update_diff['next_level_role_id'] = 0;
                  }
                  $failed_tbl_id = array();
                  if ($acc_validate == '3' || $acc_validate == '4') {

                    $failed_update_payment = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->whereRaw($condition)
                      ->where('ben_id', $beneficiary_id)->update(['approve_edited_status' => '1', 'edited_status' => '2', 'updated_at' => date('Y-m-d H:i:s')]);

                    if ($failedPaymentObj->failed_type == '2' && $failed_update_payment) {
                      // dd('ok');
                      $failed_type_app_id = $failedPaymentObj->failed_type;
                      // dd($failed_type_app_id, $beneficiary_id );
                      if (in_array($failed_type_app_id, [2])) {
                        $final_update = DB::connection('pgsql_payment')->select("Select payment.failed_update_approved_ben(in_ben_id => ARRAY[" . $beneficiary_id . "],  in_failed_type_id => " . $failed_type_app_id . ")");
                        $fun_call = $final_update[0]->failed_update_approved_ben;
                        // dd($fun_call);
                      }
                    }
                  }

                  if ($acc_validate == '0' || $acc_validate == '6') {
                    $failed_update_payment = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)->whereIn('edited_status', [0, 1, 2])->update(['edited_status' => '2', 'updated_at' => date('Y-m-d H:i:s')]);
                  } elseif ($acc_validate == '2') {
                    $failed_update_payment = 1;
                  }
                  // dd($failed_update_payment);
                  // dd($dupBankTableCheck);
                  if ($dupBankTableCheck > 0) {
                    $insertDup = [
                      // 'failed_tbl_id' => $failed_table_id,
                      'beneficiary_id' => $beneficiary_id,
                      'user_id' => Auth::user()->id,
                      'old_data' => json_encode($old_value),
                      'new_data' => json_encode($new_value),
                      'next_level_role_id' => 0,
                      'dist_code' => $bankDetails->created_by_dist_code,
                      'local_body_code' => $bankDetails->created_by_local_body_code,
                      'rural_urban_id' => $contactDetails->rural_urban_id,
                      'block_ulb_code' => $contactDetails->block_ulb_code,
                      'gp_ward_code' => $contactDetails->gp_ward_code,
                      'created_at' => date('Y-m-d H:i:s'),
                      'pmt_mode' => $failedPaymentObj->pmt_mode,
                      'failed_type' => $failedPaymentObj->failed_type,
                      'update_code' => 101,
                      'ip_address' => request()->ip(),
                      'legacy_validation_update' => $failedPaymentObj->legacy_validation_failed
                    ];
                    $payment_dup_update = [
                      'new_last_accno' => $new_bank_account_number,
                      'new_last_ifsc' => $new_bank_ifsc,
                      'ben_status' => 101,
                      'is_approved' => 1,
                    ];
                    $update_ben_track3 = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->insert($insertDup);
                    $payment_details_bank_code_dup = DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')
                      ->where('ben_id', $beneficiary_id)->where('ben_status', '-97')->update($payment_dup_update);
                    $ben_payment_update3 = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
                      ->where('ben_id', $beneficiary_id)->where('ben_status', '-97')->update(['ben_status' => 1]);
                  }

                  if ($updateBenDetails) {
                    $query = "INSERT INTO lb_scheme.update_ben_details_arc SELECT * FROM lb_scheme.update_ben_details WHERE beneficiary_id = $beneficiary_id AND id = $updateBenDetails->id";
                    $ubdate_ben_insert = DB::connection('pgsql_appwrite')->insert($query);
                    if ($ubdate_ben_insert == 1) {
                      $update_ben_revert = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $updateBenDetails->id)->delete();
                    }
                    $update_ben_track = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->insert($insert);
                  } else {
                    $update_ben_track = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->insert($insert);
                  }


                  $update_personal_details = DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benTable'])
                    ->where('beneficiary_id', $beneficiary_id)->update(['action_by' => Auth::user()->id, 'action_ip_address' => request()->ip(), 'action_type' => class_basename(request()->route()->getAction()['controller']), 'status' => '1']);
                  $benBankUpdate = DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benBankTable'])->where('beneficiary_id', $beneficiary_id)->update($ben_bank_Details_mainServer_update);
                  $benPaymentUpdate = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $beneficiary_id)->update($ben_payment_paymentServer_update);
                }

                // dump($update_personal_details); dump($benPaymentUpdate); dump($update_ben_track); dump($docBankUpdate); dump($failed_update_payment);

                // if ($beneficiary_id == 220942090) {
                //   dump($update_ben_track); dump($docBankUpdate); dump($benBankUpdate); dump($benPaymentUpdate); dd($failed_update_payment > 0);
                //   dump($new_bank_account_number); dd($old_bank_accno);
                // }

                if (($old_bank_accno === $new_bank_account_number) && ($old_bank_ifsc === $new_bank_ifsc)) {
                  if ($update_personal_details && $benPaymentUpdate && $update_ben_track && $docBankUpdate && ($failed_update_payment > 0)) {
                    // dd('Commit1');
                    DB::connection('pgsql_appwrite')->commit();
                    DB::connection('pgsql_payment')->commit();
                    DB::connection('pgsql_encwrite')->commit();

                    $response = array(
                      'status' => 4,
                      'msg' => 'Bank Details Updated Succesfully.',
                      'type' => 'green',
                      'icon' => 'fa fa-check',
                      'title' => 'Success'
                    );
                  }
                } elseif (($old_bank_accno != $new_bank_account_number) || ($old_bank_ifsc === $new_bank_ifsc) || ($old_bank_ifsc != $new_bank_ifsc)) {
                  //   if ($beneficiary_id == 220942090) {
                  // dump($update_ben_track); dump($docBankUpdate); dump($benBankUpdate); dump($benPaymentUpdate); dd($failed_update_payment > 0);
                  // }

                  if ($update_ben_track && $docBankUpdate && $benBankUpdate && $benPaymentUpdate && ($failed_update_payment > 0)) {
                    // dd('Commit');
                    DB::connection('pgsql_appwrite')->commit();
                    DB::connection('pgsql_payment')->commit();
                    DB::connection('pgsql_encwrite')->commit();

                    $response = array(
                      'status' => 4,
                      'msg' => 'Bank Details Updated Succesfully.',
                      'type' => 'green',
                      'icon' => 'fa fa-check',
                      'title' => 'Success'
                    );
                  } else {
                    // dd('No Update');
                    DB::connection('pgsql_appwrite')->rollback();
                    DB::connection('pgsql_payment')->rollback();
                    DB::connection('pgsql_encwrite')->rollback();
                    $response = array(
                      'status' => 8,
                      'msg' => 'Something went wrong1!!.',
                      'type' => 'red',
                      'icon' => 'fa fa-warning',
                      'title' => 'Error'
                    );
                  }
                } else {
                  // dd('No Update');
                  DB::connection('pgsql_appwrite')->rollback();
                  DB::connection('pgsql_payment')->rollback();
                  DB::connection('pgsql_encwrite')->rollback();
                  $response = array(
                    'status' => 8,
                    'msg' => 'Something went wrong2!!.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Error'
                  );
                }
              } else {
                $response = array(
                  'status' => 6,
                  'msg' => 'Bank account name or bank branch name are not matched',
                  'type' => 'red',
                  'icon' => 'fa fa-warning',
                  'title' => 'Not Match'
                );
              }
            }
          } else {
            $response = array(
              'status' => 9,
              'msg' => 'Please upload bank passbook copy.',
              'type' => 'red',
              'icon' => 'fa fa-warning',
              'title' => 'Required'
            );
          }
          /*-------------- End Document Upload Section ---------------*/
        } else {
          $response = array(
            'status' => 5,
            'msg' => 'This ' . $new_bank_ifsc . ' IFSC is not registered in our system.',
            'type' => 'blue',
            'icon' => 'fa fa-info',
            'title' => 'IFSC Not Found'
          );
        }

        // }
      }
    } catch (\Exception $e) {
      // dd($e);
      // if ($beneficiary_id == 215348967) {
      //   dd($e);
      // }
      // dd($e);
      // DB::rollback();
      //  $msg = 'Bank A/c & IFSC already exist.';
      //   $response = array(
      //     'status' => 2,
      //     'msg' => $msg,
      //     'type' => 'red',
      //     'icon' => 'fa fa-warning',
      //     'title' => 'Warning!'
      //   );
      DB::connection('pgsql_appwrite')->rollback();
      DB::connection('pgsql_payment')->rollback();
      DB::connection('pgsql_encwrite')->rollback();
      $response = array(
        'status' => 8,
        'msg' => 'Something Went wrong3!! ',
        'type' => 'red',
        'icon' => 'fa fa-warning',
        'title' => 'Error'
      );
      // $response = array(
      //   'exception' => true,
      //   // 'exception_message' => $e->getMessage(),
      //   // 'msg' => 'Something went wrong!! Bank Details not updated.',
      //   // 'icon' => 'fa fa-warning',
      //   // 'type' => 'red',
      //   // 'status' => 2,
      //   // 'title' => 'Warning!'
      // );
      $statuscode = 400;
    } finally {
      // dd($response);
      return response()->json($response, $statuscode);
    }
  }
  /*
    Update Mobile number of the beneficiary
  */
  public function updateApprovedBenMobileNumber(Request $request)
  {

    date_default_timezone_set('Asia/Kolkata');
    $statuscode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statuscode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statuscode);
    }
    try {
      DB::connection('pgsql_appwrite')->beginTransaction();
      DB::connection('pgsql_payment')->beginTransaction();
      $getModelFunc = new getModelFunc();
      $schemaname = $getModelFunc->getSchemaDetails();
      $beneficiary_id = $request->benId;
      $application_id = $request->appId;
      $new_mobile_no = $request->newMobileNo;
      $remarks = $request->remarks;
      $tableName = Helper::getTable($beneficiary_id);
      $benPersonalDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benTable'])->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id', 0)->first();
      $contactDetails = DB::connection('pgsql_appread')->table('lb_scheme.' . $tableName['benContactTable'])->where('beneficiary_id', $beneficiary_id)->first();

      $old_mobile_no = $benPersonalDetails->mobile_no;
      $new_value = [];
      $old_value = [];

      $old_value['mobile_no'] = trim($old_mobile_no);
      $new_value['mobile_no'] = trim($new_mobile_no);

      $insertUpdateBenDetails = [
        'beneficiary_id' => $beneficiary_id,
        'user_id' => Auth::user()->id,
        'old_data' => json_encode($old_value),
        'new_data' => json_encode($new_value),
        'next_level_role_id' => 0,
        'update_code' => 10,
        'dist_code' => $benPersonalDetails->created_by_dist_code,
        'local_body_code' => $benPersonalDetails->created_by_local_body_code,
        'rural_urban_id' => $contactDetails->rural_urban_id,
        'block_ulb_code' => $contactDetails->block_ulb_code,
        'gp_ward_code' => $contactDetails->gp_ward_code,
        'created_at' => date('Y-m-d H:i:s'),
        'remarks' => $remarks
      ];
      $ben_personal_Details_mainServer_update = [
        'mobile_no' => trim($new_mobile_no),
        'action_by' => Auth::user()->id,
        'action_ip_address' => request()->ip(),
        'action_type' => class_basename(request()->route()->getAction()['controller'])

      ];
      $ben_payment_paymentServer_update = [
        'mobile_no' => trim($new_mobile_no),
        'updated_at' => date('Y-m-d H:i:s')
      ];

      /*------------- Database Operations -----------------*/
      $benPersonlUp = DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benTable'])
        ->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id', 0)
        ->update($ben_personal_Details_mainServer_update);
      $updateBenIn = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')
        ->where('beneficiary_id', $beneficiary_id)
        ->insert($insertUpdateBenDetails);
      $benPaymentUp = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
        ->where('ben_id', $beneficiary_id)->update($ben_payment_paymentServer_update);
      /*------------- End Database Operations -----------------*/

      if ($benPersonlUp && $updateBenIn && $benPaymentUp) {
        DB::connection('pgsql_appwrite')->commit();
        DB::connection('pgsql_payment')->commit();
        $response = array(
          'status' => 1,
          'msg' => 'Mobile Number Updated Succesfully.',
          'type' => 'green',
          'icon' => 'fa fa-check',
          'title' => 'Success'
        );
      } else {
        DB::connection('pgsql_appwrite')->rollback();
        DB::connection('pgsql_payment')->rollback();
        $response = array(
          'status' => 2,
          'msg' => 'Mobile number not updated!!!.',
          'type' => 'red',
          'icon' => 'fa fa-warning',
          'title' => 'Required'
        );
      }
    } catch (\Exception $e) {
      DB::connection('pgsql_appwrite')->rollback();
      DB::connection('pgsql_payment')->rollback();
      $response = array(
        'exception' => true,
        'exception_message' => $e->getMessage(),
      );
      $statuscode = 400;
    } finally {
      return response()->json($response, $statuscode);
    }
  }
  private function validateInput($request)
  {
    $this->validate($request, [
      'bank_name' => 'required|string|max:200',
      'branch_name' => 'required|string|max:200',
      'bank_account_number' => 'required|numeric|between:00000000000000000000,9999999999999999999',
      'bank_ifsc' => 'required|max:20',
    ]);
  }
}
