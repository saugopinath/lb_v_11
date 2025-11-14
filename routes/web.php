<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ApprovedVerificationPendingController,
    ApproveEditedFailedBenNameController,
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
    BeneficiaryLogController,
    BenNameValidationFailedController,
    PensionformFaultyReportController,
    casteManagementController,
    TrackApplicantController,
    MasterDataController,
    NameValidationController,
    CmoGrivanceWorkflowController1,
    DuplicateController,
    MisReportWithFaultyController,
    StopBeneficiaryController,
    WorkflowController,
    ApprovedEditedBankDetailsController,
    ApproveEditedBankDetailsController
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




Route::controller(BenNameValidationFailedController::class)->group(function () {
    Route::get('edit-name-validation-failed', 'index')->name('edit-name-validation-failed');
    Route::post('getDataNameValidationFailed', 'getDataNameValidationFailed')->name('getDataNameValidationFailed');
    Route::post('editFailedNameDetails', 'editFailedNameDetails')->name('editFailedNameDetails');
    Route::post('updateFailedNameFromVerifier', 'updateFailedNameFromVerifier')->name('updateFailedNameFromVerifier');
    Route::post('failedNameAjaxViewPassbook', 'failedNameAjaxViewPassbook')->name('failedNameAjaxViewPassbook');

    /* Name Validation Failed 90 - 100 */
    Route::get('selectMatchingScore', 'selectMatchingScore')->name('selectMatchingScore');
    Route::get('edit-name-failed-90-to-100', 'editIndex')->name('edit-name-failed-90-to-100');
    Route::post('getDataNameValidationFailed90to100', 'getDataNameValidationFailed90to100')->name('getDataNameValidationFailed90to100');
    Route::post('updateNameValidationFailed90to100', 'updateNameValidationFailed90to100')->name('updateNameValidationFailed90to100');
});

/* ------  Approver End  ------ */
Route::controller(ApproveEditedFailedBenNameController::class)->group(function () {
    Route::get('approve-edited-name-details', 'index')->name('approve-edited-name-details');
    Route::post('getEditedNameFailedDetailsData', 'getEditedNameFailedDetailsData')->name('getEditedNameFailedDetailsData');
    Route::post('getEditFailedNameData', 'getEditFailedNameData')->name('getEditFailedNameData');
    Route::post('updateFailedNameFromApprover', 'updateFailedNameFromApprover')->name('updateFailedNameFromApprover');
    Route::post('nameMismatchRejectOtpVerify', 'nameMismatchRejectOtpVerify')->name('nameMismatchRejectOtpVerify');

    /* Name Validation Failed 90 - 100 */
    Route::get('selectVerifiedMatchingScore', 'selectMatchingScore')->name('selectVerifiedMatchingScore');
    Route::get('approve-edited-name-failed-90-to-100', 'editIndex')->name('approve-edited-name-failed-90-to-100');
    Route::post('getVerifiedNameValidationFailed90to100', 'getVerifiedNameValidationFailed90to100')->name('getVerifiedNameValidationFailed90to100');
    Route::post('getEditFailedNameData90to100', 'getEditFailedNameData90to100')->name('getEditFailedNameData90to100');
    Route::post('updateFailedNameApprove90to100', 'updateFailedNameApprove90to100')->name('updateFailedNameApprove90to100');
    Route::get('mis-report-of-90-to-100', 'indexMisReport')->name('mis-report-of-90-to-100');
    Route::post('getMis90to100', 'getMis90to100')->name('getMis90to100');


});

Route::controller(NameValidationController::class)->group(function () {
    Route::get('misReport-nameValidation', 'misReport');
    Route::get('misReport-nameValidation-Post', 'getData')->name('misReport-nameValidation-Post');
});


