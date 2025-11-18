<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    FAQController,
    PolicyController,
    AuthenticationController,
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
    userDutymanagementController,
    FinancialAssistancePaybleController,
    UpdateAadhaarDetailsController,
    DuplicateControllerBank,
    AgeCohortReportController,
    DsMisReportController,
    MisReportWithFaultyController,
    NoAadharChangeController,
    DuplicateController,
    wBPdsChangeController,
    jnmpController,
    ApproveEditedFailedBenNameController,
    MisReportAllPhaseController,
    RejectDraftController,
    SchemeOnboardingController
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

// Financial Assistance Payble As On current Date
Route::controller(FinancialAssistancePaybleController::class)->group(function () {
    Route::get('select-financial-year-payment-assistance', 'selectFinancialYearForPaymentAssistance');
    Route::get('financial-assistance-payable', 'lotGeneratedPendingIndex');
    Route::post('lotGeneratedPendingAmountReport', 'lotGeneratedPendingAmountReport');
});


//Duty Management
Route::controller(userDutymanagementController::class)->group(function () {
    Route::get('userDutymanagement/Search', 'Search');
    Route::get('userDutymanagement', 'index')->name('userDutymanagement');
    Route::post('userDutymanagement/toggleActivate', 'toggleActivate')->name('userDutymanagement/toggleActivate');
    Route::get('adduser', 'adduser')->name('adduser');
    Route::post('adduserpost', 'adduserpost')->name('adduserpost');
    Route::post('getUserInfo', 'getUserInfo')->name('getUserInfo');
    // Route::post('userDutymanagement/getUserInfo', 'getUserInfo')->name('userDutymanagement.getUserInfo');

    Route::post('userDutymanagement/Update', 'Update')->name('Update');
    Route::post('userDutymanagement/toggleDuty', 'toggleDuty')->name('userDutymanagement/toggleDuty');
    Route::post('userDutymanagement/mapNewScheme', 'mapNewScheme')->name('userDutymanagement/mapNewScheme');
    Route::get('downloadUser', 'downloadUser');
});

// Aadhaar Modification
Route::controller(UpdateAadhaarDetailsController::class)->group(function () {
    Route::any('aadhaar-details-update-hod', 'markhodselect')->name('aadhaar-details-update-hod');
    Route::post('mark4edithodpost', 'mark4edithodpost')->name('mark4edithodpost');
    Route::get('aadhaar-details-update-list-approver', 'ListView')->name('aadhaar-details-update-list-approver');
    Route::get('changeAadhar', 'change')->name('change');
    Route::post('changeAadharPost', 'changePost')->name('changeAadharPost');
});


// Duplicate Bank Account
Route::controller(DuplicateControllerBank::class)->group(function () {
    Route::get('dedupBankListView', 'dedupBankListView')->name('dedupBankListView');
    Route::get('dedupBankView', 'dedupBankView')->name('dedupBankView');
    Route::post('dupBankReject', 'dupBankReject')->name('dupBankReject');
    Route::post('DupBankAccounttExcelDistrict', 'generate_excel_list');
    Route::get('DupBankAccounttExcelState', 'generate_excel_list_state')->name('generate_excel_list_state');
    Route::post('DupBankAccountDownload', 'generate_excel_list_state_download')->name('DupBankAccountDownload');
    Route::get('dedupBankUpdate', 'dedupBankUpdate')->name('dedupBankUpdate');
    Route::post('dedupBankUpdatePost', 'dedupBankUpdatePost')->name('dedupBankUpdatePost');
    Route::get('dedupBankCron', 'dedupBankCron')->name('dedupBankCron');
    Route::post('dedupBankSamePost', 'dedupBankSamePost')->name('dedupBankSamePost');
    Route::get('dedupBankMis', 'dedupBankMis')->name('dedupBankMis');
    Route::post('dedupBankMisPost', 'getData')->name('dedupBankMisPost');
    Route::post('revertReject', 'revertReject')->name('revertReject');
    Route::get('de-Duplicate-Bank-List', 'deDuplicateBankList')->name('de-Duplicate-Bank-List');
    Route::post('getDeduplicationList', 'getDeduplicationList')->name('getDeduplicationList');
    Route::post('getBankDeduplicationListexcel', 'getBankDeduplicationListexcel')->name('getBankDeduplicationListexcel');
});


