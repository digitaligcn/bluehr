<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($title ?? config('app.name')) ?></title><link rel="stylesheet" href="<?= app_url('/assets/css/app.css') ?>"></head><body>
<?php if(!empty($_SESSION['user'])) include __DIR__.'/sidebar.php'; ?>
<main class="<?= !empty($_SESSION['user']) ? 'main' : 'main public' ?>">
<?php if(!empty($_SESSION['user'])) include __DIR__.'/topbar.php'; ?>
<div class="content"><?php foreach(flash() as $f): ?><div class="alert <?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endforeach; ?><?php include $viewFile; ?></div></main><script src="<?= app_url('/assets/js/app.js') ?>"></script></body></html>
