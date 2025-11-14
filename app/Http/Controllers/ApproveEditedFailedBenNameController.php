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
use App\Models\SubDistrict;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\GP;
use Validator;
use App\Models\LotMaster;
use App\Models\LotDetails;
use App\Models\AvLotmaster;
use App\Models\AvLotdetails;
use App\Models\FailedBankDetails;
use App\Models\BankDetails;
use App\Helpers\Helper;
use App\Models\DataSourceCommon;

class ApproveEditedFailedBenNameController extends Controller
{
	public function __construct()
  {
    set_time_limit(300);
    $this->middleware('auth');
    date_default_timezone_set('Asia/Kolkata');
    $this->source_type = 'ss_nfsa';
    $this->scheme_id = 20;
    // $this->middleware('MaintainMiddleware');
  }
  public function index()
  {
    $user_id = Auth::user()->id;
    $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
    $distCode = $dutyObj->district_code;
    if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
      $levels = [
        2 => 'Rural',
        1 => 'Urban'
      ];
      return view('ben-name-validation-failed/approved_failed_name_edited', ['levels' => $levels, 'dist_code' => $distCode]);
    } else {
      return redirect('/')->with('success', 'Unauthorized');
    }
  }
  public function getEditedNameFailedDetailsData(Request $request)
  {
    $user_id = Auth::user()->id;
    $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
    $distCode = $dutyObj->district_code;
    $rural_urban = $request->filter_1;
    $local_body_code = $request->filter_2;
    $block_ulb_code = $request->block_ulb_code;
    $gp_ward_code = $request->gp_ward_code;
    if ($request->ajax()) {
      if ((Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') && $request->update_code != '') {
        if ($rural_urban == 1) {
          $query = '';
          $query = "(select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no, bp.application_id, u.id,u.failed_tbl_id,u.beneficiary_id,u.old_data,u.new_data, u.update_code, u.next_level_role_id,u.pmt_mode,u.failed_type,b.urban_body_name as block_ulb_name, g.urban_body_ward_name as gp_ward_name from lb_scheme.update_ben_details u 
            JOIN lb_scheme.ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id 
            LEFT JOIN public.m_sub_district d ON d.sub_district_code=u.local_body_code 
            LEFT JOIN public.m_urban_body b ON b.urban_body_code=u.block_ulb_code
            LEFT JOIN public.m_urban_body_ward g ON g.urban_body_ward_code=u.gp_ward_code
            where u.dist_code=" . $distCode . " and u.next_level_role_id=5 AND bp.next_level_role_id = 0 AND u.failed_type in(3,4) AND u.legacy_validation_update = FALSE ";
          if (!empty($rural_urban)) {
            $query .= " and u.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= " and u.local_body_code=" . $local_body_code . " ";
          }
          if (!empty($block_ulb_code)) {
            $query .= " and u.block_ulb_code=" . $block_ulb_code . " ";
          }
          if (!empty($gp_ward_code)) {
            $query .= " and u.gp_ward_code=" . $gp_ward_code . " ";
          }
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND u.update_code=" . $request->update_code . "";
          }
          $query .= " )";

          $query .= "union all (select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no,bp.application_id, u.id,u.failed_tbl_id,u.beneficiary_id,u.old_data,u.new_data, u.update_code, u.next_level_role_id,u.pmt_mode,u.failed_type,b.urban_body_name as block_ulb_name, g.urban_body_ward_name as gp_ward_name from lb_scheme.update_ben_details u 
            JOIN lb_scheme.faulty_ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id 
            LEFT JOIN public.m_sub_district d ON d.sub_district_code=u.local_body_code 
            LEFT JOIN public.m_urban_body b ON b.urban_body_code=u.block_ulb_code
            LEFT JOIN public.m_urban_body_ward g ON g.urban_body_ward_code=u.gp_ward_code
            where u.dist_code=" . $distCode . " and u.next_level_role_id=5 AND bp.next_level_role_id = 0 AND u.failed_type in(3,4) AND u.legacy_validation_update = FALSE";
          if (!empty($rural_urban)) {
            $query .= "and u.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= "and u.local_body_code=" . $local_body_code . " ";
          }
          if (!empty($block_ulb_code)) {
            $query .= "and u.block_ulb_code=" . $block_ulb_code . " ";
          }
          if (!empty($gp_ward_code)) {
            $query .= "and u.gp_ward_code=" . $gp_ward_code . " ";
          }
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND u.update_code=" . $request->update_code . "";
          }
          $query .= " )";
        } elseif ($rural_urban == 2) {
          $query = '';
          $query = "(select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no,bp.application_id, u.id,u.failed_tbl_id,u.beneficiary_id,u.old_data,u.new_data, u.update_code, u.next_level_role_id, u.pmt_mode,u.failed_type, b.block_name as block_ulb_name,g.gram_panchyat_name as gp_ward_name 
          from lb_scheme.update_ben_details u 
          JOIN lb_scheme.ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id  
          LEFT JOIN public.m_block b ON b.block_code=u.local_body_code
          LEFT JOIN public.m_gp g ON g.gram_panchyat_code=u.gp_ward_code
          where u.dist_code=" . $distCode . " and u.next_level_role_id=5 AND bp.next_level_role_id = 0 AND u.failed_type in(3,4) AND u.legacy_validation_update = FALSE ";
          if (!empty($rural_urban)) {
            $query .= " and u.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= " and u.local_body_code=" . $local_body_code . " ";
          }
          if (!empty($block_ulb_code)) {
            $query .= " and u.block_ulb_code=" . $block_ulb_code . " ";
          }
          if (!empty($gp_ward_code)) {
            $query .= " and u.gp_ward_code=" . $gp_ward_code . " ";
          }
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND u.update_code=" . $request->update_code . "";
          }
          $query .= " )";
          $query .= " union all (select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no,bp.application_id, u.id,u.failed_tbl_id,u.beneficiary_id,u.old_data,u.new_data, u.update_code, u.next_level_role_id, u.pmt_mode,u.failed_type, b.block_name as block_ulb_name,g.gram_panchyat_name as gp_ward_name 
          from lb_scheme.update_ben_details u 
          JOIN lb_scheme.faulty_ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id  
          LEFT JOIN public.m_block b ON b.block_code=u.local_body_code
          LEFT JOIN public.m_gp g ON g.gram_panchyat_code=u.gp_ward_code
          where u.dist_code=" . $distCode . " and u.next_level_role_id=5 AND bp.next_level_role_id = 0 AND u.failed_type in(3,4) AND u.legacy_validation_update = FALSE ";
          if (!empty($rural_urban)) {
            $query .= " and u.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= " and u.local_body_code=" . $local_body_code . " ";
          }
          if (!empty($block_ulb_code)) {
            $query .= " and u.block_ulb_code=" . $block_ulb_code . " ";
          }
          if (!empty($gp_ward_code)) {
            $query .= " and u.gp_ward_code=" . $gp_ward_code . " ";
          }
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND u.update_code=" . $request->update_code . "";
          }
          $query .= " )";
        } else {
          $query = '';
          $query = "(select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no,bp.application_id, u.id,u.failed_tbl_id,u.beneficiary_id,
          u.old_data,u.new_data, u.update_code, u.next_level_role_id,u.pmt_mode,u.failed_type from lb_scheme.update_ben_details u
           JOIN lb_scheme.ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id 
           where u.dist_code=" . $distCode . " and u.next_level_role_id=5 AND bp.next_level_role_id = 0 AND u.failed_type in(3,4) AND u.legacy_validation_update = FALSE ";
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND u.update_code=" . $request->update_code . "";
          }
          $query .= " )";
          $query .= " union all (select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no,bp.application_id, u.id,u.failed_tbl_id,u.beneficiary_id,
          u.old_data,u.new_data, u.update_code, u.next_level_role_id,u.pmt_mode,u.failed_type from lb_scheme.update_ben_details u
           JOIN lb_scheme.faulty_ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id 
           where u.dist_code=" . $distCode . " and u.next_level_role_id=5 AND bp.next_level_role_id = 0 AND u.failed_type in(3,4) AND u.legacy_validation_update = FALSE";
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND u.update_code=" . $request->update_code . "";
          }
          $query .= " )";
        }
        // if ($distCode == 304) {
        //   dd($query);
        // }
        $data = DB::connection('pgsql_appread')->select($query);
      } else {
        $data = collect([]);
      }
      // print_r($data);
      return datatables()->of($data)
        ->addIndexColumn()
        ->addColumn('view', function ($data) {
          $action = '<button class="btn btn-primary btn-xs ben_view_button" value="' . $data->id . '_' . $data->beneficiary_id . '"><i class="glyphicon glyphicon-edit"></i>View</button>';
          return $action;
        })
        ->addColumn('check', function ($data) use($request) {
           $disabled='';
          // echo $request->update_code;die;
          if($request->update_code==13){
            $disabled='disabled';
          } 
          return '<input type="checkbox"  name="chkbx" class="all_checkbox" '. $disabled.' onclick="controlCheckBox();" value="' . $data->id . '_' . $data->beneficiary_id . '">';
        })
        ->addColumn('beneficiary_id', function ($data) {
          return $data->beneficiary_id;
        })
        ->addColumn('application_id', function ($data) {
          return $data->application_id;
        })
        ->addColumn('name', function ($data) {
          return $data->name;
        })
        ->addColumn('ss_card_no', function ($data) {
          return $data->ss_card_no;
        })
        // ->addColumn('old_acc_no', function ($data) {
        //   return json_decode($data->old_data)->bank_code;
        // })
        // ->addColumn('old_ifsc', function ($data) {
        //   return json_decode($data->old_data)->bank_ifsc;
        // })
        // ->addColumn('new_acc_no', function ($data) {
        //   return json_decode($data->new_data)->bank_code;
        // })
        // ->addColumn('new_ifsc', function ($data) {
        //   return json_decode($data->new_data)->bank_ifsc;
        // })
        ->addColumn('type', function ($data) {
          $msg = Config::get('globalconstants.failed_type.' . $data->failed_type);
          return $msg;
        })
        ->addColumn('edited_type', function ($data) {
          if($data->update_code == 11) {
            $msg = 'Minor mismatch, Keep <br>existing bank information';
          }
          else if($data->update_code == 12) {
            $msg = 'Process with new <br>bank account';
          }
          else if($data->update_code == 13) {
            $msg = 'Application is rejected <br>due to major mismatch';
          }
          return $msg;
        })        
        ->rawColumns(['beneficiary_id', 'application_id', 'name', 'ss_card_no', 'type', 'view', 'check', 'edited_type'])
        ->make(true);
    }
  }
  public function getEditFailedNameData(Request $request) {
    $response = [];
    $statuscode = 200;
    if (!$request->ajax()) {
      $statuscode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statuscode);
    }
    try {
      $benid = $request->benid;
      $arr = explode('_', $benid);
      $update_table_id = $arr[0];
      $beneficiary_id = $arr[1];
      $query = '';
      $tableName = Helper::getTable($beneficiary_id);
      $query = "select u.*,bp.* from lb_scheme.update_ben_details u 
        JOIN lb_scheme." . $tableName['benTable'] . " bp ON bp.beneficiary_id=u.beneficiary_id  
        where u.id=" . $update_table_id . " and u.beneficiary_id=" . $beneficiary_id . " and u.next_level_role_id=5 AND bp.next_level_role_id = 0";
      $bankData = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('id', $update_table_id)->where('beneficiary_id', $beneficiary_id)->first();
      $data = DB::connection('pgsql_appread')->select($query);
      $paneltext = 'Edited Banking  Information';
      // $decodeOldData = json_decode($bankData->old_data);
      // $decodeNewData = json_decode($bankData->new_data);

      $response = array(
        'personaldata' => $data,
        // 'old_bank_code' => $decodeOldData->bank_code,
        // 'old_branch_name' => $decodeOldData->branch_name,
        // 'old_bank_name' => $decodeOldData->bank_name,
        // 'old_bank_ifsc' => $decodeOldData->bank_ifsc,

        // 'new_bank_code' => $decodeNewData->bank_code,
        // 'new_branch_name' => $decodeNewData->branch_name,
        // 'new_bank_name' => $decodeNewData->bank_name,
        // 'new_bank_ifsc' => $decodeNewData->bank_ifsc,
        'paneltext' => $paneltext
      );
      //  echo "<pre>"; print_r( $response);die;
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
  public function updateFailedNameFromApprover(Request $request) {
    $statuscode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statuscode = 400;
      $response = array('return_status' => 0, 'return_msg' => 'Error occured in form submit.');
      return response()->json($response);
    }
    try {
      DB::connection('pgsql_appwrite')->beginTransaction();
      DB::connection('pgsql_payment')->beginTransaction();
      $user_id = Auth::user()->id;
      $designation_id = Auth::user()->designation_id;
      $errormsg = Config::get('constants.errormsg');
      $duty = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
      if ($duty->isEmpty) {
        return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
      }

      if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
        $is_bulk = $request->is_bulk;
        if ($is_bulk == 1) {
          $fg_is_bulk = 1;
        } else {
          $fg_is_bulk = 0;
        }
      } else {
        return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
      }
      $reject_cause = $request->reject_cause;
      $comments = trim($request->accept_reject_comments);
      $operation_type = $request->opreation_type;
      $bulk_id = $request->applicantId;
      $single_id = $request->single_app_id;
      $getModelFunc = new getModelFunc();
      $schemaname = $getModelFunc->getSchemaDetails();

      // Single Application Id Approved
      if ($fg_is_bulk == 0) { //echo 0;die;
        $single_id_arr = explode('_', $single_id);
        $update_table_id = $single_id_arr[0];
        $beneficiary_id = $single_id_arr[1];
        $application_id = $request->application_id;
        $tableName = Helper::getTable($beneficiary_id);
        if ($operation_type == 'A') {
          $return_msg = 'Beneficiary Id - ' . $beneficiary_id . ' approved successfully';
          $updateTableObj = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->first();
          // print_r($updateTableObj);die();
          // echo $updateTableObj->update_code;die();
          // Name Validation edit with existing data
          if ($updateTableObj->update_code === 11) {
            // $ben_payment_paymentServer_update = [
            //   // 'acc_validated' => 0,
            //   'acc_validated' => 2,
            //   'updated_at' => date('Y-m-d H:i:s')
            // ];
            if ($updateTableObj->legacy_validation_update==TRUE) {
              // $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 2));
              $ben_payment_paymentServer_update = [
                'name_validated_modified' => 11,
                'updated_at' => date('Y-m-d H:i:s')
              ];
            } else {
              $ben_payment_paymentServer_update = [
                // 'acc_validated' => 0,
                'acc_validated' => 2,
                'updated_at' => date('Y-m-d H:i:s')
              ];
            }
          }
          // Name Validation edit with new bank account
          else if ($updateTableObj->update_code === 12) {
            $newDecodeData = json_decode($updateTableObj->new_data);
            $decodeOldData = json_decode($updateTableObj->old_data);
            $ben_bank_update = [
              'bank_code' => $newDecodeData->bank_code,
              'bank_name' => trim($newDecodeData->bank_name),
              'branch_name' => trim($newDecodeData->branch_name),
              'bank_ifsc' => trim($newDecodeData->bank_ifsc)
            ];
            $ben_payment_paymentServer_update = [
              'last_accno' => $newDecodeData->bank_code,
              'last_ifsc' => $newDecodeData->bank_ifsc,
              'acc_validated' => 0,
              'updated_at' => date('Y-m-d H:i:s')
            ];
            $benPersonalDetailsUpdate = [ 'status' => 1 ];
            if ($updateTableObj->legacy_validation_update==TRUE) {
              $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 12));
            } 
          }
          // Reject Beneficiary
          else if ($updateTableObj->update_code === 13) {
            $ben_payment_paymentServer_update = [
              'ben_status' => -400,
              // 'acc_validated' => 2,
              'updated_at' => date('Y-m-d H:i:s')
            ];
            // $benPersonalDetailsUpdate = [ 'next_level_role_id' => -400 ];
            if ($updateTableObj->legacy_validation_update==TRUE) {
              $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 13));
            }
            
            $otp_table_insert = [
              'application_id' => $updateTableObj->application_id,
              'verification_otp' => $request->otp_login,
              'user_id' => $user_id,
              'created_at' => date('Y-m-d H:i:s')
            ];
          }
          else {
            return $response = array('return_status' => 0, 'return_msg' => 'Update code is undefined');
          }
          $update_ben_update = [
            'next_level_role_id' => 0,
            // 'update_code' => $updateTableObj->update_code,
            'approved_remarks' => $comments,
            'updated_at' => date('Y-m-d H:i:s')
          ];

          /* --------- Database Operations --------- */
          if (isset($benPersonalDetailsUpdate)) {
            DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benTable'])
            ->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id', 0)
            ->update($benPersonalDetailsUpdate);
          }
          if (isset($ben_bank_update)) {
            DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benBankTable'])
            ->where('beneficiary_id', $beneficiary_id)
            ->update($ben_bank_update);
          }
          DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
          ->where('ben_id', $beneficiary_id)
          ->update($ben_payment_paymentServer_update);
          DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
          ->where('ben_id', $beneficiary_id)->where('id', $updateTableObj->failed_tbl_id)->where('edited_status', 1)
          ->update(['edited_status' => 2,'updated_at'=>date('Y-m-d H:i:s')]);
          DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')
          ->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->where('next_level_role_id', 5)->where('update_code', $updateTableObj->update_code)
          ->update($update_ben_update);
          if (isset($otp_table_insert)) {
            DB::connection('pgsql_appwrite')->table('public.name_validation_reject_otp')->insert($otp_table_insert);
          }
          $accept_reject_model = new DataSourceCommon;
          $Table = $getModelFunc->getTable($duty->district_code, $this->source_type, 9);
          $accept_reject_model->setTable('' . $Table);
          $accept_reject_model->op_type = 'NameValApproved';
          $accept_reject_model->ben_id = $beneficiary_id;
          $accept_reject_model->application_id = $updateTableObj->application_id;
          $accept_reject_model->designation_id = $designation_id;
          $accept_reject_model->scheme_id = 20;
          $accept_reject_model->user_id = $user_id;
          $accept_reject_model->comment_message = $comments;
          $accept_reject_model->mapping_level = $duty->mapping_level;
          $accept_reject_model->created_by = $user_id;
          $accept_reject_model->created_by_level = $duty->mapping_level;
          $accept_reject_model->created_by_dist_code = $duty->district_code;
          $accept_reject_model->ip_address = request()->ip();
          $is_saved3 = $accept_reject_model->save();
          /* --------- End Database Operations --------- */

          $getFaultyObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $beneficiary_id)->first();
          if($updateTableObj->update_code == 13) {
            // New 16-12-2021
            $in_pension_id = 'ARRAY[' . "'$application_id'" . ']';
            if ($getFaultyObj->faulty_status) {
              //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(" . $in_pension_id . ", 3,'" . $comments . "')");
              $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."', rejected_cause => 3, comment_message =>'" . $comments . "')");

            } else {
              //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(" . $in_pension_id . ", 3,'" . $comments . "')");
              $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."', rejected_cause => 3, comment_message =>'" . $comments . "')");

            }
            if ($reject_fun) {
              $accept_reject_model = new DataSourceCommon;
              $Table = $getModelFunc->getTable($duty->district_code, $this->source_type, 9);
              $accept_reject_model->setTable('' . $Table);
              $accept_reject_model->op_type = 'VR';
              $accept_reject_model->ben_id = $beneficiary_id;
              $accept_reject_model->application_id = $updateTableObj->application_id;
              $accept_reject_model->designation_id = $designation_id;
              $accept_reject_model->scheme_id = 20;
              $accept_reject_model->user_id = $user_id;
              $accept_reject_model->comment_message = $comments;
              $accept_reject_model->mapping_level = $duty->mapping_level;
              $accept_reject_model->created_by = $user_id;
              $accept_reject_model->created_by_level = $duty->mapping_level;
              $accept_reject_model->created_by_dist_code = $getFaultyObj->dist_code;
              $accept_reject_model->rejected_reverted_cause = '3';
              $accept_reject_model->ip_address = request()->ip();
              $is_saved3 = $accept_reject_model->save();
            }
          }

          $response = array('return_status' => 1, 'return_msg' => $return_msg);
        }
        else if ($operation_type == 'T') {
          $return_msg = 'Beneficiary Id - ' . $beneficiary_id . ' reverted successfully';
          $updateTableObj = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->first();
          $update_ben_update = [
            'next_level_role_id' => 6,
            'approved_remarks' => $comments,
            'updated_at' => date('Y-m-d H:i:s')
          ];
          /* --------- Database Operations --------- */
          DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
          ->where('ben_id', $beneficiary_id)->where('id', $updateTableObj->failed_tbl_id)->where('edited_status', 1)
          ->update(['edited_status' => 0,'updated_at'=>date('Y-m-d H:i:s')]);
          DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')
          ->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->where('next_level_role_id', 5)->where('update_code', $updateTableObj->update_code)
          ->update($update_ben_update);

          $updateBenArc = "INSERT INTO lb_scheme.update_ben_details_arc(
            id, failed_tbl_id, beneficiary_id, old_data, new_data, user_id, deleted_at, created_at, updated_at, remarks, update_code, next_level_role_id, dist_code, local_body_code, rural_urban_id, block_ulb_code, gp_ward_code, pmt_mode, failed_type, application_id, ticket_id, ip_address, name_resposne_from_bank, ben_name, legacy_validation_update, approved_remarks)
          SELECT id, failed_tbl_id, beneficiary_id, old_data, new_data, user_id, deleted_at, created_at, updated_at, remarks, update_code, next_level_role_id, dist_code, local_body_code, rural_urban_id, block_ulb_code, gp_ward_code, pmt_mode, failed_type, application_id, ticket_id, ip_address, name_resposne_from_bank, ben_name, legacy_validation_update, approved_remarks FROM lb_scheme.update_ben_details WHERE beneficiary_id=" . $beneficiary_id . " AND id=".$update_table_id;
          $insertArc = DB::connection('pgsql_appwrite')->select($updateBenArc);
          if ($insertArc) {
            $updateBenDel = "DELETE FROM lb_scheme.update_ben_details WHERE beneficiary_id=" . $beneficiary_id . " AND id= " . $update_table_id;
            DB::connection('pgsql_appwrite')->select($updateBenDel);
          }
          /* --------- End Database Operations --------- */
          $response = array('return_status' => 1, 'return_msg' => $return_msg);
        }
        else {
          $response = array('return_status' => 0, 'return_msg' => 'Operation type is undefined');
        }
      }
      // Bulk Application Id Approved
      else if ($fg_is_bulk == 1) { //echo 1; die;
        $bulk_id_arr = explode(',', $bulk_id);
        $count = 0;
        foreach ($bulk_id_arr as $key => $value) {
          $count++;
          $bulk_single_id_arr = explode('_', $value);
          $update_table_id = $bulk_single_id_arr[0];
          $beneficiary_id = $bulk_single_id_arr[1];
          $tableName = Helper::getTable($beneficiary_id);
          if ($operation_type == 'A') {
            $updateTableObj = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->first();
            // Name Validation edit with existing data
            if ($updateTableObj->update_code == 11) {
              // $ben_payment_paymentServer_update = [
              //   // 'acc_validated' => 0,
              //   'acc_validated' => 2,
              //   'updated_at' => date('Y-m-d H:i:s')
              // ];
              if ($updateTableObj->legacy_validation_update==TRUE) {
                // $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 2));
                $ben_payment_paymentServer_update = [
                  'name_validated_modified' => 11,
                  'updated_at' => date('Y-m-d H:i:s')
                ];
              } else {
                $ben_payment_paymentServer_update = [
                  // 'acc_validated' => 0,
                  'acc_validated' => 2,
                  'updated_at' => date('Y-m-d H:i:s')
                ];
              }
            }
            // Name Validation edit with new bank account
            else if ($updateTableObj->update_code == 12) {
              $newDecodeData = json_decode($updateTableObj->new_data);
              $decodeOldData = json_decode($updateTableObj->old_data);
              $ben_bank_update = [
                'bank_code' => $newDecodeData->bank_code,
                'bank_name' => trim($newDecodeData->bank_name),
                'branch_name' => trim($newDecodeData->branch_name),
                'bank_ifsc' => trim($newDecodeData->bank_ifsc)
              ];
              $ben_payment_paymentServer_update = [
                'last_accno' => $newDecodeData->bank_code,
                'last_ifsc' => $newDecodeData->bank_ifsc,
                'acc_validated' => 0,
                'updated_at' => date('Y-m-d H:i:s')
              ];
              $benPersonalDetailsUpdate = [ 'status' => 1 ];
              if ($updateTableObj->legacy_validation_update==TRUE) {
                $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 12));
              }
            }
            // Reject Beneficiary
            else if ($updateTableObj->update_code == 13) {
              $ben_payment_paymentServer_update = [
                'ben_status' => -400,
                // 'acc_validated' => 2,
                'updated_at' => date('Y-m-d H:i:s')
              ];
              // $benPersonalDetailsUpdate = [ 'next_level_role_id' => -400 ];
              if ($updateTableObj->legacy_validation_update==TRUE) {
                $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 13));
              }
              $otp_table_insert = [
                'application_id' => $updateTableObj->application_id,
                'verification_otp' => $request->otp_login,
                'user_id' => $user_id,
                'created_at' => date('Y-m-d H:i:s')
              ];
            }
            else {
              return $response = array('return_status' => 0, 'return_msg' => 'Update code is undefined');
            }
            $update_ben_update = [
              'next_level_role_id' => 0,
              // 'update_code' => $updateTableObj->update_code,
              'approved_remarks' => $comments,
              'updated_at' => date('Y-m-d H:i:s')
            ];

            /* --------- Database Operations --------- */
            if (isset($benPersonalDetailsUpdate)) {
              DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benTable'])
              ->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id', 0)
              ->update($benPersonalDetailsUpdate);
            }
            if (isset($ben_bank_update)) {
              DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benBankTable'])
              ->where('beneficiary_id', $beneficiary_id)
              ->update($ben_bank_update);
            }
            DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')
            ->where('ben_id', $beneficiary_id)
            ->update($ben_payment_paymentServer_update);
            DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
            ->where('ben_id', $beneficiary_id)->where('id', $updateTableObj->failed_tbl_id)->where('edited_status', 1)
            ->update(['edited_status' => 2,'updated_at'=>date('Y-m-d H:i:s')]);
            DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')
            ->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->where('next_level_role_id', 5)->where('update_code', $updateTableObj->update_code)
            ->update($update_ben_update);
            if (isset($otp_table_insert)) {
              DB::connection('pgsql_appwrite')->table('public.name_validation_reject_otp')->insert($otp_table_insert);
            }
            /* --------- End Database Operations --------- */

            if($updateTableObj->update_code == 13) {
              $getFaultyObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $beneficiary_id)->first();
              // New 16-12-2021
              $in_pension_id = 'ARRAY[' . "'$application_id'" . ']';
              if ($getFaultyObj->faulty_status) {
                $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(" . $in_pension_id . ", 3,'" . $comments . "')");
              } else {
                $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(" . $in_pension_id . ", 3,'" . $comments . "')");
              }
              if ($reject_fun) {
                $accept_reject_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($duty->district_code, $this->source_type, 9);
                $accept_reject_model->setTable('' . $Table);
                $accept_reject_model->op_type = 'VR';
                $accept_reject_model->ben_id = $beneficiary_id;
                $accept_reject_model->application_id = $updateTableObj->application_id;
                $accept_reject_model->designation_id = $designation_id;
                $accept_reject_model->scheme_id = 20;
                $accept_reject_model->user_id = $user_id;
                $accept_reject_model->comment_message = $comments;
                $accept_reject_model->mapping_level = $duty->mapping_level;
                $accept_reject_model->created_by = $user_id;
                $accept_reject_model->created_by_level = $duty->mapping_level;
                $accept_reject_model->created_by_dist_code = $getFaultyObj->dist_code;
                $accept_reject_model->rejected_reverted_cause = '3';
                $accept_reject_model->ip_address = request()->ip();
                $is_saved3 = $accept_reject_model->save();
              }
            }
          }
          else if ($operation_type == 'T') {
            $return_msg = 'Beneficiary Id - ' . $beneficiary_id . ' reverted successfully';
            $updateTableObj = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->first();
            $update_ben_update = [
              'next_level_role_id' => 6,
              'approved_remarks' => $comments,
              'updated_at' => date('Y-m-d H:i:s')
            ];
            /* --------- Database Operations --------- */
            DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')
            ->where('ben_id', $beneficiary_id)->where('id', $updateTableObj->failed_tbl_id)->where('edited_status', 1)
            ->update(['edited_status' => 0,'updated_at'=>date('Y-m-d H:i:s')]);
            DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')
            ->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->where('next_level_role_id', 5)->where('update_code', $updateTableObj->update_code)
            ->update($update_ben_update);

            $updateBenArc = "INSERT INTO lb_scheme.update_ben_details_arc(
              id, failed_tbl_id, beneficiary_id, old_data, new_data, user_id, deleted_at, created_at, updated_at, remarks, update_code, next_level_role_id, dist_code, local_body_code, rural_urban_id, block_ulb_code, gp_ward_code, pmt_mode, failed_type, application_id, ticket_id, ip_address, name_resposne_from_bank, ben_name, legacy_validation_update, approved_remarks)
            SELECT id, failed_tbl_id, beneficiary_id, old_data, new_data, user_id, deleted_at, created_at, updated_at, remarks, update_code, next_level_role_id, dist_code, local_body_code, rural_urban_id, block_ulb_code, gp_ward_code, pmt_mode, failed_type, application_id, ticket_id, ip_address, name_resposne_from_bank, ben_name, legacy_validation_update, approved_remarks FROM lb_scheme.update_ben_details WHERE beneficiary_id=" . $beneficiary_id . " AND id=".$update_table_id;
            $insertArc = DB::connection('pgsql_appwrite')->select($updateBenArc);
            if ($insertArc) {
              $updateBenDel = "DELETE FROM lb_scheme.update_ben_details WHERE beneficiary_id=" . $beneficiary_id . " AND id= " . $update_table_id;
              DB::connection('pgsql_appwrite')->select($updateBenDel);
            }
            /* --------- End Database Operations --------- */
            $response = array('return_status' => 1, 'return_msg' => $return_msg);
          }
          else {
            $response = array('return_status' => 0, 'return_msg' => 'Operation type is undefined');
          }
        }
        $return_msg = $count . ' Beneficiaries Approved successfully';
        $response = array('return_status' => 1, 'return_msg' => $return_msg);
      }
      else {
        $response = array('return_status' => 0, 'return_msg' => 'Name details not updated, something went wrong.');
      }
      DB::connection('pgsql_appwrite')->commit();
      DB::connection('pgsql_payment')->commit();
    } catch (\Exception $e) {
      dd($e);
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

  public function nameMismatchRejectOtpVerify(Request $request) {
    $response = [];
    $statuscode = 200;
    if (!$request->ajax()) {
      $statuscode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statuscode);
    }
    try {
      // dd($request->all());
      $otp = md5($request->login_otp);
      $user_id = Auth::user()->id;
      // if ($user_id == 41852) {
      //   $login_otp = User::where('id', $user_id)->where('is_active', 1)->value('login_otp');
      // } else {
      //   $login_otp = User::where('id', $user_id)->where('is_active', 1)->value('last_otp');
      // }
      
      $login_otp = User::where('id', $user_id)->where('is_active', 1)->value('last_otp');
      // dd($login_otp, $otp);
      // $login_otp_last = User::where('id', $user_id)->where('is_active', 1)->value('login_otp');
      if ($otp == $login_otp /*|| $otp == md5($login_otp_last)*/) {
        $response = array(
          'status' => 1, 'msg' => 'OTP verified successfully', 'login_otp' => $login_otp
        );
      } else {
        $response = array(
          'status' => 2, 'msg' => 'OTP is not verified.',
          'type' => 'blue', 'icon' => 'fa fa-info', 'title' => 'Information!! '
        );
      }
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

  public function selectMatchingScore()
  {
      $user_id = Auth::user()->id;
      $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
      $dutyLevel = $dutyObj->mapping_level . Auth::user()->designation_id;
      $distCode = $dutyObj->district_code;
      if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
          return view('ben-name-validation-failed/select_verified_mis_match_score');
      } else {
          return redirect('/')->with('success', 'Unauthorized');
      }
  }
  public function editIndex(Request $request)
  {
    $user_id = Auth::user()->id;
    $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
    $distCode = $dutyObj->district_code;
    $matchType = $request->type;
    if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
      $levels = [
        2 => 'Rural',
        1 => 'Urban'
      ];
      return view('ben-name-validation-failed/approved_failed_name_edited_90_to100', ['levels' => $levels, 'dist_code' => $distCode, 'matchType' => $matchType]);
    } else {
      return redirect('/')->with('success', 'Unauthorized');
    }
  }

  public function getVerifiedNameValidationFailed90to100(Request $request)
  {
    $user_id = Auth::user()->id;
    $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
    $distCode = $dutyObj->district_code;
    $rural_urban = $request->filter_1;
    $local_body_code = $request->filter_2;
    $block_ulb_code = $request->block_ulb_code;
    $gp_ward_code = $request->gp_ward_code;
    $matchType = $request->matchType;
    if ($matchType == 1) {
      $whereCon = "ba.matching_score >= 90 AND ba.matching_score <= 100";
    } else {
      $whereCon = "ba.matching_score >= 40 AND ba.matching_score <= 89";
    }
    if ($request->ajax()) {
      if ((Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') && $request->update_code != '') {
        if ($rural_urban == 1) {
          $query = '';
          $query = "SELECT bp.beneficiary_id, bp.application_id, ba.failed_type, ba.process_type, ba.name, ba.response_name, ba.next_level_name_failed_id, ba.failed_tbl_id FROM lb_scheme.ben_name_failed_log ba JOIN lb_scheme.ben_personal_details bp ON ba.ben_id = bp.beneficiary_id
          -- JOIN lb_scheme.ben_name_failed_log a ON a.ben_id = ba.ben_id
          JOIN lb_scheme.ben_contact_details bc ON bc.beneficiary_id = ba.ben_id
          WHERE ".$whereCon." AND bp.created_by_dist_code = ".$distCode." AND bp.next_level_role_id = 0 AND ba.failed_type IN(3,4) ";
          if (!empty($rural_urban)) {
            $query .= " and bc.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= " and bp.created_by_local_body_code=" . $local_body_code . " ";
          }
          // if (!empty($block_ulb_code)) {
          //   $query .= " and u.block_ulb_code=" . $block_ulb_code . " ";
          // }
          // if (!empty($gp_ward_code)) {
          //   $query .= " and u.gp_ward_code=" . $gp_ward_code . " ";
          // }
          if (!empty($request->failed_type)) {
            $query .= " AND ba.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND ba.next_level_name_failed_id=" . $request->update_code . "";
          }


          $query .= "UNION ALL
          SELECT bp.beneficiary_id, bp.application_id, ba.failed_type, ba.process_type, ba.name, ba.response_name, ba.next_level_name_failed_id, ba.failed_tbl_id FROM lb_scheme.ben_name_failed_log ba JOIN lb_scheme.faulty_ben_personal_details bp ON ba.ben_id = bp.beneficiary_id
          JOIN lb_scheme.faulty_ben_contact_details bc ON bc.beneficiary_id = ba.ben_id
          WHERE ".$whereCon." AND bp.created_by_dist_code = ".$distCode." AND bp.next_level_role_id = 0 AND ba.failed_type IN(3,4) ";
          if (!empty($rural_urban)) {
            $query .= "and bc.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= "and bp.created_by_local_body_code=" . $local_body_code . " ";
          }
          // if (!empty($block_ulb_code)) {
          //   $query .= "and u.block_ulb_code=" . $block_ulb_code . " ";
          // }
          // if (!empty($gp_ward_code)) {
          //   $query .= "and u.gp_ward_code=" . $gp_ward_code . " ";
          // }
          if (!empty($request->failed_type)) {
            $query .= " AND ba.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND ba.next_level_name_failed_id=" . $request->update_code . "";
          }

        } elseif ($rural_urban == 2) {
          $query = '';
          $query = "SELECT bp.beneficiary_id, bp.application_id, ba.failed_type, ba.process_type, ba.name, ba.response_name, ba.next_level_name_failed_id, ba.failed_tbl_id FROM lb_scheme.ben_name_failed_log ba JOIN lb_scheme.ben_personal_details bp ON ba.ben_id = bp.beneficiary_id
          -- JOIN lb_scheme.ben_name_failed_log a ON a.ben_id = ba.ben_id
          JOIN lb_scheme.ben_contact_details bc ON bc.beneficiary_id = ba.ben_id
          WHERE ".$whereCon." AND bp.created_by_dist_code = ".$distCode." AND bp.next_level_role_id = 0 AND ba.failed_type IN(3,4) ";
          if (!empty($rural_urban)) {
            $query .= "and bc.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= "and bp.created_by_local_body_code=" . $local_body_code . " ";
          }
          // if (!empty($block_ulb_code)) {
          //   $query .= " and u.block_ulb_code=" . $block_ulb_code . " ";
          // }
          // if (!empty($gp_ward_code)) {
          //   $query .= " and u.gp_ward_code=" . $gp_ward_code . " ";
          // }
          if (!empty($request->failed_type)) {
            $query .= " AND ba.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND ba.next_level_name_failed_id=" . $request->update_code . "";
          }

          $query .= " UNION ALL
          SELECT bp.beneficiary_id, bp.application_id, ba.failed_type, ba.process_type, ba.name, ba.response_name, ba.next_level_name_failed_id, ba.failed_tbl_id FROM lb_scheme.ben_name_failed_log ba JOIN lb_scheme.faulty_ben_personal_details bp ON ba.ben_id = bp.beneficiary_id
          JOIN lb_scheme.faulty_ben_contact_details bc ON bc.beneficiary_id = ba.ben_id
          WHERE ".$whereCon." AND bp.created_by_dist_code = ".$distCode." AND bp.next_level_role_id = 0 AND ba.failed_type IN(3,4) ";
          if (!empty($rural_urban)) {
            $query .= "and bc.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= "and bp.created_by_local_body_code=" . $local_body_code . " ";
          }
          // if (!empty($block_ulb_code)) {
          //   $query .= " and u.block_ulb_code=" . $block_ulb_code . " ";
          // }
          // if (!empty($gp_ward_code)) {
          //   $query .= " and u.gp_ward_code=" . $gp_ward_code . " ";
          // }
          if (!empty($request->failed_type)) {
            $query .= " AND ba.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND ba.next_level_name_failed_id=" . $request->update_code . "";
          }

        } else {
          $query = '';
          $query = "SELECT bp.beneficiary_id, bp.application_id, ba.failed_type, ba.process_type, ba.name, ba.response_name, ba.next_level_name_failed_id, ba.failed_tbl_id FROM lb_scheme.ben_name_failed_log ba JOIN lb_scheme.ben_personal_details bp ON ba.ben_id = bp.beneficiary_id
          -- JOIN lb_scheme.ben_name_failed_log a ON a.ben_id = ba.ben_id
          JOIN lb_scheme.ben_contact_details bc ON bc.beneficiary_id = ba.ben_id
          WHERE ".$whereCon." AND bp.created_by_dist_code = ".$distCode." AND bp.next_level_role_id = 0 AND ba.failed_type IN(3,4) ";
          if (!empty($request->failed_type)) {
            $query .= " AND ba.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND ba.next_level_name_failed_id=" . $request->update_code . "";
          }

          $query .= "  UNION ALL
          SELECT bp.beneficiary_id, bp.application_id, ba.failed_type, ba.process_type, ba.name, ba.response_name, ba.next_level_name_failed_id, ba.failed_tbl_id FROM lb_scheme.ben_name_failed_log ba JOIN lb_scheme.faulty_ben_personal_details bp ON ba.ben_id = bp.beneficiary_id
          JOIN lb_scheme.faulty_ben_contact_details bc ON bc.beneficiary_id = ba.ben_id
          WHERE ".$whereCon." AND bp.created_by_dist_code = ".$distCode." AND bp.next_level_role_id = 0 AND ba.failed_type IN(3,4)";
          if (!empty($request->failed_type)) {
            $query .= " AND ba.failed_type=" . $request->failed_type . "";
          }
          if (!empty($request->update_code)) {
            $query .= " AND ba.next_level_name_failed_id=" . $request->update_code . "";
          }

        }
        // dd($query);
        $data = DB::connection('pgsql_appread')->select($query);
      } else {
        $data = collect([]);
      }
      // print_r($data);
      return datatables()->of($data)
        ->addIndexColumn()
        ->addColumn('view', function ($data) {
          $action = '<button class="btn btn-primary btn-xs ben_view_button" value="' . $data->next_level_name_failed_id . '_' . $data->beneficiary_id . '_'. $data->failed_tbl_id. '"><i class="glyphicon glyphicon-edit"></i>View</button>';
          return $action;
        })
        ->addColumn('check', function ($data) use($request, $matchType) {
           $disabled='';
          // echo $request->update_code;die;
          if($request->update_code==13){
            $disabled='disabled';
          } 
          $check = '';
          if ($matchType == 1) {
            $check = '<input type="checkbox"  name="chkbx" class="all_checkbox" '. $disabled.' onclick="controlCheckBox();" value="' . $data->next_level_name_failed_id . '_' . $data->beneficiary_id . '_'. $data->failed_tbl_id. '">';
          } else {
            $check = '';
          }
          return $check;
        })
        ->addColumn('beneficiary_id', function ($data) {
          return $data->beneficiary_id;
        })
        ->addColumn('application_id', function ($data) {
          return $data->application_id;
        })
        ->addColumn('name', function ($data) {
          return $data->name;
        })
        // ->addColumn('ss_card_no', function ($data) {
        //   return $data->ss_card_no;
        // })
        ->addColumn('type', function ($data) {
          $msg = Config::get('globalconstants.failed_type.' . $data->failed_type);
          return $msg;
        })
        ->addColumn('edited_type', function ($data) {
          if($data->next_level_name_failed_id == 6) {
            $msg = 'Minor mismatch, Keep <br>existing bank information';
          }
          // else if($data->update_code == 12) {
          //   $msg = 'Process with new <br>bank account';
          // }
          else if($data->next_level_name_failed_id == 7) {
            $msg = 'Application is rejected <br>due to major mismatch';
          }
          return $msg;
        })        
        ->rawColumns(['beneficiary_id', 'application_id', 'name', 'type', 'view', 'check', 'edited_type'])
        ->make(true);
    }
  }

  public function getEditFailedNameData90to100(Request $request)
  {
    $response = [];
    $statuscode = 200;
    if (!$request->ajax()) {
      $statuscode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statuscode);
    }
    try {
      $beneficiary_id = $request->benid;
      // dd($beneficiary_id);
      $arr = explode('_', $beneficiary_id);
      $process_type = $arr[0];
      $ben_id = $arr[1];
      $failed_tbl_id = $arr[2];
      // dump($process_type); dump($ben_id); dd($failed_tbl_id);
      $tableName = Helper::getTable($ben_id);
      $query = "SELECT bp.*, ba.*, l.* FROM lb_scheme.ben_accept_reject_info ba JOIN lb_scheme.".$tableName['benTable']." bp ON bp.beneficiary_id = ba.ben_id JOIN lb_scheme.ben_name_failed_log l ON l.ben_id = bp.beneficiary_id WHERE l.next_level_name_failed_id IN(6,7) AND l.failed_type IN(3,4) AND bp.beneficiary_id =".$ben_id."";
      $data = DB::connection('pgsql_appread')->select($query);
      // dd($data);
      $response = array(
        'personaldata' => $data
      );
    } catch (\Exception $e) {
      dd($e);
      $response = array(
        'exception' => true,
        'exception_message' => $e->getMessage(),
      );
      $statuscode = 400;
    } finally{
      return response()->json($response, $statuscode);
    }
  }

  public function updateFailedNameApprove90to100(Request $request)
  {
    $statuscode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statuscode = 400;
      $response = array('return_status' => 0, 'return_msg' => 'Error occured in form submit.');
      return response()->json($response);
    }
    try {
      $user_id = Auth::user()->id;
      $designation_id = Auth::user()->designation_id;
      $errormsg = Config::get('constants.errormsg');
      $duty = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
      if ($duty->isEmpty) {
        return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
      }

      if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
        $is_bulk = $request->is_bulk;
        if ($is_bulk == 1) {
          $fg_is_bulk = 1;
        } else {
          $fg_is_bulk = 0;
        }
      } else {
        return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
      }
      $reject_cause = $request->reject_cause;
      $comments = trim($request->accept_reject_comments);
      $operation_type = $request->opreation_type;
      $bulk_id = $request->applicantId;
      $single_id = $request->single_app_id;
      $update_code =  $request->update_code;
      $processType = $request->processType;
      // dump($comments); dump($operation_type); dump($bulk_id); dump($fg_is_bulk); 
      // dd($update_code);
      $getModelFunc = new getModelFunc();
      $schemaname = $getModelFunc->getSchemaDetails();
      // dd($schemaname);
      if ($fg_is_bulk == 0) {
        // dd('Not Bulk');
        $single_id_arr = explode('_', $single_id);
        $process_type = $single_id_arr[0];
        $ben_id = $single_id_arr[1];
        $failed_tbl_id = $single_id_arr[2];
        $application_id = $request->application_id;
        $tableName = Helper::getTable($ben_id);

        if ($operation_type == 'A') {
          $return_msg = 'Beneficiary Id - ' . $ben_id . ' approved successfully';
          // $benAcceptRejectInfo = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_accept_reject_info')->where('failed_tbl_id', $failed_tbl_id)->where('next_level_name_failed_id', $process_type)->where('ben_id', $ben_id)->first();
          $benPersonal_details = DB::connection('pgsql_appwrite')->table('lb_scheme.'. $tableName['benTable'])->where('beneficiary_id', $ben_id)->first();
          $nameFailedLog = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_name_failed_log')->where('ben_id', $ben_id)->first();
          $getFaultyObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $ben_id)->first();
          // dump($benAcceptRejectInfo); dd($nameFailedLog);
          $benPersonalDetails = array();
          $benPaymentDetails = array();
          $failedPaymentDetails = array();
          $benAcceptRejectInfo = array();
          DB::connection('pgsql_appwrite')->beginTransaction();
          DB::connection('pgsql_payment')->beginTransaction();
          if ($update_code == 6) { //$request->update_code
            $benPersonalDetails['ben_fname'] = $nameFailedLog->response_name;
            $benPaymentDetails['ben_name'] = $nameFailedLog->response_name;
            $failedPaymentDetails['edited_status'] = 2;
            $failedPaymentDetails['updated_at'] = date('Y-m-d H:i:s');
            $benPaymentDetails['acc_validated'] = 2;
            $benAcceptRejectInfo['op_type'] = 'MinorMismatchApprove';
            $benAcceptRejectInfo['comment_message'] = $comments;
          }
          elseif ($update_code == 7) {
            $in_pension_id = 'ARRAY[' . "'$application_id'" . ']';
            if ($getFaultyObj->faulty_status) {
              //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(" . $in_pension_id . ", 3,'" . $comments . "')");
              $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."', rejected_cause => 3, comment_message =>'" . $comments . "')");

            } else {
              //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(" . $in_pension_id . ", 3,'" . $comments . "')");
              $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."', rejected_cause => 3, comment_message =>'" . $comments . "')");
            }
            $benPaymentDetails['ben_status'] = -400;
            $failedPaymentDetails['edited_status'] = -2;
            $failedPaymentDetails['updated_at'] = date('Y-m-d H:i:s');
            if ($reject_fun) {
              $benAcceptRejectInfo['op_type'] = 'NameValRejectApprove';
              $benAcceptRejectInfo['comment_message'] = $comments;
            }
            $otp_table_insert = [
              'application_id' => $benPersonal_details->application_id,
              'verification_otp' => $request->otp_login,
              'user_id' => $user_id,
              'created_at' => date('Y-m-d H:i:s')
            ];
          }else {
            return $response = array('return_status' => 0, 'return_msg' => 'Update code is undefined');
          }
          $updateNameFailedLog = [
            'next_level_name_failed_id' => 0,
            'updated_at' => date('Y-m-d H:i:s')
          ];
          $benAcceptRejectInfo['ben_id'] = $ben_id;
          $benAcceptRejectInfo['scheme_id'] = 20;
          $benAcceptRejectInfo['created_by'] = $user_id;
          $benAcceptRejectInfo['created_by_level'] = $duty->mapping_level;
          $benAcceptRejectInfo['created_by_dist_code'] = $duty->district_code;
          $benAcceptRejectInfo['created_by_local_body_code'] = $duty->taluka_code;
          $benAcceptRejectInfo['created_at'] = date('Y-m-d H:i:s');
          $benAcceptRejectInfo['ip_address'] = request()->ip();
          $benAcceptRejectInfo['designation_id'] = $designation_id;
          $benAcceptRejectInfo['user_id'] = $user_id;
          $benAcceptRejectInfo['mapping_level'] = $duty->mapping_level;
          $benAcceptRejectInfo['application_id'] = $application_id;
          // dump($benPersonalDetails); dump($benPaymentDetails); dump($failedPaymentDetails); dump($benAcceptRejectInfo); dd($otp_table_insert);
          /*----------------- Database Operation -----------------*/
          if (isset($benPersonalDetails) && $nameFailedLog->next_level_name_failed_id == 6) {
            $updateBenPersonalDetails = DB::connection('pgsql_appwrite')->table('lb_scheme.'. $tableName['benTable'])->where('beneficiary_id', $ben_id)->where('next_level_role_id', 0)->update($benPersonalDetails);
          }
          // if (isset($benPaymentDetails)) {
            $updateBenPaymentDetails = DB::connection('pgsql_payment')->table($schemaname. '.ben_payment_details')->where('ben_id', $ben_id)->where('ben_status', 1)->update($benPaymentDetails);
          // }
          // if (isset($failedPaymentDetails)) {
            $updateFailedPaymentDetails = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $ben_id)->where('id', $failed_tbl_id)->where('edited_status', 1)->where('is_minor_mismatch', 1)->update($failedPaymentDetails);
          // }
          // if (isset($otp_table_insert)) {
          //   DB::connection('pgsql_appwrite')->table('public.name_validation_reject_otp')->insert($otp_table_insert);
          // }
          $insertBenAcceptRejectInfo = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_accept_reject_info')->insert($benAcceptRejectInfo);
          $updatebenNameFailedLog = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_name_failed_log')->where('ben_id', $ben_id)->where('failed_tbl_id', $failed_tbl_id)->update($updateNameFailedLog);
          // dump($updateBenPaymentDetails); dump($updateFailedPaymentDetails); dump($insertBenAcceptRejectInfo); dump($updatebenNameFailedLog); dd($updateBenPersonalDetails);
          /*----------------- End Database Operation -----------------*/
          if ($updateBenPaymentDetails && $updateFailedPaymentDetails && $insertBenAcceptRejectInfo && $updatebenNameFailedLog) {
            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_payment')->commit();
            $response = array('return_status' => 1, 'return_msg' => $return_msg);
          } else {
            $response = array('return_status' => 0, 'return_msg' => 'Something went wrong..');
          }
          
        } elseif ($operation_type == 'OR') {
          // dd($processType);
          $return_msg = 'Beneficiary Id - ' . $ben_id . ' over ruled successfully';
            $benAcceptRejectInfo = array();
            $failedPaymentDetails = array();

            
            $failedPaymentDetails['updated_at'] = date('Y-m-d H:i:s');
            $benAcceptRejectInfo['ben_id'] = $ben_id;
            $benAcceptRejectInfo['scheme_id'] = 20;
            $benAcceptRejectInfo['created_by'] = $user_id;
            $benAcceptRejectInfo['created_by_level'] = $duty->mapping_level;
            $benAcceptRejectInfo['created_by_dist_code'] = $duty->district_code;
            $benAcceptRejectInfo['created_by_local_body_code'] = $duty->taluka_code;
            $benAcceptRejectInfo['created_at'] = date('Y-m-d H:i:s');
            $benAcceptRejectInfo['ip_address'] = request()->ip();
            $benAcceptRejectInfo['designation_id'] = $designation_id;
            $benAcceptRejectInfo['user_id'] = $user_id;
            $benAcceptRejectInfo['mapping_level'] = $duty->mapping_level;
            $benAcceptRejectInfo['application_id'] = $application_id;
            $benPersonal_details = DB::connection('pgsql_appwrite')->table('lb_scheme.'. $tableName['benTable'])->where('beneficiary_id', $ben_id)->first();
            $nameFailedLog = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_name_failed_log')->where('ben_id', $ben_id)->first();
            $getFaultyObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $ben_id)->first();
            DB::connection('pgsql_appwrite')->beginTransaction();
            DB::connection('pgsql_payment')->beginTransaction();
          if ($processType == 11) {
            $benPaymentDetails = array();
            $benPersonalDetails = array();
            
            $updateNameFailedLog = [
              'next_level_name_failed_id' => 0,
              'process_type' => $processType,
              'updated_at' => date('Y-m-d H:i:s')
            ];
            $failedPaymentDetails['edited_status'] = 2;
            $benPaymentDetails['ben_name'] = $nameFailedLog->response_name;
            $benPaymentDetails['acc_validated'] = 2;
            $benPersonalDetails['ben_fname'] = $nameFailedLog->response_name;
            $benAcceptRejectInfo['op_type'] = 'MinorMismatchOverule';
            $benAcceptRejectInfo['comment_message'] = $comments;
          }
          if ($processType == 13) {
            $in_pension_id = 'ARRAY[' . "'$application_id'" . ']';
            if ($getFaultyObj->faulty_status) {
              //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(" . $in_pension_id . ", 3,'" . $comments . "')");
              $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."', rejected_cause => 3, comment_message =>'" . $comments . "')");

            } else {
              //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(" . $in_pension_id . ", 3,'" . $comments . "')");
              $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."', rejected_cause => 3, comment_message =>'" . $comments . "')");
            }
            $updateNameFailedLog = [
              'next_level_name_failed_id' => -1,
              'process_type' => $processType,
              'updated_at' => date('Y-m-d H:i:s')
            ];
            $benPaymentDetails['ben_status'] = -400;
            $failedPaymentDetails['edited_status'] = -2;
            if ($reject_fun) {
              $benAcceptRejectInfo['op_type'] = 'RejectOverule';
              $benAcceptRejectInfo['comment_message'] = $comments;
            }
          }
          /************* DB Operation Start **************/
          if (isset($benPersonalDetails)) {
            $updateBenPersonal = DB::connection('pgsql_appwrite')->table('lb_scheme.'. $tableName['benTable'])->where('beneficiary_id', $ben_id)->where('next_level_role_id', 0)->update($benPersonalDetails);
          }
          $updateBenPayment = DB::connection('pgsql_payment')->table($schemaname.'.ben_payment_details')->where('ben_id', $ben_id)->where('ben_status', 1)->update($benPaymentDetails);
          $updateFailedPaymentDetails = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $ben_id)->where('id', $failed_tbl_id)->where('edited_status', 1)->where('is_minor_mismatch', 1)->update($failedPaymentDetails);
          $updateNameFailed = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_name_failed_log')->where('ben_id', $ben_id)->update($updateNameFailedLog);
          $insertbenAcceptReject = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_accept_reject_info')->insert($benAcceptRejectInfo);
          /************* DB Operation End **************/
          if ($updateBenPayment && $updateFailedPaymentDetails && $updateNameFailed && $insertbenAcceptReject) {
            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_payment')->commit();
            $response = array('return_status' => 1, 'return_msg' => $return_msg);
          } else {
            $response = array('return_status' => 0, 'return_msg' => 'Something went wrong....');
          }
        } else {
          $response = array('return_status' => 0, 'return_msg' => 'Operation type is undefined');
        }
      } elseif ($fg_is_bulk == 1) {
        $bulk_id_arr = explode(',', $bulk_id);
        $count = 0;
        $i = 0;
        DB::connection('pgsql_appwrite')->beginTransaction();
        DB::connection('pgsql_payment')->beginTransaction();
        foreach ($bulk_id_arr as $key => $value) {
          $count++;
          $bulk_single_id_arr = explode('_', $value);
          $process_type = $bulk_single_id_arr[0];
          $ben_id = $bulk_single_id_arr[1];
          $failed_tbl_id = $bulk_single_id_arr[2];
          $tableName = Helper::getTable($ben_id);
          if ($operation_type == 'A') {
            $benPersonal_details = DB::connection('pgsql_appwrite')->table('lb_scheme.'. $tableName['benTable'])->where('beneficiary_id', $ben_id)->first();
            $nameFailedLog = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_name_failed_log')->where('ben_id', $ben_id)->whereNull('updated_at')->first();
            // dd($nameFailedLog);
            $benPersonalDetails = array();
            $benPaymentDetails = array();
            $failedPaymentDetails = array();
            $benAcceptRejectInfo = array();
            
            // if ($duty->district_code == 317) {
            //   dd($nameFailedLog->next_level_name_failed_id);
            // }
            if ($nameFailedLog->next_level_name_failed_id == 6) {
              $benPersonalDetails['ben_fname'] = $nameFailedLog->response_name;
              $benPaymentDetails['ben_name'] = $nameFailedLog->response_name;
              $failedPaymentDetails['edited_status'] = 2;
              $failedPaymentDetails['updated_at'] = date('Y-m-d H:i:s');
              $benPaymentDetails['acc_validated'] = 2;
              $benAcceptRejectInfo['op_type'] = 'MinorMismatchApprove';
              $benAcceptRejectInfo['rejected_reverted_cause'] = $comments;
            }
            elseif ($nameFailedLog->next_level_name_failed_id == 7) {
              $getFaultyObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $ben_id)->first();
              $in_pension_id = 'ARRAY[' . "'$application_id'" . ']';
              if ($getFaultyObj->faulty_status) {
                //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(" . $in_pension_id . ", 3,'" . $comments . "')");
                $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."', rejected_cause => 3, comment_message =>'" . $comments . "')");

              } else {
                //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(" . $in_pension_id . ", 3,'" . $comments . "')");
                $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."', rejected_cause => 3, comment_message =>'" . $comments . "')");
              }
              $benPaymentDetails['ben_status'] = -400;
              $failedPaymentDetails['edited_status'] = -2;
              $failedPaymentDetails['updated_at'] = date('Y-m-d H:i:s');
               if ($reject_fun) {
                $benAcceptRejectInfo['op_type'] = 'NameValRejectApprove';
                $benAcceptRejectInfo['comment_message'] = $comments;
               }
              $otp_table_insert = [
                'application_id' => $benPersonal_details->application_id,
                'verification_otp' => $request->otp_login,
                'user_id' => $user_id,
                'created_at' => date('Y-m-d H:i:s')
              ];
            }else {
              return $response = array('return_status' => 0, 'return_msg' => 'Update code is undefined');
            }
            $updateNameFailedLog = [
              'next_level_name_failed_id' => 0,
              'updated_at' => date('Y-m-d H:i:s')
            ];
            $benAcceptRejectInfo['ben_id'] = $ben_id;
            $benAcceptRejectInfo['scheme_id'] = 20;
            $benAcceptRejectInfo['created_by'] = $user_id;
            $benAcceptRejectInfo['created_by_level'] = $duty->mapping_level;
            $benAcceptRejectInfo['created_by_dist_code'] = $duty->district_code;
            $benAcceptRejectInfo['created_by_local_body_code'] = $duty->taluka_code;
            $benAcceptRejectInfo['created_at'] = date('Y-m-d H:i:s');
            $benAcceptRejectInfo['ip_address'] = request()->ip();
            $benAcceptRejectInfo['designation_id'] = $designation_id;
            $benAcceptRejectInfo['user_id'] = $user_id;
            $benAcceptRejectInfo['mapping_level'] = $duty->mapping_level;
            $benAcceptRejectInfo['application_id'] = $benPersonal_details->application_id;
            // dump($benPersonalDetails); dump($benPaymentDetails); dump($failedPaymentDetails); dump($benAcceptRejectInfo); dd($otp_table_insert);
            /*----------------- Database Operation -----------------*/
            if (isset($benPersonalDetails) && $nameFailedLog->next_level_name_failed_id == 6) {
              $updateBenPersonalDetails = DB::connection('pgsql_appwrite')->table('lb_scheme.'. $tableName['benTable'])->where('beneficiary_id', $ben_id)->where('next_level_role_id', 0)->update($benPersonalDetails);
            }
            // if (isset($benPaymentDetails)) {
              $updateBenPaymentDetails = DB::connection('pgsql_payment')->table($schemaname. '.ben_payment_details')->where('ben_id', $ben_id)->where('ben_status', 1)->update($benPaymentDetails);
            // }
            // if (isset($failedPaymentDetails)) {
              $updateFailedPaymentDetails = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $ben_id)->where('id', $failed_tbl_id)->where('edited_status', 1)->where('is_minor_mismatch', 1)->where('failed_type', 3)->update($failedPaymentDetails);
            // }
            if (isset($otp_table_insert)) {
              DB::connection('pgsql_appwrite')->table('public.name_validation_reject_otp')->insert($otp_table_insert);
            }
            $insertBenAcceptRejectInfo = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_accept_reject_info')->insert($benAcceptRejectInfo);
            $updatebenNameFailedLog = DB::connection('pgsql_appwrite')->table('lb_scheme.ben_name_failed_log')->where('ben_id', $ben_id)->where('failed_tbl_id', $failed_tbl_id)->update($updateNameFailedLog);
            /*----------------- End Database Operation -----------------*/
            // if ($duty->district_code == 317) {
            //   dump($ben_id); dump($updateBenPaymentDetails); dump($updateFailedPaymentDetails); dump($insertBenAcceptRejectInfo); dump($updatebenNameFailedLog); echo '</br>'; die;
            // }
            // dump($ben_id); dump($updateBenPaymentDetails); dump($updateFailedPaymentDetails); dump($insertBenAcceptRejectInfo); dump($updatebenNameFailedLog); echo '</br>'; die;
            if ($updateBenPaymentDetails && $updateFailedPaymentDetails && $insertBenAcceptRejectInfo && $updatebenNameFailedLog) {
              $i++;
              $return_msg = $count.' Beneficaries approved successfull';
              $response = array('return_status' => 1, 'return_msg' => $return_msg);
            }
            
          } else {
            $response = array('return_status' => 0, 'return_msg' => 'Operation type is undefined');
          }
        }
          if ($i == $count) {
            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_payment')->commit();
          } 
          else {
            DB::connection('pgsql_appwrite')->rollback();
            DB::connection('pgsql_payment')->rollback();
            $response = array(
              'return_status' => 0,
              'return_msg' => 'Something went wrong...',
            );
          }        
      } else {
        $response = array('return_status' => 0, 'return_msg' => 'Name details not updated, something went wrong...');
      }
      
    } catch (\Exception $e) {
      dd($e);
      DB::connection('pgsql_appwrite')->rollback();
      DB::connection('pgsql_payment')->rollback();
      $response = array(
        'exception' => true,
        'exception_message' => $e->getMessage(),
      );
      $statuscode = 400;
    } finally{
      return response()->json($response, $statuscode);
    }
  }

  public function indexMisReport(Request $request)
  {
    // $ds_phase_list = DsPhase::get();
    // $base_date  = '2020-01-01';
    // $c_time = Carbon::now();
    // $c_date = $c_time->format("Y-m-d");
    $is_active = 0;
    $roleArray = $request->session()->get('role');
    $designation_id = Auth::user()->designation_id;
    $district_visible = $is_urban_visible = $block_visible = 1;
    $municipality_visible = 0;
    $gp_ward_visible = 0;
    $muncList = collect([]);
    $gpList = collect([]);
    if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' || $designation_id == 'MisState' ||  $designation_id == 'Dashboard') {
        $district_visible = $is_urban_visible = $block_visible = 1;
    } else if ($designation_id == 'Delegated Approver' || $designation_id == 'Approver') {
        $district_code = NULL;
        $is_urban = NULL;
        $blockCode = NULL;
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $this->scheme_id) {
                $is_urban = $roleObj['is_urban'];
                $district_code = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $blockCode = $roleObj['urban_body_code'];
                    $muncList = UrbanBody::select('urban_body_code', 'urban_body_name')->where('sub_district_code', $blockCode)->get();
                    $municipality_visible = 1;
                } else {
                    $blockCode = $roleObj['taluka_code'];
                    $gpList = GP::select('gram_panchyat_code', 'gram_panchyat_name')->where('block_code', $blockCode)->get();
                }
                break;
            }
        }

        if (empty($district_code))
            return redirect("/")->with('success', 'User Disabled. ');
    } else {
        return redirect("/")->with('success', 'User Disabled. ');
    }
    //dd($district_code);
    if (!empty($district_code)) {
        $district_visible = 0;
        $district_code_fk = $district_code;
    } else {
        $district_code_fk = NULL;
    }
    if (!empty($is_urban)) {
        $is_urban_visible = 0;
        $rural_urban_fk = $is_urban;
    } else {
        $rural_urban_fk = NULL;
    }
    if (!empty($blockCode)) {
        $block_visible = 0;
        $block_munc_corp_code_fk = $blockCode;
        $gp_ward_visible = 1;
    } else {
        $block_munc_corp_code_fk = NULL;
        $gp_ward_visible = 0;
    }
    $districts = District::get();
    // dd($ds_phase_list);
    return view(
        'ben-name-validation-failed.90_to_100_mis_report',
        [
            'districts' => $districts,
            'district_visible' => $district_visible,
            'district_code_fk' => $district_code_fk,
            'is_urban_visible' => $is_urban_visible,
            'rural_urban_fk' => $rural_urban_fk,
            'block_visible' => $block_visible,
            'block_munc_corp_code_fk' => $block_munc_corp_code_fk,
            'municipality_visible' => $municipality_visible,
            'gp_ward_visible' => $gp_ward_visible,
            'is_urban_visible' => $is_urban_visible,
            // 'base_date' => $base_date,
            // 'c_date' => $c_date,
            'gpList' => $gpList,
            'muncList' => $muncList,
            // 'ds_phase_list' => $ds_phase_list
        ]
    );
  }

  public function getMis90to100(Request $request)
  {
    $district = $request->district;
    $urban_code = $request->urban_code;
    $block = $request->block;
    $muncid = $request->muncid;
    $gp_ward = $request->gp_ward;
    $minor_mismatch = $request->minor_mismatch;
    if ($minor_mismatch == 1) {
      $userMsgTitle = '90% - 100%';
    } elseif ($minor_mismatch == 2) {
      $userMsgTitle = '40% - 89%';
    } else {}
    // dump($minor_mismatch); dd($userMsgTitle);
    // $from_date = $request->from_date;
    // $to_date = $request->to_date;
    // $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');
    // dd($ds_phase);
    // $base_date  = '2025-01-24';
    // $c_time = Carbon::now();
    // $c_date = $c_time->format("Y-m-d");
    $heading_msg = '';
    $title = "";
    //$block_condition = "";
    if (!empty($district)) {
        $district_row = District::where('district_code', $district)->first();
    }

    if (!empty($block)) {

        if ($urban_code == 1) {
            $block_ulb = SubDistrict::where('sub_district_code', '=', $block)->first();
            $blk_munc_name = $block_ulb->sub_district_name;
            //$block_condition = " and rural_urban_id=1 and created_by_local_body_code=" . $block;
        } else {
            $block_ulb = Taluka::where('block_code', '=', $block)->first();
            $blk_munc_name = $block_ulb->block_name;
            // $block_condition = " and rural_urban_id=2 and  created_by_local_body_code=" . $block;
        }
    } else {
        // $block_condition = "";
    }
    if (!empty($gp_ward)) {

        if ($urban_code == 1) {
            $gp_ward_row = Ward::where('urban_body_ward_code', '=', $gp_ward)->first();
            $gp_ward_name = $gp_ward_row->urban_body_ward_name;
            //$block_condition = " and rural_urban_id=1 and created_by_local_body_code=" . $block;
        } else {
            $gp_ward_row = GP::where('gram_panchyat_code', '=', $gp_ward)->first();
            $gp_ward_name = $gp_ward_row->gram_panchyat_name;
            // $block_condition = " and rural_urban_id=2 and  created_by_local_body_code=" . $block;
        }
    }
    $rules = [
        'district' => 'nullable|integer',
        'urban_code' => 'nullable|integer',
        'block' => 'nullable|integer',
        'muncid' => 'nullable|integer',
        'gp_ward' => 'nullable|integer',
    ];
    $data = array();
    $column = "";
    $attributes = array();
    $messages = array();
    $attributes['district'] = 'District';
    $attributes['urban_code'] = 'Rural/ Urban';
    $attributes['block'] = 'Block/Sub Division';
    $attributes['muncid'] = 'Municipality';
    $attributes['gp_ward'] = 'GP/Ward';
    $validator = Validator::make($request->all(), $rules, $messages, $attributes);
    if ($validator->passes()) {
        $user_msg = "Minor Mismatch MIS Report(".$userMsgTitle.")";
        $title = $user_msg;
        //dd($title);

        $data = array();
        $return_status = 1;
        $return_msg = '';
        $heading_msg = '';
        $external = 0;
        $external_arr = array();
        $external_filter = array();
        $is_address=0;
        if (!empty($gp_ward)) {
            if ($urban_code == 1) {
                $is_address=1;
                $column = "Ward";
                $heading_msg =  $user_msg . ' of the Ward ' . $gp_ward_name;
                $data = $this->getMonorMismatch90to100WardWise($district, $block, $muncid, $gp_ward, $minor_mismatch);
            } else {
                $is_address=1;
                $column = "GP";
                $heading_msg =  $user_msg . ' of the GP ' . $gp_ward_name;
                $data = $this->getMonorMismatch90to100GpWise($district, $block, NULL, $gp_ward, $minor_mismatch);
            }
        } else if (!empty($muncid)) {
            $is_address=1;
            $column = "Ward";
            $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
            $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
            $data = $this->getMonorMismatch90to100WardWise($district, $block, $muncid, NULL, $minor_mismatch);
        } else if (!empty($block)) {
            if ($urban_code == 1) {
                $is_address=1;
                $column = "Municipality";
                $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
                $data = $this->getMonorMismatch90to100MuncWise($district, $block, NULL, NULL, $minor_mismatch);
            } else if ($urban_code == 2) {
                $is_address=1;
                $block_arr = Taluka::where('block_code', '=', $block)->first();
                $column = "GP";
                $heading_msg = 'GP Wise ' . $user_msg . ' of the Block ' . $block_arr->block_name;
                $data = $this->getMonorMismatch90to100GpWise($district, $block, NULL, $gp_ward, $minor_mismatch);
            }
        } else {

            if (!empty($district)) {
                if ($urban_code == 1) {
                    $column = "Sub Division";
                    $heading_msg = 'Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                    $data = $this->getMonorMismatch90to100SubDivWise($district, NULL, NULL, NULL, $minor_mismatch);
                } else if ($urban_code == 2) {
                    $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                    $column = "Block";
                    $data = $this->getMonorMismatch90to100BlockWise($district, NULL, NULL, NULL, $minor_mismatch);
                } else {
                    $heading_msg = 'Block/Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                    $column = "Block/Sub Division";
                    $data1 = $this->getMonorMismatch90to100BlockWise($district, NULL, NULL, NULL, $minor_mismatch);
                    $data2 = $this->getMonorMismatch90to100SubDivWise($district, NULL, NULL, NULL, $minor_mismatch);
                    $data = array_merge($data1, $data2);
                }
            } else {
                $column = "District";
                $heading_msg = 'District Wise ' . $user_msg;
                $data = $this->getMonorMismatch90to100DistrictWise($district, NULL, NULL, NULL, $minor_mismatch);
                $external = 0;
            }
        }
        if ($is_address==1) {
            $heading_msg = $heading_msg . "<span class='text-danger'> (According to Applicant’s Address)</span>";
        }
    } else {
        $return_status = 0;
        $return_msg = $validator->errors()->all();
    }

    return response()->json([
        'return_status' => $return_status,
        'return_msg' => $return_msg,
        'row_data' => $data,
        'column' => $column,
        'title' => $title,
        'heading_msg' => $heading_msg
    ]);
  }

  public function getMonorMismatch90to100BlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $minor_mismatch)
  {
    $whereCon = "fp.dist_code =".$district_code;
    $whereMain = " WHERE district_code =".$district_code;
    if ($minor_mismatch == 1) {
      $Condition = "fp.matching_score >= 90 AND fp.matching_score <= 100";
    } elseif ($minor_mismatch == 2) {
      $Condition = "fp.matching_score >= 40 AND fp.matching_score <= 89";
    } else {}

    $query = "SELECT main.location_id AS created_by_local_body_code,
      main.location_name AS block_subdiv_name,
      main.created_by_dist_code,
      COALESCE(mm.total, 0::bigint) AS total,
      COALESCE(mm.yet_to_action, 0::bigint) AS yet_to_action,
      COALESCE(mm.approval_pending, 0::bigint) AS approval_pending,
      COALESCE(mm.approved, 0::bigint) AS approved,
      COALESCE(mm.rejected, 0::bigint) AS rejected
      FROM (
      SELECT m_block.block_code AS location_id,
        m_block.block_name AS location_name,
        m_block.district_code AS created_by_dist_code
      FROM public.m_block".$whereMain.") main
      LEFT JOIN
      (SELECT fp.local_body_code,
      COUNT(1) FILTER (WHERE fp.edited_status IN(0, 1, 2) AND ".$Condition.") AS total,
      COUNT(1) FILTER (WHERE fp.edited_status = 0 AND ".$Condition.") AS yet_to_action,
      COUNT(1) FILTER (WHERE fp.edited_status = 1 AND ".$Condition.") AS approval_pending,
      COUNT(1) FILTER (WHERE fp.edited_status = 2 AND ".$Condition.") AS approved,
      COUNT(1) FILTER (WHERE fp.edited_status = -2 AND ".$Condition.") AS rejected
      FROM lb_main.failed_payment_details fp JOIN payment.ben_payment_details bp ON bp.ben_id = fp.ben_id WHERE failed_type IN(3, 4) AND is_previous_approved = 0 AND bp.ben_status = 1 AND fp.legacy_validation_failed = false AND is_minor_mismatch = 1 AND ".$whereCon."
      GROUP BY fp.local_body_code) mm ON main.location_id = mm.local_body_code";
      $result = DB::connection('pgsql_payment')->select($query);
      return $result;
  }

  public function getMonorMismatch90to100SubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $minor_mismatch)
  {
    $whereCon = "fp.dist_code =".$district_code;
    $whereMain = " WHERE district_code =".$district_code;
    if ($minor_mismatch == 1) {
      $Condition = "fp.matching_score >= 90 AND fp.matching_score <= 100";
    } elseif ($minor_mismatch == 2) {
      $Condition = "fp.matching_score >= 40 AND fp.matching_score <= 89";
    } else {}

    $query = "SELECT main.location_id AS created_by_local_body_code,
      main.location_name AS block_subdiv_name,
      main.created_by_dist_code,
      COALESCE(mm.total, 0::bigint) AS total,
      COALESCE(mm.yet_to_action, 0::bigint) AS yet_to_action,
      COALESCE(mm.approval_pending, 0::bigint) AS approval_pending,
      COALESCE(mm.approved, 0::bigint) AS approved,
      COALESCE(mm.rejected, 0::bigint) AS rejected
      FROM (
      SELECT m_sub_district.sub_district_code AS location_id,
            m_sub_district.sub_district_name AS location_name,
            m_sub_district.district_code AS created_by_dist_code
        FROM public.m_sub_district ".$whereMain.") main
      LEFT JOIN
      (SELECT fp.local_body_code,
      COUNT(1) FILTER (WHERE fp.edited_status IN(0, 1, 2) AND ".$Condition.") AS total,
      COUNT(1) FILTER (WHERE fp.edited_status = 0 AND ".$Condition.") AS yet_to_action,
      COUNT(1) FILTER (WHERE fp.edited_status = 1 AND ".$Condition.") AS approval_pending,
      COUNT(1) FILTER (WHERE fp.edited_status = 2 AND ".$Condition.") AS approved,
      COUNT(1) FILTER (WHERE fp.edited_status = -2 AND ".$Condition.") AS rejected
      FROM lb_main.failed_payment_details fp JOIN payment.ben_payment_details bp ON bp.ben_id = fp.ben_id WHERE failed_type IN(3, 4) AND is_previous_approved = 0 AND bp.ben_status = 1 AND fp.legacy_validation_failed = false AND is_minor_mismatch = 1 AND ".$whereCon."
      GROUP BY fp.local_body_code) mm ON main.location_id = mm.local_body_code";
      $result = DB::connection('pgsql_payment')->select($query);
      return $result;
  }

  public function getMonorMismatch90to100DistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL, $minor_mismatch)
  {
    $whereCon = "dist_code =".$district_code;
    $whereMain = " WHERE district_code =".$district_code;
    if ($minor_mismatch == 1) {
      $Condition = "fp.matching_score >= 90 AND fp.matching_score <= 100";
    } elseif ($minor_mismatch == 2) {
      $Condition = "fp.matching_score >= 40 AND fp.matching_score <= 89";
    } else {}

    $query = "SELECT main.location_id AS created_by_local_body_code,
      main.location_name AS block_subdiv_name,
      main.created_by_dist_code,
      COALESCE(mm.total, 0::bigint) AS total,
      COALESCE(mm.yet_to_action, 0::bigint) AS yet_to_action,
      COALESCE(mm.approval_pending, 0::bigint) AS approval_pending,
      COALESCE(mm.approved, 0::bigint) AS approved,
      COALESCE(mm.rejected, 0::bigint) AS rejected
      FROM (
      SELECT m_district.district_code AS location_id,
            m_district.district_name AS location_name,
            m_district.district_code AS created_by_dist_code
        FROM public.m_district) main
      LEFT JOIN
      (SELECT fp.dist_code,
      COUNT(1) FILTER (WHERE fp.edited_status IN(0, 1, 2) AND ".$Condition.") AS total,
      COUNT(1) FILTER (WHERE fp.edited_status = 0 AND ".$Condition.") AS yet_to_action,
      COUNT(1) FILTER (WHERE fp.edited_status = 1 AND ".$Condition.") AS approval_pending,
      COUNT(1) FILTER (WHERE fp.edited_status = 2 AND ".$Condition.") AS approved,
      COUNT(1) FILTER (WHERE fp.edited_status = -2 AND ".$Condition.") AS rejected
      FROM lb_main.failed_payment_details fp JOIN payment.ben_payment_details bp ON bp.ben_id = fp.ben_id WHERE fp.failed_type IN(3, 4) AND fp.is_previous_approved = 0 AND bp.ben_status = 1 AND fp.legacy_validation_failed = false AND is_minor_mismatch = 1 
      GROUP BY fp.dist_code) mm ON main.location_id = mm.dist_code";
      $result = DB::connection('pgsql_payment')->select($query);
      return $result;
  }
}
