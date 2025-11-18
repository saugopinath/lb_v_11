<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\District;
use App\getModelFunc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Helpers\Helper;
use Maatwebsite\Excel\Facades\Excel;

class FinancialAssistancePaybleController extends Controller
{
    public function selectFinancialYearForPaymentAssistance()
    {
        $designation = Auth::user()->designation_id;
        if ($designation == 'DDO' || $designation == 'HOD' || $designation == 'MisState') {
            $result = DB::connection('pgsql_payment')->select("select financial_year,case when is_current_year=1 then 'financial-assistance-payable' else 'previous-financial-assistance-payable' end as url from master_mgmt.m_financial_master where is_lot_generation_active=1");
            return view('lot_report/index_financial_assistance_payble')->with('fin_year',$result);
        }
        else {
            return redirect("/")->with('error', 'User Unauthorized.');
        }
    }

    public function lotGeneratedPendingIndex(Request $request) {
        $phase_master = DB::connection('pgsql_payment')->table('master_mgmt.phase_month_master')
        ->where('pl_status', 1)
        ->get();
        return view('lot_report/financial_assistance_payble')->with('ds_phase', $phase_master);
    }

    public function lotGeneratedPendingAmountReport(Request $request) 
    {
        if ($request->ajax()) {
            // $request_phase = $request->ds_phase;
            // Payment Lot Generation Pending
            $monthConstArr = array(1=>'jan', 2=>'feb', 3=>'mar', 4=>'apr', 5=>'may',6=>'jun', 7=>'jul', 8=>'aug', 9=>'sep', 10=>'oct', 11=>'nov', 12=>'dec');
            $monthArr = array(0=>4, 1=>5, 2=>6, 3=>7, 4=>8, 5=>9, 6=>10, 7=>11, 8=>12, 9=>1, 10=>2, 11=>3);
            // dd($monthArr[0]);
            // if (!is_null($request_phase)) {
            //     $allPhase = DB::connection('pgsql_payment')->table('master_mgmt.phase_month_master')->where('ds_phase', $request_phase)->where('pl_status', 1)->get();
            // } else {
            //     $allPhase = DB::connection('pgsql_payment')->table('master_mgmt.phase_month_master')->where('pl_status', 1)->get();
            // }
            $query = '';
            $valQuery ="";
            $totalPayMonthArr=array();
            // foreach ($allPhase as $key) {
                // $phase = $key->ds_phase;
                // $phase_condition = "";
                // if ($phase == 0) {
                //     $phase_condition = " and ds_phase is null ";
                // } else {
                //     $phase_condition = " and ds_phase = " . $phase . " ";
                // }
                
                // Get Current Financial Year
                if (date('m') <= 3) {
                    $current_financial_year = (date('Y')-1) . '-' . date('Y');
                } else {
                    $current_financial_year = date('Y') . '-' . (date('Y') + 1);
                }

                // $phaseTableFinYear = $key->starting_year;
			    // $phaseTableMonth = $key->starting_month;
                // $phaseTableMonthIndex = array_search($phaseTableMonth, $monthArr);
			    // $currentMonthIndex = array_search(date('n'), $monthArr);
                // $total_pay_month = 0;
                // if ($phaseTableFinYear == $current_financial_year) {
				// 	if ($phaseTableMonthIndex <= $currentMonthIndex) {
				// 		$total_pay_month = ($currentMonthIndex-$phaseTableMonthIndex)+1;
				// 	}
				// }
				// else {
				// 	$total_pay_month = $currentMonthIndex+1;
				// }
                // $totalPayMonthArr[] = ['ds_phase' => $phase, 'pay_month' => $total_pay_month, 'ds_phase_name_val' => $key->ds_phase_name];

                $query .= "select caste,
                SUM(apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status) as total_beneficiary_payment, 
                apr_status, may_status, jun_status, jul_status, aug_status, sep_status, oct_status, nov_status, dec_status, jan_status, feb_status, mar_status,
                (
                    case
                        when caste in('SC', 'ST') then (
                            apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status
                        ) * 1200
                        else (
                            apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status
                        ) * 1000
                    end
                ) as total_amount 
                from
                (
                    select caste, ";
                foreach ($monthArr as $key => $val) {
                    $statusColumn = $monthConstArr[$val];
                    $yearArr = explode('-', $current_financial_year);
                    if ($val >= 4 and  $val <= 12) {
                        $final_yymm = substr($yearArr[0], 2) . str_pad($val, 2, "0", STR_PAD_LEFT);
                    } else {
                        $final_yymm = substr($yearArr[1], 2) . str_pad($val, 2, "0", STR_PAD_LEFT);
                    }
                    $query .= " sum(case 
                        when  " . $statusColumn . "_lot_status in('R','E' )  and ben_status= any(array[1, -102]) and end_yymm >= " . $final_yymm . " and 
                        start_yymm <= ". $final_yymm ." AND acc_validated IN('2', '6')";
                    $query .= "  then 1 else 0 end 
                    ) as " . $statusColumn . "_status,";
                }
                $query = substr($query, 0, -1);
                $query .= " from
                payment.ben_payment_details
                        where
                acc_validated in('2', '6')
                and ben_status = any(array[1, -102])
                group by
                    caste
                    ) t
                group by
                    caste, apr_status, may_status, jun_status, jul_status, aug_status, sep_status, oct_status, nov_status, dec_status, jan_status, feb_status, mar_status ";
                $query .= " UNION ALL ";
                // dd($query);
                // Validation Pending Beneficiary
                $valQuery .= "select 
                caste,
                SUM(apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status) as validation_pending, 
                apr_status, may_status, jun_status, jul_status, aug_status, sep_status, oct_status, nov_status, dec_status, jan_status, feb_status, mar_status,
                (
                    case
                        when caste in('SC', 'ST') then (
                            apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status
                        ) * 1000
                        else (
                            apr_status + may_status + jun_status + jul_status + aug_status + sep_status + oct_status + nov_status + dec_status + jan_status + feb_status + mar_status
                        ) * 500
                    end
                ) as validation_pending_amount 
                from
                (
                    select caste, ";
                foreach ($monthArr as $key => $val) {
                    $statusColumn = $monthConstArr[$val];
                    $yearArr = explode('-', $current_financial_year);
                    if ($val >= 4 and  $val <= 12) {
                        $final_yymm = substr($yearArr[0], 2) . str_pad($val, 2, "0", STR_PAD_LEFT);
                    } else {
                        $final_yymm = substr($yearArr[1], 2) . str_pad($val, 2, "0", STR_PAD_LEFT);
                    }
                    $valQuery .= " sum(case 
                        when  " . $statusColumn . "_lot_status in('R','E','F' )  and ben_status = any(array[1, -102]) and end_yymm>=" . $final_yymm . " 
                        and start_yymm <= " . $final_yymm . " AND acc_validated IN('0', '1', '3', '4', '7')";                         
                    $valQuery .= " then 1 else 0 end 
                    ) as " . $statusColumn . "_status,";
                }
                $valQuery = substr($valQuery, 0, -1);
                $valQuery .= " from
                payment.ben_payment_details
                where
                    acc_validated in('0', '1', '3', '4', '7')
                    and ben_status = any(array[1, -102])
                group by
                    caste
                    ) t
                group by
                    caste, apr_status, may_status, jun_status, jul_status, aug_status, sep_status, oct_status, nov_status, dec_status, jan_status, feb_status, mar_status ";
                $valQuery .= " UNION ALL ";
                
                
            // }
            $query = substr($query, 0, -11);
            $valQuery = substr($valQuery, 0, -11);

