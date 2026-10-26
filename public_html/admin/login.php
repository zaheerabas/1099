<?php
require dirname(__DIR__) . '/app/bootstrap.php';
start_admin_session();
if (current_user()) redirect('/admin/');

$error = null;
$email = '';
$next = (string)($_GET['next'] ?? $_POST['next'] ?? '/admin/');
if (!str_starts_with($next, '/admin/') || str_contains($next, '//')) $next = '/admin/';

if (is_post()) {
    csrf_check();
    $email = (string)input('email');
    $error = attempt_login($email, (string)($_POST['password'] ?? ''));
    if (!$error) redirect($next);
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Log in | taxmetter admin</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/admin/assets/admin.css?v=1">
</head>
<body class="ad ad--auth">
<main class="ad-auth">
  <a class="ad-brand" href="/" aria-label="taxmetter home">
    <span class="ad-brand__mark"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 15a7.5 7.5 0 1 1 15 0"/><path d="M12 15l3.4-4.6"/><circle cx="12" cy="15" r="1.6" fill="#fff" stroke="none"/></svg></span>
    <span>tax<b>metter</b></span>
  </a>
  <form class="ad-card ad-auth__card" method="post" novalidate>
    <h1>Log in to the blog admin</h1>
    <?php if ($error): ?><div class="ad-flash ad-flash--error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <div class="ad-field">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" autocomplete="username" required value="<?= e($email) ?>" autofocus>
    </div>
    <div class="ad-field">
      <label for="password">Password</label>
      <input id="password" name="password" type="password" autocomplete="current-password" required>
    </div>
    <button class="ad-btn ad-btn--primary ad-btn--block" type="submit">Log in</button>
  </form>
  <p class="ad-auth__back"><a href="/">Back to the site</a> <a href="/admin/recover.php">Can’t log in?</a></p>
</main>
</body>
</html>
