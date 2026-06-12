<?php
namespace BlueHR\Controllers;
use BlueHR\Core\Controller;
use BlueHR\Core\Database;
use BlueHR\Services\Security\Audit;
class AuthController extends Controller {
    public function login(): void { if (!empty($_SESSION['user'])) redirect('/dashboard'); $this->view('auth/login', ['title'=>'Login']); }
    public function authenticate(): void {
        $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? '';
        $user = Database::one('SELECT * FROM users WHERE email=? AND status="active"', [$email]);
        if (!$user || !password_verify($password, $user['password_hash'])) { $this->view('auth/login', ['title'=>'Login', 'error'=>'Email atau password salah.']); return; }
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>$user['id'], 'name'=>$user['name'], 'email'=>$user['email']];
        Database::exec('UPDATE users SET last_login_at=? WHERE id=?', [now(), $user['id']]);
        Audit::log('login', 'user', (int)$user['id']);
        redirect('/dashboard');
    }
    public function logout(): void { Audit::log('logout'); session_destroy(); redirect('/login'); }
}
