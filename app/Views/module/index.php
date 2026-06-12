<div class="page-head"><h2><?=e($title)?></h2></div>
<div class="grid grid-2">
  <div class="card"><h3>Overview</h3><p><?=e($description)?></p></div>
  <div class="card"><h3>Features</h3><ul><?php foreach(($features ?? []) as $f): ?><li><?=e($f)?></li><?php endforeach; ?></ul></div>
</div>
<br><div class="card"><h3>Modular Location</h3><p class="muted">This feature is isolated as a module under the <code>Modules/</code> directory so future changes do not break the core application.</p></div>
