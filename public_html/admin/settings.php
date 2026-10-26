<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require_admin();
$zones = [
    'America/New_York' => 'Eastern (New York)', 'America/Chicago' => 'Central (Chicago)', 'America/Denver' => 'Mountain (Denver)',
    'America/Phoenix' => 'Arizona (Phoenix)', 'America/Los_Angeles' => 'Pacific (Los Angeles)', 'America/Anchorage' => 'Alaska (Anchorage)',
    'Pacific/Honolulu' => 'Hawaii (Honolulu)', 'UTC' => 'UTC', 'Asia/Karachi' => 'Pakistan (Karachi)',
];
$errors = [];
if (is_post()) {
    csrf_check();
    $title = mb_substr((string)input('blog_title'), 0, 120);
    $desc = mb_substr((string)input('blog_description'), 0, 300);
    $per = (int)input('posts_per_page');
    $tz = (string)input('timezone');
    if ($title === '') $errors['blog_title'] = 'Add a name for the blog.';
    if ($per < 3 || $per > 30) $errors['posts_per_page'] = 'Choose between 3 and 30.';
    if (!isset($zones[$tz])) $errors['timezone'] = 'Choose a time zone.';
    if (!$errors) {
        save_setting('blog_title', $title);
        save_setting('blog_description', $desc);
        save_setting('posts_per_page', (string)$per);
        save_setting('timezone', $tz);
        flash('success', 'Settings saved.');
        redirect('/admin/settings.php');
    }
}
$v = [
    'blog_title' => $_POST['blog_title'] ?? setting('blog_title', 'The taxmetter blog'),
    'blog_description' => $_POST['blog_description'] ?? setting('blog_description', ''),
    'posts_per_page' => $_POST['posts_per_page'] ?? setting('posts_per_page', '9'),
    'timezone' => $_POST['timezone'] ?? setting('timezone', 'America/New_York'),
];
$title = 'Settings';
$active = 'settings';
require APP_DIR . '/views/admin-top.php';
$err = fn($k) => isset($errors[$k]) ? '<p class="ad-error">' . e($errors[$k]) . '</p>' : '';
?>
<form class="ad-card ad-narrow" method="post" novalidate>
  <?= csrf_field() ?>
  <div class="ad-field"><label for="s-title">Blog name</label><input id="s-title" name="blog_title" value="<?= e($v['blog_title']) ?>" maxlength="120" required><small>The heading on /blog and the name in the RSS feed.</small><?= $err('blog_title') ?></div>
  <div class="ad-field"><label for="s-desc">Blog description</label><textarea id="s-desc" name="blog_description" rows="3" maxlength="300" data-count="160"><?= e($v['blog_description']) ?></textarea><small>Shown under the heading and used by search engines.</small></div>
  <div class="ad-field"><label for="s-per">Articles per page</label><input id="s-per" name="posts_per_page" type="number" min="3" max="30" value="<?= e($v['posts_per_page']) ?>"><?= $err('posts_per_page') ?></div>
  <div class="ad-field"><label for="s-tz">Time zone</label>
    <select id="s-tz" name="timezone"><?php foreach ($zones as $k => $l): ?><option value="<?= e($k) ?>"<?= $v['timezone'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <small>Used for publish dates and scheduling.</small><?= $err('timezone') ?></div>
  <button class="ad-btn ad-btn--primary" type="submit">Save settings</button>
</form>
<?php require APP_DIR . '/views/admin-bottom.php';
