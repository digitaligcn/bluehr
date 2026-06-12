<?php
namespace BlueHR\Controllers;
use BlueHR\Core\Controller;
use BlueHR\Core\Database;
use BlueHR\Services\Payroll\PayrollCalculator;
use BlueHR\Services\Payroll\PayrollJournalBuilder;
class PayrollController extends Controller {
    public function index(): void { $this->view('payroll/index', ['title'=>'Payroll','periods'=>Database::all('SELECT * FROM payroll_periods ORDER BY id DESC'),'runs'=>Database::all('SELECT pr.*, pp.period_month, pp.period_year FROM payroll_runs pr JOIN payroll_periods pp ON pp.id=pr.payroll_period_id ORDER BY pr.id DESC LIMIT 100')]); }
    public function createPeriod(): void { Database::insert('INSERT INTO payroll_periods(company_id,period_month,period_year,start_date,end_date,status,created_at) VALUES(?,?,?,?,?,?,?)', [1,$_POST['period_month'],$_POST['period_year'],$_POST['start_date'],$_POST['end_date'],'draft',now()]); redirect('/payroll'); }
    public function calculate(): void { (new PayrollCalculator())->calculateRun((int)$_POST['period_id']); redirect('/payroll'); }
    public function approve(): void { Database::exec('UPDATE payroll_runs SET status="approved", approved_by=?, approved_at=? WHERE id=?', [$_SESSION['user']['id'] ?? null, now(), $_POST['run_id']]); redirect('/payroll'); }
    public function journal(): void { (new PayrollJournalBuilder())->build((int)$_POST['run_id']); redirect('/payroll'); }
    public function salary(): void { $this->view('payroll/salary', ['title'=>'Salary Components','components'=>Database::all('SELECT * FROM salary_components ORDER BY type,name'),'employees'=>Database::all('SELECT id,full_name FROM employees WHERE status="active" ORDER BY full_name')]); }
    public function saveComponent(): void { Database::insert('INSERT INTO salary_components(code,name,type,calculation_type,default_amount,taxable,created_at) VALUES(?,?,?,?,?,?,?)', [$_POST['code'],$_POST['name'],$_POST['type'],$_POST['calculation_type'] ?? 'fixed',$_POST['default_amount'] ?? 0,isset($_POST['taxable'])?1:0,now()]); redirect('/payroll/salary'); }
    public function assignSalary(): void { Database::insert('INSERT INTO employee_salary_components(employee_id,component_id,amount,effective_date,created_at) VALUES(?,?,?,?,?)', [$_POST['employee_id'],$_POST['component_id'],$_POST['amount'],$_POST['effective_date'],now()]); redirect('/payroll/salary'); }
}
