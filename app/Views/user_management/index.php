<div class="page-head">
  <div>
    <h1>User Management</h1>
    <p class="muted">Manage login accounts, roles, status, and access users.</p>
  </div>
  <a class="btn primary" href="<?= app_url('/user-management/create') ?>">+ Add User</a>
</div>

<div class="card">
  <div class="toolbar">
    <input class="search-input" data-table-search="users-table" placeholder="Search user by name, email, status...">
    <a class="btn light" href="<?= app_url('/user-management/settings') ?>">User Settings</a>
    <a class="btn light" href="<?= app_url('/user-management/report') ?>">Report</a>
  </div>
  <div class="table-wrap">
    <table id="users-table" data-searchable>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= e($u['id']) ?></td>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><span class="badge <?= $u['status']==='active' ? 'success' : ($u['status']==='locked' ? 'warning' : 'muted') ?>"><?= e($u['status']) ?></span></td>
            <td><?= e($u['created_at']) ?></td>
            <td class="actions">
              <a class="btn small light" href="<?= app_url('/user-management/edit?id='.$u['id']) ?>">Edit</a>
              <?php if ((int)$u['id'] !== (int)($_SESSION['user']['id'] ?? 0)): ?>
                <form method="post" action="<?= app_url('/user-management/deactivate') ?>" class="inline-form" onsubmit="return confirm('Deactivate this user?')">
                  <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                  <button class="btn small warning">Deactivate</button>
                </form>
                <form method="post" action="<?= app_url('/user-management/delete') ?>" class="inline-form" onsubmit="return confirm('Permanently delete this user? This cannot be undone.')">
                  <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                  <button class="btn small danger">Delete</button>
                </form>
              <?php else: ?>
                <span class="muted">Current user</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
