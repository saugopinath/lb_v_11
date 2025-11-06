<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configduty;
use App\Models\District;
use App\Models\Scheme;
use Auth;
use Config;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\DataSourceCommon;
use App\Models\getModelFunc;
use App\Models\UrbanBody;
use App\Models\GP;
use App\Models\RejectRevertReason;
use Carbon\Carbon;
use App\Models\DsPhase;

class BeneficiaryListReportExcel extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
        $this->scheme_id = 20;
        $this->source_type = 'ss_nfsa';
        $phaseArr = DsPhase::where('is_current', TRUE)->first();
        $mydate = $phaseArr->base_dob;
        $this->base_dob_chk_date = $mydate;
    }
    public function generate_excel(Request $request)
    {

        ini_set('memory_limit','-1');
        $dob_base_date = Carbon::parse($this->base_dob_chk_date)->format('d/m/Y');
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

//         if($district_code==315)
// {
//     dd($report_type);
// }
        //dd($scheme_name);
        if ($report_type == 'V') {
            if($request->ds_phase=='undefined' || trim($request->ds_phase)=='')
            {
                $ds_phase='';
            }
            else{
                $ds_phase=$request->ds_phase;
            }
            if($request->district_code=='undefined' || trim($request->district_code)=='')
            {
                $created_by_dist_code='';
            }
            else{
                $created_by_dist_code=$request->district_code;
            }
            if($request->rural_urban=='undefined' || trim($request->rural_urban)=='')
            {
                $rural_urban='';
            }
            else{
                $rural_urban=$request->rural_urban;
            }
            if($request->urban_block_code_app=='undefined' || trim($request->urban_block_code_app)=='')
            {
                $created_by_local_body_code='';
            }
            else{
                $created_by_local_body_code=$request->urban_block_code_app;
            }
            if($request->municipality_code=='undefined' || trim($request->municipality_code)=='')
            {
                $municipality_code='';
            }
            else{
                $municipality_code=$request->municipality_code;
            }
            if($request->gp_ward_code_app=='undefined' || trim($request->gp_ward_code_app)=='')
            {
                $gp_ward_code='';
            }
            else{
                $gp_ward_code=$request->gp_ward_code_app;
            }
            $report_type_name = 'Verified Application List';
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);
            $bank_table = $getModelFunc->getTable($district_code, '', 4, 1);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            $condition[$contact_table . ".created_by_dist_code"] = $district_code;
            $condition[$bank_table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$contact_table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
            }
            if (!empty($ds_phase)) {
                $condition[$Table . ".ds_phase"] = $ds_phase;

            }
            if (!empty($created_by_dist_code)) {
                $condition[$Table . ".created_by_dist_code"] = $created_by_dist_code;

            }
            if (!empty($rural_urban)) {
                $condition[$contact_table . ".rural_urban_id"] = $rural_urban;

            }
            if (!empty($created_by_local_body_code)) {
                $condition[$Table . ".created_by_local_body_code"] = $created_by_local_body_code;

            }
            if (!empty($municipality_code)) {
                $condition[$contact_table . ".block_ulb_code"] = $municipality_code;

            }
            if (!empty($gp_ward_code)) {
                $condition[$contact_table . ".gp_ward_code"] = $gp_ward_code;

            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->where('next_level_role_id', '>', 0);
            $query = $query->where('next_level_role_id', '!=', 9999);
           
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
                '' . $Table . '.ds_phase as ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->get();
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') ."-" . time(). ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Block/Municipality</th><th>GP/WARD</th><th>Village/Town/City</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
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
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) . "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->village_town_city) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="13">No Records found</td></tr>';
            }
            echo '</table>';
        } else if ($report_type == 'A') {
            if($request->ds_phase=='undefined' || trim($request->ds_phase)=='')
            {
                $ds_phase='';
            }
            else{
                $ds_phase=$request->ds_phase;
            }
            if($request->district_code=='undefined' || trim($request->district_code)=='')
            {
                $created_by_dist_code='';
            }
            else{
                $created_by_dist_code=$request->district_code;
            }
            if($request->rural_urban=='undefined' || trim($request->rural_urban)=='')
            {
                $rural_urban='';
            }
            else{
                $rural_urban=$request->rural_urban;
            }
            //dd($rural_urban);
            if($request->urban_block_code_app=='undefined' || trim($request->urban_block_code_app)=='')
            {
                $created_by_local_body_code='';
            }
            else{
                $created_by_local_body_code=$request->urban_block_code_app;
            }
            if($request->municipality_code=='undefined' || trim($request->municipality_code)=='')
            {
                $municipality_code='';
            }
            else{
                $municipality_code=$request->municipality_code;
            }
            if($request->gp_ward_code_app=='undefined' || trim($request->gp_ward_code_app)=='')
            {
                $gp_ward_code='';
            }
            else{
                $gp_ward_code=$request->gp_ward_code_app;
            }
            $report_type_name = 'Approved Beneficiary List';
            $condition['next_level_role_id'] = 0;
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 1);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $contact_table = $getModelFunc->getTable($district_code, '', 3);
            $bank_table = $getModelFunc->getTable($district_code, '', 4);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            $condition[$contact_table . ".created_by_dist_code"] = $district_code;
            $condition[$bank_table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$contact_table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
            }
            if (!empty($ds_phase)) {
                $condition[$Table . ".ds_phase"] = $ds_phase;

            }
            if (!empty($created_by_dist_code)) {
                $condition[$Table . ".created_by_dist_code"] = $created_by_dist_code;

            }
            if (!empty($rural_urban)) {
                $condition[$contact_table . ".rural_urban_id"] = $rural_urban;

            }
            if (!empty($created_by_local_body_code)) {
                $condition[$Table . ".created_by_local_body_code"] = $created_by_local_body_code;

            }
            if (!empty($municipality_code)) {
                $condition[$contact_table . ".block_ulb_code"] = $municipality_code;

            }
            if (!empty($gp_ward_code)) {
                $condition[$contact_table . ".gp_ward_code"] = $gp_ward_code;

            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->where('next_level_role_id', 0);
            $query = $query->where('next_level_role_id', '!=', 9999);
            //dd($query->toSql());


            $data = $query->select(
                '' . $Table . '.application_id as application_id',
                '' . $Table . '.beneficiary_id as beneficiary_id',
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
                '' . $Table . '.ds_phase as ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->get();

            
            //dd($data);
            /*$excel_data[] = array(
                    'Application ID', 'Beneficiary ID', 'Applicant Name', 'Applicant Mobile No.', 'Father\'s Name', 'Age', 'Caste',
                    'Swasthyasathi Card No.', 'Block/Municipality', 'GP/WARD', 'Bank IFSC', 'Bank Account No.'
                );*/
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') ."-" . time(). ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Beneficiary ID</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Block/Municipality</th><th>GP/WARD</th><th>Village/Town/City</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
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
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . $row->beneficiary_id . "</td><td>" . trim($row->ben_fname) .  "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->village_town_city) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="14">No Records found</td></tr>';
            }
            echo '</table>';
        } else if ($report_type == 'R') {
            if($request->ds_phase=='undefined' || trim($request->ds_phase)=='')
            {
                $ds_phase='';
            }
            else{
                $ds_phase=$request->ds_phase;
            }
            if($request->district_code=='undefined' || trim($request->district_code)=='')
            {
                $created_by_dist_code='';
            }
            else{
                $created_by_dist_code=$request->district_code;
            }
            if($request->rural_urban=='undefined' || trim($request->rural_urban)=='')
            {
                $rural_urban='';
            }
            else{
                $rural_urban=$request->rural_urban;
            }
            if($request->urban_block_code_app=='undefined' || trim($request->urban_block_code_app)=='')
            {
                $created_by_local_body_code='';
            }
            else{
                $created_by_local_body_code=$request->urban_block_code_app;
            }
            if($request->municipality_code=='undefined' || trim($request->municipality_code)=='')
            {
                $municipality_code='';
            }
            else{
                $municipality_code=$request->municipality_code;
            }
            if($request->gp_ward_code_app=='undefined' || trim($request->gp_ward_code_app)=='')
            {
                $gp_ward_code='';
            }
            else{
                $gp_ward_code=$request->gp_ward_code_app;
            }
            $report_type_name = 'Rejected Application List';
            //$condition['next_level_role_id'] = '-100';
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 10);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
            }
            if (!empty($ds_phase)) {
                $condition[$Table . ".ds_phase"] = $ds_phase;

            }
            if (!empty($created_by_dist_code)) {
                $condition[$Table . ".created_by_dist_code"] = $created_by_dist_code;

            }
            if (!empty($rural_urban)) {
                $condition[$contact_table . ".rural_urban_id"] = $rural_urban;

            }
            if (!empty($created_by_local_body_code)) {
                $condition[$Table . ".created_by_local_body_code"] = $created_by_local_body_code;

            }
            if (!empty($municipality_code)) {
                $condition[$contact_table . ".block_ulb_code"] = $municipality_code;

            }
            if (!empty($gp_ward_code)) {
                $condition[$contact_table . ".gp_ward_code"] = $gp_ward_code;

            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin('public.users as u', 'u.id', '=', $Table . '.created_by');
            $query = $query->leftjoin('public.m_reject_revert_reason_master as rm', 'rm.id', '=', $Table . '.rejected_cause');
            // $query = $query->where('next_level_role_id', '<', 0);
            // $query = $query->where('next_level_role_id', '!=', -50);
            $query = $query->where('is_faulty', false);
            
            $data = $query->select(
                'application_id',
                'ben_fname',
                'ben_mname',
                'ben_lname',
                'father_fname',
                'father_mname',
                'father_lname',
                'mother_fname',
                'mother_mname',
                'mother_lname',
                '' . $Table . '.mobile_no as mobile_no',
                'u.mobile_no as enter_by_mobile_no',
                'rm.reason as reason',
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
                '' . $Table . '.ds_phase as ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($Table . '.gp_ward_name')->get();
            //dd($data);
            /* $excel_data[] = array(
                    'Application ID', 'Applicant Name', 'Applicant Mobile No.', 'Father\'s Name', 'Age', 'Caste',
                    'Swasthyasathi Card No.', 'Operator Mobile NO.', 'Rejected Reason', 'Block/Municipality', 'GP/WARD', 'Bank IFSC', 'Bank Account No.'
                );*/
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') ."-" . time(). ".xls";            
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Operator Mobile No.</th><th>Rejected Reason</th> <th>Block/Municipality</th><th>GP/WARD</th><th>Village/Town/City</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
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
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    $rejected_by = 'NA';
                   
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) . "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . $row->enter_by_mobile_no . "</td><td>" . $row->reason . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->village_town_city) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="15">No Records found</td></tr>';
            }
            echo '</table>';
        } else if ($report_type == 'T') {
            if($request->ds_phase=='undefined' || trim($request->ds_phase)=='')
            {
                $ds_phase='';
            }
            else{
                $ds_phase=$request->ds_phase;
            }
            if($request->district_code=='undefined' || trim($request->district_code)=='')
            {
                $created_by_dist_code='';
            }
            else{
                $created_by_dist_code=$request->district_code;
            }
            if($request->rural_urban=='undefined' || trim($request->rural_urban)=='')
            {
                $rural_urban='';
            }
            else{
                $rural_urban=$request->rural_urban;
            }
            if($request->urban_block_code_app=='undefined' || trim($request->urban_block_code_app)=='')
            {
                $created_by_local_body_code='';
            }
            else{
                $created_by_local_body_code=$request->urban_block_code_app;
            }
            if($request->municipality_code=='undefined' || trim($request->municipality_code)=='')
            {
                $municipality_code='';
            }
            else{
                $municipality_code=$request->municipality_code;
            }
            if($request->gp_ward_code_app=='undefined' || trim($request->gp_ward_code_app)=='')
            {
                $gp_ward_code='';
            }
            else{
                $gp_ward_code=$request->gp_ward_code_app;
            }
            $report_type_name = 'Reverted Application List';
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);
            $bank_table = $getModelFunc->getTable($district_code, '', 4, 1);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            $condition[$contact_table . ".created_by_dist_code"] = $district_code;
            $condition[$bank_table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$contact_table . ".created_by_local_body_code"] = $urban_body_code;
                $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
            }
            if (!empty($ds_phase)) {
                $condition[$Table . ".ds_phase"] = $ds_phase;

            }
            if (!empty($created_by_dist_code)) {
                $condition[$Table . ".created_by_dist_code"] = $created_by_dist_code;

            }
            if (!empty($rural_urban)) {
                $condition[$contact_table . ".rural_urban_id"] = $rural_urban;

            }
            if (!empty($created_by_local_body_code)) {
                $condition[$Table . ".created_by_local_body_code"] = $created_by_local_body_code;

            }
            if (!empty($municipality_code)) {
                $condition[$contact_table . ".block_ulb_code"] = $municipality_code;

            }
            if (!empty($gp_ward_code)) {
                $condition[$contact_table . ".gp_ward_code"] = $gp_ward_code;

            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin('public.users as u', 'u.id', '=', $Table . '.created_by');
            $query = $query->leftjoin('public.m_reject_revert_reason_master as rm', 'rm.id', '=', $Table . '.rejected_cause');
            $query = $query->where('next_level_role_id', -50);
            
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
                '' . $Table . '.mobile_no as mobile_no',
                'u.mobile_no as enter_by_mobile_no',
                'rm.reason as reason',
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
                '' . $Table . '.ds_phase as ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->get();
            //dd($data->toArray());

            $excel_data[] = array(
                'Application ID', 'Applicant Name', 'Applicant Mobile No.', 'Fathers Name', 'Age', 'Caste',
                'Swasthyasathi Card No.', 'Operator Mobile NO.', 'Reverted Reason', 'Block/Municipality', 'GP/WARD', 'Bank IFSC', 'Bank Account No.'
            );
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') ."-" . time(). ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Operator Mobile NO.</th><th>Reverted Reason</th><th>Block/Municipality</th><th>GP/WARD</th><th>Village/Town/City</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
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
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) . "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . $row->enter_by_mobile_no . "</td><td>" . $row->reason . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->village_town_city) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="15">No Records found</td></tr>';
            }
            echo '</table>';
        } else if ($report_type == 'F') {
            if($request->ds_phase=='undefined' || trim($request->ds_phase)=='')
            {
                $ds_phase='';
            }
            else{
                $ds_phase=$request->ds_phase;
            }
            if($request->district_code=='undefined' || trim($request->district_code)=='')
            {
                $created_by_dist_code='';
            }
            else{
                $created_by_dist_code=$request->district_code;
            }
            if($request->rural_urban=='undefined' || trim($request->rural_urban)=='')
            {
                $rural_urban='';
            }
            else{
                $rural_urban=$request->rural_urban;
            }
            if($request->urban_block_code_app=='undefined' || trim($request->urban_block_code_app)=='')
            {
                $created_by_local_body_code='';
            }
            else{
                $created_by_local_body_code=$request->urban_block_code_app;
            }
            if($request->municipality_code=='undefined' || trim($request->municipality_code)=='')
            {
                $municipality_code='';
            }
            else{
                $municipality_code=$request->municipality_code;
            }
            if($request->gp_ward_code_app=='undefined' || trim($request->gp_ward_code_app)=='')
            {
                $gp_ward_code='';
            }
            else{
                $gp_ward_code=$request->gp_ward_code_app;
            }
            $report_type_name = 'Faulty Application List';
            $Table = $getModelFunc->getTableFaulty($district_code, $this->source_type, 1, 1);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $contact_table = $getModelFunc->getTableFaulty($district_code, '', 3, 1);
            $bank_table = $getModelFunc->getTableFaulty($district_code, '', 4, 1);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            //$condition[$contact_table . ".created_by_dist_code"] = $district_code;
            //$condition[$bank_table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                 $condition[$contact_table . ".created_by_local_body_code"] = $urban_body_code;
                 $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
            }
            if (!empty($ds_phase)) {
                $condition[$Table . ".ds_phase"] = $ds_phase;

            }
            if (!empty($created_by_dist_code)) {
                $condition[$Table . ".created_by_dist_code"] = $created_by_dist_code;

            }
            if (!empty($rural_urban)) {
                $condition[$contact_table . ".rural_urban_id"] = $rural_urban;

            }
            if (!empty($created_by_local_body_code)) {
                $condition[$Table . ".created_by_local_body_code"] = $created_by_local_body_code;

            }
            if (!empty($municipality_code)) {
                $condition[$contact_table . ".block_ulb_code"] = $municipality_code;

            }
            if (!empty($gp_ward_code)) {
                $condition[$contact_table . ".gp_ward_code"] = $gp_ward_code;

            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin('public.users as u', 'u.id', '=', $Table . '.created_by');
            $query = $query->leftjoin('public.m_reject_revert_reason_master as rm', 'rm.id', '=', $Table . '.rejected_cause');
            $query = $query->whereNull('is_migrated');
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
                '' . $Table . '.mobile_no as mobile_no',
                'u.mobile_no as enter_by_mobile_no',
                'rm.reason as reason',
                'dob',
                'age_ason_01012021',
                'caste',
                'ss_card_no',
                'next_level_role_id',
                'duare_sarkar_registration_no',
                'block_ulb_name',
                'gp_ward_name',
                'village_town_city',
                'house_premise_no',
                'bank_ifsc',
                'bank_code',
                '' . $Table . '.ds_phase as ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->get();
            //dd($data);
            $excel_data[] = array(
                'Application ID', 'Applicant Name', 'Applicant Mobile No.', 'Father\'s Name', 'Age', 'Caste',
                'Swasthyasathi Card No.', 'Operator Mobile NO.', 'Block/Municipality', 'GP/WARD', 'Village/Town/City',
                'Bank IFSC', 'Bank Account No.'
            );
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') ."-" . time(). ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Operator Mobile NO.</th><th>Block/Municipality</th><th>GP/WARD</th><th>Village/Town/City</th><th>House/Premise No.</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
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
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) . "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . $row->enter_by_mobile_no . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->village_town_city) . "</td><td>" . trim($row->house_premise_no) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="15">No Records found</td></tr>';
            }
            echo '</table>';
        } else if ($report_type == 'PEL') {
            if($request->ds_phase=='undefined' || trim($request->ds_phase)=='')
            {
                $ds_phase='';
            }
            else{
                $ds_phase=$request->ds_phase;
            }
            if($request->district_code=='undefined' || trim($request->district_code)=='')
            {
                $created_by_dist_code='';
            }
            else{
                $created_by_dist_code=$request->district_code;
            }
            if($request->rural_urban=='undefined' || trim($request->rural_urban)=='')
            {
                $rural_urban='';
            }
            else{
                $rural_urban=$request->rural_urban;
            }
            if($request->urban_block_code_app=='undefined' || trim($request->urban_block_code_app)=='')
            {
                $created_by_local_body_code='';
            }
            else{
                $created_by_local_body_code=$request->urban_block_code_app;
            }
            if($request->municipality_code=='undefined' || trim($request->municipality_code)=='')
            {
                $municipality_code='';
            }
            else{
                $municipality_code=$request->municipality_code;
            }
            if($request->gp_ward_code_app=='undefined' || trim($request->gp_ward_code_app)=='')
            {
                $gp_ward_code='';
            }
            else{
                $gp_ward_code=$request->gp_ward_code_app;
            }
            $report_type_name = 'Partially Filled Up Application List';
            $Table = $getModelFunc->getTable($district_code, $this->source_type, 1, 1);
            $modelName->setConnection('pgsql_appread');
            $modelName->setTable('' . $Table);
            $contact_table = $getModelFunc->getTable($district_code, '', 3, 1);
            $bank_table = $getModelFunc->getTable($district_code, '', 4, 1);
            $condition[$Table . ".created_by_dist_code"] = $district_code;
            //$condition[$contact_table . ".created_by_dist_code"] = $district_code;
            //$condition[$bank_table . ".created_by_dist_code"] = $district_code;
            if ($role_name == 'Operator' || $role_name == 'Verifier') {
                $condition[$Table . ".created_by_local_body_code"] = $urban_body_code;
                 $condition[$contact_table . ".created_by_local_body_code"] = $urban_body_code;
                 $condition[$bank_table . ".created_by_local_body_code"] = $urban_body_code;
            }
            if (!empty($ds_phase)) {
                $condition[$Table . ".ds_phase"] = $ds_phase;

            }
            if (!empty($created_by_dist_code)) {
                $condition[$Table . ".created_by_dist_code"] = $created_by_dist_code;

            }
            if (!empty($rural_urban)) {
                $condition[$contact_table . ".rural_urban_id"] = $rural_urban;

            }
            if (!empty($created_by_local_body_code)) {
                $condition[$Table . ".created_by_local_body_code"] = $created_by_local_body_code;

            }
            if (!empty($municipality_code)) {
                $condition[$contact_table . ".block_ulb_code"] = $municipality_code;

            }
            if (!empty($gp_ward_code)) {
                $condition[$contact_table . ".gp_ward_code"] = $gp_ward_code;

            }
            $query = $modelName->where($condition);
            $query = $query->leftjoin($contact_table, $contact_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->leftjoin($bank_table, $bank_table . '.application_id', '=', $Table . '.application_id');
            $query = $query->where('is_final', false);
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
                '' . $Table . '.ds_phase as ds_phase'
            )->orderBy($Table . '.ben_fname')->orderBy($contact_table . '.gp_ward_name')->get();
            //dd($data);
            $excel_data[] = array(
                'Application ID', 'Applicant Name', 'Applicant Mobile No.', 'Father\'s Name', 'Age', 'Caste',
                'Swasthyasathi Card No.', 'Block/Municipality', 'GP/WARD', 'Village/Town/City', 'Bank IFSC', 'Bank Account No.'
            );
            $filename = $scheme_name . "-" . $report_type_name . "-" . date('d/m/Y') ."-" . time(). ".xls";
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");
            echo '<table border="1">';
            echo '<tr><th>Applicant Id</th><th>Applicant Name</th><th>Applicant Mobile No.</th><th>Father\'s Name</th><th>Age</th><th>Caste</th><th>Swasthyasathi Card No.</th><th>Operator Mobile NO.</th><th>Block/Municipality</th><th>GP/WARD</th><th>Village/Town/City</th><th>Bank IFSC</th><th>Bank Account No.</th><th>Duare Sarkar Phase</th></tr>';
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
                    if (!empty($row->dob)) {
                        $row->age_ason_01012021 = $this->ageCalculate($row->dob);
                    } else {
                        $row->age_ason_01012021 = $row->age_ason_01012021;
                    }
                    $phase_des = $this->getPhaseDes($row->ds_phase);
                    echo "<tr><td>" . $row->application_id . "</td><td>" . trim($row->ben_fname) . "</td><td>" . $row->mobile_no . "</td><td>" . trim($row->father_fname) . " " . trim($row->father_mname) . " " . trim($row->father_lname) . "</td><td>" . $row->age_ason_01012021 . "</td><td>" . $row->caste . "</td><td>" . $ss_card_no . "</td><td>" . $row->enter_by_mobile_no . "</td><td>" . trim($row->block_ulb_name) . "</td><td>" . trim($row->gp_ward_name) . "</td><td>" . trim($row->village_town_city) . "</td><td>" . trim($row->bank_ifsc) . "</td><td>" . $f_bank_code . "</td><td>" . $phase_des . "</td></tr>";
                }
            } else {
                echo '<tr><td colspan="14">No Records found</td></tr>';
            }
            echo '</table>';
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
