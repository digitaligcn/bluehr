<?php $isEdit = $mode === 'edit'; ?>
<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit User' : 'Create User' ?></h1>
    <p class="muted"><?= $isEdit ? 'Update account information, status, password, and role assignment.' : 'Create a new login account for BlueHR.' ?></p>
  </div>
  <a class="btn light" href="<?= app_url('/user-management') ?>">Back to Users</a>
</div>

<div class="card form-card">
  <form method="post" action="<?= app_url($isEdit ? '/user-management/update' : '/user-management/store') ?>">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= e($user['id']) ?>"><?php endif; ?>

    <div class="grid two">
      <div>
        <label>Name</label>
        <input name="name" required value="<?= e($user['name'] ?? '') ?>" placeholder="Full name">
      </div>
      <div>
        <label>Email</label>
        <input name="email" required type="email" value="<?= e($user['email'] ?? '') ?>" placeholder="name@example.com">
      </div>
      <div>
        <label>Password <?= $isEdit ? '<span class="muted">(leave blank to keep current)</span>' : '' ?></label>
        <input name="password" type="password" <?= $isEdit ? '' : 'required' ?> placeholder="Password">
      </div>
      <div>
        <label>Status</label>
        <?php $status = $user['status'] ?? 'active'; ?>
        <select name="status">
          <option value="active" <?= $status==='active'?'selected':'' ?>>Active</option>
          <option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Inactive</option>
          <option value="locked" <?= $status==='locked'?'selected':'' ?>>Locked</option>
        </select>
      </div>
    </div>

    <hr>
    <h3>Roles</h3>
    <div class="checkbox-grid">
      <?php foreach ($roles as $role): ?>
        <label class="check-card">
          <input type="checkbox" name="role_ids[]" value="<?= e($role['id']) ?>" <?= in_array((int)$role['id'], $selectedRoles ?? [], true) ? 'checked' : '' ?>>
          <span><?= e($role['name']) ?></span>
        </label>
      <?php endforeach; ?>
    </div>

    <div class="form-actions">
      <button class="btn primary"><?= $isEdit ? 'Update User' : 'Create User' ?></button>
      <a class="btn light" href="<?= app_url('/user-management') ?>">Cancel</a>
    </div>
  </form>
</div>
