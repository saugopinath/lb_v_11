<?php

namespace App\Http\Controllers;

use App\Models\Configduty;
use App\Models\District;
use App\Models\GP;
use App\Models\Taluka;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DrilldownFaultyReportController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    set_time_limit(200);
  }

  public function index()
  {
    $designationId = Auth::user()->designation_id;
    $userId = Auth::user()->id;
    $dutyObj = Configduty::where('user_id', $userId)->first();
    $levels = [
      2 => 'Rural',
      1 => 'Urban',
    ];
    if ($designationId == 'Approver') {
      $distCode = $dutyObj->district_code;
      return view('Drilldown-faulty.block_application_report', ['levels' => $levels, 'distCode' => $distCode]);
    } else if ($designationId == 'HOD' || $designationId == 'MisState' || $designationId == 'HOP') {
      $districts = District::select('district_name', 'district_code')->get();
      return view('Drilldown-faulty.district_application_report', ['district' => $districts, 'levels' => $levels,]);
    }
  }
  public function getFaultyDistAppData(Request $request)
  {
    if ($request->ajax()) {
      $distCode = $request->district_code;
      $rural_urban = $request->rural_urban;
      $query = '';
      if ($distCode == '') {
        $query = $this->getDistrictQuery();
      } else {
        $query = $this->getBlockSubdivQuery($rural_urban, $distCode);
      }

      $data = DB::connection('pgsql_appwrite')->select($query);
      return datatables()->of($data)
        ->addColumn('district_name', function ($data) use ($distCode) {
          if ($distCode == '') {
            $name = $data->district_name;
          } else {
            $name = $data->bsm;
          }
          return $name;
        })
        ->addColumn('total_applicant', function ($data) {
          return $data->total_applicant;
        })
        ->addColumn('total_edited', function ($data) {
          return intval($data->total_applied) + intval($data->verified) + intval($data->approved);
        })
        ->addColumn('ver_pending', function ($data) {
          return intval($data->total_applied);
        })
        ->addColumn('verified', function ($data) {
          return intval($data->verified) + intval($data->approved);
        })
        ->addColumn('app_pending', function ($data) {
          return intval($data->verified);
        })
        ->addColumn('approved', function ($data) {
          return $data->approved;
        })
        ->rawColumns(['district_name', 'total_applicant', 'total_edited', 'ver_pending', 'verified', 'app_pending', 'approved'])
        ->make(true);
    }
  }
  public function getFaultyBlockSubdivAppData(Request $request)
  {
    if ($request->ajax()) {
      $distCode = $request->district_code;
      $rural_urban = $request->filter_1;
      $query = '';
      $query = $this->getBlockSubdivQuery($rural_urban, $distCode);

      $data = DB::connection('pgsql_appwrite')->select($query);
      return datatables()->of($data)
        ->addColumn('bsm', function ($data) {
          return $data->bsm;
        })
        ->addColumn('total_applicant', function ($data) {
          return $data->total_applicant;
        })
        ->addColumn('total_edited', function ($data) {
          return intval($data->total_applied) + intval($data->verified) + intval($data->approved);
        })
        ->addColumn('ver_pending', function ($data) {
          return intval($data->total_applied);
        })
        ->addColumn('verified', function ($data) {
          return intval($data->verified) + intval($data->approved);
        })
        ->addColumn('app_pending', function ($data) {
          return intval($data->verified);
        })
        ->addColumn('approved', function ($data) {
          return $data->approved;
        })
        ->rawColumns(['bsm', 'total_applicant', 'total_edited', 'ver_pending', 'verified', 'app_pending', 'approved'])
        ->make(true);
    }
  }
  private function getDistrictQuery()
  {
    $query = "SELECT district_name,
        SUM(coalesce(total_applicant,0))   as total_applicant,
        SUM(coalesce(total_applied,0))   as total_applied,
        SUM(coalesce(verified,0))      as verified,
        SUM(coalesce(approved,0))      as approved
        from(
         SELECT b.district_name,
         count(1) filter(where db.is_migrated is null)     as total_applicant,
         count(1) Filter(where db.enq_iseligible=1 and db.is_final=true and db.next_level_role_id is null) as total_applied,
         count(1) Filter(where db.enq_iseligible=1 and db.ver_iseligible=1 and db.is_final=true and db.next_level_role_id > 0) as verified,
         0                                                    as approved
         FROM lb_scheme.faulty_draft_ben_personal_details  db
         inner join public.m_district b on b.district_code=db.created_by_dist_code group by b.district_name
               
           UNION ALL    
         SELECT b.district_name,
         0                         as total_applicant,
         count(1) Filter(where pd.next_level_role_id is null)     as total_applied,
         count(1) Filter(where pd.next_level_role_id >  0)      as verified,
         count(1) Filter(where pd.next_level_role_id =  0)      as approved
         FROM lb_scheme.faulty_ben_personal_details pd
         inner join public.m_district b on b.district_code=pd.created_by_dist_code group by b.district_name
         )x
        group by district_name order by district_name";
    return $query;
  }
  private function getBlockSubdivQuery($rural_urban, $distCode)
  {
    if ($rural_urban == 1) {
      $query = "Select bsm,
         SUM(coalesce(total_applicant,0))   as total_applicant,
         SUM(coalesce(total_applied,0))   as total_applied,
         SUM(coalesce(verified,0))      as verified,
         SUM(coalesce(approved,0))      as approved
          FROM (
            select sub_district_name as bsm,total_applicant,total_applied,verified,approved
            from public.m_sub_district sb
          left join
          (
          Select db.created_by_local_body_code, db.created_at,
          count(1) filter(where db.is_migrated is null)     as total_applicant,
          count(1) Filter(where db.enq_iseligible=1 and db.is_final=true and db.next_level_role_id is null) as total_applied,
          count(1) Filter(where db.enq_iseligible=1 and db.ver_iseligible=1 and db.is_final=true and db.next_level_role_id > 0) as verified,
          0                                                     as approved
          FROM lb_scheme.faulty_draft_ben_personal_details db 
          where db.created_by_dist_code=" . $distCode . "
           group by db.created_by_local_body_code, db.created_at
          )x  on sb.sub_district_code=x.created_by_local_body_code
          where district_code=" . $distCode . " 
          UNION ALL
          select sub_district_name as bsm,total_applicant,total_applied,verified,approved
            from public.m_sub_district sb
          left join
          (
          Select maindb.created_by_local_body_code, maindb.created_at,
          0                        as total_applicant,
          count(1) Filter(where maindb.next_level_role_id is null)    as total_applied,
          count(1) Filter(where maindb.next_level_role_id >  0)     as verified,
          count(1) Filter(where maindb.next_level_role_id =  0)     as approved
          FROM lb_scheme.faulty_ben_personal_details maindb 
          where maindb.created_by_dist_code=" . $distCode . "
           group by maindb.created_by_local_body_code, maindb.created_at
          )x  on sb.sub_district_code=x.created_by_local_body_code
          where district_code=" . $distCode . "
          ) p  group by bsm order by bsm";
    } else if ($rural_urban == 2) {
      $query = "Select bsm,
         SUM(coalesce(total_applicant,0))   as total_applicant,
         SUM(coalesce(total_applied,0))   as total_applied,
         SUM(coalesce(verified,0))      as verified,
         SUM(coalesce(approved,0))      as approved
          FROM (
            select block_name as bsm,total_applicant,total_applied,verified,approved
            from public.m_block sb
          left join
          (
          Select db.created_by_local_body_code, db.created_at,
          count(1) filter(where db.is_migrated is null)     as total_applicant,
          count(1) Filter(where db.enq_iseligible=1 and db.is_final=true and db.next_level_role_id is null) as total_applied,
          count(1) Filter(where db.enq_iseligible=1 and db.ver_iseligible=1 and db.is_final=true and db.next_level_role_id > 0) as verified,
          0                                                     as approved
          FROM lb_scheme.faulty_draft_ben_personal_details db 
          where db.created_by_dist_code=" . $distCode . "
           group by db.created_by_local_body_code, db.created_at
          )x  on sb.block_code=x.created_by_local_body_code
          where district_code=" . $distCode . " 
          UNION ALL
          select block_name as bsm,total_applicant,total_applied,verified,approved
            from public.m_block sb
          left join
          (
          Select maindb.created_by_local_body_code, maindb.created_at,
          0                        as total_applicant,
          count(1) Filter(where maindb.next_level_role_id is null)    as total_applied,
          count(1) Filter(where maindb.next_level_role_id >  0)     as verified,
          count(1) Filter(where maindb.next_level_role_id =  0)     as approved
          FROM lb_scheme.faulty_ben_personal_details maindb 
          where maindb.created_by_dist_code=" . $distCode . "
           group by maindb.created_by_local_body_code, maindb.created_at
          )x  on sb.block_code=x.created_by_local_body_code
          where district_code=" . $distCode . "
          ) p  group by bsm order by bsm";
    } else {
      $query = "Select bsm,
         SUM(coalesce(total_applicant,0))   as total_applicant,
         SUM(coalesce(total_applied,0))   as total_applied,
         SUM(coalesce(verified,0))      as verified,
         SUM(coalesce(approved,0))      as approved
          FROM (
            select sub_district_name as bsm,total_applicant,total_applied,verified,approved
            from public.m_sub_district sb
          left join
          (
          Select db.created_by_local_body_code, db.created_at,
          count(1) filter(where db.is_migrated is null)    as total_applicant,
          count(1) Filter(where db.enq_iseligible=1 and db.is_final=true and db.next_level_role_id is null) as total_applied,
          count(1) Filter(where db.enq_iseligible=1 and db.ver_iseligible=1 and db.is_final=true and db.next_level_role_id > 0) as verified,
          0                                                     as approved
          FROM lb_scheme.faulty_draft_ben_personal_details db 
          where db.created_by_dist_code=" . $distCode . "
           group by db.created_by_local_body_code, db.created_at
          )x  on sb.sub_district_code=x.created_by_local_body_code
          where district_code=" . $distCode . " 
          UNION ALL
          select sub_district_name as bsm,total_applicant,total_applied,verified,approved
            from public.m_sub_district sb
          left join
          (
          Select maindb.created_by_local_body_code, maindb.created_at,
          0                        as total_applicant,
          count(1) Filter(where maindb.next_level_role_id is null)    as total_applied,
          count(1) Filter(where maindb.next_level_role_id >  0)     as verified,
          count(1) Filter(where maindb.next_level_role_id =  0)     as approved
          FROM lb_scheme.faulty_ben_personal_details maindb 
          where maindb.created_by_dist_code=" . $distCode . "
           group by maindb.created_by_local_body_code, maindb.created_at
          )x  on sb.sub_district_code=x.created_by_local_body_code
          where district_code=" . $distCode . "
          UNION ALL
          select block_name as bsm,total_applicant,total_applied,verified,approved
            from public.m_block sb
          left join
          (
          Select db.created_by_local_body_code, db.created_at,
          count(1) filter(where db.is_migrated is null)     as total_applicant,
          count(1) Filter(where db.enq_iseligible=1 and db.is_final=true and db.next_level_role_id is null) as total_applied,
          count(1) Filter(where db.enq_iseligible=1 and db.ver_iseligible=1 and db.is_final=true and db.next_level_role_id > 0) as verified,
          0                                                     as approved
          FROM lb_scheme.faulty_draft_ben_personal_details db 
          where db.created_by_dist_code=" . $distCode . "
           group by db.created_by_local_body_code, db.created_at
          )x  on sb.block_code=x.created_by_local_body_code
          where district_code=" . $distCode . " 
          UNION ALL
          select block_name as bsm,total_applicant,total_applied,verified,approved
            from public.m_block sb
          left join
          (
          Select maindb.created_by_local_body_code, maindb.created_at,
          0                        as total_applicant,
          count(1) Filter(where maindb.next_level_role_id is null)    as total_applied,
          count(1) Filter(where maindb.next_level_role_id >  0)     as verified,
          count(1) Filter(where maindb.next_level_role_id =  0)     as approved
          FROM lb_scheme.faulty_ben_personal_details maindb 
          where maindb.created_by_dist_code=" . $distCode . "
           group by maindb.created_by_local_body_code, maindb.created_at
          )x  on sb.block_code=x.created_by_local_body_code
          where district_code=" . $distCode . "
          ) p  group by bsm order by bsm";
    }
    return $query;
  }
}
