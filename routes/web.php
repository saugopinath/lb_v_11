<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    FAQController,
    PolicyController,
    AuthenticationController,
    BasicAuthController,
    UserManualController,
    DashboardController,
    CaptchaController,
    LbEntryController,
    LakkhiBhandarWCDformController,
    LegacyProcessController,
    PensionCommonController,
    PensionformReportController,
    BeneficiaryListReportExcel,
    PensionformFaultyReportController,
    casteManagementController,
    TrackApplicantController,
    MasterDataController,
    jnmpController,
    DeactivatedBeneficiaryController,
    DsMisReportController,
    FinancialAssistancePaybleController,
};

Route::get('refresh-captcha', [CaptchaController::class, 'refreshCaptcha'])->name('refresh-captcha');
Route::controller(AuthenticationController::class)->group(function () {
    Route::get('/', 'login')->name('login');
    Route::get('/login', 'login')->name('login');
    Route::post('/loginPost', 'loginCheck')->name('loginPost');
    Route::post('/resendOtp', 'resendOtp')->name('resendOtp');
    Route::get('/otp-validate', 'otpVerification')->middleware(['2fa'])->name('otp-validate');
    Route::post('/otp-validate-post', 'otpValidate')->middleware(['2fa'])->name('otp-validate-post');
    Route::get('/forget-password', 'forgetPassword')->name('forget-password');
    Route::post('/forgetpasswordPost', 'forgetPasswordPost')->name('forgetpasswordPost');
    Route::get('/reset-password', 'resetPassword')->middleware(['2fa'])->name('reset-password');
    Route::post('/resetPasswordPost', 'resetPasswordPost')->middleware(['2fa'])->name('resetPasswordPost');
    Route::post('/logout', 'logout')->name('logout');
    Route::get('/login1', 'login1')->name('login1');
    Route::post('/login1Post', 'login1Post')->name('login1Post');
});
Route::controller(PolicyController::class)->group(function () {
    Route::get('/copyright-policy', 'copyright')->name('copyright-policy');
    Route::get('/privacy-policy', 'privacy')->name('privacy-policy');
    Route::get('/hyperlink-policy', 'hyperlink')->name('hyperlink-policy');
    Route::get('/terms-policy', 'terms_condition')->name('terms-policy');
    // Route::get('/track-application', 'track_application_view')->name('track-application');
});
Route::controller(TrackApplicantController::class)->group(function () {
    Route::get('/track-applicant', 'applicantTrack')->name('track-applicant');
    Route::get('ajaxApplicationTrack', 'ajaxApplicationTrack')->name('ajaxApplicationTrack');
    Route::get('getPaymentDetailsFinYearWiseInTrackApplication', 'getFinYearWisePaymentDetailsInTrackApplication')->name('getPaymentDetailsFinYearWiseInTrackApplication');
});
Route::controller(DashboardController::class)->group(function () {
    Route::get('dashboard', 'index')->middleware(['auth', 'verified'])->name('dashboard')->middleware('auth');
    Route::get('backendlogin', 'index')->middleware(['auth', 'verified'])->name('backendlogin')->middleware('auth');
});
Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard')->middleware('auth');
Route::controller(FAQController::class)->group(function () {
    Route::get('faq', 'index')->name('faq');
});

Route::controller(UserManualController::class)->group(function () {
    Route::any('upload-user-manual', 'upload');
    Route::get('get-user-manual', 'get')->name('get-user-manual');
    Route::get('download_user_manual', 'downloadstaticpdf')->name('download_user_manual');
});

