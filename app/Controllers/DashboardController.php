<?php
namespace BlueHR\Controllers;
use BlueHR\Core\Controller;
use BlueHR\Core\Database;
class DashboardController extends Controller {
    public function index(): void {
        $stats = [
            'employees' => Database::one('SELECT COUNT(*) c FROM employees WHERE status="active"')['c'] ?? 0,
            'present' => Database::one('SELECT COUNT(*) c FROM attendance_logs WHERE attendance_date=CURDATE() AND status IN ("present","late")')['c'] ?? 0,
            'leave_pending' => Database::one('SELECT COUNT(*) c FROM leave_requests WHERE status="pending"')['c'] ?? 0,
            'payroll_pending' => Database::one('SELECT COUNT(*) c FROM payroll_runs WHERE status IN ("draft","calculated")')['c'] ?? 0,
        ];
        $this->view('dashboard/index', ['title'=>'Dashboard', 'stats'=>$stats]);
    }
}
