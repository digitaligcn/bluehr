<?php namespace Blue\Controllers;

use Blue\Core\Database;
use Blue\Core\View;
use Blue\Services\Audit;

class UserManagementController
{
    public function index(): void
    {
        $users = Database::all('SELECT id, name, email, status, created_at, updated_at FROM users ORDER BY id DESC');
        $roles = Database::all('SELECT id, name FROM roles ORDER BY name');
        View::render('user_management/index', [
            'title' => 'User Management',
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function create(): void
    {
        $roles = Database::all('SELECT id, name FROM roles ORDER BY name');
        View::render('user_management/form', [
            'title' => 'Create User',
            'mode' => 'create',
            'user' => null,
            'roles' => $roles,
            'selectedRoles' => [],
        ]);
    }

    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $roleIds = array_map('intval', $_POST['role_ids'] ?? []);

        if ($name === '' || $email === '' || $password === '') {
            flash('danger', 'Name, email, and password are required.');
            redirect('/user-management/create');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('danger', 'Email format is invalid.');
            redirect('/user-management/create');
        }
        if (Database::one('SELECT id FROM users WHERE email=?', [$email])) {
            flash('danger', 'Email already exists. Please use another email.');
            redirect('/user-management/create');
        }

        $id = Database::insert(
            'INSERT INTO users(name,email,password_hash,status,created_at,updated_at) VALUES(?,?,?,?,NOW(),NOW())',
            [$name, $email, password_hash($password, PASSWORD_DEFAULT), $status]
        );
        $this->syncRoles((int)$id, $roleIds);
        Audit::log('user.create', 'users', $id, ['email' => $email, 'status' => $status, 'role_ids' => $roleIds]);
        flash('success', 'User created successfully.');
        redirect('/user-management');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $user = Database::one('SELECT id, name, email, status, created_at, updated_at FROM users WHERE id=?', [$id]);
        if (!$user) {
            flash('danger', 'User not found.');
            redirect('/user-management');
        }
        $roles = Database::all('SELECT id, name FROM roles ORDER BY name');
        $selected = Database::all('SELECT role_id FROM user_roles WHERE user_id=?', [$id]);
        $selectedRoles = array_map('intval', array_column($selected, 'role_id'));
        View::render('user_management/form', [
            'title' => 'Edit User',
            'mode' => 'edit',
            'user' => $user,
            'roles' => $roles,
            'selectedRoles' => $selectedRoles,
        ]);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $user = Database::one('SELECT id, email FROM users WHERE id=?', [$id]);
        if (!$user) {
            flash('danger', 'User not found.');
            redirect('/user-management');
        }

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $status = $_POST['status'] ?? 'active';
        $password = (string)($_POST['password'] ?? '');
        $roleIds = array_map('intval', $_POST['role_ids'] ?? []);

        if ($name === '' || $email === '') {
            flash('danger', 'Name and email are required.');
            redirect('/user-management/edit?id='.$id);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('danger', 'Email format is invalid.');
            redirect('/user-management/edit?id='.$id);
        }
        if (Database::one('SELECT id FROM users WHERE email=? AND id<>?', [$email, $id])) {
            flash('danger', 'Email already exists. Please use another email.');
            redirect('/user-management/edit?id='.$id);
        }

        if ($password !== '') {
            Database::exec('UPDATE users SET name=?, email=?, password_hash=?, status=?, updated_at=NOW() WHERE id=?', [
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $status,
                $id,
            ]);
        } else {
            Database::exec('UPDATE users SET name=?, email=?, status=?, updated_at=NOW() WHERE id=?', [$name, $email, $status, $id]);
        }
        $this->syncRoles($id, $roleIds);
        Audit::log('user.update', 'users', $id, ['email' => $email, 'status' => $status, 'role_ids' => $roleIds]);
        flash('success', 'User updated successfully.');
        redirect('/user-management');
    }

    public function deactivate(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)($_SESSION['user']['id'] ?? 0)) {
            flash('danger', 'You cannot deactivate your own active session user.');
            redirect('/user-management');
        }
        Database::exec('UPDATE users SET status="inactive", updated_at=NOW() WHERE id=?', [$id]);
        Audit::log('user.deactivate', 'users', $id, []);
        flash('success', 'User deactivated.');
        redirect('/user-management');
    }

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)($_SESSION['user']['id'] ?? 0)) {
            flash('danger', 'You cannot delete your own active session user.');
            redirect('/user-management');
        }
        $user = Database::one('SELECT id, email FROM users WHERE id=?', [$id]);
        if (!$user) {
            flash('danger', 'User not found.');
            redirect('/user-management');
        }
        Database::exec('DELETE FROM user_roles WHERE user_id=?', [$id]);
        Database::exec('DELETE FROM users WHERE id=?', [$id]);
        Audit::log('user.delete', 'users', $id, ['email' => $user['email']]);
        flash('success', 'User deleted permanently.');
        redirect('/user-management');
    }

    private function syncRoles(int $userId, array $roleIds): void
    {
        Database::exec('DELETE FROM user_roles WHERE user_id=?', [$userId]);
        foreach (array_unique($roleIds) as $roleId) {
            if ($roleId > 0) {
                Database::exec('INSERT INTO user_roles(user_id, role_id) VALUES(?, ?)', [$userId, $roleId]);
            }
        }
    }
}
