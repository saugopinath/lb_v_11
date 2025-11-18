<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ApprovedVerificationPendingController,
    ApproveEditedFailedBenNameController,
    FAQController,
    PolicyController,
    AuthenticationController,
    BankDetailsEditBandhanBankController,
    BeneficiaryCommonController,
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
    BeneficiaryLogController,
    BenNameValidationFailedController,
    PensionformFaultyReportController,
    casteManagementController,
    TrackApplicantController,
    MasterDataController,
    DeactivatedBeneficiaryController,
    StopBeneficiaryController,
    CmoGrivanceWorkflowController1,
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
    BackfromJBController,
    NoAadharChangeController,
    BenNameValidationFailedController
    NameValidationController,
    CmoGrivanceWorkflowController1,
    DuplicateController,
    MisReportWithFaultyController,
    StopBeneficiaryController,
    WorkflowController,
    ApprovedEditedBankDetailsController,
    ApproveEditedBankDetailsController,
    BasicAuthController,
    CheckingDataController,
    cmoDataFetchNewController,
    DocumentTypeController,
    DuplicateControllerBank,
    FaultyLbEntryeditController,
    LakkhiBhandarAjaxEntry,
    SchemeOnboardingController,
    WorkflowFaultyController
    CmoGrivanceWorkflowController1,
    BenNameValidationFailedController,
    ApproveEditedFailedBenNameController,
    BankDetailsEditBandhanBankController
    MasterDataController,
    UpdateBankDetailsController,
    DeactivatedBeneficiaryController,
    DrilldownFaultyReportController,
    MisReportController
    jnmpController,
    DeactivatedBeneficiaryController,
    DsMisReportController,
    FinancialAssistancePaybleController,
    LotReportController,
    NameValidationController,
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

// Route::get('/force-logout', function () {
//     auth()->logout();
//     session()->invalidate();
//     session()->regenerateToken();
//     return 'Logged out successfully!';
// });

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
    Route::post('ajax_personal_entry_wtSws', 'personalEntry');
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
    Route::any('application-details-read-only', 'applicantreadonlyview')->name('application-details-read-only');

    //Route::any('application-list-common', 'applicationStatusList');
});
Route::controller(LegacyProcessController::class)->group(function () {
    Route::any('legacy/getBankDetails', 'getBankDetails');
    Route::post('bankIfsc', 'bankIfsc')->name('bankIfsc');
});
Route::controller(PensionCommonController::class)->group(function () {
    Route::any('applicant/track/', 'applicantTrack');
    
});
Route::controller(PensionformReportController::class)->group(function () {
    Route::any('application-list-common', 'applicationStatusList');
});
Route::controller(BeneficiaryListReportExcel::class)->group(function () {
    Route::any('applicationListExcel', 'download_excel')->name('applicationListExcel');
});
Route::controller(PensionformFaultyReportController::class)->group(function () {
    Route::any('application-list-common-faulty', 'applicationStatusList');
    Route::get('applicationFaultyListExcel', 'generate_excel')->name('applicationFaultyListExcel');
});
Route::controller(casteManagementController::class)->group(function () {
    Route::any('casteManagement', 'index')->name('casteManagement');
    Route::get('changeCaste', 'change')->name('changeCaste');
    Route::post('changeCastePost', 'changePost')->name('changeCastePost');
    Route::any('lb-caste-application-list', 'applicationStatusList')->name('lb-caste-application-list');
    Route::post('applicationListExcelCasteChange', 'generate_excel');
    Route::any('workflowCaste', 'workflow')->name('workflowCaste');
    Route::post('getCasteApplieddata', 'getCastedata')->name('getCasteApplieddata');
    Route::post('ajaxModifiedCasteEncolser', 'ajaxModifiedCasteEncolser')->name('ajaxModifiedCasteEncolser');
    Route::post('verifyDataCaste', 'verifydata')->name('verifyDataCaste');
    Route::post('approveDataCaste',  'approvedata')->name('approveDataCaste');
    Route::get('casteInfoMis', 'casteInfoMis')->name('casteInfoMis');
    Route::post('casteInfoMisPost', 'getData')->name('casteInfoMisPost');
    Route::any('lb-caste-reverted-list', 'applicationRevertedList')->name('lb-caste-reverted-list');
    Route::get('lb-caste-revert-edit', 'revertedit')->name('lb-caste-revert-edit');
    Route::post('lb-caste-revert-edit-post', 'reverteditPost')->name('lb-caste-revert-edit-post');
    Route::get('caste-matched-report', 'casteMatchedReport')->name('caste-matched-report');
    Route::any('application-details-read-only/{id}', 'applicantreadonlyview')->name('application-details-read-only');
    Route::any('application-details-read-only', 'applicantreadonlyview')->name('application-details-read-only');
});
// Route::any('application-details-read-only/{id}', 'LakkhiBhandarWCDformController@applicantreadonlyview')->name('application-details-read-only');
// Route::any('application-details-read-only', 'LakkhiBhandarWCDformController@applicantreadonlyview')->name('application-details-read-only');

