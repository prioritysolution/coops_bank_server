<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// admin controller maping

use App\Http\Controllers\Admin\AdminLogin;
use App\Http\Controllers\Admin\OrgRegister;

// admin Controller end here

// user controller mapping
use App\Http\Controllers\Organisation\UserLogin;
use App\Http\Controllers\Organisation\ProcessMaster;
use App\Http\Controllers\Organisation\ProcessMembership;
use App\Http\Controllers\Organisation\ProcessDeposit;
use App\Http\Controllers\Organisation\ProcessBankAccount;
use App\Http\Controllers\Organisation\ProcessLoan;
use App\Http\Controllers\Organisation\ProcessInvestment;
use App\Http\Controllers\Organisation\ProcessBorrowings;
use App\Http\Controllers\Organisation\ProcessOpening;
use App\Http\Controllers\Organisation\ProcessFinancialReport;
use App\Http\Controllers\Organisation\ProcessModuleReport;
use App\Http\Controllers\Organisation\ProcessVoucherEntry;
// user Controller end here

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// admin login route

Route::post('/admin/login',[AdminLogin::class,'process_admin_login'])->middleware('api_access');

Route::group([
    'middleware' => ['auth:sanctum',]
],function(){
    Route::get('/admin/dashboard',[AdminLogin::class,'get_admin_dashboard']);
    Route::get('/admin/logout',[AdminLogin::class,'logout']);
    Route::get('/OrgSetup/GetType',[OrgRegister::class,'get_org_type']);
    Route::get('/OrgSetup/GetModule',[OrgRegister::class,'get_org_module']);
    Route::post('/OrgSetup/AddOrg',[OrgRegister::class,'process_org']);
    Route::get('/OrgSetup/OrgList',[OrgRegister::class,'get_org_list']);
    Route::post('/OrgSetup/MapBranch',[OrgRegister::class,'process_org_branch']);
    Route::get('/OrgSetup/GetBranch/{org_id}',[OrgRegister::class,'get_orgwise_branch']);
    Route::post('/OrgSetup/AddModule',[OrgRegister::class,'process_org_module']);
    Route::post('/OrgSetup/AddFinancialYear',[OrgRegister::class,'process_org_fin_year']);
    Route::get('/OrgSetup/GetListOrgModule',[OrgRegister::class,'get_org_module_list']);
    Route::post('/OrgSetup/AddRentelData',[OrgRegister::class,'process_org_rental']);
    Route::post('/OrgSetup/AddSMSData',[OrgRegister::class,'process_sms_date']);
    Route::get('/OrgSetup/GetOrgUserRole',[OrgRegister::class,'get_org_user_role']);
    Route::post('/OrgSetup/AddOrgAdminUser',[OrgRegister::class,'process_org_admin_user']);
    Route::get('/OrgSetup/CheckFinYear/{org_id}',[OrgRegister::class,'check_fin_year']);
    Route::get('/OrgSetup/GetOrgAccountHead',[OrgRegister::class,'get_org_acct_headlist']);
    Route::post('/OrgSetup/AddOrgAcctSubHead',[OrgRegister::class,'process_org_acct_sub_head']);
    Route::post('/OrgSetup/AddOrgAcctLedger',[OrgRegister::class,'process_org_acct_ledger']);
    Route::get('/OrgSetup/GetOrgAcctSubHead',[OrgRegister::class,'get_org_acct_sub_head']);
    Route::get('/OrgSetup/GetorgAcctLedger',[OrgRegister::class,'get_org_acct_ledger']);
    Route::post('/admin/AddUser',[AdminLogin::class,'process_admin_user']);
    Route::post('/admin/ChangeUserPassword',[AdminLogin::class,'process_update_Password']);
    Route::get('/admin/GetRoleAsignUserList',[AdminLogin::class,'get_admin_user_list']);
    Route::get('/admin/AssignRoleList',[AdminLogin::class,'get_user_mappling_module']);
    Route::post('/admin/AssignAdminModule',[AdminLogin::class,'process_role_menue']);
    Route::get('/OrgSetup/GetDeposit/ProductType',[OrgRegister::class,'get_deposit_prod_type']);
    Route::get('/OrgSetup/GetDeposit/DepositType',[OrgRegister::class,'get_deposit_type']);
    Route::get('/OrgSetup/GetDeposit/AccountGl/{type_name}',[OrgRegister::class,'get_deposit_gl']);
    Route::post('/OrgSetup/DepositProduct/AddProduct',[OrgRegister::class,'process_deposit_product']);
    Route::get('/OrgSetup/DepositProduct/GetProdList',[OrgRegister::class,'get_product_list']);
    Route::get('/OrgSetup/DepositProduct/CheckModule/{org_id}',[OrgRegister::class,'check_org_deposit_module']);
    Route::get('/OrgSetup/DepositProduct/GetInttType',[OrgRegister::class,'get_deposit_intt_type']);
    Route::get('/OrgSetup/DepositProduct/GetDurUnit',[OrgRegister::class,'get_deposit_dur_unit']);
    Route::get('/OrgSetup/DepositProduct/GetFineOn',[OrgRegister::class,'get_deposit_fine_on']);
    Route::post('/OrgSetup/DepositProduct/MapOrgDepositProduct',[OrgRegister::class,'process_org_deposit_product']);
    Route::get('/OrgSetup/LoanProduct/GetProductType',[OrgRegister::class,'get_loan_prd_type']);
    Route::get('/OrgSetup/LoanProduct/AccountGl/{type_name}',[OrgRegister::class,'get_loan_gl']);
    Route::post('/OrgSetup/LoanProduct/AddProduct',[OrgRegister::class,'process_loan_product']);
    Route::get('/OrgSetup/LoanProduct/GetProdList',[OrgRegister::class,'get_losn_prod_list']);
    Route::get('/OrgSetup/LoanProduct/CheckModule/{org_id}',[OrgRegister::class,'check_org_loan_module']);
    Route::get('/OrgSetup/LoanProduct/GetRepayType',[OrgRegister::class,'get_loan_repay_type']);
    Route::get('/OrgSetup/LoanProduct/GetDurUnit',[OrgRegister::class,'get_loan_dur_unit']);
    Route::get('/OrgSetup/LoanProduct/GetOverdueOn',[OrgRegister::class,'get_loan_overdue_type']);
    Route::get('/OrgSetup/LoanProduct/GetGraceOn',[OrgRegister::class,'get_loan_greace_type']);
    Route::get('/OrgSetup/LoanProduct/GetDepositProd/{org_id}',[OrgRegister::class,'get_orgwise_deposit_prod']);
    Route::post('/OrgSetup/LoanProduct/MapProduct',[OrgRegister::class,'process_org_loan_product']);
    Route::get('/OrgSetup/GetMemberType',[OrgRegister::class,'get_member_type']);
    Route::get('/OrgSetup/GetMemberGL/{type_name}',[OrgRegister::class,'get_admission_gl']);
    Route::post('/OrgSetup/AddShareProduct',[OrgRegister::class,'process_share_product']);
});

