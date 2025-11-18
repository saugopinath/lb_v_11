<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BasicAuthController extends Controller
{
    private $username;
    private $password;

    public function __construct()
    {
        set_time_limit(0);
        date_default_timezone_set('Asia/Kolkata');
        $this->username = 'WbjaybanglaDept';
        $this->password = '6voYkShku3qDLny0jORbWmtaPKyjZi94Ksl8lhL1M8N80nGIM3i';
    }
    public function getData(Request $request)
    {
        set_time_limit(0);
        $response = [];
        $statusCode = 200;
        // if (!$request->ajax()) {
        //   $statusCode = 400;
        //   $response = array('error' => 'Error occured in form submit.');
        //   return response()->json($response, $statusCode);
        // }
        try {
            $username = $this->username;
            $password = $this->password;
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $index = $request->index;
            $page_size = $request->page_size;

            /*
            // Set up the cURL request
            $ch = curl_init();

            // Set the URL to fetch data from
            // $url = 'http://172.20.141.212:8084/api/WbDeath';
            $url = 'http://172.25.152.26:8084/api/WbDeath?FromDate=' . $from_date . '&ToDate=' . $to_date . '&PageIndex=' . $index . '&PageSize=' . $page_size . '';
            // $params = [
            //   'FromDate' => '01/01/2022',
            //   'ToDate' => '16/01/2022',
            //   'PageIndex' => 1,
            //   'PageSize'  => 2,

            // ];
            // $url .= '?' . http_build_query($params);

            // echo $url;die;

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            // Execute the cURL request
            $result = curl_exec($ch);

            // Check for errors
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                // Handle the error
            }

            // Close the cURL request
            curl_close($ch);
            */

            $auth_token = base64_encode($username . ':' . $password);
            ///////////////////////////////

            $post_url = 'http://172.25.152.26:8084/api/WbDeath?FromDate=' . $from_date . '&ToDate=' . $to_date . '&PageIndex=' . $index . '&PageSize=' . $page_size . '';
            $curl = curl_init($post_url);
            $headers = array(
                'Authorization: Basic ' . $auth_token,
                'Content-Type: application/json'
            );
            //dd( $headers);
            // $data_string = json_encode($jsonArr);
            //header("Access-Control-Allow-Origin: *");
            curl_setopt($curl, CURLOPT_URL, $post_url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            // curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            // curl_setopt($curl, CURLOPT_TIMEOUT, 30); //timeout in seconds
            $post_response = curl_exec($curl);
            if (curl_errno($curl)) {
                $response_text = curl_error($curl);
            } else {
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);


                // Process the result
                $decoded_data = json_decode($post_response);
                // dd($decoded_data->data);
                $data = $decoded_data->data;
                $totalData = $decoded_data->TotalRec;
                $apiCurrentPageIndex = $decoded_data->CurrentPageIndex;
                $apiTotalRecCurrectPage = $decoded_data->TotalRecCurrectPage;
                // print_r($decoded_data);die;

                $loop_count = 0;
                $record_insert = 0;
                // $insert_arr = array();
                // Output the data
                foreach ($data as $item) {
                    $ins_arr = [
                        'slno' => $item->slno,
                        'applicationid' => $item->ApplicationId,
                        'reportingdate' => $item->ReportingDate,
                        'dateofdeath' => $item->DateOfDeath,
                        'genderdesc' => $item->GenderDesc,
                        'deceased_agetypedesc' => $item->Deceased_AgeTypeDesc,
                        'deceased_age' => $item->Deceased_Age,
                        'deceased_firstname' => $item->Deceased_FirstName,
                        'deceased_middlename' => $item->Deceased_MiddleName,
                        'deceased_lastname' => $item->Deceased_LastName,
                        'deceasedfullname' => $item->DeceasedFullName,
                        'deceased_idprooftyp' => $item->Deceased_IdProofTyp,
                        'deceased_idprooftypname' => $item->Deceased_IdProofTypName,
                        'deceasedkhadyosathicategoryid' => $item->DeceasedKhadyoSathiCategoryID,
                        'deceasedkhadyosathicatdesc' => $item->DeceasedKhadyoSathiCatDesc,
                        'deceased_idproofnumber' => $item->Deceased_IdProofNumber,
                        'present_districtname' => $item->Present_DistrictName,
                        'present_isblockorulbdesc' => $item->Present_IsBlockOrUlbDesc,
                        'present_blockmunicipalitydesc' => $item->Present_BlockMunicipalityDesc,
                        'present_pin' => $item->Present_Pin,
                        'present_grampanchayatdesc' => $item->Present_GramPanchayatDesc,
                        'present_villagetowndesc' => $item->Present_VillageTownDesc,
                        'certificateno' => $item->CertificateNo,
                        'fetching_time' => date('Y-m-d H:i:s'),
                        'running_id' => $index,
                        'from_date' => date('Y-m-d', strtotime(trim(str_replace('/', '-', $from_date)))),
                        'to_date' => date('Y-m-d', strtotime(trim(str_replace('/', '-', $to_date)))),
                        'aadhar_hash' => (trim($item->Deceased_IdProofTypName) == 'Aadhaar') ? md5($item->Deceased_IdProofNumber) : null
                    ];
                    // array_push($insert_arr, $ins_arr);
                    $is_insert = DB::table('jnmp.jnmp_data')->insert($ins_arr);
                    $record_insert = $record_insert+$is_insert;
                    $loop_count++;
                }

                // print_r($insert_arr);die;
                // DB::beginTransaction();

                // DB::commit();
                $msg = '';
                $msg = 'Total <b>'.$record_insert.'</b> out of <b>'.$totalData.'</b> is imported successfully.';
                $response = array('status' => 1, 'msg' => $msg, 'type' => 'green', 'icon' => 'fa fa-check', 'title' => 'Success', 'totalData' => $record_insert);
            }
        } catch (\Exception $e) {
            // DB::rollback();
            $response = array(
                'exception' => true,
                'exception_message' => $e->getMessage(),
                // 'exception_message' => 'Something went wrong. Data is not fetching...',
            );
            $statusCode = 400;
        } finally {
            return response()->json($response, $statusCode);
        }
    }
    public function detailsCallBack(Request $request)
    {
        set_time_limit(0);
        $response = [];
        $statusCode = 200;

        try {
            $limit = $request->limit;
            $data = DB::connection('pgsql_appwrite')->table('jnmp.jnmp_data')->where('is_details_callback', '=', 0)->limit($limit)->get();
            // echo '<pre>'; print_r($data);die;
            if (count($data) > 0) {
                $jsonArr = array();
            $updateId = array();
            foreach ($data as $arr) {
                // print $arr->applicationid;
                array_push($jsonArr, array("ApplicationId" => $arr->applicationid));
                array_push($updateId, array($arr->applicationid));
            }
            // $postJsonData = json_encode($jsonArr);
            // echo $postJsonData;
            // die;
            $username = $this->username;
            $password = $this->password;
            // echo $username, $password; die;

            $auth_token = base64_encode($username . ':' . $password);
            ///////////////////////////////

            $post_url = 'http://172.25.152.26:8084/api/WbDeathDetailsCallBack';
            $curl = curl_init($post_url);
            $headers = array(
                'Authorization: Basic ' . $auth_token,
                'Content-Type: application/json'
            );
            //dd( $headers);
            $data_string = json_encode($jsonArr);
            //header("Access-Control-Allow-Origin: *");
            curl_setopt($curl, CURLOPT_URL, $post_url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            // curl_setopt($curl, CURLOPT_TIMEOUT, 30); //timeout in seconds
            $post_response = curl_exec($curl);
            if (curl_errno($curl)) {
                $response_text = curl_error($curl);
            } else {
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                // echo $post_response;
                // Process the result
                $decoded_data = json_decode($post_response);
                // dd($decoded_data);
                $ResponseDesc = $decoded_data->ResponseDesc;
                $HttpStatusCode = $decoded_data->HttpStatusCode;
                $ResponseType = $decoded_data->ResponseType;
                // print $HttpStatusCode;
                // dd('Done');
                // $HttpStatusCode=200;
                if ($HttpStatusCode == 200) {
                    // print_r(json_decode($postJsonData));die;
                    $updateDetailsCallback = [
                        'details_callback_at' => date("Y-m-d H:i:s"),
                        'is_details_callback' => 1
                    ];
                    // foreach (json_decode($postJsonData) as $arr) {
                    //   // print $arr->ApplicationId.'<br>';
                    //   $dataUpdate = DB::table('jnmp.jnmp_data')->where('applicationid', $arr->ApplicationId)
                    //     ->where('is_details_callback', 0)
                    //     ->update($updateDetailsCallback);
                    // }
                    $dataUpdate = DB::connection('pgsql_appwrite')->table('jnmp.jnmp_data')->where('is_details_callback', '=', 0)->whereIn('applicationid', $updateId)->update($updateDetailsCallback);
                    return response()->json([
                        'ResponseType' => $ResponseType,
                        'status' => $HttpStatusCode,
                        'message' => $ResponseDesc,
                        'status' => 1, 'type' => 'green', 'icon' => 'fa fa-check', 'title' => 'Success',
                    ]);
                }
            }
            } else {
                return response()->json([
                    'status' => 1, 'type' => 'blue', 'icon' => 'fa fa-info', 'title' => 'Info.', 'message' => 'No records found.'
                ]);
            }


        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    //

    public function index()
    {
        $maxDate = DB::table('jnmp.jnmp_data')->max('fetching_time');
        if ($maxDate) {
            if (Carbon::parse($maxDate)->isToday()) {
                $resultDate = DB::table('jnmp.jnmp_data')
                    ->whereDate('fetching_time', '<', Carbon::parse($maxDate)->format('Y-m-d'))
                    ->orderBy('fetching_time', 'desc')
                    ->limit(1)
                    ->value('fetching_time');
            } else {
                $resultDate = $maxDate;
            }
            $lastFetchingDate = Carbon::parse($resultDate)->format('d/m/Y');
        } else {
            $lastFetchingDate = 'N/A';
        }
        return view('JNM.index', compact('lastFetchingDate'));
    }

    public function totalJnmp(Request $request)
    {
        $statusCode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in ajax call.');
            return response()->json($response, $statusCode);
        }
        try {
            $query = "";
            $query1 = "";
            $query2 = "";
            $query = "select count(1) as total_count from jnmp.jnmp_data";
            $query1 = "select count(1) as remaining_count from jnmp.jnmp_data where is_details_callback=0";
            $query2 = "select count(1) as updated_jnmp from jnmp.jnmp_data where is_details_callback=1";
            $query3 = "select count(1) filter(where jnmp_marked=1) as jnmp_mark,
            count(1) filter(where next_level_role_id=-94) as cur_jnmp_mark_as_death,
            count(1) filter(where jnmp_marked=1 and next_level_role_id=0) as re_activate
            from lb_scheme.ben_personal_details";

            $query4 = "select count(1) filter(where jnmp_marked=1) as f_jnmp_mark,
            count(1) filter(where next_level_role_id=-94) as f_cur_jnmp_mark_as_death,
            count(1) filter(where jnmp_marked=1 and next_level_role_id=0) as f_re_activate
            from lb_scheme.faulty_ben_personal_details";

            $getTotalJnmp = DB::select(DB::raw($query));
            $getRemainJnmp = DB::select(DB::raw($query1));
            $getUpdatedJnmp = DB::select(DB::raw($query2));
            $getMainData = DB::select(DB::raw($query3));
            $getFaultyData = DB::select(DB::raw($query4));
            // dump($getTotalJnmp[0]->total_count);
            // dump($getRemainJnmp[0]->remaining_count);
            // dump($getUpdatedJnmp);
            // dd(1);
            $response = array(
                'status' => 1, 'totalJnmp' =>   $getTotalJnmp[0]->total_count, 'remainingJnmp' => $getRemainJnmp[0]->remaining_count, 'updatedJnmp' => $getUpdatedJnmp[0]->updated_jnmp,
                'data1' => (($getMainData[0]->jnmp_mark) + ($getFaultyData[0]->f_jnmp_mark)),
                'data2' => (($getMainData[0]->cur_jnmp_mark_as_death) + ($getFaultyData[0]->f_cur_jnmp_mark_as_death)),
                'data3' => (($getMainData[0]->re_activate) + ($getFaultyData[0]->f_re_activate)),
                'type' => 'green', 'icon' => 'fa fa-check', 'title' => 'Success'
            );
            // dd($response);
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
    public function jnmpDataMarkasDeathInLB(Request $request)
    {
        $statusCode = 200;
        $response = [];
        if (!$request->ajax()) {
            $statusCode = 400;
            $response = array('error' => 'Error occured in ajax call.');
            return response()->json($response, $statusCode);
        }
        try {
            $functionMainServer = DB::connection('pgsql_appwrite')->select("SELECT jnmp.marking_jnmp_data_to_beneficiary_master();");
            $functionPaymentServer = DB::connection('pgsql_payment')->select("SELECT lb_main.marking_jnmp_data_to_payment_master();");
            if ($functionMainServer[0]->functionMainServer > 0 && $functionPaymentServer[0]->functionPaymentServer > 0) {
                $response = array(
                    'status' => 1, 'msg' => 'Marking Done at Lakshmir Bhandar Portal',
                    'type' => 'green', 'icon' => 'fa fa-check', 'title' => 'Success'
                );
            } else {
                $response = array(
                    'status' => 2, 'msg' => 'Something Went Wrong.',
                    'type' => 'red', 'icon' => 'fa fa-check', 'title' => 'Error'
                );
            }
            // dd($response);
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
    public function jnmpMarkedProcess()
    {
        $time_array = DB::select(DB::raw("select to_char(now(),'MONYYYY') as datetime"));
        $var_file_name = $time_array[0]->datetime;
        $log_file_name = 'jnmp_scheduler_log/log_jnmp_marked_' . $var_file_name . '.txt';

        Storage::append($log_file_name, 'Function marking_jnmp_data_to_beneficiary_master() has started on ' . ' Date : ' . date("l jS \of F Y h:i:s A"));
        Storage::append($log_file_name, '=====================================================');

        $applicationFun_call = "";
        $query1 = "SELECT jnmp.marking_jnmp_data_to_beneficiary_master()";
        $applicationFun_call = DB::connection('pgsql_appwrite')->select($query1);
        $applicantCount = $applicationFun_call[0]->marking_jnmp_data_to_beneficiary_master;

        Storage::append($log_file_name, ' JNMP Applications Marking Count : '.$applicantCount.' time:- ' . ' Date : ' . date("l jS \of F Y h:i:s A") );
        Storage::append($log_file_name, '-----------------------------------------------------');
        Storage::append($log_file_name, 'Function marking_jnmp_data_to_beneficiary_master() has ended on ' . ' Date : ' . date("l jS \of F Y h:i:s A") );
    }
}
