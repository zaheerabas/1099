<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$user = require_login();
$now = now_utc();
$counts = [
    'published' => (int)q_val("SELECT COUNT(*) FROM posts WHERE status = 'published' AND published_at <= ?", [$now]),
    'scheduled' => (int)q_val("SELECT COUNT(*) FROM posts WHERE status = 'published' AND published_at > ?", [$now]),
    'drafts' => (int)q_val("SELECT COUNT(*) FROM posts WHERE status = 'draft'"),
    'views' => (int)q_val('SELECT COALESCE(SUM(views), 0) FROM posts'),
];
$recent = q_all('SELECT ' . POST_COLUMNS . post_from() . ' ORDER BY p.updated_at DESC LIMIT 6');
$popular = q_all('SELECT ' . POST_COLUMNS . post_from() . " WHERE p.status = 'published' AND p.views > 0 ORDER BY p.views DESC LIMIT 5");

$title = 'Dashboard';
$active = 'dashboard';
$topActions = '<a class="ad-btn ad-btn--primary" href="/admin/post-edit.php">New article</a>';
require APP_DIR . '/views/admin-top.php';
?>
<p class="ad-hello">Hi <?= e(strtok($user['name'], ' ')) ?>.</p>
<dl class="ad-stats">
  <div><dt>Published</dt><dd><a href="/admin/posts.php?status=published"><?= $counts['published'] ?></a></dd></div>
  <div><dt>Scheduled</dt><dd><a href="/admin/posts.php?status=scheduled"><?= $counts['scheduled'] ?></a></dd></div>
  <div><dt>Drafts</dt><dd><a href="/admin/posts.php?status=draft"><?= $counts['drafts'] ?></a></dd></div>
  <div><dt>Article views</dt><dd><?= number_format($counts['views']) ?></dd></div>
</dl>

<div class="ad-grid2">
  <section class="ad-card">
    <h2 class="ad-h2">Recently edited</h2>
    <?php if (!$recent): ?>
      <div class="ad-empty"><p>No articles yet.</p><a class="ad-btn ad-btn--primary" href="/admin/post-edit.php">Write your first article</a></div>
    <?php else: ?>
      <ul class="ad-list">
        <?php foreach ($recent as $p): $st = post_state($p); ?>
          <li>
            <a href="/admin/post-edit.php?id=<?= (int)$p['id'] ?>"><?= e($p['title']) ?></a>
            <span class="ad-badge ad-badge--<?= $st ?>"><?= ucfirst($st) ?></span>
            <small>Edited <?= e(fmt_date($p['updated_at'], 'M j, g:i a')) ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
  <section class="ad-card">
    <h2 class="ad-h2">Most read</h2>
    <?php if (!$popular): ?>
      <div class="ad-empty"><p>Views appear here once published articles get readers.</p></div>
    <?php else: ?>
      <ol class="ad-list ad-list--ranked">
        <?php foreach ($popular as $p): ?>
          <li><a href="<?= e(post_url($p)) ?>" target="_blank" rel="noopener"><?= e($p['title']) ?></a><small><?= number_format((int)$p['views']) ?> views</small></li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </section>
</div>
<?php require APP_DIR . '/views/admin-bottom.php';
