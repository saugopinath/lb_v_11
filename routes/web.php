<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    FAQController,
    PolicyController,
    AuthenticationController,
    BankDetailsEditBandhanBankController,
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
    UpdateBankDetailsController
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
    Route::get('/track-application', 'track_application_view')->name('track-application');
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
    Route::get('downaloadEncloser', 'viewimage');
    Route::post('partialReject', 'partialReject')->name('partialReject');
});
Route::controller(LegacyProcessController::class)->group(function () {
    Route::any('legacy/getBankDetails', 'getBankDetails');
    Route::post('bankIfsc','bankIfsc')->name('bankIfsc');
});
Route::controller(PensionCommonController::class)->group(function () {
    Route::any('applicant/track/', 'applicantTrack');
    Route::get('ajaxApplicationTrack', 'ajaxApplicationTrack');
    Route::get('getPaymentDetailsFinYearWiseInTrackApplication', 'getPaymentDetailsFinYearWiseInTrackApplication')->name('getPaymentDetailsFinYearWiseInTrackApplication');
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



Route::controller(BankDetailsEditBandhanBankController::class)->group(function () {
    Route::get('failed-bank-details-edit', 'index')->name('failed-bank-details-edit');
    Route::post('linelisting-bank-edit', 'getData')->name('linelisting-bank-edit');
    Route::post('editBankDetails', 'editBankDetails')->name('editBankDetails');
    Route::post('updateBankDetails', 'updateBankDetails')->name('updateBankDetails');
    Route::get('rectified-bank-details-edit', 'verified')->name('rectified-bank-details-edit');
    Route::post('completedBankValidationVerified', 'completedBankValidationVerified')->name('completedBankValidationVerified');
    Route::post('completedBankValidationApproved', 'completedBankValidationApproved')->name('completedBankValidationApproved');
    Route::post('ajaxViewPassbook', 'ajaxViewPassbook')->name('ajaxViewPassbook');
    Route::post('getBankFailedexcel', 'getBankFailedexcel')->name('getBankFailedexcel');
});


// Update Bank Details for Approved Beneficiary
// Route::get('bank-details-update', 'UpdateBankDetailsController@index')->name('bank-details-update');
// Route::post('getLineListBankEdit', 'UpdateBankDetailsController@getLineListBankEdit')->name('getLineListBankEdit');
// Route::post('getBenDataForBankUpdate', 'UpdateBankDetailsController@getBenDataForBankUpdate')->name('getBenDataForBankUpdate');
// Route::post('updateApprovedBenBankDetails', 'UpdateBankDetailsController@updateApprovedBenBankDetails')->name('updateApprovedBenBankDetails');
// Route::post('updateApprovedBenMobileNumber', 'UpdateBankDetailsController@updateApprovedBenMobileNumber')->name('updateApprovedBenMobileNumber');
Route::controller(UpdateBankDetailsController::class)->group(function () {
    Route::get('bank-details-update', 'index')->name('bank-details-update');
    Route::post('getLineListBankEdit', 'getLineListBankEdit')->name('getLineListBankEdit');
    Route::post('getBenDataForBankUpdate', 'getBenDataForBankUpdate')->name('getBenDataForBankUpdate');
    Route::post('updateApprovedBenBankDetails', 'updateApprovedBenBankDetails')->name('updateApprovedBenBankDetails');
    Route::post('updateApprovedBenMobileNumber', 'updateApprovedBenMobileNumber')->name('updateApprovedBenMobileNumber');
});


// De-activated Beneficiary
Route::get('de-activate-beneficiary', 'DeactivatedBeneficiaryController@index')->name('de-active-beneficiary');
Route::post('get-linelisting-deactive', 'DeactivatedBeneficiaryController@getData')->name('get-linelisting-deactive');
Route::post('getBeneficiaryPersonalData', 'DeactivatedBeneficiaryController@getBeneficiaryPersonalData')->name('getBeneficiaryPersonalData');
Route::post('updateStopPaymentFinal', 'DeactivatedBeneficiaryController@updateStopPaymentFinal')->name('updateStopPaymentFinal');
Route::get('de-activated-list', 'DeactivatedBeneficiaryController@deActivatedReport')->name('de-activated-list');
Route::post('getDeActivatedBenDataList', 'DeactivatedBeneficiaryController@getDeActivatedBenDataList')->name('getDeActivatedBenDataList');