Route::controller(LbEntryController::class)->group(function () {
    Route::post('ajax_check_dup_aadhar', 'checkDupaadhaar');
    Route::get('getAge', 'ajaxgetage');
    Route::post('ajax_aadhar_entry_wtSws', 'aadharEntry');
    Route::get('ajax_personal_entry_wtSws', 'personalEntry');
    Route::post('ajax_contact_entry_wtSws', 'contactEntry');
    Route::post('ajax_bank_entry_wtSws', 'bankEntry');
    Route::post('ajax_encloser_entry_wtSws', 'encloserEntry');
    Route::post('ajax_check_encloser_wtSws', 'checkEncolser');
    Route::post('ajax_declaration_entry_wtSws', 'declarationEntry');
    Route::get('lb-entry-draft-edit', 'draftedit')->name('lb-entry-draft-edit');
    Route::post('verifyDatawtSws', 'forwardData');
});
Route::controller(LakkhiBhandarWCDformController::class)->group(function () {
    Route::any('lb-applicant-list/{list_type}', 'applicantList');
    Route::any('lb-applicant-list-datatable/{list_type}', 'applicantListDatatable');
    Route::get('downaloadEncloser', 'viewimage');
    Route::post('partialReject', 'partialReject')->name('partialReject');
});
Route::controller(LegacyProcessController::class)->group(function () {
    Route::any('legacy/getBankDetails', 'getBankDetails');
});
Route::controller(PensionCommonController::class)->group(function () {
    Route::any('applicant/track/', 'applicantTrack');
});
Route::controller(PensionformReportController::class)->group(function () {
    Route::any('application-list-common', 'applicationStatusList');
});
Route::controller(BeneficiaryListReportExcel::class)->group(function () {
    Route::any('applicationListExcel', 'generate_excel')->name('applicationListExcel');
});
Route::controller(PensionformFaultyReportController::class)->group(function () {
    Route::any('application-list-common-faulty', 'applicationStatusList');
    Route::post('applicationFaultyListExcel', 'generate_excel')->name('applicationFaultyListExcel');
});
Route::controller(casteManagementController::class)->group(function () {
    Route::any('casteManagement', 'index')->name('casteManagement');
    Route::get('changeCaste', 'change')->name('changeCaste');
    Route::post('changeCastePost', 'changePost')->name('changeCastePost');
    Route::any('lb-caste-application-list', 'applicationStatusList')->name('lb-caste-application-list');
    Route::post('applicationListExcelCasteChange', 'generate_excel');
    Route::any('workflowCaste', 'workflow');
    Route::post('getCasteApplieddata', 'getCastedata')->name('getCasteApplieddata');
    Route::post('ajaxModifiedCasteEncolser', 'ajaxModifiedCasteEncolser')->name('ajaxModifiedCasteEncolser');
    Route::post('verifyDataCaste', 'verifydata');
    Route::post('approveDataCaste', 'approvedata');
    Route::get('casteInfoMis', 'casteInfoMis');
    Route::post('casteInfoMisPost', 'getData')->name('casteInfoMisPost');
    Route::any('lb-caste-reverted-list', 'applicationRevertedList');
    Route::get('lb-caste-revert-edit', 'revertedit')->name('lb-caste-revert-edit');
    Route::post('lb-caste-revert-edit-post', 'reverteditPost')->name('lb-caste-revert-edit-post');
    Route::get('caste-matched-report', 'casteMatchedReport')->name('caste-matched-report');
});

Route::controller(MasterDataController::class)->group(function () {
    Route::post('masterDataAjax/getUrban', 'getUrban');
    Route::post('masterDataAjax/getTaluka', 'getTaluka');
    Route::post('masterDataAjax/getGp', 'getGp');
    Route::post('masterDataAjax/getWard', 'getWard');
});

Route::controller(BasicAuthController::class)->group(function () {
    Route::get('jnmp-fetch-callback-api', 'index')->name('jnmp-fetch-callback-api');
    Route::post('jnmpDataMarkasDeathInLB', 'jnmpDataMarkasDeathInLB')->name('jnmpDataMarkasDeathInLB');
    Route::post('totalJnmp', 'totalJnmp')->name('totalJnmp');
    Route::post('jnmpInsertData', 'getData')->name('jnmpInsertData');
    Route::post('jnmpDataCallbackDetails', 'detailsCallBack')->name('jnmpDataCallbackDetails');
    Route::get('jnmpMarkedProcess', 'jnmpMarkedProcess')->name('jnmpMarkedProcess');
});


