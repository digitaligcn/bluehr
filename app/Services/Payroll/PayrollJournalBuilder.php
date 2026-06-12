<?php
namespace BlueHR\Services\Payroll;
use BlueHR\Core\Database;
class PayrollJournalBuilder {
    public function build(int $runId): int {
        $run = Database::one('SELECT * FROM payroll_runs WHERE id=?', [$runId]);
        if (!$run) throw new \RuntimeException('Payroll run not found');
        $journalId = Database::insert('INSERT INTO payroll_journals(payroll_run_id,journal_no,journal_date,description,status,created_at) VALUES(?,?,?,?,?,?)', [$runId,'J-PAY-' . time(), date('Y-m-t'), 'Payroll journal run #' . $runId, 'validated', now()]);
        $lines = [
            ['salary_expense','Beban gaji', $run['total_gross'], 0],
            ['salary_payable','Utang gaji', 0, $run['total_net']],
            ['pph21_payable','Utang PPh 21', 0, $run['total_tax']],
            ['bpjs_payable','Utang BPJS karyawan', 0, $run['total_deduction']],
        ];
        foreach ($lines as $l) {
            $map = Database::one('SELECT * FROM accurate_account_mappings WHERE mapping_key=? LIMIT 1', [$l[0]]);
            Database::insert('INSERT INTO payroll_journal_lines(payroll_journal_id,account_mapping_id,description,debit,credit,created_at) VALUES(?,?,?,?,?,?)', [$journalId,$map['id'] ?? 0,$l[1],$l[2],$l[3],now()]);
        }
        return (int)$journalId;
    }
}
