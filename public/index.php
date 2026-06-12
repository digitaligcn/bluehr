<?php
require dirname(__DIR__) . '/app/Core/helpers.php';
spl_autoload_register(function($class){
    $prefix='BlueHR\\';
    if (str_starts_with($class,$prefix)) {
        $relative = str_replace('\\','/',substr($class,strlen($prefix))) . '.php';
        foreach ([dirname(__DIR__).'/app/'.$relative, dirname(__DIR__).'/'.$relative] as $path) {
            if (is_file($path)) { require $path; return; }
        }
    }
});
if (!is_file(dirname(__DIR__) . '/.env') && basename($_SERVER['SCRIPT_NAME']) !== 'install.php') { header('Location: ../install.php'); exit; }
session_name(config('app.session_name')); session_start();
use BlueHR\Core\Router;
use BlueHR\Controllers\AuthController;
use BlueHR\Controllers\DashboardController;
use BlueHR\Controllers\UserController;
use BlueHR\Controllers\EmployeeController;
use BlueHR\Controllers\OrganizationController;
use BlueHR\Controllers\AttendanceController;
use BlueHR\Controllers\LeaveController;
use BlueHR\Controllers\OvertimeController;
use BlueHR\Controllers\PayrollController;
use BlueHR\Controllers\AccurateController;
use BlueHR\Controllers\OperationalController;
use BlueHR\Controllers\ModuleController;
use BlueHR\Controllers\PerformanceController;

