<?php
namespace BlueHR\Controllers;
use BlueHR\Core\Controller;
use BlueHR\Core\Database;
class AttendanceController extends Controller {
    public function index(): void { $rows=Database::all('SELECT a.*, e.full_name FROM attendance_logs a JOIN employees e ON e.id=a.employee_id ORDER BY a.attendance_date DESC,a.id DESC LIMIT 300'); $this->view('attendance/index', ['title'=>'Attendance','rows'=>$rows,'employees'=>Database::all('SELECT id,full_name FROM employees WHERE status="active" ORDER BY full_name')]); }
    public function store(): void { Database::insert('INSERT INTO attendance_logs(employee_id,attendance_date,check_in,check_out,status,source,notes,created_at) VALUES(?,?,?,?,?,?,?,?)', [$_POST['employee_id'],$_POST['attendance_date'],$_POST['check_in']?:null,$_POST['check_out']?:null,$_POST['status'],'manual',$_POST['notes']??null,now()]); redirect('/attendance'); }
}
