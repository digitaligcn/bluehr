<div class="page-head">
  <div>
    <h1><?= e($title) ?></h1>
    <p class="muted">Konfigurasi jenis cuti dan aturan eligibilitas.</p>
  </div>
  <a class="btn light" href="<?= app_url('/leave-types') ?>">← Kembali</a>
</div>

<form method="post" action="<?= app_url($mode === 'edit' ? '/leave-types/update' : '/leave-types/store') ?>">
  <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
  <?php if ($mode === 'edit'): ?>
    <input type="hidden" name="id" value="<?= e($type['id']) ?>">
  <?php endif; ?>

  <div class="card" style="margin-bottom:1.5rem">
    <div class="card-header"><h3>Informasi Jenis Cuti</h3></div>
    <div class="form-grid">
      <div class="form-group" style="grid-column:span 2">
        <label>Nama Jenis Cuti <span class="required">*</span></label>
        <input type="text" name="name" value="<?= e($type['name'] ?? '') ?>" required placeholder="contoh: Cuti Tahunan">
      </div>
      <div class="form-group" style="grid-column:span 2">
        <label>Deskripsi</label>
        <textarea name="description" rows="2"><?= e($type['description'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label>Quota Tahunan (hari)</label>
        <input type="number" name="annual_quota" value="<?= e($type['annual_quota'] ?? 12) ?>" min="0" step="0.5">
      </div>
      <div class="form-group">
        <label>Maks Hari per Pengajuan</label>
        <input type="number" name="max_days_per_request" value="<?= e($type['max_days_per_request'] ?? 0) ?>" min="0">
        <small class="muted">0 = tidak dibatasi</small>
      </div>
      <div class="form-group">
        <label>Batasan Gender</label>
        <select name="gender_restriction">
          <option value="all"    <?= ($type['gender_restriction'] ?? 'all') === 'all'    ? 'selected' : '' ?>>Semua</option>
          <option value="male"   <?= ($type['gender_restriction'] ?? '') === 'male'   ? 'selected' : '' ?>>Pria saja</option>
          <option value="female" <?= ($type['gender_restriction'] ?? '') === 'female' ? 'selected' : '' ?>>Wanita saja</option>
        </select>
      </div>
      <div class="form-group">
        <label>Carry Forward Maks (hari)</label>
        <input type="number" name="carry_forward_max" value="<?= e($type['carry_forward_max'] ?? 0) ?>" min="0" step="0.5">
      </div>
      <div class="form-group" style="grid-column:span 2">
        <div class="checkbox-group">
          <label><input type="checkbox" name="is_paid" <?= ($type['is_paid'] ?? 1) ? 'checked' : '' ?>> Cuti Berbayar</label>
          <label><input type="checkbox" name="requires_attachment" <?= ($type['requires_attachment'] ?? 0) ? 'checked' : '' ?>> Wajib Lampiran</label>
          <label><input type="checkbox" name="is_carry_forward" <?= ($type['is_carry_forward'] ?? 0) ? 'checked' : '' ?>> Bisa Carry Forward</label>
          <label><input type="checkbox" name="is_active" <?= ($type['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label>
        </div>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:1.5rem">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
      <h3>Aturan Eligibilitas</h3>
      <button type="button" class="btn small primary" onclick="addRule()">+ Tambah Rule</button>
    </div>
    <p class="muted" style="padding:0 1.5rem 1rem">Kosongkan field untuk berarti "semua". Override Quota: kosongkan untuk pakai quota default.</p>
    <div class="table-wrap">
      <table id="rules-table">
        <thead>
          <tr>
            <th>Min. Masa Kerja (bln)</th>
            <th>Maks. Masa Kerja (bln)</th>
            <th>Tipe Karyawan</th>
            <th>Job Level</th>
            <th>Override Quota (hari)</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="rules-body">
          <?php foreach ($rules as $r): ?>
          <tr>
            <td><input type="number" name="rule_min_months[]" value="<?= e($r['min_working_months']) ?>" min="0" style="width:90px"></td>
            <td><input type="number" name="rule_max_months[]" value="<?= e($r['max_working_months'] ?? '') ?>" placeholder="∞" style="width:90px"></td>
            <td>
              <select name="rule_emp_type[]" style="min-width:140px">
                <option value="">Semua</option>
                <?php foreach ($employment_types as $et): ?>
                  <option value="<?= e($et['id']) ?>" <?= $r['employment_type_id'] == $et['id'] ? 'selected' : '' ?>><?= e($et['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td>
              <select name="rule_job_level[]" style="min-width:140px">
                <option value="">Semua</option>
                <?php foreach ($job_levels as $jl): ?>
                  <option value="<?= e($jl['id']) ?>" <?= $r['job_level_id'] == $jl['id'] ? 'selected' : '' ?>><?= e($jl['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="number" name="rule_quota[]" value="<?= e($r['quota_override'] ?? '') ?>" placeholder="default" min="0" step="0.5" style="width:100px"></td>
            <td><button type="button" class="btn small danger" onclick="this.closest('tr').remove()">✕</button></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div style="display:flex;gap:1rem">
    <button type="submit" class="btn primary">Simpan</button>
    <a href="<?= app_url('/leave-types') ?>" class="btn light">Batal</a>
  </div>
</form>

<script>
const empTypes = <?= json_encode($employment_types) ?>;
const jobLevels = <?= json_encode($job_levels) ?>;
function addRule() {
  const body = document.getElementById('rules-body');
  const tr = document.createElement('tr');
  let empOpts = '<option value="">Semua</option>' + empTypes.map(e => `<option value="${e.id}">${e.name}</option>`).join('');
  let jlOpts  = '<option value="">Semua</option>' + jobLevels.map(j => `<option value="${j.id}">${j.name}</option>`).join('');
  tr.innerHTML = `
    <td><input type="number" name="rule_min_months[]" value="0" min="0" style="width:90px"></td>
    <td><input type="number" name="rule_max_months[]" placeholder="∞" style="width:90px"></td>
    <td><select name="rule_emp_type[]" style="min-width:140px">${empOpts}</select></td>
    <td><select name="rule_job_level[]" style="min-width:140px">${jlOpts}</select></td>
    <td><input type="number" name="rule_quota[]" placeholder="default" min="0" step="0.5" style="width:100px"></td>
    <td><button type="button" class="btn small danger" onclick="this.closest('tr').remove()">✕</button></td>
  `;
  body.appendChild(tr);
}
</script>
