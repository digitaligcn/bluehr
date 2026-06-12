<?php namespace BlueHR\Controllers;

use BlueHR\Core\Database;


use BlueHR\Core\Controller;

class LeaveTypeController extends Controller
{
    public function index(): void
    {
        $types = Database::all('
            SELECT lt.*, COUNT(ler.id) as rule_count
            FROM leave_types lt
            LEFT JOIN leave_eligibility_rules ler ON ler.leave_type_id = lt.id
            GROUP BY lt.id ORDER BY lt.name
        ');
        return $this->render('leave/types/index', ['title'=>'Jenis Cuti','types'=>$types]);
    }

    public function create(): void
    {
        return $this->render('leave/types/form', [
            'title'=>'Tambah Jenis Cuti','mode'=>'create','type'=>null,'rules'=>[],
            'job_levels'=>Database::all('SELECT id,name FROM job_levels ORDER BY rank_no'),
            'employment_types'=>Database::all('SELECT id,name FROM employment_types ORDER BY name'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate();
        $id = Database::insert(
            'INSERT INTO leave_types (name,description,annual_quota,max_days_per_request,is_paid,requires_attachment,is_carry_forward,carry_forward_max,gender_restriction,is_active) VALUES (?,?,?,?,?,?,?,?,?,?)',
            $data
        );
        $this->syncRules((int)$id);
        flash('success','Jenis cuti berhasil ditambahkan.');
        redirect('/leave-types');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $type = Database::one('SELECT * FROM leave_types WHERE id=?', [$id]);
        if (!$type) { flash('danger','Tidak ditemukan.'); redirect('/leave-types'); }
        $rules = Database::all('SELECT * FROM leave_eligibility_rules WHERE leave_type_id=? ORDER BY min_working_months', [$id]);
        return $this->render('leave/types/form', [
            'title'=>'Edit Jenis Cuti','mode'=>'edit','type'=>$type,'rules'=>$rules,
            'job_levels'=>Database::all('SELECT id,name FROM job_levels ORDER BY rank_no'),
            'employment_types'=>Database::all('SELECT id,name FROM employment_types ORDER BY name'),
        ]);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!Database::one('SELECT id FROM leave_types WHERE id=?',[$id])) { flash('danger','Tidak ditemukan.'); redirect('/leave-types'); }
        $data = $this->validate();
        $data[] = $id;
        Database::exec('UPDATE leave_types SET name=?,description=?,annual_quota=?,max_days_per_request=?,is_paid=?,requires_attachment=?,is_carry_forward=?,carry_forward_max=?,gender_restriction=?,is_active=? WHERE id=?',$data);
        $this->syncRules($id);
        flash('success','Jenis cuti berhasil diperbarui.');
        redirect('/leave-types');
    }

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        Database::exec('DELETE FROM leave_eligibility_rules WHERE leave_type_id=?',[$id]);
        Database::exec('DELETE FROM leave_types WHERE id=?',[$id]);
        flash('success','Jenis cuti dihapus.');
        redirect('/leave-types');
    }

    private function validate(): array
    {
        $name = trim($_POST['name'] ?? '');
        if ($name==='') { flash('danger','Nama wajib diisi.'); redirect('/leave-types'); }
        return [
            $name, trim($_POST['description']??''),
            (float)($_POST['annual_quota']??0), (int)($_POST['max_days_per_request']??0),
            isset($_POST['is_paid'])?1:0, isset($_POST['requires_attachment'])?1:0,
            isset($_POST['is_carry_forward'])?1:0, (float)($_POST['carry_forward_max']??0),
            $_POST['gender_restriction']??'all', isset($_POST['is_active'])?1:0,
        ];
    }

    private function syncRules(int $leaveTypeId): void
    {
        Database::exec('DELETE FROM leave_eligibility_rules WHERE leave_type_id=?',[$leaveTypeId]);
        $mins=$_POST['rule_min_months']??[]; $maxs=$_POST['rule_max_months']??[];
        $emps=$_POST['rule_emp_type']??[]; $jls=$_POST['rule_job_level']??[];
        $quotas=$_POST['rule_quota']??[];
        foreach($mins as $i=>$min){
            Database::exec(
                'INSERT INTO leave_eligibility_rules (leave_type_id,min_working_months,max_working_months,employment_type_id,job_level_id,quota_override,is_active) VALUES (?,?,?,?,?,?,1)',
                [$leaveTypeId,(int)$min,$maxs[$i]!==''?(int)$maxs[$i]:null,$emps[$i]!==''?(int)$emps[$i]:null,$jls[$i]!==''?(int)$jls[$i]:null,$quotas[$i]!==''?(float)$quotas[$i]:null]
            );
        }
    }
}
