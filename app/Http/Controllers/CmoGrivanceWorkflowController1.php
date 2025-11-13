<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\District;
use App\Scheme;
use Redirect;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Validator;
use DateTime;
use Config;
use App\Models\Configduty;
use Maatwebsite\Excel\Facades\Excel;
use App\DataSourceCommon;

use App\getModelFunc;
use Illuminate\Support\Facades\Crypt;
use App\RejectRevertReason;
use App\AadharDuplicateTrail;
use App\SubDistrict;
use App\Taluka;
use App\DocumentType;
use Illuminate\Support\Facades\Storage;
use App\SchemeDocMap;
use File;
use App\BankDetails;
use App\UrbanBody;
use App\Ward;
use App\Models\GP;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\AcceptRejectInfo;
use App\Traits\TraitAadharUpdate;
use phpDocumentor\Reflection\PseudoTypes\True_;
use App\Helpers\DupCheck;
use App\Traits\TraitCMOValidate;

class CmoGrivanceWorkflowController1 extends Controller
{
    use TraitCMOValidate;
    public function __construct()
    {
        set_time_limit(120);
        $this->middleware('auth');
        $this->scheme_id = 20;
        date_default_timezone_set('Asia/Kolkata');
    }
    public function index()
    {
        $user_id = Auth::user()->id;
        $designation = Auth::user()->designation_id;
        $mapObj = DB::connection('pgsql_mis')
            ->table('public.duty_assignement')
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->first();
        if ($designation == 'Verifier' || $designation == 'Delegated Verifier') {
            if ($mapObj->is_urban == 1) {
                $urban_body_code = $mapObj->urban_body_code;
                $urban_bodys = UrbanBody::where(
                    'sub_district_code',
                    $urban_body_code
                )
                    ->select('urban_body_code', 'urban_body_name')
                    ->get();
                return view('cmo-grievance1/index', [
                    'mapLevel' => $mapObj->mapping_level . $designation,
                    'urban_bodys' => $urban_bodys,
                    'local_body_code' => $urban_body_code,
                    'district_code' => $mapObj->district_code,
                ]);
            } else {
                $taluka_code = $mapObj->taluka_code;
                $gps = GP::where('block_code', $taluka_code)
                    ->select('gram_panchyat_code', 'gram_panchyat_name')
                    ->get();
                return view('cmo-grievance1/index', [
                    'mapLevel' => $mapObj->mapping_level . $designation,
                    'gps' => $gps,
                    'local_body_code' => $taluka_code,
                    'district_code' => $mapObj->district_code,
                ]);
            }
        } else if ($designation == 'Approver' || $designation == 'Delegated Approver') {
            $district_code = $mapObj->district_code;
            return view('cmo-grievance1/index', [
                'mapLevel' => $mapObj->mapping_level,
                'district_code' => $district_code,
            ]);
        } else if ($designation == 'HOD') {
            return view('cmo-grievance1/index', [
                'mapLevel' => $mapObj->mapping_level,
            ]);
        } else {
            return redirect('/')->with('success', 'UnAuthorized');
        }
    }
    public function listing(Request $request)
    {
        // dd($request->all());
        if ($request->ajax()) {
            $user_id = Auth::user()->id;
            $designation = Auth::user()->designation_id;
            $scheme_id = $this->scheme_id;
            $mapObj = DB::connection('pgsql_mis')
                ->table('public.duty_assignement')
                ->where('user_id', $user_id)
                ->where('is_active', 1)
                ->first();
            if ($designation == 'Verifier' || $designation == 'Delegated Verifier') {
                $created_by_district_code = $mapObj->district_code;
                if ($mapObj->is_urban == 1) {
                    $mapLevel = 'SubdivVerifier';
                    $created_by_local_body_code = $mapObj->urban_body_code;
                } else {
                    $mapLevel = 'BlockVerifier';
                    $created_by_local_body_code = $mapObj->taluka_code;
                }
            } else if ($designation == 'Approver' || $designation == 'Delegated Approver') {
                $created_by_district_code = $mapObj->district_code;
                $mapLevel = 'DistrictApprover';
            } else if ($designation == 'HOD') {
                $mapLevel = 'Department';
            } else {
                return redirect('/')->with('success', 'UnAuthorized');
            }

            $process_type = $request->process_type;
            $whereCondition = ' 1=1 ';

            if ($mapLevel == 'BlockVerifier' || $mapLevel == 'SubdivVerifier') {
                $whereCondition = $whereCondition . " and lb_dist_code='" . $created_by_district_code . "'";
                $whereCondition = $whereCondition . " and lb_local_body_code='" . $created_by_local_body_code . "'";
                if ($process_type == 1) {
                    $whereCondition = $whereCondition . ' and is_processed = 0 and send_to_op = 0';
                } else if ($process_type == 2) {
                    $whereCondition = $whereCondition . ' and is_processed = 1';
                } else if ($process_type == 3) {
                    $whereCondition = $whereCondition . ' and is_processed = 2';
                } else if ($process_type == 4) {
                    $whereCondition = $whereCondition . ' and is_processed = 3';
                } else if ($process_type == 5) {
                    $whereCondition = $whereCondition . ' and send_to_op = 1 and is_processed = 0';
                }


                $query = "Select grievance_id,applicant_name,pri_cont_no,created_on,is_processed,is_redressed,is_mark,is_change_block,lb_dist_code,lb_local_body_code, send_to_op from cmo.cmo_sm_data  where  " . $whereCondition . "";
            }
            /*elseif ($mapLevel == 'SubdivVerifier') {

                $whereCondition = $whereCondition . " and lb_dist_code='" . $created_by_district_code . "'";
                $munlist = UrbanBody::where('sub_district_code', $created_by_local_body_code)->get()->toArray();
                $munlist_ids = array_column($munlist, 'urban_body_code');
                $munlist_ids_list = "'" . implode("', '", $munlist_ids) . "'";
                $whereCondition = $whereCondition . " and lb_local_body_code IN (" . $munlist_ids_list . ")";
                if ($process_type == 1) {
                    $whereCondition = $whereCondition . ' and is_processed = 0';
                } else if ($process_type == 2) {
                    $whereCondition = $whereCondition . ' and is_processed = 1';
                } else if ($process_type == 3) {
                    $whereCondition = $whereCondition . ' and is_processed = 2';
                } else if ($process_type == 4) {
                    $whereCondition = $whereCondition . ' and is_processed = 3';
                } else if ($process_type == 5) {
                    $whereCondition = $whereCondition . ' and send_to_op = 1 and is_processed = 0';
                }


                $query = "Select grievance_id,applicant_name,pri_cont_no,created_on,is_processed,is_redressed,is_mark,is_change_block,lb_dist_code,lb_local_body_code,
              lb_local_body_code from cmo.cmo_sm_data  where  " . $whereCondition . "";


            } */ elseif ($mapLevel == 'DistrictApprover') {

                $whereCondition = $whereCondition . " and lb_dist_code='" . $created_by_district_code . "'";
                if ($process_type == 1) {
                    $whereCondition = $whereCondition . ' and is_processed=1';
                } else if ($process_type == 3) {
                    $whereCondition = $whereCondition . ' and is_processed = 2';
                } else if ($process_type == 4) {
                    $whereCondition = $whereCondition . ' and is_processed = 3';
                } else if ($process_type == 5) {
                    $whereCondition = $whereCondition . ' and send_to_op = 1 and is_processed = 0';
                } else if ($process_type == 6) {
                    $whereCondition = $whereCondition . " and (lb_local_body_code is null OR TRIM(lb_local_body_code) = '')";
                }

                $query = "Select grievance_id,applicant_name,pri_cont_no,created_on,is_processed,is_redressed,is_mark,is_change_block,lb_dist_code,lb_local_body_code, send_to_op from cmo.cmo_sm_data  where  " . $whereCondition . "";
            } elseif ($mapLevel == 'Department') {

                if ($process_type == 7) {
                    $whereCondition = $whereCondition . ' and is_processed = 0 and lb_dist_code is null and lb_local_body_code is null';
                } else if ($process_type == 4) {
                    $whereCondition = $whereCondition . ' and is_processed = 3';
                }
                $query = "Select grievance_id,applicant_name,pri_cont_no,created_on,is_processed,is_redressed,is_mark,is_change_block,lb_dist_code,lb_local_body_code, send_to_op from cmo.cmo_sm_data  where  " . $whereCondition . "";
            } else {
                return redirect('/')->with('success', 'UnAuthorized');
            }

            //   dd($query);
            $data = DB::select($query);
            // dd($data);
            return datatables()
                ->of($data)
                ->addColumn('view', function ($data) use ($scheme_id, $mapLevel, $process_type) {
                    $action = '';
                    if ($mapLevel == 'BlockVerifier' || $mapLevel == 'SubdivVerifier') {
                        if ($process_type == 1) {
                            if ($data->is_processed == 0) {
                                $action = '<button value="' . $data->grievance_id . '_' . $scheme_id . '_' . $data->pri_cont_no . '" class="btn btn-xs btn-info find_applicant"><i class="glyphicon glyphicon-edit"></i>Find</button>';
                            } else {
                                $action = '';
                            }
                        } else if ($process_type == 2) {
                            if ($data->is_processed == 1) {
                                $action = 'Marked but Approval Pending';
                            } else
                                $action = '';
                        } else if ($process_type == 3) {
                            if ($data->is_processed == 2) {
                                $action = 'Marked and Approved but Yet not send to CMO';
                            } else
                                $action = '';
                        } else if ($process_type == 4) {
                            if ($data->is_processed == 3) {
                                $action = 'Marked and Approved and Send to CMO';
                            } else
                                $action = '';
                        } else if ($process_type == 5) {
                            if ($data->send_to_op == 1 && $data->is_processed == 0) {
                                $action = 'Sent to Operator for New Entry';
                            } else
                                $action = '';
                        }
                    }

                    if ($mapLevel == 'DistrictApprover') {
                        if ($process_type == 1) {
                            if ($data->is_processed == 1) {
                                $action = '<button value="' . $data->grievance_id . '_' . $scheme_id . '_' . $data->pri_cont_no . '" class="btn btn-xs btn-info grivance_tag_applicant"><i class="glyphicon glyphicon-edit"></i>Details</button>';
                            } else {
                                $action = '';
                            }
                        } else if ($process_type == 3) {
                            if ($data->is_processed == 2) {
                                $action = 'Marked and Approved but Yet not send to CMO';
                            } else
                                $action = '';
                        } else if ($process_type == 4) {
                            if ($data->is_processed == 3) {
                                $action = 'Marked and Approved and Send to CMO';
                            } else
                                $action = '';
                        } else if ($process_type == 5) {
                            if ($data->send_to_op == 1 && $data->is_processed == 0) {
                                $action = 'Sent to Operator for New Entry';
                            } else
                                $action = '';
                        } else if ($process_type == 6) {
                            if ($data->is_processed == 0) {
                                // $action = '<button value="' . $data->grievance_id . '_' . $scheme_id . '_' . $data->pri_cont_no . '" class="btn btn-xs btn-info find_applicant"><i class="glyphicon glyphicon-edit"></i>Find</button>';
                                $action = '<button value="' . $data->grievance_id . '_' . $scheme_id . '_' . $data->pri_cont_no . '_' . $data->lb_dist_code . '" class="btn btn-xs btn-info mapbos"><i class=""></i>Map Block/Sub-Division</button>';
                            } else
                                $action = '';
                        }
                    }
                    if ($mapLevel == 'Department') {
                        if ($process_type == 7) {
                            if ($data->is_processed == 0) {
                                $action = '<button value="' . $data->grievance_id . '_' . $scheme_id . '_' . $data->pri_cont_no . '" class="btn btn-xs btn-info find_applicant"><i class="glyphicon glyphicon-edit"></i>Find</button>';
                            } else {
                                $action = '';
                            }
                        } else if ($process_type == 4) {
                            if ($data->is_processed == 3) {
                                $action = 'Marked and Approved and Send to CMO';
                            } else
                                $action = '';
                        }
                    }


                    return $action;
                })
                ->addColumn('grievance_id', function ($data) {
                    return $data->grievance_id;
                })
                ->addColumn('grievance_name', function ($data) {
                    return $data->applicant_name;
                })
                ->addColumn('sm_mobile_no', function ($data) {
                    return $data->pri_cont_no;
                })
                ->addColumn('cmo_receive_date', function ($data) {
                    list($date) = explode("T", $data->created_on);
                    return $date;
                })
                // ->addColumn('gp_ward_name', function ($data) {
                //     return $data->gram_panchyat_name;
                // })
                // ->addColumn('description', function ($data) {
                //     return $data->complain_description;
                // })
                ->rawColumns(['view', 'grievance_id', 'grievance_name', 'sm_mobile_no', 'cmo_receive_date'])
                ->make(true);
        }
    }
    public function find(Request $request)
    {
        //   dd($request->all());
        $user_id = Auth::user()->id;
        $designation = Auth::user()->designation_id;
        $grievance_id = $request->grievance_id;
        $scheme_id = $request->scheme_id;
        $grievance_mobile_no = $request->grievance_mobile_no;
        $districtList = District::get();
        $mapObj = DB::connection('pgsql_mis')->table('public.duty_assignement')->where('user_id', $user_id)->where('is_active', 1)->first();
        $row = DB::connection('pgsql_mis')->table('cmo.cmo_sm_data')->where('grievance_id', $grievance_id)->where('pri_cont_no', $grievance_mobile_no)->first();
        $gp_name = DB::connection('pgsql_mis')->table('public.m_gp')->where('gram_panchyat_code', $row->lb_gp_ward_code)->first();
        $atr = DB::connection('pgsql_mis')->select(
            'select  atn_id,atr_desc from cmo.m_cmo_atr order by atn_id,atr_desc'
        );
        //    dd($row->atr_type);
        // $scheme = DB::connection('pgsql_mis')->select('select id,scheme_name from public.m_scheme where id in (select scheme_id from public.duty_assignement where user_id=' . $user_id . ' and is_active=1) order by scheme_name');
        if ($designation == 'Verifier' || $designation == 'Delegated Verifier' || $designation == 'Approver' || $designation == 'Delegated Approver' || $designation == 'HOD') {
            return view('cmo-grievance1/find_applicant', ['scheme_id' => $scheme_id, 'grievance_id' => $grievance_id, 'grievance_mobile_no' => $grievance_mobile_no, 'row' => $row, 'atr' => $atr, 'districtList' => $districtList]);
        } else {
            return redirect("/")->with('success', 'User disabled. No scheme assign to this user');
        }
    }
    public function redress(Request $request)
    {
        $response = [];
        $statusCode = 200;
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = ['error' => 'Error occured in form submit.'];
            return response()->json($response, $statusCode);
        }
        try {
            $rules = [
                'atr_type' => 'required',
                'remarks' => ['nullable', 'regex:/^[a-zA-Z0-9 ]*$/'],
            ];
            $attributes = [
                'atr_type' => 'ATR Type',
                'remarks' => 'Remarks',
            ];
            $messages = [
                'required' => 'The :attribute field is required.',
            ];
            $validator = Validator::make(
                $request->all(),
                $rules,
                $messages,
                $attributes
            );
            if ($validator->passes()) {
                $user_id = Auth::user()->id;
                $scheme_id = $request->scheme_id;
                $grievance_mobile_no = $request->grievance_mobile_no;
                $grievance_id = $request->grievance_id;
                $atr_type = $request->atr_type;
                $remarks = $request->remarks;
                $atr = DB::connection('pgsql_mis')->select(
                    "select  atn_id,atr_desc from cmo.m_cmo_atr where atn_id = '" . $atr_type . "'"
                );
                DB::beginTransaction();
                $updateDetails = [];
                $updateDetails['is_redressed'] = 1;
                $updateDetails['is_processed'] = 1;
                $updateDetails['atr_type'] = $atr_type;
                $updateDetails['atr_desc'] = trim($atr[0]->atr_desc);
                $updateDetails['redressed_by'] = $user_id;
                $updateDetails['redressed_date'] = date('Y-m-d H:i:s');
                $updateDetails['remarks'] = $remarks;
                // dd($updateDetails);
                $is_update = DB::table('cmo.cmo_sm_data')
                    ->where('grievance_id', $grievance_id)
                    ->where('pri_cont_no', $grievance_mobile_no)
                    ->where('is_processed', 0)
                    ->where('is_redressed', 0)
                    ->update($updateDetails);
                if ($is_update) {
                    DB::commit();
                    $response = [
                        'status' => 1,
                        'msg' => 'Grievance redress Successfully',
                        'type' => 'green',
                        'icon' => 'fa fa-check',
                        'title' => 'Success',
                    ];
                } else {
                    DB::rollback();
                    $response = [
                        'status' => 3,
                        'msg' => '3 Somethimg went wrong!!',
                        'type' => 'red',
                        'icon' => 'fa fa-warning',
                        'title' => 'Warning!!',
                    ];
                }
            } else {
                $return_status = 0;
                $return_msg = $validator->errors()->all();
                $response = [
                    'status' => $return_status,
                    'msg' => $return_msg,
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Warning!!',
                ];
            }
        } catch (\Exception $e) {
            //  dd($e);
            DB::rollback();
            $response = [
                'exception' => true,
                // 'exception_message' => $e->getMessage(),
                'exception_message' =>
                'Something went wrong. May be session time out logout and login again.',
            ];
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
    public function benlisting(Request $request)
    {
        // dd($request->all());
        if ($request->ajax()) {
            $user_id = Auth::user()->id;
            $designation = Auth::user()->designation_id;
            $scheme_id = $request->scheme_id;
            $grivence_mobile = $request->grivence_mobile;
            $grievance_id = $request->grievance_id;
            $new_process_id = $request->new_process_id;
            $input_value = $request->input_value;
            $mapObj = DB::connection('pgsql_mis')
                ->table('public.duty_assignement')
                ->where('user_id', $user_id)
                ->where('is_active', 1)
                ->first();
            if ($designation == 'Verifier' || $designation == 'Delegated Verifier') {
                if ($mapObj->is_urban == 1) {
                    $local_body_code = $mapObj->urban_body_code;
                } else {
                    $local_body_code = $mapObj->taluka_code;
                }
            } else if ($designation == 'Approver' || $designation == 'Delegated Approver') {
                $district_code = $mapObj->district_code;
            }
            if ($new_process_id == 5) {
                $input_value = strtolower(str_replace(' ', '', $input_value));
            }
            if ($new_process_id == 1) {
                $input_con1 = " where bp.application_id=" . $input_value . "";
                $input_con2 = " where fp.application_id=" . $input_value . "";
                $input_con3 = " where dp.application_id=" . $input_value . "";
                // $input_con4 = " where brd.application_id=".$input_value."" ;
            } else if ($new_process_id == 2) {
                $input_con1 = " where bp.mobile_no='" . $input_value . "'";
                $input_con2 = " where fp.mobile_no='" . $input_value . "'";
                $input_con3 = " where dp.mobile_no='" . $input_value . "'";
                // $input_con4= " where brd.mobile_no='".$input_value."'" ;
            } else if ($new_process_id == 3) {
                $aadhar_hash = md5($input_value);
                // dd($aadhar_hash);
                $input_con1 = " where ba.aadhar_hash='" . $aadhar_hash . "'";
                $input_con2 = " where fba.aadhar_hash='" . $aadhar_hash . "'";
                $input_con3 = " where dba.aadhar_hash='" . $aadhar_hash . "'";
                // $input_con4 = " where brd.aadhar_no='".$input_value."'" ;
            } else if ($new_process_id == 4) {
                $input_con1 = " where bb.bank_code='" . $input_value . "'";
                $input_con2 = " where bfb.bank_code='" . $input_value . "'";
                $input_con3 = " where bdb.bank_code='" . $input_value . "'";
                // $input_con4 = " where brd.bank_code='".$input_value."'" ;
            } else if ($new_process_id == 5) {
                $input_con1 = " where LOWER(REPLACE(CONCAT(TRIM(COALESCE(bp.ben_fname, '')), TRIM(COALESCE(bp.ben_mname, '')), TRIM(COALESCE(bp.ben_lname, ''))), ' ', ''))='" . $input_value . "'";
                $input_con2 = " where LOWER(REPLACE(CONCAT(TRIM(COALESCE(fp.ben_fname, '')), TRIM(COALESCE(fp.ben_mname, '')), TRIM(COALESCE(fp.ben_lname, ''))), ' ', ''))='" . $input_value . "'";
                $input_con3 = " where LOWER(REPLACE(CONCAT(TRIM(COALESCE(dp.ben_fname, '')), TRIM(COALESCE(dp.ben_mname, '')), TRIM(COALESCE(dp.ben_lname, ''))), ' ', ''))='" . $input_value . "'";
                // $input_con4 = " where LOWER(REPLACE(CONCAT(TRIM(COALESCE(brd.ben_fname, '')), TRIM(COALESCE(brd.ben_mname, '')), TRIM(COALESCE(brd.ben_lname, ''))), ' ', ''))='".$input_value."'" ;
            }
            if ($designation == 'Verifier' || $designation == 'Delegated Verifier') {
                $condition1 = " and bp.created_by_local_body_code=" . $local_body_code . "";
                $condition2 = " and fp.created_by_local_body_code=" . $local_body_code . "";
                $condition3 = " and dp.created_by_local_body_code=" . $local_body_code . "";
                // $condition4 = " and brd.created_by_local_body_code=".$local_body_code."" ;
            }
            if ($designation == 'Approver' || $designation == 'Delegated Approver') {
                $condition1 = " and bp.created_by_dist_code=" . $local_body_code . "";
                $condition2 = " and fp.created_by_dist_code=" . $local_body_code . "";
                $condition3 = " and dp.created_by_dist_code=" . $local_body_code . "";
                //  $condition4 = " and brd.created_by_dist_code=".$local_body_code."" ;
            }

            //   dd($input_value);
            // $table_name = $this->getSchemaName($scheme_id);
            // $query =  "Select b.*,md.district_name, bl_div.block_subdiv_name,ms.scheme_name from $table_name b join public.m_district md ON md.district_code=b.created_by_dist_code 
            // JOIN public.m_scheme ms ON ms.id=b.scheme_id 
            // JOIN (SELECT block_code AS block_subdiv_code,block_name AS block_subdiv_name FROM public.m_block 
            //     UNION ALL
            // SELECT sub_district_code AS block_subdiv_code, sub_district_name AS block_subdiv_name FROM 	public.m_sub_district
            // ) bl_div ON bl_div.block_subdiv_code=b.created_by_local_body_code 
            // where b.scheme_id=".$scheme_id."";
            $query = "SELECT bp.application_id,bp.ben_fname,bp.ben_mname,bp.ben_lname,bp.father_fname,bp.father_mname,bp.father_lname,bp.next_level_role_id,bp.mobile_no,bc.rural_urban_id, bc.gp_ward_name,md.district_name, bl_div.block_subdiv_name,bp.sm_flag, cmo_mark, bc.block_ulb_name FROM lb_scheme.ben_personal_details bp
                JOIN lb_scheme.ben_contact_details bc ON bp.application_id = bc.application_id
                JOIN lb_scheme.ben_aadhar_details ba ON bp.application_id = ba.application_id
                JOIN lb_scheme.ben_bank_details bb ON bb.application_id = bp.application_id
                LEFT JOIN public.m_district md ON md.district_code = bp.created_by_dist_code
                LEFT JOIN 
                (
                    SELECT block_code AS block_subdiv_code, block_name AS block_subdiv_name FROM public.m_block
                    UNION ALL
                    SELECT sub_district_code AS block_subdiv_code, sub_district_name AS block_subdiv_name FROM public.m_sub_district
                ) bl_div ON bl_div.block_subdiv_code = bp.created_by_local_body_code $input_con1 $condition1
                UNION ALL
                SELECT fp.application_id,fp.ben_fname,fp.ben_mname,fp.ben_lname,fp.father_fname,fp.father_mname,fp.father_lname,fp.next_level_role_id,fp.mobile_no,bfc.rural_urban_id,bfc.gp_ward_name, mfd.district_name, bfl_div.block_subdiv_name,fp.sm_flag, cmo_mark, bfc.block_ulb_name FROM lb_scheme.faulty_ben_personal_details fp
                JOIN lb_scheme.faulty_ben_contact_details bfc ON fp.application_id = bfc.application_id
                JOIN lb_scheme.ben_aadhar_details fba ON fba.application_id = fp.application_id
                JOIN lb_scheme.faulty_ben_bank_details bfb ON bfb.application_id = fp.application_id
                LEFT JOIN public.m_district mfd ON mfd.district_code = fp.created_by_dist_code
                LEFT JOIN 
                (
                    SELECT block_code AS block_subdiv_code, block_name AS block_subdiv_name FROM public.m_block
                    UNION ALL
                    SELECT sub_district_code AS block_subdiv_code, sub_district_name AS block_subdiv_name FROM public.m_sub_district
                ) bfl_div ON bfl_div.block_subdiv_code = fp.created_by_local_body_code $input_con2 $condition2
                UNION ALL
                SELECT dp.application_id,dp.ben_fname,dp.ben_mname,dp.ben_lname,dp.father_fname,dp.father_mname,dp.father_lname,dp.next_level_role_id,dp.mobile_no,bdc.rural_urban_id, bdc.gp_ward_name, mdd.district_name, bdl_div.block_subdiv_name,dp.sm_flag, cmo_mark, bdc.block_ulb_name FROM lb_scheme.draft_ben_personal_details dp
                JOIN lb_scheme.draft_ben_contact_details bdc ON dp.application_id = bdc.application_id
                JOIN lb_scheme.ben_aadhar_details dba ON dba.application_id = dp.application_id
                JOIN lb_scheme.draft_ben_bank_details bdb ON bdb.application_id = dp.application_id
                LEFT JOIN public.m_district mdd ON mdd.district_code = dp.created_by_dist_code
                LEFT JOIN 
                (
                    SELECT block_code AS block_subdiv_code, block_name AS block_subdiv_name FROM public.m_block
                    UNION ALL
                    SELECT sub_district_code AS block_subdiv_code, sub_district_name AS block_subdiv_name FROM public.m_sub_district
                ) bdl_div ON bdl_div.block_subdiv_code = dp.created_by_local_body_code $input_con3 $condition3;
               
                ";
            //   UNION ALL
            //  SELECT dp.application_id,dp.ben_fname,dp.ben_mname,dp.ben_lname, mdd.district_name, bdl_div.block_subdiv_name FROM lb_scheme.ben_reject_details brd
            //   JOIN public.m_district mrd ON mrd.district_code = brd.created_by_dist_code
            //   JOIN 
            //   (
            //      SELECT block_code AS block_subdiv_code, block_name AS block_subdiv_name FROM public.m_block
            //       UNION ALL
            //      SELECT sub_district_code AS block_subdiv_code, sub_district_name AS block_subdiv_name FROM public.m_sub_district
            //   ) brl_div ON brl_div.block_subdiv_code = brd.created_by_local_body_code $input_con4 $condition4;
            // dd($query);
            $data = DB::select($query);
            return datatables()
                ->of($data)
                ->addColumn('view', function ($data) use ($grievance_id) {
                    // if ($data->cmo_mark == 1) {
                    //     $action = '<b>Already Marked</b>';
                    // } else {
                        $action = '<button value="' . $data->application_id . '_' . $data->mobile_no . '_' . $grievance_id . '" class="btn btn-xs btn-info process_applicant"><i class="glyphicon glyphicon-edit"></i> Process</button>';
                    // }
                    return $action;
                })
                ->addColumn('application_id', function ($data) {
                    return $data->application_id;
                })
                ->addColumn('name', function ($data) {
                    return $data->ben_fname . ' ' . $data->ben_mname . ' ' . $data->ben_lname;
                })
                ->addColumn('father_name', function ($data) {
                    return $data->father_fname . ' ' . $data->father_mname . ' ' . $data->father_lname;
                })
                ->addColumn('address', function ($data) {
                    $address = '';
                    $address = 'District - ' . $data->district_name . '<br>';
                    if ($data->rural_urban_id == 1) {
                        $address .= 'Sub-division - ' . $data->block_subdiv_name . '<br>';
                        $address .= 'Municipality - ' . $data->block_ulb_name . '<br>';
                        $address .= 'Ward - ' . $data->gp_ward_name;
                    } else {
                        $address .= 'Block - ' . $data->block_subdiv_name . '<br>';
                        $address .= 'GP - ' . $data->gp_ward_name;
                    }
                    return $address;
                })
                // ->addColumn('bank_info', function ($data) {
                //     $bank = '';
                //     if (!is_null($data->bank_name)) {
                //       $bank .= 'Bank Name - ' . $data->bank_name . '<br>';
                //     }
                //     if (!is_null($data->branch_name)) {
                //       $bank .= 'Branch - ' . $data->branch_name . '<br>';
                //     }
                //     $bank .= 'A/c No - ' . $data->bank_code . '<br>';
                //     $bank .= 'IFSC - ' . $data->bank_ifsc;
                //     return $bank;
                // })
                ->addColumn('mobile_no', function ($data) {
                    return $data->mobile_no;
                })
                ->addColumn('status', function ($data) {
                    if ($data->next_level_role_id == 0) {
                        $action = '<b>Approved</b>';
                    }
                    if ($data->next_level_role_id > 0) {
                        $action = '<b>Verified but Approval Pending</b>';
                    }
                    if ($data->next_level_role_id < 0) {
                        $action = '<b>Rejected</b>';
                    }
                    if ($data->next_level_role_id == NULL) {
                        $action = '<b>Non Verified</b>';
                    }
                    return $action;
                })
                ->rawColumns(['view', 'id', 'scheme_name', 'name', 'father_name', 'mobile_no', 'address', 'status'])
                ->make(true);
        }
    }
    public function processPost(Request $request)
    {
        //    dd($request->all());
        $response = [];
        $statusCode = 200;
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = ['error' => 'Error occured in form submit.'];
            return response()->json($response, $statusCode);
        }
        try {
            $rules = [
                'atr_type' => 'required',
                'remarks' => ['nullable', 'regex:/^[a-zA-Z0-9 ]*$/'],
            ];
            $attributes = [
                'atr_type' => 'ATR Type',
                'remarks' => 'Remarks',
            ];
            $messages = [
                'required' => 'The :attribute field is required.',
            ];
            $validator = Validator::make(
                $request->all(),
                $rules,
                $messages,
                $attributes
            );
            if ($validator->passes()) {
                $user_id = Auth::user()->id;
                $designation = Auth::user()->designation_id;
                $application_id = $request->application_id;
                $grievance_id = $request->grievance_id;
                $grievance_mobile_no = $request->grievance_mobile_no;
                $atr_type = $request->atr_type;
                $remarks = $request->remarks;
                $c_time = date('Y-m-d H:i:s', time());
                $ben_personal_details = DB::table('lb_scheme.ben_personal_details')->where('application_id', $application_id)->first();
                $ben_faulty_details = DB::table('lb_scheme.faulty_ben_personal_details')->where('application_id', $application_id)->first();
                $ben_draft_details = DB::table('lb_scheme.draft_ben_personal_details')->where('application_id', $application_id)->first();
                $ben_reject_details = DB::table('lb_scheme.ben_reject_details')->where('application_id', $application_id)->first();
                $benDetails = [];
                $benDetails['cmo_mark'] = 1;
                if ($ben_personal_details) {
                    $table = 'lb_scheme.ben_personal_details';
                    $next_level_role_id = $ben_personal_details->next_level_role_id;
                    $table_source = 1;
                } else if ($ben_faulty_details) {
                    $table = 'lb_scheme.faulty_ben_personal_details';
                    $next_level_role_id = $ben_faulty_details->next_level_role_id;
                    $table_source = 2;
                } else if ($ben_draft_details) {
                    $table = 'lb_scheme.draft_ben_personal_details';
                    $next_level_role_id = $ben_draft_details->next_level_role_id;
                    $table_source = 3;
                } else if ($ben_reject_details) {
                    $table = 'lb_scheme.draft_ben_personal_details';
                    $next_level_role_id = $ben_draft_details->next_level_role_id;
                    $table_source = 4;
                }
                $atr = DB::connection('pgsql_mis')->select(
                    "select  atn_id,atr_desc from cmo.m_cmo_atr where atn_id = '" . $atr_type . "'"
                );
                // dd($table);

                // $benDetails['next_level_role_id'] = 43;
                // if($designation == 'Verifier'){
                //     if($ben_details->next_level_role_id == NULL){
                //         $benDetails['next_level_role_id'] = $next_level_role_id;
                //         $benDetails['is_verified'] = 1;
                //         $benDetails['verified_by'] = $user_id;
                //         $benDetails['verification_date'] = $c_time;
                //     }
                // }
                DB::beginTransaction();
                $updateDetails = [];
                $benAcceptRejectInfo = [];

                $updateDetails['lb_application_id'] = $application_id;
                $updateDetails['atr_type'] = $atr_type;
                $updateDetails['atr_desc'] = trim($atr[0]->atr_desc);
                $updateDetails['remarks'] = $remarks;
                $updateDetails['is_processed'] = 1;
                $updateDetails['is_mark'] = 1;
                $updateDetails['marked_by'] = $user_id;
                $updateDetails['marked_date'] = date('Y-m-d H:i:s');
                $updateDetails['lb_next_level_role_id'] = $next_level_role_id;
                $updateDetails['table_source'] = $table_source;

                $benAcceptRejectInfo['application_id'] = $application_id;
                $benAcceptRejectInfo['created_by'] = $user_id;
                $benAcceptRejectInfo['user_id'] = $user_id;
                $benAcceptRejectInfo['created_at'] = date('Y-m-d H:i:s');
                $benAcceptRejectInfo['op_type'] = 'CMO-Marking';
                $benAcceptRejectInfo['designation_id'] = $designation;
                $benAcceptRejectInfo['ip_address'] = $request->ip();

                $is_insert = DB::table('lb_scheme.ben_accept_reject_info')->insert($benAcceptRejectInfo);
                $is_update = DB::table('cmo.cmo_sm_data')
                    ->where('grievance_id', $grievance_id)
                    // ->where('scheme_id', $scheme_id)
                    // ->where('pri_cont_no', $grievance_mobile_no)
                    ->where('is_processed', 0) //Temporary Code
                    ->where('is_redressed', 0)
                    ->update($updateDetails);
                $ben_update = DB::table($table)
                    ->where('application_id', $application_id)
                    ->update($benDetails);
                if ($is_update == 1 && $ben_update == 1) {
                    DB::commit();
                    $response = [
                        'status' => 1,
                        'msg' => 'Beneficiary Marked Successfully',
                        'type' => 'green',
                        'icon' => 'fa fa-check',
                        'title' => 'Success',
                    ];
                } else {
                    DB::rollback();
                    $response = [
                        'status' => 3,
                        'msg' => '3 Somethimg went wrong!!',
                        'type' => 'red',
                        'icon' => 'fa fa-warning',
                        'title' => 'Warning!!',
                    ];
                }
            } else {
                $return_status = 0;
                $return_msg = $validator->errors()->all();
                $response = [
                    'status' => $return_status,
                    'msg' => $return_msg,
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Warning!!',
                ];
            }
        } catch (\Exception $e) {
            // dd($e);
            DB::rollback();
            $response = [
                'exception' => true,
                // 'exception_message' => $e->getMessage(),
                'exception_message' =>
                'Something went wrong. May be session time out logout and login again.',
            ];
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
    public function transfar(Request $request)
    {
        // dd($request->all());
        $response = [];
        $statusCode = 200;
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = ['error' => 'Error occured in form submit.'];
            return response()->json($response, $statusCode);
        }
        try {
            $rules = [
                'atr_type' => 'required',
                'remarks' => ['nullable', 'regex:/^[a-zA-Z0-9 ]*$/'],
                'district' => 'required',
                'rural_urban' => 'required',
                'block' => 'required',
            ];
            $attributes = [
                'atr_type' => 'ATR Type',
                'remarks' => 'Remarks',
                'district' => 'District',
                'rural_urban' => 'Rural/Urban',
                'block' => 'Block',
            ];
            $messages = [
                'required' => 'The :attribute field is required.',
            ];
            $validator = Validator::make(
                $request->all(),
                $rules,
                $messages,
                $attributes
            );
            if ($validator->passes()) {
                $user_id = Auth::user()->id;
                $grievance_id = $request->grievance_id;
                $grievance_mobile_no = $request->grievance_mobile_no;
                $atr_type = $request->atr_type;
                $remarks = $request->remarks;
                $district = $request->district;
                $rural_urban = $request->rural_urban;
                $block = $request->block;
                $atr = DB::connection('pgsql_mis')->select(
                    "select  atn_id,atr_desc from cmo.m_cmo_atr where atn_id = '" . $atr_type . "'"
                );
                // $table = $this->getSchemaName($scheme_id);
                DB::beginTransaction();
                $is_insert = DB::statement("INSERT INTO cmo.cmo_sm_data_archive(grievance_id, 
             grievance_no,grievance_source,receipt_mode,received_at,reference_no,applicant_name,pri_cont_no,alt_cont_no,cont_email,applicant_gender,applicant_age,applicant_caste,applicant_reigion,applicant_address,state_id,district_id,block_id,municipality_id,gp_id,ward_id,police_station_id,assembly_const_id,postoffice_id,employment_type,employment_status,grievance_category,grievance_description,action_requested,usb_unique_id,parent_grievance_id,status,atr_recv_cmo_flag,emergency_flag,created_by,updated_by,sub_division_id,uploaded_doc_id,created_by_position,updated_by_position,assigned_to_id,assigned_to_position,educational_qualification_id,professional_qualification_id,skill_id,address_type,action_taken_note,atn_id,force_closure_2020,closure_reason_id,deo_phone_no,assigned_by_office_id,assigned_to_office_id,assigned_by_office_cat,assigned_to_office_cat,atr_submit_by_lastest_office_id,direct_close,lb_next_level_role_id,marked_date,marked_by,lb_name,lb_id,scheme_id,is_processed,level_type,remarks,redressed_by,redressed_date,is_redressed,lb_rural_urban_id,is_change_block,change_block_by,change_block_date,response_back_date,response_back_by,api_fetching_date,atr_recv_cmo_date,grievence_close_date,created_on,updated_on,grievance_generate_date,current_atr_date,lb_dist_code,lb_local_body_code,atr_type,atr_desc
            ) (SELECT grievance_id, 
             grievance_no,grievance_source,receipt_mode,received_at,reference_no,applicant_name,pri_cont_no,alt_cont_no,cont_email,applicant_gender,applicant_age, applicant_caste, applicant_reigion,applicant_address,state_id,district_id,block_id,municipality_id,gp_id,ward_id,police_station_id,assembly_const_id,postoffice_id,employment_type,employment_status,grievance_category,grievance_description,action_requested,usb_unique_id,parent_grievance_id,status,atr_recv_cmo_flag,emergency_flag,created_by,updated_by,sub_division_id,uploaded_doc_id,created_by_position,updated_by_position,assigned_to_id,assigned_to_position,educational_qualification_id,professional_qualification_id,skill_id,address_type,action_taken_note,atn_id,force_closure_2020,closure_reason_id,deo_phone_no,assigned_by_office_id,assigned_to_office_id,assigned_by_office_cat,assigned_to_office_cat,atr_submit_by_lastest_office_id,direct_close,lb_next_level_role_id,marked_date,marked_by,lb_name,lb_id,scheme_id,is_processed,level_type,remarks,redressed_by,redressed_date,is_redressed,lb_rural_urban_id,is_change_block,change_block_by,change_block_date,response_back_date,response_back_by,api_fetching_date,atr_recv_cmo_date,grievence_close_date,created_on,updated_on,grievance_generate_date,current_atr_date,lb_dist_code,lb_local_body_code,atr_type,atr_desc
              from cmo.cmo_sm_data where grievance_id='" . $grievance_id . "')");
                $updateDetails = [];
                $updateDetails['atr_type'] = $atr_type;
                $updateDetails['atr_desc'] = trim($atr[0]->atr_desc);
                $updateDetails['remarks'] = $remarks;
                $updateDetails['lb_dist_code'] = $district;
                /*if ($rural_urban == 1) {
                    $updateDetails['lb_local_body_code'] = $block;
                    $updateDetails['lb_local_body_code'] = null;
                } else {
                    $updateDetails['lb_local_body_code'] = $block;
                    $updateDetails['lb_local_body_code'] = null;
                }*/
                $updateDetails['lb_local_body_code'] = $block;
                $updateDetails['is_change_block'] = 1;
                $updateDetails['change_block_by'] = $user_id;
                $updateDetails['change_block_date'] = date('Y-m-d H:i:s');
                $is_update = DB::table('cmo.cmo_sm_data')
                    ->where('grievance_id', $grievance_id)
                    // ->where('scheme_id', $scheme_id)
                    ->where('pri_cont_no', $grievance_mobile_no)
                    ->where('is_processed', 0) //Temporary Code
                    ->update($updateDetails);
                if ($is_update && $is_insert) {
                    DB::commit();
                    $response = [
                        'status' => 1,
                        'msg' => 'Block Transfer Successfully',
                        'type' => 'green',
                        'icon' => 'fa fa-check',
                        'title' => 'Success',
                    ];
                } else {
                    DB::rollback();
                    $response = [
                        'status' => 3,
                        'msg' => '3 Somethimg went wrong!!',
                        'type' => 'red',
                        'icon' => 'fa fa-warning',
                        'title' => 'Warning!!',
                    ];
                }
            } else {
                $return_status = 0;
                $return_msg = $validator->errors()->all();
                $response = [
                    'status' => $return_status,
                    'msg' => $return_msg,
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Warning!!',
                ];
            }
        } catch (\Exception $e) {
            if ($grievance_id == 5201149) {
                dd($e);
            }
            //  dd($e);
            DB::rollback();
            $response = [
                'exception' => true,
                // 'exception_message' => $e->getMessage(),
                'exception_message' =>
                'Something went wrong. May be session time out logout and login again.',
            ];
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
    public function hodIndex(Request $request)
    {

        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $duty = Configduty::where('user_id', '=', $user_id)->first();



        $districts = District::get();
        return view(
            'cmo-grievance1/hod_linelisting',
            [
                'districts' => $districts
            ]
        );
    }
    private function getBenDetails()
    {
        $table = "
        SELECT bp.application_id,bp.ben_fname,bp.ben_mname,bp.ben_lname,bp.father_fname,bp.father_mname,bp.father_lname,bp.next_level_role_id,bp.mobile_no,bp.sm_flag,bp.caste,bc.rural_urban_id, bc.gp_ward_name,md.district_name, bl_div.block_subdiv_name, gp_div.gp_name,bp.cmo_mark FROM lb_scheme.ben_personal_details bp
        JOIN lb_scheme.ben_contact_details bc ON bp.application_id = bc.application_id
        JOIN lb_scheme.ben_aadhar_details ba ON bp.application_id = ba.application_id
        JOIN lb_scheme.ben_bank_details bb ON bp.application_id = bb.application_id
        LEFT JOIN public.m_district md ON md.district_code = bp.created_by_dist_code
        LEFT JOIN (
            SELECT block_code AS block_subdiv_code, block_name AS block_subdiv_name FROM public.m_block
            UNION ALL
            SELECT sub_district_code AS block_subdiv_code, sub_district_name AS block_subdiv_name FROM public.m_sub_district
        ) bl_div ON bl_div.block_subdiv_code = bp.created_by_local_body_code
        LEFT JOIN (
            SELECT gram_panchyat_code AS gp_code, gram_panchyat_name AS gp_name FROM public.m_gp
            UNION ALL
            SELECT urban_body_code AS gp_code, urban_body_name AS gp_name FROM public.m_urban_body
        ) gp_div ON gp_div.gp_code = bc.gp_ward_code
        UNION ALL
        SELECT fp.application_id,fp.ben_fname,fp.ben_mname,fp.ben_lname,fp.father_fname,fp.father_mname,fp.father_lname,fp.next_level_role_id,fp.mobile_no,fp.sm_flag,fp.caste,bfc.rural_urban_id, bfc.gp_ward_name, mfd.district_name, bfl_div.block_subdiv_name, gp_div.gp_name, fp.cmo_mark FROM lb_scheme.faulty_ben_personal_details fp
        JOIN lb_scheme.faulty_ben_contact_details bfc ON fp.application_id = bfc.application_id
        JOIN lb_scheme.ben_aadhar_details fba ON fba.application_id = fp.application_id
        JOIN lb_scheme.faulty_ben_bank_details bfb ON bfb.application_id = fp.application_id
        LEFT JOIN public.m_district mfd ON mfd.district_code = fp.created_by_dist_code
        LEFT JOIN (
            SELECT block_code AS block_subdiv_code, block_name AS block_subdiv_name FROM public.m_block
            UNION ALL
            SELECT sub_district_code AS block_subdiv_code, sub_district_name AS block_subdiv_name FROM public.m_sub_district
        ) bfl_div ON bfl_div.block_subdiv_code = fp.created_by_local_body_code
        LEFT JOIN (
            SELECT gram_panchyat_code AS gp_code, gram_panchyat_name AS gp_name FROM public.m_gp
            UNION ALL
            SELECT urban_body_code AS gp_code, urban_body_name AS gp_name FROM public.m_urban_body
        ) gp_div ON gp_div.gp_code = bfc.gp_ward_code
        UNION ALL
        SELECT dp.application_id,dp.ben_fname,dp.ben_mname,dp.ben_lname,dp.father_fname,dp.father_mname,dp.father_lname,dp.next_level_role_id,dp.mobile_no, dp.sm_flag, dp.caste, bdc.rural_urban_id, bdc.gp_ward_name, mdd.district_name, bdl_div.block_subdiv_name, gp_div.gp_name ,dp.cmo_mark FROM lb_scheme.draft_ben_personal_details dp
        JOIN lb_scheme.draft_ben_contact_details bdc ON dp.application_id = bdc.application_id
        JOIN lb_scheme.ben_aadhar_details dba ON dba.application_id = dp.application_id
        JOIN lb_scheme.draft_ben_bank_details bdb ON bdb.application_id = dp.application_id
        LEFT JOIN public.m_district mdd ON mdd.district_code = dp.created_by_dist_code
        LEFT JOIN 
        (
            SELECT block_code AS block_subdiv_code, block_name AS block_subdiv_name FROM public.m_block
            UNION ALL
            SELECT sub_district_code AS block_subdiv_code, sub_district_name AS block_subdiv_name FROM public.m_sub_district
        ) bdl_div ON bdl_div.block_subdiv_code = dp.created_by_local_body_code
        LEFT JOIN (
            SELECT gram_panchyat_code AS gp_code, gram_panchyat_name AS gp_name FROM public.m_gp
            UNION ALL
            SELECT urban_body_code AS gp_code, urban_body_name AS gp_name FROM public.m_urban_body
        ) gp_div ON gp_div.gp_code = bdc.gp_ward_code";
        return $table;
    }
    public function hodList(Request $request)
    {
        // dd($request->all());
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        $district = $request->district;
        $operation_type = $request->process_type;

        // $table = $this->getBenDetails();
        if ($request->ajax()) {
            if ($designation_id == 'HOD') {
                $whereCondition = " is_processed=" . $operation_type;
                if (!empty($district)) {
                    $whereCondition = $whereCondition . " and lb_dist_code='" . $district . "'";
                }
                $query = '';

                $query .= "SELECT grievance_id,applicant_name,pri_cont_no,created_on,is_processed,is_redressed,is_mark,is_change_block,lb_dist_code,lb_local_body_code FROM cmo.cmo_sm_data   where  " . $whereCondition . "";


                $data = DB::connection('pgsql')->select($query);
                // $districts = District::where('district_code',$district)->first();
                return datatables()->of($data)
                    ->addIndexColumn()
                    // ->addColumn('name', function ($data) {
                    //     return $data->ben_fname . ' ' . $data->ben_mname . ' ' . $data->ben_lname;
                    // })
                    // ->addColumn('address', function ($data) {
                    //     $address = '';
                    //     if(!empty($district)){
                    //         $address = 'District - ' . $data->district_name. '<br>';
                    //     }
                    //     if ($data->rural_urban_id == 1) {
                    //         $address .= 'Municipality - ' . $data->block_subdiv_name . '<br>';
                    //         $address .= 'Ward - ' . $data->gp_ward_name;
                    //     } else {
                    //         $address .= 'Block - ' . $data->block_subdiv_name . '<br>';
                    //         $address .= 'GP - ' . $data->gp_ward_name;
                    //     }
                    //     return $address;
                    // })
                    // ->addColumn('cmo_address', function ($data) {
                    //     $cmo_address = '';
                    //     $cmo_address .= 'Block/Municipality - ' . $data->g_block_ulb_name . '<br>';
                    //     $cmo_address .= 'GP/Ward - ' . $data->g_gp_ward_name;
                    //     return $cmo_address;
                    // })
                    ->addColumn('view', function ($data) {
                        $action = '<button class="btn btn-primary btn-xs grivance_tag_applicant" value="' . $data->grievance_id . '_' . $data->is_redressed . '"><i class="glyphicon glyphicon-edit"></i>View</button>';
                        if ($data->is_processed == 3) {
                            $action = '<b>Pushed to CMO</b>';
                        }
                        return $action;
                    })

                    ->addColumn('grievance_id', function ($data) {
                        return $data->grievance_id;
                    })
                    ->addColumn('grievance_name', function ($data) {
                        return $data->applicant_name;
                    })
                    ->addColumn('sm_mobile_no', function ($data) {
                        return $data->pri_cont_no;
                    })
                    ->addColumn('cmo_receive_date', function ($data) {
                        list($date) = explode("T", $data->created_on);
                        return $date;
                    })
                    ->addColumn('check', function ($data) {
                        if ($data->is_processed == 2) {
                            return '<input type="checkbox"  name="chkbx" class="all_checkbox"  onclick="controlCheckBox();" value="' . $data->grievance_id . '_' . $data->is_redressed . '">';
                        } else {
                            return '';
                        }
                    })
                    ->rawColumns(['view', 'grievance_id', 'grievance_name', 'sm_mobile_no', 'cmo_receive_date', 'check'])
                    ->make(true);
            }
        }
    }
    public function hodView(Request $request)
    {
        $response = [];
        $statusCode = 200;
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = ['error' => 'Error occured in form submit.'];
            return response()->json($response, $statusCode);
        }
        try {
            // dd($request->all());
            $grievance_id = $request->grievance_id;
            $scheme_id = $request->scheme_id;
            $is_redressed = $request->is_redressed;
            $table = $this->getBenDetails();
            $query = '';
            // $query .= "SELECT cmo.grievance_id,cmo.pri_cont_no,cmo.applicant_age,cmo.created_on,cmo.applicant_name,cmo.grievance_description,cmo.remarks,ben.application_id,ben.ben_fname,ben.ben_mname,ben.ben_lname,ben.mobile_no,ben.caste,ben.district_name,ben.block_subdiv_name,ben.next_level_role_id,cmo.atr_type FROM cmo.cmo_sm_data cmo  join ($table) ben on cmo.lb_application_id=ben.application_id AND cmo.is_processed = 1 AND cmo.grievance_id= '".$grievance_id."' and ben.sm_flag = 1";
            if ($is_redressed == 0) {
                $query .= "SELECT cmo.grievance_id,cmo.grievance_no,cmo.pri_cont_no,cmo.applicant_age,cmo.created_on,cmo.applicant_name,cmo.grievance_description,cmo.remarks,ben.application_id,ben.ben_fname,ben.ben_mname,ben.ben_lname,ben.mobile_no,ben.caste,ben.district_name,ben.block_subdiv_name,ben.next_level_role_id,cmo.atr_type,cmo.atr_desc,cmo.is_redressed,cmo.lb_application_id FROM cmo.cmo_sm_data cmo  join ($table) ben on cmo.lb_application_id=ben.application_id AND cmo.is_processed = 1 AND cmo.grievance_id= '" . $grievance_id . "' and ben.cmo_mark = 1";
            }
            if ($is_redressed == 1) {
                $query .= "SELECT * FROM cmo.cmo_sm_data where is_processed = 1 AND grievance_id= '" . $grievance_id . "' and is_redressed = 1";
            }
            // dd($query);
            $data = DB::connection('pgsql')->select($query);
            //   dd($data);
            // $districts = District::where('district_code',$data[0]->lb_dist_code)->first();
            $atr = DB::connection('pgsql_mis')->select(
                "select  atn_id,atr_desc from cmo.m_cmo_atr where atn_id = '" . $data[0]->atr_type . "'"
            );
            if ($data == NULL) {
                return $response = [
                    'status' => 1,
                    'msg' => 'Somethimg went wrong.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Warning!!',
                ];
            } else {
                $response = $data;
            }
            // dd($ben_arr);  
        } catch (\Exception $e) {
            //throw $th;
            // dd($e);
            $response = [
                'exception' => true,
                // 'exception_message' => $e->getMessage(),
                'exception_message' =>
                'Something went wrong. May be session time out logout and login again.',
            ];
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
    public function sendBackToCmo(Request $request)
    {
        //dump($request->all());
        $response = [];
        $statusCode = 200;

        $user_id = Auth::user()->id;
        $dutyObj = Configduty::where('user_id', '=', $user_id)
            ->where('is_active', 1)
            ->first();
        $is_bulk = $request->is_bulk;
        $grievance_id = (int) $request->grivance_id;
        if ($dutyObj->mapping_level == 'Department') {
            $opreation_type = 'HOD';
        } elseif ($dutyObj->mapping_level == 'District') {
            $opreation_type = 'A';
        }
        //dump($opreation_type);        
        // $is_bulk =1;
        // dd($grievance_id);
        if ($is_bulk == 0) {
            if ($opreation_type == 'A') {
                try {
                    $legacy_validation_update = DB::connection('pgsql')->table('cmo.cmo_sm_data')->where('grievance_id', $grievance_id)->where('is_processed', 1)->first();
                    if ($legacy_validation_update == NULL) {
                        return $response = [
                            'status' => 3,
                            'msg' => 'Somethimg went wrong.',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Warning!!',
                        ];
                    } else {
                        $data = array(
                            "data" => array(
                                array(
                                    "position_id" => 1,
                                    "grievance_status" => "GM014",
                                    "grievance_id" => null,
                                    "comment" => $legacy_validation_update->remarks,
                                    "bulk_grivance_id" => [
                                        $grievance_id
                                    ],
                                    "assign_comment" => null,
                                    "action_proposed" => null,
                                    "urgency_flag" => null,
                                    "addl_doc_id" => array(),
                                    "atn_id" => (int) $legacy_validation_update->atr_type,
                                    "atn_reason_master_id" => null,
                                    "action_taken_note" => $legacy_validation_update->atr_desc,
                                    "contact_date" => null,
                                    "tentative_date" => null,
                                    "atr_doc_id" => array(),
                                    "action" => "TA"
                                )
                            )
                        );

                        $cmo_data = $this->submitNewATR($data);
                        //  dd($cmo_data);
                        $status = $cmo_data['status'];
                        $message = $cmo_data['message'];
                        // $exception = $cmo_data['exception'];
                        $updateBenDetails = [];
                        $updateBenDetails['is_processed'] = 3;
                        $updateBenDetails['response_back_by'] = $user_id;
                        $updateBenDetails['response_back_date'] = date('Y-m-d H:i:s');
                        DB::beginTransaction();
                        // dd($status);
                        if ($status == 200 && $message == 'Grievance status updated successfully') {
                            $is_update = DB::table('cmo.cmo_sm_data')
                                ->where('grievance_id', $grievance_id)
                                ->where('is_processed', 2) //Temporary Code
                                ->update($updateBenDetails);
                            if ($is_update) {
                                DB::commit();
                                $response = [
                                    'status' => 1,
                                    'msg' => 'ATR Response Back To CMO Successfully',
                                    'type' => 'green',
                                    'icon' => 'fa fa-check',
                                    'title' => 'Success',
                                ];
                            } else {
                                DB::rollback();
                                $response = [
                                    'status' => 3,
                                    'msg' => 'Somethimg went wrong!!',
                                    'type' => 'red',
                                    'icon' => 'fa fa-warning',
                                    'title' => 'Warning!!',
                                ];
                            }
                        } else {
                            DB::rollback();
                            $response = [
                                'status' => 3,
                                'msg' => 'API Calling Problem. Please try again!!',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Warning!!',
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // dd($e);
                    $response = [
                        'exception' => true,
                        // 'exception_message' => $e->getMessage(),
                        'exception_message' =>
                        'Something went wrong. May be session time out logout and login again.',
                    ];
                    $statusCode = 400;
                } finally {
                    return response()->json($response, $statusCode);
                }
            }
            if ($opreation_type == 'HOD') {
                try {
                    $legacy_validation_update = DB::connection('pgsql')->table('cmo.cmo_sm_data')->where('grievance_id', $grievance_id)->where('is_processed', 2)->first();
                    if ($legacy_validation_update == NULL) {
                        return $response = [
                            'status' => 3,
                            'msg' => 'Somethimg went wrong..',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Warning!!',
                        ];
                    } else {
                        $comment = $legacy_validation_update->remarks;
                        $comment = str_replace(["\t", "\n", "\r"], ' ', $comment);
                        // Remove all special characters except letters, numbers, and spaces
                        $comment = preg_replace('/[^a-zA-Z0-9 ]/', '', $comment);
                        // If you want to trim extra spaces too
                        $comment = preg_replace('/\s+/', ' ', $comment); // Replace multiple spaces with one
                        $comment = trim($comment);
                        $data = array(
                            "data" => array(
                                array(
                                    "position_id" => 1,
                                    "grievance_status" => "GM014",
                                    "grievance_id" => null,
                                    "comment" => $comment,
                                    "bulk_grivance_id" => [
                                        $grievance_id
                                    ],
                                    "assign_comment" => null,
                                    "action_proposed" => null,
                                    "urgency_flag" => null,
                                    "addl_doc_id" => array(),
                                    "atn_id" => (int) $legacy_validation_update->atr_type,
                                    "atn_reason_master_id" => null,
                                    "action_taken_note" => $legacy_validation_update->atr_desc,
                                    "contact_date" => null,
                                    "tentative_date" => null,
                                    "atr_doc_id" => array(),
                                    "action" => "TA"
                                )
                            )
                        );

                        $cmo_data = $this->submitNewATR($data);
                        //  dd($cmo_data);
                        $status = $cmo_data['status'];
                        $message = $cmo_data['message'];
                        // $exception = $cmo_data['exception'];
                        $updateBenDetails = [];
                        $updateBenDetails['is_processed'] = 3;
                        $updateBenDetails['response_back_by'] = $user_id;
                        $updateBenDetails['response_back_date'] = date('Y-m-d H:i:s');
                        DB::beginTransaction();
                        // dd($status);
                        if ($status == 200 && $message == 'Grievance status updated successfully') {
                            $is_update = DB::table('cmo.cmo_sm_data')
                                ->where('grievance_id', $grievance_id)
                                ->where('is_processed', 2) //Temporary Code
                                ->update($updateBenDetails);
                            if ($is_update) {
                                DB::commit();
                                $response = [
                                    'status' => 1,
                                    'msg' => 'ATR Response Back To CMO Successfully',
                                    'type' => 'green',
                                    'icon' => 'fa fa-check',
                                    'title' => 'Success',
                                ];
                            } else {
                                DB::rollback();
                                $response = [
                                    'status' => 3,
                                    'msg' => 'Somethimg went wrong!!',
                                    'type' => 'red',
                                    'icon' => 'fa fa-warning',
                                    'title' => 'Warning!!',
                                ];
                            }
                        } else {
                            DB::rollback();
                            $response = [
                                'status' => 3,
                                'msg' => 'API Calling Problem. Please try again!!',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Warning!!',
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // dd($e);
                    $response = [
                        'exception' => true,
                        // 'exception_message' => $e->getMessage(),
                        'exception_message' =>
                        'Something went wrong. May be session time out logout and login again.',
                    ];
                    $statusCode = 400;
                } finally {
                    return response()->json($response, $statusCode);
                }
            }
        }
        if ($is_bulk == 1) {
            // dump('entering');
            if ($opreation_type == 'HOD') {
                //  dump('enteringg');
                $applicantId = $request->applicantId;
                $bulk_id_arr = explode(',', $applicantId);
                // dd($bulk_id_arr);
                try {
                    $grievance_array = array();
                    foreach ($bulk_id_arr as $key => $value) {
                        $bulk_single_id_arr = explode('_', $value);
                        $griv_id = $bulk_single_id_arr[0];
                        $grievance_array[] = $griv_id;
                        $legacy_validation_update = DB::connection('pgsql')->table('cmo.cmo_sm_data')->where('grievance_id', $griv_id)->where('is_processed', 2)->first();
                        if ($legacy_validation_update == NULL) {
                            return $response = [
                                'status' => 3,
                                'msg' => 'Somethimg went wrong..',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Warning!!',
                            ];
                        } else {
                            $comment = $legacy_validation_update->remarks;
                            $comment = str_replace(["\t", "\n", "\r"], ' ', $comment);
                            // Remove all special characters except letters, numbers, and spaces
                            $comment = preg_replace('/[^a-zA-Z0-9 ]/', '', $comment);
                            // If you want to trim extra spaces too
                            $comment = preg_replace('/\s+/', ' ', $comment); // Replace multiple spaces with one
                            $comment = trim($comment);

                            $data_array["data"][] = [
                                "position_id" => 1,
                                "grievance_status" => "GM014",
                                "grievance_id" => null,
                                "comment" => $comment, // Set to null as per required format
                                "bulk_grivance_id" => [(int) $griv_id], // Convert to integer
                                "assign_comment" => null,
                                "action_proposed" => null,
                                "urgency_flag" => null,
                                "addl_doc_id" => [],
                                "atn_id" => (int) $legacy_validation_update->atr_type, // Ensure it's an integer
                                "atn_reason_master_id" => null,
                                "action_taken_note" => $legacy_validation_update->atr_desc,
                                "contact_date" => null,
                                "tentative_date" => null,
                                "atr_doc_id" => [],
                                "action" => "TA"
                            ];
                        }
                        $data = $data_array;
                    }
                    //dd($data);
                    $cmo_data = $this->submitNewATR($data);
                    $status = $cmo_data['status'];
                    $message = $cmo_data['message'];
                    // $exception = $cmo_data['exception'];
                    $updateBenDetails = [];
                    $updateBenDetails['is_processed'] = 3;
                    $updateBenDetails['response_back_by'] = $user_id;
                    $updateBenDetails['response_back_date'] = date('Y-m-d H:i:s');
                    // dd($status);
                    DB::beginTransaction();
                    if ($status == 200 && $message == 'Grievance status updated successfully') {
                        $is_update = DB::table('cmo.cmo_sm_data')
                            ->whereIn('grievance_id', $grievance_array)
                            ->where('is_processed', 2) //Temporary Code
                            ->update($updateBenDetails);
                        if ($is_update) {
                            DB::commit();
                            $response = [
                                'status' => 1,
                                'msg' => 'ATR Response Back To CMO Successfully',
                                'type' => 'green',
                                'icon' => 'fa fa-check',
                                'title' => 'Success',
                            ];
                        } else {
                            DB::rollback();
                            $response = [
                                'status' => 3,
                                'msg' => 'Somethimg went wrong!!',
                                'type' => 'red',
                                'icon' => 'fa fa-warning',
                                'title' => 'Warning!!',
                            ];
                        }
                    } else {
                        DB::rollback();
                        $response = [
                            'status' => 3,
                            'msg' => 'API Calling Problem. Please try again!!',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Warning!!',
                        ];
                    }
                } catch (\Exception $e) {
                    dd($e);
                    $response = [
                        'exception' => true,
                        // 'exception_message' => $e->getMessage(),
                        'exception_message' =>
                        'Something went wrong. May be session time out logout and login again.',
                    ];
                    $statusCode = 400;
                } finally {
                    return response()->json($response, $statusCode);
                }
            }
        }
    }
    public function sendOperator(Request $request)
    {
        //   dd($request->all());
        $response = [];
        $statusCode = 200;
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = ['error' => 'Error occured in form submit.'];
            return response()->json($response, $statusCode);
        }
        try {
            $rules = [
                'atr_type' => 'required',
                'remarks' => ['nullable', 'regex:/^[a-zA-Z0-9 ]*$/'],
            ];
            $attributes = [
                'atr_type' => 'ATR Type',
                'remarks' => 'Remarks',
            ];
            $messages = [
                'required' => 'The :attribute field is required.',
            ];
            $validator = Validator::make(
                $request->all(),
                $rules,
                $messages,
                $attributes
            );
            if ($validator->passes()) {
                $user_id = Auth::user()->id;
                $scheme_id = $request->scheme_id;
                $designation = Auth::user()->designation_id;
                $duty_obj = Configduty::where('user_id', $user_id)->where('scheme_id', $scheme_id)->first();
                if ($duty_obj->mapping_level == "Department") {
                    $created_by_local_body_code = NULL;
                    $created_by_dist_code = NULL;
                } else {
                    $created_by_dist_code = $duty_obj->district_code;
                    if ($duty_obj->mapping_level == "Subdiv") {
                        $created_by_local_body_code = $duty_obj->urban_body_code;
                    } else if ($duty_obj->mapping_level == "Block") {
                        $created_by_local_body_code = $duty_obj->taluka_code;
                    } else if ($duty_obj->mapping_level == "District") {
                        $created_by_local_body_code = NULL;
                    }
                }

                // $next_level_role_id = Workflow::getParentId($scheme_id, Auth::user()->designation_id);
                // $ben_id = $request->ben_id;
                $scheme_id = $request->scheme_id;
                $grievance_id = $request->grievance_id;
                $grievance_mobile_no = $request->grievance_mobile_no;
                $atr_type = $request->atr_type;
                $remarks = $request->remarks;
                // $table = $this->getSchemaName($scheme_id);
                $table = 'pension.beneficiaries';
                $c_time = date('Y-m-d H:i:s', time());
                // $ben_details = DB::table($table)->where('id', $ben_id)->where('scheme_id', $scheme_id)->first();
                // $benDetails = [];
                // // $benDetails['sm_flag'] = 1;
                // $benDetails['cmo_mark'] = 1;
                // if($designation == 'Verifier'){
                //     if($ben_details->next_level_role_id == NULL){
                //         $benDetails['next_level_role_id'] = $next_level_role_id;
                //         $benDetails['is_verified'] = 1;
                //         $benDetails['verified_by'] = $user_id;
                //         $benDetails['verification_date'] = $c_time;
                //     }
                // }
                $atr = DB::connection('pgsql_mis')->select(
                    "select  atn_id,atr_desc from cmo.m_cmo_atr where atn_id = '" . $atr_type . "'"
                );
                DB::beginTransaction();
                $updateDetails = [];
                // $updateDetails['jb_id'] = $ben_id;
                $updateDetails['scheme_id'] = $scheme_id;
                $updateDetails['atr_type'] = $atr_type;
                $updateDetails['atr_desc'] = trim($atr[0]->atr_desc);
                $updateDetails['remarks'] = $remarks;
                // $updateDetails['is_processed'] = 1;
                $updateDetails['send_to_op'] = 1;
                $updateDetails['send_to_op_by'] = $user_id;
                $updateDetails['send_to_op_date'] = date('Y-m-d H:i:s');
                $is_update = DB::table('cmo.cmo_sm_data')
                    ->where('grievance_id', $grievance_id)
                    // ->where('scheme_id', $scheme_id)
                    // ->where('grievance_mobile', $grievance_mobile_no)
                    ->where('is_processed', 0) //Temporary Code
                    ->where('is_redressed', 0)
                    ->where('send_to_op', 0)
                    ->update($updateDetails);

                // $ben_update = DB::table($table)
                //     ->where('id', $ben_id)
                //     ->where('scheme_id', $scheme_id)
                //     ->update($benDetails);



                // $accept_reject_model = new AcceptRejectInfo;
                // $accept_reject_model->created_at = $c_time;
                // $accept_reject_model->application_id = $ben_id;
                // $accept_reject_model->scheme_id = $scheme_id;
                // $accept_reject_model->user_id = $user_id;
                // $accept_reject_model->comment_message = $remarks;
                // $accept_reject_model->user_id = $user_id;
                // $accept_reject_model->created_by_dist_code = $created_by_dist_code;
                // $accept_reject_model->created_by_local_body_code = $created_by_local_body_code;
                // $accept_reject_model->ip_address = request()->ip();
                // $accept_reject_model->module_name = class_basename(Route::current()->controller) . '@' . Route::getCurrentRoute()->getActionMethod() . 'AV';
                // $accept_reject_model->op_type = 'SMATAG';
                // $is_saved_log = $accept_reject_model->save();
                // dump($is_update); dd($ben_update);         
                if ($is_update == 1) {
                    DB::commit();
                    $response = [
                        'status' => 1,
                        'msg' => 'Beneficiary has been send to Operator Successfully',
                        'type' => 'green',
                        'icon' => 'fa fa-check',
                        'title' => 'Success',
                    ];
                } else {
                    DB::rollback();
                    $response = [
                        'status' => 3,
                        'msg' => '3 Somethimg went wrong!!',
                        'type' => 'red',
                        'icon' => 'fa fa-warning',
                        'title' => 'Warning!!',
                    ];
                }
            } else {
                $return_status = 0;
                $return_msg = $validator->errors()->all();
                $response = [
                    'status' => $return_status,
                    'msg' => $return_msg,
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Warning!!',
                ];
            }
        } catch (\Exception $e) {
            //    dd($e);
            DB::rollback();
            $response = [
                'exception' => true,
                // 'exception_message' => $e->getMessage(),
                'exception_message' =>
                'Something went wrong. May be session time out logout and login again.',
            ];
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
    public function opListCmo()
    {
        // dd('ok');
        $user_id = Auth::user()->id;
        $designation = Auth::user()->designation_id;
        $mapObj = DB::connection('pgsql_mis')
            ->table('public.duty_assignement')
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->first();

        if ($designation == 'Operator') {

            if ($mapObj->is_urban == 1) {
                $urban_body_code = $mapObj->urban_body_code;
                $urban_bodys = UrbanBody::where(
                    'sub_district_code',
                    $urban_body_code
                )
                    ->select('urban_body_code', 'urban_body_name')
                    ->get();
                return view('cmo-grievance1/cmo-op-list', [
                    'mapLevel' => $mapObj->mapping_level . $designation,
                    'urban_bodys' => $urban_bodys,
                    'local_body_code' => $urban_body_code,
                    'district_code' => $mapObj->district_code,
                ]);
            } else {
                $taluka_code = $mapObj->taluka_code;
                $gps = GP::where('block_code', $taluka_code)
                    ->select('gram_panchyat_code', 'gram_panchyat_name')
                    ->get();
                return view('cmo-grievance1/cmo-op-list', [
                    'mapLevel' => $mapObj->mapping_level . $designation,
                    'gps' => $gps,
                    'local_body_code' => $taluka_code,
                    'district_code' => $mapObj->district_code,
                ]);
            }
        }
    }
    public function cmoEntryList(Request $request)
    {
        if ($request->ajax()) {
            // dd($request->all());
            $user_id = Auth::user()->id;
            $designation = Auth::user()->designation_id;
            $mapObj = DB::connection('pgsql_mis')
                ->table('public.duty_assignement')
                ->where('user_id', $user_id)
                ->where('is_active', 1)
                ->first();
            if ($mapObj->is_urban == 1) {
                $local_body_code = $mapObj->urban_body_code;
            } else {
                $local_body_code = $mapObj->taluka_code;
            }
            /*if ($mapObj->is_urban == 1) {
                $munlist = UrbanBody::where('sub_district_code', $local_body_code)->get()->toArray();
                $munlist_ids = array_column($munlist, 'urban_body_code');
                $munlist_ids_list = "'" . implode("', '", $munlist_ids) . "'";
                $query = "Select * from cmo.cmo_sm_data where is_processed=0 and is_redressed=0 and lb_local_body_code IN (" . $munlist_ids_list . ") and send_to_op=1";
            } else
                $query = " Select * from cmo.cmo_sm_data where is_processed=0 and is_redressed=0 and lb_local_body_code='" . $local_body_code . "' and send_to_op=1";*/
            $query = " Select * from cmo.cmo_sm_data where is_processed=0 and is_redressed=0 and lb_local_body_code='" . $local_body_code . "' and send_to_op=1";

            //  dd($query);
            $data = DB::select($query);

            return datatables()
                ->of($data)
                ->addColumn('view', function ($data) {
                    if ($data->is_processed == 0) {
                        // href="cmo-grievance1-find?id=' . $data->jb_beneficiary_id  . '&scheme_id=' . $data->scheme_id . '&sm_mobile_no='.$data->sm_mobile_no.'"
                        $action = '<a href="' . route('lb-entry-draft-edit', ['type' => 10, 'grievance_id' => $data->grievance_id]) . '">
                        <button class="btn btn-xs btn-info find_applicant">
                            <i class="glyphicon glyphicon-edit"></i> Entry
                        </button>
                    </a>';
                    }
                    return $action;
                })
                ->addColumn('grievance_id', function ($data) {
                    return $data->grievance_id;
                })
                ->addColumn('grievance_name', function ($data) {
                    return $data->applicant_name;
                })
                ->addColumn('sm_mobile_no', function ($data) {
                    return $data->pri_cont_no;
                })
                ->addColumn('cmo_receive_date', function ($data) {
                    return $data->created_on;
                })

                // ->addColumn('description', function ($data) {
                //     return $data->complain_description;
                // })
                ->rawColumns(['view', 'grievance_id', 'grievance_name', 'sm_mobile_no', 'cmo_receive_date',])
                ->make(true);
        }
    }
    public function applicanttagdetails(Request $request)
    {
        try {
            $user_id = Auth::user()->id;
            $designation = Auth::user()->designation_id;
            $scheme_id = $this->scheme_id;
            $whereCondition = " 1=1 ";
            $mapObj = DB::connection('pgsql_mis')
                ->table('public.duty_assignement')
                ->where('user_id', $user_id)
                ->where('is_active', 1)
                ->first();
            if ($designation == 'Verifier' || $designation == 'Delegated Verifier') {
                $created_by_district_code = $mapObj->district_code;
                if ($mapObj->is_urban == 1) {
                    $mapLevel = 'SubdivVerifier';
                    $created_by_local_body_code = $mapObj->urban_body_code;
                    // $whereCondition = $whereCondition . " and lb_dist_code='" . $created_by_district_code . "'";
                    // $munlist = UrbanBody::where('sub_district_code', $created_by_local_body_code)->get()->toArray();
                    // $munlist_ids = array_column($munlist, 'urban_body_code');
                    // $munlist_ids_list = "'" . implode("', '", $munlist_ids) . "'";
                    // $whereCondition = $whereCondition . " and lb_local_body_code IN (" . $munlist_ids_list . ")";
                    // $whereCondition = $whereCondition . " and lb_local_body_code='" . $created_by_local_body_code . "'";
                } else {
                    $mapLevel = 'BlockVerifier';
                    $created_by_local_body_code = $mapObj->taluka_code;
                    // $whereCondition = $whereCondition . " and lb_dist_code='" . $created_by_district_code . "'";
                    // $whereCondition = $whereCondition . " and lb_local_body_code='" . $created_by_local_body_code . "'";
                }
                $whereCondition = $whereCondition . " and lb_dist_code='" . $created_by_district_code . "'";
                $whereCondition = $whereCondition . " and lb_local_body_code='" . $created_by_local_body_code . "'";
            } else if ($designation == 'Approver' || $designation == 'Delegated Approver') {
                $created_by_district_code = $mapObj->district_code;
                $mapLevel = 'DistrictApprover';
                $whereCondition = $whereCondition . " and lb_dist_code='" . $created_by_district_code . "'";
            } else if ($designation == 'HOD') {
                $mapLevel = 'Department';
            } else {
                return redirect('/')->with('success', 'UnAuthorized');
            }
            $grievance_id = $request->grievance_id;
            if (empty($grievance_id)) {
                return redirect("/cmo-grievance-workflow1")->with('msg1', 'Grievance Id Not Found');
            }
            $whereCondition = $whereCondition . " and grievance_id='" . $grievance_id . "'";

            $atr_details = DB::connection('pgsql_mis')->table('cmo.cmo_sm_data')->where('grievance_id', $grievance_id)->whereraw($whereCondition)->first();
            if (!is_null($atr_details->lb_application_id)) {
                if ($atr_details->table_source == 1) {
                    $ben_tag_details = DB::table('lb_scheme.ben_personal_details')->where('application_id', $atr_details->lb_application_id)->first();
                    $ben_contact_details = DB::table('lb_scheme.ben_contact_details')->where('application_id', $atr_details->lb_application_id)->first();
                    $ben_bank_details = DB::table('lb_scheme.ben_bank_details')->where('application_id', $atr_details->lb_application_id)->first();
                } else if ($atr_details->table_source == 2) {
                    $ben_tag_details = DB::table('lb_scheme.faulty_ben_personal_details')->where('application_id', $atr_details->lb_application_id)->first();
                    $ben_contact_details = DB::table('lb_scheme.faulty_ben_contact_details')->where('application_id', $atr_details->lb_application_id)->first();
                    $ben_bank_details = DB::table('lb_scheme.faulty_ben_bank_details')->where('application_id', $atr_details->lb_application_id)->first();
                } else if ($atr_details->table_source == 3) {
                    $ben_tag_details = DB::table('lb_scheme.draft_ben_personal_details')->where('application_id', $atr_details->lb_application_id)->first();
                    $ben_contact_details = DB::table('lb_scheme.draft_ben_contact_details')->where('application_id', $atr_details->lb_application_id)->first();
                    $ben_bank_details = DB::table('lb_scheme.draft_ben_bank_details')->where('application_id', $atr_details->lb_application_id)->first();
                }
                if ($atr_details->table_source == 4) {
                    $ben_tag_details = DB::table('lb_scheme.ben_reject_details')->where('application_id', $atr_details->lb_application_id)->first();
                    $ben_contact_details = collect([]);
                    $ben_bank_details = collect([]);
                }
                if ($atr_details->table_source == null) {
                    $ben_tag_details = collect([]);
                    $ben_contact_details = collect([]);
                    $ben_bank_details = collect([]);
                }
            } else {
                $ben_tag_details = collect([]);
                $ben_contact_details = collect([]);
                $ben_bank_details = collect([]);
            }
            //dd( $ben_contact_details);

            return view('cmo-grievance1/find_applicant_tag', [
                'scheme_id' => $scheme_id,
                'grievance_id' => $grievance_id,
                'mapLevel' => $mapLevel,
                'atr_details' => $atr_details,
                'ben_tag_details' => $ben_tag_details,
                'ben_contact_details' => $ben_contact_details,
                'ben_bank_details' => $ben_bank_details
            ]);
        } catch (\Exception $e) {
            // dd($e);
            return redirect("/cmo-grievance-workflow1")->with('msg1', 'Somethimg went wrong....');
        }
    }
    public function approve(Request $request)
    {


        $grievance_id = $request->grivance_id;
        // dd($grievance_id);
        if (empty($grievance_id)) {
            return redirect("/cmo-grievance-workflow1")->with('msg1', 'Grievance Id Not Found');
        }
        $user_id = Auth::user()->id;
        $designation = Auth::user()->designation_id;
        $scheme_id = $this->scheme_id;
        $whereCondition = " is_processed=1 ";
        $mapObj = DB::connection('pgsql_mis')
            ->table('public.duty_assignement')
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->first();
        $created_by_district_code = $mapObj->district_code;
        $whereCondition = $whereCondition . " and grievance_id='" . $grievance_id . "'";
        $atr_details = DB::connection('pgsql_mis')->table('cmo.cmo_sm_data')->where('grievance_id', $grievance_id)->whereraw($whereCondition)->first();
        if (is_null($atr_details)) {
            return redirect("/cmo-grievance-workflow1")->with('error', 'Not Allowded');
        }
        DB::beginTransaction();
        $updateDetails = [];
        $updateDetails['is_processed'] = 2;
        $benAcceptRejectInfo['application_id'] = $atr_details->lb_application_id;
        $benAcceptRejectInfo['created_by'] = $user_id;
        $benAcceptRejectInfo['user_id'] = $user_id;
        $benAcceptRejectInfo['created_at'] = date('Y-m-d H:i:s');
        $benAcceptRejectInfo['op_type'] = 'CMO-Marking-Approve';
        $benAcceptRejectInfo['designation_id'] = $designation;
        $benAcceptRejectInfo['ip_address'] = $request->ip();

        $is_insert = DB::table('lb_scheme.ben_accept_reject_info')->insert($benAcceptRejectInfo);
        $is_update = DB::table('cmo.cmo_sm_data')
            ->where('grievance_id', $grievance_id)
            ->where('is_processed', 1)
            ->where('lb_dist_code', $created_by_district_code)
            ->update($updateDetails);
        if ($is_update && $is_insert) {
            // dd('ok');
            DB::commit();
            return redirect("/cmo-grievance-workflow1")->with('message', 'Grievance ATR Approve Successfully');
        } else {
            //dd('ok2');
            DB::rollback();
            return redirect("/cmo-grievance-workflow1")->with('msg1', 'Somethimg went wrong!!');
        }
    }

    public function revert(Request $request)
    {
        // dd($request->all());
        $grievance_id = $request->grivance_id;
        // dd($grievance_id);
        if (empty($grievance_id)) {
            return redirect("/cmo-grievance-workflow1")->with('msg1', 'Grievance Id Not Found');
        }
        $user_id = Auth::user()->id;
        $designation = Auth::user()->designation_id;
        $scheme_id = $this->scheme_id;
        $whereCondition = " is_processed=1 ";
        $mapObj = DB::connection('pgsql_mis')
            ->table('public.duty_assignement')
            ->where('user_id', $user_id)
            ->where('is_active', 1)
            ->first();
        $created_by_district_code = $mapObj->district_code;
        $whereCondition = $whereCondition . " and grievance_id='" . $grievance_id . "'";
        $atr_details = DB::connection('pgsql_mis')->table('cmo.cmo_sm_data')->where('grievance_id', $grievance_id)->whereraw($whereCondition)->first();
        if (is_null($atr_details)) {
            return redirect("/cmo-grievance-workflow1")->with('error', 'Not Allowded');
        }
        DB::beginTransaction();
        $updateDetails = [];
        $updateDetails = [];
        $updateDetails['lb_application_id'] = NULL;
        $updateDetails['atr_type'] = NULL;
        $updateDetails['atr_desc'] = NULL;
        $updateDetails['remarks'] = NULL;
        $updateDetails['is_processed'] = 0;
        $updateDetails['is_mark'] = 0;
        $updateDetails['marked_by'] = NULL;
        $updateDetails['marked_date'] = NULL;
        $updateDetails['lb_next_level_role_id'] = NULL;
        $updateDetails['table_source'] = NULL;
        $updateDetails['is_redressed'] = 0;
        $benAcceptRejectInfo['application_id'] = $atr_details->lb_application_id;
        $benAcceptRejectInfo['created_by'] = $user_id;
        $benAcceptRejectInfo['user_id'] = $user_id;
        $benAcceptRejectInfo['created_at'] = date('Y-m-d H:i:s');
        $benAcceptRejectInfo['op_type'] = 'CMO-Marking-Revert';
        $benAcceptRejectInfo['designation_id'] = $designation;
        $benAcceptRejectInfo['ip_address'] = $request->ip();
        $benAcceptRejectInfo['grievance_id'] = $grievance_id;

        $is_insert = DB::table('lb_scheme.ben_accept_reject_info')->insert($benAcceptRejectInfo);
        $is_update = DB::table('cmo.cmo_sm_data')
            ->where('grievance_id', $grievance_id)
            ->where('is_processed', 1)
            ->where('lb_dist_code', $created_by_district_code)
            ->update($updateDetails);
        // dd($is_insert &&  $is_update);
        if ($is_update && $is_insert) {
            // dd('ok');
            DB::commit();
            return redirect("/cmo-grievance-workflow1")->with('success', 'Grievance ATR Reverted Successfully');
        } else {
            //dd('ok2');
            DB::rollback();
            return redirect("/cmo-grievance-workflow1")->with('msg1', 'Somethimg went wrong!!');
        }
    }
    public function cmoMisReport(Request $request)
    {
        $is_active = 0;
        $base_date = '2020-01-01';
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        $district_visible = $is_urban_visible = $block_visible = 1;
        $municipality_visible = 0;
        $gp_ward_visible = 0;
        $muncList = collect([]);
        $gpList = collect([]);
        if ($designation_id == 'Admin' || $designation_id == 'HOD' || $designation_id == 'HOP' || $designation_id == 'MisState' || $designation_id == 'Dashboard') {
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
            'cmo-grievance1.cmo-mis',
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
    public function getMisReport(Request $request)
    {
        $district = $request->district;
        $urban_code = $request->urban_code;
        $block = $request->block;
        $muncid = $request->muncid;
        $gp_ward = $request->gp_ward;

        // dd($gp_ward);
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
            $user_msg = "CMO MIS Report";
            $title = $user_msg;
            //dd($title);

            $data = array();
            $return_status = 1;
            $return_msg = '';
            $heading_msg = '';
            $external = 0;
            $external_arr = array();
            $external_filter = array();
            $is_address = 0;
            if (!empty($gp_ward)) {
                if ($urban_code == 1) {
                    $is_address = 1;
                    $column = "Ward";
                    $heading_msg = $user_msg . ' of the Ward ' . $gp_ward_name;
                    $data = $this->getWardWise($district, $block, $muncid, $gp_ward);
                } else {
                    $is_address = 1;
                    $column = "GP";
                    $heading_msg = $user_msg . ' of the GP ' . $gp_ward_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward);
                }
            } else if (!empty($muncid)) {
                $is_address = 1;
                $column = "Ward";
                $municipality_row = UrbanBody::where('urban_body_code', '=', $muncid)->first();
                $heading_msg = 'Ward Wise ' . $user_msg . ' of the Municipality ' . $municipality_row->urban_body_name;
                $data = $this->getWardWise($district, $block, $muncid, NULL);
            } else if (!empty($block)) {
                if ($urban_code == 1) {
                    $is_address = 1;
                    $column = "Municipality";
                    $heading_msg = 'Municipality Wise ' . $user_msg . ' of the Sub Division ' . $blk_munc_name;
                    $data = $this->getMuncWise($district, $block, NULL, NULL);
                } else if ($urban_code == 2) {
                    $is_address = 1;
                    $block_arr = Taluka::where('block_code', '=', $block)->first();
                    $column = "GP";
                    $heading_msg = 'GP Wise ' . $user_msg . ' of the Block ' . $block_arr->block_name;
                    $data = $this->getGpWise($district, $block, NULL, $gp_ward);
                }
            } else {

                if (!empty($district)) {
                    if ($urban_code == 1) {
                        $column = "Sub Division";
                        $heading_msg = 'Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $data = $this->getSubDivWise($district, NULL, NULL, NULL);
                    } else if ($urban_code == 2) {
                        $heading_msg = 'Block Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block";
                        $data = $this->getBlockWise($district, NULL, NULL, NULL);
                    } else {
                        $heading_msg = 'Block/Sub Division Wise ' . $user_msg . ' of the District ' . $district_row->district_name;
                        $column = "Block/Sub Division";
                        $data1 = $this->getBlockWise($district, NULL, NULL, NULL);
                        $data2 = $this->getSubDivWise($district, NULL, NULL, NULL);
                        $data = array_merge($data1, $data2);
                    }
                } else {
                    $column = "District";
                    $heading_msg = 'District Wise ' . $user_msg;
                    $data = $this->getDistrictWise($district, NULL, NULL, NULL);
                    $external = 0;
                }
            }
            if ($is_address == 1) {
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
    public function getBlockWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL)
    {
        $whereCon = "fp.dist_code =" . $district_code;
        $whereMain = " WHERE district_code =" . $district_code;


        $query = "select A.location_id,A.location_name,
      COALESCE(cmo.total_grievance,0) as total_grievance,
      COALESCE(cmo.total_verification_pending,0) as total_verification_pending, 
      COALESCE(cmo.total_verified,0) as total_verified, 
      COALESCE(cmo.total_approved,0) as total_approved,
      COALESCE(cmo.total_grievance_back,0) as total_grievance_back
      from(
      select block_code as location_id,'Block-'||block_name as location_name
       from public.m_block " . $whereMain . " ) as A  
      LEFT JOIN
      (select  count(1)  as total_grievance,
      count(1) filter(where is_processed = 0) as total_verification_pending,
      count(1) filter(where is_processed = 1) as total_verified,
      count(1) filter(where is_processed = 2) as total_approved,
      count(1) filter(where is_processed = 3) as total_grievance_back,
      lb_local_body_code
      from cmo.cmo_sm_data WHERE lb_dist_code = '" . $district_code . "' AND lb_local_body_code::text ~ '^\d+$' group by lb_local_body_code) as cmo ON A.location_id=cmo.lb_local_body_code::int";
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }
    public function getSubDivWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL)
    {
        $whereMain = " WHERE district_code =" . $district_code;

        $query = "SELECT 
        A.location_id,
        A.location_name,
        COALESCE(cmo.total_grievance, 0) AS total_grievance,
        COALESCE(cmo.total_verification_pending, 0) AS total_verification_pending, 
        COALESCE(cmo.total_verified, 0) AS total_verified, 
        COALESCE(cmo.total_approved, 0) AS total_approved,
        COALESCE(cmo.total_grievance_back, 0) AS total_grievance_back
        FROM (
            SELECT 
                sub.sub_district_code AS location_id,
                'SubDiv-' || sub.sub_district_name AS location_name
            FROM public.m_sub_district sub
            " . $whereMain . "
        ) AS A
        LEFT JOIN (
        SELECT 
            COUNT(1) AS total_grievance,
            count(1) filter(where is_processed = 0) as total_verification_pending,
            count(1) filter(where is_processed = 1) as total_verified,
            count(1) filter(where is_processed = 2) as total_approved,
            count(1) filter(where is_processed = 3) as total_grievance_back,
            lb_local_body_code
                from cmo.cmo_sm_data where TRIM(lb_dist_code) = '" . $district_code . "' 
         and lb_local_body_code::text ~ '^\d+$' group by lb_local_body_code) as cmo ON A.location_id=lb_local_body_code::int
         UNION ALL
            select 
            -1 as location_id, 
            'Unmapped (Block & Sub-Div null)' as location_name,
            COUNT(1) AS total_grievance,
            count(1) filter(where is_processed = 0) as total_verification_pending,
            count(1) filter(where is_processed = 1) as total_verified,
            count(1) filter(where is_processed = 2) as total_approved,
            count(1) filter(where is_processed = 3) as total_grievance_back
            from cmo.cmo_sm_data  
            where TRIM(lb_dist_code) = '" . $district_code . "'
            and (lb_local_body_code is null OR TRIM(lb_local_body_code) = '')";
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }
    public function getDistrictWise($district_code = NULL, $ulb_code = NULL, $block_ulb_code = NULL, $gp_ward_code = NULL)
    {
        $whereCon = "where 1=1";

        $query = "select A.location_id,A.location_name,
        COALESCE(cmo.total_grievance,0) as total_grievance,
        COALESCE(cmo.total_verification_pending,0) as total_verification_pending, 
        COALESCE(cmo.total_verified,0) as total_verified, 
        COALESCE(cmo.total_approved,0) as total_approved,
        COALESCE(cmo.total_grievance_back,0) as total_grievance_back
        from(
        select district_code as location_id,district_name as location_name
         from public.m_district ) as A  
        LEFT JOIN
        (select  count(1)  as total_grievance,
		count(1) filter(where is_processed = 0) as total_verification_pending,
	    count(1) filter(where is_processed = 1) as total_verified,
	    count(1) filter(where is_processed = 2) as total_approved,
	    count(1) filter(where is_processed = 3) as total_grievance_back,
	    lb_dist_code
        from cmo.cmo_sm_data group by lb_dist_code) as cmo ON A.location_id=cmo.lb_dist_code::int";
        $result = DB::connection('pgsql_appwrite')->select($query);
        return $result;
    }

    public function mapbosget(Request $request)
    {
        $grievance_id = $request->grievance_id;
        $grievance_data = DB::table('cmo.cmo_sm_data')
            ->select('grievance_description', 'applicant_name', 'grievance_id', 'lb_dist_code', 'grievance_no', 'pri_cont_no', 'applicant_age')
            ->where('grievance_id', $grievance_id)
            ->first();
        $dist_name = District::where('district_code', $grievance_data->lb_dist_code)->value('district_name');
        return response()->json([
            'grievance_data' => $grievance_data,
            'district_name' => $dist_name
        ]);
    }


    public function getblksublist(Request $request)
    {
        if ($request->mapping_type == 1) {
            $results = Taluka::where('district_code', $request->dist_code)
                ->select('block_code as id', 'block_name as name')
                ->get();
        } else {
            $results = SubDistrict::where('district_code', $request->dist_code)
                ->select('sub_district_code as id', 'sub_district_name as name')
                ->get();
        }
        return response()->json($results);
    }

    public function getMunicipalityList(Request $request)
    {
        $results = UrbanBody::where('sub_district_code', $request->subdivision_id)
            ->select('urban_body_code as id', 'urban_body_name as name')
            ->get();
        return response()->json($results);
    }
    public function mapbospost(Request $request)
    {
        $user_id = Auth::user()->id;
        $designation_id = Auth::user()->designation_id;
        if ($designation_id == 'Delegated Approver' || $designation_id == 'Approver') {
            $rules = [
                'blk_sub_value' => 'required|integer',
            ];
            if ($request->mapping_type == 2) {
                // $rules['municipality'] = 'required|integer';
                $attributes = [
                    'blk_sub_value' => 'Subdivision',
                    // 'municipality' => 'Municipality',
                ];
            } else {
                $attributes = [
                    'blk_sub_value' => 'Block ',
                ];
            }
            $messages = [
                'required' => ':attribute is required.',
                'integer' => ':attribute is required.',
            ];
            $validator = Validator::make(
                $request->all(),
                $rules,
                $messages,
                $attributes
            );
            if ($validator->passes()) {
                $updateDetails = [];
                $updateDetails['lb_local_body_code'] = $request->blk_sub_value;
                $benAcceptRejectInfo['grievance_id'] = $request->grievance_id;
                $benAcceptRejectInfo['created_by'] = $user_id;
                $benAcceptRejectInfo['user_id'] = $user_id;
                $benAcceptRejectInfo['created_at'] = date('Y-m-d H:i:s');
                $benAcceptRejectInfo['op_type'] = 'CMO-Map-Grievance';
                $benAcceptRejectInfo['ip_address'] = $request->ip();
                DB::beginTransaction();
                $is_update = DB::table('cmo.cmo_sm_data')
                    ->where('grievance_id', $request->grievance_id)
                    ->update($updateDetails);
                $is_insert = DB::table('lb_scheme.ben_accept_reject_info')->insert($benAcceptRejectInfo);
                if ($is_update && $is_insert) {
                    DB::commit();
                    $response = array(
                        'return_status' => 1,
                        'type' => 'green',
                        'icon' => 'fa fa-check',
                        'title' => 'Success',
                        'return_msg' => 'The applicant successfully mapped',
                    );
                } else {
                    DB::rollback();
                    $response = array(
                        'return_status' => 2,
                        'type' => 'red',
                        'icon' => 'fa fa-warning',
                        'title' => 'Error',
                        'return_msg' => 'Something went wrong, Please try again!',
                    );
                }
            } else {
                $response = array(
                    'return_status' => 0,
                    // 'type' => 'red', 
                    // 'icon' => 'fa fa-warning', 
                    // 'title' =>'Error',
                    'return_msg' => $validator->errors()->all(),
                );
            }
            return response()->json(
                $response
            );
        } else {
            return redirect('/')->with('message', 'UnAuthorized');
        }
    }
}
