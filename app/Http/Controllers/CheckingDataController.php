<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Configduty;
use App\getModelFunc;
use App\LotMaster;
use App\LotDetails;
use App\AvLotmaster;
use App\AvLotdetails;
use App\FailedBankDetails;
use App\UrbanBody;
use App\GP;
use App\BankDetails;
use App\Helpers\Helper;
use App\DataSourceCommon;
use App\District;
use Barryvdh\DomPDF\Facade as PDF;
use App\DocumentType;

class CheckingDataController extends Controller
{
    public function __construct()
    {
        set_time_limit(300);
        $this->middleware('auth');
        date_default_timezone_set('Asia/Kolkata');
    }
    public function index()
    {
        $mv_array = ['mv_phase_12_block_subdiv','mv_phase_12_gp_ward','mv_ds_phase_12_block_subdiv','mv_ds_phase_12_gp_ward','mv_ds_phase_11_block_subdiv', 'mv_ds_phase_11_gp_ward', 'mv_phase_11_block_subdiv', 'mv_phase_11_gp_ward', 'mv_phase_10_block_subdiv', 'mv_phase_10_gp_ward', 'mv_ds_phase_10_block_subdiv', 'mv_ds_phase_10_gp_ward', 'mv_ds_phase_9_block_subdiv', 'mv_ds_phase_9_gp_ward', 'mv_phase_9_block_subdiv', 'mv_phase_9_gp_ward','mv_ds_phase_8_block_subdiv', 'mv_ds_phase_8_gp_ward', 'mv_phase_8_block_subdiv', 'mv_phase_8_gp_ward', 'mv_phase_2_block_subdiv', 'mv_phase_2_gp_ward', 'mv_phase_3_block_subdiv', 'mv_phase_3_gp_ward', 'mv_phase_4_block_subdiv', 'mv_phase_4_gp_ward', 'mv_phase_5_block_subdiv', 'mv_phase_5_gp_ward', 'mv_phase_6_block_subdiv', 'mv_phase_6_gp_ward', 'mv_phase_7_block_subdiv', 'mv_phase_7_gp_ward','mv_all_phase_mis_report'];
        return view('checkingData/mv-refresh', ['mv_arrays' => $mv_array]);
    }
    public function getrefreshMVData(Request $request)
    {
        $statuscode = 400;
        $response = [];
        $statuscode = 200;
        if (!$request->ajax()) {
            $statuscode = 400;
            $response = array('error' => 'Error occured in form submit.');
            return response()->json($response, $statuscode);
        }
        try {
            $attributes = array();
            $required = 'required';
            $rules['mv_name'] = $required;
            $messages['mv_name.required'] = "MV must be select";
            //dd($rules);
            $validator = Validator::make($request->all(), $rules, $messages, $attributes);
            if ($validator->passes()) {
              $valid = 1;
            } else {
              $valid = 0;
              $return_msg = $validator->errors()->all();
              $return_status = 0;

              $response = array(
                'status' => 3, 'msg' => $return_msg,
                'type' => 'red', 'icon' => 'fa fa-warning', 'title' => 'Error'
              );
            }
            $valid=1;
            if ($valid==1) {
                $mv_name = $request->mv_name;
                $mvObj = DB::table('public.m_phase_report_time')->where('mv_name', $mv_name)->orderBy('id', 'DESC')->first();
                //dd( $mvObj);
                if(!is_null($mvObj)){
                    $last_updated_at = $mvObj->report_generation_time;
                }
               else{
                $last_updated_at ='Yet not Updated';
               }

                $html='';
                $html='<table id="example" class="display  table table-bordered" cellspacing="0" width="100%">
                <thead style="font-size: 12px;">
                    <th>MV Name</th>
                    <th>Last Report Generated On</th>
                    <th>Action</th>
                </thead>
                <tbody style="font-size: 14px;">
                    <tr>
                        <td>' . $mv_name . '</td>
                        <td>' . $last_updated_at . '</td>
                        <td><button class="btn btn-success refresh_mv_btn" value="'.$mv_name.'" >Refresh MV</button></td>
                    </tr>
                </tbody>
                </table>';
                // dump($mv_name);
                // dump($html);
                $response = array('mv_name' => $mv_name, 'htmlDiv' => $html);
                // dd($response);
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

    public function postrefreshMVData(Request $request) {
        $statuscode = 400;
        $response = [];
        $statuscode = 200;
        if (!$request->ajax()) {
            $statuscode = 400;
            $response = array('error' => 'Error occured in form submit.');
            return response()->json($response, $statuscode);
        }
        try {
            $mv_name = $request->mv_name;
            $c_time = date('Y-m-d H:i:s');
            DB::connection('pgsql_appwrite')->beginTransaction();
            $is_insert = DB::connection('pgsql_appwrite')->table('public.m_phase_report_time')->insert(['mv_name' => $mv_name, 'report_generation_time' => $c_time]);
            $query = "REFRESH MATERIALIZED VIEW lb_scheme.".$mv_name." WITH DATA";
            $is_mv_referesh = DB::connection('pgsql_appwrite')->select($query);  
            DB::connection('pgsql_appwrite')->commit();
            $response = array(
                'status' => 4, 'msg' => 'Refresh Succesfully.',
                'type' => 'green', 'icon' => 'fa fa-check', 'title' => 'Success'
            );
        } catch (\Exception $e) {
            DB::connection('pgsql_appwrite')->rollback();
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
            );
            $statuscode = 400;
        } finally {
            return response()->json($response, $statuscode);
        }
    }
}
