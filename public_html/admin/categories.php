<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require_login();
$errors = [];
$form = ['id' => 0, 'name' => '', 'slug' => '', 'description' => ''];

if (is_post()) {
    csrf_check();
    $do = (string)input('do');
    $cid = (int)input('id');
    if ($do === 'delete' && $cid) {
        $n = (int)q_val('SELECT COUNT(*) FROM posts WHERE category_id = ?', [$cid]);
        q('UPDATE posts SET category_id = NULL WHERE category_id = ?', [$cid]);
        q('DELETE FROM categories WHERE id = ?', [$cid]);
        flash('success', $n ? "Category deleted. Its $n " . ($n === 1 ? 'article now has' : 'articles now have') . ' no category.' : 'Category deleted.');
        redirect('/admin/categories.php');
    }
    $form = ['id' => $cid, 'name' => mb_substr((string)input('name'), 0, 120), 'slug' => (string)input('slug'), 'description' => mb_substr((string)input('description'), 0, 500)];
    if ($form['name'] === '') $errors['name'] = 'Add a name.';
    if (!$errors) {
        $slug = unique_slug('categories', $form['slug'] ?: $form['name'], $cid);
        if ($cid) {
            q('UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?', [$form['name'], $slug, $form['description'], $cid]);
            flash('success', 'Category saved.');
        } else {
            q('INSERT INTO categories (name, slug, description, created_at) VALUES (?, ?, ?, ?)', [$form['name'], $slug, $form['description'], now_utc()]);
            flash('success', 'Category added.');
        }
        redirect('/admin/categories.php');
    }
}
if (!is_post() && isset($_GET['edit'])) {
    $row = q_one('SELECT * FROM categories WHERE id = ?', [(int)$_GET['edit']]);
    if ($row) $form = $row;
}
$cats = all_categories();

$title = 'Categories';
$active = 'categories';
require APP_DIR . '/views/admin-top.php';
?>
<div class="ad-grid2 ad-grid2--form">
  <form class="ad-card" method="post" novalidate>
    <h2 class="ad-h2"><?= $form['id'] ? 'Edit category' : 'Add a category' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">
    <div class="ad-field">
      <label for="c-name">Name</label>
      <input id="c-name" name="name" value="<?= e($form['name']) ?>" maxlength="120" required data-slug-source<?= isset($errors['name']) ? ' aria-invalid="true"' : '' ?>>
      <?= isset($errors['name']) ? '<p class="ad-error">' . e($errors['name']) . '</p>' : '' ?>
    </div>
    <div class="ad-field">
      <label for="c-slug">Web address</label>
      <input id="c-slug" name="slug" value="<?= e($form['slug']) ?>" maxlength="120" data-slug data-locked="<?= $form['id'] ? '1' : '0' ?>" placeholder="made-from-the-name">
      <small>Used in /blog/category/<?= e($form['slug'] ?: 'name') ?></small>
    </div>
    <div class="ad-field">
      <label for="c-desc">Description <span class="ad-opt">optional</span></label>
      <textarea id="c-desc" name="description" rows="3" maxlength="500"><?= e($form['description']) ?></textarea>
      <small>Shown at the top of the category page.</small>
    </div>
    <div class="ad-row">
      <button class="ad-btn ad-btn--primary" type="submit"><?= $form['id'] ? 'Save category' : 'Add category' ?></button>
      <?php if ($form['id']): ?><a class="ad-link" href="/admin/categories.php">Cancel</a><?php endif; ?>
    </div>
  </form>

  <section class="ad-card ad-table-card">
    <?php if (!$cats): ?>
      <div class="ad-empty"><p>No categories yet. Add one to group related articles.</p></div>
    <?php else: ?>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead><tr><th>Name</th><th class="ad-num">Articles</th><th><span class="ad-sr">Actions</span></th></tr></thead>
        <tbody>
          <?php foreach ($cats as $c): ?>
            <tr>
              <td><a class="ad-row-title" href="/admin/categories.php?edit=<?= (int)$c['id'] ?>"><?= e($c['name']) ?></a><div class="ad-muted">/blog/category/<?= e($c['slug']) ?></div></td>
              <td class="ad-num"><a href="/admin/posts.php?category=<?= (int)$c['id'] ?>"><?= (int)$c['n'] ?></a></td>
              <td class="ad-actions-cell">
                <a href="/admin/categories.php?edit=<?= (int)$c['id'] ?>">Edit</a>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <button class="ad-link ad-link--danger" type="submit" data-confirm="Delete the “<?= e($c['name']) ?>” category? Its articles stay, without a category.">Delete</button></form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </section>
</div>
<?php require APP_DIR . '/views/admin-bottom.php';
