<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configduty;
use App\Models\MapLavel;
use App\Models\District;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\UrbanBody;
use App\Models\GP;

use App\Models\DocumentType;
use App\Models\DataSourceCommon;
use App\Models\getModelFunc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\RejectRevertReason;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Models\DistrictEntryMapping;
use App\Models\DsPhase;

class WorkflowController extends Controller
{
  // use SendsPasswordResetEmails;

  public function __construct()
  {
    $this->middleware('auth');
    $this->source_type = 'ss_nfsa';
    $this->scheme_id = 20;
    $phaseArr = DsPhase::where('is_current', TRUE)->first();
    //$mydate = $phaseArr->base_dob;

    // $myYear =  date("Y");
    //$mydate =  $myYear.'-'.'01'.'-'.'01';
    $mydate = date('Y-m-d');
    $max_date = strtotime("-25 year", strtotime($mydate));
    $max_date = date("Y-m-d", $max_date);
    $min_date = strtotime("-60 year", strtotime($mydate));
    $min_date = date("Y-m-d", $min_date);
    $this->base_dob_chk_date = $mydate;
    $this->max_dob = $max_date;
    $this->min_dob = $min_date;
  }

  public function shemeSessionCheck(Request $request)
  {
    $scheme_id = 0;

    if ($request->get('pr1')) {
      if ($request->get('pr1') == "lb_wcd") {
        $scheme_id = 20;
      } else {
        return redirect("/")->with('error', ' Parameter Invalid');
      }
    } else {
      return redirect("/")->with('error', 'Method is not valid');
    }

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

      //  $ben_table = 'dist_' . $distCode . '.beneficiary';
      return true;
    } else {
      return false;
    }
  }

  public function applicationdetails(Request $request)
  {
    if ($this->shemeSessionCheck($request)) {
      $errormsg = Config::get('constants.errormsg');
      $scheme_id = $request->session()->get('scheme_id');
      $dob_base_date = Carbon::parse($this->base_dob_chk_date)->format('d/m/Y');
      $ben_table = $request->session()->get('ben_table');
      $mappingLevel = $request->session()->get('level');
      $district_code = $request->session()->get('distCode');
      $is_first = $request->session()->get('is_first');
      $is_urban = $request->session()->get('is_urban');
      $urban_body_code = $request->session()->get('bodyCode');
      $taluka_code = $request->session()->get('bodyCode');
      $role_id = $request->session()->get('role_id');
      $user_id = Auth::user()->id;
      $reject_revert_reason = RejectRevertReason::where('status', true)->get();
      $getModelFunc = new getModelFunc();
      $personal_table = $getModelFunc->getTable($district_code, '', 1, 1);
      $personal_modal = new DataSourceCommon;
      $personal_modal->setTable('' . $personal_table);
      $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);
      $aadhar_table = $getModelFunc->getTable($district_code, '', 2, 1);
      $allowded_arr = DistrictEntryMapping::where('district_code', $district_code)->first();
      $verification_allowded = intval($allowded_arr->main_verification);
      $approval_allowded = intval($allowded_arr->main_approval);

      $ds_phase_list = DsPhase::all();
      if ($is_first) {   // First Level Verifier   	
        if ($mappingLevel == "State") {
          $level = "State";
        }
        /****************************District************************* */ else if ($mappingLevel == "District") {
          $rows = $personal_modal->where('next_level_role_id', null)->where('created_by_dist_code', $district_code)->orderBy('id', 'desc')->paginate(10);
          return view('processApplication/pension_list', ['nhm_employee_details' => $rows, 'reject_revert_reason' => $reject_revert_reason, 'sessiontimeoutmessage' => $errormsg['sessiontimeOut']]);
        } else if ($mappingLevel == "Subdiv") {
          if ($is_urban == 1) {
            $duty_level = "SubdivVerifier";
            $urban_bodys = UrbanBody::where('sub_district_code', $urban_body_code)->select('urban_body_code', 'urban_body_name')->get();
            $urban_body_codes = [];
            $i = 0;
            foreach ($urban_bodys as $urban_body) {

              $urban_body_codes[$i] = $urban_body->urban_body_code;
              $i++;
            }
            if (request()->ajax()) {
              $ds_phase = trim($request->ds_phase);
              $condition = array();

              // --- Custom Conditions Setup (Retained & Updated for taluka_code) ---
              if ($ds_phase != '' && $ds_phase > 0) {
                $condition[$personal_table . ".ds_phase"] = $ds_phase;
              }
              $condition[$personal_table . ".created_by_dist_code"] = $district_code;
              $condition[$personal_table . ".created_by_local_body_code"] = $taluka_code; // New condition
              $condition[$contact_table . ".created_by_dist_code"] = $district_code;
              $condition[$contact_table . ".created_by_local_body_code"] = $taluka_code; // New condition
              $condition["is_final"] = true;

              // --- Base Query Construction with Join (Only contact_table) ---
              $query = $personal_modal->where($condition)
                // New condition: wherenuul on next_level_role_id
                ->whereNull($personal_table . '.next_level_role_id')
                ->join($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');
              // Removed aadhar_table join and aadhar_hash check from previous version

              // To prevent column name conflicts after joins, explicitly select fields.
              // We select all from the personal table and the needed fields from contact table.
              $query->select($personal_table . '.*', $contact_table . '.gp_ward_name', $contact_table . '.block_ulb_name'); // block_ulb_name added for filter check

              // --- Custom Filters Application (Retained) ---
              if (!empty($request->filter_1)) {
                // Assuming block_ulb_code is in the contact table
                $query = $query->where($contact_table . '.block_ulb_code', $request->filter_1);
              }
              if (!empty($request->filter_2)) {
                // Assuming gp_ward_code is in the contact table
                $query = $query->where($contact_table . '.gp_ward_code', $request->filter_2);
              }
              if (!empty($request->caste_category)) {
                $query = $query->where('caste', $request->caste_category);
              }
              if ($ds_phase == 0) {
                $query = $query->whereNull($personal_table . '.ds_phase');
              }

              // --- Global Search (Serachvalue) ---
              $serachvalue = $request->input('search.value', '');

              if (!empty($serachvalue)) {
                if (preg_match('/^[0-9]*$/', $serachvalue)) {
                  // Numeric Search Logic (Based on original string length check)
                  $query->where(function ($query1) use ($serachvalue, $personal_table) {
                    if (strlen($serachvalue) < 10) {
                      $query1->where($personal_table . '.application_id', $serachvalue);
                    } else if (strlen($serachvalue) == 10) {
                      $query1->where($personal_table . '.mobile_no', $serachvalue);
                    } else if (strlen($serachvalue) == 17) {
                      $query1->where($personal_table . '.ss_card_no', $serachvalue);
                    } else if (strlen($serachvalue) == 20) {
                      $query1->where($personal_table . '.duare_sarkar_registration_no', $serachvalue);
                    }
                  });
                } else {
                  // String Search Logic (Updated to check ben_fname and gp_ward_name)
                  $query->where(function ($query1) use ($serachvalue, $personal_table, $contact_table) {
                    $query1->where($personal_table . '.ben_fname', 'like', $serachvalue . '%')
                      ->orWhere($contact_table . '.gp_ward_name', 'like', $serachvalue . '%');
                  });
                }
              }

              // Set default ordering, which DataTables will use unless overridden by column sorting
              $query->orderBy($personal_table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name');

              // --- DataTables Execution (The Fix) ---
              // Pass the built query to eloquent(). DataTables handles counting, offset, limit, and sorting automatically.
              return datatables()->eloquent($query)
                // Removed setTotalRecords, setFilteredRecords, and skipPaging()
                ->addColumn('view', function ($data) {
                  $action = '<button class="btn btn-primary ben_view_button" value=' . $data->application_id . '>View</button>';
                  return $action;
                })->addColumn('id', function ($data) {
                  return $data->application_id;
                })
                ->addColumn('name', function ($data) {
                  // Assuming getName() method exists on the $personal_modal Model instance
                  return $data->getName();
                })
                ->addColumn('mobile_no', function ($data) {
                  return $data->mobile_no;
                })
                ->addColumn('duare_sarkar_registration_no', function ($data) {
                  return $data->duare_sarkar_registration_no;
                })
                ->addColumn('age', function ($data) {
                  // Assuming ageCalculate() is a method on the current Controller/Class instance
                  return $this->ageCalculate($data->dob);
                })->addColumn('ss_card_no', function ($data) {
                  return $data->ss_card_no;
                })
                ->rawColumns(['view', 'id', 'name'])
                ->make(true);
            } else {
              return view('processApplication/linelisting_verified_subdiv')
                ->with('duty_level', $duty_level)->with('urban_bodys', $urban_bodys)
                ->with('sessiontimeoutmessage', $errormsg['sessiontimeOut'])
                ->with('dist_code', $district_code)->with('scheme_id', $scheme_id)
                ->with('reject_revert_reason', $reject_revert_reason)
                ->with('dob_base_date', $dob_base_date)
                ->with('ds_phase_list', $ds_phase_list)
                ->with('verification_allowded', $verification_allowded);
            }
          } else {
            $rows = $personal_modal->where('next_level_role_id', null)
              ->where('created_by_local_body_code', $taluka_code)
              ->orderBy('id', 'desc')->paginate(10);
            return view('processApplication/pension_list', ['nhm_employee_details' => $rows, 'reject_revert_reason' => $reject_revert_reason, 'sessiontimeoutmessage' => $errormsg['sessiontimeOut']]);
          }
        }
        /****************************Block************************* */ else if ($mappingLevel == "Block") {
          $duty_level = "BlockVerifier";
          $gps = GP::where('block_code', $taluka_code)->select('gram_panchyat_code', 'gram_panchyat_name')->get();
          if (request()->ajax()) {
            $ds_phase = trim($request->ds_phase);
            $condition = array();

            // --- Custom Conditions Setup ---
            if ($ds_phase != '' && $ds_phase > 0) {
              $condition[$personal_table . ".ds_phase"] = $ds_phase;
            }
            $condition[$personal_table . ".created_by_dist_code"] = $district_code;
            $condition[$personal_table . ".created_by_local_body_code"] = $taluka_code;
            $condition[$contact_table . ".created_by_dist_code"] = $district_code;
            $condition[$contact_table . ".created_by_local_body_code"] = $taluka_code;

            // Note: The original code used $condition["is_final"] = true; AND a direct where clause.
            // We will rely on the direct where clause applied to the query below for simplicity and clarity.

            // --- Base Query Construction with Join ---
            $query = $personal_modal->where($condition)
              // Apply the core conditions
              ->whereNull($personal_table . '.next_level_role_id')
              ->where($personal_table . '.is_final', true)
              ->join($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');

            // To prevent column name conflicts after joins, explicitly select fields.
            // Select all from the personal table and the needed fields from contact table.
            $query->select($personal_table . '.*', $contact_table . '.gp_ward_name');

            // --- Custom Filters Application ---
            if (!empty($request->filter_1)) {
              // Filter_1 is now mapped to gp_ward_code based on your latest logic
              $query = $query->where($contact_table . '.gp_ward_code', $request->filter_1);
            }
            if (!empty($request->caste_category)) {
              $query = $query->where($personal_table . '.caste', $request->caste_category);
            }
            if ($ds_phase == 0) {
              $query = $query->whereNull($personal_table . '.ds_phase');
            }

            // --- Global Search (Serachvalue) ---
            $serachvalue = $request->input('search.value', '');

            if (!empty($serachvalue)) {
              if (preg_match('/^[0-9]*$/', $serachvalue)) {
                // Numeric Search Logic (Based on original string length check)
                $query->where(function ($query1) use ($serachvalue, $personal_table) {
                  if (strlen($serachvalue) < 10) {
                    $query1->where($personal_table . '.application_id', $serachvalue);
                  } else if (strlen($serachvalue) == 10) {
                    $query1->where($personal_table . '.mobile_no', $serachvalue);
                  } else if (strlen($serachvalue) == 17) {
                    $query1->where($personal_table . '.ss_card_no', $serachvalue);
                  } else if (strlen($serachvalue) == 20) {
                    $query1->where($personal_table . '.duare_sarkar_registration_no', $serachvalue);
                  }
                });
              } else {
                // String Search Logic
                $query->where(function ($query1) use ($serachvalue, $personal_table, $contact_table) {
                  $query1->where($personal_table . '.ben_fname', 'like', $serachvalue . '%')
                    ->orWhere($contact_table . '.gp_ward_name', 'like', $serachvalue . '%');
                });
              }
            }

            // Set default ordering (DataTables will use this unless overridden by client-side sorting)
            $query->orderBy($personal_table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name');

            // --- DataTables Execution (The Fix) ---
            // Pass the built query to eloquent(). DataTables handles counting, offset, limit, and sorting automatically.
            return datatables()->eloquent($query)
              // Removed setTotalRecords, setFilteredRecords, and skipPaging()
              ->addColumn('view', function ($data) {
                $action = '<button class="btn btn-primary btn-sm ben_view_button" value=' . $data->application_id . '><i class="glyphicon glyphicon-edit"></i>View</button>';
                return $action;
              })->addColumn('id', function ($data) {
                return $data->application_id;
              })
              ->addColumn('name', function ($data) {
                // Assuming getName() method exists on the Model instance
                return $data->getName();
              })
              ->addColumn('age', function ($data) {
                // Assuming ageCalculate() is a method on the current Controller/Class instance
                return $this->ageCalculate($data->dob);
              })->addColumn('mobile_no', function ($data) {
                return $data->mobile_no;
              })
              ->addColumn('duare_sarkar_registration_no', function ($data) {
                return $data->duare_sarkar_registration_no;
              })->addColumn('ss_card_no', function ($data) {
                return $data->ss_card_no;
              })
              ->rawColumns(['view', 'id', 'name', 'ss_card_no'])
              ->make(true);
          } else {
            return view('processApplication/linelisting_verified')
              ->with('duty_level', $duty_level)->with('gps', $gps)
              ->with('sessiontimeoutmessage', $errormsg['sessiontimeOut'])
              ->with('dist_code', $district_code)->with('scheme_id', $scheme_id)
              ->with('reject_revert_reason', $reject_revert_reason)
              ->with('dob_base_date', $dob_base_date)
              ->with('ds_phase_list', $ds_phase_list)
              ->with('verification_allowded', $verification_allowded);
          }
        }
      } else {
        $approveBtnvisible = 1;
        if ($mappingLevel == "State") {
          $duty_level = "StateApprover";
          // $levels = [
          //   2 => 'Rural',
          //   1 => 'Urban',
          // ];
          if (request()->ajax()) {
            $condition = array();
            $condition['next_level_role_id'] = $role_id;
            $condition["is_final"] = true;

            if (!empty($request->district_code))
              $condition['created_by_dist_code'] = $request->district_code;
            $data = $personal_modal->where('next_level_role_id', $role_id)
              ->where($condition)
              ->get();
            //$data->approveBtnvisible = $approveBtnvisible;
            return datatables()->of($data)
              ->addColumn('view', function ($data) {
                $action = '<a href="' . route('nhmemployee.showApplicantDetails', $data->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> View</a>';



                return $action;
              })
              ->addColumn('check', function ($data) use ($approveBtnvisible) {

                return '<input type="checkbox" name="approvalcheck[]" onchange="document.getElementById(\'bulk_approve\').disabled = !this.checked;" value="' . $data->application_id . '">';
              })
              ->addColumn('id', function ($data) {
                return $data->getBenidAttribute();
              })
              ->addColumn('name', function ($data) {
                return $data->getName();
              })
              ->rawColumns(['view', 'check', 'id', 'name'])
              ->make(true);
          } else {
            $districts = District::select(['district_code', 'district_name'])->get();
          }
        }
        /****************************District************************* */ else if ($mappingLevel == "District") {
          //return redirect("/")->with('error', 'Approval temporary suspended.');
          $duty_level = 'DistrictApprover';
          $levels = [
            2 => 'Rural',
            1 => 'Urban',
          ];

          if (request()->ajax()) {
            $ds_phase = trim($request->ds_phase);
            $condition = array();

            // --- Custom Conditions Setup (Retained) ---
            if ($ds_phase != '' && $ds_phase > 0) {
              $condition[$personal_table . ".ds_phase"] = $ds_phase;
            }
            $condition[$personal_table . ".created_by_dist_code"] = $district_code;
            $condition[$contact_table . ".created_by_dist_code"] = $district_code;
            $condition[$aadhar_table . ".created_by_dist_code"] = $district_code;
            $condition["is_final"] = true;

            // --- Base Query Construction with Joins ---
            $query = $personal_modal->where($condition)
              ->where('next_level_role_id', $role_id)
              ->join($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id')
              ->join($aadhar_table, $aadhar_table . '.application_id', '=', $personal_table . '.application_id')
              ->whereNotNull('aadhar_hash');


            $query->select($personal_table . '.*', $contact_table . '.gp_ward_name', $contact_table . '.block_ulb_name', $contact_table . '.rural_urban_id');


            if (!empty($request->filter_1)) {
              $query = $query->where($contact_table . '.rural_urban_id', $request->filter_1);
            }
            if (!empty($request->filter_2)) {
              $query = $query->where($personal_table . '.created_by_local_body_code', $request->filter_2);
            }
            if (!empty($request->block_ulb_code)) {
              $query = $query->where($contact_table . '.block_ulb_code', $request->block_ulb_code);
            }
            if (!empty($request->gp_ward_code)) {
              $query = $query->where($contact_table . '.gp_ward_code', $request->gp_ward_code);
            }
            if (!empty($request->caste_category)) {
              $query = $query->where('caste', $request->caste_category);
            }
            if ($ds_phase == 0) {
              $query = $query->whereNull($personal_table . '.ds_phase');
            }

            // --- Global Search (Serachvalue) ---
            $serachvalue = $request->input('search.value', '');

            if (!empty($serachvalue)) {
              if (preg_match('/^[0-9]*$/', $serachvalue)) {
                // Numeric Search Logic (Based on original string length check)
                $query->where(function ($query1) use ($serachvalue, $personal_table) {
                  if (strlen($serachvalue) < 10) {
                    $query1->where($personal_table . '.application_id', $serachvalue);
                  } else if (strlen($serachvalue) == 10) {
                    $query1->where($personal_table . '.mobile_no', $serachvalue);
                  } else if (strlen($serachvalue) == 17) {
                    $query1->where($personal_table . '.ss_card_no', $serachvalue);
                  } else if (strlen($serachvalue) == 20) {
                    $query1->where($personal_table . '.duare_sarkar_registration_no', $serachvalue);
                  }
                });
              } else {
                // String Search Logic
                $query->where(function ($query1) use ($serachvalue, $personal_table, $contact_table) {
                  $query1->where($personal_table . '.ben_fname', 'like', $serachvalue . '%')
                    ->orWhere($contact_table . '.block_ulb_name', 'like', $serachvalue . '%');
                });
              }
            }

            // Set default ordering, which DataTables will use unless overridden by column sorting
            $query->orderBy($personal_table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name');

            // --- DataTables Execution (The Fix) ---
            // Pass the built query to eloquent(). DataTables handles counting, offset, limit, and sorting automatically.
            return datatables()->eloquent($query)
              // Removed setTotalRecords, setFilteredRecords, and skipPaging()
              ->addColumn('check', function ($data) use ($approveBtnvisible) {
                // NOTE: $data here is an Eloquent Model instance containing joined data
                return '<input type="checkbox"  name="chkbx" class="all_checkbox"  onclick="controlCheckBox();" value="' . $data->application_id . '">';
              })->addColumn('view', function ($data) {
                $action = '<button class="btn btn-primary btn-sm ben_view_button" value=' . $data->application_id . '><i class="glyphicon glyphicon-edit"></i>View</button>';
                return $action;
              })->addColumn('id', function ($data) {
                return $data->application_id;
              })
              ->addColumn('name', function ($data) {
                // Assuming getName() method exists on the $personal_modal Model instance
                return $data->getName();
              })
              ->addColumn('mobile_no', function ($data) {
                return $data->mobile_no;
              })
              ->addColumn('duare_sarkar_registration_no', function ($data) {
                return $data->duare_sarkar_registration_no;
              })
              ->addColumn('age', function ($data) {
                // Assuming ageCalculate() is a method on the current Controller/Class instance
                return $this->ageCalculate($data->dob);
              })->addColumn('ss_card_no', function ($data) {
                return $data->ss_card_no;
              })
              ->rawColumns(['view', 'check', 'id', 'name', 'ss_card_no'])
              ->make(true);
          }


          return view('processApplication/linelisting_approved')->with('duty_level', $duty_level)
            ->with('levels', $levels)->with('approveBtnvisible', $approveBtnvisible)
            ->with('dist_code', $district_code)->with('scheme_id', $scheme_id)
            ->with('sessiontimeoutmessage', $errormsg['sessiontimeOut'])
            ->with('reject_revert_reason', $reject_revert_reason)
            ->with('dob_base_date', $dob_base_date)
            ->with('ds_phase_list', $ds_phase_list)
            ->with('approval_allowded', $approval_allowded);
        } else {
          if ($is_urban == 1) {
            $duty_level = "ULB";
          } else {
            $duty_level = "Block";
            $rows = $data = $personal_modal->where('next_level_role_id', $role_id)->where('created_by_local_body_code', $taluka_code)->orderBy('id', 'desc')->paginate(10);
            return view('processApplication/linelisting_approved', ['datas' => $rows, 'dist_code' => $district_code, 'reject_revert_reason' => $reject_revert_reason, 'sessiontimeoutmessage' => $errormsg['sessiontimeOut'], 'dob_base_date' => $dob_base_date]);
          }
        }
      }
    } else {
      return redirect('/')->with('success', 'User Disabled for this scheme');
    }
  }











  // public function forwardData(Request $request)
  // {
  //   //dd($request->all());
  //   $this->shemeSessionCheck($request);
  //   $scheme_id = $request->session()->get('scheme_id');
  //   $mappingLevel = $request->session()->get('level');
  //   $district_code = $request->session()->get('distCode');
  //   $is_first = $request->session()->get('is_first');
  //   $is_urban = $request->session()->get('is_urban');
  //   $urban_body_code = $request->session()->get('bodyCode');
  //   $taluka_code = $request->session()->get('bodyCode');
  //   $role_id = $request->session()->get('role_id');
  //   $user_id = Auth::user()->id;
  //   $designation_id = Auth::user()->designation_id;
  //   $id = $request->id;
  //   $Verified = "Verified";
  //   $Rejected = 1;
  //   $comments = $request->comments;
  //   $errormsg = Config::get('constants.errormsg');
  //   $duty = Configduty::where('user_id', '=', $user_id)->where('scheme_id', $scheme_id)->first();
  //   if ($duty->isEmpty) {
  //     return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
  //   }
  //   if ($designation_id == 'Delegated Verifier') {
  //     $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', 'Verifier')->where('stack_level', $duty->mapping_level)->first();
  //   } elseif ($designation_id == 'Delegated Approver') {
  //     $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', 'Approver')->where('stack_level', $duty->mapping_level)->first();
  //   } else {
  //     $role = MapLavel::where('scheme_id', $scheme_id)->where('role_name', $designation_id)->where('stack_level', $duty->mapping_level)->first();
  //   }

  //   if ($role->isEmpty) {
  //     return response()->json(['return_status' => 0, 'return_msg' => $errormsg['notauthorized']]);
  //   }
  //   if ($designation_id == 'Approver' ||  $designation_id == 'Delegated Approver') {
  //     $is_bulk = $request->is_bulk;
  //     if ($is_bulk == 1) {
  //       $is_bulk = 1;
  //     } else {
  //       $is_bulk = 0;
  //       if (empty($id)) {
  //         return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Found']);
  //       }
  //       if (!ctype_digit($id)) {
  //         return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
  //       }
  //     }
  //   } else {
  //     $is_bulk = 0;
  //     if (empty($id)) {
  //       return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Found']);
  //     }
  //     if (!ctype_digit($id)) {
  //       return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid dd']);
  //     }
  //   }


  //   // $modelName = new DataSourceCommon;
  //   // $table2 = 'dist_' . $district_code . '.beneficiary';
  //   // $modelName->setTable('' . $table2);

  //   $getModelFunc = new getModelFunc();
  //   $personal_model = new DataSourceCommon;
  //   $Table = $getModelFunc->getTable($district_code, $this->source_type, 1,  1);
  //   $personal_model->setTable('' . $Table);
  //   $pension_details_aadhar = new DataSourceCommon;
  //   $Table = $getModelFunc->getTable($district_code, $this->source_type, 2, 1);
  //   $pension_details_aadhar->setTable('' . $Table);



  //   $opreation_type = $request->opreation_type;
  //   if ($is_bulk == 1) {
  //   }
  //   //dd($id);
  //   if ($opreation_type == 'A') { //echo 1;die;
  //     //return redirect("/")->with('error', 'Approval temporary suspended.');
  //     $approval_allowded = DistrictEntryMapping::where('main_approval', true)->where('district_code',  $district_code)->count();
  //     if ($approval_allowded == 0) {
  //       return response()->json(['return_status' => 0, 'return_msg' => 'Approval is temporarily suspended']);
  //     }
  //     if ($is_bulk == 0) {
  //       $row = $personal_model->select('application_id')->where('application_id', $id)->where('next_level_role_id', $role->id)->where('created_by_dist_code', $request->session()->get('distCode'))->first();
  //       $count_approved = DB::table('lb_scheme.ben_personal_details')->whereraw("date(approved_at)>='2024-11-21' ")->count();
  //       $count_rejected = DB::table('lb_scheme.ben_reject_details')->whereraw("date(rejection_date)>='2024-11-21' ")->count();
  //       if (($count_approved + $count_rejected) >= 507002) {
  //         return response()->json(['return_status' => 0, 'return_msg' => 'Approval quota has been exceeded']);
  //       }
  //     } else {
  //       $applicant_id_post = request()->input('applicantId');

  //       $applicant_id_in = explode(',', $applicant_id_post);
  //       // print_r( $applicant_id_in);die;
  //       $row_list = $personal_model->select('application_id')->whereIn('application_id', $applicant_id_in)->where('next_level_role_id', $role->id)->where('created_by_dist_code', $request->session()->get('distCode'))->get();
  //       if (count($row_list) != count($applicant_id_in)) {
  //         return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
  //       }
  //       $count_approved = DB::table('lb_scheme.ben_personal_details')->whereraw("date(approved_at)>='2024-11-21' ")->count();
  //       $count_rejected = DB::table('lb_scheme.ben_reject_details')->whereraw("date(rejection_date)>='2024-11-21' ")->count();
  //       if (($count_approved + $count_rejected + count($applicant_id_in)) >= 507002) {
  //         return response()->json(['return_status' => 0, 'return_msg' => 'Approval quota has been exceeded']);
  //       }
  //     }
  //   } else if ($opreation_type == 'V') {
  //     return response()->json(['return_status' => 0, 'return_msg' => 'Verification is temporarily suspended']);
  //     $verification_allowded = DistrictEntryMapping::where('main_verification', true)->where('district_code',  $district_code)->count();
  //     if ($verification_allowded == 0) {
  //       return response()->json(['return_status' => 0, 'return_msg' => 'Verification is temporarily suspended']);
  //     }
  //     if ($is_bulk == 0) {
  //       $row = $personal_model->select('application_id')->where('application_id', $id)->whereNull('next_level_role_id')->where('created_by_dist_code', $request->session()->get('distCode'))->first();
  //       $row_aadhar = $pension_details_aadhar->select('application_id', 'encoded_aadhar', 'aadhar_hash')->where('application_id', $id)->where('created_by_dist_code', $request->session()->get('distCode'))->first();
  //       if (empty($row_aadhar->aadhar_hash)) {
  //         $aadhar_hash = md5(Crypt::decryptString($row_aadhar->encoded_aadhar));
  //         $aadhar_is_update = 1;
  //       } else {
  //         $aadhar_is_update = 0;
  //       }
  //       $count_draft = DB::table('lb_scheme.draft_ben_personal_details')->where('next_level_role_id', 43)->count();
  //       $count_approved = DB::table('lb_scheme.ben_personal_details')->whereraw("date(approved_at)>='2024-11-21' ")->count();
  //       $count_rejected = DB::table('lb_scheme.ben_reject_details')->whereraw("date(rejection_date)>='2024-11-21' ")->count();
  //       if (($count_draft + $count_approved + $count_rejected) >= 507002) {
  //         return response()->json(['return_status' => 0, 'return_msg' => 'Verification quota has been exceeded']);
  //       }
  //     } else {
  //       $applicant_id_post = request()->input('applicantId');

  //       $applicant_id_in = explode(',', $applicant_id_post);

  //       $row_list = $personal_model->select('application_id')->whereIn('application_id', $applicant_id_in)->whereNull('next_level_role_id')->whereNull('next_level_role_id')->where('created_by_dist_code', $request->session()->get('distCode'))->get();
  //       if (count($row_list) != count($applicant_id_in)) {
  //         return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
  //       }
  //       $count_draft = DB::table('lb_scheme.draft_ben_personal_details')->where('next_level_role_id', 43)->count();
  //       $count_approved = DB::table('lb_scheme.ben_personal_details')->whereraw("date(approved_at)>='2024-11-21' ")->count();
  //       $count_rejected = DB::table('lb_scheme.ben_reject_details')->whereraw("date(rejection_date)>='2024-11-21' ")->count();
  //       if (($count_draft + $count_approved + $count_rejected + count($applicant_id_in)) >= 507002) {
  //         return response()->json(['return_status' => 0, 'return_msg' => 'Verification quota has been exceeded']);
  //       }
  //     }
  //   } else if ($opreation_type == 'R') {
  //     if ($is_bulk == 0) {
  //       $row = $personal_model->select('application_id')->where('application_id', $id)->where('created_by_dist_code', $request->session()->get('distCode'))->first();
  //     } else {
  //       $applicant_id_post = request()->input('applicantId');

  //       $applicant_id_in = explode(',', $applicant_id_post);

  //       $row_list = $personal_model->select('application_id')->whereIn('application_id', $applicant_id_in)->where('created_by_dist_code', $request->session()->get('distCode'))->get();
  //       if (count($row_list) != count($applicant_id_in)) {
  //         return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
  //       }
  //     }
  //   } else if ($opreation_type == 'T') {
  //     if ($is_bulk == 0) {
  //       $row = $personal_model->select('application_id')->where('application_id', $id)->where('created_by_dist_code', $request->session()->get('distCode'))->first();
  //     } else {
  //       $applicant_id_post = request()->input('applicantId');

  //       $applicant_id_in = explode(',', $applicant_id_post);

  //       $row_list = $personal_model->select('application_id')->whereIn('application_id', $applicant_id_in)->where('created_by_dist_code', $request->session()->get('distCode'))->get();
  //       if (count($row_list) != count($applicant_id_in)) {
  //         return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
  //       }
  //     }
  //   }
  //   if ($is_bulk == 0 && empty($row->application_id)) {
  //     return response()->json(['return_status' => 0, 'return_msg' => 'Applicant Id Not Valid']);
  //   }
  //   $reject_cause = $request->reject_cause;
  //   $comments = trim($request->accept_reject_comments);


  //   if ($opreation_type == 'A') {
  //     // return redirect("/")->with('error', 'Approval temporary suspended.');
  //     $txt = 'Approved';
  //     $next_level_role_id = $role->parent_id;
  //     $rejected_cause = NULL;
  //     $message = 'Approved Succesfully!';
  //   } else if ($opreation_type == 'V') {
  //     $txt = 'Verified';
  //     $next_level_role_id = $role->parent_id;
  //     $rejected_cause = NULL;
  //     $message = 'Verified Succesfully!';
  //   } else if ($opreation_type == 'R') {
  //     $txt = 'Rejected';
  //     $next_level_role_id = -100;
  //     $rejected_cause = $reject_cause;
  //     $message = 'Rejected Succesfully!';
  //   } else if ($opreation_type == 'T') {
  //     $txt = 'Reverted';
  //     $next_level_role_id = -50;
  //     $rejected_cause = $reject_cause;
  //     $message = 'Reverted Succesfully!';
  //   }
  //   $input = [
  //     'action_by' => Auth::user()->id,
  //     'action_ip_address' => request()->ip(),
  //     'action_type' => class_basename(request()->route()->getAction()['controller']),
  //     'next_level_role_id' => $next_level_role_id,
  //     'rejected_cause' => $rejected_cause,
  //     'comments' => $comments
  //   ];



  //   try {


  //     DB::beginTransaction();
  //     $applicationid_arr = array();
  //     $send_sms_arr = array();
  //     $aadhar_update_status = 1;
  //     if ($is_bulk == 1) {



  //       $is_status_updated = $personal_model->whereIn('application_id', $applicant_id_in)->update($input);
  //       $j = 0;
  //       //dd($row_list->toArray());
  //       foreach ($row_list as $app_row) {
  //         $accept_reject_model = new DataSourceCommon;
  //         $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
  //         $accept_reject_model->setTable('' . $Table);
  //         $accept_reject_model->op_type = $opreation_type;
  //         $accept_reject_model->application_id = $app_row->application_id;
  //         $accept_reject_model->designation_id = $designation_id;
  //         $accept_reject_model->scheme_id = $scheme_id;
  //         $accept_reject_model->user_id = $user_id;
  //         $accept_reject_model->comment_message = $comments;
  //         $accept_reject_model->rejected_reverted_cause = $rejected_cause;
  //         $accept_reject_model->mapping_level = $mappingLevel;
  //         $accept_reject_model->created_by = $user_id;
  //         $accept_reject_model->created_by_level = $mappingLevel;
  //         $accept_reject_model->created_by_dist_code = $district_code;
  //         $accept_reject_model->created_by_local_body_code = $request->session()->get('bodyCode');
  //         $accept_reject_model->ip_address = request()->ip();
  //         $is_saved = $accept_reject_model->save();
  //         if ($is_saved) {
  //           $j++;
  //         }
  //       }
  //       if (count($row_list) == $j) {
  //         $remarks_status = 1;
  //       } else {
  //         $remarks_status = 0;
  //       }
  //       $aadhar_update_status = 1;
  //     } else {
  //       $accept_reject_model = new DataSourceCommon;
  //       $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
  //       $accept_reject_model->setTable('' . $Table);
  //       $is_status_updated = $personal_model->where('application_id', $id)->update($input);
  //       $accept_reject_model->op_type = $opreation_type;
  //       $accept_reject_model->ben_id = $id;
  //       $accept_reject_model->application_id = $row->application_id;
  //       $accept_reject_model->designation_id = $designation_id;
  //       $accept_reject_model->scheme_id = $scheme_id;
  //       $accept_reject_model->user_id = $user_id;
  //       $accept_reject_model->comment_message = $comments;
  //       $accept_reject_model->mapping_level = $mappingLevel;
  //       $accept_reject_model->created_by = $user_id;
  //       $accept_reject_model->created_by_level = $mappingLevel;
  //       $accept_reject_model->created_by_dist_code = $district_code;
  //       $accept_reject_model->created_by_local_body_code = $request->session()->get('bodyCode');
  //       $accept_reject_model->rejected_reverted_cause = $rejected_cause;
  //       $accept_reject_model->ip_address = request()->ip();
  //       $is_saved = $accept_reject_model->save();

  //       if ($is_saved) {
  //         $remarks_status = 1;
  //       } else {
  //         $remarks_status = 0;
  //       }
  //       if ($opreation_type == 'V') {
  //         if ($aadhar_is_update) {
  //           $update_aadhar_arr = array();
  //           $update_aadhar_arr['aadhar_hash'] = $aadhar_hash;
  //           $update_aadhar_arr['action_by'] = Auth::user()->id;
  //           $update_aadhar_arr['action_ip_address'] = request()->ip();
  //           $update_aadhar_arr['action_type'] = class_basename(request()->route()->getAction()['controller']);
  //           try {
  //             $aadhar_update_status = $pension_details_aadhar->where('application_id', $id)->update($update_aadhar_arr);
  //           } catch (\Exception $e) {

  //             DB::rollback();
  //             $return_status = 0;
  //             $return_msg = 'Aadhaar No. is Duplicate..';
  //             return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
  //           }
  //         } else {
  //           $aadhar_update_status = 1;
  //         }
  //       } else {
  //         $aadhar_update_status = 1;
  //       }
  //     }

  //     if ($opreation_type == 'A' || $opreation_type == 'R') {
  //       //echo 1;
  //       if ($is_bulk == 1) { //echo 1;
  //         foreach ($row_list as $app_row) {
  //           array_push($applicationid_arr, $app_row->application_id);
  //         }
  //         $implode_application_arr = implode("','", $applicationid_arr);
  //         $in_pension_id = 'ARRAY[' . "'$implode_application_arr'" . ']';
  //         if ($opreation_type == 'A') { //echo 2;
  //           //$fun_return = DB::select("select lb_scheme.beneficiary_approve_final(" . $in_pension_id . ")");
  //           $fun_return = DB::select("select lb_scheme.beneficiary_approve_final(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '" . request()->ip() . "', in_action_type => '" . class_basename(request()->route()->getAction()['controller']) . "')");
  //         } else { //echo 3;
  //           //$reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final(" . $in_pension_id . ")");
  //           $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '" . request()->ip() . "', in_action_type => '" . class_basename(request()->route()->getAction()['controller']) . "')");
  //         }
  //       } else { // echo 4;
  //         $in_pension_id = 'ARRAY[' . "'$row->application_id'" . ']';
  //         if ($opreation_type == 'A') {  //echo 5;
  //           //$fun_return = DB::select("select lb_scheme.beneficiary_approve_final(" . $in_pension_id . ")");
  //           $fun_return = DB::select("select lb_scheme.beneficiary_approve_final(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '" . request()->ip() . "', in_action_type => '" . class_basename(request()->route()->getAction()['controller']) . "')");
  //         } else { //echo 6;
  //           //$reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final(" . $in_pension_id . ")");
  //           $reject_fun = DB::select("select lb_scheme.beneficiary_rejected_final(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '" . request()->ip() . "', in_action_type => '" . class_basename(request()->route()->getAction()['controller']) . "')");
  //         }
  //       }
  //       $aadhar_update_status = 1;
  //     }

  //     //  print_r($applicant_id_in);die;

  //     if ($is_status_updated && $remarks_status && $aadhar_update_status) {

  //       $return_status = 1;
  //       if ($is_bulk == 1) {
  //         $return_msg = "Applications " . $message;
  //       } else
  //         $return_msg = "Application with ID:" . $row->application_id . " " . $message;
  //     } else {

  //       $return_status = 0;
  //       $return_msg = $errormsg['roolback'];
  //     }
  //     DB::commit();
  //   } catch (\Exception $e) {
  //     //dd($e);
  //     $return_status = 0;
  //     $return_msg = $errormsg['roolback'];
  //     //$return_msg = $e;
  //     DB::rollback();
  //   }
  //   return response()->json(['return_status' => $return_status, 'return_msg' => $return_msg]);
  // }

  public function getBenViewPersonalData(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }


    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {
        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;
        $getModelFunc = new getModelFunc();
        $personal_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
        $personal_model->setConnection('pgsql_appread');
        $personal_model->setTable('' . $Table);
        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Delegated Verifier' || $designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;

        $personaldata = $personal_model->where('application_id', $benid)->where($condition)->first()->toArray();
        // $contactdata = $contact_table->where('application_id', $benid)->first();
        // $bankdata = $bank_table->where('application_id', $benid)->first();
        // $dist_name = District::where('district_code', $contactdata->dist_code)->value('district_name');
        //print_r( $personaldata);die;
        if (!empty($personaldata['dob'])) {
          $extract_dob = Carbon::parse($personaldata['dob'])->format('d/m/Y');
          $personaldata['formatted_dob'] = $extract_dob;
          $personaldata['age_ason_01012021'] = $this->ageCalculate($personaldata['dob']);
        } else {
          $personaldata['formatted_dob'] = '';
          $personaldata['age_ason_01012021'] = '';
        }
        if (!empty($personaldata['duare_sarkar_date'])) {
          $extract_duare_sarkar_date = Carbon::parse($personaldata['duare_sarkar_date'])->format('d/m/Y');
          $personaldata['formatted_duare_sarkar_date'] = $extract_duare_sarkar_date;
        } else {
          $personaldata['formatted_duare_sarkar_date'] = '';
        }
      }
      $response = array('personaldata' => $personaldata, 'benid' => $benid);
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
  public function getBenViewContactData(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }


    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {
        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;
        $getModelFunc = new getModelFunc();
        $contact_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 3, 1);
        $contact_model->setConnection('pgsql_appread');
        $contact_model->setTable('' . $Table);
        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Delegated Verifier' || $designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;
        $contactdata = $contact_model->where('application_id', $benid)->where($condition)->first()->toArray();
        $dist_name = District::select('district_name')->where('district_code', $contactdata['dist_code'])->first();
        $contactdata['dist_name'] = $dist_name->district_name;
      }
      $response = array('contactdata' => $contactdata, 'benid' => $benid);
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
  public function getBenViewBankData(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }


    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {

        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;

        $getModelFunc = new getModelFunc();
        $bank_model = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 4, 1);
        $bank_model->setConnection('pgsql_appread');
        $bank_model->setTable('' . $Table);
        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Delegated Verifier' || $designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;
        $bankdata = $bank_model->where('application_id', $benid)->where($condition)->first()->toArray();
      }
      $response = array('bankdata' => $bankdata, 'benid' => $benid);
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
  public function getBenViewEncloserData(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];

    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }
    $scheme_id = $this->scheme_id;
    $benid = $request->benid;
    try {
      $html = '';
      if (!empty($benid)) {
        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;

        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Delegated Verifier' || $designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;
        $is_draft = 1;
        $getModelFunc = new getModelFunc();
        $DraftPfImageTable = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 5, $is_draft);
        $DraftPfImageTable->setConnection('pgsql_encread');
        $DraftPfImageTable->setTable('' . $Table);
        $DraftEncloserTable = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 6, $is_draft);
        $DraftEncloserTable->setConnection('pgsql_encread');
        $DraftEncloserTable->setTable('' . $Table);
        $doc_arr = array();
        $encloserdata_arr = array();
        $doc_profile = DocumentType::select('id')->where('is_profile_pic', TRUE)->first();
        $profileImageCnt = $DraftPfImageTable->where('image_type', $doc_profile->id)->where('application_id', $benid)->where($condition)->count();
        if ($profileImageCnt) {
          array_push($doc_arr, $doc_profile->id);
        }
        $encolserdata = $DraftEncloserTable->select('document_type')->where('application_id', $benid)->where($condition)->get()->pluck('document_type')->toArray();
        if (count($encolserdata) > 0) {
          foreach ($encolserdata as $en) {
            array_push($doc_arr, $en);
          }
        }
        if (count($doc_arr) > 0) {
          $encloserdata = DocumentType::select('id', 'doc_name', 'is_profile_pic')->whereIn('id', $doc_arr)->get();
        }
      }
      if (count($encloserdata) > 0) {
        $p = 0;
        $html = '';
        foreach ($encloserdata as $enc) {
          if ($p == 0 || ($p % 2 == 0)) {
            $html = $html . '<tr>';
          }
          $html = $html . '    
          <th scope="row">' . $enc->doc_name . '</th>
          <td  scope="row" class="encView">&nbsp;&nbsp;&nbsp;<a class="btn btn-xs btn-primary" href="javascript:void(0);" onclick="View_encolser_modal(\'' . $enc->doc_name . '\',' . $enc->id . ',' . intval($enc->is_profile_pic) . ',' . $benid . ')">View</a></td>';
          if (($p % 2 == 0) && ($p % 2 != 0)) {
            $html = $html . '</tr>';
          }
          $p++;
        }
      }

      $response = array('html' => $html, 'benid' => $benid);
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
  public function getBenViewAadharData(Request $request)
  { //echo 1;die;
    $statusCode = 200;
    $response = [];
    $benid = $request->benid;
    $scheme_id = $this->scheme_id;
    if (!$request->ajax()) {
      $statusCode = 400;
      $response = array('error' => 'Error occured in form submit.');
      return response()->json($response, $statusCode);
    }
    try {
      if (!empty($benid)) {
        $this->shemeSessionCheck($request);
        $district_code = $request->session()->get('distCode');
        $is_urban = $request->session()->get('is_urban');
        if ($is_urban == 1) {
          $urban_body_code = $request->session()->get('bodyCode');
          $body_code = $urban_body_code;
        } else if ($is_urban == 2) {
          $taluka_code = $request->session()->get('bodyCode');
          $body_code = $taluka_code;
        }
        $designation_id = Auth::user()->designation_id;

        $condition = array();
        $condition['created_by_dist_code'] = $district_code;
        if ($designation_id == 'Delegated Verifier' || $designation_id == 'Verifier' || $designation_id == 'Operator')
          $condition['created_by_local_body_code'] = $body_code;
        $getModelFunc = new getModelFunc();

        $AadharObj = new DataSourceCommon;
        $Table = $getModelFunc->getTable($district_code, $this->source_type, 2);
        $AadharObj->setConnection('pgsql_appread');
        $AadharObj->setTable('' . $Table);
        $aadhardata = $AadharObj->where('application_id', $benid)->where($condition)->first();
        //dd($aadhardata);
        $aadhar_no = Crypt::decryptString($aadhardata->encoded_aadhar);
      }
      $response = array('aadhar_no' => $aadhar_no);
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
  function ageCalculate($dob)
  {
    $diff = 0;
    if ($dob != '') {
      //$diff = $this->ageCalculate($dob);
      $diff = Carbon::parse($dob)->diffInYears($this->base_dob_chk_date);
    }
    return $diff;
  }
}
