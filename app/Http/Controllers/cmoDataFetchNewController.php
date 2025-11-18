<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UpdateBenDetails;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Helpers\APICurl;
use App\Helpers\JWTToken;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Configduty;
use App\getModelFunc;
use App\UrbanBody;
use App\GP;
use App\MapLavel;
use Maatwebsite\Excel\Facades\Excel;
use App\DocumentType;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;
use Carbon\Carbon;
use App\District;
use App\SubDistrict;
use App\Taluka;
use App\Ward;
use App\DsPhase;
use App\Scheme;
use App\RejectRevertReason;
use App\AcceptRejectInfo;
use App\Traits\TraitCMOValidate;

class cmoDataFetchNewController extends Controller
{
    use TraitCMOValidate;
    public function __construct()
    {
        set_time_limit(120);
        $this->middleware('auth');
        date_default_timezone_set('Asia/Kolkata');
    }
    public function index()
    {
        $hasUnfetched = DB::connection('pgsql_appwrite')
            ->table('cmo.cmo_response_json')
            ->where('is_fetched', 0)
            // ->exists();
            ->count();
        // dd($hasUnfetched);
        if ($hasUnfetched > 0) {
            $columnName = 'Action';
            $massage = '(Please fetch the previous data,Otherwise import data can not be processed.)*';
        } else {
            $columnName = 'Count';
            $massage = '';
        }
        return view('Cmo_data_fetching/index', compact('columnName', 'massage'));
    }
    public function dataFetch(Request $request)
    {
        $response = [];
        $statusCode = 200;
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = ['error' => 'Error occured in form submit.'];
            return response()->json($response, $statusCode);
        }

        try {
            $hasUnfetched = DB::connection('pgsql_appwrite')
                ->table('cmo.cmo_response_json')
                ->where('is_fetched', 0)
                ->count();

            if ($hasUnfetched > 0) {
                return $response = [
                    'status' => 3,
                    'msg' => 'Previouly import data is not fetch, please fetch this first.',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Warning!!',
                ];
            } else {
                $validator = Validator::make($request->all(), [
                    'from_date' => 'required|date',
                    'to_date' => 'required|date',
                ]);
                if ($validator->fails()) {
                    return $response = [
                        'status' => 3,
                        'msg' => $validator->errors()->first(),
                        'type' => 'red',
                        'icon' => 'fa fa-warning',
                        'title' => 'Warning!!',
                    ];
                    // return response()->json($response, 400);
                } else {
                    ini_set('memory_limit', '-1');
                    $from_date = Carbon::parse($request->from_date)->format('Y-m-d 00:00:00');
                    $to_date = Carbon::parse($request->to_date)->format('Y-m-d 00:00:00');
                    $cmo_data = $this->pullNewCmo($from_date, $to_date);
                    $data = json_decode($cmo_data->getContent(), true);
                    $status = $data['status'];
                    if ($status == 200) {
                        return $response = [
                            'status' => 1,
                            'msg' => 'Data Fetch Successfully',
                            'type' => 'green',
                            'icon' => 'fa fa-check',
                            'title' => 'Success',
                        ];
                    } else if ($status == 400) {
                        return $response = [
                            'status' => 3,
                            'msg' => 'No Record Found',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Warning!!',
                        ];
                    } else if ($status == 500) {
                        return $response = [
                            'status' => 3,
                            'msg' => 'Curl Error Occured',
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Warning!!',
                        ];
                    } else if ($status == 300) {
                        $message = $data['message'];
                        return $response = [
                            'status' => 3,
                            'msg' => $message,
                            'type' => 'red',
                            'icon' => 'fa fa-warning',
                            'title' => 'Warning!!',
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            dd($e);
            $response = [
                'exception' => true,
                'exception_message' => $e->getMessage(),
                // 'exception_message' =>
                //     'Something went wrong. May be session time out logout and login again.',
            ];
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
    public function dataListing(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::connection('pgsql_appwrite')
                ->table('cmo.cmo_response_json')
                ->select('id', 'is_fetched', 'from_date', 'to_date')
                ->orderBy('to_date', 'desc');

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('From Date', function ($row) {
                    return !empty($row->from_date) ? \Carbon\Carbon::parse($row->from_date)->format('d-m-Y') : '';
                })
                ->addColumn('To Date', function ($row) {
                    return !empty($row->to_date) ? \Carbon\Carbon::parse($row->to_date)->format('d-m-Y') : '';
                })
                // ->addColumn('Action', function ($row) {
                //     $val = '';
                //     if ($row->is_fetched == 1) {
                //         $val = DB::connection("pgsql_appwrite")
                //             ->table("cmo.cmo_sm_data")
                //             ->whereRaw("api_fetching_date::date BETWEEN ? AND ?", [Carbon::parse($row->from_date), Carbon::parse($row->to_date)])
                //             ->count();
                //     } elseif ($row->is_fetched == 0) {
                //         $val = '<button value="' . $row->id . '" class="btn btn-success btn-xs name="fetch_btn" id="fetch_btn" type="button">Fetch Data</button>';
                //     }
                //     return $val;
                // })
                ->addColumn('DynamicColumn', function ($row) {
                    $val = '';
                    if ($row->is_fetched == 1) {
                        $val = DB::connection("pgsql_appwrite")
                            ->table("cmo.cmo_sm_data")
                            ->whereRaw("api_fetching_date::date BETWEEN ? AND ?", [Carbon::parse($row->from_date), Carbon::parse($row->to_date)])
                            ->count();
                    } elseif ($row->is_fetched == 0) {
                        $val = '<button value="' . $row->id . '" class="btn btn-success btn-xs name="fetch_btn" id="fetch_btn" type="button">Fetch Data</button>';
                    }
                    return $val;
                })
                ->rawColumns(['DynamicColumn'])
                ->make(true);
        } else {

            return response()->json([
                'status' => 1,
                'message' => 'Invalid Request',
            ]);
        }
    }

    public function dataImport(Request $request)
    {
        $response = [];
        try {
            // dd('dbsjcs');
            $import = "SELECT cmo.insert_data_from_jsonb_array()";
            $fun_call = DB::connection('pgsql_appwrite')->select($import);
            if ($fun_call) {
                return $response = [
                    'status' => 1,
                    'msg' => 'The Imported Data Insert Successfully',
                    'type' => 'green',
                    'icon' => 'fa fa-check',
                    'title' => 'Success',
                ];
            } else {
                return $response = [
                    'status' => 2,
                    'msg' => 'Something went wrong while importing data',
                    'type' => 'red',
                    'icon' => 'fa fa-warning',
                    'title' => 'Warning!!',
                ];
            }
        } catch (\Exception $e) {
            dd($e);
            $response = [
                'exception' => true,
                'exception_message' => $e->getMessage(),

            ];
        } finally {
            return response()->json($response);
        }
    }
}
