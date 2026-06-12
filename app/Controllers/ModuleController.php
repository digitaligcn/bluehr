<?php
namespace BlueHR\Controllers;

use BlueHR\Core\Controller;
use BlueHR\Core\Database;
use BlueHR\Services\Security\Audit;

class ModuleController extends Controller
{
    private function safeAll(string $sql, array $params=[]): array { try { return Database::all($sql,$params); } catch (\Throwable $e) { return []; } }
    private function safeOne(string $sql, array $params=[]): array { try { return Database::one($sql,$params) ?: []; } catch (\Throwable $e) { return []; } }

    public function settings(): void
    {
        $settings=$this->safeAll('SELECT * FROM app_settings ORDER BY setting_group, setting_key');
        $this->view('settings/index',[
            'title'=>'Global & Module Settings',
            'settings'=>$settings,
            'roles'=>$this->safeAll('SELECT * FROM roles ORDER BY id'),
            'perms'=>$this->safeAll('SELECT * FROM permissions ORDER BY module,code')
        ]);
    }

    public function saveSettings(): void
    {
        $group = trim($_POST['_group'] ?? 'general') ?: 'general';
        foreach (($_POST['settings']??[]) as $k=>$v) {
            if (is_array($v)) $v = json_encode($v);
            save_setting((string)$k,(string)$v,$group);
        }
        Audit::log('save_settings','app_settings',null,['group'=>$group,'keys'=>array_keys($_POST['settings']??[])]);
        flash('success','Settings saved.');
        redirect($_POST['_redirect'] ?? '/settings');
    }

    private function settingsPage(string $moduleKey, string $moduleTitle, array $sections, array $records=[]): void
    {
        $tables = [];
        foreach ($records as $recordKey => $record) {
            $tables[$recordKey] = $this->safeAll($record['query'] ?? ('SELECT * FROM '.$record['table'].' ORDER BY id DESC LIMIT 100'));
        }
        $this->view('module/settings', [
            'title'=>$moduleTitle.' Settings',
            'moduleKey'=>$moduleKey,
            'module'=>$moduleTitle,
            'sections'=>$sections,
            'records'=>$records,
            'tables'=>$tables,
            'roles'=>$this->safeAll('SELECT * FROM roles ORDER BY name'),
            'users'=>$this->safeAll('SELECT id,name,email FROM users ORDER BY name'),
            'employees'=>$this->safeAll('SELECT id,employee_no,full_name FROM employees ORDER BY full_name'),
            'jobLevels'=>$this->safeAll('SELECT * FROM job_levels ORDER BY rank_no,name'),
            'employmentTypes'=>$this->safeAll('SELECT * FROM employment_types ORDER BY name'),
            'branches'=>$this->safeAll('SELECT * FROM branches ORDER BY name'),
            'departments'=>$this->safeAll('SELECT * FROM departments ORDER BY name'),
            'positions'=>$this->safeAll('SELECT * FROM positions ORDER BY name'),
            'facilities'=>$this->safeAll('SELECT * FROM facilities ORDER BY name'),
            'leaveTypes'=>$this->safeAll('SELECT * FROM leave_types ORDER BY name'),
            'approvalTypes'=>$this->safeAll('SELECT * FROM approval_types ORDER BY name'),
        ]);
    }

    private function yesNo(): array { return ['0'=>'No','1'=>'Yes']; }
    private function activeInactive(): array { return ['active'=>'Active','inactive'=>'Inactive']; }