Route::controller(jnmpController::class)->group(function () {
    // Janma Mrityu Integration
    Route::get('jnmp-data', 'index')->name('jnmp-data');
    Route::post('getJnmpData', 'getJnmpData')->name('getJnmpData');
    Route::post('modalDataView', 'modalDataView')->name('modalDataView');
    Route::post('activeJnmpBeneficiary', 'activeBeneficiary')->name('activeJnmpBeneficiary');
    Route::post('generateExcel', 'generateExcel')->name('generateExcel');

    // JNMP List at HOD
    Route::get('jnmp-marked-data', 'jnmpMarkedDataAtHOD')->name('jnmp-marked-data');
    Route::post('jnmpMarkedData', 'jnmpMarkedData')->name('jnmpMarkedData');
    Route::post('generateJnmpDataHodExcel', 'generateJnmpDataHodExcel')->name('generateJnmpDataHodExcel');
});

// De-activated Beneficiary
Route::controller(DeactivatedBeneficiaryController::class)->group(function () {
    Route::get('de-activate-beneficiary', 'index')->name('de-active-beneficiary');
    Route::post('get-linelisting-deactive', 'getData')->name('get-linelisting-deactive');
    Route::post('getBeneficiaryPersonalData', 'getBeneficiaryPersonalData')->name('getBeneficiaryPersonalData');
    Route::post('updateStopPaymentFinal', 'updateStopPaymentFinal')->name('updateStopPaymentFinal');
    Route::get('de-activated-list', 'deActivatedReport')->name('de-activated-list');
    Route::post('getDeActivatedBenDataList', 'getDeActivatedBenDataList')->name('getDeActivatedBenDataList');
});

Route::controller(DsMisReportController::class)->group(function () {
    Route::get('dsreportphaseselect', 'dsReportphaseCommon')->name('dsreportphaseselect');
    Route::post('dsMisReport', 'index')->name('dsMisReport');
    Route::post('dsMisReportPost', 'getData')->name('dsMisReportPost');
});

// Financial Assistance Payble As On current Date

// Month wise payment report
Route::get('monthwise-payment-report', 'LotReportController@getMonthWisePayReport')->name('monthwise-payment-report');
Route::post('totalMonthwisePaymentReport', 'LotReportController@totalMonthwisePaymentReport')->name('totalMonthwisePaymentReport');

// Financial Assistance Payble As On current Date
Route::get('select-financial-year-payment-assistance', 'FinancialAssistancePaybleController@selectFinancialYearForPaymentAssistance')->name('select-financial-year-payment-assistance');
Route::get('financial-assistance-payable', 'FinancialAssistancePaybleController@lotGeneratedPendingIndex')->name('financial-assistance-payable');
Route::post('lotGeneratedPendingAmountReport', 'FinancialAssistancePaybleController@lotGeneratedPendingAmountReport')->name('lotGeneratedPendingAmountReport');
// Route::get('financial-assistance-payable', 'LotReportController@lotGeneratedPendingIndex')->name('financial-assistance-payable');
// Route::post('lotGeneratedPendingAmountReport', 'LotReportController@lotGeneratedPendingAmountReport')->name('lotGeneratedPendingAmountReport');
Route::post('paymentlotGeneratedPendingAmountReport', 'LotReportController@paymentlotGeneratedPendingAmountReport')->name('paymentlotGeneratedPendingAmountReport');
// Route::get('select-financial-year-payment-assistance', 'LotReportController@selectFinancialYearForPaymentAssistance')->name('select-financial-year-payment-assistance');
Route::get('previous-financial-assistance-payable', 'LotReportController@previousFinancialAssistancePayable')->name('previous-financial-assistance-payable');
Route::post('getPreviousFinancialAssistancePayable', 'LotReportController@getPreviousFinancialAssistancePayable')->name('getPreviousFinancialAssistancePayable');

// Monthly Disbursement Report Based on the Lot Pushed date to the Bank
Route::get('monthly-disbursement', 'LotReportController@monthlyDisbursementIndex')->name('monthly-disbursement');
Route::post('monthly-disbursement-report', 'LotReportController@monthlyDisbursement')->name('monthly-disbursement-report');