// CMO1
Route::controller(CmoGrivanceWorkflowController1::class)->group(function () {
    //cmo verifier
    Route::get('cmo-grievance-workflow1', 'index')->name('cmo-grievance-workflow1');
    Route::post('cmo-grievance-linelisting1', 'listing')->name('cmo-grievance-linelisting1');
    Route::post('cmo-grievance-find1', 'find')->name('cmo-grievance-find1');
    Route::post('cmo-grievance-redress1', 'redress')->name('cmo-grievance-redress1');
    Route::post('cmo-grievance-transfar1', 'transfar')->name('cmo-grievance-transfar1');
    Route::post('cmo-grievance-process-post1', 'processPost')->name('cmo-grievance-process-post1');
    Route::post('cmo-grievance-benLising1', 'benlisting')->name('cmo-grievance-benLising1');
    Route::post('cmo-sent-to-operator1', 'sendOperator')->name('cmo-sent-to-operator1');
    Route::get('cmo-grievance-entry-list1', 'opListCmo')->name('cmo-grievance-entry-list1');
    Route::get('cmo-op_entryList1', 'cmoEntryList')->name('cmo-op_entryList1');
    Route::post('cmo-grievance-applicant-tag1', 'applicanttagdetails')->name('cmo-grievance-applicant-tag1');
    Route::post('cmo_grivance_approve1', 'approve')->name('cmo_grivance_approve1');
    Route::post('cmo_grivance_revert1', 'revert')->name('cmo_grivance_revert1');
    //cmo HOD
    Route::get('cmo-grievance-hod1', 'hodIndex')->name('cmo-grievance-hod1');
    Route::post('cmo-grievance-hod-listing1', 'hodList')->name('cmo-grievance-hod-listing1');
    Route::post('cmo-grievance-hod-view1', 'hodView')->name('cmo-grievance-hod-view1');
    Route::post('cmo-grievance-hod-post1', 'sendBackToCmo')->name('cmo-grievance-hod-post1');
    Route::post('cmo-grievance-hod-revert1', 'hodRevert')->name('cmo-grievance-hod-revert1');
    // CMO MIS Report
    Route::get('cmo-mis-report1', 'cmoMisReport')->name('cmo-mis-report1');
    Route::post('get-mis-report1', 'getMisReport')->name('get-mis-report1');

    Route::any('cmo-mapbosget', 'mapbosget')->name('cmo-mapbosget');
    Route::any('cmo-getblksublist', 'getblksublist')->name('cmo-getblksublist');
    Route::any('cmo-getMunicipalityList', 'getMunicipalityList')->name('cmo-getMunicipalityList');
    Route::any('cmo-mapbospost', 'mapbospost')->name('cmo-mapbospost');
});


Route::controller(WorkflowController::class)->group(function () {
    Route::any('workflow', 'applicationdetails')->name('workflow');
});


Route::controller(LakkhiBhandarWCDformController::class)->group(function () {
    Route::any('application-details-read-only/{id}', 'applicantreadonlyview')->name('application-details-read-only');
    Route::any('application-details-read-only', 'applicantreadonlyview')->name('application-details-read-only');
});


Route::controller(WorkflowController::class)->group(function () {
    Route::post('getBenViewPersonalData', 'getBenViewPersonalData')->name('getBenViewPersonalData');
    Route::post('getBenViewContactData', 'getBenViewContactData')->name('getBenViewContactData');
    Route::post('getBenViewBankData', 'getBenViewBankData')->name('getBenViewBankData');
    Route::post('getBenViewEncloserData', 'getBenViewEncloserData')->name('getBenViewEncloserData');
    Route::post('getBenViewAadharData', 'getBenViewAadharData')->name('getBenViewAadharData');
});

// Beneficiary Log Report
Route::controller(BeneficiaryLogController::class)->group(function () {
    Route::get('beneficiary-log-report', 'index')->name('beneficiary-log-report');
    Route::post('getBeneficiaryLog', 'getBeneficiaryLog')->name('getBeneficiaryLog');
});


// Mis Report with Faulty
Route::controller(MisReportWithFaultyController::class)->group(function () {
    Route::get('misReportWithFaulty', 'index')->name('misReportWithFaulty');
    Route::post('misReportWithFaultyPost', 'getData')->name('misReportWithFaultyPost');
    Route::get('misReportWithNormal', 'NormalEntryIndex')->name('misReportWithNormal');
    Route::post('misReportWithNormalPost', 'NormalEntryGetData')->name('misReportWithNormalPost');
});