    private function generalFields(string $prefix): array
    {
        return [[
            'title'=>'Operational Parameters',
            'description'=>'Core behavior for this module in daily operation.',
            'fields'=>[
                ['key'=>$prefix.'_enabled','label'=>'Enable module','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>$prefix.'_number_prefix','label'=>'Document / record prefix','type'=>'text','default'=>strtoupper(substr($prefix,0,3)).'-'],
                ['key'=>$prefix.'_default_status','label'=>'Default status','type'=>'text','default'=>'draft'],
                ['key'=>$prefix.'_email_notification','label'=>'Email notification','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>$prefix.'_require_approval','label'=>'Require approval','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
            ]
        ]];
    }

    public function employeeSettings(): void
    {
        $sections = [[
            'title'=>'Employee Operational Parameters',
            'description'=>'Control employee numbering, profile visibility, and required HR profile data.',
            'fields'=>[
                ['key'=>'employee_number_prefix','label'=>'Employee number prefix','type'=>'text','default'=>'EMP-'],
                ['key'=>'employee_number_padding','label'=>'Number padding','type'=>'number','default'=>'4'],
                ['key'=>'employee_photo_required','label'=>'Employee photo required','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'employee_private_contact_visibility','label'=>'Private contact visibility','type'=>'select','options'=>['hr'=>'HR only','manager'=>'HR + Manager','self'=>'Employee + HR'],'default'=>'hr'],
                ['key'=>'employee_resume_enabled','label'=>'Resume/CV tab enabled','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'employee_work_permit_alert_days','label'=>'Work permit expiry alert days','type'=>'number','default'=>'30'],
            ]
        ]];
        $records = [
            'skill_type'=>['title'=>'Skill Types','table'=>'skill_types','fields'=>[['name'=>'name','label'=>'Skill Type','type'=>'text','required'=>true]]],
            'tag'=>['title'=>'Employee Tags','table'=>'tags','fields'=>[['name'=>'name','label'=>'Tag Name','type'=>'text','required'=>true],['name'=>'color','label'=>'Color','type'=>'text','default'=>'#38bdf8']]],
            'departure_reason'=>['title'=>'Departure Reasons','table'=>'departure_reasons','fields'=>[['name'=>'name','label'=>'Reason','type'=>'text','required'=>true],['name'=>'description','label'=>'Description','type'=>'textarea']]],
        ];
        $this->settingsPage('employee','Employee',$sections,$records);
    }

    public function organizationSettings(): void
    {
        $sections = [[
            'title'=>'Organization Parameters',
            'description'=>'Configure organization structure, job hierarchy, and work locations.',
            'fields'=>[
                ['key'=>'organization_chart_enabled','label'=>'Organization chart enabled','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'organization_cost_center_required','label'=>'Cost center required for departments','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'organization_branch_required','label'=>'Branch required on employee profile','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'organization_position_approval_required','label'=>'Job movement requires approval','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
            ]
        ]];
        $records = [
            'branch'=>['title'=>'Branches','table'=>'branches','query'=>'SELECT code,name,status,cost_center,accurate_branch_id FROM branches ORDER BY name','fields'=>[['name'=>'code','label'=>'Code','type'=>'text','required'=>true],['name'=>'name','label'=>'Branch Name','type'=>'text','required'=>true],['name'=>'cost_center','label'=>'Cost Center','type'=>'text'],['name'=>'accurate_branch_id','label'=>'Accurate Branch ID','type'=>'text'],['name'=>'status','label'=>'Status','type'=>'select','options'=>$this->activeInactive()]]],
            'department'=>['title'=>'Departments','table'=>'departments','query'=>'SELECT code,name,status,cost_center,accurate_department_id FROM departments ORDER BY name','fields'=>[['name'=>'code','label'=>'Code','type'=>'text','required'=>true],['name'=>'name','label'=>'Department Name','type'=>'text','required'=>true],['name'=>'cost_center','label'=>'Cost Center','type'=>'text'],['name'=>'accurate_department_id','label'=>'Accurate Department ID','type'=>'text'],['name'=>'status','label'=>'Status','type'=>'select','options'=>$this->activeInactive()]]],
            'position'=>['title'=>'Positions','table'=>'positions','query'=>'SELECT name,grade,status FROM positions ORDER BY name','fields'=>[['name'=>'name','label'=>'Position Name','type'=>'text','required'=>true],['name'=>'grade','label'=>'Grade','type'=>'text'],['name'=>'status','label'=>'Status','type'=>'select','options'=>$this->activeInactive()]]],
            'job_level'=>['title'=>'Job Levels','table'=>'job_levels','query'=>'SELECT code,name,rank_no FROM job_levels ORDER BY rank_no,name','fields'=>[['name'=>'code','label'=>'Code','type'=>'text','required'=>true],['name'=>'name','label'=>'Level Name','type'=>'text','required'=>true],['name'=>'rank_no','label'=>'Rank No','type'=>'number','default'=>'1']]],
            'employment_type'=>['title'=>'Employment Types','table'=>'employment_types','query'=>'SELECT name,is_permanent FROM employment_types ORDER BY name','fields'=>[['name'=>'name','label'=>'Employment Type','type'=>'text','required'=>true],['name'=>'is_permanent','label'=>'Permanent?','type'=>'select','options'=>$this->yesNo()]]],
            'work_location'=>['title'=>'Work Locations','table'=>'work_locations','query'=>'SELECT name,type,address FROM work_locations ORDER BY name','fields'=>[['name'=>'name','label'=>'Location Name','type'=>'text','required'=>true],['name'=>'type','label'=>'Type','type'=>'select','options'=>['office'=>'Office','home'=>'Home','remote'=>'Remote','client'=>'Client Site']],['name'=>'address','label'=>'Address','type'=>'textarea']]],
            'working_schedule'=>['title'=>'Working Schedules','table'=>'working_schedules','query'=>'SELECT name,flexible_enabled,auto_attendance_enabled,min_hours,timezone FROM working_schedules ORDER BY name','fields'=>[['name'=>'name','label'=>'Schedule Name','type'=>'text','required'=>true],['name'=>'flexible_enabled','label'=>'Flexible','type'=>'select','options'=>$this->yesNo()],['name'=>'auto_attendance_enabled','label'=>'Auto Attendance','type'=>'select','options'=>$this->yesNo()],['name'=>'min_hours','label'=>'Minimum Hours','type'=>'number','default'=>'8'],['name'=>'timezone','label'=>'Timezone','type'=>'text','default'=>'Asia/Jakarta']]],
        ];
        $this->settingsPage('organization','Organization',$sections,$records);
    }

    public function attendanceSettings(): void
    {
        $sections = [[
            'title'=>'Attendance Operational Parameters',
            'description'=>'Configure flexible working hour, auto attendance, tolerances, and correction rules.',
            'fields'=>[
                ['key'=>'attendance_flexible_hours','label'=>'Flexible working hour','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'attendance_auto_generation','label'=>'Auto attendance generation','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'attendance_exclude_leave','label'=>'Exclude approved leave from auto attendance','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'attendance_exclude_holiday','label'=>'Exclude public/company holiday','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'attendance_late_tolerance_minutes','label'=>'Late tolerance minutes','type'=>'number','default'=>'15'],
                ['key'=>'attendance_minimum_hours','label'=>'Minimum working hours','type'=>'number','default'=>'8'],
                ['key'=>'attendance_manual_correction_approval','label'=>'Manual correction requires approval','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
            ]
        ]];
        $records = [
            'shift_template'=>['title'=>'Shift Templates','table'=>'shift_templates','fields'=>[['name'=>'name','label'=>'Shift Name','type'=>'text','required'=>true],['name'=>'start_time','label'=>'Start Time','type'=>'time'],['name'=>'end_time','label'=>'End Time','type'=>'time'],['name'=>'break_minutes','label'=>'Break Minutes','type'=>'number','default'=>'60']]],
            'public_holiday'=>['title'=>'Holiday Calendar','table'=>'public_holidays','fields'=>[['name'=>'holiday_date','label'=>'Date','type'=>'date','required'=>true],['name'=>'name','label'=>'Holiday Name','type'=>'text','required'=>true],['name'=>'type','label'=>'Type','type'=>'select','options'=>['national'=>'National Holiday','collective'=>'Collective Leave','company'=>'Company Holiday']]]],
        ];
        $this->settingsPage('attendance','Attendance',$sections,$records);
    }

    public function leaveSettings(): void
    {
        $sections = [[
            'title'=>'Leave Operational Parameters',
            'description'=>'Configure allocation, carry forward, approval, and leave balance behavior.',
            'fields'=>[
                ['key'=>'leave_employee_self_service','label'=>'Employee self-service leave request','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'leave_negative_balance_allowed','label'=>'Allow negative leave balance','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'leave_carry_forward_enabled','label'=>'Carry forward enabled','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'leave_carry_forward_max_days','label'=>'Max carry forward days','type'=>'number','default'=>'6'],
                ['key'=>'leave_default_approval_flow','label'=>'Default approval flow','type'=>'select','options'=>['manager'=>'Direct Manager','hr'=>'HR','manager_hr'=>'Manager then HR','custom'=>'Custom Approval Engine'],'default'=>'manager_hr'],
                ['key'=>'leave_attachment_required_for_sick','label'=>'Attachment required for sick leave','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
            ]
        ]];
        $records = [
            'leave_type'=>['title'=>'Leave Types','table'=>'leave_types','fields'=>[['name'=>'name','label'=>'Leave Type','type'=>'text','required'=>true],['name'=>'annual_quota','label'=>'Annual Quota','type'=>'number','default'=>'12'],['name'=>'is_paid','label'=>'Paid Leave?','type'=>'select','options'=>$this->yesNo()]]],
            'leave_reason'=>['title'=>'Leave Reasons','table'=>'leave_reasons','fields'=>[['name'=>'leave_type_id','label'=>'Leave Type ID','type'=>'number'],['name'=>'name','label'=>'Reason','type'=>'text','required'=>true],['name'=>'is_active','label'=>'Active','type'=>'select','options'=>$this->yesNo()]]],
            'public_holiday'=>['title'=>'National / Collective Holiday','table'=>'public_holidays','fields'=>[['name'=>'holiday_date','label'=>'Date','type'=>'date','required'=>true],['name'=>'name','label'=>'Holiday Name','type'=>'text','required'=>true],['name'=>'type','label'=>'Type','type'=>'select','options'=>['national'=>'National Holiday','collective'=>'Collective Leave','company'=>'Company Holiday']]]],
        ];
        $this->settingsPage('leave','Leave',$sections,$records);
    }

    public function overtimeSettings(): void
    {
        $sections = [[
            'title'=>'Overtime Operational Parameters',
            'description'=>'Configure overtime calculation, approval, limits, and payroll linkage.',
            'fields'=>[
                ['key'=>'overtime_enabled','label'=>'Overtime enabled','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'overtime_requires_approval','label'=>'Requires approval','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'overtime_hourly_divisor','label'=>'Hourly divisor','type'=>'number','default'=>'173'],
                ['key'=>'overtime_max_hours_per_day','label'=>'Max hours per day','type'=>'number','default'=>'4'],
                ['key'=>'overtime_payroll_component','label'=>'Payroll component code','type'=>'text','default'=>'OVERTIME'],
                ['key'=>'overtime_email_notification','label'=>'Email notification','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
            ]
        ]];
        $this->settingsPage('overtime','Overtime',$sections);
    }

    public function payrollSettings(): void
    {
        $sections = [
            ['title'=>'Operational Parameters','description'=>'Rules that control payroll calculation and monthly operations.','fields'=>[
                ['key'=>'payroll_calculation_method','label'=>'Payroll calculation method','type'=>'select','options'=>['monthly'=>'Monthly','weekly'=>'Weekly','custom'=>'Custom'],'default'=>'monthly'],
                ['key'=>'payroll_prorate_salary','label'=>'Prorate salary','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'payroll_prorate_method','label'=>'Prorate method','type'=>'select','options'=>['calendar_days'=>'Calendar Days','working_days'=>'Working Days'],'default'=>'working_days'],
                ['key'=>'payroll_attendance_cutoff_day','label'=>'Attendance cutoff day','type'=>'number','default'=>'25'],
                ['key'=>'payroll_overtime_cutoff_day','label'=>'Overtime cutoff day','type'=>'number','default'=>'25'],
                ['key'=>'payroll_leave_cutoff_day','label'=>'Leave cutoff day','type'=>'number','default'=>'25'],
                ['key'=>'payroll_payment_day','label'=>'Payroll payment day','type'=>'number','default'=>'28'],
            ]],
            ['title'=>'BPJS/PPh21 Parameters','description'=>'Default tax and BPJS parameters used by payroll calculation.','fields'=>[
                ['key'=>'payroll_bpjs_kesehatan_employee_pct','label'=>'BPJS Kesehatan employee %','type'=>'number','default'=>'1'],
                ['key'=>'payroll_bpjs_kesehatan_company_pct','label'=>'BPJS Kesehatan company %','type'=>'number','default'=>'4'],
                ['key'=>'payroll_bpjs_jht_employee_pct','label'=>'BPJS JHT employee %','type'=>'number','default'=>'2'],
                ['key'=>'payroll_bpjs_jht_company_pct','label'=>'BPJS JHT company %','type'=>'number','default'=>'3.7'],
                ['key'=>'payroll_pph21_method','label'=>'PPh21 method','type'=>'select','options'=>['gross'=>'Gross','gross_up'=>'Gross Up','net'=>'Net'],'default'=>'gross'],
                ['key'=>'payroll_npwp_penalty_pct','label'=>'Non-NPWP adjustment %','type'=>'number','default'=>'20'],
            ]],
            ['title'=>'Approval Workflow','description'=>'Controls who must approve payroll before posting or payslip release.','fields'=>[
                ['key'=>'payroll_require_hr_approval','label'=>'Require HR approval','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'payroll_require_finance_approval','label'=>'Require Finance approval','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'payroll_require_director_approval','label'=>'Require Director approval','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'payroll_allow_recalculate_after_approval','label'=>'Allow recalculation after approval','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'payroll_allow_post_before_approval','label'=>'Allow posting to Accurate before approval','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
            ]],
            ['title'=>'Payslip Visibility','description'=>'Controls employee self-service payslip access.','fields'=>[
                ['key'=>'payslip_employee_can_view','label'=>'Employee can view payslip','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'payslip_visible_after','label'=>'Visible after','type'=>'select','options'=>['approved'=>'Payroll Approved','paid'=>'Payroll Paid','posted'=>'Posted to Accurate'],'default'=>'approved'],
                ['key'=>'payslip_allow_pdf_download','label'=>'Allow PDF download','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'payslip_show_tax_detail','label'=>'Show tax detail','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'payslip_show_bpjs_detail','label'=>'Show BPJS detail','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
            ]]
        ];
        $records = [
            'salary_component'=>['title'=>'Salary Components','table'=>'salary_components','fields'=>[['name'=>'code','label'=>'Code','type'=>'text','required'=>true],['name'=>'name','label'=>'Name','type'=>'text','required'=>true],['name'=>'type','label'=>'Type','type'=>'select','options'=>['earning'=>'Earning','deduction'=>'Deduction','company_contribution'=>'Company Contribution']],['name'=>'calculation_type','label'=>'Calculation Type','type'=>'select','options'=>['fixed'=>'Fixed','formula'=>'Formula','manual'=>'Manual']],['name'=>'default_amount','label'=>'Default Amount','type'=>'number','default'=>'0'],['name'=>'taxable','label'=>'Taxable','type'=>'select','options'=>$this->yesNo()]]],
            'payroll_period'=>['title'=>'Payroll Periods','table'=>'payroll_periods','fields'=>[['name'=>'period_month','label'=>'Month','type'=>'number','required'=>true],['name'=>'period_year','label'=>'Year','type'=>'number','required'=>true],['name'=>'start_date','label'=>'Start Date','type'=>'date','required'=>true],['name'=>'end_date','label'=>'End Date','type'=>'date','required'=>true],['name'=>'status','label'=>'Status','type'=>'select','options'=>['draft'=>'Draft','processing'=>'Processing','calculated'=>'Calculated','approved'=>'Approved','posted'=>'Posted','closed'=>'Closed']]]],
            'accurate_mapping'=>['title'=>'Accurate Journal Mapping','table'=>'accurate_account_mappings','query'=>'SELECT mapping_key,local_name,accurate_account_no,accurate_account_name FROM accurate_account_mappings ORDER BY mapping_key','fields'=>[['name'=>'mapping_key','label'=>'Mapping Key','type'=>'select','options'=>['salary_expense'=>'Salary Expense','allowance_expense'=>'Allowance Expense','overtime_expense'=>'Overtime Expense','bpjs_company_expense'=>'BPJS Company Expense','salary_payable'=>'Salary Payable','pph21_payable'=>'PPh21 Payable','bpjs_payable'=>'BPJS Payable','employee_loan'=>'Employee Loan','reimbursement_payable'=>'Reimbursement Payable']],['name'=>'local_name','label'=>'Local Name','type'=>'text','required'=>true],['name'=>'accurate_account_id','label'=>'Accurate Account ID','type'=>'text','required'=>true],['name'=>'accurate_account_no','label'=>'Accurate Account No','type'=>'text'],['name'=>'accurate_account_name','label'=>'Accurate Account Name','type'=>'text','required'=>true]]],
        ];
        $this->settingsPage('payroll','Payroll',$sections,$records);
    }

    public function accurateSettings(): void
    {
        $sections = [[
            'title'=>'Accurate Integration Parameters','description'=>'Configure OAuth, database, journal posting behavior, and API safety limits.','fields'=>[
                ['key'=>'accurate_client_id','label'=>'Client ID','type'=>'text'],
                ['key'=>'accurate_client_secret','label'=>'Client Secret','type'=>'password'],
                ['key'=>'accurate_redirect_uri','label'=>'Redirect URI','type'=>'text','default'=>url('/accurate/callback')],
                ['key'=>'accurate_scope','label'=>'Scope','type'=>'textarea','default'=>''],
                ['key'=>'accurate_rate_limit_per_second','label'=>'Rate limit per second','type'=>'number','default'=>'6'],
                ['key'=>'accurate_max_parallel','label'=>'Max parallel process','type'=>'number','default'=>'4'],
                ['key'=>'accurate_journal_endpoint','label'=>'Payroll journal endpoint','type'=>'text','default'=>'/api/journal-voucher/save.do'],
                ['key'=>'accurate_handle_308_redirect','label'=>'Handle 308 redirect automatically','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
            ]
        ]];
        $records = [
            'accurate_mapping'=>['title'=>'Accurate Account Mappings','table'=>'accurate_account_mappings','query'=>'SELECT mapping_key,local_name,accurate_account_no,accurate_account_name FROM accurate_account_mappings ORDER BY mapping_key','fields'=>[['name'=>'mapping_key','label'=>'Mapping Key','type'=>'text','required'=>true],['name'=>'local_name','label'=>'Local Name','type'=>'text','required'=>true],['name'=>'accurate_account_id','label'=>'Accurate Account ID','type'=>'text','required'=>true],['name'=>'accurate_account_no','label'=>'Accurate Account No','type'=>'text'],['name'=>'accurate_account_name','label'=>'Accurate Account Name','type'=>'text','required'=>true]]],
        ];
        $this->settingsPage('accurate','Accurate Integration',$sections,$records);
    }

    public function recruitmentSettings(): void
    {
        $sections = [[
            'title'=>'Recruitment Parameters','description'=>'Configure recruitment stages, document requirements, and approval rules.',
            'fields'=>[
                ['key'=>'recruitment_candidate_number_prefix','label'=>'Candidate number prefix','type'=>'text','default'=>'CAN-'],
                ['key'=>'recruitment_default_stage','label'=>'Default stage','type'=>'select','options'=>['applied'=>'Applied','screening'=>'Screening','interview'=>'Interview','offering'=>'Offering','hired'=>'Hired','rejected'=>'Rejected'],'default'=>'applied'],
                ['key'=>'recruitment_cv_required','label'=>'CV required','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'recruitment_offer_requires_approval','label'=>'Offer requires approval','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'recruitment_email_template','label'=>'Default email template','type'=>'textarea'],
            ]
        ]];
        $this->settingsPage('recruitment','Recruitment',$sections);
    }

    public function onboardingSettings(): void
    {
        $sections = [[
            'title'=>'Onboarding Parameters','description'=>'Configure default onboarding checklists, probation, and asset handover controls.',
            'fields'=>[
                ['key'=>'onboarding_default_due_days','label'=>'Default task due days','type'=>'number','default'=>'7'],
                ['key'=>'onboarding_contract_required','label'=>'Contract required','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'onboarding_asset_handover_required','label'=>'Asset handover required','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'onboarding_probation_review_days','label'=>'Probation review days','type'=>'number','default'=>'90'],
            ]
        ]];
        $this->settingsPage('onboarding','Onboarding',$sections);
    }

    public function reimbursementSettings(): void
    {
        $sections = [[
            'title'=>'Reimbursement Parameters','description'=>'Configure claim rules, receipt validation, approvals, and finance posting.',
            'fields'=>[
                ['key'=>'reimbursement_receipt_required','label'=>'Receipt required','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'reimbursement_approval_flow','label'=>'Approval flow','type'=>'select','options'=>['manager'=>'Manager','finance'=>'Finance','manager_finance'=>'Manager then Finance','custom'=>'Custom Approval Engine'],'default'=>'manager_finance'],
                ['key'=>'reimbursement_max_amount_without_director','label'=>'Max amount without director approval','type'=>'number','default'=>'5000000'],
                ['key'=>'reimbursement_accurate_mapping_key','label'=>'Accurate mapping key','type'=>'text','default'=>'reimbursement_payable'],
            ]
        ]];
        $this->settingsPage('reimbursement','Reimbursement',$sections);
    }

    public function loanSettings(): void
    {
        $sections = [[
            'title'=>'Employee Loan Parameters','description'=>'Configure loan approval, installment, and payroll deduction behavior.',
            'fields'=>[
                ['key'=>'loan_max_installment_months','label'=>'Max installment months','type'=>'number','default'=>'12'],
                ['key'=>'loan_requires_approval','label'=>'Requires approval','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'loan_payroll_deduction_component','label'=>'Payroll deduction component','type'=>'text','default'=>'EMPLOYEE_LOAN'],
                ['key'=>'loan_accurate_mapping_key','label'=>'Accurate mapping key','type'=>'text','default'=>'employee_loan'],
            ]
        ]];
        $this->settingsPage('loans','Employee Loan',$sections);
    }

    public function trainingSettings(): void
    {
        $sections = [[
            'title'=>'Training Parameters','description'=>'Configure training approval, budget control, certificates, and evaluations.',
            'fields'=>[
                ['key'=>'training_budget_approval_required','label'=>'Budget approval required','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'training_certificate_required','label'=>'Certificate required after training','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'training_evaluation_required','label'=>'Evaluation required','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'training_default_status','label'=>'Default status','type'=>'select','options'=>['planned'=>'Planned','ongoing'=>'Ongoing','completed'=>'Completed','cancelled'=>'Cancelled'],'default'=>'planned'],
            ]
        ]];
        $this->settingsPage('training','Training',$sections);
    }

    public function approvalSettings(): void
    {
        $sections = [[
            'title'=>'Approval Engine Parameters','description'=>'Configure the generic approval engine used by leave, overtime, procurement, duty trip, and other requests.',
            'fields'=>[
                ['key'=>'approval_email_notification','label'=>'Email notifications','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'approval_whatsapp_notification','label'=>'WhatsApp notification','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'approval_default_sla_days','label'=>'Default SLA days','type'=>'number','default'=>'3'],
                ['key'=>'approval_allow_revision','label'=>'Allow request revision','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'approval_parallel_enabled','label'=>'Parallel approval enabled','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
            ]
        ]];
        $records = [
            'approval_type'=>['title'=>'Approval Types','table'=>'approval_types','fields'=>[['name'=>'name','label'=>'Type Name','type'=>'text','required'=>true],['name'=>'code','label'=>'Code','type'=>'text','required'=>true],['name'=>'description','label'=>'Description','type'=>'textarea'],['name'=>'requires_amount','label'=>'Requires Amount','type'=>'select','options'=>$this->yesNo()],['name'=>'requires_attachment','label'=>'Requires Attachment','type'=>'select','options'=>$this->yesNo()]]],
        ];
        $this->settingsPage('approval','Approval',$sections,$records);
    }

    public function facilitySettings(): void
    {
        $sections = [[
            'title'=>'Facility & Benefit Parameters','description'=>'Control eligibility, validity, return rules, and approval for facilities/benefits.',
            'fields'=>[
                ['key'=>'facility_eligibility_strict_mode','label'=>'Strict eligibility mode','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'facility_override_requires_approval','label'=>'Override requires approval','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'facility_return_required_on_resign','label'=>'Return required on resign','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'facility_expiry_alert_days','label'=>'Expiry alert days','type'=>'number','default'=>'30'],
            ]
        ]];
        $records = [
            'facility'=>['title'=>'Facilities / Benefits','table'=>'facilities','fields'=>[['name'=>'name','label'=>'Name','type'=>'text','required'=>true],['name'=>'category','label'=>'Category','type'=>'text'],['name'=>'description','label'=>'Description','type'=>'textarea'],['name'=>'is_asset','label'=>'Is Asset','type'=>'select','options'=>$this->yesNo()]]],
            'insurance'=>['title'=>'Insurance Types','table'=>'insurances','fields'=>[['name'=>'name','label'=>'Insurance Name','type'=>'text','required'=>true],['name'=>'provider','label'=>'Provider','type'=>'text'],['name'=>'description','label'=>'Description','type'=>'textarea']]],
        ];
        $this->settingsPage('facilities','Facilities & Benefits',$sections,$records);
    }

    public function documentSettings(): void
    {
        $sections = [[
            'title'=>'Document Parameters','description'=>'Configure allowed file types, storage provider, Google Drive routing, and retention.',
            'fields'=>[
                ['key'=>'document_storage_provider','label'=>'Storage provider','type'=>'select','options'=>['local'=>'Local Server','google_drive'=>'Google Drive'],'default'=>'local'],
                ['key'=>'document_allowed_extensions','label'=>'Allowed extensions','type'=>'text','default'=>'pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
                ['key'=>'document_max_upload_mb','label'=>'Max upload size MB','type'=>'number','default'=>'10'],
                ['key'=>'document_cv_folder_key','label'=>'CV folder setting key','type'=>'text','default'=>'gdrive_cv_folder_id'],
                ['key'=>'document_employee_folder_key','label'=>'Employee document folder key','type'=>'text','default'=>'gdrive_employee_doc_folder_id'],
                ['key'=>'document_retention_months','label'=>'Retention months','type'=>'number','default'=>'60'],
            ]
        ]];
        $this->settingsPage('documents','Documents',$sections);
    }

    public function aiSettings(): void
    {
        $sections = [[
            'title'=>'AI Analytics Parameters','description'=>'Configure AI provider, allowed analysis scope, privacy safeguards, and audit requirements.',
            'fields'=>[
                ['key'=>'ai_enabled','label'=>'Enable AI module','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'ai_provider','label'=>'AI provider','type'=>'select','options'=>['openai'=>'OpenAI','local'=>'Local/Private','other'=>'Other'],'default'=>'openai'],
                ['key'=>'ai_api_key','label'=>'API key','type'=>'password'],
                ['key'=>'ai_model','label'=>'Model','type'=>'text','default'=>'gpt-4.1-mini'],
                ['key'=>'ai_allow_payroll_analysis','label'=>'Allow payroll analysis','type'=>'select','options'=>$this->yesNo(),'default'=>'0'],
                ['key'=>'ai_mask_sensitive_data','label'=>'Mask sensitive data','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'ai_audit_required','label'=>'Audit every analysis','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
            ]
        ]];
        $this->settingsPage('ai','AI Analytics',$sections);
    }

    public function whatsappSettings(): void
    {
        $sections = [[
            'title'=>'WhatsApp Parameters','description'=>'Configure WhatsApp number normalization, quick actions, and notification templates.',
            'fields'=>[
                ['key'=>'whatsapp_enabled','label'=>'Enable WhatsApp quick contact','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'whatsapp_default_country_code','label'=>'Default country code','type'=>'text','default'=>'62'],
                ['key'=>'whatsapp_show_on_employee_list','label'=>'Show button on employee list','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'whatsapp_show_on_candidate_list','label'=>'Show button on candidate list','type'=>'select','options'=>$this->yesNo(),'default'=>'1'],
                ['key'=>'whatsapp_approval_template','label'=>'Approval notification template','type'=>'textarea'],
            ]
        ]];
        $records = [
            'whatsapp_template'=>['title'=>'WhatsApp Templates','table'=>'whatsapp_templates','fields'=>[['name'=>'name','label'=>'Template Name','type'=>'text','required'=>true],['name'=>'message','label'=>'Message','type'=>'textarea','required'=>true]]],
        ];
        $this->settingsPage('whatsapp','WhatsApp',$sections,$records);
    }

    public function storeSettingRecord(): void
    {
        $type = $_POST['_record_type'] ?? '';
        $redirect = $_POST['_redirect'] ?? '/settings';
        $map = $this->recordMap();
        if (!isset($map[$type])) { flash('error','Unknown setting record type.'); redirect($redirect); }
        $def = $map[$type];
        $columns = [];
        $values = [];
        foreach ($def['columns'] as $column => $options) {
            $columns[] = $column;
            if (($options['default_company'] ?? false) === true) { $values[] = 1; continue; }
            $val = $_POST[$column] ?? ($options['default'] ?? null);
            if (($options['bool'] ?? false) === true) $val = (int)$val;
            if ($val === '') $val = null;
            $values[] = $val;
        }
        $placeholders = implode(',', array_fill(0,count($columns),'?'));
        $sql = 'INSERT INTO '.$def['table'].'('.implode(',',$columns).') VALUES('.$placeholders.')';
        try {
            $id = Database::insert($sql,$values);
            Audit::log('create_'.$type,$def['table'],(int)$id,$_POST);
            flash('success',$def['label'].' saved.');
        } catch (\Throwable $e) {
            flash('error','Save failed: '.$e->getMessage());
        }
        redirect($redirect);
    }

    public function deleteSettingRecord(): void
    {
        $type     = $_POST['_record_type'] ?? '';
        $redirect = $_POST['_redirect'] ?? '/settings';
        $id       = (int)($_POST['id'] ?? 0);
        $map      = $this->recordMap();
        if (!isset($map[$type]) || $id <= 0) { flash('error','Invalid request.'); redirect($redirect); }
        $def = $map[$type];
        try {
            Database::exec('DELETE FROM '.$def['table'].' WHERE id=?', [$id]);
            Audit::log('delete_'.$type, $def['table'], $id, []);
            flash('success', $def['label'].' deleted.');
        } catch (\Throwable $e) {
            flash('error', 'Delete failed: '.$e->getMessage());
        }
        redirect($redirect);
    }

    public function editSettingRecord(): void
    {
        $type = $_GET['type'] ?? '';
        $id   = (int)($_GET['id'] ?? 0);
        $map  = $this->recordMap();
        if (!isset($map[$type]) || $id <= 0) { flash('error','Invalid request.'); redirect('/settings'); }
        $def  = $map[$type];
        $row  = Database::one('SELECT * FROM '.$def['table'].' WHERE id=?', [$id]);
        if (!$row) { flash('error','Record not found.'); redirect('/settings'); }
        $this->view('module/record_edit', [
            'title'     => 'Edit '.$def['label'],
            'type'      => $type,
            'def'       => $def,
            'row'       => $row,
            'redirect'  => $_SERVER['HTTP_REFERER'] ?? '/settings',
        ]);
    }

    public function updateSettingRecord(): void
    {
        $type     = $_POST['_record_type'] ?? '';
        $redirect = $_POST['_redirect'] ?? '/settings';
        $id       = (int)($_POST['id'] ?? 0);
        $map      = $this->recordMap();
        if (!isset($map[$type]) || $id <= 0) { flash('error','Invalid request.'); redirect($redirect); }
        $def  = $map[$type];
        $sets = [];
        $vals = [];
        foreach ($def['columns'] as $column => $options) {
            if ($options['default_company'] ?? false) continue;
            $sets[] = $column.'=?';
            $val = $_POST[$column] ?? ($options['default'] ?? null);
            if (($options['bool'] ?? false) === true) $val = (int)$val;
            if ($val === '') $val = null;
            $vals[] = $val;
        }
        $vals[] = $id;
        try {
            Database::exec('UPDATE '.$def['table'].' SET '.implode(',',$sets).' WHERE id=?', $vals);
            Audit::log('update_'.$type, $def['table'], $id, $_POST);
            flash('success', $def['label'].' updated.');
        } catch (\Throwable $e) {
            flash('error', 'Update failed: '.$e->getMessage());
        }
        redirect($redirect);
    }

    private function recordMap(): array
    {
        return [
            'skill_type'=>['label'=>'Skill type','table'=>'skill_types','columns'=>['name'=>[]]],
            'tag'=>['label'=>'Tag','table'=>'tags','columns'=>['name'=>[],'color'=>['default'=>'#38bdf8']]],
            'departure_reason'=>['label'=>'Departure reason','table'=>'departure_reasons','columns'=>['name'=>[],'description'=>[]]],
            'branch'=>['label'=>'Branch','table'=>'branches','columns'=>['company_id'=>['default_company'=>true],'code'=>[],'name'=>[],'cost_center'=>[],'accurate_branch_id'=>[],'status'=>['default'=>'active']]],
            'department'=>['label'=>'Department','table'=>'departments','columns'=>['company_id'=>['default_company'=>true],'code'=>[],'name'=>[],'cost_center'=>[],'accurate_department_id'=>[],'status'=>['default'=>'active']]],
            'position'=>['label'=>'Position','table'=>'positions','columns'=>['name'=>[],'grade'=>[],'status'=>['default'=>'active']]],
            'job_level'=>['label'=>'Job level','table'=>'job_levels','columns'=>['code'=>[],'name'=>[],'rank_no'=>['default'=>1]]],
            'employment_type'=>['label'=>'Employment type','table'=>'employment_types','columns'=>['name'=>[],'is_permanent'=>['bool'=>true,'default'=>0]]],
            'work_location'=>['label'=>'Work location','table'=>'work_locations','columns'=>['company_id'=>['default_company'=>true],'name'=>[],'type'=>['default'=>'office'],'address'=>[]]],
            'working_schedule'=>['label'=>'Working schedule','table'=>'working_schedules','columns'=>['name'=>[],'flexible_enabled'=>['bool'=>true,'default'=>0],'auto_attendance_enabled'=>['bool'=>true,'default'=>0],'min_hours'=>['default'=>8],'timezone'=>['default'=>'Asia/Jakarta']]],
            'shift_template'=>['label'=>'Shift template','table'=>'shift_templates','columns'=>['name'=>[],'start_time'=>[],'end_time'=>[],'break_minutes'=>['default'=>60]]],
            'public_holiday'=>['label'=>'Holiday','table'=>'public_holidays','columns'=>['holiday_date'=>[],'name'=>[],'type'=>['default'=>'national']]],
            'leave_type'=>['label'=>'Leave type','table'=>'leave_types','columns'=>['name'=>[],'annual_quota'=>['default'=>0],'is_paid'=>['bool'=>true,'default'=>1]]],
            'leave_reason'=>['label'=>'Leave reason','table'=>'leave_reasons','columns'=>['leave_type_id'=>[],'name'=>[],'is_active'=>['bool'=>true,'default'=>1]]],
            'salary_component'=>['label'=>'Salary component','table'=>'salary_components','columns'=>['code'=>[],'name'=>[],'type'=>['default'=>'earning'],'calculation_type'=>['default'=>'fixed'],'default_amount'=>['default'=>0],'taxable'=>['bool'=>true,'default'=>1]]],
            'payroll_period'=>['label'=>'Payroll period','table'=>'payroll_periods','columns'=>['company_id'=>['default_company'=>true],'period_month'=>[],'period_year'=>[],'start_date'=>[],'end_date'=>[],'status'=>['default'=>'draft']]],
            'accurate_mapping'=>['label'=>'Accurate mapping','table'=>'accurate_account_mappings','columns'=>['company_id'=>['default_company'=>true],'mapping_key'=>[],'local_name'=>[],'accurate_account_id'=>[],'accurate_account_no'=>[],'accurate_account_name'=>[]]],
            'approval_type'=>['label'=>'Approval type','table'=>'approval_types','columns'=>['name'=>[],'code'=>[],'description'=>[],'requires_amount'=>['bool'=>true,'default'=>0],'requires_attachment'=>['bool'=>true,'default'=>0],'is_active'=>['bool'=>true,'default'=>1]]],
            'facility'=>['label'=>'Facility','table'=>'facilities','columns'=>['name'=>[],'category'=>[],'description'=>[],'is_asset'=>['bool'=>true,'default'=>0]]],
            'insurance'=>['label'=>'Insurance','table'=>'insurances','columns'=>['name'=>[],'provider'=>[],'description'=>[]]],
            'whatsapp_template'=>['label'=>'WhatsApp template','table'=>'whatsapp_templates','columns'=>['name'=>[],'message'=>[]]],
        ];
    }

    public function approval(): void
    {
        $types=$this->safeAll('SELECT * FROM approval_types ORDER BY id DESC');
        $requests=$this->safeAll('SELECT r.*, t.name type_name FROM approval_requests r LEFT JOIN approval_types t ON t.id=r.request_type_id ORDER BY r.id DESC LIMIT 100');
        $this->view('module/approval',['title'=>'Approval Center','types'=>$types,'requests'=>$requests]);
    }
    public function storeApprovalType(): void { $_POST['_record_type']='approval_type'; $_POST['_redirect']='/approval'; $this->storeSettingRecord(); }

    public function facilities(): void
    {
        $facilities=$this->safeAll('SELECT * FROM facilities ORDER BY id DESC');
        $rules=$this->safeAll('SELECT r.*, f.name facility_name, j.name job_level_name FROM facility_eligibility_rules r LEFT JOIN facilities f ON f.id=r.facility_id LEFT JOIN job_levels j ON j.id=r.job_level_id ORDER BY r.id DESC LIMIT 100');
        $levels=$this->safeAll('SELECT * FROM job_levels ORDER BY rank_no,name');
        $this->view('module/facilities',['title'=>'Facilities & Benefits','facilities'=>$facilities,'rules'=>$rules,'levels'=>$levels]);
    }
    public function storeFacility(): void { $_POST['_record_type']='facility'; $_POST['_redirect']='/facilities'; $this->storeSettingRecord(); }
    public function storeFacilityRule(): void { $id=Database::insert('INSERT INTO facility_eligibility_rules(facility_id,job_level_id,is_eligible,effective_start,effective_end) VALUES(?,?,?,?,?)',[(int)($_POST['facility_id']??0),(int)($_POST['job_level_id']??0),isset($_POST['is_eligible'])?1:0,$_POST['effective_start']?:date('Y-m-d'),$_POST['effective_end']?:null]); Audit::log('create_facility_rule','facility_rule',(int)$id,$_POST); flash('success','Eligibility rule saved.'); redirect('/facilities'); }

    public function documents(): void { $files=$this->safeAll('SELECT * FROM uploaded_files ORDER BY id DESC LIMIT 200'); $this->view('module/documents',['title'=>'Documents & CV Upload','files'=>$files]); }
    public function uploadDocument(): void
    {
        $dir=storage_path('uploads'); if(!is_dir($dir)) mkdir($dir,0775,true);
        $name=''; $path='';
        if(!empty($_FILES['file']['name'])){ $name=basename($_FILES['file']['name']); $target=$dir.'/'.date('YmdHis').'-'.preg_replace('/[^A-Za-z0-9._-]/','_',$name); move_uploaded_file($_FILES['file']['tmp_name'],$target); $path=$target; }
        $id=Database::insert('INSERT INTO uploaded_files(category,original_filename,storage_provider,local_path,uploaded_by,notes,created_at) VALUES(?,?,?,?,?,?,NOW())',[trim($_POST['category']??'supporting'),$name,'local',$path,$_SESSION['user']['id']??null,trim($_POST['notes']??'')]);
        Audit::log('upload_document','uploaded_file',(int)$id, ['file'=>$name]); flash('success','Document uploaded.'); redirect('/documents');
    }

    public function googleDrive(): void { $this->view('module/google_drive',['title'=>'Google Drive Settings']); }
    public function saveGoogleDrive(): void { foreach(['gdrive_enabled','gdrive_client_id','gdrive_client_secret','gdrive_refresh_token','gdrive_default_folder_id','gdrive_cv_folder_id','gdrive_employee_doc_folder_id','gdrive_supporting_folder_id'] as $k) save_setting($k,(string)($_POST[$k]??''),'google_drive'); flash('success','Google Drive settings saved.'); redirect('/settings/google-drive'); }
    public function whatsapp(): void { $templates=$this->safeAll('SELECT * FROM whatsapp_templates ORDER BY id DESC'); $this->view('module/whatsapp',['title'=>'WhatsApp Contact','templates'=>$templates]); }
    public function storeWhatsappTemplate(): void { $_POST['_record_type']='whatsapp_template'; $_POST['_redirect']='/whatsapp'; $this->storeSettingRecord(); }
    public function ai(): void { $logs=$this->safeAll('SELECT * FROM ai_analysis_logs ORDER BY id DESC LIMIT 100'); $this->view('module/ai',['title'=>'AI People Analytics','logs'=>$logs]); }
    public function aiAnalyze(): void { $summary='AI provider is not connected. This record stores the requested analysis scope and audit trail. Configure AI Settings to enable live analysis.'; $id=Database::insert('INSERT INTO ai_analysis_logs(user_id,employee_id,scope,prompt,response_summary,risk_level,created_at) VALUES(?,?,?,?,?,?,NOW())',[$_SESSION['user']['id']??null,(int)($_POST['employee_id']??0),trim($_POST['scope']??'general'),trim($_POST['prompt']??''),$summary,'normal']); Audit::log('ai_analysis','ai_log',(int)$id,$_POST); flash('success','AI analysis audit saved.'); redirect('/ai'); }
}
