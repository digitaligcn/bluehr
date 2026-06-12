<?php
namespace BlueHR\Controllers;
use BlueHR\Core\Controller;
use BlueHR\Core\Database;
class OvertimeController extends Controller {
    public function index(): void { $rows=Database::all('SELECT o.*, e.full_name FROM overtime_requests o JOIN employees e ON e.id=o.employee_id ORDER BY o.id DESC LIMIT 300'); $this->view('overtime/index', ['title'=>'Overtime','rows'=>$rows,'employees'=>Database::all('SELECT id,full_name FROM employees WHERE status="active" ORDER BY full_name')]); }
    public function store(): void { $hours=(strtotime($_POST['end_time'])-strtotime($_POST['start_time']))/3600; Database::insert('INSERT INTO overtime_requests(employee_id,overtime_date,start_time,end_time,total_hours,reason,status,created_at) VALUES(?,?,?,?,?,?,?,?)', [$_POST['employee_id'],$_POST['overtime_date'],$_POST['start_time'],$_POST['end_time'],$hours,$_POST['reason']??null,'pending',now()]); redirect('/overtime'); }
}
