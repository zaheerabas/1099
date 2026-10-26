<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$me = require_login();
$errors = [];
$form = ['name' => $me['name'], 'email' => $me['email']];

if (is_post()) {
    csrf_check();
    $form = ['name' => mb_substr((string)input('name'), 0, 120), 'email' => mb_strtolower((string)input('email'))];
    $current = (string)($_POST['current'] ?? '');
    $new = (string)($_POST['new'] ?? '');
    $hash = (string)q_val('SELECT password_hash FROM users WHERE id = ?', [(int)$me['id']]);
    if ($form['name'] === '') $errors['name'] = 'Add your name.';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    elseif (q_val('SELECT id FROM users WHERE email = ? AND id <> ?', [$form['email'], (int)$me['id']])) $errors['email'] = 'Another user already has this email.';
    $changingLogin = $new !== '' || $form['email'] !== $me['email'];
    if ($changingLogin && !password_verify($current, $hash)) $errors['current'] = 'Enter your current password to change your email or password.';
    if ($new !== '') { $p = password_problem($new); if ($p) $errors['new'] = $p; }
    if (!$errors) {
        q('UPDATE users SET name = ?, email = ? WHERE id = ?', [$form['name'], $form['email'], (int)$me['id']]);
        if ($new !== '') { q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), (int)$me['id']]); session_regenerate_id(true); }
        flash('success', $new !== '' ? 'Profile saved. Use your new password next time you log in.' : 'Profile saved.');
        redirect('/admin/profile.php');
    }
}
$title = 'Your profile';
$active = 'profile';
require APP_DIR . '/views/admin-top.php';
$err = fn($k) => isset($errors[$k]) ? '<p class="ad-error">' . e($errors[$k]) . '</p>' : '';
?>
<form class="ad-card ad-narrow" method="post" novalidate>
  <?= csrf_field() ?>
  <div class="ad-field"><label for="p-name">Name</label><input id="p-name" name="name" value="<?= e($form['name']) ?>" required><small>Shown as the author on your articles.</small><?= $err('name') ?></div>
  <div class="ad-field"><label for="p-email">Email</label><input id="p-email" name="email" type="email" value="<?= e($form['email']) ?>" autocomplete="username" required><?= $err('email') ?></div>
  <h2 class="ad-h3">Change password</h2>
  <div class="ad-field"><label for="p-new">New password <span class="ad-opt">optional</span></label><input id="p-new" name="new" type="password" autocomplete="new-password" minlength="10"><small>At least 10 characters.</small><?= $err('new') ?></div>
  <div class="ad-field"><label for="p-cur">Current password</label><input id="p-cur" name="current" type="password" autocomplete="current-password"><small>Needed only to change your email or password.</small><?= $err('current') ?></div>
  <button class="ad-btn ad-btn--primary" type="submit">Save profile</button>
</form>
<?php require APP_DIR . '/views/admin-bottom.php';
