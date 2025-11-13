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
use App\TModels\aluka;
use App\Models\Ward;
use App\Models\GP;
use App\Models\SchemeDocMap;
use App\Models\DocumentType;
use App\Helpers\Helper;
use App\Models\DataSourceCommon;
use App\Models\DsPhase;
use App\Models\RejectRevertReason;
use App\Models\Scheme;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
class DeactivatedBeneficiaryController extends Controller
{
  public function __construct()
  {
    set_time_limit(60);
    $this->middleware('auth');
    $this->source_type = 'ss_nfsa';
    $this->scheme_id = 20;
  }
  /*
    Landing Page on the De-activated Beneficiary
  */
  public function index()
  {
    //return redirect("/")->with('error', 'Payment Server is down for Maintenance. Please try after some time.');

    if (Auth::user()->designation_id == 'Approver' || Auth::user()->designation_id == 'Verifier' || Auth::user()->designation_id == 'Delegated Approver' || Auth::user()->designation_id == 'Delegated Verifier') {
      return view('de-activated-beneficiary/index');
    } else {
      return redirect('/')->with('success', 'Unauthorized');
    }
  }
  /*
    Get List of Beneficiary
  */
  public function getData(Request $request)
  {
    if ($request->ajax()) {
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
      if ($designation == 'Delegated Verifier' ||  $designation == 'Delegated Approver' || $designation == 'Approver' || $designation == 'Verifier') {
        if (!empty($beneficiary_id) || !empty($application_id) || !empty($ss_card_no)) {
          $query = "(select md.district_name, bl_div.block_subdiv_name, bp.ben_fname,bp.ben_mname, bp.ben_lname,bp.beneficiary_id, bp.mobile_no,bp.ss_card_no, bp.application_id, bp.next_level_role_id, bc.block_ulb_name,bc.gp_ward_name, bc.rural_urban_id, bb.bank_code,bb.bank_ifsc, bp.ds_phase,bp.dob,to_char(bp.dob + interval '60 year','yymm')::smallint as end_yymm from lb_scheme.ben_personal_details bp
            JOIN lb_scheme.ben_contact_details bc ON bp.beneficiary_id=bc.beneficiary_id
            JOIN lb_scheme.ben_bank_details bb ON bb.beneficiary_id=bp.beneficiary_id 
            JOIN public.m_district md ON md.district_code=bp.created_by_dist_code 
            JOIN (select block_code as block_subdiv_code,block_name as block_subdiv_name from public.m_block UNION ALL
              select sub_district_code as block_subdiv_code, sub_district_name as block_subdiv_name from public.m_sub_district
            ) bl_div ON bl_div.block_subdiv_code=bp.created_by_local_body_code
            where bp.created_by_dist_code=" . $distCode . " ";
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
            where bp.created_by_dist_code=" . $distCode . " ";
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
          $data = DB::connection('pgsql_appread')->select($query);
        } else {
          $data = collect([]);
        }
      } else {
        $data = collect([]);
      }

      return datatables()->of($data)
        // ->addIndexColumn()
        ->addColumn('action', function ($data) {
          $action = '';
          $aadhar_count = DB::connection('pgsql_appread')->table('lb_scheme.ben_aadhar_details')->where('beneficiary_id', $data->beneficiary_id)->where('application_id', $data->application_id)->where('is_dup', 1)->count();
          if ($aadhar_count > 0) {
            $action = '<h4><label class="label label-danger">Please De-duplicate Aadhar First</label></h4>';
          } else {
            if ($data->next_level_role_id == 0 || $data->next_level_role_id == -94) {
              $getModelFunc = new getModelFunc();
              $schemaname = $getModelFunc->getSchemaDetails();
              $benStatus = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->select('ben_status')->where('ben_id', $data->beneficiary_id)->first();
              if (!empty($benStatus)) {
                if ($benStatus->ben_status == 1) {
                  $action =
                    '<div>
                    <select class="form-control" name="select_item_update" id="select_item_update_' . $data->beneficiary_id . '" required>
                      <option value="">---- Select ----</option>
                      <option value="SP" selected>De-activate Beneficiary</option>
                    </select>
                  </div>
                  <div align="center" style="margin-top:5px;">
                    <button class="btn btn-info btn-block btn-sm" name="ben_edit" class="ben_edit" value="' . $data->beneficiary_id . '" onclick="editFunction(' . $data->beneficiary_id . ');"><i class="fa fa-edit"></i> Edit</button>
                  </div>';
                } elseif ($benStatus->ben_status == -97) {
                  $action = '<h5><label class="label label-danger">Bank A/c & IFSC Duplicate, please De-duplicate Bank Account.</label></h5>';
                } elseif ($benStatus->ben_status == -98) {
                  $action = '<h5><label class="label label-danger">Bank A/c & IFSC Duplicate Reject</label></h5>';
                } elseif ($benStatus->ben_status == -99) {
                  $action = '<h5><label class="label label-danger">In-active Beneficiary</label></h5>';
                } elseif ($benStatus->ben_status == -102) {
                  $action = '<h5><label class="label label-danger">Caste change modification is under process</label></h5>';
                } elseif ($benStatus->ben_status == -94) {
                  $action = '<h5><span class="text-danger"><b>Beneficiary Payment has been <br>Suspended due to Death case<br>(As per the data Comes from<br> Janma-Mrityu Portal)</b></span></h5>';
                } else {
                  $action = '<h5><label class="label label-danger">In-active Beneficiary</label></h5>';
                }
              } else {
                if ($data->ds_phase == 4 || $data->ds_phase == 5) {
                  $action = '<p class="text-primary"><b>Account validation and payment<br> process has not been initiated.</b></p>';
                }
                else {
                  $action = '<h5><label class="label label-danger">Beneficiary is under migration process</label></h5>';
                }
              }
              $current_yymm = date('ym');
              if ($data->end_yymm <= $current_yymm) { 
                $monthYear = Config::get('constants.month_list.' . substr($data->end_yymm, 2, 2)) . ' - 20' . substr($data->end_yymm, 0, 2);
                $action = '<p class="text-primary"><b>Beneficiary age exceeded <br>60 years on ' . $monthYear . '</b></p>';
              }
            } else {
              $action = '<h5><label class="label label-danger">Already De-activate</label></h5>';
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
    Get Beneficiary Personal Data
  */
  public function getBeneficiaryPersonalData(Request $request)
  {
    $response = [];
    $statusCode = 200;
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }
    try {
      $beneficiary_id = $request->benid;
      $tableName = Helper::getTable($beneficiary_id);
      $query = '';
      $query = "select * from lb_scheme." . $tableName['benTable'] . " bp where bp.beneficiary_id=" . $beneficiary_id . " and bp.next_level_role_id=0";
      $attachment_doc = "select * from public.m_attached_doc where id>=100";
      $data = DB::connection('pgsql_appread')->select($query);
      $attach = DB::connection('pgsql_appread')->select($attachment_doc);
      $response = array(
        'personaldata' => $data, 'attach_doc' => $attach
      );
    } catch (\Exception $e) {
      $response = array(
        'exception' => true,
        'exception_message' => $e->getMessage(),
      );
      $statusCode = 400;
    } finally {
      return response()->json($response, $statusCode);
    }
  }
  /*
    Final Section Stop Payment of one Beneficiay
  */
  public function updateStopPaymentFinal(Request $request)
  {

    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('return_status' => 0, 'return_msg' => 'Error occured in form submit.');
      return response()->json($response);
    }
    $user_id = Auth::user()->id;
    $designation_id = Auth::user()->designation_id;
    $errormsg = Config::get('constants.errormsg');
    $duty = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
    if ($duty->isEmpty) {
      return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
    }
    $is_valid = 0;
    if ($designation_id == 'Approver' || $designation_id == 'Verifier' || $designation_id == 'Delegated Verifier' ||  $designation_id == 'Delegated Approver') {
      $is_valid = 1;
    } else {
      return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
    }

    $comments = $request->comments;
    $update_type = $request->update_type;
    $beneficiary_id = $request->beneficiary_id;
    $application_id = $request->application_id;
    $doc_type = $request->doc_type;
    $reason = $request->reason;
    $file_stop_payment = $request->file('file_stop_payment');
    $getModelFunc = new getModelFunc();
    $schemaname = $getModelFunc->getSchemaDetails();
    $tableName = Helper::getTable($beneficiary_id);
    if ($is_valid == 1) {
      try {
        DB::connection('pgsql_appwrite')->beginTransaction();
        DB::connection('pgsql_encwrite')->beginTransaction();
        DB::connection('pgsql_payment')->beginTransaction();
        $getBenDetailsObj = DB::connection('pgsql_appread')
          ->table('lb_scheme.' . $tableName['benTable'] . ' AS bp')
          ->join('lb_scheme.' . $tableName['benContactTable'] . ' AS bc', 'bc.beneficiary_id', '=', 'bp.beneficiary_id')
          ->where('bp.beneficiary_id', $beneficiary_id)
          ->where('bp.application_id', $application_id)
          ->select('bp.ben_fname', 'bp.ben_mname', 'bp.ben_lname', 'bp.ss_card_no', 'bp.mobile_no', 'bp.created_by_dist_code', 'bp.created_by_local_body_code', 'bc.block_ulb_code', 'bc.gp_ward_code', 'bc.rural_urban_id')
          ->get();
        $getFaultyObj = DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $beneficiary_id)->first();
        $getAadharDetailsObj = DB::connection('pgsql_appread')->table('lb_scheme.ben_aadhar_details')->where('beneficiary_id', $beneficiary_id)
          ->where('application_id', $application_id)->first();

        // New For Aadhar Table Update
        $is_aadhar_update = 0;
        if (isset($getAadharDetailsObj)) {
          $updateAadhaarDetails = [
            'aadhar_hash_adj' => $getAadharDetailsObj->aadhar_hash,
            'aadhar_hash' => null,
            'action_by' => Auth::user()->id,
            'action_ip_address' => request()->ip(),
            'action_type' => class_basename(request()->route()->getAction()['controller'])
          ];
          $is_aadhar_update = 1;
        }

        $img_data = file_get_contents($file_stop_payment);
        $extension = $file_stop_payment->getClientOriginalExtension();
        $mime_type = $file_stop_payment->getMimeType();
        $base64 = base64_encode($img_data);
        $miscAttachmentInsert = [
          'application_id' => $application_id,
          'beneficiary_id' => $beneficiary_id,
          'document_type' => $doc_type,
          'attched_document' => $base64,
          'user_level' => $duty->mapping_level,
          'created_by' => $user_id,
          'document_extension' => $extension,
          'document_mime_type' => $mime_type,
          'status' => 1,
          'created_at' => date('Y-m-d H:i:s')
        ];
        $miscBenDetailsInsert = [
          'beneficiary_id' => $beneficiary_id,
          'application_id' => $application_id,
          'ben_name' => $getBenDetailsObj[0]->ben_fname . ' ' . $getBenDetailsObj[0]->ben_mname . ' ' . $getBenDetailsObj[0]->ben_lname,
          'ss_card_no' => $getBenDetailsObj[0]->ss_card_no,
          'mobile_no' => $getBenDetailsObj[0]->mobile_no,
          'created_by' => $user_id,
          'user_level' => $duty->mapping_level,
          'reason' => $reason,
          'remarks' => $comments,
          'status' => 1,
          'dist_code' => $getBenDetailsObj[0]->created_by_dist_code,
          'local_body_code' => $getBenDetailsObj[0]->created_by_local_body_code,
          'rural_urban_id' => $getBenDetailsObj[0]->rural_urban_id,
          'block_ulb_code' => $getBenDetailsObj[0]->block_ulb_code,
          'gp_ward_code' => $getBenDetailsObj[0]->gp_ward_code,
          'created_at' => date('Y-m-d H:i:s')
        ];
        $updateBenPersonalDetails = ['action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])];

        $updateBenPaymentDetails = ['ben_status' => -99, 'rejected_date' => date('Y-m-d H:i:s')];
        // echo $is_aadhar_update;die();
        //------------------ Database Operations ----------------------
        DB::connection('pgsql_encwrite')->table('lb_scheme.misc_ben_documents')->insert($miscAttachmentInsert);
        DB::connection('pgsql_appwrite')->table('lb_scheme.misc_ben_details')->insert($miscBenDetailsInsert);
        DB::connection('pgsql_appwrite')->table('lb_scheme.' . $tableName['benTable'] . '')->where('beneficiary_id', $beneficiary_id)->where('application_id', $application_id)->update($updateBenPersonalDetails);
        if ($is_aadhar_update == 1) {
          DB::connection('pgsql_appwrite')->table('lb_scheme.ben_aadhar_details')->where('beneficiary_id', $beneficiary_id)->where('application_id', $application_id)->update($updateAadhaarDetails);
        }
        DB::connection('pgsql_payment')->table($schemaname . '.ben_payment_details')->where('ben_id', $beneficiary_id)->where('application_id', $application_id)->update($updateBenPaymentDetails);
        DB::connection('pgsql_payment')->table('lb_main.ben_payment_details_bank_code_dup')->where('ben_id', $beneficiary_id)->where('application_id', $application_id)->update($updateBenPaymentDetails);

        // New 16-12-2021
        $in_pension_id = 'ARRAY[' . "'$application_id'" . ']';
        if ($getFaultyObj->faulty_status) {
          
          //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(" . $in_pension_id . "," . $reason . ",'" . $comments . "')");
          $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary_faulty(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."',rejected_cause => " . $reason . ",comment_message => '" . $comments . "')");

        } else {
          //$reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(" . $in_pension_id . "," . $reason . ",'" . $comments . "')");
          $reject_fun = DB::connection('pgsql_appwrite')->select("select lb_scheme.rejected_approved_beneficiary(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."',rejected_cause => " . $reason . ",comment_message => '" . $comments . "')");

        }
        if ($reject_fun) {

          // if($application_id==101147706)
          // {

          //   dd(123);
          // }
          $accept_reject_model = new DataSourceCommon;
          $Table = $getModelFunc->getTable($duty->district_code, $this->source_type, 9);
          $accept_reject_model->setTable('' . $Table);
          $accept_reject_model->op_type = 'RA';
          $accept_reject_model->ben_id = $beneficiary_id;
          $accept_reject_model->application_id = $application_id;
          $accept_reject_model->designation_id = $designation_id;
          $accept_reject_model->scheme_id = 20;
          $accept_reject_model->user_id = $user_id;
          $accept_reject_model->comment_message = $comments;
          $accept_reject_model->mapping_level = $duty->mapping_level;
          $accept_reject_model->created_by = $user_id;
          $accept_reject_model->created_by_level = $duty->mapping_level;
          $accept_reject_model->created_by_dist_code = $getBenDetailsObj[0]->created_by_dist_code;
          $accept_reject_model->rejected_reverted_cause = $reason;
          $accept_reject_model->ip_address = request()->ip();
          $is_saved3 = $accept_reject_model->save();
        }

        //----------------- End Database Opertion --------------------

        $response = array('return_status' => 1, 'return_msg' => 'De-activated Successfully');
        DB::connection('pgsql_appwrite')->commit();
        DB::connection('pgsql_encwrite')->commit();
        DB::connection('pgsql_payment')->commit();
      } catch (\Exception $e) {
        dd($e);
        DB::connection('pgsql_appwrite')->rollback();
        DB::connection('pgsql_encwrite')->rollback();
        DB::connection('pgsql_payment')->rollback();
        $response = array(
          'return_status' => 0,
          'return_msg' => $e->getMessage(),
          // 'return_msg' => 'De-activation Failed !!'
        );
        $statusCode = 400;
      } finally {
        return response()->json($response, $statusCode);
      }
    } else {
      return response()->json(['return_status' => 0, 'return_msg' => 'Something went wrong !!']);
    }
  }
  /*
    Stop Payment List Landing Page
  */
  public function deActivatedReport(Request $request)
  {
    $designation = Auth::user()->designation_id;
    $user_id = Auth::user()->id;
    $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
    $mapLevel = $dutyObj->mapping_level;
    $distCode = $dutyObj->district_code;
    if ($dutyObj->is_urban == 1) {
      $bodyCode = $dutyObj->urban_body_code;
    } else {
      $bodyCode = $dutyObj->taluka_code;
    }
    $district = District::select('district_code', 'district_name')->get();
    return view('de-activated-beneficiary/linelisting_de_activated', ['districts' => $district, 'mapLevel' => $mapLevel, 'distCode' => $distCode, 'bodyCode' => $bodyCode, 'rural_urban' => $dutyObj->is_urban]);
  }
  /*
    Stop Payment Get Data
  */
  public function getDeActivatedBenDataList(Request $request)
  {
    if ($request->ajax()) {
      $user_id = Auth::user()->id;
      $dutyObj = Configduty::where('user_id', '=', $user_id)->where('is_active', 1)->first();
      $distCode = $dutyObj->district_code;
      if ($dutyObj->is_urban == 1) {
        $bodyCode = $dutyObj->urban_body_code;
      } else {
        $bodyCode = $dutyObj->taluka_code;
      }

      $ajax_dist_code = $request->dist_code;
      $ajax_rural_urban = $request->filter_1;
      $ajax_local_body = $request->filter_2;
      if ($ajax_dist_code == '') {
        $finalDistCode = $distCode;
      } else {
        $finalDistCode = $ajax_dist_code;
      }
      if ($ajax_rural_urban == '') {
        $finalRuralUrban = $dutyObj->is_urban;
      } else {
        $finalRuralUrban = $ajax_rural_urban;
      }
      if ($ajax_local_body == '') {
        $finalLocalBody = $bodyCode;
      } else {
        $finalLocalBody = $ajax_local_body;
      }
      $query = '';
      $query = "select * from lb_scheme.ben_personal_details bp
        JOIN lb_scheme.ben_contact_details bc ON bp.beneficiary_id=bc.beneficiary_id 
        where bp.next_level_role_id=-99 ";
      if (!is_null($finalDistCode)) {
        $query .= " and bp.created_by_dist_code=" . $finalDistCode . " ";
      }
      if (!is_null($finalRuralUrban)) {
        $query .= " and bc.rural_urban_id=" . $finalRuralUrban . " ";
      }
      if (!is_null($finalLocalBody)) {
        $query .= " and bp.created_by_local_body_code=" . $finalLocalBody . " ";
      }
      $query .= " order by bp.beneficiary_id";
      // print $query;die();
      $data = DB::connection('pgsql_appread')->select($query);

      return datatables()->of($data)
        ->addIndexColumn()
        ->addColumn('beneficiary_id', function ($data) {
          return $data->beneficiary_id;
        })
        ->addColumn('application_id', function ($data) {
          return $data->application_id;
        })
        ->addColumn('name', function ($data) {
          return $data->ben_fname . ' ' . $data->ben_mname . ' ' . $data->ben_lname;
        })
        ->addColumn('ss_card_no', function ($data) {
          return $data->ss_card_no;
        })
        ->addColumn('mobile_no', function ($data) {
          return $data->mobile_no;
        })
        ->addColumn('block_ulb_name', function ($data) {
          return $data->block_ulb_name;
        })
        ->addColumn('gp_ward_name', function ($data) {
          return $data->gp_ward_name;
        })
        ->rawColumns(['beneficiary_id', 'application_id', 'name', 'ss_card_no', 'mobile_no', 'block_ulb_name', 'gp_ward_name'])
        ->make(true);
    }
  }
  public function schemeSessionCheck(Request $request)
  {
      $scheme_id = $this->scheme_id;
      $is_active = 0;
      $roleArray = $request->session()->get('role');
      foreach ($roleArray as $roleObj) {
          if ($roleObj['scheme_id'] == $scheme_id) {
              $is_active = 1;
              $request->session()->put('level', $roleObj['mapping_level']);
              $distCode = $roleObj['district_code'];
              $request->session()->put('distCode', $roleObj['district_code']);
              $request->session()->put('scheme_id', $scheme_id);
              $request->session()->put('is_first', $roleObj['is_first']);
              $request->session()->put('is_urban', $roleObj['is_urban']);
              $request->session()->put('role_id', $roleObj['id']);
              if ($roleObj['is_urban'] == 1) {
                  $request->session()->put('bodyCode', $roleObj['urban_body_code']);
              } else {
                  $request->session()->put('bodyCode', $roleObj['taluka_code']);
              }
              break;
          }
      }
      if ($is_active == 1) {
          return true;
      } else {
          return false;
      }
  }
  public function listReport(Request $request)
  {
    try {
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $urban_body_code = '';
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $mapping_level = $roleObj['mapping_level'];
                if($mapping_level=='District'){
                  $is_rural_visible=1;
                  $block_munc_visible=1;
                }
                $is_urban = $roleObj['is_urban'];
                $distCode = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $block_munc_visible=1;
                    $block_munc_text='Municipality';
                    $gp_ward_text='WARD';
                    $blockCode = $roleObj['urban_body_code'];
                    $urban_body_code = $roleObj['urban_body_code'];
                    $block_munc_list = UrbanBody::select('urban_body_code as code','urban_body_name as name')->where('sub_district_code', $blockCode)->get();
                } else if ($roleObj['is_urban'] == 2) {
                    $gp_ward_text='GP';
                    $blockCode = $roleObj['taluka_code'];
                    $urban_body_code = $blockCode;
                    $block_ulb_code=$blockCode;
                    $gp_ward_list = GP::select('gram_panchyat_code as code','gram_panchyat_name as name')->where('block_code', $blockCode)->get();
                }
                break;
            }
        }
     // $ds_phase_list = Config::get('constants.ds_phase.phaselist');
         $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');

         $mappingLevel = $request->session()->get('level');
         $role_name = Auth::user()->designation_id;
         //$rejection_cause_list = Config::get('constants.rejection_cause');
         $rejection_cause_list = RejectRevertReason::where('status', true)->get()->toArray();
         $designation_id = Auth::user()->designation_id;
         $is_rural_visible = 0;
         $urban_visible = 0;
         $munc_visible = 0;
         $gp_ward_visible = 0;
         $muncList = collect([]);
         $gpwardList = collect([]);
         $modelName = new DataSourceCommon;
         $getModelFunc = new getModelFunc();
        
         $download_excel = 1;
        
         $condition = array();
         $report_type=$request->report_type;
          //dd($report_type);
         if ($report_type == 'D') {
          $report_type_name = 'Deacivated Beneficiary List';

        }
        else if ($report_type == 'R') {
          $report_type_name = 'Name Validation Rejection';

        }
        else{
          $report_type_name = 'Deacivated/Name Validation Rejection';
        }
         $logTable='lb_scheme.ben_accept_reject_info';
         //$Table
         $Table = 'lb_scheme.ben_reject_details';
         $modelName->setConnection('pgsql_appread');
         $modelName->setTable('' . $Table);
         $condition[$Table . ".created_by_dist_code"] = $distCode;  
         if(in_array($designation_id, array('Operator','Verifier','Delegated Verifier'))){
            $condition[$Table . ".created_by_local_body_code"] = $blockCode;
         }
        
         if (request()->ajax()) {
            $searchValue = trim($request->search['value'] ?? '');
            $ds_phase    = trim($request->ds_phase ?? '');
            $rural_urbanid     = trim($request->rural_urbanid ?? '');
            $urban_body_code     = trim($request->urban_body_code ?? '');
            $block_ulb_code     = trim($request->block_ulb_code ?? '');
            $gp_ward_code  = trim($request->gp_ward_code ?? '');
            $report_type  = trim($request->report_type ?? '');
            $query = $modelName
                ->where($condition)
                ->select([
                    $Table . '.created_by_dist_code as created_by_dist_code',
                    $Table . '.application_id as application_id',
                    $Table . '.ds_phase as ds_phase',
                    $Table . '.ben_fname as ben_fname',
                    $Table . '.ben_mname as ben_mname',
                    $Table . '.ben_lname as ben_lname',
                    $Table . '.father_fname as father_fname',
                    $Table . '.father_mname as father_mname',
                    $Table . '.father_lname as father_lname',
                    $Table . '.mobile_no as mobile_no',
                    $Table . '.caste as caste',
                    $Table . '.block_ulb_name as block_ulb_name',
                    $Table . '.gp_ward_name as gp_ward_name',
                    $Table . '.village_town_city as village_town_city',
                    $Table . '.bank_ifsc as bank_ifsc',
                    $Table . '.bank_code as bank_code',
                    $Table . '.rejected_cause as rejected_cause',
                    $Table . '.next_level_role_id as next_level_role_id',

                ]);
          

           
           
            if ($ds_phase !== '') {
                if($ds_phase==0){
                 $query->whereRaw(" (".$Table.".ds_phase=0 or ".$Table.".ds_phase IS NULL");
                }
                $query->whereRaw(" (".$Table.".ds_phase=".$ds_phase." or ".$Table.".mark_ds_phase=".$ds_phase."");
            }
            if (!empty($rural_urbanid)) {
                $query->where($Table . ".rural_urban_id", $rural_urbanid);
            }
            if (!empty($urban_body_code)) {
                $query->where($Table . ".created_by_local_body_code", $urban_body_code);
            }
            if (!empty($block_ulb_code)) {
                $query->where($Table . ".block_ulb_code", $block_ulb_code);
            }
            if (!empty($gp_ward_code)) {
                $query->where($Table . ".gp_ward_code", $gp_ward_code);
            }
            if ($report_type == 'D') {
               $query->where($Table .'.next_level_role_id',-99);
              
             // $query = $query->where('op_type','RA');
              //$report_type_name = 'Deacivated Beneficiary List';
  
            }
            else if ($report_type == 'R') {
              $query->where($Table .'.next_level_role_id',-400);
             // $query = $query->where('op_type','VR')->where('rejected_reverted_cause',3);
              //$report_type_name = 'Name Validation Rejection';
  
            }
            else{
             // $report_type_name = 'Deacivated/Name Validation Rejection';
              $query = $query->whereIn($Table .'.next_level_role_id',[-400,-99]);
              //$query = $query->whereIN('op_type',['RA','VR']);
            }
// dd($query->toSql());
            // Yajra v12 DataTables (NO manual offset/limit/count)
            return DataTables::eloquent($query)
                ->filter(function ($q) use ($searchValue, $Table) {
                    if ($searchValue == '') {
                        return;
                    }   

                    // $sv = trim($searchValue);
                    // if (is_numeric($sv)) {
                    //     $q->where(function ($sub) use ($sv, $personal_table) {
                    //         $sub->where("{$personal_table}.application_id", (int)$sv)
                    //             ->orWhereRaw("CAST({$personal_table}.mobile_no AS TEXT)", [$sv]);
                    //     });
                    // } else {
                    //     $q->where(function ($sub) use ($sv, $personal_table) {
                    //         $sub->where("{$personal_table}.ben_fname", 'ilike', $sv . '%');
                    //     });
                    // }

                    
                    if (is_numeric($searchValue)) {
                        
                       $q->where(function ($q) use ($Table, $searchValue) {
                            // Cast columns to TEXT and compare as string to avoid integer overflow
                            $q->whereRaw("CAST({$Table}.application_id AS TEXT) = ?", [$searchValue])
                                ->orWhereRaw("CAST({$Table}.mobile_no AS TEXT) = ?", [$searchValue]);
                        });
                        // dd($q->tosql());
                    } else {
                        // dd('kii');
                        $q->Where(function ($q) use ($Table, $searchValue) {
                            $q->orWhere($Table . '.ben_fname', 'ilike', $searchValue . '%');
                        });
                        return $q;
                        // dd($q->tosql());
                    }
                    // dd($q->tosql());
                }, true)
                ->addColumn('name', function ($data) {
                     return ($data->ben_fname . ' ' . $data->ben_mname . ' ' . $data->ben_lname);
                 })->addColumn('father_name', function ($data) {
                     return ($data->father_fname . ' ' . $data->father_mname . ' ' . $data->father_lname);
                 })->addColumn('mobile_no', function ($data) {
                     return $data->mobile_no;
                 })->addColumn('applicant_mobile_no', function ($data) use ($report_type) {
                     
                         return $data->applicant_mobile_no;
                     
                 })->addColumn('rejected_type', function ($data) use ($report_type) {
                   $rejected_type='Deactivated/Name Validation Rejection';
                   if($data->next_level_role_id==-99)
                   $rejected_type='Deactivated';
                   else if($data->next_level_role_id==-400)
                   $rejected_type='Name Validation';
                   return $rejected_type;
               
               })->addColumn('rejected_reason', function ($data) use ($report_type, $rejection_cause_list) {
                        $description='';
                         foreach ($rejection_cause_list as $rejArr) {
                             if ($rejArr['id'] == $data->rejected_reason) {
                                 $description = $rejArr['reason'];
                                 break;
                             }
                         }
                         return $description;
                     
                 })->addColumn('rejected_by', function ($data) use ($report_type) {
                  $rejected_by_row = DB::select("select created_by,created_at from lb_scheme.ben_accept_reject_info 
                  where application_id=" . $data->application_id . " and trim(op_type) IN ('RA','VR')  order by id limit 1"); 
                  if (!empty($rejected_by_row)) {
                    
                  $return_text='Rejected by the Approver on '.$rejected_by_row[0]->created_at;
                   
                    return $return_text;
                  }
                  else{
                    return 'NA';
                  }
          
                })->make(true);
             
         } else {
             $errormsg = Config::get('constants.errormsg');
             $scheme_name_arr = Scheme::select('scheme_name')->where('id', $this->scheme_id)->first();
             return view('de-activated-beneficiary.listReport')
                 ->with('district_code', $request->session()->get('distCode'))
                 ->with('scheme', $request->session()->get('scheme_id'))
                 // ->with('schemetype','$schemetype')
                 ->with('report_type_name', $report_type_name)
                 ->with('is_rural_visible', $is_rural_visible)
                 ->with('is_urban', $is_urban)
                 ->with('urban_visible', $urban_visible)
                 ->with('urban_body_code', $urban_body_code)
                 ->with('urban_visible', $urban_visible)
                 ->with('munc_visible', $munc_visible)
                 ->with('gp_ward_visible', $gp_ward_visible)
                 ->with('muncList', $muncList)
                 ->with('gpwardList', $gpwardList)
                 ->with('mappingLevel', $mappingLevel)
                 ->with('sessiontimeoutmessage', $errormsg['sessiontimeOut'])
                 ->with('scheme_name',  $scheme_name_arr->scheme_name)
                 ->with('ds_phase_list',  $ds_phase_list)
                 ->with('download_excel',  $download_excel);
         }
     
    }
    catch (\Exception $e) {
      dd($e);
      }
    }
    public function generate_excel(Request $request)
    {
      try {
       // $dob_base_date = Carbon::parse($this->base_dob_chk_date)->format('d/m/Y');
        $scheme_id = $this->scheme_id;
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $mapping_level = $roleObj['mapping_level'];
                $district_code = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $urban_body_code = $roleObj['urban_body_code'];
                } else {
                    $urban_body_code = $roleObj['taluka_code'];
                }
                break;
            }
        }

        $modelName = new DataSourceCommon;
        $getModelFunc = new getModelFunc();
        $report_type = $request->get('type');
        $condition = array();
        $role_name = Auth::user()->designation_id;
        $scheme_name_row = Scheme::where('id', $scheme_id)->first();
        $scheme_name = $scheme_name_row->scheme_name;
        $report_type=$request->report_type;
        if ($report_type == 'D') {
         $report_type_name = 'Deacivated Beneficiary List';

       }
       else if ($report_type == 'R') {
         $report_type_name = 'Name Validation Rejection';

       }
       else{
         $report_type_name = 'Deacivated/Name Validation Rejection';
       }
       $rejection_cause_list = RejectRevertReason::where('status', true)->get()->toArray();
            $Table = 'lb_scheme.ben_reject_details';
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            //$condition[$contact_table . ".created_by_dist_code"] = $district_code;
            //$condition[$bank_table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier' || $role_name == 'Delegated Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                // $condition[$contact_table . ".created_by_local_body_code"] = $urban_body_code;
                // $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
            }
            $query = $modelName->where($condition);
            if ($report_type == 'D') {
              $query = $query->where($Table .'.next_level_role_id',-99);
             // $query = $query->where('op_type','RA');
              //$report_type_name = 'Deacivated Beneficiary List';
  
            }
            else if ($report_type == 'R') {
              $query = $query->where($Table .'.next_level_role_id',-400);
             // $query = $query->where('op_type','VR')->where('rejected_reverted_cause',3);
              //$report_type_name = 'Name Validation Rejection';
  
            }
            else{
             // $report_type_name = 'Deacivated/Name Validation Rejection';
              $query = $query->whereIn($Table .'.next_level_role_id',[-400,-99]);
              //$query = $query->whereIN('op_type',['RA','VR']);
            }
            $data = $query->select(
                '' . $Table . '.application_id as application_id',
                'ben_fname',
                'ben_mname',
                'ben_lname',
                'father_fname',
                'father_mname',
                'father_lname',
                'mother_fname',
                'mother_mname',
                'mother_lname',
                'mobile_no',
                'dob',
                'age_ason_01012021',
                'caste',
                'ss_card_no',
                'next_level_role_id',
                'duare_sarkar_registration_no',
                'block_ulb_name',
                'gp_ward_name',
                'village_town_city',
                'bank_ifsc',
                'bank_code',
                'ds_phase',
                'rejected_cause'
            )->orderBy($Table . '.ben_fname')->orderBy($Table . '.gp_ward_name')->get();
            //dd($data);
            $excel_data[] = array(
                'Application ID', 'Applicant Name', 'Father\'s Name', 'Caste',
                'Swasthyasathi Card No.', 'Block/Municipality', 'GP/WARD', 'Village/Town/City', 'Bank IFSC', 'Bank Account No.','Rejection Type'
            );
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') . ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><td alignment="center" colspan="14">'.$report_type_name.'</td></tr>';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Father\'s Name</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Block/Municipality</th><th>GP/WARD</th><th>Village/Town/City</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th><th>Rejection Type</th></tr>';
            if (count($data) > 0) {
                foreach ($data as $row) {
                    $sws_card_no = (string) $row->ss_card_no;
                    if (!empty($sws_card_no))
                        $ss_card_no = "'$sws_card_no'";
                    else
                        $ss_card_no = '';
                    $bank_code = (string) $row->bank_code;
                    if (!empty($bank_code))
                        $f_bank_code = "'$bank_code'";
                    else
                        $f_bank_code = '';
                    
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    //$rejected_type='Deactivated/Name Validation Rejection';
                    if($row->next_level_role_id==-99)
                    $rejected_type='Deactivated';
                    else if($row->next_level_role_id==-400)
                    $rejected_type='Name Validation';
                   /* $rejected_by_row = DB::select("select created_by,created_at from lb_scheme.ben_accept_reject_info 
                  where application_id=" . $row->application_id . " and trim(op_type) IN ('RA','VR')  order by id limit 1"); 
                  if (!empty($rejected_by_row)) {
                    $reject_text='';
                    $rejected_by_user_row = DB::select("select mobile_no from public.users  where id=" . $rejected_by_row[0]->created_by."  limit 1"); 
                    if (!empty($rejected_by_user_row)) {
                      $reject_text='Rejected by the User with Mobile No.'.$rejected_by_user_row[0]->mobile_no.' on '.$rejected_by_row[0]->created_at;
                    }
                    else{
                      $reject_text='NA';
                    }
                   
                  }
                  else{
                    $reject_text='NA';
                  }
                  $cause_description='';
                         foreach ($rejection_cause_list as $rejArr) {
                             if ($rejArr['id'] == $row->rejected_cause) {
                                 $description = $rejArr['reason'];
                                 break;
                             }
                      }
                      */
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->village_town_city) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td><td>" . $rejected_type . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="16">No Records found</td></tr>';
            }
            echo '</table>';
          }catch (\Exception $e) {
           //dd($e);
        }
        }
        function getPhaseDes($phase_code)
        {
            $phaseArr = DsPhase::where('phase_code', $phase_code)->first();
            //$phaselist = Config::get('constants.ds_phase.phaselist');
            $des = 'Phase II';
            if (!empty($phaseArr)) {
                $des = $phaseArr->phase_des;
            }
            return $des;
        }
}
