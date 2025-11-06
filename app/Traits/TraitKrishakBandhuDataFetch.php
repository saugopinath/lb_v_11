<?php

namespace App\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use Carbon\Carbon;
use App\Scheme;
use App\Helpers\JWTToken;
use App\Helpers\APICurl;
trait TraitKrishakBandhuDataFetch
{

    public function dataPull($aadhar_no = NULL, $voter_id = NULL, $kb_id = NULL)
    {
        try {
            $post_url = 'https://krishakbandhu.wb.gov.in/api/kb_to_joybangla';

            // Build query parameters safely
            $query = [];
            if (!empty($aadhar_no)) {
                $query['aadhaar_no'] = $aadhar_no;
            }
            if (!empty($voter_id)) {
                $query['VOTER'] = $voter_id;
            }
            if (!empty($kb_id)) {
                $query['kb_no'] = $kb_id;
            }

            if (!empty($query)) {
                $post_url .= '?' . http_build_query($query);
            }
            // dd($post_url);
            $curl = curl_init($post_url);
            $headers = [
                'Content-Type: application/json',
            ];
            // $data = [];
            // $data_string = json_encode($data);
// dd($post_url);
            curl_setopt($curl, CURLOPT_URL, $post_url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            // curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            $post_response = curl_exec($curl);
            // dd($post_response);
            // dd($post_response);

            if (curl_errno($curl)) {
                $response_text = curl_error($curl);
                return response()->json([
                    'status' => 500,
                    'message' => $response_text, // show actual error
                ]);
            } else {
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                $post_response = json_decode($post_response, true);
                if (($post_response['message'] ?? null) === 'Data Found.') {
                    return response()->json([
                        'status' => 200,
                        'message' => $post_response['message'] ?? '',
                        'data' => $post_response['data'] ?? $post_response
                    ]);
                } elseif (
                    ($post_response['error'] ?? null) === 'Internal Server Error' &&
                    ($post_response['status'] ?? null) == 500
                ) {
                    return response()->json([
                        'status' => 404,
                        'message' => $post_response['error'] ?? 'Error',
                        'data' => null
                    ]);
                } else {
                    return response()->json([
                        'status' => 500,
                        'message' => $post_response['message'] ?? 'Unknown error',
                        'data' => null
                    ]);
                }


            }
        } catch (\Exception $e) {
            dd($e);
        }

    }


}