// admin route end here

// user route here

Route::post('/user/login',[UserLogin::class,'process_user_login'])->middleware('api_access');

Route::group([
    'middleware' => ['auth:sanctum',]
],function(){
    // login route

    Route::get('/Org/GetFinancialYear/{org_id}',[UserLogin::class,'get_current_financial_year']);
    Route::get('/Org/GetUserDashboard/{org_id}',[UserLogin::class,'get_dashboard']);
    Route::post('/Org/GetDashboardItem',[UserLogin::class,'get_dashboard_item']);
    Route::post('/Org/User/ProcessLogOut',[UserLogin::class,'process_logout']);

    // login route end here

    // master route

    Route::post('/Org/MasterSetup/AddState',[ProcessMaster::class,'process_add_state']);
    Route::get('/Org/MasterSetup/GetStateList/{org_id}',[ProcessMaster::class,'get_sate_list']);
    Route::put('/Org/MasterSetup/UpdateState',[ProcessMaster::class,'process_update_state']);
    Route::post('/Org/MasterSetup/AddDistrict',[ProcessMaster::class,'process_add_district']);
    Route::get('/Org/MasterSetup/GetDistList/{org_id}',[ProcessMaster::class,'get_dist_list']);
    Route::put('/Org/MasterSetup/UpdateDist',[ProcessMaster::class,'process_update_dist']);
    Route::get('/Org/MasterSetup/GetStateWistDist/{state_id}/{org_id}',[ProcessMaster::class,'get_statewise_dist']);
    Route::post('/Org/MasterSetup/AddBlock',[ProcessMaster::class,'process_add_block']);
    Route::get('/Org/MasterSetup/GetBlockList/{org_id}',[ProcessMaster::class,'get_block_list']);
    Route::put('/Org/MasterSetup/UpdateBlock',[ProcessMaster::class,'process_update_block']);
    Route::post('/Org/MasterSetup/AddPoliceStation',[ProcessMaster::class,'process_add_police']);
    Route::get('/Org/MasterSetup/GetPoliceList/{org_id}',[ProcessMaster::class,'get_police_list']);
    Route::put('/Org/MasterSetup/UpdatePoliceStation',[ProcessMaster::class,'process_update_police']);
    Route::post('/Org/MasterSetup/AddPostOffice',[ProcessMaster::class,'process_add_postoffice']);
    Route::get('/Org/MasterSetup/GetPostOffice/{org_id}',[ProcessMaster::class,'get_post_office']);
    Route::put('/Org/MasterSetup/UpdatePostOffice',[ProcessMaster::class,'process_update_postoffice']);
    Route::get('/Org/MasterSetup/GetDistWiseBlock/{org_id}/{dist_id}/{state_id}',[ProcessMaster::class,'get_distwise_block']);
    Route::post('/Org/MasterSetup/AddVillage',[ProcessMaster::class,'process_add_village']);
    Route::get('/Org/MasterSetup/GetVillageList/{org_id}',[ProcessMaster::class,'get_village_list']);
    Route::put('/Org/MasterSetup/UpdateVillage',[ProcessMaster::class,'process_update_village']);
    Route::post('/Org/MasterSetup/AddUnit',[ProcessMaster::class,'process_add_unit']);
    Route::get('/Org/MasterSetup/GetUnitList/{org_id}',[ProcessMaster::class,'get_unit_list']);
    Route::put('/Org/MasterSetup/UpdateUnit',[ProcessMaster::class,'process_update_unit']);
    Route::get('/Org/MasterSetup/GetCashDenom',[ProcessMaster::class,'get_cash_denom']);
    Route::get('/Org/MasterSetup/GetRelationType',[ProcessMaster::class,'get_relation_type']);
    Route::get('/Org/MasterSetup/GetGender',[ProcessMaster::class,'get_gender_list']);
    Route::get('/Org/MasterSetup/GetCaste',[ProcessMaster::class,'get_caste_list']);
    Route::get('/Org/MasterSetup/GetReligion',[ProcessMaster::class,'get_religion_list']);
    Route::get('/Org/MasterSetup/GetBlockWiseVilleage/{org_id}/{blk_id}',[ProcessMaster::class,'get_blkwise_village']);
    Route::get('/Org/MasterSetup/GetDistWisePolice/{org_id}/{dist_id}',[ProcessMaster::class,'get_distwise_police']);
    Route::get('/Org/MasterSetup/GetDistWisePost/{org_id}/{dist_id}',[ProcessMaster::class,'get_distwise_post']);
    Route::post('/Org/MasterSetup/ProcessShareProduct',[ProcessMaster::class,'process_share_product']);
    Route::get('/Org/MasterSetup/ShareProductDetails/{org_id}/{prod_id}',[ProcessMaster::class,'get_share_details']);
    Route::get('/Org/MasterSetup/GetAgentPayout',[ProcessMaster::class,'get_agent_paytype']);
    Route::post('/Org/MasterSetup/AddDepositAgent',[ProcessMaster::class,'process_deposit_agent']);
    Route::get('/Org/MasterSetup/GetDepositAgent/{org_id}',[ProcessMaster::class,'get_deposit_agent']);
    Route::post('/Org/MasterSetup/DepositIntSlab',[ProcessMaster::class,'deposit_Intt_Slab']);

    // end master route

    // membership route start here
    
    Route::post('/Org/MemberShip/AddProfile',[ProcessMembership::class,'process_member_profile_add']);
    Route::get('/Org/MemberShip/GetMemberData/{org_id}/{member_no}',[ProcessMembership::class,'get_member_data']);
    Route::get('/Org/MemberShip/MemberSearch/{org_id}/{mem_name}',[ProcessMembership::class,'process_member_search']);
    Route::get('/Org/MemberShip/GetProductData/{org_id}/{type_id}',[ProcessMembership::class,'get_shprod_details']);
    Route::post('/Org/MemberShip/AddMembership',[ProcessMembership::class,'process_membership']);
    Route::post('/Org/MemberShip/GetShereData',[ProcessMembership::class,'get_share_details']);
    Route::post('/Org/MemberShip/IssueShare',[ProcessMembership::class,'process_share_issue']);
    Route::post('/Org/MemberShip/RefundShare',[ProcessMembership::class,'process_refund_share']);
    Route::post('/Org/MemberShip/WithdrwanMembership',[ProcessMembership::class,'process_withdrw_membership']);

    Route::post('/Org/MemberShip/GetShareLedger',[ProcessMembership::class,'process_share_ledger']);
    Route::post('/Org/MemberShip/GetMemberInfo',[ProcessMembership::class,'process_member_info']);

    // membership route end here

    // Deposit Route Start Here

    Route::get('/Org/ProcessDeposit/GetProdType/{org_id}',[ProcessDeposit::class,'get_org_prodtype']);
    Route::get('/Org/ProcessDeposit/GetProduct/{org_id}/{type_id}',[ProcessDeposit::class,'get_org_deposit_product']);
    Route::get('/Org/ProcessDeposit/GetDuration',[ProcessDeposit::class,'get_deposit_duration']);
    Route::get('/Org/ProcessDeposit/GetOperationMode',[ProcessDeposit::class,'get_operation_mode']);
    Route::get('/Org/ProcessDeposit/GetMaturInstruction',[ProcessDeposit::class,'get_mature_instruction']);
    Route::get('/Org/ProcessDeposit/GetIntPayoutMode',[ProcessDeposit::class,'get_interest_payout']);
    Route::post('/Org/ProcessDeposit/CheckAmount',[ProcessDeposit::class,'check_dep_amount']);
    Route::post('/Org/ProcessDeposit/CheckDuration',[ProcessDeposit::class,'check_dep_duration']);
    Route::post('/Org/ProcessDeposit/GetInttRate',[ProcessDeposit::class,'get_dep_intt_rate']);
    Route::post('/Org/ProcessDeposit/GetMaturityAmt',[ProcessDeposit::class,'get_deposit_mature']);
    Route::post('/Org/ProcessDeposit/GetPayoutAmount',[ProcessDeposit::class,'get_dep_payout_interest']);
    Route::get('/Org/ProcessDeposit/GetEcsAccount/{org_id}/{member_id}',[ProcessDeposit::class,'get_ecs_account']);
    Route::post('/Org/ProcessDeposit/AddDepositAccount',[ProcessDeposit::class,'process_deposit_account']);
    Route::post('/Org/ProcessDeposit/GetAccountDetails',[ProcessDeposit::class,'get_dep_account_data']);
    Route::get('/Org/ProcessDeposit/SearchAccount/{org_id}/{type}/{value}',[ProcessDeposit::class,'search_account']);
    Route::post('/Org/ProcessDeposit/PostDeposit',[ProcessDeposit::class,'process_deposit_post']);
    Route::post('/Org/ProcessDeposit/PostWithdrwan',[ProcessDeposit::class,'process_deposit_withdrwan']);
    Route::get('/Org/ProcessDeposit/GetSpecimen/{org_id}/{acct_id}',[ProcessDeposit::class,'get_specimen']);
    Route::post('/Org/ProcessDeposit/GetCloseData',[ProcessDeposit::class,'get_close_acct_Data']);
    Route::post('/Org/ProcessDeposit/GetMatureData',[ProcessDeposit::class,'get_mature_data']);
    Route::post('/Org/ProcessDeposit/PostCloseAccount',[ProcessDeposit::class,'process_close_account']);
    Route::post('/Org/ProcessDeposit/CalMatureInterest',[ProcessDeposit::class,'calculate_mature_interest']);
    Route::post('/Org/ProcessDeposit/CalBonusInterest',[ProcessDeposit::class,'calculate_bonus_intt']);
    Route::post('/Org/ProcessDeposit/MatureAccount',[ProcessDeposit::class,'process_mature_account']);
    Route::post('/Org/ProcessDeposit/ProcessRenewal',[ProcessDeposit::class,'process_account_renewal']);
    Route::post('/Org/ProcessDeposit/GetPayoutAccount',[ProcessDeposit::class,'get_payout_account']);
    Route::post('/Org/ProcessDeposit/ProcessBlkIntPayout',[ProcessDeposit::class,'process_blkintt_payout']);
    Route::post('/Org/ProcessDeposit/ProcessSingleInttPayout',[ProcessDeposit::class,'process_singintt_payout']);
    Route::post('/Org/ProcessDeposit/GetBalance',[ProcessDeposit::class,'get_account_balance']);

    Route::post('/Org/ProcessDeposit/GetLedger',[ProcessDeposit::class,'process_ledger']);

    // Deposit Route End Here

    // Loan Route Start Here

    Route::post('/Org/ProcessLoan/GetMemberInfo',[ProcessLoan::class,'get_member_info']);
    Route::get('/Org/ProcessLoan/GetProduct/{org_id}',[ProcessLoan::class,'get_loan_product']);
    Route::get('/Org/ProcessLoan/GetDuration/{prod_id}/{org_id}',[ProcessLoan::class,'get_prod_duration']);
    Route::get('/Org/ProcessLoan/GetRepayMode/{prod_id}/{org_id}',[ProcessLoan::class,'get_prod_repay_mode']);
    Route::post('/Org/ProcessLoan/CheckAmount',[ProcessLoan::class,'check_appl_amount']);
    Route::post('/Org/ProcessLoan/CheckDuration',[ProcessLoan::class,'check_duration']);
    Route::get('/Org/ProcessLoan/GetInterestRate/{org_id}/{prod_id}',[ProcessLoan::class,'get_interest_rate']);
    Route::post('/Org/ProcessLoan/GetEmi',[ProcessLoan::class,'get_emi_amount']);
    Route::post('/Org/ProcessLoan/EligibleLoan',[ProcessLoan::class,'chek_loan_eligible']);
    Route::post('/Org/ProcessLoan/AddApplication',[ProcessLoan::class,'process_loan_application']);
    Route::get('/Org/ProcessLoan/GetDisbList/{org_id}/{branch_id}',[ProcessLoan::class,'get_pending_disb_list']);
    Route::post('/Org/ProcessLoan/SearchAccount',[ProcessLoan::class,'search_account']);
    Route::get('/Org/ProcessLoan/GenerateSchdule/{org_id}/{acct_id}/{mode}',[ProcessLoan::class,'generate_schdule']);
    Route::post('/Org/ProcessLoan/DIsbShareDepBalance',[ProcessLoan::class,'get_dep_share_balance']);
    Route::post('/Org/ProcessLoan/GetDisbNeedAmount',[ProcessLoan::class,'get_disb_cal_amount']);
    Route::post('/Org/ProcessLoan/DisburseLoan',[ProcessLoan::class,'process_loan_disburse']);
    Route::post('/Org/ProcessLoan/GetRepayData',[ProcessLoan::class,'get_repay_data']);
    Route::post('/Org/ProcessLoan/PostRepay',[ProcessLoan::class,'process_loan_repayment']);

    Route::post('/Org/ProcessLoan/GetLedger',[ProcessLoan::class,'process_ledger']);

    // Loan Route End Here

    // Bank Route Start Here

    Route::get('/Org/ProcessBankAccount/GetAccountType',[ProcessBankAccount::class,'get_account_type']);
    Route::get('/Org/ProcessBankAccount/GetGl',[ProcessBankAccount::class,'get_bank_gl']);
    Route::post('/Org/ProcessBankAccount/AddAccount',[ProcessBankAccount::class,'process_bank_account']);
    Route::get('/Org/ProcessBankAccount/GetAccount/{org_id}',[ProcessBankAccount::class,'get_bank_account']);
    Route::post('/Org/ProcessBankAccount/GetBalance',[ProcessBankAccount::class,'get_bank_balance']);
    Route::post('/Org/ProcessBankAccount/Deposit',[ProcessBankAccount::class,'process_bank_deposit']);
    Route::post('/Org/ProcessBankAccount/Withdrwan',[ProcessBankAccount::class,'process_bank_withdrwan']);
    Route::post('/Org/ProcessBankAccount/Transfer',[ProcessBankAccount::class,'process_bank_transfer']);
    Route::post('/Org/ProcessBankAccount/CloseAccount',[ProcessBankAccount::class,'process_close_account']);

    Route::post('/Org/ProcessBankAccount/GetLedger',[ProcessBankAccount::class,'process_bank_ledger']);

    // Bank Route End Here

    // Voucher Entry Route Start Here

    Route::get('/Org/ProcessVoucherEntry/GetLedgerList',[ProcessVoucherEntry::class,'get_ledger_list']);
    Route::get('/Org/ProcessVoucherEntry/GetSubLedger/{org_id}/{gl_id}',[ProcessVoucherEntry::class,'get_sub_ledger_list']);
    Route::post('/Org/ProcessVoucherEntry/GetSubLedgerBalance',[ProcessVoucherEntry::class,'get_subledger_balance']);
    Route::post('/Org/ProcessVoucherEntry/PostVoucher',[ProcessVoucherEntry::class,'process_voucher_posting']);

    // Voucher Entry Route End Here

    // Investment Route Start Here

    Route::get('/Org/ProcessInvestment/GetAccountType',[ProcessInvestment::class,'get_account_type']);
    Route::get('/Org/ProcessInvestment/GetInvestType',[ProcessInvestment::class,'get_invest_type']);
    Route::get('/Org/ProcessInvestment/GetInterestType',[ProcessInvestment::class,'get_interest_type']);
    Route::post('/Org/ProcessInvestment/CalMatureValue',[ProcessInvestment::class,'calculate_mature_val']);
    Route::get('/Org/ProcessInvestment/GetLedger/{mode}',[ProcessInvestment::class,'get_invest_ledger']);
    Route::post('/Org/ProcessInvestment/AddAccount',[ProcessInvestment::class,'process_invest_account']);
    Route::get('/Org/ProcessInvestment/AccountList/{org_id}/{type}',[ProcessInvestment::class,'get_account_list']);
    Route::post('/Org/ProcessInvestment/InterestPost',[ProcessInvestment::class,'process_intt_posting']);
    Route::post('/Org/ProcessInvestment/InstallmentPost',[ProcessInvestment::class,'processs_installment_post']);
    Route::get('/Org/ProcessInvestment/GetInvestInfo/{org_id}/{invest_id}',[ProcessInvestment::class,'get_invest_info']);
    Route::post('/Org/ProcessInvestment/ProcessRenewal',[ProcessInvestment::class,'process_renewal']);
    Route::post('/Org/ProcessInvestment/CalInterest',[ProcessInvestment::class,'calculate_interest']);
    Route::post('/Org/ProcessInvestment/CloseAccount',[ProcessInvestment::class,'process_close_account']);

    Route::post('/Org/ProcessInvestment/GetLedger',[ProcessInvestment::class,'process_ledger']);

    // Investment Route End Here

    // Borrowings Route Start Here

    Route::get('/Org/ProcessBorrowings/GetProdType',[ProcessBorrowings::class,'get_prod_type']);
    Route::get('/Org/ProcessBorrowings/GetRepayMode',[ProcessBorrowings::class,'get_repay_mode']);
    Route::get('/Org/ProcessBorrowings/GetLedger/{type}',[ProcessBorrowings::class,'get_ledger']);
    Route::post('/Org/ProcessBorrowings/AddAccount',[ProcessBorrowings::class,'process_account']);
    Route::get('/Org/ProcessBorrowings/GetAccountList/{org_id}',[ProcessBorrowings::class,'get_account_list']);
    Route::post('/Org/ProcessBorrowings/GetAcctInfo',[ProcessBorrowings::class,'get_account_info']);
    Route::post('/Org/ProcessBorrowings/Disburse',[ProcessBorrowings::class,'process_disburse']);
    Route::post('/Org/ProcessBorrowings/Repayment',[ProcessBorrowings::class,'process_repayment']);

    Route::post('/Org/ProcessBorrowings/GetLedger',[ProcessBorrowings::class,'process_ledger']);

    // Borrowings Route End Here

    // Openoing Entry Route Start Here

    Route::post('/Org/ProcessOpening/Membership',[ProcessOpening::class,'process_opn_membership']);
    Route::post('/Org/ProcessOpening/DepositAccount',[ProcessOpening::class,'process_deposit_account']);
    Route::post('/Org/ProcessOpening/Investment',[ProcessOpening::class,'process_investment']);
    Route::post('/Org/ProcessOpening/BankAccount',[ProcessOpening::class,'process_bank_acct']);
    Route::post('/Org/ProcessOpening/BankBorrowings',[ProcessOpening::class,'process_borrowings']);
    Route::post('/Org/ProcessOpening/LoanAccount',[ProcessOpening::class,'process_member_loan']);
    Route::get('/Org/ProcessOpening/GetAcctMainHead',[ProcessOpening::class,'get_acct_main_head']);
    Route::get('/Org/ProcessOpening/GetSubHead/{head_id}',[ProcessOpening::class,'get_acct_sub_head']);
    Route::get('/Org/ProcessOpening/GetLedger/{sub_id}',[ProcessOpening::class,'get_acct_ledger']);
    Route::post('/Org/ProcessOpening/AddAcctBalance',[ProcessOpening::class,'process_acct_opn_balance']);
    Route::get('/Org/ProcessOpening/GetBranchList/{org_id}/{branch_id}',[ProcessOpening::class,'get_org_branch_list']);

    // Opening Entry Route End Here

    // Financial Report Route Start Here
    
    Route::post('/Org/FinancialReporting/Glbalancing',[ProcessFinancialReport::class,'process_gl_balancing']);
    Route::post('/Org/FinancialReporting/GetDayBook',[ProcessFinancialReport::class,'process_daybook']);
    Route::post('/Org/FinancialReporting/GetCashBalance',[ProcessFinancialReport::class,'process_cash_balance']);
    Route::post('/Org/FinancialReporting/GetCashAcct',[ProcessFinancialReport::class,'process_cash_acct']);
    Route::post('/Org/FinancialReporting/GetCashBook',[ProcessFinancialReport::class,'process_cash_book']);
    Route::get('/Org/FinancialReporting/GetAcctLedger',[ProcessFinancialReport::class,'get_acct_ledger']);
    Route::post('/Org/FinancialReporting/GenereateAcctLedger',[ProcessFinancialReport::class,'genereate_ledger']);
    Route::post('/Org/FinancialReporting/GetVoucherList',[ProcessFinancialReport::class,'get_voucher_list']);
    Route::get('/Org/FinancialReporting/GetVoucherDetails/{org_id}/{txn_id}',[ProcessFinancialReport::class,'get_voucher_details']);

    // Financial Report Route End Here

    // Modulewise Report Route Start Here

    // share
    Route::get('/Org/ProcessModuleReport/Membership/GetReportType',[ProcessModuleReport::class,'get_mem_report_type']);
    Route::post('/Org/ProcessModuleReport/Membership/MemberRegister',[ProcessModuleReport::class,'process_member_register']);
    Route::post('/Org/ProcessModuleReport/Membership/TransRegister',[ProcessModuleReport::class,'process_member_trans_register']);
    Route::post('/Org/ProcessModuleReport/Membership/WithdrwanRegister',[ProcessModuleReport::class,'process_member_withdrw_register']);
    Route::post('/Org/ProcessModuleReport/Membership/GetDetailedList',[ProcessModuleReport::class,'process_member_detailedlist']);
    Route::post('/Org/ProcessModuleReport/Membership/GetDividendList',[ProcessModuleReport::class,'process_member_dividendlist']);

    // end share

    // deposit start
    
    Route::get('/Org/ProcessModuleReport/Deposit/GetReportType',[ProcessModuleReport::class,'get_dep_report_type']);
    Route::get('/Org/ProcessModuleReport/Deposit/GetProduct/{org_id}',[ProcessModuleReport::class,'get_deposit_product']);
    Route::post('/Org/ProcessModuleReport/Deposit/OpeningRegister',[ProcessModuleReport::class,'process_dep_open_register']);
    Route::post('/Org/ProcessModuleReport/Deposit/TransRegister',[ProcessModuleReport::class,'process_dep_trans_register']);
    Route::post('/Org/ProcessModuleReport/Deposit/CloseRegister',[ProcessModuleReport::class,'process_dep_close_register']);
    Route::post('/Org/ProcessModuleReport/Deposit/GetDetailedlist',[ProcessModuleReport::class,'process_dep_detailedlist']);
    Route::post('/Org/ProcessModuleReport/Deposit/GetInterestList',[ProcessModuleReport::class,'process_interest_list']);
    // deposit end

    // Loan Start
    Route::get('/Org/ProcessModuleReport/Loan/GetReportType',[ProcessModuleReport::class,'get_ln_report_type']);
    Route::get('/Org/ProcessModuleReport/Loan/GetProduct/{org_id}',[ProcessModuleReport::class,'get_loan_product']);
    Route::post('/Org/ProcessModuleReport/Loan/GetDisburseRegister',[ProcessModuleReport::class,'process_ln_disb_register']);
    Route::post('/Org/ProcessModuleReport/Loan/GetRepayRegister',[ProcessModuleReport::class,'process_ln_repay_register']);
    Route::post('/Org/ProcessModuleReport/Loan/GetDetailedList',[ProcessModuleReport::class,'process_loan_detailedlist']);

    // Loan End Here

    // Bank Start Here
    
    Route::get('/Org/ProcessModuleReport/Bank/GetReportType',[ProcessModuleReport::class,'get_bank_report_type']);
    Route::post('/Org/ProcessModuleReport/Bank/GetDetailedList',[ProcessModuleReport::class,'process_bank_detailedlist']);
    
    // Bank End Here

    // Investment Start Here

    Route::get('/Org/ProcessModuleReport/Investment/GetReportType',[ProcessModuleReport::class,'get_invest_report_type']);
    
    // Investment End Here
    // Modulewise Route End Here
});

// user route end here