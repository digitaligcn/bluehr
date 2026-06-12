<?php
namespace BlueHR\Controllers;

use BlueHR\Core\Controller;
use BlueHR\Core\Database;
use BlueHR\Services\Security\Audit;

class OperationalController extends Controller
{
    private function employees(): array
    {
        return Database::all('SELECT id, employee_no, full_name FROM employees ORDER BY full_name LIMIT 500');
    }

    private function employeeName($id): string
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            foreach ($this->employees() as $e) $cache[(string)$e['id']] = $e['full_name'];
        }
        return $cache[(string)$id] ?? '-';
    }

    public function recruitment(): void
    {
        $rows = Database::all('SELECT * FROM candidates ORDER BY id DESC LIMIT 200');
        $this->view('recruitment/index', ['title' => 'Recruitment', 'rows' => $rows]);
    }

    public function storeCandidate(): void
    {
        verify_csrf();
        $id = Database::insert('INSERT INTO candidates(full_name,email,phone,position_applied,stage,status,created_at) VALUES(?,?,?,?,?,?,?)', [
            trim($_POST['full_name'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['phone'] ?? ''), trim($_POST['position_applied'] ?? ''), $_POST['stage'] ?? 'screening', $_POST['status'] ?? 'active', now()
        ]);
        Audit::log('create_candidate', 'candidate', (int)$id, $_POST);
        flash('success', 'Candidate saved.');
        redirect('/recruitment');
    }

    public function updateCandidateStage(): void
    {
        verify_csrf();
        Database::exec('UPDATE candidates SET stage=?, status=? WHERE id=?', [$_POST['stage'] ?? 'screening', $_POST['status'] ?? 'active', (int)($_POST['id'] ?? 0)]);
        Audit::log('update_candidate_stage', 'candidate', (int)($_POST['id'] ?? 0), $_POST);
        flash('success', 'Candidate workflow updated.');
        redirect('/recruitment');
    }

    public function onboarding(): void
    {
        $rows = Database::all('SELECT t.*, e.full_name employee_name FROM onboarding_tasks t LEFT JOIN employees e ON e.id=t.employee_id ORDER BY t.id DESC LIMIT 200');
        $this->view('onboarding/index', ['title' => 'Onboarding', 'rows' => $rows, 'employees' => $this->employees()]);
    }

    public function storeOnboardingTask(): void
    {
        verify_csrf();
        $id = Database::insert('INSERT INTO onboarding_tasks(employee_id,task_name,due_date,status,created_at) VALUES(?,?,?,?,?)', [
            (int)($_POST['employee_id'] ?? 0), trim($_POST['task_name'] ?? ''), $_POST['due_date'] ?: null, $_POST['status'] ?? 'open', now()
        ]);
        Audit::log('create_onboarding_task', 'onboarding_task', (int)$id, $_POST);
        flash('success', 'Onboarding task saved.');
        redirect('/onboarding');
    }

    public function updateOnboardingTask(): void
    {
        verify_csrf();
        Database::exec('UPDATE onboarding_tasks SET status=? WHERE id=?', [$_POST['status'] ?? 'open', (int)($_POST['id'] ?? 0)]);
        Audit::log('update_onboarding_task', 'onboarding_task', (int)($_POST['id'] ?? 0), $_POST);
        flash('success', 'Onboarding status updated.');
        redirect('/onboarding');
    }

    public function reimbursement(): void
    {
        $rows = Database::all('SELECT r.*, e.full_name employee_name FROM reimbursements r LEFT JOIN employees e ON e.id=r.employee_id ORDER BY r.id DESC LIMIT 200');
        $this->view('reimbursement/index', ['title' => 'Reimbursement', 'rows' => $rows, 'employees' => $this->employees()]);
    }

    public function storeReimbursement(): void
    {
        verify_csrf();
        $id = Database::insert('INSERT INTO reimbursements(employee_id,claim_type,amount,description,status,created_at) VALUES(?,?,?,?,?,?)', [
            (int)($_POST['employee_id'] ?? 0), trim($_POST['claim_type'] ?? ''), (float)($_POST['amount'] ?? 0), trim($_POST['description'] ?? ''), 'pending', now()
        ]);
        Audit::log('create_reimbursement', 'reimbursement', (int)$id, $_POST);
        flash('success', 'Reimbursement request saved.');
        redirect('/reimbursement');
    }

    public function approveReimbursement(): void
    {
        verify_csrf();
        Database::exec('UPDATE reimbursements SET status=? WHERE id=?', [$_POST['status'] ?? 'approved', (int)($_POST['id'] ?? 0)]);
        Audit::log('update_reimbursement_status', 'reimbursement', (int)($_POST['id'] ?? 0), $_POST);
        flash('success', 'Reimbursement workflow updated.');
        redirect('/reimbursement');
    }

    public function loans(): void
    {
        $rows = Database::all('SELECT l.*, e.full_name employee_name FROM employee_loans l LEFT JOIN employees e ON e.id=l.employee_id ORDER BY l.id DESC LIMIT 200');
        $this->view('loans/index', ['title' => 'Employee Loans', 'rows' => $rows, 'employees' => $this->employees()]);
    }

    public function storeLoan(): void
    {
        verify_csrf();
        $principal = (float)($_POST['principal'] ?? 0);
        $id = Database::insert('INSERT INTO employee_loans(employee_id,loan_no,principal,outstanding,installment,status,created_at) VALUES(?,?,?,?,?,?,?)', [
            (int)($_POST['employee_id'] ?? 0), trim($_POST['loan_no'] ?? ''), $principal, $principal, (float)($_POST['installment'] ?? 0), $_POST['status'] ?? 'active', now()
        ]);
        Audit::log('create_employee_loan', 'employee_loan', (int)$id, $_POST);
        flash('success', 'Employee loan saved.');
        redirect('/loans');
    }

    public function updateLoan(): void
    {
        verify_csrf();
        Database::exec('UPDATE employee_loans SET outstanding=?, status=? WHERE id=?', [(float)($_POST['outstanding'] ?? 0), $_POST['status'] ?? 'active', (int)($_POST['id'] ?? 0)]);
        Audit::log('update_employee_loan', 'employee_loan', (int)($_POST['id'] ?? 0), $_POST);
        flash('success', 'Employee loan updated.');
        redirect('/loans');
    }

    public function performance(): void
    {
        $rows = Database::all('SELECT p.*, e.full_name employee_name FROM performance_reviews p LEFT JOIN employees e ON e.id=p.employee_id ORDER BY p.id DESC LIMIT 200');
        $this->view('performance/index', ['title' => 'Performance', 'rows' => $rows, 'employees' => $this->employees()]);
    }

    public function storePerformance(): void
    {
        verify_csrf();
        $id = Database::insert('INSERT INTO performance_reviews(employee_id,period_name,score,status,created_at) VALUES(?,?,?,?,?)', [
            (int)($_POST['employee_id'] ?? 0), trim($_POST['period_name'] ?? ''), (float)($_POST['score'] ?? 0), $_POST['status'] ?? 'draft', now()
        ]);
        Audit::log('create_performance_review', 'performance_review', (int)$id, $_POST);
        flash('success', 'Performance review saved.');
        redirect('/performance');
    }

    public function updatePerformance(): void
    {
        verify_csrf();
        Database::exec('UPDATE performance_reviews SET score=?, status=? WHERE id=?', [(float)($_POST['score'] ?? 0), $_POST['status'] ?? 'draft', (int)($_POST['id'] ?? 0)]);
        Audit::log('update_performance_review', 'performance_review', (int)($_POST['id'] ?? 0), $_POST);
        flash('success', 'Performance workflow updated.');
        redirect('/performance');
    }

    public function training(): void
    {
        $rows = Database::all('SELECT * FROM training_programs ORDER BY id DESC LIMIT 200');
        $this->view('training/index', ['title' => 'Training', 'rows' => $rows]);
    }

    public function storeTraining(): void
    {
        verify_csrf();
        $id = Database::insert('INSERT INTO training_programs(name,start_date,end_date,budget,status,created_at) VALUES(?,?,?,?,?,?)', [
            trim($_POST['name'] ?? ''), $_POST['start_date'] ?: null, $_POST['end_date'] ?: null, (float)($_POST['budget'] ?? 0), $_POST['status'] ?? 'planned', now()
        ]);
        Audit::log('create_training_program', 'training_program', (int)$id, $_POST);
        flash('success', 'Training program saved.');
        redirect('/training');
    }

    public function updateTraining(): void
    {
        verify_csrf();
        Database::exec('UPDATE training_programs SET status=? WHERE id=?', [$_POST['status'] ?? 'planned', (int)($_POST['id'] ?? 0)]);
        Audit::log('update_training_program', 'training_program', (int)($_POST['id'] ?? 0), $_POST);
        flash('success', 'Training workflow updated.');
        redirect('/training');
    }

    public function reports(): void
    {
        $data = [
            'employees' => Database::one('SELECT COUNT(*) c FROM employees')['c'] ?? 0,
            'attendance_today' => Database::one('SELECT COUNT(*) c FROM attendance_logs WHERE attendance_date=CURDATE()')['c'] ?? 0,
            'leave_pending' => Database::one("SELECT COUNT(*) c FROM leave_requests WHERE status='pending'")['c'] ?? 0,
            'overtime_pending' => Database::one("SELECT COUNT(*) c FROM overtime_requests WHERE status='pending'")['c'] ?? 0,
            'reimbursement_pending' => Database::one("SELECT COUNT(*) c FROM reimbursements WHERE status='pending'")['c'] ?? 0,
            'payroll_runs' => Database::all('SELECT pr.*, pp.period_month, pp.period_year FROM payroll_runs pr JOIN payroll_periods pp ON pp.id=pr.payroll_period_id ORDER BY pr.id DESC LIMIT 20'),
            'audit' => Database::all('SELECT * FROM audit_logs ORDER BY id DESC LIMIT 50'),
        ];
        $this->view('reports/index', ['title' => 'Reports', 'data' => $data]);
    }

    public function settings(): void
    {
        $roles = Database::all('SELECT * FROM roles ORDER BY id');
        $perms = Database::all('SELECT * FROM permissions ORDER BY module, code');
        $this->view('settings/index', ['title' => 'Settings', 'roles' => $roles, 'perms' => $perms]);
    }
}
