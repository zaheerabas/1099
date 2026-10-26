<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$me = require_admin();
$errors = [];
$form = ['id' => 0, 'name' => '', 'email' => '', 'role' => 'editor'];

if (is_post()) {
    csrf_check();
    $do = (string)input('do');
    $uid = (int)input('id');
    $admins = (int)q_val("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $target = $uid ? q_one('SELECT * FROM users WHERE id = ?', [$uid]) : null;

    if ($do === 'delete' && $target) {
        if ($uid === (int)$me['id']) flash('error', 'You can’t delete your own account.');
        elseif ($target['role'] === 'admin' && $admins <= 1) flash('error', 'Keep at least one administrator.');
        else {
            q('DELETE FROM users WHERE id = ?', [$uid]);
            flash('success', $target['name'] . ' was removed. Their articles stay on the blog without an author.');
        }
        redirect('/admin/users.php');
    }

    $form = ['id' => $uid, 'name' => mb_substr((string)input('name'), 0, 120), 'email' => mb_strtolower((string)input('email')), 'role' => input('role') === 'admin' ? 'admin' : 'editor'];
    $pw = (string)($_POST['password'] ?? '');
    if ($form['name'] === '') $errors['name'] = 'Add a name.';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    elseif (q_val('SELECT id FROM users WHERE email = ? AND id <> ?', [$form['email'], $uid])) $errors['email'] = 'Another user already has this email.';
    if (!$uid || $pw !== '') { $p = password_problem($pw); if ($p) $errors['password'] = $p; }
    if ($target && $target['role'] === 'admin' && $form['role'] !== 'admin' && $admins <= 1) $errors['role'] = 'Keep at least one administrator.';

    if (!$errors) {
        if ($uid) {
            q('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?', [$form['name'], $form['email'], $form['role'], $uid]);
            if ($pw !== '') q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $uid]);
            flash('success', 'User saved.' . ($pw !== '' ? ' Their new password works now.' : ''));
        } else {
            q('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?)', [$form['name'], $form['email'], password_hash($pw, PASSWORD_DEFAULT), $form['role'], now_utc()]);
            flash('success', $form['name'] . ' can now log in at /admin with this email and password.');
        }
        redirect('/admin/users.php');
    }
}
if (!is_post() && isset($_GET['edit'])) {
    $row = q_one('SELECT id, name, email, role FROM users WHERE id = ?', [(int)$_GET['edit']]);
    if ($row) $form = $row;
}
$users = q_all('SELECT u.id, u.name, u.email, u.role, u.last_login_at, (SELECT COUNT(*) FROM posts p WHERE p.author_id = u.id) AS n FROM users u ORDER BY u.name');

$title = 'Users';
$active = 'users';
require APP_DIR . '/views/admin-top.php';
$err = fn($k) => isset($errors[$k]) ? '<p class="ad-error">' . e($errors[$k]) . '</p>' : '';
?>
<div class="ad-grid2 ad-grid2--form">
  <form class="ad-card" method="post" novalidate autocomplete="off">
    <h2 class="ad-h2"><?= $form['id'] ? 'Edit user' : 'Add a user' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">
    <div class="ad-field"><label for="u-name">Name</label><input id="u-name" name="name" value="<?= e($form['name']) ?>" required><?= $err('name') ?></div>
    <div class="ad-field"><label for="u-email">Email</label><input id="u-email" name="email" type="email" value="<?= e($form['email']) ?>" required><?= $err('email') ?></div>
    <fieldset class="ad-field ad-radios">
      <legend>Role</legend>
      <label class="ad-check"><input type="radio" name="role" value="editor"<?= $form['role'] !== 'admin' ? ' checked' : '' ?>><span><b>Editor</b>: writes and publishes articles, manages categories and media</span></label>
      <label class="ad-check"><input type="radio" name="role" value="admin"<?= $form['role'] === 'admin' ? ' checked' : '' ?>><span><b>Administrator</b>: everything, plus users and settings</span></label>
      <?= $err('role') ?>
    </fieldset>
    <div class="ad-field">
      <label for="u-pw"><?= $form['id'] ? 'New password' : 'Password' ?><?= $form['id'] ? ' <span class="ad-opt">leave empty to keep</span>' : '' ?></label>
      <input id="u-pw" name="password" type="password" autocomplete="new-password" minlength="10">
      <small>At least 10 characters. Share it with them privately.</small><?= $err('password') ?>
    </div>
    <div class="ad-row">
      <button class="ad-btn ad-btn--primary" type="submit"><?= $form['id'] ? 'Save user' : 'Add user' ?></button>
      <?php if ($form['id']): ?><a class="ad-link" href="/admin/users.php">Cancel</a><?php endif; ?>
    </div>
  </form>

  <section class="ad-card ad-table-card">
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead><tr><th>Name</th><th>Role</th><th class="ad-num ad-hide-sm">Articles</th><th class="ad-hide-sm">Last login</th><th><span class="ad-sr">Actions</span></th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><span class="ad-row-title"><?= e($u['name']) ?></span><?= (int)$u['id'] === (int)$me['id'] ? ' <span class="ad-muted">(you)</span>' : '' ?><div class="ad-muted"><?= e($u['email']) ?></div></td>
              <td><?= $u['role'] === 'admin' ? 'Administrator' : 'Editor' ?></td>
              <td class="ad-num ad-hide-sm"><?= (int)$u['n'] ?></td>
              <td class="ad-hide-sm"><?= $u['last_login_at'] ? e(fmt_date($u['last_login_at'], 'M j, Y')) : '<span class="ad-muted">Never</span>' ?></td>
              <td class="ad-actions-cell">
                <a href="/admin/users.php?edit=<?= (int)$u['id'] ?>">Edit</a>
                <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <button class="ad-link ad-link--danger" type="submit" data-confirm="Remove <?= e($u['name']) ?>? They won’t be able to log in. Their articles stay.">Remove</button></form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php require APP_DIR . '/views/admin-bottom.php';
