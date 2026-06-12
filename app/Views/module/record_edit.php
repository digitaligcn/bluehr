<div class="page-head">
  <div>
    <h2><?=e($title)?></h2>
    <p class="muted">Edit record data.</p>
  </div>
  <a class="btn btn-light" href="<?=e($redirect)?>">← Back</a>
</div>

<div class="card">
  <form method="post" action="<?=url('/settings/record/update')?>">
    <?=csrf_field()?>
    <input type="hidden" name="_record_type" value="<?=e($type)?>">
    <input type="hidden" name="_redirect" value="<?=e($redirect)?>">
    <input type="hidden" name="id" value="<?=e($row['id'])?>">

    <div class="form-grid">
      <?php foreach($def['columns'] as $column => $options):
        if ($options['default_company'] ?? false) continue;
        $value = $row[$column] ?? ($options['default'] ?? '');
      ?>
        <div class="form-group">
          <label><?=e(ucwords(str_replace('_',' ',$column)))?></label>
          <?php if (($options['bool'] ?? false) === true): ?>
            <select name="<?=e($column)?>">
              <option value="0" <?=$value==0?'selected':''?>>No</option>
              <option value="1" <?=$value==1?'selected':''?>>Yes</option>
            </select>
          <?php else: ?>
            <input type="text" name="<?=e($column)?>" value="<?=e((string)$value)?>">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="form-actions" style="margin-top:1.5rem">
      <button class="btn btn-primary">Save Changes</button>
      <a href="<?=e($redirect)?>" class="btn btn-light">Cancel</a>
    </div>
  </form>
</div>
