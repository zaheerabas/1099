<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$user = require_login();
$now = now_utc();

/* ---------- Bulk actions and single delete ---------- */
if (is_post()) {
    csrf_check();
    $ids = array_values(array_filter(array_map('intval', (array)($_POST['ids'] ?? []))));
    $action = (string)input('action');
    if (!$ids) {
        flash('error', 'Select at least one article first.');
    } else {
        $in = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'publish') {
            q("UPDATE posts SET status = 'published', published_at = COALESCE(published_at, ?), updated_at = ? WHERE id IN ($in)", array_merge([$now, $now], $ids));
            flash('success', count($ids) === 1 ? 'Article published.' : count($ids) . ' articles published.');
        } elseif ($action === 'draft') {
            q("UPDATE posts SET status = 'draft', updated_at = ? WHERE id IN ($in)", array_merge([$now], $ids));
            flash('success', count($ids) === 1 ? 'Article moved to drafts.' : count($ids) . ' articles moved to drafts.');
        } elseif ($action === 'delete') {
            q("DELETE FROM posts WHERE id IN ($in)", $ids);
            flash('success', count($ids) === 1 ? 'Article deleted.' : count($ids) . ' articles deleted.');
        }
    }
    redirect('/admin/posts.php' . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']));
}

/* ---------- Filters ---------- */
$status = (string)($_GET['status'] ?? 'all');
$search = trim(mb_substr((string)($_GET['q'] ?? ''), 0, 80));
$catId = (int)($_GET['category'] ?? 0);
$pageNo = max(1, (int)($_GET['page'] ?? 1));

$where = ['1=1'];
$params = [];
if ($status === 'published') { $where[] = "p.status = 'published' AND p.published_at <= ?"; $params[] = $now; }
elseif ($status === 'scheduled') { $where[] = "p.status = 'published' AND p.published_at > ?"; $params[] = $now; }
elseif ($status === 'draft') { $where[] = "p.status = 'draft'"; }
else $status = 'all';
if ($catId) { $where[] = 'p.category_id = ?'; $params[] = $catId; }
if ($search !== '') { $where[] = "(p.title LIKE ? ESCAPE '!' OR p.slug LIKE ? ESCAPE '!')"; $params[] = like_pattern($search); $params[] = like_pattern($search); }
$w = implode(' AND ', $where);

$total = (int)q_val('SELECT COUNT(*)' . post_from() . " WHERE $w", $params);
$pg = paginate($total, 20, $pageNo);
$posts = q_all('SELECT ' . POST_COLUMNS . post_from() . " WHERE $w ORDER BY p.updated_at DESC LIMIT ? OFFSET ?", array_merge($params, [$pg['per'], $pg['offset']]));

$tabCounts = [
    'all' => (int)q_val('SELECT COUNT(*) FROM posts'),
    'published' => (int)q_val("SELECT COUNT(*) FROM posts WHERE status = 'published' AND published_at <= ?", [$now]),
    'scheduled' => (int)q_val("SELECT COUNT(*) FROM posts WHERE status = 'published' AND published_at > ?", [$now]),
    'draft' => (int)q_val("SELECT COUNT(*) FROM posts WHERE status = 'draft'"),
];
$cats = all_categories();

$title = 'Articles';
$active = 'posts';
$topActions = '<a class="ad-btn ad-btn--primary" href="/admin/post-edit.php">New article</a>';
require APP_DIR . '/views/admin-top.php';
$labels = ['all' => 'All', 'published' => 'Published', 'scheduled' => 'Scheduled', 'draft' => 'Drafts'];
?>
<nav class="ad-tabs" aria-label="Filter by status">
  <?php foreach ($labels as $k => $l): ?>
    <a href="<?= e(with_query('/admin/posts.php', ['status' => $k === 'all' ? null : $k, 'q' => $search, 'category' => $catId ?: null])) ?>"<?= $status === $k ? ' aria-current="page"' : '' ?>><?= $l ?> <span><?= $tabCounts[$k] ?></span></a>
  <?php endforeach; ?>
</nav>

<form class="ad-filters" method="get">
  <?php if ($status !== 'all'): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
  <label class="ad-sr" for="f-q">Search articles</label>
  <input id="f-q" type="search" name="q" value="<?= e($search) ?>" placeholder="Search by title or address">
  <label class="ad-sr" for="f-cat">Category</label>
  <select id="f-cat" name="category">
    <option value="">All categories</option>
    <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>"<?= $catId === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
  </select>
  <button class="ad-btn" type="submit">Filter</button>
  <?php if ($search !== '' || $catId): ?><a class="ad-link" href="<?= e(with_query('/admin/posts.php', ['status' => $status === 'all' ? null : $status])) ?>">Clear</a><?php endif; ?>
