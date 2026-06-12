<?php
namespace BlueHR\Controllers;
use BlueHR\Core\Controller;
use BlueHR\Core\Database;
use BlueHR\Services\Security\Audit;
class EmployeeController extends Controller {
    public function index(): void { $employees=Database::all('SELECT e.*, d.name department_name, p.name position_name, b.name branch_name FROM employees e LEFT JOIN departments d ON d.id=e.department_id LEFT JOIN positions p ON p.id=e.position_id LEFT JOIN branches b ON b.id=e.branch_id ORDER BY e.id DESC LIMIT 300'); $this->view('employees/index', ['title'=>'Employees','employees'=>$employees]); }
    public function create(): void { $this->view('employees/form', ['title'=>'Add Employee','employee'=>null,'departments'=>Database::all('SELECT * FROM departments ORDER BY name'),'branches'=>Database::all('SELECT * FROM branches ORDER BY name'),'positions'=>Database::all('SELECT * FROM positions ORDER BY name')]); }
    public function store(): void {
        $id=Database::insert('INSERT INTO employees(employee_no,full_name,email,phone,gender,birth_place,birth_date,address,nik,npwp,marital_status,bank_name,bank_account_no,bank_account_name,company_id,branch_id,department_id,position_id,supervisor_id,employment_status,join_date,status,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            $_POST['employee_no'],$_POST['full_name'],$_POST['email']??null,$_POST['phone']??null,$_POST['gender']??null,$_POST['birth_place']??null,$_POST['birth_date']??null,$_POST['address']??null,$_POST['nik']??null,$_POST['npwp']??null,$_POST['marital_status']??null,$_POST['bank_name']??null,$_POST['bank_account_no']??null,$_POST['bank_account_name']??null,1,$_POST['branch_id']?:null,$_POST['department_id']?:null,$_POST['position_id']?:null,null,$_POST['employment_status']??'permanent',$_POST['join_date'],$_POST['status']??'active',now()
        ]);
        Audit::log('create_employee','employee',(int)$id); redirect('/employees');
    }
}
