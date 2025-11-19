<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scheme;

use App\Models\District;
use App\Models\UrbanBody;
use App\Models\DocumentType;
use App\Models\SchemeDocMap;
use App\Models\Assembly;
use App\Models\Taluka;
use App\Models\Ward;
use App\Models\GP;
use App\Models\User;
use Redirect;
use Auth;
use Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Validator;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\DataSourceCommon;
use App\Models\getModelFunc;
use Illuminate\Support\Facades\Crypt;
use App\Models\RejectRevertReason;
use App\Models\DistrictEntryMapping;
use App\Models\DsPhase;

class LakkhiBhandarWCDformController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        date_default_timezone_set('Asia/Kolkata');
        $phaseArr = DsPhase::where('is_current', TRUE)->first();
        //$mydate = $phaseArr->base_dob;
        $mydate = date('Y-m-d');
        $max_date = strtotime("-25 year", strtotime($mydate));
        $max_date = date("Y-m-d", $max_date);
        $min_date = strtotime("-60 year", strtotime($mydate));
        $min_date = date("Y-m-d", $min_date);
        $this->base_dob_chk_date = $mydate;
        $this->max_dob = $max_date;
        $this->min_dob = $min_date;
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
    }
    public function search(Request $request)
    {
        $ds_phase_list = DsPhase::all();
        $cur_ds_phase_arr = $ds_phase_list->where('is_current', TRUE)->first();
        $cur_ds_phase = $cur_ds_phase_arr->phase_code;
        $scheme_id = $this->scheme_id;
        $roleArray = $request->session()->get('role');
        $designation_id = Auth::user()->designation_id;
        $user_id = Auth::user()->id;
        $is_active = 0;
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $is_urban = $roleObj['is_urban'];
                $distCode = $roleObj['district_code'];
                if ($roleObj['is_urban'] == 1) {
                    $blockCode = $roleObj['urban_body_code'];
                } else {
                    $blockCode = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($distCode)) {
            return redirect("/")->with('error', 'User Disabled. ');
        }
        if (!in_array($designation_id, array('Operator'))) {
            return redirect("/")->with('error', 'Not Allowed');
        }
        $reject_revert_reason = RejectRevertReason::where('status', true)->get();
        $entry_allowed_main = DistrictEntryMapping::where('main_entry', true)->where('district_code',  $distCode)->count();
        $entry_allowed_faulty = DistrictEntryMapping::where('faulty_entry', true)->where('district_code',  $distCode)->count();
        $entry_allowed_main=0;
        $entry_allowed_faulty=0;
        if (request()->ajax()) {
            $modelName = new DataSourceCommon;
            $getModelFunc = new getModelFunc();
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 8);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $condition = array();
            $condition["is_faulty"] = FALSE;
            $sws_card_no = trim($request->sws_card_no);
            $ds_phase = trim($request->ds_phase);
            if (!empty($sws_card_no))
                $condition["ss_family_id"] = $sws_card_no;
            else {
                $condition["created_by_dist_code"] = $distCode;
                $condition["created_by_local_body_code"] = $blockCode;
                $condition["status"] = 1;
            }

            if (!empty($request->search['value']))
                $serachvalue = trim($request->search['value']);
            else
                $serachvalue = '';
            $limit = $request->input('length');
            $offset = $request->input('start');
            $totalRecords = 0;
            $filterRecords = 0;
            $data = array();

            $query = $modelName->where($condition)->where('ds_phase','<=',6);
            if (empty($serachvalue)) {
                $totalRecords = $query->count('ss_ben_id');
                $data = $query->orderBy('ss_family_id')->orderBy('ss_ben_name')->offset($offset)->limit($limit)->get([
                    'created_by_dist_code', 'created_by_local_body_code', 'status', 'user_id', 'ss_ben_id', 'ss_family_id', 'ss_ben_name', 'lb_application_id', 'status', 'adharcardno'
                ]);

                $filterRecords = count($data);
                //dump($limit);
                // dump($offset);
                // dump($totalRecords);
                // dump($filterRecords);
                //dd($data->toArray());
            } else {
                if (is_numeric($serachvalue)) {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        $query1->where('lb_application_id', $serachvalue)
                            ->orWhere('adharcardno', $serachvalue);
                    });
                    $totalRecords = $query->count('ss_ben_id');
                    $data = $query->orderBy('ss_family_id')->orderBy('ss_ben_name')->offset($offset)->limit($limit)->get(
                        [
                            'created_by_dist_code', 'created_by_local_body_code', 'status', 'user_id', 'ss_ben_id', 'ss_family_id', 'ss_ben_name', 'lb_application_id', 'status', 'adharcardno'
                        ]
                    );
                } else {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        $query1->where('ss_ben_name', 'ilike', $serachvalue . '%');
                    });
                    $totalRecords = $query->count('ss_ben_id');
                    $data = $query->orderBy('ss_family_id')->orderBy('ss_ben_name')->offset($offset)->limit($limit)->get(
                        [
                            'created_by_dist_code', 'created_by_local_body_code', 'status', 'user_id', 'ss_ben_id', 'ss_family_id', 'ss_ben_name', 'lb_application_id', 'status', 'adharcardno'
                        ]
                    );
                }
                $filterRecords = count($data);
            }

            return datatables()->of($data)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($filterRecords)
                ->skipPaging()
                ->addColumn('name', function ($data) {
                    return trim($data->ss_ben_name);
                })->addColumn('application_id', function ($data) use ($sws_card_no, $user_id) {
                    if (!empty($sws_card_no)) {
                        if (!empty($data->lb_application_id)) {

                            return $data->lb_application_id;
                        } else {
                            return 'NA';
                        }
                    } else {
                        if (!empty($data->lb_application_id))
                            return $data->lb_application_id;
                        else
                            return 'NA';
                    }
                })->addColumn('adharcardno', function ($data) {
                    if (!empty($data->adharcardno)) {
                        return ('********' . substr($data->adharcardno, -4));
                    } else
                        return '';
                })->addColumn('father_name', function ($data) {
                    return $data->fathername;
                })->addColumn('sws_card_no', function ($data) {
                    return $data->ss_family_id;
                })->addColumn('status', function ($data) use ($sws_card_no, $distCode, $blockCode) {
                    if (!empty($sws_card_no)) {
                        if (!empty($data->lb_application_id)) {
                            if ($blockCode == $data->created_by_local_body_code) {
                                if ($data->status == 2)
                                    $status_msg = 'Already Applied';
                                else if ($data->status == 1)
                                    $status_msg = 'In-Progress';
                                else
                                    return '';
                            } else {
                                $status_msg = 'Already Edited by some other user';
                            }
                        } else {
                            $status_msg = 'NA';
                        }
                    } else {
                        if ($data->status == 2)
                            $status_msg = 'Already Applied';
                        else if ($data->status == 1)
                            $status_msg = 'In-Progress';
                        else
                            $status_msg = '';
                    }
                    if (!empty($status_msg)) {

                        if ($status_msg == 'Already Applied') {
                            $status = '<span class="label label-success">' . $status_msg . '</span>';
                        } else
                            $status = '<span class="label label-warning">' . $status_msg . '</span>';
                    } else
                        $status = '';
                    return $status;
                })->addColumn('father_name', function ($data) {
                    return trim($data->ss_father_name);
                })->addColumn('Action', function ($data) use ($sws_card_no, $distCode, $blockCode, $entry_allowed_main) {
                    if (!empty($sws_card_no)) {
                        //dd($entry_allowed);
                        if (!empty($data->lb_application_id)) {
                            if ($blockCode == $data->created_by_local_body_code) {
                                if ($data->status == 2)
                                    $action = 'NA';
                                else if ($data->status == 1)
                                    $action = '<a href="formEntry?scheme_slug=lb_wcd&add_edit_status=4&source_id=' . $data->ss_ben_id . '&sws_no=' . $data->ss_family_id . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Apply</a>&nbsp;&nbsp;&nbsp;&nbsp<button value="' . $data->lb_application_id . '" id="rej_' . $data->lb_application_id . '" class="btn btn-danger btn-sm rej-btn" type="button">Reject</button>';
                                else {

                                    $action = '<a href="formEntry?scheme_slug=lb_wcd&add_edit_status=4&source_id=' . $data->ss_ben_id . '&sws_no=' . $data->ss_family_id . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Apply</a>';
                                }
                            } else {
                                $action = 'NA';
                            }
                        } else {
                            if ($entry_allowed_main == 1) {
                                $action = '<a href="formEntry?scheme_slug=lb_wcd&add_edit_status=4&source_id=' . $data->ss_ben_id . '&sws_no=' . $data->ss_family_id . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Apply</a>';
                            } else
                                $action = 'NA';
                        }
                    } else {
                        if ($data->status == 2)
                            $action = 'NA';
                        else if ($data->status == 1)
                            $action = '<a href="formEntry?scheme_slug=lb_wcd&add_edit_status=4&source_id=' . $data->ss_ben_id . '&sws_no=' . $data->ss_family_id . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Apply</a>&nbsp;&nbsp;&nbsp;&nbsp<button value="' . $data->lb_application_id . '" id="rej_' . $data->lb_application_id . '" class="btn btn-danger btn-sm rej-btn" type="button">Reject</button>';
                        else {
                            if ($entry_allowed_main == 1) {
                                $action = '<a href="formEntry?scheme_slug=lb_wcd&add_edit_status=4&source_id=' . $data->ss_ben_id . '&sws_no=' . $data->ss_family_id . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Apply</a>';
                            } else
                                $action = 'NA';
                        }
                    }
                    return $action;
                })
                ->rawColumns(['Action', 'id', 'name', 'status'])
                ->make(true);
        }
        $errormsg = Config::get('constants.errormsg');
        return view(
            'LokkhiBhandarWCD.sws_card_no_search',
            [
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'reject_revert_reason' => $reject_revert_reason,
                'entry_allowed_main' => $entry_allowed_main,
                'entry_allowed_faulty' => $entry_allowed_faulty,
                'ds_phase_list' => $ds_phase_list

            ]
        );
    }

    public function index(Request $request)
    {
        $dob_base_date = Carbon::parse($this->base_dob_chk_date)->format('d/m/Y');
        $scheme_slug = trim($request->scheme_slug);
        if (!empty($request->tab_code)) {
            $tab_code = trim($request->tab_code);
        } else {
            $tab_code = '';
        }
        // dd($scheme_slug);
        $add_edit_status = trim($request->add_edit_status);
        //dd($add_edit_status);
        if (!in_array($add_edit_status, array(1, 2, 3, 4))) {
            return redirect("/")->with('error', 'Not Allowed');
        }
        $designation_id = Auth::user()->designation_id;
        $max_dob = $this->max_dob;
        if (empty($scheme_slug)) {
            return redirect("/")->with('error', 'Paramenter not Passed');
        }
        $scheme_row = Scheme::where('short_code', $scheme_slug)->where('is_active', 1)->first();
        if (empty($scheme_row->id)) {
            return redirect("/")->with('error', 'Scheme Not Found');
        } else {
            $scheme_id =  $scheme_row->id;
        }
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $request->session()->put('level', $roleObj['mapping_level']);
                $dist_code = $roleObj['district_code'];
                $request->session()->put('distCode', $roleObj['district_code']);
                if ($roleObj['is_urban'] == 1) {
                    $blockCode = $roleObj['urban_body_code'];
                    $request->session()->put('blockCode', $roleObj['urban_body_code']);
                    $urban_code = 1;
                } else {
                    $blockCode = $roleObj['taluka_code'];
                    $request->session()->put('blockCode', $roleObj['taluka_code']);
                    $urban_code = 2;
                }
                break;
            }
        }
        //$is_active = 1;
        if ($is_active == 0 || empty($dist_code)) {
            return redirect("/")->with('error', 'User Disabled');
        }
        if ($is_active == 1) {

            $district_list = District::select(
                'id',
                'district_code',
                'district_name',
                'rch_district_code',
                'is_revenue_district',
                'state_code',
                'district_status'
            )->get();
            $block_ulb_list = collect([]);
            $gp_ward_list = collect([]);
            $user_id = Auth::user()->id;
            $row = collect([]);
            $id = NULL;
            $source_id = NULL;
            $family_id = NULL;
            $source_type = NULL;
            $row->ds_phase = '';
            $row->duare_sarkar_registration_no = '';
            $row->duare_sarkar_date = '';
            $row->ben_fname = '';
            $row->ben_mname = '';
            $row->ben_lname = '';
            $row->gender = '';
            $row->dob = '';
            $row->ben_age = '';
            $row->father_fname = '';
            $row->father_mname = '';
            $row->father_lname = '';
            $row->mother_fname = '';
            $row->mother_mname = '';
            $row->mother_lname = '';
            $row->caste = '';
            $row->caste_certificate_no = '';
            $row->marital_status = '';
            $row->spouse_fname = '';
            $row->spouse_mname = '';
            $row->spouse_lname = '';
            $row->aadhar_no = '';
            $row->aadhar_no_hidden = '';

            $row->sws_card_no = '';
            $row->dist_code = '';
            $row->rural_urban_id = '';
            $row->police_station = '';
            $row->block_ulb_code = '';
            $row->gp_ward_code = '';
            $row->village_town_city = '';
            $row->house_premise_no = '';
            $row->post_office = '';
            $row->pincode = '';
            $row->residency_period = '';
            $row->mobile_no = '';
            $row->email = '';
            $row->bank_name = '';
            $row->branch_name = '';
            $row->bank_code = '';
            $row->bank_ifsc = '';
            $row->is_resident = '';
            $row->earn_monthly_remuneration = '';
            $row->info_genuine_decl = '';
            $row->id = NULL;
            if ($add_edit_status == 1) {
            } else {
                // dd($add_edit_status);
                if ($add_edit_status == 4 || $add_edit_status == 3 || $add_edit_status == 2) {

                    if ($add_edit_status == 4) {
                        $id = NULL;
                        $source_id = trim($request->source_id);
                        //dd($source_id);
                        $source_type = $this->source_type;
                        $sws_no = trim($request->sws_no);
                        $max_dob = $this->max_dob;
                        //dd($sws_no);
                        if ($designation_id != 'Operator') {
                            return redirect("/")->with('error', 'Not Allowed');
                        }
                        if (empty($sws_no)) {
                            return redirect("/")->with('error', 'Swasthyasathi Card No. Not Valid');
                        }
                        if (empty($source_id)) {
                            return redirect("/")->with('error', 'Source ID Not Valid');
                        }
                        if (!is_numeric($source_id)) {
                            return redirect("/")->with('error', 'Source ID Not Valid');
                        }
                        $modelMapping = new DataSourceCommon;
                        $getModelFunc = new getModelFunc();
                        $Table = $getModelFunc->getTable($dist_code, $this->source_type, 8);
                        $modelMapping->setConnection('pgsql_appread');

                        $modelMapping->setTable('' . $Table);
                        $printable = $modelMapping->where(['ss_ben_id' => $source_id, 'ss_family_id' => $sws_no])->first();
                        if (empty($printable->ss_ben_id)) {
                            return redirect("/")->with('error', 'Swasthyasathi Card No. Not Found');
                        } else {

                            if (!empty($printable->user_id)) {
                                if ($printable->created_by_local_body_code != $blockCode) {
                                    return redirect("/")->with('error', 'Already Edited by Some Other User.');
                                }
                            }
                        }
                        $row->id = $printable->ss_ben_id;
                        $row->dist_code = $dist_code;
                        $row->rural_urban_id = $urban_code;
                        $row->aadhar_no = $printable->adharcardno;

                        if ($urban_code == 1) {
                            $row->block_ulb_code = '';
                            $row->gp_ward_code = '';
                        } else {
                            $row->block_ulb_code = $blockCode;
                            $row->gp_ward_code = '';
                        }
                        $row->sl_no = $printable->sl_no;
                        $f_name = '';
                        $m_name = '';
                        $l_name = '';
                        $row->sws_full_name = trim($printable->ss_ben_name);
                        $name_explode = explode(' ', trim($printable->ss_ben_name));
                        if (count($name_explode) == 1) {
                            $f_name = $name_explode[0];
                            $m_name = '';
                            $l_name = '';
                        } else if (count($name_explode) == 2) {
                            $f_name = $name_explode[0];
                            $m_name = '';
                            $l_name = $name_explode[1];
                        } else if (count($name_explode) == 3) {
                            $f_name = $name_explode[0];
                            $m_name = $name_explode[1];
                            $l_name = $name_explode[2];
                        } else {
                            $f_name = trim($printable->name);
                            $m_name = '';
                            $l_name = '';
                        }
                        $row->ben_fname = $f_name;
                        $row->ben_mname = $m_name;
                        $row->ben_lname = $l_name;

                        $row->av_status = '';
                        $row->assembly_code = '';


                        $row->ssurn = $printable->ss_family_id;
                        $row->sws_card_no = $printable->ss_family_id;
                        $row->village_town_city = '';
                        $row->house_premise_no = '';
                        if (trim($printable->genderid) == 'M')
                            $gender = 'Male';
                        else if (trim($printable->genderid) == 'F')
                            $gender = 'Female';
                        else
                            $gender = 'Others';
                        $row->gender = $gender;
                        $f_name = '';
                        $m_name = '';
                        $l_name = '';
                        $name_explode = explode(' ', trim($printable->fathername));
                        if (count($name_explode) == 1) {
                            $f_name = $name_explode[0];
                            $m_name = '';
                            $l_name = '';
                        } else if (count($name_explode) == 2) {
                            $f_name = $name_explode[0];
                            $m_name = '';
                            $l_name = $name_explode[1];
                        } else if (count($name_explode) == 3) {
                            $f_name = $name_explode[0];
                            $m_name = $name_explode[1];
                            $l_name = $name_explode[2];
                        } else {
                            $f_name = trim($printable->name);
                            $m_name = '';
                            $l_name = '';
                        }
                        $row->father_fname = $f_name;
                        $row->father_mname = $m_name;
                        $row->father_lname = $l_name;
                        $row->mother_fname = '';
                        $row->mother_mname = '';
                        $row->mother_lname = '';
                        $row->dob = '';
                        $row->caste = trim($printable->casttype);
                        $row->marital_status = '';
                        $f_name = '';
                        $m_name = '';
                        $l_name = '';
                        $name_explode = explode(' ', trim($printable->spousename));
                        if (count($name_explode) == 1) {
                            $f_name = $name_explode[0];
                            $m_name = '';
                            $l_name = '';
                        } else if (count($name_explode) == 2) {
                            $f_name = $name_explode[0];
                            $m_name = '';
                            $l_name = $name_explode[1];
                        } else if (count($name_explode) == 3) {
                            $f_name = $name_explode[0];
                            $m_name = $name_explode[1];
                            $l_name = $name_explode[2];
                        } else {
                            $f_name = trim($printable->name);
                            $m_name = '';
                            $l_name = '';
                        }
                        if ($urban_code == 1) {
                            $block_ulb_list = UrbanBody::select('urban_body_code as block_ulb_code', 'urban_body_name as block_ulb_name')->where('sub_district_code', $blockCode)->get();
                            if (count($block_ulb_list) > 0) {
                                $munc_in = $block_ulb_list->pluck('block_ulb_code');
                                $gp_ward_list = Ward::select('urban_body_ward_code as gp_ward_code', 'urban_body_ward_name as gp_ward_name')->whereIn('urban_body_code', $munc_in)->get();
                            }
                        } else {
                            $block_ulb_list = Taluka::select('block_code as block_ulb_code', 'block_name as block_ulb_name')->where('district_code', $dist_code)->get();
                            $row->block_ulb_code = $blockCode;

                            $gp_ward_list = GP::select('gram_panchyat_code as gp_ward_code', 'gram_panchyat_name as gp_ward_name')->where('block_code', $blockCode)->get();
                        }
                        $row->mobile_no =  $printable->mobileno;
                    } else if ($add_edit_status == 3 || $add_edit_status == 2) {
                        $printable = collect([]);
                        $id = NULL;
                        $source_id = NULL;
                        $source_type = NULL;
                        $sws_no = NULL;
                        $row->sws_full_name =  '';
                    }
                    $tab_id = 1;
                    $personalCount = 0;
                    $contactCount = 0;
                    $bankCount = 0;
                    $encolserCount = 0;
                    $otherCount = 0;
                    $application_id = '';
                    $personal_table_id = '';
                    $max_tab_code = 0;
                    $getModelFunc = new getModelFunc();
                    $DraftPersonalTable = new DataSourceCommon;
                    $Table = $getModelFunc->getTable($dist_code, $this->source_type, 1, 1);
                    $DraftPersonalTable->setTable('' . $Table);
                    if ($add_edit_status == 4) {
                        $PersonalnData = $DraftPersonalTable->where('ss_card_no', $sws_no)->where('ss_ben_id', $source_id)->first();
                    } else if ($add_edit_status == 3 || $add_edit_status == 2) {


                        if ($add_edit_status == 2) {
                            $PersonalnData = $DraftPersonalTable->where('application_id', $request->application_id)->where('next_level_role_id', -50);
                        } else {
                            $PersonalnData = $DraftPersonalTable->where('application_id', $request->application_id);
                        }
                        $PersonalnData = $PersonalnData->first();
                        $row->sws_card_no =   $PersonalnData->ss_card_no;
                        $row->sws_full_name =   $PersonalnData->ss_full_name;
                        $row->id =   $PersonalnData->ss_ben_id;
                        $application_id = $request->application_id;
                    }
                    //dd($add_edit_status);
                    if (!empty($PersonalnData)) {
                        if ($PersonalnData->created_by_local_body_code != $blockCode) {
                            return redirect("/lb-wcd-search")->with('error', 'Already Edited by some other user . You cannot edit it.');
                        }
                        $personalCount = 1;
                        $application_id = $PersonalnData->application_id;
                        if ($add_edit_status == 4) {
                            $request->session()->put('lb_draft_app_' . $sws_no . '_' . $request->source_id, $application_id);
                        }
                        $DraftAadharTable = new DataSourceCommon;
                        $Table = $getModelFunc->getTable($dist_code, $this->source_type, 2, 1);
                        $DraftAadharTable->setConnection('pgsql_appread');
                        $DraftAadharTable->setTable('' . $Table);
                        $AadharData = $DraftAadharTable->where('application_id', $PersonalnData->application_id)->first();
                        $tab_id = $PersonalnData->tab_code;
                        $personal_table_id = $PersonalnData->id;
                        $max_tab_code = $PersonalnData->tab_code;
                        $row->ds_phase = $PersonalnData->ds_phase;
                        // dd($row->ds_phase);
                        $row->duare_sarkar_registration_no = $PersonalnData->duare_sarkar_registration_no;
                        $row->duare_sarkar_date = $PersonalnData->duare_sarkar_date;
                        $row->ben_fname = $PersonalnData->ben_fname;
                        $row->ben_mname = $PersonalnData->ben_mname;
                        $row->ben_lname = $PersonalnData->ben_lname;
                        $row->father_fname = $PersonalnData->father_fname;
                        $row->father_mname = $PersonalnData->father_mname;
                        $row->father_lname = $PersonalnData->father_lname;
                        $row->mother_fname = $PersonalnData->mother_fname;
                        $row->mother_mname = $PersonalnData->mother_mname;
                        $row->mother_lname = $PersonalnData->mother_lname;
                        $row->dob = $PersonalnData->dob;
                        if (!empty($PersonalnData->dob)) {
                            $row->ben_age = $this->ageCalculate($PersonalnData->dob);
                        }
                        $row->caste = $PersonalnData->caste;
                        $row->caste_certificate_no = $PersonalnData->caste_certificate_no;
                        $row->marital_status = $PersonalnData->marital_status;
                        $row->spouse_fname = $PersonalnData->spouse_fname;
                        $row->spouse_mname = $PersonalnData->spouse_mname;
                        $row->spouse_lname = $PersonalnData->spouse_lname;
                        $row->mobile_no = $PersonalnData->mobile_no;
                        $row->email = $PersonalnData->email;
                        if (!empty($AadharData)) {
                            $row->aadhar_no = Crypt::decryptString($AadharData->encoded_aadhar);
                        } else
                            $row->aadhar_no = '';
                        if ($tab_id > 1) {
                            $DraftContactTable = new DataSourceCommon;
                            $Table = $getModelFunc->getTable($dist_code, $this->source_type, 3, 1);
                            $DraftAadharTable->setConnection('pgsql_appread');
                            $DraftContactTable->setTable('' . $Table);
                            $contactData = $DraftContactTable->select('dist_code', 'block_ulb_code', 'block_ulb_name', 'gp_ward_code', 'gp_ward_name', 'police_station', 'village_town_city', 'house_premise_no', 'post_office', 'residency_period',  'pincode', 'rural_urban_id')->where('application_id', $application_id)->first();
                            //dd($contactData);
                            if (!empty($contactData)) {
                                $contactCount = 1;
                                $row->dist_code = $contactData->dist_code;
                                $row->rural_urban_id = $contactData->rural_urban_id;
                                $row->police_station = $contactData->police_station;
                                $row->block_ulb_code = $contactData->block_ulb_code;
                                $row->block_ulb_name = $contactData->block_ulb_name;
                                if ($row->rural_urban_id == 1) {
                                    $block_ulb_list = UrbanBody::select('urban_body_code as block_ulb_code', 'urban_body_name as block_ulb_name')->where('district_code', $contactData->dist_code)->get();
                                    $gp_ward_list = Ward::select('urban_body_ward_code as gp_ward_code', 'urban_body_ward_name as gp_ward_name')->where('urban_body_code', $contactData->block_ulb_code)->get();
                                } else {
                                    $block_ulb_list = Taluka::select('block_code as block_ulb_code', 'block_name as block_ulb_name')->where('district_code', $contactData->dist_code)->get();
                                    $gp_ward_list = GP::select('gram_panchyat_code as gp_ward_code', 'gram_panchyat_name as gp_ward_name')->where('block_code', $contactData->block_ulb_code)->get();
                                }
                                $row->gp_ward_code = $contactData->gp_ward_code;
                                $row->gp_ward_name = $contactData->gp_ward_name;
                                $row->village_town_city = $contactData->village_town_city;
                                $row->house_premise_no = $contactData->house_premise_no;
                                $row->post_office = $contactData->post_office;
                                $row->pincode = $contactData->pincode;
                                $row->residency_period = $contactData->residency_period;
                                $row->email = $contactData->email;
                            }
                        }
                        if ($tab_id > 2) {
                            $DraftBankTable = new DataSourceCommon;
                            $Table = $getModelFunc->getTable($dist_code, $this->source_type, 4, 1);
                            $DraftBankTable->setConnection('pgsql_appread');
                            $DraftBankTable->setTable('' . $Table);
                            $bankData = $DraftBankTable->select('bank_code', 'bank_name', 'branch_name', 'bank_ifsc')->where('application_id', $application_id)->first();
                            if (!empty($bankData)) {
                                $bankCount = 1;
                                $row->bank_name = $bankData->bank_name;
                                $row->branch_name = $bankData->branch_name;
                                $row->bank_ifsc = $bankData->bank_ifsc;
                                $row->bank_code = $bankData->bank_code;
                            }
                        }
                        if ($tab_id >= 3) {
                            $DraftPfImageTable = new DataSourceCommon;
                            $Table = $getModelFunc->getTable($dist_code, $this->source_type, 5, 1);
                            $DraftPfImageTable->setConnection('pgsql_encread');
                            $DraftPfImageTable->setTable('' . $Table);

                            $DraftEncloserTable = new DataSourceCommon;
                            $Table = $getModelFunc->getTable($dist_code, $this->source_type, 6, 1);
                            $DraftEncloserTable->setConnection('pgsql_encread');

                            $DraftEncloserTable->setTable('' . $Table);

                            $doc_profile = DocumentType::select('id')->where('is_profile_pic', TRUE)->first();
                            $profileImagedata = $DraftPfImageTable->where('image_type', $doc_profile->id)->where('application_id', $application_id)->first();
                            $encolserdata = $DraftEncloserTable->select('document_type')->where('application_id', $application_id)->get()->pluck('document_type');
                            //dd($encolserdata->toArray());
                            if (!empty($profileImagedata) || count($encolserdata) > 0) {
                                $encolserCount = 1;
                            }
                        }
                        if ($tab_id > 4) {
                            $DraftOtherTable = new DataSourceCommon;
                            $Table = $getModelFunc->getTable($dist_code, $this->source_type, 7, 1);
                            $DraftOtherTable->setConnection('pgsql_appread');

                            $DraftOtherTable->setTable('' . $Table);

                            $otherData = $DraftOtherTable->select('is_resident', 'earn_monthly_remuneration', 'info_genuine_decl')->where('application_id', $application_id)->first();
                            //dd($otherData);
                            if (!empty($otherData)) {
                                $otherCount = 1;
                                $row->is_resident = $otherData->is_resident;
                                $row->earn_monthly_remuneration = $otherData->earn_monthly_remuneration;
                                $row->info_genuine_decl = $otherData->info_genuine_decl;
                            }
                        }
                    }
                } else {
                }
            }

            $document_msg = "";
            $doc_profile_image = DocumentType::get()
                ->where("is_profile_pic", true)->first();

            if ($doc_profile_image) {
                $doc_profile_image_id = $doc_profile_image->id;
            }
            $doc_id_list = SchemeDocMap::select('doc_list_man', 'doc_list_opt', 'doc_list_man_group')->where('scheme_code', $scheme_id)->first()->toArray();
            // dd($doc_id_list['doc_list_man']);
            if (isset($doc_id_list['doc_list_man']) && $doc_id_list['doc_list_man'] != 'null') {
                // dd($doc_id_list);
                $doc_list_man = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->whereIn("id", json_decode($doc_id_list['doc_list_man']))->get()->toArray();
            } else
                $doc_list_man = array();
            if (isset($doc_id_list['doc_list_opt']) && $doc_id_list['doc_list_opt'] != 'null') {
                $doc_list_opt = DocumentType::select('id',  'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->whereIn("id", json_decode($doc_id_list['doc_list_opt']))->get()->toArray();
            } else
                $doc_list_opt = array();
            if (count($doc_list_man) > 0 || count($doc_list_opt) > 0) {
                $doc_list = array_merge($doc_list_man, $doc_list_opt);
            } else {
                $doc_list = array();
            }
            $encloser_list = array();
            $i = 0;
            //dd($doc_list);
            if (count($doc_list) > 0) {
                foreach ($doc_list as $doc) {
                    $encloser_list[$i]['application_id'] = $application_id;
                    $encloser_list[$i]['id'] = $doc['id'];
                    $encloser_list[$i]['is_profile_pic'] = intval($doc['is_profile_pic']);
                    $encloser_list[$i]['doc_size_kb'] = $doc['doc_size_kb'];
                    $encloser_list[$i]['doc_name'] = $doc['doc_name'];
                    $encloser_list[$i]['doc_type'] = $doc['doc_type'];
                    if (in_array($doc['id'], json_decode($doc_id_list['doc_list_man']))) {
                        $encloser_list[$i]['required'] = 1;
                    } else {
                        $encloser_list[$i]['required'] = 0;
                    }
                    if ($doc['is_profile_pic']) {
                        if ($tab_id >= 3) {
                            if (!empty($profileImagedata->application_id)) {
                                $encloser_list[$i]['can_download'] = 1;
                            } else {
                                $encloser_list[$i]['can_download'] = 0;
                            }
                        } else {
                            $encloser_list[$i]['can_download'] = 0;
                        }
                    } else {
                        //dd($encolserdata);
                        if ($tab_id >= 3) {
                            if (in_array($doc['id'], $encolserdata->toArray())) {
                                $encloser_list[$i]['can_download'] = 1;
                            } else {
                                $encloser_list[$i]['can_download'] = 0;
                            }
                        } else {
                            $encloser_list[$i]['can_download'] = 0;
                        }
                    }
                    $i++;
                }
            }
            $entry_allowed = DistrictEntryMapping::where('main_entry', true)->where('district_code',  $dist_code)->count();
            $errormsg = Config::get('constants.errormsg');
            return view('LokkhiBhandarWCD/addForm', [
                'add_edit_status' => $add_edit_status,
                'row' => $row,
                'id' => $id,
                'application_id' => $application_id,
                'source_id' => $source_id,
                'source_type' => $source_type,
                'sws_no' => $sws_no,
                'max_dob' => $max_dob,
                'min_dob' => $this->min_dob,
                'district_list' => $district_list,
                'block_ulb_list' => $block_ulb_list,
                'gp_ward_list' => $gp_ward_list,
                'scheme_id' => $scheme_id,
                'doc_list_man' => $doc_list_man,
                'doc_list_opt' => $doc_list_opt,
                'encloser_list' => $encloser_list,
                'profile_img' => $doc_profile_image_id,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'personalCount' => $personalCount,
                'contactCount' => $contactCount,
                'bankCount' => $bankCount,
                'otherCount' => $otherCount,
                'encolserCount' => $encolserCount,
                'tab_code' => $tab_code,
                'personal_table_id' => $personal_table_id,
                'max_tab_code' => $max_tab_code,
                'application_id' => $application_id,
                'entry_allowed' => $entry_allowed,
                'dob_base_date' => $dob_base_date
            ]);
        }
        if ($is_active == 0) {
            return redirect("/")->with('error', 'User Disabled');
        } else {
            return redirect("/")->with('success', 'User Disabled');
        }
    }
    public function submittedList(Request $request)
    {
        $is_active = 0;
        $ds_phase_list = DsPhase::all();
        // dd($ds_phase_list->toArray());
        if (request()->ajax()) {
            $scheme_id = $this->scheme_id;
            //$rejection_cause_list = Config::get('constants.rejection_cause');
            $rejection_cause_list = RejectRevertReason::where('status', true)->get()->toArray();

            $roleArray = $request->session()->get('role');
            foreach ($roleArray as $roleObj) {
                if ($roleObj['scheme_id'] == $scheme_id) {
                    $is_active = 1;
                    $is_urban = $roleObj['is_urban'];
                    $distCode = $roleObj['district_code'];
                    if ($roleObj['is_urban'] == 1) {
                        $blockCode = $roleObj['urban_body_code'];
                    } else {
                        $blockCode = $roleObj['taluka_code'];
                    }
                    break;
                }
            }
            if ($is_active == 0 || empty($distCode)) {
                return redirect("/")->with('error', 'User Disabled. ');
            }
            $ds_phase = trim($request->ds_phase);
            $modelName = new DataSourceCommon;
            $getModelFunc = new getModelFunc();
            $personal_table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
            $contact_table = $getModelFunc->getTable($distCode, '', 3, 1);
            $modelName->setConnection('pgsql_appread');

            $modelName->setTable('' . $personal_table);
            $condition = array();
            $condition[$personal_table . ".created_by_dist_code"] = $distCode;
            $condition[$personal_table . ".created_by_local_body_code"] = $blockCode;
            $condition["is_final"] = true;
            if (!empty($ds_phase)) {
                $condition[$personal_table . ".ds_phase"] = $ds_phase;
            }
            // $condition["payment_count"] = 0;
            // $condition["lot_generated"] = 0;
            if (!empty($request->search['value']))
                $serachvalue = $request->search['value'];
            else
                $serachvalue = '';
            $limit = $request->input('length');
            $offset = $request->input('start');
            $totalRecords = 0;
            $filterRecords = 0;
            $data = array();

            $query = $modelName->where($condition)->whereNull('next_level_role_id');
            $query = $query->join($contact_table, $contact_table . '.application_id', '=', $personal_table . '.application_id');

            if (empty($serachvalue)) {
                $totalRecords = $query->count($personal_table . '.application_id');
                $data = $query->orderBy($personal_table . '.application_id', 'ASC')->offset($offset)->limit($limit)->get([
                    $personal_table . '.application_id', 'ben_fname', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname', 'mobile_no', 'duare_sarkar_registration_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id',$personal_table . '.ds_phase'
                ]);
                $filterRecords = count($data);
            } else {
                if (preg_match('/^[0-9]*$/', $serachvalue)) {
                    $query = $query->where(function ($query1) use ($serachvalue, $personal_table) {
                        if (strlen($serachvalue) < 10) {
                            $query1->where($personal_table . '.application_id', $serachvalue);
                        } else if (strlen($serachvalue) == 10) {
                            $query1->where('mobile_no', $serachvalue);
                        } else if (strlen($serachvalue) == 17) {
                            $query1->where('ss_card_no', $serachvalue);
                        } else if (strlen($serachvalue) == 20) {
                            $query1->where('duare_sarkar_registration_no', $serachvalue);
                        }
                    });
                    $totalRecords = $query->count($personal_table . '.application_id');
                    $data = $query->orderBy($personal_table . '.application_id', 'ASC')->offset($offset)->limit($limit)->get(
                        [
                            $personal_table . '.application_id', 'ben_fname', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname', 'mobile_no', 'duare_sarkar_registration_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id',$personal_table . '.ds_phase'

                        ]
                    );
                } else {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        $query1->where('ben_fname', 'like', $serachvalue . '%')
                            ->orWhere('gp_ward_name', 'like', $serachvalue . '%')->orWhere('block_ulb_name', 'like', $serachvalue . '%');
                    });
                    $totalRecords = $query->count($personal_table . '.application_id');
                    $data = $query->orderBy($personal_table . '.application_id', 'ASC')->offset($offset)->limit($limit)->get(
                        [
                            $personal_table . '.application_id', 'ben_fname', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname', 'mobile_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id',$personal_table . '.ds_phase'

                        ]
                    );
                }
                $filterRecords = count($data);
            }
            return datatables()->of($data)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($filterRecords)
                ->skipPaging()
                ->addColumn('Edit', function ($data) {
                    if(isset($data->ds_phase) && $data->ds_phase>6){
                    // $action = '<a href="lb-entry-draft-edit?application_id=' . $data->application_id . '&status=2" class="btn btn-xs btn-info">Edit</a>';
                    $action = '<a href="formEntry?scheme_slug=lb_wcd&add_edit_status=3&application_id=' . $data->application_id . '" class="btn btn-xs btn-info">Edit</a>';
                    $action =   $action . '&nbsp&nbsp&nbsp';
                    $action =   $action . '<a href="application-details-view?scheme_slug=lb_wcd&is_draft=1&application_id=' . $data->application_id . '" class="btn btn-xs btn-primary">View</a>';
                    }
                    else{
                        $action = '<a href="formEntry?scheme_slug=lb_wcd&add_edit_status=3&application_id=' . $data->application_id . '" class="btn btn-xs btn-info">Edit</a>';
                        $action =   $action . '&nbsp&nbsp&nbsp';
                        $action =   $action . '<a href="application-details-view?scheme_slug=lb_wcd&is_draft=1&application_id=' . $data->application_id . '" class="btn btn-xs btn-primary">View</a>'; 
                    }


                    return $action;
                })->addColumn('id', function ($data) {
                    return $data->application_id;
                })
                ->addColumn('ben_id', function ($data) {
                    return $data->id;
                })->addColumn('name', function ($data) {
                    return $data->ben_fname;
                })->addColumn('family_id', function ($data) {
                    return $data->family_id;
                })
                ->addColumn('ben_fname', function ($data) {
                    return $data->ben_fname;
                })
                ->addColumn('ben_mname', function ($data) {
                    return $data->ben_mname;
                })
                ->addColumn('ben_lname', function ($data) {
                    return $data->ben_lname;
                })->addColumn('father_fname', function ($data) {
                    return $data->father_fname;
                })->addColumn('father_mname', function ($data) {
                    return $data->father_mname;
                })->addColumn('father_lname', function ($data) {
                    return $data->father_lname;
                })->addColumn('father_name', function ($data) {
                    return ($data->father_fname . ' ' . $data->father_mname . ' ' . $data->father_lname);
                })->addColumn('mobile_no', function ($data) {
                    return $data->mobile_no;
                })->addColumn('dob', function ($data) {
                    return $data->dob;
                })->addColumn('ben_age', function ($data) {
                    if (!empty($data->dob)) {
                        $ben_age = $this->ageCalculate($data->dob);
                    } else {
                        $ben_age = '';
                    }
                    return $ben_age;
                })->addColumn('duare_sarkar_registration_no', function ($data) {
                    return $data->duare_sarkar_registration_no;
                })
                ->rawColumns(['Edit', 'id', 'name'])
                ->make(true);
        }
        $errormsg = Config::get('constants.errormsg');
        return view(
            'LokkhiBhandarWCD.linelisting_submitted',
            [
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut'],
                'ds_phase_list' => $ds_phase_list
            ]
        );
    }

    public function revertedList(Request $request)
    {
        $rejection_cause_list = RejectRevertReason::where('status', true)->get()->toArray();
        $ds_phase_list = DsPhase::all();
        if (request()->ajax()) {
            $scheme_id = $this->scheme_id;
            //$rejection_cause_list = Config::get('constants.rejection_cause');
            // dd( $rejection_cause_list);

            $roleArray = $request->session()->get('role');
            foreach ($roleArray as $roleObj) {
                if ($roleObj['scheme_id'] == $scheme_id) {
                    $is_active = 1;
                    $is_urban = $roleObj['is_urban'];
                    $distCode = $roleObj['district_code'];
                    if ($roleObj['is_urban'] == 1) {
                        $blockCode = $roleObj['urban_body_code'];
                    } else {
                        $blockCode = $roleObj['taluka_code'];
                    }
                    break;
                }
            }
            if ($is_active == 0 || empty($distCode)) {
                return redirect("/")->with('error', 'User Disabled. ');
            }
            $modelName = new DataSourceCommon;
            $getModelFunc = new getModelFunc();
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 1, 1);
            $modelName->setConnection('pgsql_appread');
            $ds_phase = trim($request->ds_phase);
            $modelName->setTable('' . $Table);

            $condition = array();
            $condition["created_by_dist_code"] = $distCode;
            $condition["created_by_local_body_code"] = $blockCode;
            if (!empty($ds_phase)) {
                $condition["ds_phase"] = $ds_phase;
            }
            if (!empty($request->search['value']))
                $serachvalue = $request->search['value'];
            else
                $serachvalue = '';
            $limit = $request->input('length');
            $offset = $request->input('start');
            $totalRecords = 0;
            $filterRecords = 0;
            $data = array();

            $query = $modelName->where($condition)->where('next_level_role_id', -50);
            if (empty($serachvalue)) {
                $totalRecords = $query->count('application_id');
                $data = $query->orderBy('application_id', 'ASC')->offset($offset)->limit($limit)->get([
                    'rejected_cause', 'comments', 'application_id', 'ben_fname', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname', 'mobile_no', 'duare_sarkar_registration_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id','ds_phase'
                ]);
                $filterRecords = count($data);
            } else {
                if (is_numeric($serachvalue)) {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        if (strlen($serachvalue) < 10) {
                            $query1->where('application_id', $serachvalue);
                        } else if (strlen($serachvalue) == 10) {
                            $query1->where('mobile_no', $serachvalue);
                        } else if (strlen($serachvalue) == 17) {
                            $query1->where('ss_card_no', $serachvalue);
                        } else if (strlen($serachvalue) == 20) {
                            $query1->where('duare_sarkar_registration_no', $serachvalue);
                        }
                    });
                    $totalRecords = $query->count('application_id');
                    $data = $query->orderBy('application_id', 'ASC')->offset($offset)->limit($limit)->get(
                        [
                            'rejected_cause', 'comments', 'application_id', 'ben_fname', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname', 'mobile_no', 'duare_sarkar_registration_no', 'dob', 'age_ason_01012021', 'ss_card_no',   'next_level_role_id','ds_phase'

                        ]
                    );
                } else {
                    $query = $query->where(function ($query1) use ($serachvalue) {
                        $query1->where('ben_fname', 'like', $serachvalue . '%');
                    });
                    $totalRecords = $query->count('application_id');
                    $data = $query->orderBy('application_id', 'ASC')->offset($offset)->limit($limit)->get(
                        [
                            'rejected_cause', 'comments', 'application_id', 'ben_fname', 'ben_mname', 'ben_lname', 'father_fname', 'father_mname', 'father_lname', 'mother_fname', 'mother_mname', 'mother_lname', 'mobile_no', 'duare_sarkar_registration_no', 'dob', 'age_ason_01012021', 'ss_card_no', 'next_level_role_id','ds_phase'

                        ]
                    );
                }
                $filterRecords = count($data);
            }
            return datatables()->of($data)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($filterRecords)
                ->skipPaging()
                ->addColumn('Edit', function ($data) {
                    if(isset($data->ds_phase) && $data->ds_phase<=6){
                    $action = '<a href="formEntry?scheme_slug=lb_wcd&add_edit_status=2&application_id=' . $data->application_id . '" class="btn btn-xs btn-info">Edit</a>';
                    $action =   $action . '&nbsp&nbsp&nbsp';
                    $action =   $action . '<a href="application-details-view?is_draft=1&scheme_slug=lb_wcd&application_id=' . $data->application_id . '" class="btn btn-xs btn-primary">View</a>';
                    $action =   $action . '&nbsp&nbsp&nbsp';
                    $action =  $action . '<button value="' . $data->application_id . '" id="rej_' . $data->application_id . '" class="btn btn-danger btn-xs rej-btn" type="button">Reject</button>';
                    }
                    else{
                    $action = '<a href="lb-entry-draft-edit?application_id=' . $data->application_id . '&status=3" class="btn btn-xs btn-info">Edit</a>';
                    $action =   $action . '&nbsp&nbsp&nbsp';
                    $action =   $action . '<a href="application-details-view?is_draft=1&scheme_slug=lb_wcd&application_id=' . $data->application_id . '" class="btn btn-xs btn-primary">View</a>';
                    $action =   $action . '&nbsp&nbsp&nbsp';
                    $action =  $action . '<button value="' . $data->application_id . '" id="rej_' . $data->application_id . '" class="btn btn-danger btn-xs rej-btn" type="button">Reject</button>';
                    }
                    return $action;
                })->addColumn('id', function ($data) {
                    return $data->application_id;
                })
                ->addColumn('ben_id', function ($data) {
                    return $data->id;
                })->addColumn('name', function ($data) {
                    return $data->ben_fname;
                })->addColumn('family_id', function ($data) {
                    return $data->family_id;
                })
                ->addColumn('ben_fname', function ($data) {
                    return $data->ben_fname;
                })
                ->addColumn('ben_mname', function ($data) {
                    return $data->ben_mname;
                })
                ->addColumn('ben_lname', function ($data) {
                    return $data->ben_lname;
                })->addColumn('father_fname', function ($data) {
                    return $data->father_fname;
                })->addColumn('father_mname', function ($data) {
                    return $data->father_mname;
                })->addColumn('father_lname', function ($data) {
                    return $data->father_lname;
                })->addColumn('father_name', function ($data) {
                    return ($data->father_fname . ' ' . $data->father_mname . ' ' . $data->father_lname);
                })->addColumn('mobile_no', function ($data) {
                    return $data->mobile_no;
                })->addColumn('dob', function ($data) {
                    return $data->dob;
                })->addColumn('ben_age', function ($data) {
                    if (!empty($data->dob)) {
                        $ben_age = $this->ageCalculate($data->dob);
                    } else {
                        $ben_age = '';
                    }
                    return $ben_age;
                })->addColumn('duare_sarkar_registration_no', function ($data) {
                    return $data->duare_sarkar_registration_no;
                })->addColumn('rejected_reason', function ($data) use ($rejection_cause_list) {
                    $description = '';
                    foreach ($rejection_cause_list as $rejArr) {
                        if ($rejArr['id'] == $data->rejected_cause) {
                            $description = $rejArr['reason'];
                            break;
                        }
                    }
                    return $description;
                })->addColumn('reverted_remarks', function ($data) {
                    return trim($data->comments);
                })
                ->rawColumns(['Edit', 'id', 'name'])
                ->make(true);
        }
        $errormsg = Config::get('constants.errormsg');
        return view(
            'LokkhiBhandarWCD.linelisting_reverted',
            [
                'reject_revert_reason' => $rejection_cause_list,
                'ds_phase_list' => $ds_phase_list,
                'sessiontimeoutmessage' => $errormsg['sessiontimeOut']
            ]
        );
    }
    public function applicantreadonlyview(Request $request)
    {
        $district_list =  District::select(
            'id',
            'district_code',
            'district_name',
            'rch_district_code',
            'is_revenue_district',
            'state_code',
            'district_status'
        )->get();
        $user_id = Auth::user()->id;
        //dd($request->toArray());
        $application_id = $request->application_id;
        $ben_id = $request->ben_id;
        $designation_id = Auth::user()->designation_id;
        $scheme_id = $this->scheme_id;
        $row = array();
        $is_active = 0;
        $roleArray = $request->session()->get('role');
        foreach ($roleArray as $roleObj) {
            if ($roleObj['scheme_id'] == $scheme_id) {
                $is_active = 1;
                $mapping_level = $roleObj['mapping_level'];
                $distCode = $roleObj['district_code'];
                $is_urban = $roleObj['is_urban'];
                if ($roleObj['is_urban'] == 1) {
                    $blockCode = $roleObj['urban_body_code'];
                } else {
                    $blockCode = $roleObj['taluka_code'];
                }
                break;
            }
        }
        if ($is_active == 0 || empty($distCode)) {
            return redirect("/")->with('error', 'User Disabled');
        }
        if (empty($application_id)) {
            return redirect("/")->with('error', 'Applicant ID Not Pass');
        }
        if (!empty($request->is_draft)) {
            $is_draft = 1;
        } else {
            $is_draft = NULL;
        }
        $getModelFunc = new getModelFunc();
        if ($request->is_reject == 1) {
            // dd('ok');
            $DraftPersonalTable = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 10);
            $DraftPersonalTable->setConnection('pgsql_appread');

            $DraftPersonalTable->setTable('' . $Table);
            $PersonalnData = $DraftPersonalTable->where('application_id', $application_id)->first();
            // dd($PersonalnData);
            if (empty($PersonalnData)) {
                return redirect("/")->with('error', 'Applicant ID Not Valid');
            }
            $reject_row = RejectRevertReason::where('id', $PersonalnData->rejected_cause)->first();
        } else {
            //dd('ok');
            if ($is_draft == 2) {
                $DraftPersonalTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 10);
                $DraftPersonalTable->setConnection('pgsql_appread');

                $DraftPersonalTable->setTable('' . $Table);
                $PersonalnData = $DraftPersonalTable->where('application_id', $application_id);
            } else {
                $DraftPersonalTable = new DataSourceCommon;
                $Table = $getModelFunc->getTable($distCode, $this->source_type, 1,  $is_draft);
                $DraftPersonalTable->setConnection('pgsql_appread');

                $DraftPersonalTable->setTable('' . $Table);
                $PersonalnData = $DraftPersonalTable->where('application_id', $application_id);
                if ($is_draft == 1) {
                    $PersonalnData = $PersonalnData->where('is_final', TRUE);
                }
                $PersonalnData = $PersonalnData->first();
                if (empty($PersonalnData)) {
                    return redirect("/")->with('error', 'Applicant ID Not Valid1');
                }
            }
        }
        $row = collect([]);
        $row->sws_card_no = $PersonalnData->ss_card_no;
        $row->duare_sarkar_registration_no = $PersonalnData->duare_sarkar_registration_no;
        $row->duare_sarkar_date = $PersonalnData->duare_sarkar_date;
        $row->gender = $PersonalnData->gender;
        $row->application_id = $application_id;
        $row->ben_fname = $PersonalnData->ben_fname;
        $row->ben_mname = $PersonalnData->ben_mname;
        $row->ben_lname = $PersonalnData->ben_lname;
        $row->father_fname = $PersonalnData->father_fname;
        $row->father_mname = $PersonalnData->father_mname;
        $row->father_lname = $PersonalnData->father_lname;
        $row->mother_fname = $PersonalnData->mother_fname;
        $row->mother_mname = $PersonalnData->mother_mname;
        $row->mother_lname = $PersonalnData->mother_lname;
        $row->dob = $PersonalnData->dob;
        if (!empty($PersonalnData->dob)) {
            $row->ben_age = $this->ageCalculate($PersonalnData->dob);
        }
        //$row->ben_age = $PersonalnData->age_ason_01012021;
        $row->caste = $PersonalnData->caste;
        $row->caste_certificate_no = $PersonalnData->caste_certificate_no;
        $row->marital_status = $PersonalnData->marital_status;
        $row->spouse_fname = $PersonalnData->spouse_fname;
        $row->spouse_mname = $PersonalnData->spouse_mname;
        $row->spouse_lname = $PersonalnData->spouse_lname;
        $row->mobile_no = $PersonalnData->mobile_no;
        $row->aadhar_no = $PersonalnData->aadhar_no;
        $row->email = $PersonalnData->email;
        $row->next_level_role_id = $PersonalnData->next_level_role_id;
        $row->comments = $PersonalnData->comments;
        if ($request->is_reject == 1) {
            $row->dist_code = $PersonalnData->dist_code;
            $row->rural_urban_id = $PersonalnData->rural_urban_id;
            $row->police_station = $PersonalnData->police_station;
            $row->block_ulb_code = $PersonalnData->block_ulb_code;
            $row->block_ulb_name = $PersonalnData->block_ulb_name;
            $row->gp_ward_code = $PersonalnData->gp_ward_code;
            $row->gp_ward_name = $PersonalnData->gp_ward_name;
            $row->village_town_city = $PersonalnData->village_town_city;
            $row->house_premise_no = $PersonalnData->house_premise_no;
            $row->post_office = $PersonalnData->post_office;
            $row->pincode = $PersonalnData->pincode;
            $row->residency_period = $PersonalnData->residency_period;
            $row->email = $PersonalnData->email;
            $row->rejected_cause =  $reject_row->reason;
        } else {
            $DraftContactTable = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 3,  $is_draft);
            $DraftContactTable->setConnection('pgsql_appread');

            $DraftContactTable->setTable('' . $Table);
            $contactData = $DraftContactTable->select('dist_code', 'block_ulb_code', 'block_ulb_name', 'gp_ward_code', 'gp_ward_name', 'police_station', 'village_town_city', 'house_premise_no', 'post_office', 'residency_period',  'pincode', 'rural_urban_id')->where('application_id', $application_id)->first();
            $row->dist_code = $contactData->dist_code;
            $row->rural_urban_id = $contactData->rural_urban_id;
            $row->police_station = $contactData->police_station;
            $row->block_ulb_code = $contactData->block_ulb_code;
            $row->block_ulb_name = $contactData->block_ulb_name;
            $row->gp_ward_code = $contactData->gp_ward_code;
            $row->gp_ward_name = $contactData->gp_ward_name;
            $row->village_town_city = $contactData->village_town_city;
            $row->house_premise_no = $contactData->house_premise_no;
            $row->post_office = $contactData->post_office;
            $row->pincode = $contactData->pincode;
            $row->residency_period = $contactData->residency_period;
        }
        if ($request->is_reject == 1) {
            $row->bank_name = $PersonalnData->bank_name;
            $row->branch_name = $PersonalnData->branch_name;
            $row->bank_ifsc = $PersonalnData->bank_ifsc;
            $row->bank_code = $PersonalnData->bank_code;
        } else {
            $DraftBankTable = new DataSourceCommon;
            $Table = $getModelFunc->getTable($distCode, $this->source_type, 4,  $is_draft);
            $DraftBankTable->setConnection('pgsql_appread');

            $DraftBankTable->setTable('' . $Table);
            $bankData = $DraftBankTable->select('bank_code', 'bank_name', 'branch_name', 'bank_ifsc')->where('application_id', $application_id)->first();
            $row->bank_name = $bankData->bank_name;
            $row->branch_name = $bankData->branch_name;
            $row->bank_ifsc = $bankData->bank_ifsc;
            $row->bank_code = $bankData->bank_code;
        }
        $DraftPfImageTable = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 5,  $is_draft);
        $DraftPfImageTable->setConnection('pgsql_encread');
        $DraftPfImageTable->setTable('' . $Table);
        $DraftEncloserTable = new DataSourceCommon;
        $Table = $getModelFunc->getTable($distCode, $this->source_type, 6,  $is_draft);
        $DraftEncloserTable->setConnection('pgsql_encread');
        $DraftEncloserTable->setTable('' . $Table);
        $doc_profile = DocumentType::select('id')->where('is_profile_pic', TRUE)->first();
        $profileImagedata = $DraftPfImageTable->where('image_type', $doc_profile->id)->where('application_id', $application_id)->first();
        $encolserdata = $DraftEncloserTable->select('document_type')->where('application_id', $application_id)->get()->pluck('document_type');

        $districts = $district_list;
        if ($request->is_reject == 1) {
            $district_row = $district_list->where('district_code', $PersonalnData->dist_code)->first();
            if (trim($PersonalnData->rural_urban_id == 1)) {
                $block_munc_row = Urbanbody::where('urban_body_code', $row->block_ulb_code)->first();
                $block_mun_name = trim($block_munc_row->urban_body_name);
                $gp_ward_row = Ward::where('urban_body_ward_code', $row->gp_ward_code)->first();
                $gp_ward_name = trim($gp_ward_row->urban_body_ward_name);
            } else {
                $block_munc_row = Taluka::where('block_code', $row->block_ulb_code)->first();
                $block_mun_name = trim($block_munc_row->block_name);
                $gp_ward_row = GP::where('gram_panchyat_code', $row->gp_ward_code)->first();
                $gp_ward_name = trim($gp_ward_row->gram_panchyat_name);
            }
        } else {
            $district_row = $district_list->where('district_code', $contactData->dist_code)->first();
            if (trim($contactData->rural_urban_id == 1)) {
                $block_munc_row = Urbanbody::where('urban_body_code', $row->block_ulb_code)->first();
                $block_mun_name = trim($block_munc_row->urban_body_name);
                $gp_ward_row = Ward::where('urban_body_ward_code', $row->gp_ward_code)->first();
                $gp_ward_name = trim($gp_ward_row->urban_body_ward_name);
            } else {
                $block_munc_row = Taluka::where('block_code', $row->block_ulb_code)->first();
                $block_mun_name = trim($block_munc_row->block_name);
                $gp_ward_row = GP::where('gram_panchyat_code', $row->gp_ward_code)->first();
                $gp_ward_name = trim($gp_ward_row->gram_panchyat_name);
            }
            if ($PersonalnData->next_level_role_id == -50) {
                if (!empty($PersonalnData->rejected_cause)) {
                    $reject_row = RejectRevertReason::where('id', $PersonalnData->rejected_cause)->first();
                    $row->rejected_cause =  $reject_row->reason;
                } else {
                    $row->rejected_cause =  '';
                }
            }
        }
        $row->dist_name = trim($district_row->district_name);

        $row->block_ulb_name = $block_mun_name;
        $row->gp_ward_name = $gp_ward_name;
        $row->fotter_text = '';
        $doc_id_list = SchemeDocMap::select('doc_list_man', 'doc_list_opt', 'doc_list_man_group')->where('scheme_code', $scheme_id)->first();

        if (!empty($doc_id_list->doc_list_man))
            $doc_list_man = DocumentType::get()->whereIn("id", json_decode($doc_id_list->doc_list_man));
        else
            $doc_list_man = collect([]);
        if (!empty($doc_id_list->doc_list_opt))
            $doc_list_opt = DocumentType::get()->whereIn("id", json_decode($doc_id_list->doc_list_opt));
        else
            $doc_list_opt = collect([]);


        $doc_id_list = SchemeDocMap::select('doc_list_man', 'doc_list_opt', 'doc_list_man_group')->where('scheme_code', $scheme_id)->first()->toArray();
        // dd($doc_id_list['doc_list_man']);
        if (isset($doc_id_list['doc_list_man']) && $doc_id_list['doc_list_man'] != 'null') {
            // dd($doc_id_list);
            $doc_list_man = DocumentType::select('id', 'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->whereIn("id", json_decode($doc_id_list['doc_list_man']))->get()->toArray();
        } else
            $doc_list_man = array();
        if (isset($doc_id_list['doc_list_opt']) && $doc_id_list['doc_list_opt'] != 'null') {
            $doc_list_opt = DocumentType::select('id',  'is_profile_pic', 'doc_size_kb', 'doc_name', 'doc_type', 'doucument_group')->whereIn("id", json_decode($doc_id_list['doc_list_opt']))->get()->toArray();
        } else
            $doc_list_opt = array();
        if (count($doc_list_man) > 0 || count($doc_list_opt) > 0) {
            $doc_list = array_merge($doc_list_man, $doc_list_opt);
        } else {
            $doc_list = array();
        }
        $encloser_list = array();
        $i = 0;
        // dd($doc_list);
        if (count($doc_list) > 0) {
            foreach ($doc_list as $doc) {


                if ($doc['is_profile_pic']) {

                    if (!empty($profileImagedata->application_id)) {
                        $encloser_list[$i]['id'] = $doc['id'];
                        $encloser_list[$i]['is_profile_pic'] = $doc['is_profile_pic'];

                        $encloser_list[$i]['doc_name'] = $doc['doc_name'];
                        $encloser_list[$i]['can_download'] = 1;
                        $i++;
                    }
                } else {

                    if (in_array($doc['id'], $encolserdata->toArray())) {
                        $encloser_list[$i]['id'] = $doc['id'];
                        $encloser_list[$i]['is_profile_pic'] = $doc['is_profile_pic'];
                        $encloser_list[$i]['doc_name'] = $doc['doc_name'];
                        $i++;
                    }
                }
            }
        }
        //dd($encloser_list);
        $errormsg = Config::get('constants.errormsg');
        if (!empty($row->dob)) {
            $row->ben_age = $this->ageCalculate($row->dob);
        }
        return view('LokkhiBhandarWCD/pension_view_details_read_only', [
            'designation_id' => $designation_id, 'application_id' => $application_id, 'row' => $row,  'districts' => $districts, 'scheme_id' => $scheme_id, 'doc_list_man' => $doc_list_man, 'doc_list_opt' => $doc_list_opt,
            'encloser_list' => $encloser_list,
            'is_draft' => $is_draft,
            'ben_id' => $ben_id,
            'is_reject' => $request->is_reject,
            'sessiontimeoutmessage' => $errormsg['sessiontimeOut']
        ]);
    }
    public function partialReject(Request $request)
    {
        try {
            $is_active = 0;
            $scheme_id = $this->scheme_id;
            $user_id = Auth::user()->id;
            $designation_id = Auth::user()->designation_id;
            $errormsg = Config::get('constants.errormsg');
            $roleArray = $request->session()->get('role');
            foreach ($roleArray as $roleObj) {
                if ($roleObj['scheme_id'] == $scheme_id) {
                    $is_active = 1;
                    $is_urban = $roleObj['is_urban'];
                    $district_code = $roleObj['district_code'];
                    $mapping_level = $roleObj['mapping_level'];
                    if ($roleObj['is_urban'] == 1) {
                        $blockCode = $roleObj['urban_body_code'];
                        $urban_code = 1;
                    } else {
                        $blockCode = $roleObj['taluka_code'];
                        $urban_code = 2;
                    }
                    break;
                }
            }
            if ($is_active == 0 || empty($district_code)) {
                return redirect("/")->with('error', 'User Disabled. ');
            }
            if ($designation_id != 'Operator') {
                return redirect("/")->with('error', 'User Disabled. ');
            }
            $application_id = $request->application_id;
            $reject_cause = $request->reject_cause;
            if (empty($application_id)) {
                return redirect("/")->with('error', ' Application Id Not Found');
            }

            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $personal_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
            $personal_model->setTable('' . $Table);
            $row = $personal_model->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $blockCode)->first();
            $bank_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 4, 1);
            $bank_model->setTable('' . $Table);
            $row_bank = $bank_model->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $blockCode)->first();

            $ds_phase=$row->ds_phase;
            if($ds_phase<=6){
                $url='lb-wcd-search';
            }
            else{
                $url='lb-draft-list';
            }
            if (empty($row->application_id)) {
                return redirect("/".$url)->with('error', 'Application Id not valid');
            }
           
            $cnt = RejectRevertReason::where('id', $reject_cause)->count();
            if ($cnt == 0) {
                return redirect("/".$url)->with('error', 'Rejection Cause not valid');
            }
            $in_pension_id = 'ARRAY[' . "'$row->application_id'" . ']';
            DB::beginTransaction();
            try {
                $input = ['rejected_cause' => $reject_cause,'action_by' => Auth::user()->id,'action_ip_address' => request()->ip(),'action_type' => class_basename(request()->route()->getAction()['controller'])
];
                $is_status_updated = $personal_model->where('application_id', $application_id)->update($input);
                $accept_reject_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
                $accept_reject_model->setTable('' . $Table);
                $accept_reject_model->op_type = 'PR';
                $accept_reject_model->application_id = $row->application_id;
                $accept_reject_model->designation_id = $designation_id;
                $accept_reject_model->scheme_id = $scheme_id;
                $accept_reject_model->user_id = $user_id;
                $accept_reject_model->mapping_level = $mapping_level;
                $accept_reject_model->created_by = $user_id;
                $accept_reject_model->created_by_level = $mapping_level;
                $accept_reject_model->created_by_dist_code = $district_code;
                $accept_reject_model->created_by_local_body_code = $blockCode;
                $accept_reject_model->rejected_reverted_cause = $reject_cause;
                $accept_reject_model->ip_address = request()->ip();
                $is_saved = $accept_reject_model->save();
                //$reject_fun_arr = DB::select("select lb_scheme.beneficiary_rejected_partial(" . $in_pension_id . ")");
                $reject_fun_arr = DB::select("select lb_scheme.beneficiary_rejected_partial(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."')");

                $beneficiary_rejected_partial = $reject_fun_arr[0]->beneficiary_rejected_partial;
                
                if($is_saved && $beneficiary_rejected_partial==1){
                    DB::commit();
                    return redirect("/".$url)->with('success', 'Application has been successfully rejected')->with('id', $application_id);
                }
               else{
                DB::rollback();
                return redirect("/".$url)->with('error', $errormsg['roolback']);
               }
            } catch (\Exception $e) {
                //dd($e);
                DB::rollback();
                return redirect("/".$url)->with('error', $errormsg['roolback']);
            }
        } catch (\Exception $e) {
            //dd($e);
            return redirect("/".$url)->with('error', $errormsg['roolback']);
        }
    }
    public function revertReject(Request $request)
    {
        try {
            $is_active = 0;
            $scheme_id = $this->scheme_id;
            $user_id = Auth::user()->id;
            $designation_id = Auth::user()->designation_id;
            $errormsg = Config::get('constants.errormsg');
            $roleArray = $request->session()->get('role');
            foreach ($roleArray as $roleObj) {
                if ($roleObj['scheme_id'] == $scheme_id) {
                    $is_active = 1;
                    $is_urban = $roleObj['is_urban'];
                    $district_code = $roleObj['district_code'];
                    $mapping_level = $roleObj['mapping_level'];
                    if ($roleObj['is_urban'] == 1) {
                        $blockCode = $roleObj['urban_body_code'];
                        $urban_code = 1;
                    } else {
                        $blockCode = $roleObj['taluka_code'];
                        $urban_code = 2;
                    }
                    break;
                }
            }
            if ($is_active == 0 || empty($district_code)) {
                return redirect("/")->with('error', 'User Disabled. ');
            }
            if ($designation_id != 'Operator') {
                return redirect("/")->with('error', 'User Disabled. ');
            }
            $application_id = $request->application_id;
            $reject_cause = $request->reject_cause;
            if (empty($application_id)) {
                return redirect("/")->with('error', ' Application Id Not Found');
            }

            $getModelFunc = new getModelFunc();
            $schemaname = $getModelFunc->getSchemaDetails();
            $personal_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
            $personal_model->setTable('' . $Table);
            $row = $personal_model->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $blockCode)->first();
          
            if (empty($row->application_id)) {
                return redirect("/reverted-list")->with('error', 'Application Id not valid');
            }
            $bank_model = new DataSourceCommon;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 4, 1);
            $bank_model->setTable('' . $Table);
            $row_bank = $bank_model->where('application_id', $request->application_id)->where('created_by_dist_code', $district_code)->where('created_by_local_body_code', $blockCode)->first();
            $cnt = RejectRevertReason::where('id', $reject_cause)->count();
            if ($cnt == 0) {
                return redirect("/reverted-list")->with('error', 'Rejection Cause not valid');
            }
            $in_pension_id = 'ARRAY[' . "'$row->application_id'" . ']';
            DB::beginTransaction();
            try {
                $input = ['rejected_cause' => $reject_cause];
                $is_status_updated = $personal_model->where('application_id', $application_id)->update($input);
                $accept_reject_model = new DataSourceCommon;
                $Table = $getModelFunc->getTable($district_code, $this->source_type, 9);
                $accept_reject_model->setTable('' . $Table);
                $accept_reject_model->op_type = 'RR';
                $accept_reject_model->application_id = $row->application_id;
                $accept_reject_model->designation_id = $designation_id;
                $accept_reject_model->scheme_id = $scheme_id;
                $accept_reject_model->user_id = $user_id;
                $accept_reject_model->mapping_level = $mapping_level;
                $accept_reject_model->created_by = $user_id;
                $accept_reject_model->created_by_level = $mapping_level;
                $accept_reject_model->created_by_dist_code = $district_code;
                $accept_reject_model->created_by_local_body_code = $blockCode;
                $accept_reject_model->rejected_reverted_cause = $reject_cause;
                $accept_reject_model->ip_address = request()->ip();
                $is_saved = $accept_reject_model->save();
               // $beneficiary_rejected_partial_arr = DB::select("select lb_scheme.beneficiary_rejected_partial(" . $in_pension_id . ")");
               $beneficiary_rejected_partial_arr = DB::select("select lb_scheme.beneficiary_rejected_partial(in_application_id => $in_pension_id,in_action_by => $user_id,in_ip_address => '".request()->ip()."', in_action_type => '".class_basename(request()->route()->getAction()['controller'])."')");

                $beneficiary_rejected_partial = $beneficiary_rejected_partial_arr[0]->beneficiary_rejected_partial;
                
                if($is_saved && $beneficiary_rejected_partial==1){
                    DB::commit();
                    return redirect("/reverted-list")->with('success', 'Application has been successfully rejected')->with('id', $application_id);
                }
               else{
                DB::rollback();
                return redirect("/reverted-list")->with('error', $errormsg['roolback']);
               }
                //return redirect("/reverted-list")->with('success', 'Application has been successfully rejected')->with('id', $application_id);
            } catch (\Exception $e) {
                //dd($e);
                DB::rollback();
                return redirect("/reverted-list")->with('error', $errormsg['roolback']);
            }
        } catch (\Exception $e) {
            //dd($e);
            return redirect("/reverted-list")->with('error', $errormsg['roolback']);
        }
    }
    public function importswslist(Request $request)
    {
        $statusCode = 200;
        $result = '';
        $sws_card_no = trim($request->sws_card_no);
        $return_arr=array();
        $return_arr['result']=$result;
        $return_arr['isValid']=1;
        $return_arr['errorMsg']='';
        if(empty($sws_card_no)){
            $return_arr['isValid']=0;
            $return_arr['errorMsg']='Swasthyasathi Card No. Empty.';
            return $return_arr;
        }
        if (!is_numeric($sws_card_no)) {
            $return_arr['isValid']=0;
            $return_arr['errorMsg']='Swasthyasathi Card No. Invalid.';
            return $return_arr;
        }
        try {
            $modelName = new DataSourceCommon;
            $Table = 'lb_scheme.ss_lb_mapping_correction';
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $condition = array();
            $condition["ss_family_id"] = $sws_card_no;
            $query = $modelName->where($condition);
            $data = $query->orderBy('ss_family_id')->orderBy('ss_ben_name')->get([
                'slno','is_exported', 'ss_family_id', 'fathername','ss_ben_name', 'adharcardno'
            ]);
            if(count($data)>0){
                $dataSet='';
                foreach($data as $item){
                    $ss_family_id="'$item->ss_family_id'";
                    $slno="'$item->slno'";
                    if($item->is_exported==1){
                        $status='Already Imported';
                        $actionable=0;
                    }
                    else  if($item->is_exported==0){       
                            $status='NA';
                            $actionable=1;
                        
                    }
                    //dd( $actionable);
                    $dataSet=$dataSet.'<tr>';
                    $dataSet=$dataSet.'<td>'.$item->ss_ben_name.'</td>';
                    $dataSet=$dataSet.'<td>'.$item->fathername.'</td>';
                    $dataSet=$dataSet.'<td>'.$item->ss_family_id.'</td>';
                    $dataSet=$dataSet.'<td>'.$status.'</td>';
                    if($actionable==1){
                        $dataSet=$dataSet.'<td id="impotrId_'.$item->slno.'"><button class="btn btn-info" onclick="return importData('.$ss_family_id.','.$slno.')">Import</button></td>';
                    }
                    else if($actionable==0){
                        $dataSet=$dataSet.'<td id="impotrId_'.$item->slno.'">NA</td>';
                    }
                    $dataSet=$dataSet.'</tr>';

                }
                $return_arr['isValid']=1;
                $return_arr['result']=$dataSet;
                return $return_arr;  
            }
            else{
                $return_arr['isValid']=0;
                $return_arr['errorMsg']='No data Found.';
                return $return_arr;  
            }

        }
        catch (\Exception $e) {
           // dd($e);
            $return_arr['isValid']=0;
            $return_arr['errorMsg']='Server is Busy.. Try Again.';
            return $return_arr;
        }
    }
    public function importswspost(Request $request)
    {
        $user_id = Auth::user()->id;
        $statusCode = 200;
        $result = '';
        $sws_card_no = trim($request->sws_card_no);
        $slno = trim($request->slno);
        $return_arr=array();
        $return_arr['isValid']=1;
        $return_arr['errorMsg']='';
        $return_arr['Msg']='';
        if(empty($sws_card_no)){
            $return_arr['isValid']=0;
            $return_arr['errorMsg']='Swasthyasathi Card No. Empty.';
            return $return_arr;
        }
        if (!is_numeric($sws_card_no)) {
            $return_arr['isValid']=0;
            $return_arr['errorMsg']='Swasthyasathi Card No. Invalid.';
            return $return_arr;
        }
        if(empty($slno)){
            $return_arr['isValid']=0;
            $return_arr['errorMsg']='Swasthyasathi Card Serial No. Empty.';
            return $return_arr;
        }
        if (!is_numeric($slno)) {
            $return_arr['isValid']=0;
            $return_arr['errorMsg']='Swasthyasathi Card Serial No. Invalid.';
            return $return_arr;
        }
        try {
            $modelName = new DataSourceCommon;
            $Table = 'lb_scheme.ss_lb_mapping_correction';
            $modelName->setConnection('pgsql_appwrite');
            $modelName->setTable('' . $Table);
            $condition = array();
            $condition["ss_family_id"] = $sws_card_no;
            $condition["slno"] = $slno;
            $condition["is_exported"] = 0;
            $query = $modelName->where($condition);
            $import_model = new DataSourceCommon;
            $Table = 'lb_scheme.ss_lb_mapping';
            $import_model->setTable('' . $Table);
            
            $data = $query->first();
           // dd( $data->ss_family_id);
            if($data->ss_family_id!=''){
                $count=$import_model->where('ss_family_id', $sws_card_no)->where('ss_ben_name', trim($data->ss_ben_name))->count();
                if($count>0){
                    $return_arr['isValid']=0;
                    $return_arr['errorMsg']='Already Imported';
                    return $return_arr;  
                }
                DB::beginTransaction();
               
                $import_arr['ss_family_id'] = $data->ss_family_id;
                $import_arr['ss_ben_name'] = $data->ss_ben_name;
                $import_arr['status'] = NULL;
                $import_arr['is_imported'] = 1;
                $import_arr['adharcardno'] = $data->adharcardno;
                $import_arr['fathername'] = $data->fathername;
                $import_arr['ss_ben_id'] = $data->ss_ben_id;
                $is_saved = $import_model->insert($import_arr);
                $is_update = $modelName->where('ss_family_id', $sws_card_no)->where('slno', $slno)->where('is_exported', 0)->
                update(['is_exported'=>1,'imported_by'=>$user_id,'imported_at'=>date('Y-m-d H:i:s', time())]);
                if ($is_saved &&  $is_update) {
                    DB::commit();
                    $return_arr['isValid']=1;
                    $return_arr['errorMsg']='';
                    $return_arr['Msg']='Swasthyasathi Data has been successfully Imported';
                     return $return_arr;  
                }
                else{
                    DB::rollback();
                    $return_arr['isValid']=0;
                    $return_arr['errorMsg']='Some error.Please try again';
                    return $return_arr;  
                }
            }
            else{
                $return_arr['isValid']=0;
                $return_arr['errorMsg']='No data Found.';
                return $return_arr;  
            }

        }
        catch (\Exception $e) {
            //dd($e);
            $return_arr['isValid']=0;
            $return_arr['errorMsg']='Server is Busy.. Try Again.';
            return $return_arr;
        }
    }
    function notEligibleDescription($code)
    {
        $description = '';
        $rejection_cause = Config::get('constants.not_eligible_cause');
        foreach ($rejection_cause as $key => $val) {
            if ($key == $code) {
                $description = $val;
                break;
            }
        }
        return $description;
    }
    function sourceTypeDescription($code)
    {
        $description = '';
        $rejection_cause = Config::get('constants.lb_source');
        foreach ($rejection_cause as $key => $val) {
            if ($key == $code) {
                $description = $val;
                break;
            }
        }
        return $description;
    }
    function ajaxgetage(Request $request)
    {
        $diff = 0;
        if ($request->dob != '') {
            $diff = $this->ageCalculate($request->dob);
            // $diff = Carbon::parse($request->dob)->diffInYears($this->base_dob_chk_date);
        }
        return $diff;
    }
    function ageCalculate($dob)
    {
        $diff = 0;
        if ($dob != '') {
            // $diff = $this->ageCalculate($dob);
            $diff = Carbon::parse($dob)->diffInYears($this->base_dob_chk_date);
        }
        return $diff;
    }
}
