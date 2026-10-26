<?php
/**
 * taxmetter blog installer. Open https://your-domain/install.php once after uploading.
 * It creates app/config.php, the database tables and the first administrator, then locks itself.
 */
declare(strict_types=1);
define('APP_DIR', __DIR__ . '/app');
define('ROOT_DIR', __DIR__);
ini_set('display_errors', '0');

$configFile = APP_DIR . '/config.php';
$installed = is_file($configFile);

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$checks = [
    ['PHP 8.0 or newer', PHP_VERSION_ID >= 80000, 'Your server runs PHP ' . PHP_VERSION . '. Change it in cPanel under “Select PHP Version”.'],
    ['PDO database driver (SQLite or MySQL)', extension_loaded('pdo_sqlite') || extension_loaded('pdo_mysql'), 'Turn on pdo_sqlite or pdo_mysql in cPanel under “Select PHP Version” › Extensions.'],
    ['DOM extension (cleans article HTML)', extension_loaded('dom'), 'Turn on the dom (xml) extension.'],
    ['Fileinfo extension (checks uploads)', extension_loaded('fileinfo'), 'Turn on the fileinfo extension.'],
    ['mbstring extension', extension_loaded('mbstring'), 'Turn on the mbstring extension.'],
    ['The app/data folder is writable', is_writable(APP_DIR . '/data'), 'Set the permission of app/data to 755 in File Manager.'],
    ['The app folder is writable (for config.php)', is_writable(APP_DIR), 'Set the permission of the app folder to 755 while installing.'],
    ['The uploads folder is writable', is_writable(ROOT_DIR . '/uploads'), 'Set the permission of the uploads folder to 755.'],
];
$gd = function_exists('imagecreatetruecolor');
$ready = !in_array(false, array_column($checks, 1), true);

