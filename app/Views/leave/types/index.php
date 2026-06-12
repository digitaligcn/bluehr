<div class="page-head">
  <div>
    <h1>Jenis Cuti</h1>
    <p class="muted">Kelola jenis cuti dan aturan eligibilitas karyawan.</p>
  </div>
  <a class="btn primary" href="<?= app_url('/leave-types/create') ?>">+ Tambah Jenis Cuti</a>
</div>

<div class="card">
  <div class="toolbar">
    <input class="search-input" data-table-search="leave-types-table" placeholder="Cari jenis cuti...">
  </div>
  <div class="table-wrap">
    <table id="leave-types-table" data-searchable>
      <thead>
        <tr>
          <th>Nama</th>
          <th>Quota/Tahun</th>
          <th>Maks/Req</th>
          <th>Dibayar</th>
          <th>Gender</th>
          <th>Carry Forward</th>
          <th>Rules</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($types as $t): ?>
        <tr>
          <td>
            <strong><?= e($t['name']) ?></strong>
            <?php if ($t['description']): ?>
              <br><small class="muted"><?= e($t['description']) ?></small>
            <?php endif; ?>
          </td>
          <td><?= e($t['annual_quota']) ?> hari</td>
          <td><?= e($t['max_days_per_request']) ?> hari</td>
          <td><span class="badge <?= $t['is_paid'] ? 'success' : 'muted' ?>"><?= $t['is_paid'] ? 'Dibayar' : 'Tidak' ?></span></td>
          <td><?= ['all'=>'Semua','male'=>'Pria','female'=>'Wanita'][$t['gender_restriction']] ?? '-' ?></td>
          <td><?= $t['is_carry_forward'] ? $t['carry_forward_max'].' hari' : '-' ?></td>
          <td><span class="badge info"><?= e($t['rule_count']) ?> rule</span></td>
          <td><span class="badge <?= $t['is_active'] ? 'success' : 'muted' ?>"><?= $t['is_active'] ? 'Aktif' : 'Non-aktif' ?></span></td>
          <td class="actions">
            <a class="btn small light" href="<?= app_url('/leave-types/edit?id='.$t['id']) ?>">Edit</a>
            <form method="post" action="<?= app_url('/leave-types/delete') ?>" class="inline-form" onsubmit="return confirm('Hapus jenis cuti ini?')">
              <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="id" value="<?= e($t['id']) ?>">
              <button class="btn small danger">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
