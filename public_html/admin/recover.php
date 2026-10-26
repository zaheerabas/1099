<?php
/**
 * Password recovery for when nobody can log in.
 * Off by default. To use it: in cPanel File Manager, edit app/config.php and set
 *   'recovery_key' => 'a-long-random-phrase-only-you-know',
 * then open /admin/recover.php, enter that key, and choose a new password.
 * Afterwards set 'recovery_key' back to '' to switch it off again.
 */
require dirname(__DIR__) . '/app/bootstrap.php';
start_admin_session();
$key = (string)($GLOBALS['config']['recovery_key'] ?? '');
$enabled = strlen($key) >= 16;
$error = null; $done = false;

if ($enabled && is_post()) {
    csrf_check();
    if (login_locked()) {
        $error = 'Too many failed attempts. Wait ' . LOGIN_WINDOW_MIN . ' minutes and try again.';
    } elseif (!hash_equals($key, (string)($_POST['key'] ?? ''))) {
        q('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)', [client_ip(), now_utc()]);
        $error = 'That recovery key doesn’t match the one in app/config.php.';
    } else {
        $email = mb_strtolower((string)input('email'));
        $pw = (string)($_POST['password'] ?? '');
        $user = q_one('SELECT id FROM users WHERE email = ?', [$email]);
        if (!$user) $error = 'No user has that email address.';
        elseif ($p = password_problem($pw)) $error = $p;
        else {
            q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), (int)$user['id']]);
            q('DELETE FROM login_attempts WHERE ip = ?', [client_ip()]);
            $done = true;
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title>Recover access | taxmetter admin</title>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/admin/assets/admin.css?v=1">
</head>
<body class="ad ad--auth">
<main class="ad-auth">
  <div class="ad-card ad-auth__card">
    <h1>Recover access</h1>
    <?php if (!$enabled): ?>
      <p>Recovery is switched off. To switch it on, edit <code>app/config.php</code> in cPanel File Manager and set <code>'recovery_key'</code> to a phrase of at least 16 characters, then reload this page.</p>
    <?php elseif ($done): ?>
      <div class="ad-flash ad-flash--success" role="status">Password changed.</div>
      <p><b>Now switch recovery off:</b> set <code>'recovery_key' =&gt; ''</code> in app/config.php.</p>
      <a class="ad-btn ad-btn--primary ad-btn--block" href="/admin/login.php">Log in</a>
    <?php else: ?>
      <?php if ($error): ?><div class="ad-flash ad-flash--error" role="alert"><?= e($error) ?></div><?php endif; ?>
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="ad-field"><label for="key">Recovery key from app/config.php</label><input id="key" name="key" type="password" required autocomplete="off"></div>
        <div class="ad-field"><label for="email">Email of the account</label><input id="email" name="email" type="email" required></div>
        <div class="ad-field"><label for="password">New password</label><input id="password" name="password" type="password" minlength="10" required autocomplete="new-password"><small>At least 10 characters.</small></div>
        <button class="ad-btn ad-btn--primary ad-btn--block" type="submit">Set new password</button>
      </form>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