Route::controller(MasterDataController::class)->group(function () {
    Route::post('masterDataAjax/getUrban', 'getUrban');
    Route::post('masterDataAjax/getTaluka', 'getTaluka');
    Route::post('masterDataAjax/getGp', 'getGp');
    Route::post('masterDataAjax/getWard', 'getWard');
});
Route::controller(DeactivatedBeneficiaryController::class)->group(function () {
    Route::any('deacivated-list', 'listReport');
    Route::post('deacivated-list-Excel', 'generate_excel');
});
Route::controller(StopBeneficiaryController::class)->group(function () {
    Route::any('stop-list', 'listReport');
    Route::post('stop-list-Excel', 'generate_excel');
});
Route::controller(BeneficiaryCommonController::class)->group(function () {
  Route::post('getPersonalApproved', 'getPersonalApproved')->name('getPersonalApproved');
  Route::post('getAadhaarApproved', 'getAadhaarApproved')->name('getAadhaarApproved');
  Route::post('getContactApproved', 'getContactApproved')->name('getContactApproved');
  Route::post('getBankApproved', 'getBankApproved')->name('getBankApproved');
  Route::post('getInvestigatorApproved', 'getInvestigatorApproved')->name('getInvestigatorApproved');
});

Route::controller(CmoGrivanceWorkflowController1::class)->group(function () {
   Route::get('cmo-grievance-entry-list1', 'opListCmo')->name('cmo-grievance-entry-list1');
   Route::get('cmo-op_entryList1', 'cmoEntryList')->name('cmo-op_entryList1');
});

Route::controller(BackfromJBController::class)->group(function () { 
    Route::any('backfromjb', 'marked_list')->name('backfromjb');
    Route::any('showbackfromjb/{application_id}/{is_faulty}', 'showApplicantDetails')->name('showbackfromjb');
    Route::post('forward-backfromjb', 'verifydata')->name('forward-backfromjb');
});

Route::controller(NoAadharChangeController::class)->group(function () {
    Route::any('noaadharlist','list')->name('noaadharlist');
    Route::get('Viewnoaadhar','Viewnoaadhar')->name('Viewnoaadhar');
    Route::post('noaadharPost','noaadharPost')->name('noaadharPost');
    Route::post('BulkApprovenoaadhar','bulkApprove')->name('BulkApprovenoaadhar');
    Route::get('noaadharPdfDownload','pdf')->name('noaadharPdfDownload');
    Route::any('noaadharMisReport','misReport')->name('noaadharMisReport');
    Route::any('noaadharMisReportPost','misReportPost')->name('noaadharMisReportPost');
    Route::post('applicationListNoaadharExcel','generate_excel')->name('applicationListNoaadharExcel');
});

