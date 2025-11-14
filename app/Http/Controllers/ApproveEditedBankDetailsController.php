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
use App\Models\LotMaster;
use App\Models\LotDetails;
use App\Models\AvLotmaster;
use App\Models\AvLotdetails;
use App\Models\FailedBankDetails;
use App\Models\UrbanBody;
use App\Models\GP;
use App\Models\BankDetails;
use App\Helpers\Helper;

class ApproveEditedBankDetailsController extends Controller
{
  public function __construct()
  {
    set_time_limit(300);
    $this->middleware('auth');

    // $this->middleware('MaintainMiddleware');
  }
  public function index()
  {
    // return redirect("/")->with('error', 'Consolidation  on financial year closing  work is in progress. Please try after 1st april 2022.');

    $user_id = Auth::user()->id;
    $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
    $distCode = $dutyObj->district_code;
    if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
      $levels = [
        2 => 'Rural',
        1 => 'Urban'
      ];
      return view('failed-edit-bank-details/approved_bank_edit_details', ['levels' => $levels, 'dist_code' => $distCode]);
    } else {
      return redirect('/')->with('success', 'Unauthorized');
    }
  }
  public function getEditedBankDetailsData(Request $request)
  {
    $user_id = Auth::user()->id;
    $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
    $distCode = $dutyObj->district_code;
    $rural_urban = $request->filter_1;
    $local_body_code = $request->filter_2;
    $block_ulb_code = $request->block_ulb_code;
    $gp_ward_code = $request->gp_ward_code;
    if ($request->ajax()) {
      if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Delegated Approver') {
        if ($rural_urban == 1) {
          $query = '';
          $query = "(select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no, u.id,u.failed_tbl_id,u.beneficiary_id,u.old_data,u.new_data, u.next_level_role_id,u.pmt_mode,u.failed_type from lb_scheme.update_ben_details u 
            JOIN lb_scheme.ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id 
            where u.dist_code=" . $distCode . " and bp.next_level_role_id = 0 and u.next_level_role_id=1 AND u.update_code NOT IN(35, 36, 37)";
          if (!empty($rural_urban)) {
            $query .= "and u.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= "and u.local_body_code=" . $local_body_code . " ";
          }
          // if (!empty($block_ulb_code)) {
          //   $query .= "and u.block_ulb_code=" . $block_ulb_code . " ";
          // }
          // if (!empty($gp_ward_code)) {
          //   $query .= "and u.gp_ward_code=" . $gp_ward_code . " ";
          // }
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          // if (!empty($request->pay_mode)) {
          //   $query .= " AND u.pmt_mode=" . $request->pay_mode . "";
          // }
          $query .= " )";

          $query .= "union all (select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no, u.id,u.failed_tbl_id,u.beneficiary_id,u.old_data,u.new_data, u.next_level_role_id,u.pmt_mode,u.failed_type from lb_scheme.update_ben_details u 
            JOIN lb_scheme.faulty_ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id 
            where u.dist_code=" . $distCode . " and bp.next_level_role_id = 0 and u.next_level_role_id=1 AND u.update_code NOT IN(35, 36, 37)";
          if (!empty($rural_urban)) {
            $query .= "and u.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= "and u.local_body_code=" . $local_body_code . " ";
          }
          // if (!empty($block_ulb_code)) {
          //   $query .= "and u.block_ulb_code=" . $block_ulb_code . " ";
          // }
          // if (!empty($gp_ward_code)) {
          //   $query .= "and u.gp_ward_code=" . $gp_ward_code . " ";
          // }
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          $query .= " )";
        } elseif ($rural_urban == 2) {
          $query = '';
          $query = "(select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no, u.id,u.failed_tbl_id,u.beneficiary_id,u.old_data,u.new_data, u.next_level_role_id, u.pmt_mode,u.failed_type 
          from lb_scheme.update_ben_details u 
          JOIN lb_scheme.ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id 
          where u.dist_code=" . $distCode . " and bp.next_level_role_id = 0 and u.next_level_role_id=1 and u.failed_type in(1,2) AND u.update_code NOT IN(35, 36, 37)";
          if (!empty($rural_urban)) {
            $query .= "and u.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= "and u.local_body_code=" . $local_body_code . " ";
          }
          // if (!empty($block_ulb_code)) {
          //   $query .= "and u.block_ulb_code=" . $block_ulb_code . " ";
          // }
          // if (!empty($gp_ward_code)) {
          //   $query .= "and u.gp_ward_code=" . $gp_ward_code . " ";
          // }
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          // if (!empty($request->pay_mode)) {
          //   $query .= " AND u.pmt_mode=" . $request->pay_mode . "";
          // }
          $query .= " )";
          $query .= " union all (select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no, u.id,u.failed_tbl_id,u.beneficiary_id,u.old_data,u.new_data, u.next_level_role_id, u.pmt_mode,u.failed_type 
          from lb_scheme.update_ben_details u 
          JOIN lb_scheme.faulty_ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id  
          where u.dist_code=" . $distCode . " and bp.next_level_role_id = 0 and u.next_level_role_id=1 AND u.update_code NOT IN(35, 36, 37)";
          if (!empty($rural_urban)) {
            $query .= "and u.rural_urban_id=" . $rural_urban . " ";
          }
          if (!empty($local_body_code)) {
            $query .= "and u.local_body_code=" . $local_body_code . " ";
          }
          // if (!empty($block_ulb_code)) {
          //   $query .= "and u.block_ulb_code=" . $block_ulb_code . " ";
          // }
          // if (!empty($gp_ward_code)) {
          //   $query .= "and u.gp_ward_code=" . $gp_ward_code . " ";
          // }
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          $query .= " )";
        } else {
          $query = '';
          $query = "(select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no, u.id,u.failed_tbl_id,u.beneficiary_id,
          u.old_data,u.new_data, u.next_level_role_id,u.pmt_mode,u.failed_type from lb_scheme.update_ben_details u
           JOIN lb_scheme.ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id 
           where u.dist_code=" . $distCode . " and bp.next_level_role_id = 0 and u.next_level_role_id=1 AND (u.update_code NOT IN (35, 36, 37) OR u.update_code IS NULL)";
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          $query .= " )";
          $query .= " union all (select concat(bp.ben_fname,' ',bp.ben_mname,' ',bp.ben_lname) as name, bp.ss_card_no, u.id,u.failed_tbl_id,u.beneficiary_id,
          u.old_data,u.new_data, u.next_level_role_id,u.pmt_mode,u.failed_type from lb_scheme.update_ben_details u
           JOIN lb_scheme.faulty_ben_personal_details bp ON bp.beneficiary_id=u.beneficiary_id 
           where u.dist_code=" . $distCode . " and bp.next_level_role_id = 0 and u.next_level_role_id=1 AND (u.update_code NOT IN (35, 36, 37) OR u.update_code IS NULL)";
          if (!empty($request->failed_type)) {
            $query .= " AND u.failed_type=" . $request->failed_type . "";
          }
          $query .= " )";
        }
          // print $query; die;
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
        ->addColumn('check', function ($data) {
          return '<input type="checkbox"  name="chkbx" class="all_checkbox"  onclick="controlCheckBox();" value="' . $data->id . '_' . $data->beneficiary_id . '">';
        })
        ->addColumn('beneficiary_id', function ($data) {
          return $data->beneficiary_id;
        })
        ->addColumn('name', function ($data) {
          return $data->name;
        })
        ->addColumn('ss_card_no', function ($data) {
          return $data->ss_card_no;
        })
        ->addColumn('old_acc_no', function ($data) {
          return json_decode($data->old_data)->bank_code;
        })
        ->addColumn('old_ifsc', function ($data) {
          return json_decode($data->old_data)->bank_ifsc;
        })
        ->addColumn('new_acc_no', function ($data) {
          return json_decode($data->new_data)->bank_code;
        })
        ->addColumn('new_ifsc', function ($data) {
          return json_decode($data->new_data)->bank_ifsc;
        })
        ->addColumn('type', function ($data) {
          $msg = Config::get('globalconstants.failed_type.' . $data->failed_type);
          return $msg;
        })
        ->rawColumns(['beneficiary_id', 'name', 'ss_card_no', 'old_acc_no', 'old_ifsc', 'new_acc_no', 'new_ifsc', 'type', 'view', 'check'])
        ->make(true);
    }
  }
  public function getUpdateEditBenData(Request $request)
  {
    $statuscode = 400;
    // $response = array('error' => 'Consolidation  on financial year closing  work is in progress. Please try after 1st april 2022.');
    // return response()->json($response, $statuscode);
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
        where u.id=" . $update_table_id . " and u.beneficiary_id=" . $beneficiary_id . " and u.next_level_role_id=1 and bp.next_level_role_id = 0 and u.failed_type in(1,2)";
      $bankData = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('id', $update_table_id)->where('beneficiary_id', $beneficiary_id)->first();
      $data = DB::connection('pgsql_appread')->select($query);
      $old_fname = '';
      $old_mname = '';
      $old_lname = '';
      $new_fname = '';
      $new_mname = '';
      $new_lname = '';
      $paneltext = 'Edited Banking  Information';
      // $bankData->old_data='{"bank_name":"UNION BANK OF INDIA",
      //   "branch_name":"DINHATA",
      //   "bank_ifsc":"UBIN0567655",
      //   "bank_code":"676502020000142",
      // "ben_fname":"Pradyut",
      // "ben_mname":"Kumar",
      // "ben_lname":"Basuri"}';
      //   $bankData->new_data='{"bank_name":"UNION BANK OF INDIA IN",
      //     "branch_name":"DINHATAA",
      //     "bank_ifsc":"UBIN05676551",
      //     "bank_code":"6765020200001421",
      //     "ben_fname":"Santu",
      //     "ben_mname":"Basuri",
      //     "ben_lname":"Mondal"}';
      $decodeOldData = json_decode($bankData->old_data);
      $decodeNewData = json_decode($bankData->new_data);
      // print_r( $decodeNewData);die;
      // echo $decodeOldData->ben_mname;die;
      if (!empty($decodeOldData->ben_fname) || !empty($decodeOldData->ben_mname) || !empty($decodeOldData->ben_lname)) {
        $old_fname = $decodeOldData->ben_fname;
        $old_mname = $decodeOldData->ben_mname;
        $old_lname = $decodeOldData->ben_lname;

        $new_fname = $decodeNewData->ben_fname;
        $new_mname = $decodeNewData->ben_mname;
        $new_lname = $decodeNewData->ben_lname;
        $paneltext = 'Edited Banking & Name Information';
      }
      $response = array(
        'personaldata' => $data,
        'old_bank_code' => $decodeOldData->bank_code,
        'old_branch_name' => $decodeOldData->branch_name,
        'old_bank_name' => $decodeOldData->bank_name,
        'old_bank_ifsc' => $decodeOldData->bank_ifsc,

        'old_fname' => $old_fname,
        'old_mname' => $old_mname,
        'old_lname' => $old_lname,

        'new_fname' => $new_fname,
        'new_mname' => $new_mname,
        'new_lname' => $new_lname,

        'new_bank_code' => $decodeNewData->bank_code,
        'new_branch_name' => $decodeNewData->branch_name,
        'new_bank_name' => $decodeNewData->bank_name,
        'new_bank_ifsc' => $decodeNewData->bank_ifsc,
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



  public function approvedEditedBankData(Request $request)
  {
    // dd($request->all());
    $statuscode = 400;
    // $response = array('error' => 'Consolidation  on financial year closing  work is in progress. Please try after 1st april 2022.');
    // return response()->json($response, $statuscode);
    date_default_timezone_set('Asia/Kolkata');
    $statuscode = 200;
    $response = [];
    // if (!$request->ajax()) {
    //   $statuscode = 400;
    //   $response = array('return_status' => 0, 'return_msg' => 'Error occured in form submit.');
    //   return response()->json($response);
    // }
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
    // dd($operation_type);
    $bulk_id = $request->applicantId;
    $single_id = $request->single_app_id;
    $getModelFunc = new getModelFunc();
    $schemaname = $getModelFunc->getSchemaDetails();
    

    // print $schemaname;die();
    // Single Application Id Approved
    if ($fg_is_bulk == 0) { //echo 1;die;
      $single_id_arr = explode('_', $single_id);
      $update_table_id = $single_id_arr[0];
      $beneficiary_id = $single_id_arr[1];
      $application_id = $request->application_id;

      // if($beneficiary_id=='209292367')
      // {
      //   dd(11);
      // }
      // echo $application_id;die;
      $tableName = Helper::getTable($beneficiary_id);
      if ($operation_type == 'A') {
        $return_msg = 'Beneficiary Id - ' . $beneficiary_id . ' approved successfully';
        try {
          DB::connection('pgsql_appwrite')->beginTransaction();
          DB::connection('pgsql_payment')->beginTransaction();
          $updateTableObjTemp = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)
            ->where('next_level_role_id', '1')->whereIn('failed_type', [1, 2])->get();
          $updateTableIdTemp = array();
          foreach ($updateTableObjTemp as $updateTableObjTempVal) {
            array_push($updateTableIdTemp, $updateTableObjTempVal->id);
          }
          $updateTableObj = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->first();

          // Get Current Financial Year
          $currentyear = date('Y');
          $prevYear = date('Y') - 1;
          $nextyear = date('Y') + 1;
          $month = date('n');
          if ($month > 3) {
              $cur_fin_year = $currentyear .'-' .$nextyear;
          } else {
              $cur_fin_year = $prevYear .'-' .($prevYear+1);
          }

          $newDecodeData = json_decode($updateTableObj->new_data);
          $decodeOldData = json_decode($updateTableObj->old_data);
          $ben_bank_update = [
            'bank_code' => $newDecodeData->bank_code,
            'bank_name' => trim($newDecodeData->bank_name),
            'branch_name' => trim($newDecodeData->branch_name),
            'bank_ifsc' => trim($newDecodeData->bank_ifsc),
            'is_dup' => 0
          ];
          // print_r($ben_bank_update);die;
          $ben_payment_paymentServer_update = [
            'last_accno' => $newDecodeData->bank_code,
            'last_ifsc' => $newDecodeData->bank_ifsc,
            'acc_validated' => 0,
            'updated_at' => date('Y-m-d H:i:s')
          ];

          $update_code = 1;
          $ben_personal_update = [];
          if (!empty($newDecodeData->ben_fname) || !empty($newDecodeData->ben_mname) || !empty($newDecodeData->ben_lname)) {
            if (
              $decodeOldData->bank_name != $newDecodeData->bank_name || $decodeOldData->branch_name != $newDecodeData->branch_name ||
              $decodeOldData->bank_ifsc != $newDecodeData->bank_ifsc || $decodeOldData->bank_code != $newDecodeData->bank_code
            ) {
              $update_code = 3;
            } else {
              $update_code = 2;
            }
            $ben_name_array = array('ben_name' => $newDecodeData->ben_fname . ' ' . $newDecodeData->ben_mname . ' ' . $newDecodeData->ben_lname);
            $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, $ben_name_array);

            $personal_name_array = array('ben_fname' => $newDecodeData->ben_fname, 'ben_mname' => $newDecodeData->ben_mname, 'ben_lname' => $newDecodeData->ben_lname);
            $ben_personal_update = array_merge($ben_personal_update, $personal_name_array);
          }
          $status_arr = array('status' => $update_code, 'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']));
          $ben_personal_update = array_merge($ben_personal_update, $status_arr);
          $failed_tbl_id = array();
          /*--------- For Payment Transaction Bank Update -----------*/
          if ($updateTableObj->failed_type == 2) {
            
              $update_failed_payment = 1;
                $failedTableObj = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)
                  ->where('edited_status', '1')->first();
                // dd($failedTableObj);
                $updateFailedApprovedEditedStatus = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)
                  ->where('edited_status', '1')->where('failed_type', $updateTableObj->failed_type)->where('approve_edited_status', 0)->update(['approve_edited_status' => 1]);
                if ($updateFailedApprovedEditedStatus) {
                  $failed_type_app_id = $failedTableObj->failed_type;
                    if (in_array($failed_type_app_id, [2])) {
                      // dd('OK');
                      $final_update = DB::connection('pgsql_payment')->select("Select payment.failed_update_approved_ben(in_ben_id => ARRAY[" . $beneficiary_id . "],  in_failed_type_id => " . $failed_type_app_id . ")");
                      $fun_call = $final_update[0]->failed_update_approved_ben;
                    }
                }


            //   foreach ($failedTableObj as $failedTableObjValue) {
            //     array_push($failed_tbl_id, $failedTableObjValue->id);
            //     if ($failedTableObjValue->fin_year == $cur_fin_year) {
            //       $pmtMode = $failedTableObjValue->pmt_mode;
            //       if ($pmtMode == 1) {
            //         $lotMode = DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $failedTableObjValue->lot_no)->value('lot_type');
            //       } else if ($pmtMode == 2) {
            //         $lotMode = DB::connection('pgsql_payment')->table($schemaname . '.sbi_lot_master')->where('lot_no', $failedTableObjValue->lot_no)->value('lot_type');
            //       }
            //       $lotMonth = $failedTableObjValue->lot_month;
            //       $ben_status_columns = Helper::getMonthColumn($lotMonth);
            //       $lotType = $ben_status_columns['lot_type'];
            //       $lotStatus = $ben_status_columns['lot_status'];

            //       if (trim($lotMode) == 'R') { // Regular Lot
            //         $add_ben_transaction_details = array($lotType => 'E', $lotStatus => 'E');
            //       } else if (trim($lotMode) == 'A') { // Arrear Lot
            //         $add_ben_transaction_details = array($lotType => 'D', $lotStatus => 'E');
            //       }
            //       //echo "<pre>";print_r($add_ben_payment_details);die;
            //       // $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, $add_ben_payment_details);
            //       // echo "<pre>";print_r($add_ben_payment_details);die;
            //       if ($updateTableObj->legacy_validation_update==TRUE) {
            //         $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 10));
            //       }
            //   }
            //   else {
            //     $ben_status_columns = Helper::getMonthColumn(13);
            //     $lotType = $ben_status_columns['lot_type'];
            //     $lotStatus = $ben_status_columns['lot_status'];
            //     $add_ben_transaction_details = array($lotType => 'D', $lotStatus => 'E');
            //     // $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, $add_ben_payment_details);
            //     if ($updateTableObj->legacy_validation_update==TRUE) {
            //       $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 10));
            //     }
            //   }
            // }
            
          } else {
            $fun_call = 1;
            array_push($failed_tbl_id, $updateTableObj->failed_tbl_id);
            $update_failed_payment = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)->whereIn('id', $failed_tbl_id)->update(['edited_status' => 2, 'updated_at' => date('Y-m-d H:i:s')]);
          }
          //echo "<pre>";print_r($ben_payment_paymentServer_update);die;
          /*-------- End ----------*/

          $update_ben_update = [
            'next_level_role_id' => 0,
            'update_code' => $update_code,
            'remarks' => $comments,
            'updated_at' => date('Y-m-d H:i:s')
          ];
          //echo "<pre>";print_r($updateTableIdTemp);die;
          // dd($ben_payment_paymentServer_update);
          $update_ben_personal = DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benTable'])->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id', 0)->update($ben_personal_update);
          $update_ben_details = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->whereIn('id', $updateTableIdTemp)->update($update_ben_update);
          $updateBankTable = DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benBankTable'])->where('beneficiary_id', $beneficiary_id)->update($ben_bank_update);

          $ben_payment_details = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $beneficiary_id)->update($ben_payment_paymentServer_update);
          
          $response = array('return_status' => 1, 'return_msg' => $return_msg);
          // if ($beneficiary_id == 214233934) {
          //   dump($update_ben_personal); dump($update_ben_details); dump($updateBankTable); dump($ben_payment_details); dump($update_failed_payment); dd($fun_call);
          // }
          
          if($update_ben_personal && $update_ben_details && $updateBankTable && $ben_payment_details && $update_failed_payment && $fun_call)
          {
            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_payment')->commit();
          }else{
            DB::connection('pgsql_appwrite')->rollback();
            DB::connection('pgsql_payment')->rollback();
            $response = array('return_status' => 0, 'return_msg' => 'Something went wrong!!');
            $statuscode = 400;
          }
        } catch (\Exception $e) {
         echo  $e->getMessage(); die;
        // echo  $e->getMessage();
          DB::connection('pgsql_appwrite')->rollback();
          DB::connection('pgsql_payment')->rollback();
          $response = array('return_status' => 0, 'return_msg' => 'Bank details not updated!!');
          $statuscode = 400;
        } finally {
          return response()->json($response, $statuscode);
        }
      } elseif ($operation_type == 'T') {
        $return_msg = 'Beneficiary Id - ' . $beneficiary_id . ' reverted successfully';
        try {
          DB::connection('pgsql_appwrite')->beginTransaction();
          DB::connection('pgsql_payment')->beginTransaction();
          // $update_ben_update = [
          //   'next_level_role_id' => 2,
          //   'update_code' => 1,
          //   'remarks' => $comments,
          //   'updated_at' => date('Y-m-d H:i:s')
          // ];
          $updateTableObj = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->first();
          $failed_payment_update = [
            'edited_status' => 0,
            'updated_at' => date('Y-m-d H:i:s')
          ];
          $query = "INSERT INTO lb_scheme.update_ben_details_arc SELECT * FROM lb_scheme.update_ben_details WHERE beneficiary_id = $beneficiary_id AND id = $update_table_id";
          $ubdate_ben_insert =DB::connection('pgsql_appwrite')->insert($query);
          // DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->update($update_ben_update);
          if($ubdate_ben_insert == 1){
           $update_ben_revert = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->delete();
          }
          $update_failed_revert = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)->where('id', $updateTableObj->failed_tbl_id)->update($failed_payment_update);
          $response = array('return_status' => 1, 'return_msg' => $return_msg);
          if($update_ben_revert && $update_failed_revert){
            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_payment')->commit();
          }else{
            DB::connection('pgsql_appwrite')->rollback();
            DB::connection('pgsql_payment')->rollback();
            $response = array('return_status' => 0, 'return_msg' => 'Something went wrong!!');
            $statuscode = 400;
          }
        } catch (\Exception $e) {
          // dd($e);
          // echo  $e->getMessage(); die;
          DB::connection('pgsql_appwrite')->rollback();
          DB::connection('pgsql_payment')->rollback();
          $response = array('return_status' => 0, 'return_msg' => 'Bank details not updated!!');
          $statuscode = 400;
        } finally {
          return response()->json($response, $statuscode);
        }
      } else {
        return response()->json(['return_status' => 0, 'return_msg' => 'Operation type is not valid!!']);
      }
    }
    // Bluk Application Id Approved
    elseif ($fg_is_bulk == 1) { //echo 2;die;
      $bulk_id_arr = explode(',', $bulk_id);
      // DB::unprepared();
      if ($operation_type == 'A') {

        try {
          $count = 0;
          $updateCount = 0;
          foreach ($bulk_id_arr as $key => $value) {
            // print_r($value);die;
            $count++;
            DB::connection('pgsql_appwrite')->beginTransaction();
            DB::connection('pgsql_payment')->beginTransaction();
            $bulk_single_id_arr = explode('_', $value);
            $update_table_id = $bulk_single_id_arr[0];
            $beneficiary_id = $bulk_single_id_arr[1];
            $tableName = Helper::getTable($beneficiary_id);
            // print $update_table_id.' '.$beneficiary_id.'<br>';die();
            $updateTableObjTemp = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)
              ->where('next_level_role_id', '1')->get();
            $updateTableIdTemp = array();
            foreach ($updateTableObjTemp as $updateTableObjTempVal) {
              array_push($updateTableIdTemp, $updateTableObjTempVal->id);
            }
            $updateTableObj = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->first();
            
            // Get Current Financial Year
            $currentyear = date('Y');
            $prevYear = date('Y') - 1;
            $nextyear = date('Y') + 1;
            $month = date('n');
            if ($month > 3) {
                $cur_fin_year = $currentyear .'-' .$nextyear;
            } else {
                $cur_fin_year = $prevYear .'-' .($prevYear+1);
            }

            //    $updateTableObj->new_data='{"bank_name":"UNION BANK OF INDIA IN",
            // "branch_name":"DINHATAA",
            // "bank_ifsc":"UBIN05676551",
            // "bank_code":"6765020200001421",
            // "ben_fname":"Santu",
            // "ben_mname":"Basuri",
            // "ben_lname":"Mondal"}';

            $newDecodeData = json_decode($updateTableObj->new_data);
            $decodeOldData = json_decode($updateTableObj->old_data);
            $ben_bank_update = [
              'bank_code' => $newDecodeData->bank_code,
              'bank_name' => $newDecodeData->bank_name,
              'branch_name' => $newDecodeData->branch_name,
              'bank_ifsc' => $newDecodeData->bank_ifsc,
              'is_dup' => 0,
              'action_by' => Auth::user()->id,
              'action_ip_address' => request()->ip(),
              'action_type' => class_basename(request()->route()->getAction()['controller'])
            ];
            $ben_payment_paymentServer_update = [
              'last_accno' => $newDecodeData->bank_code,
              'last_ifsc' => $newDecodeData->bank_ifsc,
              'acc_validated' => 0,
              'name_validated_modified' => 10,
              'updated_at' => date('Y-m-d H:i:s')
            ];

            // echo 1;die();
            $update_code = 1;
            $ben_personal_update = [];
            if (!empty($newDecodeData->ben_fname) || !empty($newDecodeData->ben_mname) || !empty($newDecodeData->ben_lname)) {
              if (
                $decodeOldData->bank_name != $newDecodeData->bank_name || $decodeOldData->branch_name != $newDecodeData->branch_name ||
                $decodeOldData->bank_ifsc != $newDecodeData->bank_ifsc || $decodeOldData->bank_code != $newDecodeData->bank_code
              ) {
                $update_code = 3;
              } else {
                $update_code = 2;
              }
              $ben_name_array = array('ben_name' => $newDecodeData->ben_fname . ' ' . $newDecodeData->ben_mname . ' ' . $newDecodeData->ben_lname);
              $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, $ben_name_array);

              $personal_name_array = array('action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller']),'ben_fname' => $newDecodeData->ben_fname, 'ben_mname' => $newDecodeData->ben_mname, 'ben_lname' => $newDecodeData->ben_lname);
              $ben_personal_update = array_merge($ben_personal_update, $personal_name_array);
            }
            $status_arr = array('status' => $update_code);
            $ben_personal_update = array_merge($ben_personal_update, $status_arr);
            $failed_tbl_id = array();
            /*--------- For Payment Transaction Bank Update -----------*/
            if ($updateTableObj->failed_type == 2) {
              // dd($updateTableObj->failed_type);
              $update_failed_payment_bulk = 1;
                $failedTableObj = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)
                  ->where('edited_status', '1')->first();
                // dd($failedTableObj);
                $updateFailedApprovedEditedStatus = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)
                  ->where('edited_status', '1')->where('failed_type', $updateTableObj->failed_type)->where('approve_edited_status', 0)->update(['approve_edited_status' => 1]);
                if ($updateFailedApprovedEditedStatus) {
                  $failed_type_app_id = $failedTableObj->failed_type;
                    if (in_array($failed_type_app_id, [2])) {
                      // dd('OK');
                      $final_update = DB::connection('pgsql_payment')->select("Select payment.failed_update_approved_ben(in_ben_id => ARRAY[" . $beneficiary_id . "],  in_failed_type_id => " . $failed_type_app_id . ")");
                      $fun_call = $final_update[0]->failed_update_approved_ben;
                    }
                    // dd();
                }

              //   foreach ($failedTableObj as $failedTableObjValue) {
              //     array_push($failed_tbl_id, $failedTableObjValue->id);
              //     if ($failedTableObjValue->fin_year == $cur_fin_year) {
              //     $pmtMode = $failedTableObjValue->pmt_mode;
              //     if ($pmtMode == 1) {
              //       $lotMode = DB::connection('pgsql_payment')->table('bandhan.lot_master')->where('lot_no', $failedTableObjValue->lot_no)->value('lot_type');
              //     } else if ($pmtMode == 2) {
              //       $lotMode = DB::connection('pgsql_payment')->table($schemaname . '.sbi_lot_master')->where('lot_no', $failedTableObjValue->lot_no)->value('lot_type');
              //     }

                  // array_push($failed_tbl_id, $failedTableObjValue->id);

              //     $lotMonth = $failedTableObjValue->lot_month;
              //     $ben_status_columns = Helper::getMonthColumn($lotMonth);
              //     $lotType = $ben_status_columns['lot_type'];
              //     $lotStatus = $ben_status_columns['lot_status'];

              //     if (trim($lotMode) == 'R') { // Regular Lot
              //       $add_ben_transaction_details = array($lotType => 'E', $lotStatus => 'E');
              //     } else if (trim($lotMode) == 'A') { // Arrear Lot
              //       $add_ben_transaction_details = array($lotType => 'D', $lotStatus => 'E');
              //     }
              //     //echo "<pre>";print_r($add_ben_payment_details);die;
              //     // $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, $add_ben_payment_details);
              //     // echo "<pre>";print_r($add_ben_payment_details);die;
              //     if ($updateTableObj->legacy_validation_update==TRUE) {
              //       $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 10));
              //     }
              //   }
              //   else {
              //     $ben_status_columns = Helper::getMonthColumn(13);
              //     $lotType = $ben_status_columns['lot_type'];
              //     $lotStatus = $ben_status_columns['lot_status'];
              //     $add_ben_transaction_details = array($lotType => 'D', $lotStatus => 'E');
              //     // $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, $add_ben_payment_details); 
              //     if ($updateTableObj->legacy_validation_update==TRUE) {
              //       $ben_payment_paymentServer_update = array_merge($ben_payment_paymentServer_update, array('name_validated_modified' => 10));
              //     }
              //   }
              // }
              
            } else {
              array_push($failed_tbl_id, $updateTableObj->failed_tbl_id);
              $update_failed_payment_bulk = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)->whereIn('id', $failed_tbl_id)->where('edited_status', '1')->update(['edited_status' => 2, 'updated_at' => date('Y-m-d H:i:s')]);
              $fun_call = 1;
            }
            //echo "<pre>";print_r($ben_payment_paymentServer_update);die;
            /*-------- End ----------*/

            $update_ben_update = [
              'next_level_role_id' => 0,
              'update_code' => $update_code,
              'remarks' => $comments,
              'updated_at' => date('Y-m-d H:i:s')
            ];
           
            //echo "<pre>";print_r($ben_bank_update);die;
            $update_ben_personal_details_bulk = DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benTable'])->where('beneficiary_id', $beneficiary_id)->where('next_level_role_id', 0)->whereNull('status')->update($ben_personal_update);
            $update_ben_details_bulk =  DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->whereIn('id', $updateTableIdTemp)->where('next_level_role_id', '1')->update($update_ben_update);
            $update_bank_details_bulk = DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benBankTable'])->where('beneficiary_id', $beneficiary_id)->update($ben_bank_update);
            $update_payment_details_bulk = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $beneficiary_id)->update($ben_payment_paymentServer_update);
            // $update_failed_payment_bulk = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)->whereIn('id', $failed_tbl_id)->where('edited_status', '1')->update(['edited_status' => 2, 'updated_at' => date('Y-m-d H:i:s')]);
            // dump($update_ben_personal_details_bulk); dump($update_ben_details_bulk); dump($update_bank_details_bulk); dump($update_payment_details_bulk); dump($update_failed_payment_bulk); dd($fun_call);
            if($update_ben_personal_details_bulk && $update_ben_details_bulk && $update_bank_details_bulk && $update_payment_details_bulk && $update_failed_payment_bulk && $fun_call)
            {
              $updateCount++;
            }
            // dd($update_failed_payment_bulk);
            // dump($update_ben_personal_details_bulk); dump($update_ben_details_bulk); dump($update_bank_details_bulk); dump($update_payment_details_bulk); dump($update_failed_payment_bulk); dd($fun_call);
            // if($update_ben_personal_details_bulk && $update_ben_details_bulk && $update_bank_details_bulk && $update_payment_details_bulk && $update_failed_payment_bulk)
            // {
            //   DB::connection('pgsql_appwrite')->commit();
            //   DB::connection('pgsql_payment')->commit();
            // }else{
            //   DB::connection('pgsql_appwrite')->rollback();
            //   DB::connection('pgsql_payment')->rollback();
            //   $response = array('return_status' => 0, 'return_msg' => 'Something went wrong!!');
            //   $statuscode = 400;
            // }
            
          }
          // dump($count); dd($updateCount);
          if ($count == $updateCount) {
            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_payment')->commit();
            $return_msg = $count . ' Beneficiaries Bank Details Approved successfully';
            $response = array('return_status' => 1, 'return_msg' => $return_msg);
          } else {
            DB::connection('pgsql_appwrite')->rollback();
            DB::connection('pgsql_payment')->rollback();
            $response = array('return_status' => 0, 'return_msg' => 'Something went wrong!!');
            $statuscode = 400;
          }
          
        } catch (\Exception $e) {
          echo  $e->getMessage(); die;
          DB::connection('pgsql_appwrite')->rollback();
          DB::connection('pgsql_payment')->rollback();
          $response = array('return_status' => 0, 'return_msg' => 'Bank details not updated!!');
          $statuscode = 400;
        } finally {
          return response()->json($response);
        }
      } elseif ($operation_type == 'T') {
        return response()->json(['return_status' => 0, 'return_msg' => 'Bulk revert is not allowed, Please try one by one!!']);
        $return_msg = 'Beneficiaries Reverted successfully';
        try {
          DB::connection('pgsql_appwrite')->beginTransaction();
          DB::connection('pgsql_payment')->beginTransaction();
          foreach ($bulk_id_arr as $key => $value) {
            $bulk_single_id_arr = explode('_', $value);
            $update_table_id = $bulk_single_id_arr[0];
            $beneficiary_id = $bulk_single_id_arr[1];
            // print $update_table_id.' '.$beneficiary_id.'<br>';
            $update_ben_update = [
              'next_level_role_id' => 2,
              'update_code' => 1,
              'remarks' => $comments,
              'updated_at' => date('Y-m-d H:i:s')
            ];
            $updateTableObj = DB::connection('pgsql_appread')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->first();
            $failed_payment_update = [
              'edited_status' => 0,
              'updated_at' => date('Y-m-d H:i:s')
            ];
           $ben_details_update_bulk = DB::connection('pgsql_appwrite')->table('lb_scheme.update_ben_details')->where('beneficiary_id', $beneficiary_id)->where('id', $update_table_id)->update($update_ben_update);
           $failed_details_update_bulk = DB::connection('pgsql_payment')->table('lb_main.failed_payment_details')->where('ben_id', $beneficiary_id)->where('id', $updateTableObj->failed_tbl_id)->update($failed_payment_update);
          }
          $response = array('return_status' => 1, 'return_msg' => $return_msg);
          if($ben_details_update_bulk && $failed_details_update_bulk){
            DB::connection('pgsql_appwrite')->commit();
            DB::connection('pgsql_payment')->commit();
          }else{
            DB::connection('pgsql_appwrite')->rollback();
            DB::connection('pgsql_payment')->rollback();
            $response = array('return_status' => 0, 'return_msg' => 'Something went wrong!!');
            $statuscode = 400;
          }
          
        } catch (\Exception $e) {
          // echo  $e->getMessage(); die;
          DB::connection('pgsql_appwrite')->rollback();
          DB::connection('pgsql_payment')->rollback();
          $response = array('return_status' => 0, 'return_msg' => 'Bank details not updated!!');
          $statuscode = 400;
        } finally {
          return response()->json($response, $statuscode);
        }
      } else {
        return response()->json(['return_status' => 0, 'return_msg' => 'Operation type is not valid!!']);
      }
    }
    return response()->json(['return_status' => 1, 'return_msg' => 'Successfully']);
  }

}
