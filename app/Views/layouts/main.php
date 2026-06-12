<?php if (($view ?? '') === '') {} ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=e($title ?? config('app.name'))?></title>
    <link rel="stylesheet" href="<?=url('assets/css/app.css')?>">
</head>
<body>
<?php if (empty($_SESSION['user'])): ?>
    <?php require $viewFile; ?>
<?php else: ?>
    <?php require base_path('app/Views/layouts/sidebar.php'); ?>
    <main class="main">
        <div class="topbar">
            <div><strong><?=e($title ?? '')?></strong></div>
            <div class="muted"><?=e($_SESSION['user']['name'] ?? '')?> | <a href="<?=url('/logout')?>">Logout</a></div>
        </div>
        <div class="content">
            <?php foreach (flash_get() as $f): ?>
                <div class="alert alert-<?=e($f['type'])?>"><?=e($f['message'])?></div>
            <?php endforeach; ?>
            <?php require $viewFile; ?>
        </div>
    </main>
<?php endif; ?>
<script src="<?=url('assets/js/app.js')?>"></script>
</body>
</html>