$public = ['/login','/authenticate','/accurate/callback'];
$path = request_path();
if (!in_array($path, $public, true) && empty($_SESSION['user'])) redirect('/login');
$r=new Router();
$r->get('/login',[AuthController::class,'login']); $r->post('/authenticate',[AuthController::class,'authenticate']); $r->get('/logout',[AuthController::class,'logout']);
$r->get('/',[DashboardController::class,'index']); $r->get('/dashboard',[DashboardController::class,'index']);
$r->get('/users',[UserController::class,'index']); $r->get('/users/create',[UserController::class,'create']); $r->post('/users/store',[UserController::class,'store']);
$r->get('/employees',[EmployeeController::class,'index']); $r->get('/employees/create',[EmployeeController::class,'create']); $r->post('/employees/store',[EmployeeController::class,'store']); $r->get('/employees/settings',[ModuleController::class,'employeeSettings']);
$r->get('/organization',[OrganizationController::class,'index']); $r->post('/organization/branch',[OrganizationController::class,'saveBranch']); $r->post('/organization/department',[OrganizationController::class,'saveDepartment']); $r->post('/organization/position',[OrganizationController::class,'savePosition']); $r->get('/organization/settings',[ModuleController::class,'organizationSettings']);
$r->get('/attendance',[AttendanceController::class,'index']); $r->post('/attendance/store',[AttendanceController::class,'store']); $r->get('/attendance/settings',[ModuleController::class,'attendanceSettings']);
$r->get('/leave',[LeaveController::class,'index']); $r->post('/leave/store',[LeaveController::class,'store']); $r->post('/leave/approve',[LeaveController::class,'approve']); $r->get('/leave/settings',[ModuleController::class,'leaveSettings']);
$r->get('/overtime',[OvertimeController::class,'index']); $r->post('/overtime/store',[OvertimeController::class,'store']); $r->get('/overtime/settings',[ModuleController::class,'overtimeSettings']);
$r->get('/payroll',[PayrollController::class,'index']); $r->post('/payroll/period',[PayrollController::class,'createPeriod']); $r->post('/payroll/calculate',[PayrollController::class,'calculate']); $r->post('/payroll/approve',[PayrollController::class,'approve']); $r->post('/payroll/journal',[PayrollController::class,'journal']); $r->get('/payroll/salary',[PayrollController::class,'salary']); $r->post('/payroll/component',[PayrollController::class,'saveComponent']); $r->post('/payroll/assign-salary',[PayrollController::class,'assignSalary']); $r->get('/payroll/settings',[ModuleController::class,'payrollSettings']);
$r->get('/accurate',[AccurateController::class,'index']); $r->post('/accurate/config',[AccurateController::class,'saveConfig']); $r->get('/accurate/connect',[AccurateController::class,'connect']); $r->get('/accurate/callback',[AccurateController::class,'callback']); $r->post('/accurate/mapping',[AccurateController::class,'saveMapping']); $r->post('/accurate/post-payroll-journal',[AccurateController::class,'postPayrollJournal']); $r->get('/accurate/settings',[ModuleController::class,'accurateSettings']);
$r->get('/recruitment',[OperationalController::class,'recruitment']); $r->post('/recruitment/store',[OperationalController::class,'storeCandidate']); $r->get('/recruitment/settings',[ModuleController::class,'recruitmentSettings']);
$r->get('/onboarding',[OperationalController::class,'onboarding']); $r->post('/onboarding/store',[OperationalController::class,'storeOnboardingTask']); $r->get('/onboarding/settings',[ModuleController::class,'onboardingSettings']);
$r->get('/reimbursement',[OperationalController::class,'reimbursement']); $r->post('/reimbursement/store',[OperationalController::class,'storeReimbursement']); $r->get('/reimbursement/settings',[ModuleController::class,'reimbursementSettings']);
$r->get('/loans',[OperationalController::class,'loans']); $r->post('/loans/store',[OperationalController::class,'storeLoan']); $r->get('/loans/settings',[ModuleController::class,'loanSettings']);
$r->get('/training',[OperationalController::class,'training']); $r->post('/training/store',[OperationalController::class,'storeTraining']); $r->get('/training/settings',[ModuleController::class,'trainingSettings']);
$r->get('/performance',[PerformanceController::class,'dashboard']); $r->get('/performance/periods',[PerformanceController::class,'periods']); $r->post('/performance/periods/store',[PerformanceController::class,'storePeriod']); $r->get('/performance/kpi',[PerformanceController::class,'kpi']); $r->post('/performance/kpi/store',[PerformanceController::class,'storeKpi']); $r->get('/performance/goals',[PerformanceController::class,'goals']); $r->post('/performance/goals/store',[PerformanceController::class,'storeGoal']); $r->get('/performance/competencies',[PerformanceController::class,'competencies']); $r->post('/performance/competencies/store',[PerformanceController::class,'storeCompetency']); $r->get('/performance/reviews',[PerformanceController::class,'reviews']); $r->post('/performance/reviews/store',[PerformanceController::class,'storeReview']); $r->get('/performance/calibration',[PerformanceController::class,'calibration']); $r->post('/performance/calibration/store',[PerformanceController::class,'storeCalibration']); $r->get('/performance/improvement',[PerformanceController::class,'improvement']); $r->post('/performance/improvement/store',[PerformanceController::class,'storeImprovement']); $r->get('/performance/bonus',[PerformanceController::class,'bonus']); $r->post('/performance/bonus/store',[PerformanceController::class,'storeBonusRule']); $r->get('/performance/settings',[PerformanceController::class,'settings']);
$r->get('/approval',[ModuleController::class,'approval']); $r->post('/approval/type/store',[ModuleController::class,'storeApprovalType']); $r->get('/approval/settings',[ModuleController::class,'approvalSettings']);
$r->get('/facilities',[ModuleController::class,'facilities']); $r->post('/facilities/store',[ModuleController::class,'storeFacility']); $r->post('/facilities/rule/store',[ModuleController::class,'storeFacilityRule']); $r->get('/facilities/settings',[ModuleController::class,'facilitySettings']);
$r->get('/documents',[ModuleController::class,'documents']); $r->post('/documents/upload',[ModuleController::class,'uploadDocument']); $r->get('/documents/settings',[ModuleController::class,'documentSettings']);
$r->get('/ai',[ModuleController::class,'ai']); $r->post('/ai/analyze',[ModuleController::class,'aiAnalyze']); $r->get('/ai/settings',[ModuleController::class,'aiSettings']);
$r->get('/whatsapp',[ModuleController::class,'whatsapp']); $r->post('/whatsapp/template/store',[ModuleController::class,'storeWhatsappTemplate']); $r->get('/whatsapp/settings',[ModuleController::class,'whatsappSettings']);
$r->get('/settings/google-drive',[ModuleController::class,'googleDrive']); $r->post('/settings/google-drive/save',[ModuleController::class,'saveGoogleDrive']);
$r->get('/reports',[OperationalController::class,'reports']); $r->get('/settings',[ModuleController::class,'settings']); $r->post('/settings/save',[ModuleController::class,'saveSettings']); $r->post('/settings/record/store',[ModuleController::class,'storeSettingRecord']); $r->post('/settings/record/delete',[ModuleController::class,'deleteSettingRecord']); $r->get('/settings/record/edit',[ModuleController::class,'editSettingRecord']); $r->post('/settings/record/update',[ModuleController::class,'updateSettingRecord']);
$r->dispatch();
