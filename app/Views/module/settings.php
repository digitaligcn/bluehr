<div class="page-head">
  <div>
    <h2><?=e($title)?></h2>
    <p class="muted">Operational settings for the <?=e($module)?> module. These settings are isolated by module to preserve BlueHR modular architecture.</p>
  </div>
  <a class="btn btn-light" href="<?=url('/settings')?>">All Settings</a>
</div>

<?php foreach(($sections ?? []) as $section): ?>
<div class="card settings-section">
  <div class="section-head">
    <div>
      <h3><?=e($section['title'] ?? 'Settings')?></h3>
      <?php if(!empty($section['description'])): ?><p class="muted"><?=e($section['description'])?></p><?php endif; ?>
    </div>
  </div>
  <form method="post" action="<?=url('/settings/save')?>" class="settings-form">
    <?=csrf_field()?>
    <input type="hidden" name="_group" value="<?=e($moduleKey ?? 'general')?>">
    <input type="hidden" name="_redirect" value="<?=e(request_path())?>">
    <div class="form-grid">
      <?php foreach(($section['fields'] ?? []) as $field):
        $key = $field['key'];
        $type = $field['type'] ?? 'text';
        $value = setting($key, $field['default'] ?? '');
      ?>
        <label class="field">
          <span><?=e($field['label'] ?? $key)?></span>
          <?php if($type === 'select'): ?>
            <select name="settings[<?=e($key)?>]">
              <?php foreach(($field['options'] ?? []) as $optValue => $optLabel): ?>
                <option value="<?=e($optValue)?>" <?=$value==(string)$optValue?'selected':''?>><?=e($optLabel)?></option>
              <?php endforeach; ?>
            </select>
          <?php elseif($type === 'textarea'): ?>
            <textarea name="settings[<?=e($key)?>]" rows="3"><?=e($value)?></textarea>
          <?php elseif($type === 'password'): ?>
            <input type="password" name="settings[<?=e($key)?>]" value="<?=e($value)?>" autocomplete="new-password">
          <?php else: ?>
            <input type="<?=e($type)?>" name="settings[<?=e($key)?>]" value="<?=e($value)?>">
          <?php endif; ?>
          <?php if(!empty($field['help'])): ?><small><?=e($field['help'])?></small><?php endif; ?>
        </label>
      <?php endforeach; ?>
    </div>
    <div class="form-actions"><button class="btn btn-primary">Save <?=e($section['title'] ?? 'Settings')?></button></div>
  </form>
</div>
<?php endforeach; ?>

<?php foreach(($records ?? []) as $recordKey => $record): $rows = $tables[$recordKey] ?? []; ?>
<div class="grid grid-2 settings-record-grid">
  <div class="card">
    <h3>Add <?=e($record['title'] ?? $recordKey)?></h3>
    <form method="post" action="<?=url('/settings/record/store')?>">
      <?=csrf_field()?>
      <input type="hidden" name="_record_type" value="<?=e($recordKey)?>">
      <input type="hidden" name="_redirect" value="<?=e(request_path())?>">
      <?php foreach(($record['fields'] ?? []) as $field):
        $name = $field['name'];
        $type = $field['type'] ?? 'text';
      ?>
        <label><?=e($field['label'] ?? $name)?></label>
        <?php if($type === 'select'): ?>
          <select name="<?=e($name)?>" <?=!empty($field['required'])?'required':''?>>
            <?php foreach(($field['options'] ?? []) as $optValue => $optLabel): ?>
              <option value="<?=e($optValue)?>"><?=e($optLabel)?></option>
            <?php endforeach; ?>
          </select>
        <?php elseif($type === 'textarea'): ?>
          <textarea name="<?=e($name)?>" rows="3" <?=!empty($field['required'])?'required':''?>><?=e($field['default'] ?? '')?></textarea>
        <?php else: ?>
          <input type="<?=e($type)?>" name="<?=e($name)?>" value="<?=e($field['default'] ?? '')?>" <?=!empty($field['required'])?'required':''?>>
        <?php endif; ?>
      <?php endforeach; ?>
      <button class="btn btn-primary">Save <?=e($record['title'] ?? $recordKey)?></button>
    </form>
  </div>
  <div class="card">
    <h3><?=e($record['title'] ?? $recordKey)?> List</h3>
    <?php if(empty($rows)): ?>
      <p class="muted">No records yet.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table compact">
          <tr>
            <?php foreach(array_keys($rows[0]) as $col): ?><th><?=e(ucwords(str_replace('_',' ',$col)))?></th><?php endforeach; ?>
          </tr>
          <?php foreach($rows as $row): ?>
            <tr>
              <?php foreach($row as $value): ?><td><?=e((string)$value)?></td><?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>

<div class="card">
  <h3>Module Audit Note</h3>
  <p class="muted">Every settings update is saved in <code>app_settings</code> by module group and recorded in the audit log when available. Master data additions are stored in their own module tables.</p>
</div>