// Age Cohort Report
Route::controller(AgeCohortReportController::class)->group(function () {
    Route::get('ageCohortGet', action: 'index');
    Route::post('ageCohortPost', 'getData')->name('ageCohortPost');
});


// Age Cohort Report
Route::controller(DsMisReportController::class)->group(function () {
    Route::get('dsreportphaseselect', action: 'dsReportphaseCommon');
    Route::post('dsMisReport', 'index')->name('dsMisReport');
    Route::post('dsMisReportPost', 'getData')->name('dsMisReportPost');
});

// Mis Report with Faulty
Route::controller(MisReportWithFaultyController::class)->group(function () {
    Route::get('misReportWithFaulty', action: 'index');
    Route::get('misReportWithFaultyPost', 'getData')->name('misReportWithFaultyPost');
    Route::get('misReportWithNormal', 'NormalEntryIndex')->name('misReportWithNormal');
    Route::post('misReportWithNormalPost', 'NormalEntryGetData')->name('misReportWithNormalPost');
});

// Aadhar not Avilable
Route::controller(NoAadharChangeController::class)->group(function () {
    Route::any('noaadharlist', action: 'list')->name('noaadharlist');
    Route::get('Viewnoaadhar', 'Viewnoaadhar')->name('Viewnoaadhar');
    Route::get('noaadharPost', 'noaadharPost')->name('noaadharPost');
    Route::post('misReportWithNormalPost', 'NormalEntryGetData')->name('misReportWithNormalPost');
    Route::post('BulkApprovenoaadhar', 'bulkApprove')->name('BulkApprovenoaadhar');
    Route::get('noaadharPdfDownload', 'pdf')->name('noaadharPdfDownload');
    Route::any('noaadharMisReport', 'misReport')->name('noaadharMisReport');
    Route::any('noaadharMisReportPost', 'misReportPost')->name('noaadharMisReportPost');
    Route::post('applicationListNoaadharExcel', 'generate_excel')->name('applicationListNoaadharExcel');
});

//Aadhar Dedplication
Route::controller(DuplicateController::class)->group(function () {
Route::any('lb-dup-aadhar-list-approved-verifier', 'dup_aadhar_approved_verifier');
Route::get('dedupAadhaarView', 'dedupAadhaarView');
Route::post('dupAadharReject', 'dupAadharReject')->name('dupAadharReject');
Route::post('dupAadharModify', 'dupAadharmodify')->name('dupAadharModify');
Route::any('lb-dup-aadhar-list-approved-approver', 'dup_aadhar_approved_approver');
Route::post('dupAadhaarApproved', 'dupAadhaarApproved')->name('dupAadhaarApproved');
Route::get('dupAadhaarMis','misAadhar')->name('dupAadhaarMis');
Route::any('dupAadhaarMisPost','misAadharPost')->name('dupAadhaarMisPost');
Route::get('dup-aadhar-ben-list', 'dupAadharBenList')->name('dup-aadhar-ben-list');
Route::post('dupAadharGetBenList', 'dupAadharGetBenList')->name('dupAadharGetBenList');
Route::post('GetdupAadharBenListExcel', 'GetdupAadharBenListExcel')->name('GetdupAadharBenListExcel');
});


////////////////////////////WBPDS MIS REPORT START /////////////////////////////////////////////////////
Route::controller(wBPdsChangeController::class)->group(function () {
    Route::get('aadharNameValidMIS', 'aadharNameValidMIS')->name('aadharNameValidMIS');
    Route::post('aadharNameValidMISPost', 'getData')->name('aadharNameValidMISPost');
});


