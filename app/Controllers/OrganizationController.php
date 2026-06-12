<?php
namespace BlueHR\Controllers;
use BlueHR\Core\Controller;
use BlueHR\Core\Database;
class OrganizationController extends Controller {
    public function index(): void { $this->view('organization/index', ['title'=>'Organization','branches'=>Database::all('SELECT * FROM branches ORDER BY name'),'departments'=>Database::all('SELECT * FROM departments ORDER BY name'),'positions'=>Database::all('SELECT p.*, d.name department_name FROM positions p LEFT JOIN departments d ON d.id=p.department_id ORDER BY p.name')]); }
    public function saveBranch(): void { Database::insert('INSERT INTO branches(company_id,code,name,created_at) VALUES(?,?,?,?)', [1,$_POST['code'],$_POST['name'],now()]); redirect('/organization'); }
    public function saveDepartment(): void { Database::insert('INSERT INTO departments(company_id,code,name,parent_id,created_at) VALUES(?,?,?,?,?)', [1,$_POST['code'],$_POST['name'],$_POST['parent_id']?:null,now()]); redirect('/organization'); }
    public function savePosition(): void { Database::insert('INSERT INTO positions(department_id,name,grade,created_at) VALUES(?,?,?,?)', [$_POST['department_id'],$_POST['name'],$_POST['grade']??null,now()]); redirect('/organization'); }
}
