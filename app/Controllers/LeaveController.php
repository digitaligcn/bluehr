<?php
namespace BlueHR\Controllers;
use BlueHR\Core\Controller;
use BlueHR\Core\Database;
class LeaveController extends Controller {
    public function index(): void { $rows=Database::all('SELECT l.*, e.full_name, lt.name leave_type FROM leave_requests l JOIN employees e ON e.id=l.employee_id JOIN leave_types lt ON lt.id=l.leave_type_id ORDER BY l.id DESC LIMIT 300'); $this->view('leave/index', ['title'=>'Leave','rows'=>$rows,'employees'=>Database::all('SELECT id,full_name FROM employees WHERE status="active" ORDER BY full_name'),'types'=>Database::all('SELECT * FROM leave_types ORDER BY name')]); }
    public function store(): void { $days=(strtotime($_POST['end_date'])-strtotime($_POST['start_date']))/86400+1; Database::insert('INSERT INTO leave_requests(employee_id,leave_type_id,start_date,end_date,total_days,reason,status,created_at) VALUES(?,?,?,?,?,?,?,?)', [$_POST['employee_id'],$_POST['leave_type_id'],$_POST['start_date'],$_POST['end_date'],$days,$_POST['reason']??null,'pending',now()]); redirect('/leave'); }
    public function approve(): void { Database::exec('UPDATE leave_requests SET status="approved", approved_by=?, approved_at=? WHERE id=?', [$_SESSION['user']['id'] ?? null, now(), $_POST['id']]); redirect('/leave'); }
}
