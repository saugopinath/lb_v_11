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
use App\Helpers\Helper;
use App\Models\DataSourceCommon;
use App\Models\DsPhase;
use App\Models\RejectRevertReason;
use App\Models\Scheme;
use Carbon\Carbon;

class StopBeneficiaryController extends Controller
{
  public function __construct()
  {
    set_time_limit(60);
    $this->middleware('auth');
    $this->source_type = 'ss_nfsa';
    $this->scheme_id = 20;
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
      // $ds_phase_list = Config::get('constants.ds_phase.phaselist');
      $ds_phase_list = DsPhase::get()->pluck('phase_des', 'phase_code');

      if ($this->schemeSessionCheck($request)) {
        $mappingLevel = $request->session()->get('level');
        $role_name = Auth::user()->designation_id;
        //$rejection_cause_list = Config::get('constants.rejection_cause');
        $rejection_cause_list = RejectRevertReason::where('status', true)->get()->toArray();
        $is_rural_visible = 0;
        $urban_visible = 0;
        $munc_visible = 0;
        $gp_ward_visible = 0;
        $muncList = collect([]);
        $gpwardList = collect([]);
        $modelName = new DataSourceCommon;
        $getModelFunc = new getModelFunc();
        $caste = $request->caste;
        $block_ulb_code = $request->block_ulb_code;
        $gp_ward_code = $request->gp_ward_code;
        $download_excel = 1;
        if ($role_name == 'Approver' || $role_name == 'Delegated Approver') {
          $is_urban = $request->rural_urbanid;
          $district_code = $request->session()->get('distCode');
          $urban_body_code = $request->urban_body_code;
          $block_ulb_code = $request->block_ulb_code;
          $is_rural_visible = 1;
          $urban_visible = 1;
          $munc_visible = 1;
          $gp_ward_visible = 1;
        } else if ($role_name == 'Verifier' || $role_name == 'Delegated Verifier' || $role_name == 'Operator') {
          $district_code = $request->session()->get('distCode');
          if ($mappingLevel == 'Block') {
            $block_ulb_code = NULL;
            $is_rural_visible = 0;
            $is_urban = 2;
            $munc_visible = 0;
            $urban_body_code = $request->session()->get('bodyCode');
            $block_ulb_code = NULL;
            $gpwardList = GP::where('block_code', $urban_body_code)->get();
            $gp_ward_visible = 1;
          } else if ($mappingLevel == 'Subdiv') {
            $block_ulb_code = $request->block_ulb_code;
            $urban_body_code = $request->session()->get('bodyCode');
            $is_rural_visible = 0;
            $is_urban = 1;
            $munc_visible = 1;
            $gp_ward_visible = 1;
            $muncList = UrbanBody::where('sub_district_code', $urban_body_code)->get();
            $block_ulb_code = $request->block_ulb_code;
          }
        }
        $condition = array();
        $report_type = $request->report_type;
        if ($report_type == 1) {
          $report_type_name = 'Acount Validation Failed';
        } else if ($report_type == 2) {
          $report_type_name = 'Payment Validation Failed';
        } else if ($report_type == 3) {
          $report_type_name = 'Dupliate Bank';
        } else if ($report_type == 4) {
          $report_type_name = 'Dupliate Aadhaar';
        } else if ($report_type == 5) {
          $report_type_name = 'Under Caste Modification';
        } else if ($report_type == 6) {
          $report_type_name = 'Deactivated';
        } else if ($report_type == 7) {
          $report_type_name = 'Name Validation Rejection';
        } else if ($report_type == 8) {
          $report_type_name = 'Duplicate Bank Rejection';
        } else if ($report_type == 9) {
          $report_type_name = 'Rejection';
        } else {
          $report_type_name = 'Acount Validation Failed';
          $report_type = 1;
        }



        if (request()->ajax()) {
          // dd($request->all());
          $condition = array();
          $blocktable = 'public.m_block';
          $munctable = 'public.m_urban_body';
          $gptable = 'public.m_gp';
          $wardtable = 'public.m_urban_body_ward';

          // $condition[edited_status"] = $district_code;
          if ($report_type == 1 || $report_type == 2) {
            $Table = 'lb_main.failed_payment_details';


            $condition = [];

            if (!empty($request->ds_phase)) {
              $condition["ds_phase"] = $request->ds_phase;
            }

            if (!empty($district_code)) {
              $condition[$Table . ".dist_code"] = $district_code;
            }

            if (!empty($is_urban)) {
              if ($is_urban == 2 && !empty($urban_body_code)) {
                $condition[$Table . ".local_body_code"] = $urban_body_code;
              }

              if ($is_urban == 1 && !empty($urban_body_code)) {
                $condition[$Table . ".local_body_code"] = $urban_body_code;
              }
            }

            if (!empty($gp_ward_code)) {
              $condition[$Table . ".gp_ward_code"] = $gp_ward_code;
            }

            if ($report_type == 1) {
              $condition[$Table . ".failed_type"] = 1;
            } else if ($report_type == 2) {
              $condition[$Table . ".failed_type"] = 2;
            }


            $modelName->setConnection('pgsql_payment');
            $modelName->setTable($Table);

            $schemaname = $getModelFunc->getSchemaDetails();
            $paymenttable = $schemaname . '.ben_payment_details';
            // $search = $request->search['value'];
            $search = $request->search_value;


            $query = $modelName->where($condition)
              ->whereIn('edited_status', [0, 1])
              ->where($paymenttable . ".ben_status", 1)
              ->join($paymenttable, $paymenttable . '.ben_id', '=', $Table . '.ben_id')
              ->leftjoin($blocktable, $blocktable . '.block_code', '=', $paymenttable . '.block_ulb_code')
              ->leftjoin($munctable, $munctable . '.urban_body_code', '=', $paymenttable . '.block_ulb_code')
              ->leftjoin($gptable, $gptable . '.gram_panchyat_code', '=', $paymenttable . '.gp_ward_code')
              ->leftjoin($wardtable, $wardtable . '.urban_body_ward_code', '=', $paymenttable . '.gp_ward_code')
              ->select([
                $paymenttable . '.application_id',
                $paymenttable . '.ben_name',
                $paymenttable . '.mobile_no',
                $paymenttable . '.last_accno as bank_code',
                $paymenttable . '.last_ifsc as bank_ifsc',
                $paymenttable . '.rural_urban_id',
                $paymenttable . '.gp_ward_code',
                $paymenttable . '.block_ulb_code',
                $Table . '.edited_status',
                $Table . '.failed_type',
                $Table . '.created_at as lot_created_at',
                $Table . '.lot_month',
                $blocktable . '.block_name',
                $munctable . '.urban_body_name as munc_name',
                $gptable . '.gram_panchyat_name as gp_name',
                $wardtable . '.urban_body_ward_name as ward_name',
              ]);





            if (!empty($search)) {
              if (preg_match('/^[0-9]+$/', $search)) {
                // Numeric search - check application_id or mobile_no
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  if (strlen($search) < 10) {
                    $query1->where($paymenttable . '.application_id', $search);
                  } else if (strlen($search) == 10) {
                    $query1->where($paymenttable . '.mobile_no', $search);
                  }
                });
              } else {
                // Non-numeric search - search by beneficiary name
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  $query1->where($paymenttable . '.ben_name', 'ilike', $search . '%');
                });
              }
            }




          } else if ($report_type == 3) {
            $Table = 'lb_main.ben_payment_details_bank_code_dup';
            $modelName->setConnection('pgsql_payment');
            $modelName->setTable($Table);

            $schemaname = $getModelFunc->getSchemaDetails();
            $paymenttable = $schemaname . '.ben_payment_details';

            $condition = [];


            if (!empty($request->ds_phase)) {
              $condition[$paymenttable . ".ds_phase"] = $request->ds_phase;
            }

            if (!empty($district_code)) {
              $condition[$Table . ".dist_code"] = $district_code;
            }

            if (!empty($is_urban)) {
              if ($is_urban == 2 && !empty($urban_body_code)) {
                $condition[$Table . ".local_body_code"] = $urban_body_code;
              }
              if ($is_urban == 1 && !empty($urban_body_code)) {
                $condition[$Table . ".local_body_code"] = $urban_body_code;
              }
              if ($is_urban == 1 && !empty($block_ulb_code)) {
                $condition[$Table . ".block_ulb_code"] = $block_ulb_code;
              }
            }

            if (!empty($gp_ward_code)) {
              $condition[$Table . ".gp_ward_code"] = $gp_ward_code;
            }

            $search = $request->search['value'];


            $query = $modelName->where($condition)
              ->where($paymenttable . ".ben_status", -97)
              ->join($paymenttable, $paymenttable . '.ben_id', '=', $Table . '.ben_id')
              ->leftjoin($blocktable, $blocktable . '.block_code', '=', $paymenttable . '.block_ulb_code')
              ->leftjoin($munctable, $munctable . '.urban_body_code', '=', $paymenttable . '.block_ulb_code')
              ->leftjoin($gptable, $gptable . '.gram_panchyat_code', '=', $paymenttable . '.gp_ward_code')
              ->leftjoin($wardtable, $wardtable . '.urban_body_ward_code', '=', $paymenttable . '.gp_ward_code')
              ->select([
                $paymenttable . '.application_id',
                $paymenttable . '.ben_name',
                $paymenttable . '.mobile_no',
                $paymenttable . '.last_accno as bank_code',
                $paymenttable . '.last_ifsc as bank_ifsc',
                $paymenttable . '.rural_urban_id',
                $paymenttable . '.gp_ward_code',
                $paymenttable . '.block_ulb_code',
                $blocktable . '.block_name',
                $munctable . '.urban_body_name as munc_name',
                $gptable . '.gram_panchyat_name as gp_name',
                $wardtable . '.urban_body_ward_name as ward_name'
              ]);


            if (!empty($search)) {
              if (preg_match('/^[0-9]+$/', $search)) {
                // Numeric search - check application_id or mobile_no
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  if (strlen($search) < 10) {
                    $query1->where($paymenttable . '.application_id', $search);
                  } else if (strlen($search) == 10) {
                    $query1->where($paymenttable . '.mobile_no', $search);
                  }
                });
              } else {
                // Non-numeric search - search by beneficiary name
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  $query1->where($paymenttable . '.ben_name', 'ilike', $search . '%');
                });
              }
            }

          } else if ($report_type == 4) {
            $schemaname = $getModelFunc->getSchemaDetails();
            $paymenttable = $schemaname . '.ben_payment_details';

            $modelName->setConnection('pgsql_payment');
            $modelName->setTable($paymenttable);

            $condition = [];


            if (!empty($request->ds_phase)) {
              $condition[$paymenttable . ".ds_phase"] = $request->ds_phase;
            }

            if (!empty($district_code)) {
              $condition[$paymenttable . ".dist_code"] = $district_code;
            }

            if (!empty($is_urban)) {
              if ($is_urban == 2 && !empty($urban_body_code)) {
                $condition[$paymenttable . ".local_body_code"] = $urban_body_code;
              }
              if ($is_urban == 1 && !empty($urban_body_code)) {
                $condition[$paymenttable . ".local_body_code"] = $urban_body_code;
              }
              if ($is_urban == 1 && !empty($block_ulb_code)) {
                $condition[$paymenttable . ".block_ulb_code"] = $block_ulb_code;
              }
            }

            if (!empty($gp_ward_code)) {
              $condition[$paymenttable . ".gp_ward_code"] = $gp_ward_code;
            }

            $search = $request->search['value'];


            $query = $modelName
              ->where($condition)
              ->where($paymenttable . ".ben_status", 0)
              ->leftjoin($blocktable, $blocktable . '.block_code', '=', $paymenttable . '.block_ulb_code')
              ->leftjoin($munctable, $munctable . '.urban_body_code', '=', $paymenttable . '.block_ulb_code')
              ->leftjoin($gptable, $gptable . '.gram_panchyat_code', '=', $paymenttable . '.gp_ward_code')
              ->leftjoin($wardtable, $wardtable . '.urban_body_ward_code', '=', $paymenttable . '.gp_ward_code')
              ->select([
                $paymenttable . '.application_id',
                $paymenttable . '.ben_name',
                $paymenttable . '.mobile_no',
                $paymenttable . '.last_accno as bank_code',
                $paymenttable . '.last_ifsc as bank_ifsc',
                $paymenttable . '.rural_urban_id',
                $paymenttable . '.gp_ward_code',
                $paymenttable . '.block_ulb_code',
                $blocktable . '.block_name',
                $munctable . '.urban_body_name as munc_name',
                $gptable . '.gram_panchyat_name as gp_name',
                $wardtable . '.urban_body_ward_name as ward_name'
              ]);


            if (!empty($search)) {
              if (preg_match('/^[0-9]+$/', $search)) {
                // Numeric search - check application_id or mobile_no
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  if (strlen($search) < 10) {
                    $query1->where($paymenttable . '.application_id', $search);
                  } else if (strlen($search) == 10) {
                    $query1->where($paymenttable . '.mobile_no', $search);
                  }
                });
              } else {
                // Non-numeric search - search by beneficiary name
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  $query1->where($paymenttable . '.ben_name', 'ilike', $search . '%');
                });
              }
            }

          } else if ($report_type == 5) {
            $paymenttable = 'lb_scheme.ben_caste_modification_track';

            $modelName->setConnection('pgsql_appread');
            $modelName->setTable($paymenttable);

            $condition = [];
            if (!empty($request->ds_phase)) {
              $condition[$paymenttable . ".ds_phase"] = $request->ds_phase;
            }

            if (!empty($district_code)) {
              $condition[$paymenttable . ".created_by_dist_code"] = $district_code;
            }

            if (!empty($is_urban)) {
              if ($is_urban == 2 && !empty($urban_body_code)) {
                $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
              }

              if ($is_urban == 1 && !empty($urban_body_code)) {
                $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
              }

              if ($is_urban == 1 && !empty($block_ulb_code)) {
                $condition[$paymenttable . ".block_ulb_code"] = $block_ulb_code;
              }
            }

            if (!empty($gp_ward_code)) {
              $condition[$paymenttable . ".gp_ward_code"] = $gp_ward_code;
            }

            $search = $request->search['value'];
            $query = $modelName
              ->where($condition)
              ->whereraw("(next_level_role_id_caste IS NULL or next_level_role_id_caste>0 or next_level_role_id_caste=-50)")
              ->select([
                $paymenttable . '.application_id',
                $paymenttable . '.ben_name',
                $paymenttable . '.mobile_no',
                $paymenttable . '.rural_urban_id',
                $paymenttable . '.block_ulb_code',
                $paymenttable . '.block_ulb_name as block_name',
                $paymenttable . '.block_ulb_name as munc_name',
                $paymenttable . '.gp_ward_code',
                $paymenttable . '.gp_ward_name as gp_name',
                $paymenttable . '.gp_ward_name as ward_name',
                $paymenttable . '.next_level_role_id_caste'
              ]);

            if (!empty($search)) {
              if (preg_match('/^[0-9]+$/', $search)) {
                // Numeric search - check application_id or mobile_no
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  if (strlen($search) < 10) {
                    $query1->where($paymenttable . '.application_id', $search);
                  } else if (strlen($search) == 10) {
                    $query1->where($paymenttable . '.mobile_no', $search);
                  }
                });
              } else {
                // Non-numeric search - search by beneficiary name
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  $query1->where($paymenttable . '.ben_name', 'ilike', $search . '%');
                });
              }
            }
          } else if ($report_type == 6) {
            $paymenttable = 'lb_scheme.ben_reject_details';

            $modelName->setConnection('pgsql_appread');
            $modelName->setTable($paymenttable);

            $condition = [];
            if (!empty($request->ds_phase)) {
              $condition[$paymenttable . ".ds_phase"] = $request->ds_phase;
            }

            if (!empty($district_code)) {
              $condition[$paymenttable . ".created_by_dist_code"] = $district_code;
            }

            if (!empty($is_urban)) {

              if ($is_urban == 2 && !empty($urban_body_code)) {
                $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
              }

              if ($is_urban == 1 && !empty($urban_body_code)) {
                $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
              }

              if ($is_urban == 1 && !empty($block_ulb_code)) {
                $condition[$paymenttable . ".block_ulb_code"] = $block_ulb_code;
              }
            }

            if (!empty($gp_ward_code)) {
              $condition[$paymenttable . ".gp_ward_code"] = $gp_ward_code;
            }

            $search = $request->search['value'];
            $query = $modelName
              ->where($condition)
              ->where("next_level_role_id", -99)
              ->select([
                $paymenttable . '.application_id',
                $paymenttable . '.ben_fname as ben_name',
                $paymenttable . '.mobile_no',
                $paymenttable . '.rural_urban_id',
                $paymenttable . '.block_ulb_code',
                $paymenttable . '.block_ulb_name as block_name',
                $paymenttable . '.block_ulb_name as munc_name',
                $paymenttable . '.gp_ward_code',
                $paymenttable . '.gp_ward_name as gp_name',
                $paymenttable . '.gp_ward_name as ward_name',
                $paymenttable . '.next_level_role_id'
              ]);

            if (!empty($search)) {
              if (preg_match('/^[0-9]+$/', $search)) {
                // Numeric search - check application_id or mobile_no
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  if (strlen($search) < 10) {
                    $query1->where($paymenttable . '.application_id', $search);
                  } else if (strlen($search) == 10) {
                    $query1->where($paymenttable . '.mobile_no', $search);
                  }
                });
              } else {
                // Non-numeric search - search by beneficiary name
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  $query1->where($paymenttable . '.ben_name', 'ilike', $search . '%');
                });
              }
            }

          } else if ($report_type == 7) {
            $paymenttable = 'lb_scheme.ben_reject_details';

            $modelName->setConnection('pgsql_appread');
            $modelName->setTable($paymenttable);

            $condition = [];

            if (!empty($request->ds_phase)) {
              $condition[$paymenttable . ".ds_phase"] = $request->ds_phase;
            }

            if (!empty($district_code)) {
              $condition[$paymenttable . ".created_by_dist_code"] = $district_code;
            }

            if (!empty($is_urban)) {

              if ($is_urban == 2 && !empty($urban_body_code)) {
                $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
              }

              if ($is_urban == 1 && !empty($urban_body_code)) {
                $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
              }

              if ($is_urban == 1 && !empty($block_ulb_code)) {
                $condition[$paymenttable . ".block_ulb_code"] = $block_ulb_code;
              }
            }

            if (!empty($gp_ward_code)) {
              $condition[$paymenttable . ".gp_ward_code"] = $gp_ward_code;
            }

            $search = $request->search['value'];

            $query = $modelName
              ->where($condition)
              ->where("next_level_role_id", -400)
              ->select([
                $paymenttable . '.application_id',
                $paymenttable . '.ben_fname as ben_name',
                $paymenttable . '.mobile_no',
                $paymenttable . '.rural_urban_id',
                $paymenttable . '.block_ulb_code',
                $paymenttable . '.block_ulb_name as block_name',
                $paymenttable . '.block_ulb_name as munc_name',
                $paymenttable . '.gp_ward_code',
                $paymenttable . '.gp_ward_name as gp_name',
                $paymenttable . '.gp_ward_name as ward_name',
                $paymenttable . '.next_level_role_id'
              ]);

            if (!empty($search)) {
              if (preg_match('/^[0-9]+$/', $search)) {
                // Numeric search - check application_id or mobile_no
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  if (strlen($search) < 10) {
                    $query1->where($paymenttable . '.application_id', $search);
                  } else if (strlen($search) == 10) {
                    $query1->where($paymenttable . '.mobile_no', $search);
                  }
                });
              } else {
                // Non-numeric search - search by beneficiary name
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  $query1->where($paymenttable . '.ben_name', 'ilike', $search . '%');
                });
              }
            }
          } else if ($report_type == 8) {
            $Table = 'lb_main.ben_payment_details_bank_code_dup';
            $paymenttable = $Table;

            $modelName->setConnection('pgsql_payment');
            $modelName->setTable($Table);

            $condition = [];

            if (!empty($district_code)) {
              $condition[$Table . ".dist_code"] = $district_code;
            }

            if (!empty($is_urban)) {

              if ($is_urban == 2 && !empty($urban_body_code)) {
                $condition[$Table . ".local_body_code"] = $urban_body_code;
              }

              if ($is_urban == 1 && !empty($urban_body_code)) {
                $condition[$Table . ".local_body_code"] = $urban_body_code;
              }

              if ($is_urban == 1 && !empty($block_ulb_code)) {
                $condition[$Table . ".block_ulb_code"] = $block_ulb_code;
              }
            }

            if (!empty($gp_ward_code)) {
              $condition[$Table . ".gp_ward_code"] = $gp_ward_code;
            }

            $search = $request->search['value'];
            $query = $modelName
              ->where($condition)
              ->whereIn("ben_status", [-98, -99])
              ->leftJoin($blocktable, $blocktable . '.block_code', '=', $paymenttable . '.block_ulb_code')
              ->leftJoin($munctable, $munctable . '.urban_body_code', '=', $paymenttable . '.block_ulb_code')
              ->leftJoin($gptable, $gptable . '.gram_panchyat_code', '=', $paymenttable . '.gp_ward_code')
              ->leftJoin($wardtable, $wardtable . '.urban_body_ward_code', '=', $paymenttable . '.gp_ward_code')
              ->select([
                $paymenttable . '.application_id',
                $paymenttable . '.ben_name',
                $paymenttable . '.mobile_no',
                $paymenttable . '.last_accno as bank_code',
                $paymenttable . '.last_ifsc as bank_ifsc',
                $paymenttable . '.rural_urban_id',
                $paymenttable . '.gp_ward_code',
                $paymenttable . '.block_ulb_code',
                $blocktable . '.block_name',
                $munctable . '.urban_body_name as munc_name',
                $gptable . '.gram_panchyat_name as gp_name',
                $wardtable . '.urban_body_ward_name as ward_name',
                $paymenttable . '.rejected_date'
              ]);

            if (!empty($search)) {
              if (preg_match('/^[0-9]+$/', $search)) {
                // Numeric search - check application_id or mobile_no
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  if (strlen($search) < 10) {
                    $query1->where($paymenttable . '.application_id', $search);
                  } else if (strlen($search) == 10) {
                    $query1->where($paymenttable . '.mobile_no', $search);
                  }
                });
              } else {
                // Non-numeric search - search by beneficiary name
                $query = $query->where(function ($query1) use ($search, $paymenttable) {
                  $query1->where($paymenttable . '.ben_name', 'ilike', $search . '%');
                });
              }
            }

          } else if ($report_type == 9) {
            $report_type_name = 'Rejection';
          } else {
            $report_type_name = 'Rejection';
          }






          return datatables()
            ->eloquent($query)
            ->addColumn('application_id', function ($data) use ($report_type) {

              return $data->application_id;
            })
            ->addColumn('name', function ($data) {
              return ($data->ben_name);
            })
            ->addColumn('mobile_no', function ($data) use ($report_type) {

              return $data->mobile_no;
            })->addColumn('block_munc_name', function ($data) use ($report_type) {
              $description = '';
              if ($data->rural_urban_id == 1) {
                $description = $data->munc_name;
              } else if ($data->rural_urban_id == 2) {
                $description = $data->block_name;
              }
              return $description;
            })->addColumn('gp_ward_name', function ($data) use ($report_type) {
              $description = '';
              if ($data->rural_urban_id == 1) {
                $description = $data->ward_name;
              } else if ($data->rural_urban_id == 2) {
                $description = $data->gp_name;
              }
              return $description;
            })->addColumn('status', function ($data) use ($report_type) {
              $description = '';
              if ($report_type == 1 || $report_type == 2) {
                if ($data->edited_status == 0)
                  $description = 'Pending at Verifier';
                else if ($data->edited_status == 1)
                  $description = 'Pending at Approver';
              } else if ($report_type == 3 || $report_type == 4) {
                $description = 'Pending at Verifier/Approver';
              } else if ($report_type == 5) {
                if (is_int($data->next_level_role_id_caste) && $data->next_level_role_id_caste > 0) {
                  $description = 'Pending at Approver';
                } else
                  $description = 'Pending at Verifier';
              } else if ($report_type == 6) {
                $description = 'Rejected by Approver';
              } else if ($report_type == 7) {
                $description = 'Rejected by Approver';
              } else if ($report_type == 8) {
                $description = 'Rejected on ' . $data->rejected_date;
              }
              return $description;
            })->make(true);

        } else {
          $errormsg = Config::get('constants.errormsg');
          $scheme_name_arr = Scheme::select('scheme_name')->where('id', $this->scheme_id)->first();
          return view('stop-beneficiary.listReport')
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
            ->with('scheme_name', $scheme_name_arr->scheme_name)
            ->with('ds_phase_list', $ds_phase_list)
            ->with('download_excel', $download_excel);
        }
      } else {
        return redirect('/')->with('error', 'User not Authorized for this scheme');
      }
    } catch (\Exception $e) {
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
      $role_name = Auth::user()->designation_id;
      $scheme_name_row = Scheme::where('id', $scheme_id)->first();
      $scheme_name = $scheme_name_row->scheme_name;
      $report_type = $request->report_type;
      if ($report_type == 1) {
        $report_type_name = 'Acount Validation Failed';
      } else if ($report_type == 2) {
        $report_type_name = 'Payment Validation Failed';
      } else if ($report_type == 3) {
        $report_type_name = 'Dupliate Bank';
      } else if ($report_type == 4) {
        $report_type_name = 'Dupliate Aadhaar';
      } else if ($report_type == 5) {
        $report_type_name = 'Under Caste Modification';
      } else if ($report_type == 6) {
        $report_type_name = 'Deactivated';
      } else if ($report_type == 7) {
        $report_type_name = 'Name Validation Rejection';
      } else if ($report_type == 8) {
        $report_type_name = 'Duplicate Bank Rejection';
      } else if ($report_type == 9) {
        $report_type_name = 'Rejection';
      } else {
        $report_type_name = 'Acount Validation Failed';
        $report_type = 1;
      }
      $blocktable = 'public.m_block';
      $munctable = 'public.m_urban_body';
      $gptable = 'public.m_gp';
      $wardtable = 'public.m_urban_body_ward';
      $is_urban = $request->rural_urbanid;
      $urban_body_code = $request->urban_body_code;
      $block_ulb_code = $request->block_ulb_code;
      $gp_ward_code = $request->gp_ward_code;
      // $condition[edited_status"] = $district_code;
      if ($report_type == 1 || $report_type == 2) {
        $Table = 'lb_main.failed_payment_details';
        if (!empty($request->ds_phase)) {
          $condition["ds_phase"] = $request->ds_phase;
        }
        if (!empty($district_code)) {
          $condition[$Table . ".dist_code"] = $district_code;
        }
        if (!empty($is_urban)) {
          // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
          if ($is_urban == 2) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 2;
              $condition[$Table . ".local_body_code"] = $urban_body_code;
            }
          }
          //'Urban'
          if ($is_urban == 1) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 1;
              $condition[$Table . ".local_body_code"] = $urban_body_code;
            }
            // if (!empty($block_ulb_code)) {
            //   $condition[$Table . ".block_ulb_code"] = $block_ulb_code;
            // }
          }
        }
        if (!empty($gp_ward_code)) {
          $condition[$Table . ".gp_ward_code"] = $gp_ward_code;
        }
        if ($report_type == 1) {
          $condition[$Table . ".failed_type"] = 1;
        } else if ($report_type == 2) {
          $condition[$Table . ".failed_type"] = 2;
        }
        $modelName->setConnection('pgsql_payment');
        $modelName->setTable('' . $Table);
        $schemaname = $getModelFunc->getSchemaDetails();
        $paymenttable = $schemaname . '.ben_payment_details';

        $data = array();
        $query = $modelName->where($condition)->whereIn('edited_status', [0, 1])->where($paymenttable . ".ben_status", 1);
        $query = $query->join($paymenttable, $paymenttable . '.ben_id', '=', $Table . '.ben_id');
        $query = $query->leftjoin($blocktable, $blocktable . '.block_code', '=', $paymenttable . '.block_ulb_code');
        $query = $query->leftjoin($munctable, $munctable . '.urban_body_code', '=', $paymenttable . '.block_ulb_code');
        $query = $query->leftjoin($gptable, $gptable . '.gram_panchyat_code', '=', $paymenttable . '.gp_ward_code');
        $query = $query->leftjoin($wardtable, $wardtable . '.urban_body_ward_code', '=', $paymenttable . '.gp_ward_code');
        $data = $query->orderBy($paymenttable . '.ben_name')->orderBy('gp_ward_code')->get(
          [
            '' . $paymenttable . '.application_id as application_id',
            '' . $paymenttable . '.ben_name as ben_name',
            '' . $paymenttable . '.mobile_no as mobile_no',
            '' . $paymenttable . '.last_accno as bank_code',
            '' . $paymenttable . '.last_ifsc as bank_ifsc',
            '' . $paymenttable . '.rural_urban_id as rural_urban_id',
            '' . $paymenttable . '.block_ulb_code as block_ulb_code',
            '' . $paymenttable . '.gp_ward_code as gp_ward_code',
            '' . $Table . '.edited_status as edited_status',
            '' . $Table . '.failed_type as failed_type',
            '' . $Table . '.created_at as lot_created_at',
            '' . $Table . '.lot_month as lot_month',
            '' . $blocktable . '.block_name as block_name',
            '' . $munctable . '.urban_body_name as munc_name',
            '' . $gptable . '.gram_panchyat_name as gp_name',
            '' . $wardtable . '.urban_body_ward_name as ward_name',
            '' . $paymenttable . '.ds_phase as ds_phase',
          ]
        );
      } else if ($report_type == 3) {
        $Table = 'lb_main.ben_payment_details_bank_code_dup';
        if (!empty($request->ds_phase)) {
          $condition["ds_phase"] = $request->ds_phase;
        }
        if (!empty($district_code)) {
          $condition[$Table . ".dist_code"] = $district_code;
        }
        if (!empty($is_urban)) {
          // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
          if ($is_urban == 2) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 2;
              $condition[$Table . ".local_body_code"] = $urban_body_code;
            }
          }
          //'Urban'
          if ($is_urban == 1) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 1;
              $condition[$Table . ".local_body_code"] = $urban_body_code;
            }
            if (!empty($block_ulb_code)) {
              $condition[$Table . ".block_ulb_code"] = $block_ulb_code;
            }
          }
        }
        if (!empty($gp_ward_code)) {
          $condition[$Table . ".gp_ward_code"] = $gp_ward_code;
        }

        $modelName->setConnection('pgsql_payment');
        $modelName->setTable('' . $Table);
        $schemaname = $getModelFunc->getSchemaDetails();
        $paymenttable = $schemaname . '.ben_payment_details';

        $data = array();
        $query = $modelName->where($condition)->where($paymenttable . ".ben_status", -97);
        $query = $query->join($paymenttable, $paymenttable . '.ben_id', '=', $Table . '.ben_id');
        $query = $query->leftjoin($blocktable, $blocktable . '.block_code', '=', $paymenttable . '.block_ulb_code');
        $query = $query->leftjoin($munctable, $munctable . '.urban_body_code', '=', $paymenttable . '.block_ulb_code');
        $query = $query->leftjoin($gptable, $gptable . '.gram_panchyat_code', '=', $paymenttable . '.gp_ward_code');
        $query = $query->leftjoin($wardtable, $wardtable . '.urban_body_ward_code', '=', $paymenttable . '.gp_ward_code');
        $data = $query->orderBy($paymenttable . '.ben_name')->orderBy('gp_ward_code')->get(
          [
            '' . $paymenttable . '.application_id as application_id',
            '' . $paymenttable . '.ben_name as ben_name',
            '' . $paymenttable . '.mobile_no as mobile_no',
            '' . $paymenttable . '.last_accno as bank_code',
            '' . $paymenttable . '.last_ifsc as bank_ifsc',
            '' . $paymenttable . '.rural_urban_id as rural_urban_id',
            '' . $paymenttable . '.block_ulb_code as block_ulb_code',
            '' . $paymenttable . '.gp_ward_code as gp_ward_code',
            '' . $blocktable . '.block_name as block_name',
            '' . $munctable . '.urban_body_name as munc_name',
            '' . $gptable . '.gram_panchyat_name as gp_name',
            '' . $wardtable . '.urban_body_ward_name as ward_name',
            '' . $paymenttable . '.ds_phase as ds_phase',
          ]
        );
      } else if ($report_type == 4) {
        $schemaname = $getModelFunc->getSchemaDetails();
        $paymenttable = $schemaname . '.ben_payment_details';
        $modelName->setConnection('pgsql_payment');
        $modelName->setTable('' . $paymenttable);

        if (!empty($request->ds_phase)) {
          $condition["ds_phase"] = $request->ds_phase;
        }
        if (!empty($district_code)) {
          $condition[$paymenttable . ".dist_code"] = $district_code;
        }
        if (!empty($is_urban)) {
          // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
          if ($is_urban == 2) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 2;
              $condition[$paymenttable . ".local_body_code"] = $urban_body_code;
            }
          }
          //'Urban'
          if ($is_urban == 1) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 1;
              $condition[$paymenttable . ".local_body_code"] = $urban_body_code;
            }
            if (!empty($block_ulb_code)) {
              $condition[$paymenttable . ".block_ulb_code"] = $block_ulb_code;
            }
          }
        }
        if (!empty($gp_ward_code)) {
          $condition[$paymenttable . ".gp_ward_code"] = $gp_ward_code;
        }



        $data = array();
        $query = $modelName->where($condition)->where($paymenttable . ".ben_status", 0);
        $query = $query->leftjoin($blocktable, $blocktable . '.block_code', '=', $paymenttable . '.block_ulb_code');
        $query = $query->leftjoin($munctable, $munctable . '.urban_body_code', '=', $paymenttable . '.block_ulb_code');
        $query = $query->leftjoin($gptable, $gptable . '.gram_panchyat_code', '=', $paymenttable . '.gp_ward_code');
        $query = $query->leftjoin($wardtable, $wardtable . '.urban_body_ward_code', '=', $paymenttable . '.gp_ward_code');
        $data = $query->orderBy($paymenttable . '.ben_name')->orderBy('gp_ward_code')->get(
          [
            '' . $paymenttable . '.application_id as application_id',
            '' . $paymenttable . '.ben_name as ben_name',
            '' . $paymenttable . '.mobile_no as mobile_no',
            '' . $paymenttable . '.last_accno as bank_code',
            '' . $paymenttable . '.last_ifsc as bank_ifsc',
            '' . $paymenttable . '.rural_urban_id as rural_urban_id',
            '' . $paymenttable . '.block_ulb_code as block_ulb_code',
            '' . $paymenttable . '.gp_ward_code as gp_ward_code',
            '' . $blocktable . '.block_name as block_name',
            '' . $munctable . '.urban_body_name as munc_name',
            '' . $gptable . '.gram_panchyat_name as gp_name',
            '' . $wardtable . '.urban_body_ward_name as ward_name',
            '' . $paymenttable . '.ds_phase as ds_phase',
          ]
        );
      } else if ($report_type == 5) {
        $paymenttable = 'lb_scheme.ben_caste_modification_track';
        $modelName->setConnection('pgsql_appread');
        $modelName->setTable('' . $paymenttable);

        if (!empty($request->ds_phase)) {
          $condition["ds_phase"] = $request->ds_phase;
        }
        if (!empty($district_code)) {
          $condition[$paymenttable . ".created_by_dist_code"] = $district_code;
        }
        if (!empty($is_urban)) {
          // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
          if ($is_urban == 2) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 2;
              $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
            }
          }
          //'Urban'
          if ($is_urban == 1) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 1;
              $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
            }
            if (!empty($block_ulb_code)) {
              $condition[$paymenttable . ".block_ulb_code"] = $block_ulb_code;
            }
          }
        }
        if (!empty($gp_ward_code)) {
          $condition[$paymenttable . ".gp_ward_code"] = $gp_ward_code;
        }



        $data = array();
        $query = $modelName->where($condition)->whereraw("(next_level_role_id_caste IS NULL or next_level_role_id_caste>0 or next_level_role_id_caste=-50)");
        $data = $query->orderBy($paymenttable . '.ben_name')->orderBy('gp_ward_code')->get(
          [
            '' . $paymenttable . '.application_id as application_id',
            DB::raw('CONCAT(ben_fame, " ",ben_mame," ",ben_lname) AS ben_name'),
            '' . $paymenttable . '.mobile_no as mobile_no',
            '' . $paymenttable . '.rural_urban_id as rural_urban_id',
            '' . $paymenttable . '.block_ulb_code as block_ulb_code',
            '' . $paymenttable . '.block_ulb_name as block_name',
            '' . $paymenttable . '.block_ulb_name as munc_name',
            '' . $paymenttable . '.gp_ward_code as gp_ward_code',
            '' . $paymenttable . '.gp_ward_name as gp_name',
            '' . $paymenttable . '.gp_ward_name as ward_name',
            '' . $paymenttable . '.next_level_role_id_caste as next_level_role_id_caste',
            '' . $paymenttable . '.ds_phase as ds_phase',
          ]
        );
      } else if ($report_type == 6) {
        $paymenttable = 'lb_scheme.ben_reject_details';
        $modelName->setConnection('pgsql_appread');
        $modelName->setTable('' . $paymenttable);

        if (!empty($request->ds_phase)) {
          $condition["ds_phase"] = $request->ds_phase;
        }
        if (!empty($district_code)) {
          $condition[$paymenttable . ".created_by_dist_code"] = $district_code;
        }
        if (!empty($is_urban)) {
          // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
          if ($is_urban == 2) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 2;
              $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
            }
          }
          //'Urban'
          if ($is_urban == 1) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 1;
              $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
            }
            if (!empty($block_ulb_code)) {
              $condition[$paymenttable . ".block_ulb_code"] = $block_ulb_code;
            }
          }
        }
        if (!empty($gp_ward_code)) {
          $condition[$paymenttable . ".gp_ward_code"] = $gp_ward_code;
        }



        $data = array();
        $query = $modelName->where($condition)->where("next_level_role_id", -99);
        $data = $query->orderBy($paymenttable . '.ben_fname')->orderBy('gp_ward_code')->get(
          [
            '' . $paymenttable . '.application_id as application_id',
            '' . $paymenttable . '.ben_fname as ben_name',
            '' . $paymenttable . '.mobile_no as mobile_no',
            '' . $paymenttable . '.rural_urban_id as rural_urban_id',
            '' . $paymenttable . '.block_ulb_code as block_ulb_code',
            '' . $paymenttable . '.block_ulb_name as block_name',
            '' . $paymenttable . '.block_ulb_name as munc_name',
            '' . $paymenttable . '.gp_ward_code as gp_ward_code',
            '' . $paymenttable . '.gp_ward_name as gp_name',
            '' . $paymenttable . '.gp_ward_name as ward_name',
            '' . $paymenttable . '.next_level_role_id as next_level_role_id',
            '' . $paymenttable . '.ds_phase as ds_phase',
          ]
        );
      } else if ($report_type == 7) {
        $paymenttable = 'lb_scheme.ben_reject_details';
        $modelName->setConnection('pgsql_appread');
        $modelName->setTable('' . $paymenttable);

        if (!empty($request->ds_phase)) {
          $condition["ds_phase"] = $request->ds_phase;
        }
        if (!empty($district_code)) {
          $condition[$paymenttable . ".created_by_dist_code"] = $district_code;
        }
        if (!empty($is_urban)) {
          // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
          if ($is_urban == 2) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 2;
              $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
            }
          }
          //'Urban'
          if ($is_urban == 1) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 1;
              $condition[$paymenttable . ".created_by_local_body_code"] = $urban_body_code;
            }
            if (!empty($block_ulb_code)) {
              $condition[$paymenttable . ".block_ulb_code"] = $block_ulb_code;
            }
          }
        }
        if (!empty($gp_ward_code)) {
          $condition[$paymenttable . ".gp_ward_code"] = $gp_ward_code;
        }



        $data = array();
        $query = $modelName->where($condition)->where("next_level_role_id", -400);
        $data = $query->orderBy($paymenttable . '.ben_fname')->orderBy('gp_ward_code')->get(
          [
            '' . $paymenttable . '.application_id as application_id',
            '' . $paymenttable . '.ben_fname as ben_name',
            '' . $paymenttable . '.mobile_no as mobile_no',
            '' . $paymenttable . '.rural_urban_id as rural_urban_id',
            '' . $paymenttable . '.block_ulb_code as block_ulb_code',
            '' . $paymenttable . '.block_ulb_name as block_name',
            '' . $paymenttable . '.block_ulb_name as munc_name',
            '' . $paymenttable . '.gp_ward_code as gp_ward_code',
            '' . $paymenttable . '.gp_ward_name as gp_name',
            '' . $paymenttable . '.gp_ward_name as ward_name',
            '' . $paymenttable . '.next_level_role_id as next_level_role_id',
            '' . $paymenttable . '.ds_phase as ds_phase',
          ]
        );
      } else if ($report_type == 8) {
        $Table = 'lb_main.ben_payment_details_bank_code_dup';

        if (!empty($district_code)) {
          $condition[$Table . ".dist_code"] = $district_code;
        }
        if (!empty($is_urban)) {
          // $condition[$contact_table . ".rural_urban_id"] = $is_urban;
          if ($is_urban == 2) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 2;
              $condition[$Table . ".local_body_code"] = $urban_body_code;
            }
          }
          //'Urban'
          if ($is_urban == 1) {
            if (!empty($urban_body_code)) {
              //$condition["rural_urban_id"] = 1;
              $condition[$Table . ".local_body_code"] = $urban_body_code;
            }
            if (!empty($block_ulb_code)) {
              $condition[$Table . ".block_ulb_code"] = $block_ulb_code;
            }
          }
        }
        if (!empty($gp_ward_code)) {
          $condition[$Table . ".gp_ward_code"] = $gp_ward_code;
        }

        $modelName->setConnection('pgsql_payment');
        $modelName->setTable('' . $Table);
        $paymenttable = $Table;

        $data = array();
        $query = $modelName->where($condition)->whereIn("ben_status", [-98, -99]);
        $query = $query->leftjoin($blocktable, $blocktable . '.block_code', '=', $paymenttable . '.block_ulb_code');
        $query = $query->leftjoin($munctable, $munctable . '.urban_body_code', '=', $paymenttable . '.block_ulb_code');
        $query = $query->leftjoin($gptable, $gptable . '.gram_panchyat_code', '=', $paymenttable . '.gp_ward_code');
        $query = $query->leftjoin($wardtable, $wardtable . '.urban_body_ward_code', '=', $paymenttable . '.gp_ward_code');
        $data = $query->orderBy($paymenttable . '.ben_name')->orderBy('gp_ward_code')->get(
          [
            '' . $paymenttable . '.application_id as application_id',
            '' . $paymenttable . '.ben_name as ben_name',
            '' . $paymenttable . '.mobile_no as mobile_no',
            '' . $paymenttable . '.last_accno as bank_code',
            '' . $paymenttable . '.last_ifsc as bank_ifsc',
            '' . $paymenttable . '.rural_urban_id as rural_urban_id',
            '' . $paymenttable . '.block_ulb_code as block_ulb_code',
            '' . $paymenttable . '.gp_ward_code as gp_ward_code',
            '' . $blocktable . '.block_name as block_name',
            '' . $munctable . '.urban_body_name as munc_name',
            '' . $gptable . '.gram_panchyat_name as gp_name',
            '' . $wardtable . '.urban_body_ward_name as ward_name',
            '' . $paymenttable . '.rejected_date as rejected_date'

          ]
        );
      }
      //dd($data->toArray());
      $excel_data[] = array(
        'Application ID',
        'Applicant Name',
        'Mobile No',
        'Block/Municipality',
        'GP/WARD',
        'DS Phase',
        'Status'
      );
      $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') . "-" . time() . ".xls";
      header("Content-Type: application/xls");
      header("Content-Disposition: attachment; filename=" . $filename);
      header("Pragma: no-cache");
      header("Expires: 0");
      echo '<table border="1">';
      echo '<tr><td alignment="center" colspan="7">' . $report_type_name . '</td></tr>';
      echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Mobile No.</th><th>Block/Municipality</th><th>GP/WARD</th><th>DS Phase</th><th>Status</th></tr>';
      if (count($data) > 0) {
        foreach ($data as $row) {

          $mobile_no = (string) $row->mobile_no;
          if (!empty($mobile_no))
            $f_mobile_no = "'$mobile_no'";
          else
            $f_mobile_no = '';
          $block_ulb_name = '';
          if ($row->rural_urban_id == 1) {
            $block_ulb_name = $row->munc_name;
          } else if ($row->rural_urban_id == 2) {
            $block_ulb_name = $row->block_name;
          }
          $gp_ward_name = '';
          if ($row->rural_urban_id == 1) {
            $gp_ward_name = $row->ward_name;
          } else if ($row->rural_urban_id == 2) {
            $gp_ward_name = $row->gp_name;
          }
          if ($row->ds_phase != '') {
            $phase_des = $this->getPhaseDes($row->ds_phase);
          } else {
            $phase_des = '';
          }
          $status_description = '';
          if ($report_type == 1 || $report_type == 2) {
            if ($row->edited_status == 0)
              $status_description = 'Pending at Verifier';
            else if ($row->edited_status == 1)
              $status_description = 'Pending at Approver';
          } else if ($report_type == 3 || $report_type == 4) {
            $status_description = 'Pending at Verifier/Approver';
          } else if ($report_type == 5) {
            if (is_int($row->next_level_role_id_caste) && $row->next_level_role_id_caste > 0) {
              $status_description = 'Pending at Approver';
            } else
              $status_description = 'Pending at Verifier';
          } else if ($report_type == 6 || $report_type == 7) {
            $status_description = 'Rejected by Approver';
          } else if ($report_type == 8) {
            $status_description = 'Rejected on ' . $row->rejected_date;
          }
          echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_name) . "</td><td>" . $f_mobile_no . "</td><td>" . trim($block_ulb_name) . "</td><td>" . trim($gp_ward_name) . "</td><td>" . $phase_des . "</td><td>" . $status_description . "</td></tr>";
        }
      } else {
        echo '<tr><td colspan="7">No Records found</td></tr>';
      }
      echo '</table>';
    } catch (\Exception $e) {
      //dd($e);
    }
  }
  function mishod(Request $request)
  {
    try {
      $role_name = Auth::user()->designation_id;
      if ($role_name != 'HOD') {
        return redirect('/')->with('error', 'User not Authorized');
      }
      $district_list = District::orderBy('district_code')->get();
      $list = array();
      $total = array();
      $total['acc_validation'] = 0;
      $total['payment_validation'] = 0;
      $total['dup_bank'] = 0;
      $total['dup_aadhaar'] = 0;
      $total['caste'] = 0;
      $total['deactivated'] = 0;
      $total['name_validation_rejection'] = 0;
      $total['dup_bank_rejection'] = 0;
      $i = 1;
      $getModelFunc = new getModelFunc();
      $schemaname = $getModelFunc->getSchemaDetails();
      $paymenttable = $schemaname . '.ben_payment_details';
      $failedTable = 'lb_main.failed_payment_details';
      $dupBankTable = 'lb_main.ben_payment_details_bank_code_dup';
      $casteTable = 'lb_scheme.ben_caste_modification_track';
      $rejectTable = 'lb_scheme.ben_reject_details';

      foreach ($district_list as $district_item) {
        $list[$i]['slno'] = $i;
        $list[$i]['district_name'] = $district_item->district_name;
        $modelName1 = new DataSourceCommon;
        $modelName1->setConnection('pgsql_payment');
        $modelName1->setTable('' . $failedTable);
        $query1 = $modelName1->where('' . $failedTable . '.dist_code', $district_item->district_code)->where('failed_type', 1)->whereIn('edited_status', [0, 1]);
        $query1 = $query1->join($paymenttable, $paymenttable . '.ben_id', '=', $failedTable . '.ben_id');
        $query1 = $query1->where($paymenttable . ".ben_status", 1);
        $list[$i]['acc_validation'] = intval($query1->count($failedTable . '.ben_id'));
        $total['acc_validation'] = $total['acc_validation'] + intval($query1->count($failedTable . '.ben_id'));
        $modelName2 = new DataSourceCommon;
        $modelName2->setConnection('pgsql_payment');
        $modelName2->setTable('' . $failedTable);
        $query2 = $modelName2->where('' . $failedTable . '.dist_code', $district_item->district_code)->where('failed_type', 2)->whereIn('edited_status', [0, 1]);
        $query2 = $query2->join($paymenttable, $paymenttable . '.ben_id', '=', $failedTable . '.ben_id');
        $query2 = $query2->where($paymenttable . ".ben_status", 1);
        $list[$i]['payment_validation'] = intval($query2->count($failedTable . '.ben_id'));
        $total['payment_validation'] = $total['payment_validation'] + intval($query2->count($failedTable . '.ben_id'));

        $modelName3 = new DataSourceCommon;
        $modelName3->setConnection('pgsql_payment');
        $modelName3->setTable('' . $dupBankTable);
        $query3 = $modelName3->where('dist_code', $district_item->district_code)->where("ben_status", -97);
        $list[$i]['dup_bank'] = intval($query3->count('ben_id'));
        $total['dup_bank'] = $total['dup_bank'] + intval($query3->count('ben_id'));

        $modelName4 = new DataSourceCommon;
        $modelName4->setConnection('pgsql_payment');
        $modelName4->setTable('' . $paymenttable);
        $query4 = $modelName4->where('dist_code', $district_item->district_code)->where("ben_status", 0);
        $list[$i]['dup_aadhaar'] = intval($query4->count('ben_id'));
        $total['dup_aadhaar'] = $total['dup_aadhaar'] + intval($query4->count('ben_id'));

        $modelName5 = new DataSourceCommon;
        $modelName5->setConnection('pgsql_appread');
        $modelName5->setTable('' . $casteTable);
        $query5 = $modelName5->where('created_by_dist_code', $district_item->district_code)->whereraw("(next_level_role_id_caste IS NULL or next_level_role_id_caste>0 or next_level_role_id_caste=-50)");
        $list[$i]['caste'] = intval($query5->count('beneficiary_id'));
        $total['caste'] = $total['caste'] + intval($query5->count('beneficiary_id'));

        $modelName6 = new DataSourceCommon;
        $modelName6->setConnection('pgsql_appread');
        $modelName6->setTable('' . $rejectTable);
        $query6 = $modelName6->where('created_by_dist_code', $district_item->district_code)->where("next_level_role_id", -99);
        $list[$i]['deactivated'] = intval($query6->count('application_id'));
        $total['deactivated'] = $total['deactivated'] + intval($query6->count('application_id'));

        $modelName7 = new DataSourceCommon;
        $modelName7->setConnection('pgsql_appread');
        $modelName7->setTable('' . $rejectTable);
        $query7 = $modelName7->where('created_by_dist_code', $district_item->district_code)->where("next_level_role_id", -400);
        $list[$i]['name_validation_rejection'] = intval($query7->count('application_id'));
        $total['name_validation_rejection'] = $total['name_validation_rejection'] + intval($query7->count('application_id'));

        $modelName8 = new DataSourceCommon;
        $modelName8->setConnection('pgsql_payment');
        $modelName8->setTable('' . $dupBankTable);
        $query8 = $modelName8->where('dist_code', $district_item->district_code)->whereIn("ben_status", [-98, -99]);
        $list[$i]['dup_bank_rejection'] = intval($query8->count('application_id'));
        $total['dup_bank_rejection'] = $total['dup_bank_rejection'] + intval($query8->count('application_id'));

        $i++;
      }
      //dd($list);
      return view('stop-beneficiary.mishod')->with('list', $list)->with('total', $total);
    } catch (\Exception $e) {
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