$errors = [];
$samples = true;
$v = [
    'site_url' => (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
    'driver' => extension_loaded('pdo_sqlite') ? 'sqlite' : 'mysql',
    'db_host' => 'localhost', 'db_port' => '3306', 'db_name' => '', 'db_user' => '', 'db_pass' => '',
    'name' => '', 'email' => '', 'blog_title' => 'The taxmetter blog',
];

if (!$installed && $ready && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    foreach ($v as $k => $_) if (isset($_POST[$k])) $v[$k] = trim((string)$_POST[$k]);
    $v['email'] = strtolower($v['email']);
    $samples = isset($_POST['samples']);
    $pw = (string)($_POST['password'] ?? '');
    if (!preg_match('#^https?://[^/\s]+$#', rtrim($v['site_url'], '/'))) $errors['site_url'] = 'Use the full address without a path, like https://taxmetter.com';
    if ($v['name'] === '') $errors['name'] = 'Add your name.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if (mb_strlen($pw) < 10) $errors['password'] = 'Use at least 10 characters.';
    if ($pw !== (string)($_POST['password2'] ?? '')) $errors['password2'] = 'The two passwords don’t match.';
    if ($v['driver'] === 'mysql' && ($v['db_name'] === '' || $v['db_user'] === '')) $errors['db'] = 'Fill in the MySQL database name and user.';

    if (!$errors) {
        $db = $v['driver'] === 'mysql'
            ? ['driver' => 'mysql', 'host' => $v['db_host'] ?: 'localhost', 'port' => (int)($v['db_port'] ?: 3306), 'name' => $v['db_name'], 'user' => $v['db_user'], 'pass' => $v['db_pass']]
            : ['driver' => 'sqlite', 'file' => 'blog-' . bin2hex(random_bytes(6)) . '.sqlite'];
        $GLOBALS['config'] = ['site_url' => rtrim($v['site_url'], '/'), 'db' => $db, 'debug' => false, 'recovery_key' => ''];
        require APP_DIR . '/lib/db.php';
        try {
            $pdo = db_connect($db);
            $exists = false;
            try { $pdo->query('SELECT 1 FROM posts LIMIT 1'); $exists = true; } catch (Throwable $e) {}
            if ($exists) throw new RuntimeException('This database already has blog tables. Use an empty database.');
            foreach (schema_statements($db['driver']) as $sql) $pdo->exec($sql);
            $now = gmdate('Y-m-d H:i:s');
            $pdo->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?,?,?,?,?)')
                ->execute([$v['name'], $v['email'], password_hash($pw, PASSWORD_DEFAULT), 'admin', $now]);
            $uid = (int)$pdo->lastInsertId();
            $set = $pdo->prepare('INSERT INTO settings (name, value) VALUES (?, ?)');
            foreach ([
                'blog_title' => $v['blog_title'] ?: 'The taxmetter blog',
                'blog_description' => 'Plain-English guides to 1099 taxes, deductions and quarterly payments for freelancers and contractors.',
                'posts_per_page' => '9',
                'timezone' => 'America/New_York',
            ] as $k => $val) $set->execute([$k, $val]);
            $cat = $pdo->prepare('INSERT INTO categories (name, slug, description, created_at) VALUES (?,?,?,?)');
            $catIds = [];
            foreach ([
                ['Self-employment tax', 'self-employment-tax', 'How self-employment tax works and how to lower it.'],
                ['Deductions', 'deductions', 'Business expenses and deductions freelancers can claim.'],
                ['Quarterly taxes', 'quarterly-taxes', 'Estimated tax payments, due dates and penalties.'],
            ] as [$cn, $cs, $cd]) {
                $cat->execute([$cn, $cs, $cd, $now]);
                $catIds[$cs] = (int)$pdo->lastInsertId();
            }
            if ($samples) {
                $add = $pdo->prepare('INSERT INTO posts (title, slug, excerpt, content, category_id, author_id, status, published_at, seo_description, featured, views, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
                foreach (require APP_DIR . '/lib/sample-posts.php' as $sp) {
                    $at = gmdate('Y-m-d H:i:s', time() - $sp['days_ago'] * 86400);
                    $add->execute([$sp['title'], $sp['slug'], $sp['excerpt'], $sp['content'], $catIds[$sp['category']] ?? null, $uid, 'published', $at, $sp['seo_description'], $sp['featured'], 0, $at, $at]);
                }
            }
            $welcome = '<p>This is a draft to show how the editor works. Edit it, or delete it from the Articles page.</p>'
                . '<h2>Formatting</h2><p>Use the toolbar for <strong>bold</strong>, <em>italic</em>, links, lists, quotes and headings. Headings become a contents list on long articles.</p>'
                . '<h2>Images</h2><p>Use the image button in the toolbar to upload a picture into the article, and the Cover image box for the picture at the top.</p>'
                . '<h2>Publishing</h2><p>Select Publish to put the article live, or pick a future date first to schedule it.</p>';
            $pdo->prepare('INSERT INTO posts (title, slug, excerpt, content, category_id, author_id, status, featured, views, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
                ->execute(['Welcome to your blog', 'welcome-to-your-blog', 'A quick tour of the editor.', $welcome, null, $uid, 'draft', 0, 0, $now, $now]);

            $php = "<?php\n// taxmetter blog settings. Created by install.php on " . date('Y-m-d') . ".\n// Keep this file private. Set 'debug' => true only while fixing a problem.\n// Locked out? Set 'recovery_key' to a long secret phrase, open /admin/recover.php, then set it back to ''.\nreturn " . var_export($GLOBALS['config'], true) . ";\n";
            if (file_put_contents($configFile, $php, LOCK_EX) === false) throw new RuntimeException('Couldn’t write app/config.php. Make the app folder writable and try again.');
            @chmod($configFile, 0640);
            $installed = true;
            $justInstalled = true;
        } catch (Throwable $ex) {
            $msg = $ex->getMessage();
            if ($ex instanceof PDOException && $v['driver'] === 'mysql') $msg = 'Couldn’t connect to MySQL. Check the database name, user and password in cPanel › MySQL Databases, and that the user is added to the database with All Privileges. (' . $msg . ')';
            $errors['db'] = $msg;
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Install the blog | taxmetter</title>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/admin/assets/admin.css?v=1">
</head>
<body class="ad ad--auth">
<main class="ad-auth ad-auth--wide">
  <p class="ad-brand"><span class="ad-brand__mark"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 15a7.5 7.5 0 1 1 15 0"/><path d="M12 15l3.4-4.6"/><circle cx="12" cy="15" r="1.6" fill="#fff" stroke="none"/></svg></span><span>tax<b>metter</b> blog setup</span></p>

<?php if ($installed): ?>
  <div class="ad-card ad-auth__card">
    <?php if (!empty($justInstalled)): ?>
      <h1>The blog is installed</h1>
      <p>Your administrator account is ready, with a sample draft<?= $samples ? ', six example articles' : '' ?> and three starter categories.</p>
      <div class="ad-flash ad-flash--error" role="alert"><b>Delete install.php now.</b> In cPanel File Manager, delete <code>install.php</code> from public_html. The blog keeps working without it.</div>
      <a class="ad-btn ad-btn--primary ad-btn--block" href="/admin/login.php">Log in to the admin</a>
    <?php else: ?>
      <h1>Already installed</h1>
      <p>The blog is set up. For safety, delete <code>install.php</code> from your hosting.</p>
      <a class="ad-btn ad-btn--primary ad-btn--block" href="/admin/login.php">Go to the admin</a>
    <?php endif; ?>
  </div>
<?php else: ?>
  <form class="ad-card ad-auth__card" method="post" novalidate>
    <h1>Install the blog</h1>
    <p class="ad-muted">This takes about a minute. You’ll do it once.</p>

    <h2 class="ad-h3">Server check</h2>
    <ul class="ad-checks">
      <?php foreach ($checks as [$label, $ok, $fix]): ?>
        <li class="<?= $ok ? 'ok' : 'bad' ?>"><span><?= h($label) ?></span><?php if (!$ok): ?><small><?= h($fix) ?></small><?php endif; ?></li>
      <?php endforeach; ?>
      <li class="<?= $gd ? 'ok' : 'warn' ?>"><span>GD image library <?= $gd ? '' : '(optional)' ?></span><?php if (!$gd): ?><small>Without GD, images are stored as uploaded and not resized. Turn on the gd extension if you can.</small><?php endif; ?></li>
    </ul>

    <?php if (!$ready): ?>
      <div class="ad-flash ad-flash--error" role="alert">Fix the items marked above, then reload this page.</div>
    <?php else: ?>
      <?php if (isset($errors['db'])): ?><div class="ad-flash ad-flash--error" role="alert"><?= h($errors['db']) ?></div><?php endif; ?>

      <h2 class="ad-h3">Site</h2>
      <div class="ad-field"><label for="site_url">Site address</label><input id="site_url" name="site_url" value="<?= h($v['site_url']) ?>" required><small>Used for links in search results, the RSS feed and share previews.</small><?= isset($errors['site_url']) ? '<p class="ad-error">' . h($errors['site_url']) . '</p>' : '' ?></div>
      <div class="ad-field"><label for="blog_title">Blog name</label><input id="blog_title" name="blog_title" value="<?= h($v['blog_title']) ?>"></div>
      <div class="ad-field"><label class="ad-check"><input type="checkbox" name="samples" value="1"<?= $samples ? ' checked' : '' ?>><span><b>Add six example articles</b> about 1099 taxes, so the blog isn’t empty. You can edit or delete them later.</span></label></div>

      <h2 class="ad-h3">Database</h2>
      <fieldset class="ad-field ad-radios">
        <legend class="ad-sr">Database type</legend>
        <label class="ad-check"><input type="radio" name="driver" value="sqlite"<?= $v['driver'] === 'sqlite' ? ' checked' : '' ?><?= extension_loaded('pdo_sqlite') ? '' : ' disabled' ?> data-db="sqlite"><span><b>Simple (SQLite)</b>: nothing to set up. A database file is created in app/data. Good for most blogs.</span></label>
        <label class="ad-check"><input type="radio" name="driver" value="mysql"<?= $v['driver'] === 'mysql' ? ' checked' : '' ?><?= extension_loaded('pdo_mysql') ? '' : ' disabled' ?> data-db="mysql"><span><b>MySQL</b>: create an empty database and user first in cPanel › MySQL Databases.</span></label>
      </fieldset>
      <div class="ad-mysql" data-mysql<?= $v['driver'] === 'mysql' ? '' : ' hidden' ?>>
        <div class="ad-grid2"><div class="ad-field"><label for="db_host">Host</label><input id="db_host" name="db_host" value="<?= h($v['db_host']) ?>"></div><div class="ad-field"><label for="db_port">Port</label><input id="db_port" name="db_port" value="<?= h($v['db_port']) ?>"></div></div>
        <div class="ad-field"><label for="db_name">Database name</label><input id="db_name" name="db_name" value="<?= h($v['db_name']) ?>" placeholder="cpaneluser_blog"></div>
        <div class="ad-field"><label for="db_user">Database user</label><input id="db_user" name="db_user" value="<?= h($v['db_user']) ?>" autocomplete="off"></div>
        <div class="ad-field"><label for="db_pass">Database password</label><input id="db_pass" name="db_pass" type="password" value="<?= h($v['db_pass']) ?>" autocomplete="off"></div>
      </div>

      <h2 class="ad-h3">Your administrator account</h2>
      <div class="ad-field"><label for="name">Your name</label><input id="name" name="name" value="<?= h($v['name']) ?>" required><small>Shown as the author on your articles.</small><?= isset($errors['name']) ? '<p class="ad-error">' . h($errors['name']) . '</p>' : '' ?></div>
      <div class="ad-field"><label for="email">Email</label><input id="email" name="email" type="email" value="<?= h($v['email']) ?>" required autocomplete="username"><?= isset($errors['email']) ? '<p class="ad-error">' . h($errors['email']) . '</p>' : '' ?></div>
      <div class="ad-field"><label for="password">Password</label><input id="password" name="password" type="password" required minlength="10" autocomplete="new-password"><small>At least 10 characters.</small><?= isset($errors['password']) ? '<p class="ad-error">' . h($errors['password']) . '</p>' : '' ?></div>
      <div class="ad-field"><label for="password2">Password again</label><input id="password2" name="password2" type="password" required autocomplete="new-password"><?= isset($errors['password2']) ? '<p class="ad-error">' . h($errors['password2']) . '</p>' : '' ?></div>
      <button class="ad-btn ad-btn--primary ad-btn--block" type="submit">Install the blog</button>
    <?php endif; ?>
  </form>
  <script>
    document.querySelectorAll('[data-db]').forEach(function (r) {
      r.addEventListener('change', function () { document.querySelector('[data-mysql]').hidden = r.value !== 'mysql' || !r.checked; });
    });
  </script>
<?php endif; ?>
</main>
</body>
</html>