            // dump($query);
            // dump($valQuery);die;
            // print_r($totalPayMonthArr); die;
            $data = DB::connection('pgsql_payment')->select($query);

            // print $valQuery; die;
            $valData = DB::connection('pgsql_payment')->select($valQuery);

            // Approval Pending
            $approvalQuery = '';
            $approvalQuery = "select LEFT(caste::varchar,2)::character varying(2) as caste, sum(partial_data+full_data+verification_pending+verified+reverted) as total, 
            case when caste='OTHERS' then 1000 when caste='SC' then 1200 when caste='ST' then 1200 else 0 end as phase_amount 
            from (  
                select caste,
                count(1) filter(where is_final=FALSE) as partial_data,
                count(1) filter(where is_final=TRUE) as full_data,
                count(1) filter(where is_final=TRUE and next_level_role_id IS NULL) as verification_pending,
                count(1) filter(where is_final=TRUE and next_level_role_id >0 and next_level_role_id!=9999) 
                as verified,
                count(1) filter(where is_final=TRUE and next_level_role_id=-50) as reverted
                from lb_scheme.draft_ben_personal_details ";
            $approvalQuery .= "group by caste 
            ) t group by caste";

            $approvalPendingData = DB::connection('pgsql_appread')->select($approvalQuery);

            // print_r($approvalPendingData);

            // Get Approval Pending Data
            $casteArr = array('OT','SC','ST');
            $finalapprovalPendingData = array();
            // foreach ($allPhase as $key) {
            //     $app_phase = $key->ds_phase;
                foreach ($casteArr as $caste) {
                    $is_matched = 0;
                    foreach ($approvalPendingData as $ap) {
                        if ($caste == $ap->caste /*&& $app_phase == $ap->ds_phase*/) {
                            $finalapprovalPendingData[] = ['caste' => $caste, 'total' => $ap->total];
                            $is_matched = 1;
                        }
                    }
                    if ($is_matched == 0) {
                        $finalapprovalPendingData[] = ['caste' => $caste, 'total' => 0];
                    }
                }
            // }
            // print_r($finalapprovalPendingData);die;
            $wef_yymm = '240'.$monthArr[0];
            $amount_master = DB::connection('pgsql_payment')->table('master_mgmt.amount_master')->where('wef_yymm', '>=', $wef_yymm)->get(['caste','amount']);
            return response()->json(
                [
                    'row_data' => $data, 
                    'val_data' => $valData,
                    // 'payble_month' => $totalPayMonthArr,
                    'approval_pending' => $finalapprovalPendingData,
                    'amount_master_data' => $amount_master
                ]
            );
        }
    }
}