</form>

<?php if (!$posts): ?>
  <div class="ad-card ad-empty">
    <?php if ($tabCounts['all'] === 0): ?>
      <p>No articles yet.</p><a class="ad-btn ad-btn--primary" href="/admin/post-edit.php">Write your first article</a>
    <?php else: ?>
      <p>No articles match these filters.</p><a class="ad-btn" href="/admin/posts.php">Show all articles</a>
    <?php endif; ?>
  </div>
<?php else: ?>
<form method="post" class="ad-card ad-table-card" data-bulk>
  <?= csrf_field() ?>
  <div class="ad-bulk">
    <label class="ad-sr" for="bulk-action">Action for selected articles</label>
    <select id="bulk-action" name="action">
      <option value="">With selected…</option>
      <option value="publish">Publish</option>
      <option value="draft">Move to drafts</option>
      <option value="delete">Delete</option>
    </select>
    <button class="ad-btn" type="submit" data-bulk-apply>Apply</button>
    <span class="ad-bulk__count" aria-live="polite"></span>
  </div>
  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead>
        <tr>
          <th class="ad-col-check"><input type="checkbox" data-check-all aria-label="Select all articles on this page"></th>
          <th>Title</th>
          <th>Status</th>
          <th class="ad-hide-sm">Category</th>
          <th class="ad-hide-sm">Author</th>
          <th>Date</th>
          <th class="ad-num ad-hide-sm">Views</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $p): $st = post_state($p); ?>
          <tr>
            <td class="ad-col-check"><input type="checkbox" name="ids[]" value="<?= (int)$p['id'] ?>" aria-label="Select “<?= e($p['title']) ?>”"></td>
            <td class="ad-title-cell">
              <a class="ad-row-title" href="/admin/post-edit.php?id=<?= (int)$p['id'] ?>"><?= e($p['title']) ?></a><?= $p['featured'] ? ' <span class="ad-pin" title="Pinned to the top of the blog">Pinned</span>' : '' ?>
              <div class="ad-row-actions">
                <a href="/admin/post-edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
                <?php if ($st === 'published'): ?><a href="<?= e(post_url($p)) ?>" target="_blank" rel="noopener">View</a><?php else: ?><a href="/admin/preview.php?id=<?= (int)$p['id'] ?>" target="_blank" rel="noopener">Preview</a><?php endif; ?>
                <button type="submit" form="del-<?= (int)$p['id'] ?>" class="ad-link ad-link--danger" data-confirm="Delete “<?= e($p['title']) ?>”? This can’t be undone.">Delete</button>
              </div>
            </td>
            <td><span class="ad-badge ad-badge--<?= $st ?>"><?= ucfirst($st) ?></span></td>
            <td class="ad-hide-sm"><?= e($p['category_name'] ?: '—') ?></td>
            <td class="ad-hide-sm"><?= e($p['author_name'] ?: '—') ?></td>
            <td><?= $st === 'draft' ? '<span class="ad-muted">Edited ' . e(fmt_date($p['updated_at'], 'M j')) . '</span>' : e(fmt_date($p['published_at'], 'M j, Y')) ?></td>
            <td class="ad-num ad-hide-sm"><?= number_format((int)$p['views']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</form>
<?php foreach ($posts as $p): ?>
  <form id="del-<?= (int)$p['id'] ?>" method="post" hidden><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="ids[]" value="<?= (int)$p['id'] ?>"></form>
<?php endforeach; ?>

<?php if ($pg['pages'] > 1): ?>
  <nav class="ad-pages" aria-label="Pages">
    <?php if ($pg['page'] > 1): ?><a class="ad-btn" href="<?= e(with_query('/admin/posts.php', ['status' => $status === 'all' ? null : $status, 'q' => $search, 'category' => $catId ?: null, 'page' => $pg['page'] - 1])) ?>">Previous</a><?php endif; ?>
    <span>Page <?= $pg['page'] ?> of <?= $pg['pages'] ?></span>
    <?php if ($pg['page'] < $pg['pages']): ?><a class="ad-btn" href="<?= e(with_query('/admin/posts.php', ['status' => $status === 'all' ? null : $status, 'q' => $search, 'category' => $catId ?: null, 'page' => $pg['page'] + 1])) ?>">Next</a><?php endif; ?>
  </nav>
<?php endif; ?>
<?php endif; ?>
<?php require APP_DIR . '/views/admin-bottom.php';
