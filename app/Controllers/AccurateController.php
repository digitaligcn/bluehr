<?php
namespace BlueHR\Controllers;
use BlueHR\Core\Controller;
use BlueHR\Core\Database;
use BlueHR\Services\Accurate\AccurateOAuthService;
use BlueHR\Services\Accurate\AccurateApiClient;
use BlueHR\Services\Security\Crypto;
class AccurateController extends Controller {
    public function index(): void {
        $conn=Database::one('SELECT * FROM accurate_connections ORDER BY id DESC LIMIT 1');
        $maps=Database::all('SELECT * FROM accurate_account_mappings ORDER BY mapping_key');
        $logs=Database::all('SELECT * FROM accurate_sync_logs ORDER BY id DESC LIMIT 100');
        $this->view('accurate/index', ['title'=>'Accurate API','conn'=>$conn,'maps'=>$maps,'logs'=>$logs]);
    }
    public function saveConfig(): void {
        $existing=Database::one('SELECT * FROM accurate_connections ORDER BY id DESC LIMIT 1');
        if ($existing) Database::exec('UPDATE accurate_connections SET client_id=?, client_secret_encrypted=?, redirect_uri=?, scope=?, status=?, updated_at=? WHERE id=?', [$_POST['client_id'],Crypto::encrypt($_POST['client_secret']),$_POST['redirect_uri'],$_POST['scope'],'draft',now(),$existing['id']]);
        else Database::insert('INSERT INTO accurate_connections(company_id,client_id,client_secret_encrypted,redirect_uri,scope,status,created_at) VALUES(?,?,?,?,?,?,?)', [1,$_POST['client_id'],Crypto::encrypt($_POST['client_secret']),$_POST['redirect_uri'],$_POST['scope'],'draft',now()]);
        redirect('/accurate');
    }
    public function connect(): void { $conn=Database::one('SELECT * FROM accurate_connections ORDER BY id DESC LIMIT 1'); if (!$conn) redirect('/accurate'); header('Location: '.(new AccurateOAuthService())->authorizationUrl($conn)); exit; }
    public function callback(): void {
        if (!empty($_GET['state']) && !hash_equals($_SESSION['accurate_oauth_state'] ?? '', $_GET['state'])) exit('Invalid OAuth state');
        $conn=Database::one('SELECT * FROM accurate_connections ORDER BY id DESC LIMIT 1');
        if (!$conn || empty($_GET['code'])) exit('Missing connection or code');
        (new AccurateOAuthService())->exchangeCode($conn, $_GET['code']);
        redirect('/accurate');
    }
    public function saveMapping(): void {
        Database::insert('INSERT INTO accurate_account_mappings(company_id,mapping_key,local_name,accurate_account_id,accurate_account_no,accurate_account_name,created_at) VALUES(?,?,?,?,?,?,?)', [1,$_POST['mapping_key'],$_POST['local_name'],$_POST['accurate_account_id'],$_POST['accurate_account_no'],$_POST['accurate_account_name'],now()]);
        redirect('/accurate');
    }
    public function postPayrollJournal(): void {
        $journal=Database::one('SELECT * FROM payroll_journals WHERE id=?', [$_POST['journal_id']]);
        $token=Database::one('SELECT * FROM accurate_tokens ORDER BY id DESC LIMIT 1');
        $db=Database::one('SELECT * FROM accurate_databases WHERE is_active=1 ORDER BY id DESC LIMIT 1');
        if (!$journal || !$token || !$db) { Database::insert('INSERT INTO accurate_sync_logs(company_id,entity_type,entity_id,action,status,error_message,created_at) VALUES(?,?,?,?,?,?,?)',[1,'payroll_journal',$_POST['journal_id']??0,'post','failed','Missing journal/token/database',now()]); redirect('/accurate'); }
        $lines=Database::all('SELECT pjl.*, aam.accurate_account_no FROM payroll_journal_lines pjl JOIN accurate_account_mappings aam ON aam.id=pjl.account_mapping_id WHERE payroll_journal_id=?', [$journal['id']]);
        $payload=['transDate'=>$journal['journal_date'],'description'=>$journal['description']];
        foreach ($lines as $i=>$l) { $payload["detailJournal[$i].accountNo"]=$l['accurate_account_no']; $payload["detailJournal[$i].memo"]=$l['description']; $payload["detailJournal[$i].debit"]=$l['debit']; $payload["detailJournal[$i].credit"]=$l['credit']; }
        try { $client=new AccurateApiClient(Crypto::decrypt($token['access_token_encrypted']), $db['host'] ?: 'https://account.accurate.id'); $res=$client->post('/api/journal-voucher/save.do', $payload); Database::insert('INSERT INTO accurate_sync_logs(company_id,entity_type,entity_id,action,status,request_payload,response_payload,error_message,created_at) VALUES(?,?,?,?,?,?,?,?,?)',[1,'payroll_journal',$journal['id'],'post',$res['ok']?'success':'failed',json_encode($payload),json_encode($res['json'] ?? $res['body']),$res['ok']?null:'Endpoint must be verified in Accurate Developer API docs',now()]); } catch (\Throwable $e) { Database::insert('INSERT INTO accurate_sync_logs(company_id,entity_type,entity_id,action,status,request_payload,error_message,created_at) VALUES(?,?,?,?,?,?,?,?)',[1,'payroll_journal',$journal['id'],'post','failed',json_encode($payload),$e->getMessage(),now()]); }
        redirect('/accurate');
    }
}