// JNMP List at HOD
Route::controller(jnmpController::class)->group(function () {
    Route::get('jnmp-marked-data', 'jnmpMarkedDataAtHOD')->name('jnmp-marked-data');
    Route::post('jnmpMarkedData', 'jnmpMarkedData')->name('jnmpMarkedData');
    Route::post('generateJnmpDataHodExcel', 'generateJnmpDataHodExcel')->name('generateJnmpDataHodExcel');
});


/* Name Validation Failed 90 - 100 */
Route::controller(ApproveEditedFailedBenNameController::class)->group(callback: function () {
    Route::get('selectVerifiedMatchingScore', 'selectMatchingScore')->name('selectVerifiedMatchingScore');
    Route::get('approve-edited-name-failed-90-to-100', 'editIndex')->name('approve-edited-name-failed-90-to-100');
    Route::post('getVerifiedNameValidationFailed90to100', 'getVerifiedNameValidationFailed90to100')->name('getVerifiedNameValidationFailed90to100');
    Route::post('getEditFailedNameData90to100', 'getEditFailedNameData90to100')->name('getEditFailedNameData90to100');
    Route::post('updateFailedNameApprove90to100', 'updateFailedNameApprove90to100')->name('updateFailedNameApprove90to100');
    Route::get('mis-report-of-90-to-100', 'indexMisReport')->name('mis-report-of-90-to-100');
    Route::post('getMis90to100', 'getMis90to100')->name('getMis90to100');
});


// MIS Report for All Phase
Route::controller(MisReportAllPhaseController::class)->group(callback: function () {
    Route::get('misReportAllPhase', 'index');
    Route::post('misReportAllPhasePost', 'getData')->name('misReportAllPhasePost');
});


// HOD Reject Non Approved Data
Route::controller(RejectDraftController::class)->group(callback: function () {
    Route::any('rejectDraft', 'list');
    Route::post('getBenViewPersonalDataDraft', 'getBenViewPersonalData')->name('getBenViewPersonalDataDraft');
    Route::post('getBenViewContactDataDraft', 'getBenViewContactData')->name('getBenViewContactDataDraft');
    Route::post('getBenViewBankDataDraft', 'getBenViewBankData')->name('getBenViewBankDataDraft');
    Route::post('getBenViewAadharDataDraft', 'getBenViewAadharData')->name('getBenViewAadharDataDraft');
    Route::post('getBenViewPersonalDataFaultyDraft', 'getBenViewPersonalDataFaulty')->name('getBenViewPersonalDataFaultyDraft');
    Route::post('getBenViewContactDataFaultyDraft', 'getBenViewContactDataFaulty')->name('getBenViewContactDataFaultyDraft');
    Route::post('getBenViewBankDataFaultyDraft', 'getBenViewBankDataFaulty')->name('getBenViewBankDataFaultyDraft');
    Route::post('rejectDraftPost', 'reject');
});


//Scheme Onboard
Route::controller(SchemeOnboardingController::class)->group(callback: function () {
    Route::get('onboardscheme', 'index');
    Route::get('getschemefromtype', 'getschemefromtype');
    Route::post('schemeOnboardToggleActivate', 'schemeOnboardToggleActivate');
    Route::post('workflowListView', 'workflowListView');

    Route::post('getAddUpdateLevelInfo', 'getAddUpdateLevelInfo');
    Route::post('addUpdateMap', 'addUpdateMap');
    Route::post('addNewSchemeType', 'addNewSchemeType');

    //Scheme Add
    Route::post('getSchemeDetail', 'getSchemeDetail');
    Route::post('addUpdateScheme', 'addUpdateScheme');
    Route::post('getAllItemList', 'getAllItemList');
    Route::post('toggleItemStatus', 'toggleItemStatus');
    Route::post('deleteItem', 'deleteItem');
});