Route::controller(BenNameValidationFailedController::class)->group(function () {
    Route::get('selectMatchingScore','selectMatchingScore')->name('selectMatchingScore');
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
    /////////////////////////
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

Route::controller(CmoGrivanceWorkflowController1::class)->group(function () {
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

    // CMO HOD
    Route::get('cmo-grievance-hod1', 'hodIndex')->name('cmo-grievance-hod1');
    Route::post('cmo-grievance-hod-listing1', 'hodList')->name('cmo-grievance-hod-listing1');
    Route::post('cmo-grievance-hod-view1', 'hodView')->name('cmo-grievance-hod-view1');
    Route::post('cmo-grievance-hod-post1', 'sendBackToCmo')->name('cmo-grievance-hod-post1');
    Route::post('cmo-grievance-hod-revert1', 'hodRevert')->name('cmo-grievance-hod-revert1');

    // CMO MIS Report
    Route::get('cmo-mis-report1', 'cmoMisReport')->name('cmo-mis-report1');
    Route::post('get-mis-report1', 'getMisReport')->name('get-mis-report1');

    // Map & Municipality related
    Route::any('cmo-mapbosget', 'mapbosget')->name('cmo-mapbosget');
    Route::any('cmo-getblksublist', 'getblksublist')->name('cmo-getblksublist');
    Route::any('cmo-getMunicipalityList', 'getMunicipalityList')->name('cmo-getMunicipalityList');
    Route::any('cmo-mapbospost', 'mapbospost')->name('cmo-mapbospost');
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

// Duplicate Bank Account


Route::controller(DuplicateControllerBank::class)->group(function () {
    Route::get('dedupBankListView', 'dedupBankListView');
    Route::get('dedupBankView', 'dedupBankView');
    Route::post('dupBankReject', 'dupBankReject')->name('dupBankReject');
    Route::post('DupBankAccounttExcelDistrict', 'generate_excel_list');
    Route::get('DupBankAccounttExcelState', 'generate_excel_list_state')->name('DupBankAccounttExcelState');
    Route::post('DupBankAccountDownload', 'generate_excel_list_state_download')->name('DupBankAccountDownload');
    Route::get('dedupBankUpdate', 'dedupBankUpdate')->name('dedupBankUpdate');
    Route::post('dedupBankUpdatePost', 'dedupBankUpdatePost')->name('dedupBankUpdatePost');
    Route::get('dedupBankCron', 'dedupBankCron')->name('dedupBankCron');
    Route::post('dedupBankSamePost', 'dedupBankSamePost')->name('dedupBankSamePost');
    Route::get('dedupBankMis', 'dedupBankMis');
    Route::post('dedupBankMisPost', 'getData')->name('dedupBankMisPost');
});

Route::controller(LakkhiBhandarAjaxEntry::class)->group(function () {
    Route::post('ajaxGetEncloser', 'ajaxGetEncloser');
});

//Faulty Without Doc
Route::controller(WorkflowFaultyController::class)->group(function () {
    Route::any('workflowFaulty', 'applicationdetails');
    Route::post('verifyDataFaulty', 'forwardDataFaulty');
    Route::post('getBenViewPersonalDataFaulty', 'getBenViewPersonalDataFaulty')->name('getBenViewPersonalDataFaulty');
    Route::post('getBenViewAadharDataFaulty', 'getBenViewAadharDataFaulty')->name('getBenViewAadharDataFaulty');
    Route::post('getBenViewContactDataFaulty', 'getBenViewContactDataFaulty')->name('getBenViewContactDataFaulty');
    Route::post('getBenViewBankDataFaulty', 'getBenViewBankDataFaulty')->name('getBenViewBankDataFaulty');
    Route::post('getBenViewInvestigatorData', 'getBenViewInvestigatorData')->name('getBenViewInvestigatorData');
    Route::post('ajaxGetEncloserFaulty', 'ajaxGetEncloserFaulty');
});

//Process Incomplete faulty application
Route::controller(FaultyLbEntryeditController::class)->group(function () {
    Route::any('faulty-lb-draft-list', 'viwlist');
    Route::get('faulty-lb-entry-edit', 'edit')->name('faulty-lb-entry-edit');
    Route::post('faulty_ajax_check_dup_aadhar', 'checkDupaadhaar');
    Route::post('ajax_personal_entry_faulty', 'personalEntry');
    Route::post('ajax_contact_entry_faulty', 'contactEntry');
    Route::post('ajax_bank_entry_faulty', 'bankEntry');
    Route::post('ajax_encloser_faulty_entry', 'encloserEntry');
    Route::post('ajax_check_encloser_faulty', 'checkEncolser');
    Route::post('ajax_declaration_entry_faulty', 'declarationEntry');
    Route::post('getfaultyBenViewPersonalData', 'getBenViewPersonalData')->name('getfaultyBenViewPersonalData');
    Route::post('getfaultyBenViewContactData', 'getBenViewContactData')->name('getfaultyBenViewContactData');
    Route::post('getfaultyBenViewBankData', 'getBenViewBankData')->name('getfaultyBenViewBankData');
    Route::post('getfaultyBenViewEncloserData', 'getBenViewEncloserData')->name('getfaultyBenViewEncloserData');
    Route::post('getfaultyBenViewAadharData', 'getBenViewAadharData')->name('getfaultyBenViewAadharData');
    Route::post('verifyDatafaultymigrate', 'forwardData');
    Route::post('getExcelfaulty', 'getExcel')->name('getExcelfaulty');
    Route::post('searchfaulty-application', 'searchfaultyapplication')->name('searchfaulty-application');
    Route::get('downaloadEncloser_faulty', 'viewimage');
});


Route::controller(DuplicateControllerBank::class)->group(function () {
    /////////////////////////De-duplication Bank Approver/////////////////////////////////////
    Route::get('de-dup-Bank-Approver-List-View', 'dedupBankApproverListView')->name('de-dup-Bank-Approver-List-View');
    Route::post('dedupBankApproverList', 'dedupBankApproverList')->name('dedupBankApproverList');
    Route::post('getApproverModalView', 'getApproverModalView')->name('getApproverModalView');
    Route::post('updateDuplicateBankApprover', 'updateDuplicateBankApprover')->name('updateDuplicateBankApprover');
    // Duplicate Bank List at Approver End
    Route::get('de-Duplicate-Bank-List', 'deDuplicateBankList')->name('de-Duplicate-Bank-List');
    Route::post('getDeduplicationList', 'getDeduplicationList')->name('getDeduplicationList');
    Route::post('getBankDeduplicationListexcel', 'getBankDeduplicationListexcel')->name('getBankDeduplicationListexcel');
});

// Refresh MV Manually
Route::controller(CheckingDataController::class)->group(function () {
    Route::get('refresh-meterialized-view', 'index')->name('refresh-meterialized-view');
    Route::post('getrefreshMVData', 'getrefreshMVData')->name('getrefreshMVData');
    Route::post('postrefreshMVData', 'postrefreshMVData')->name('postrefreshMVData');
});

Route::controller(BasicAuthController::class)->group(function () {
    Route::post('jnmpInsertData', 'getData')->name('jnmpInsertData');
    Route::get('jnmp-fetch-callback-api', 'index')->name('jnmp-fetch-callback-api');
    Route::post('totalJnmp', 'totalJnmp')->name('totalJnmp');
    Route::post('jnmpDataCallbackDetails', 'detailsCallBack')->name('jnmpDataCallbackDetails');
    Route::post('jnmpDataMarkasDeathInLB', 'jnmpDataMarkasDeathInLB')->name('jnmpDataMarkasDeathInLB');
    Route::get('jnmpMarkedProcess', 'jnmpMarkedProcess')->name('jnmpMarkedProcess');
});


// CMO Data Fetch
Route::controller(cmoDataFetchNewController::class)->group(function () {
    Route::get('cmo-data-fetching', 'index')->name('cmo-data-fetching');
    Route::post('cmo-data-fetch-response', 'dataFetch')->name('cmo-data-fetch-response');
    Route::get('cmo-data-listing', 'dataListing')->name('cmo-data-listing');
    Route::post('cmo-data-fetch-import', 'dataImport')->name('cmo-data-fetch-import');
});

Route::controller(SchemeOnboardingController::class)->group(function () {
    //Scheme Onboard
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


//Document Management
Route::resource('document-mgmt', DocumentTypeController::class);
Route::controller(DocumentTypeController::class)->group(function () {
    Route::get('scheme-doc-map', 'assigndocumenttoscheme');
    Route::get('ajaxschemeChnageRequest/{id}', 'ajaxschemeChnageRequest');
    Route::post('documentsetupforScheme', 'documentsetupforScheme');
    Route::get('ajaxschemenameRequest/{id}', 'ajaxschemenameRequest');

    Route::get('document-mgmt-list', 'index')->name('getDocumentList');
    Route::post('documentToggleActivate', 'documentToggleActivate')->name('documentToggleActivate');
    Route::post('deleteDocument', 'deleteDocument')->name('deleteDocument');
    Route::post('documentSave', 'documentSaveUpdate')->name('documentSave');
    Route::post('editDocument', 'editDocument')->name('editDocument');
    Route::post('documentUpdate', 'documentSaveUpdate')->name('documentUpdate');
Route::post('bankIfsc', 'LegacyProcessController@bankIfsc')->name('bankIfsc');
Route::controller(BankDetailsEditBandhanBankController::class)->group(function () {
    Route::post('getBankFailedexcel', 'getBankFailedexcel')->name('getBankFailedexcel');
});


Route::controller(BenNameValidationFailedController::class)->group(function () {
    Route::get('selectMatchingScore', 'selectMatchingScore')->name('selectMatchingScore');
    Route::get('edit-name-failed-90-to-100', 'editIndex')->name('edit-name-failed-90-to-100');
    Route::post('editFailedNameDetails', 'editFailedNameDetails')->name('editFailedNameDetails');
    Route::post('getDataNameValidationFailed90to100', 'getDataNameValidationFailed90to100')->name('getDataNameValidationFailed90to100');
    Route::post('updateNameValidationFailed90to100', 'updateNameValidationFailed90to100')->name('updateNameValidationFailed90to100');
    Route::post('failedNameAjaxViewPassbook', 'failedNameAjaxViewPassbook')->name('failedNameAjaxViewPassbook');
});


Route::controller(ApproveEditedFailedBenNameController::class)->group(function () {
    Route::get('mis-report-of-90-to-100', 'indexMisReport')->name('mis-report-of-90-to-100');
    Route::get('selectVerifiedMatchingScore', 'selectMatchingScore')->name('selectVerifiedMatchingScore');
    Route::post('getMis90to100', 'getMis90to100')->name('getMis90to100');
    Route::post('nameMismatchRejectOtpVerify', 'nameMismatchRejectOtpVerify')->name('nameMismatchRejectOtpVerify');
    Route::get('approve-edited-name-failed-90-to-100', 'editIndex')->name('approve-edited-name-failed-90-to-100');
    Route::post('getEditFailedNameData90to100', 'getEditFailedNameData90to100')->name('getEditFailedNameData90to100');
    Route::post('getVerifiedNameValidationFailed90to100', 'getVerifiedNameValidationFailed90to100')->name('getVerifiedNameValidationFailed90to100');
    Route::post('updateFailedNameApprove90to100', 'updateFailedNameApprove90to100')->name('updateFailedNameApprove90to100');
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
// Route::get('de-activate-beneficiary', 'DeactivatedBeneficiaryController@index')->name('de-active-beneficiary');
// Route::post('get-linelisting-deactive', 'DeactivatedBeneficiaryController@getData')->name('get-linelisting-deactive');
// Route::post('getBeneficiaryPersonalData', 'DeactivatedBeneficiaryController@getBeneficiaryPersonalData')->name('getBeneficiaryPersonalData');
// Route::post('updateStopPaymentFinal', 'DeactivatedBeneficiaryController@updateStopPaymentFinal')->name('updateStopPaymentFinal');
// Route::get('de-activated-list', 'DeactivatedBeneficiaryController@deActivatedReport')->name('de-activated-list');
// Route::post('getDeActivatedBenDataList', 'DeactivatedBeneficiaryController@getDeActivatedBenDataList')->name('getDeActivatedBenDataList');
// De-activated Beneficiary
Route::controller(DeactivatedBeneficiaryController::class)->group(function () {
    Route::get('de-activate-beneficiary', 'index')->name('de-active-beneficiary');
    Route::post('get-linelisting-deactive', 'getData')->name('get-linelisting-deactive');
    Route::post('getBeneficiaryPersonalData', 'getBeneficiaryPersonalData')->name('getBeneficiaryPersonalData');
    Route::post('updateStopPaymentFinal', 'updateStopPaymentFinal')->name('updateStopPaymentFinal');
    Route::get('de-activated-list', 'deActivatedReport')->name('de-activated-list');
    Route::post('getDeActivatedBenDataList', 'getDeActivatedBenDataList')->name('getDeActivatedBenDataList');
});

Route::controller(BeneficiaryCommonController::class)->group(function () {
    Route::post('getPersonalApproved', 'getPersonalApproved')->name('getPersonalApproved');
    Route::post('getAadhaarApproved', 'getAadhaarApproved')->name('getAadhaarApproved');
    Route::post('getContactApproved', 'getContactApproved')->name('getContactApproved');
    Route::post('getBankApproved', 'getBankApproved')->name('getBankApproved');
    Route::post('getInvestigatorApproved', 'getInvestigatorApproved')->name('getInvestigatorApproved');
});

// Route::post('getPersonalApproved', 'BeneficiaryCommonController@getPersonalApproved')->name('getPersonalApproved');
// Route::post('getAadhaarApproved', 'BeneficiaryCommonController@getAadhaarApproved')->name('getAadhaarApproved');
// Route::post('getContactApproved', 'BeneficiaryCommonController@getContactApproved')->name('getContactApproved');
// Route::post('getBankApproved', 'BeneficiaryCommonController@getBankApproved')->name('getBankApproved');
// Route::post('getInvestigatorApproved', 'BeneficiaryCommonController@getInvestigatorApproved')->name('getInvestigatorApproved');
Route::controller(MisReportController::class)->group(function () {

    Route::get('misReport-faulty', 'faulty')->name('misReport-faulty');
    Route::post('misReport-faulty-Post', 'getData_faulty')->name('misReport-faulty-Post');
});

Route::controller(DrilldownFaultyReportController::class)->group(function () {
    Route::get('faulty-application-report', 'index')->name('faulty-application-report');
    Route::post('getFaultyDistAppData', 'getFaultyDistAppData')->name('getFaultyDistAppData');
    Route::post('getFaultyBlockSubdivAppData', 'getFaultyBlockSubdivAppData')->name('getFaultyBlockSubdivAppData');
});

// Route::get('faulty-application-report', 'DrilldownFaultyReportController@index')->name('faulty-application-report');
// Route::post('getFaultyDistAppData', 'DrilldownFaultyReportController@getFaultyDistAppData')->name('getFaultyDistAppData');
// Route::post('getFaultyBlockSubdivAppData', 'DrilldownFaultyReportController@getFaultyBlockSubdivAppData')->name('getFaultyBlockSubdivAppData');

Route::controller(BasicAuthController::class)->group(function () {
    Route::get('jnmp-fetch-callback-api', 'index')->name('jnmp-fetch-callback-api');
    Route::post('jnmpDataMarkasDeathInLB', 'jnmpDataMarkasDeathInLB')->name('jnmpDataMarkasDeathInLB');
    Route::post('totalJnmp', 'totalJnmp')->name('totalJnmp');
    Route::post('jnmpInsertData', 'getData')->name('jnmpInsertData');
    Route::post('jnmpDataCallbackDetails', 'detailsCallBack')->name('jnmpDataCallbackDetails');
    Route::get('jnmpMarkedProcess', 'jnmpMarkedProcess')->name('jnmpMarkedProcess');
});







// Route::get('misReport-faulty', 'MisReportController@faulty');
// Route::post('misReport-faulty-Post', 'MisReportController@getData_faulty')->name('misReport-faulty-Post');
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

Route::controller(LotReportController::class)->group(function () {
    // Month wise payment report
    Route::get('monthwise-payment-report', 'getMonthWisePayReport')->name('monthwise-payment-report');
    Route::post('totalMonthwisePaymentReport', 'totalMonthwisePaymentReport')->name('totalMonthwisePaymentReport');
    // Route::get('financial-assistance-payable', 'lotGeneratedPendingIndex')->name('financial-assistance-payable');
    // Route::post('lotGeneratedPendingAmountReport', 'lotGeneratedPendingAmountReport')->name('lotGeneratedPendingAmountReport');
    Route::post('paymentlotGeneratedPendingAmountReport', 'paymentlotGeneratedPendingAmountReport')->name('paymentlotGeneratedPendingAmountReport');
    // Route::get('select-financial-year-payment-assistance', 'selectFinancialYearForPaymentAssistance')->name('select-financial-year-payment-assistance');
    Route::get('previous-financial-assistance-payable', 'previousFinancialAssistancePayable')->name('previous-financial-assistance-payable');
    Route::post('getPreviousFinancialAssistancePayable', 'getPreviousFinancialAssistancePayable')->name('getPreviousFinancialAssistancePayable');

    // Monthly Disbursement Report Based on the Lot Pushed date to the Bank
    Route::get('monthly-disbursement', 'monthlyDisbursementIndex')->name('monthly-disbursement');
    Route::post('monthly-disbursement-report', 'monthlyDisbursement')->name('monthly-disbursement-report');

    //HOD Report
    Route::any('report-validation-lot', 'reportValidationLot')->name('report-validation-lot');
    Route::any('report-payment-lot', 'reportPaymentLot')->name('report-payment-lot');
    Route::any('report-name-mismatch-validation-list', 'getNameMismatchValidationList')->name('getNameMismatchValidationList');
    Route::post('getNameMismatchExcelList', 'getNameMismatchExcelList')->name('getNameMismatchExcelList');
    Route::any('datewise-lot-report', 'reportDatewiseLot')->name('reportDatewiseLot');

    // Legacy Validation Report
    Route::get('legacy-validation-report', 'legacyValidationReport')->name('legacy-validation-report');
    Route::post('legacyValidationLotReport', 'legacyValidationLotReport')->name('legacyValidationLotReport');
});

Route::controller(FinancialAssistancePaybleController::class)->group(function () {
    // Financial Assistance Payble As On current Date
    Route::get('select-financial-year-payment-assistance', 'selectFinancialYearForPaymentAssistance')->name('select-financial-year-payment-assistance');
    Route::get('financial-assistance-payable', 'lotGeneratedPendingIndex')->name('financial-assistance-payable');
    Route::post('lotGeneratedPendingAmountReport', 'lotGeneratedPendingAmountReport')->name('lotGeneratedPendingAmountReport');
});
Route::controller(NameValidationController::class)->group(function () {
Route::get('misReport-nameValidation', 'misReport');
Route::get('misReport-nameValidation-Post', 'getData')->name('misReport-nameValidation-Post');
});
