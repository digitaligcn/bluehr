<?php
namespace BlueHR\Controllers;

use BlueHR\Core\Controller;
use BlueHR\Core\Database;
use BlueHR\Services\Security\Audit;
use PDOException;

class UserController extends Controller {
    public function index(): void {
        $users = Database::all('SELECT u.*, GROUP_CONCAT(r.name) roles FROM users u LEFT JOIN user_roles ur ON ur.user_id=u.id LEFT JOIN roles r ON r.id=ur.role_id GROUP BY u.id ORDER BY u.id DESC');
        $this->view('users/index', ['title' => 'Users', 'users' => $users]);
    }

    public function create(): void {
        $roles = Database::all('SELECT * FROM roles ORDER BY name');
        $this->view('users/form', ['title' => 'Create User', 'roles' => $roles, 'user' => null]);
    }

    public function store(): void {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if ($name === '' || $email === '' || $password === '') {
            flash('danger', 'Name, email, and password are required.');
            redirect('/users/create');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('danger', 'Invalid email format.');
            redirect('/users/create');
        }

        $existing = Database::one('SELECT id FROM users WHERE email = ? LIMIT 1', [$email]);
        if ($existing) {
            flash('warning', 'Email already exists. Please use another email or edit the existing user.');
            redirect('/users/create');
        }

        try {
            $id = Database::insert(
                'INSERT INTO users(name,email,password_hash,status,created_at) VALUES(?,?,?,?,?)',
                [$name, $email, password_hash($password, PASSWORD_DEFAULT), $status, now()]
            );

            foreach ($_POST['roles'] ?? [] as $roleId) {
                Database::insert('INSERT IGNORE INTO user_roles(user_id,role_id) VALUES(?,?)', [$id, $roleId]);
            }

            Audit::log('create_user', 'user', (int)$id);
            flash('success', 'User created successfully.');
            redirect('/users');
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                flash('warning', 'Email already exists. Please use another email or edit the existing user.');
                redirect('/users/create');
            }
            throw $e;
        }
    }
}