// Approved & Verificaiton Pending Beneficiary List
Route::controller(ApprovedVerificationPendingController::class)->group(function () {
    Route::get('approved-verification-pending-list', 'index')->name('approved-verification-pending-list');
    Route::post('getApprovedVerificationPendingList', 'getApprovedVerificationPendingList')->name('getApprovedVerificationPendingList');
    Route::post('generateExcelApprovedVerificationPendingList', 'generateExcelApprovedVerificationPendingList')->name('generateExcelApprovedVerificationPendingList');
});


//Payment Issue
Route::controller(StopBeneficiaryController::class)->group(function () {
    Route::any('stop-list', 'listReport');
    Route::post('stop-list-excel', 'generate_excel');
    Route::any('stop-list-mis', 'mishod');
});



// Approved Bank Edited Details

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

Route::post('legacy/getBankDetails', 'LegacyProcessController@getBankDetails');
Route::post('bankIfsc', 'LegacyProcessController@bankIfsc')->name('bankIfsc');

Route::controller(LegacyProcessController::class)->group(function () {
    Route::post('legacy/getBankDetails', 'getBankDetails');
    Route::post('bankIfsc', 'bankIfsc')->name('bankIfsc');
});

Route::any('lb-dup-aadhar-list-approved-verifier', 'DuplicateController@dup_aadhar_approved_verifier');
Route::get('dedupAadhaarView', 'DuplicateController@dedupAadhaarView');
Route::post('dupAadharReject', 'DuplicateController@dupAadharReject')->name('dupAadharReject');
Route::post('dupAadharModify', 'DuplicateController@dupAadharmodify')->name('dupAadharModify');
Route::any('lb-dup-aadhar-list-approved-approver', 'DuplicateController@dup_aadhar_approved_approver');
Route::post('dupAadhaarApproved', 'DuplicateController@dupAadhaarApproved')->name('dupAadhaarApproved');
Route::get('dupAadhaarMis', 'DuplicateController@misAadhar')->name('dupAadhaarMis');
Route::any('dupAadhaarMisPost', 'DuplicateController@misAadharPost')->name('dupAadhaarMisPost');
Route::get('dup-aadhar-ben-list', 'DuplicateController@dupAadharBenList')->name('dup-aadhar-ben-list');
Route::post('dupAadharGetBenList', 'DuplicateController@dupAadharGetBenList')->name('dupAadharGetBenList');
Route::post('GetdupAadharBenListExcel', 'DuplicateController@GetdupAadharBenListExcel')->name('GetdupAadharBenListExcel');


Route::controller(DuplicateController::class)->group(function () {
    Route::any('lb-dup-aadhar-list-approved-verifier', 'dup_aadhar_approved_verifier');
    Route::get('dedupAadhaarView', 'dedupAadhaarView');
    Route::post('dupAadharReject', 'dupAadharReject')->name('dupAadharReject');
    Route::post('dupAadharModify', 'dupAadharmodify')->name('dupAadharModify');
    Route::any('lb-dup-aadhar-list-approved-approver', 'dup_aadhar_approved_approver');
    Route::post('dupAadhaarApproved', 'dupAadhaarApproved')->name('dupAadhaarApproved');
    Route::get('dupAadhaarMis', 'misAadhar')->name('dupAadhaarMis');
    Route::any('dupAadhaarMisPost', 'misAadharPost')->name('dupAadhaarMisPost');
    Route::get('dup-aadhar-ben-list', 'dupAadharBenList')->name('dup-aadhar-ben-list');
    Route::post('dupAadharGetBenList', 'dupAadharGetBenList')->name('dupAadharGetBenList');
    Route::post('GetdupAadharBenListExcel', 'GetdupAadharBenListExcel')->name('GetdupAadharBenListExcel');
});


Route::controller(ApproveEditedBankDetailsController::class)->group(function () {
    Route::get('approved-edited-bank-details', 'index')->name('approved-edited-bank-details');
    Route::post('getEditedBankData', 'getEditedBankDetailsData')->name('getEditedBankData');
    Route::post('getUpdateEditDetailsBenData', 'getUpdateEditBenData')->name('getUpdateEditDetailsBenData');
    Route::post('approvedEditedBankData', 'approvedEditedBankData')->name('approvedEditedBankData');
});