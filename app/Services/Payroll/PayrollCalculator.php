<?php
namespace BlueHR\Services\Payroll;
use BlueHR\Core\Database;
class PayrollCalculator {
    public function calculateRun(int $periodId): int {
        $period = Database::one('SELECT * FROM payroll_periods WHERE id=?', [$periodId]);
        if (!$period) throw new \RuntimeException('Payroll period not found');
        $runId = Database::insert('INSERT INTO payroll_runs(payroll_period_id,run_no,status,created_at) VALUES(?,?,?,?)', [$periodId, 'PAY-' . $period['period_year'] . str_pad($period['period_month'],2,'0',STR_PAD_LEFT) . '-' . time(), 'calculated', now()]);
        $employees = Database::all('SELECT * FROM employees WHERE status="active"');
        $totalGross=0; $totalDeduction=0; $totalTax=0; $totalNet=0;
        foreach ($employees as $e) {
            $basic = (float)(Database::one('SELECT amount FROM employee_salary_components esc JOIN salary_components sc ON sc.id=esc.component_id WHERE employee_id=? AND sc.code="BASIC" ORDER BY effective_date DESC LIMIT 1', [$e['id']])['amount'] ?? 0);
            $allow = (float)(Database::one('SELECT COALESCE(SUM(amount),0) v FROM employee_salary_components esc JOIN salary_components sc ON sc.id=esc.component_id WHERE employee_id=? AND sc.type="earning" AND sc.code<>"BASIC"', [$e['id']])['v'] ?? 0);
            $overtime = (float)(Database::one('SELECT COALESCE(SUM(total_hours),0) h FROM overtime_requests WHERE employee_id=? AND status="approved" AND overtime_date BETWEEN ? AND ?', [$e['id'],$period['start_date'],$period['end_date']])['h'] ?? 0) * 25000;
            $unpaidDays = (float)(Database::one('SELECT COALESCE(SUM(total_days),0) d FROM leave_requests lr JOIN leave_types lt ON lt.id=lr.leave_type_id WHERE lr.employee_id=? AND lr.status="approved" AND lt.is_paid=0 AND lr.start_date BETWEEN ? AND ?', [$e['id'],$period['start_date'],$period['end_date']])['d'] ?? 0);
            $deduction = ($basic / 22) * $unpaidDays;
            $bpjsEmployee = ($basic + $allow) * 0.02;
            $bpjsCompany = ($basic + $allow) * 0.04;
            $gross = $basic + $allow + $overtime + $bpjsCompany;
            $pph21 = max(0, ($basic + $allow + $overtime - 5400000) * 0.05);
            $net = $basic + $allow + $overtime - $deduction - $bpjsEmployee - $pph21;
            Database::insert('INSERT INTO payroll_lines(payroll_run_id,employee_id,basic_salary,total_allowance,total_overtime,total_deduction,bpjs_employee,bpjs_company,pph21,take_home_pay,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)', [$runId,$e['id'],$basic,$allow,$overtime,$deduction,$bpjsEmployee,$bpjsCompany,$pph21,$net,now()]);
            $totalGross += $gross; $totalDeduction += $deduction + $bpjsEmployee; $totalTax += $pph21; $totalNet += $net;
        }
        Database::exec('UPDATE payroll_runs SET total_gross=?, total_deduction=?, total_tax=?, total_net=? WHERE id=?', [$totalGross,$totalDeduction,$totalTax,$totalNet,$runId]);
        return (int)$runId;
    }
}
