<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$user = require_login();

$id = (int)($_GET['id'] ?? 0);
$post = $id ? post_by_id($id) : null;
if ($id && !$post) { flash('error', 'That article no longer exists.'); redirect('/admin/posts.php'); }

$errors = [];
$v = $post ?: [
    'id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'cover_image' => null, 'cover_alt' => '',
    'category_id' => null, 'status' => 'draft', 'published_at' => null, 'seo_title' => '', 'seo_description' => '', 'featured' => 0,
];

if (is_post()) {
    csrf_check();

    if (input('do') === 'delete' && $post) {
        q('DELETE FROM posts WHERE id = ?', [$id]);
        flash('success', 'Article deleted.');
        redirect('/admin/posts.php');
    }

    $v['title'] = mb_substr((string)input('title'), 0, 255);
    $v['slug'] = (string)input('slug');
    $v['excerpt'] = mb_substr((string)input('excerpt'), 0, 600);
    $v['content'] = fill_image_alts(sanitize_html((string)($_POST['content'] ?? '')));
    $v['cover_alt'] = mb_substr((string)input('cover_alt'), 0, 255);
    $v['category_id'] = (int)input('category_id') ?: null;
    $v['seo_title'] = mb_substr((string)input('seo_title'), 0, 255);
    $v['seo_description'] = mb_substr((string)input('seo_description'), 0, 320);
    $v['featured'] = input('featured') ? 1 : 0;
    $intent = (string)input('intent');                       // save | publish | unpublish
    $status = in_array(input('status'), ['draft', 'published'], true) ? (string)input('status') : 'draft';
    if ($intent === 'publish') $status = 'published';
    if ($intent === 'unpublish') $status = 'draft';
    $v['status'] = $status;
    $when = local_to_utc((string)input('published_at'));
    if ($status === 'published') $v['published_at'] = $when ?: ($post['published_at'] ?? null) ?: now_utc();
    else $v['published_at'] = $when;

    if ($v['title'] === '') $errors['title'] = 'Add a title.';
    if ($v['category_id'] && !q_val('SELECT id FROM categories WHERE id = ?', [$v['category_id']])) $v['category_id'] = null;
    if ($status === 'published' && plain_text($v['content']) === '' && strpos($v['content'], '<img') === false) $errors['content'] = 'Write the article before publishing it.';
    if (input('published_at') !== '' && !$when) $errors['published_at'] = 'Use a full date and time.';

    // Cover image: new upload, choose from library, or remove
    if (!empty($_FILES['cover']['name'])) {
        try {
            $m = store_upload($_FILES['cover'], (int)$user['id'], $v['cover_alt']);
            $v['cover_image'] = $m['path'];
        } catch (RuntimeException $ex) {
            $errors['cover'] = $ex->getMessage();
        }
    } elseif (input('cover_existing') !== '' && q_val('SELECT id FROM media WHERE path = ?', [(string)input('cover_existing')])) {
        $v['cover_image'] = (string)input('cover_existing');
    }
    if (input('cover_remove')) $v['cover_image'] = null;

    if (!$errors) {
        $v['slug'] = unique_slug('posts', $v['slug'] !== '' ? $v['slug'] : $v['title'], $id);
        $now = now_utc();
        $data = [$v['title'], $v['slug'], $v['excerpt'], $v['content'], $v['cover_image'], $v['cover_alt'], $v['category_id'], $v['status'],
            $v['published_at'], $v['seo_title'], $v['seo_description'], $v['featured']];
        if ($post) {
            q('UPDATE posts SET title=?, slug=?, excerpt=?, content=?, cover_image=?, cover_alt=?, category_id=?, status=?, published_at=?, seo_title=?, seo_description=?, featured=?, updated_at=? WHERE id=?',
                array_merge($data, [$now, $id]));
        } else {
            q('INSERT INTO posts (title, slug, excerpt, content, cover_image, cover_alt, category_id, status, published_at, seo_title, seo_description, featured, author_id, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                array_merge($data, [(int)$user['id'], $now, $now]));
            $id = (int)db()->lastInsertId();
        }
        $saved = post_by_id($id);
        $st = post_state($saved);
        $msg = match (true) {
            $intent === 'publish' && $st === 'scheduled' => 'Article scheduled for ' . fmt_date($saved['published_at'], 'M j, Y \a\t g:i a') . '.',
            $intent === 'publish' => 'Article published.',
            $intent === 'unpublish' => 'Article moved to drafts. It’s no longer on the blog.',
            default => $st === 'draft' ? 'Draft saved.' : 'Changes saved.',
        };
        flash('success', $msg);
        redirect('/admin/post-edit.php?id=' . $id);
    }
    flash('error', 'Check the highlighted fields.');
}

$state = $post ? post_state($post) : 'draft';
$cats = all_categories();
$library = q_all("SELECT path, alt, original_name FROM media ORDER BY id DESC LIMIT 60");

$title = $post ? 'Edit article' : 'New article';
$active = $post ? 'posts' : 'new';
$headExtra = '<link rel="stylesheet" href="/admin/assets/vendor/quill.snow.css?v=2.0.3">';
$footExtra = '<script src="/admin/assets/vendor/quill.js?v=2.0.3"></script><script src="/admin/assets/editor.js?v=1" defer></script>';
$topActions = $post
    ? ($state === 'published'
        ? '<a class="ad-btn" href="' . e(post_url($post)) . '" target="_blank" rel="noopener">View article</a>'
        : '<a class="ad-btn" href="/admin/preview.php?id=' . (int)$post['id'] . '" target="_blank" rel="noopener">Preview</a>')
    : '';
require APP_DIR . '/views/admin-top.php';
$err = fn($k) => isset($errors[$k]) ? '<p class="ad-error" id="err-' . $k . '">' . e($errors[$k]) . '</p>' : '';
$siteUrl = rtrim((string)$GLOBALS['config']['site_url'], '/');
?>
<form class="ad-editor" method="post" enctype="multipart/form-data" id="post-form" novalidate data-dirty-check>
  <?= csrf_field() ?>
  <input type="hidden" name="intent" value="save">
  <div class="ad-editor__main">
    <div class="ad-field">
      <label for="title">Title</label>
      <input id="title" name="title" class="ad-input-title" value="<?= e($v['title']) ?>" maxlength="255" required placeholder="How to pay quarterly taxes as a freelancer"<?= isset($errors['title']) ? ' aria-invalid="true" aria-describedby="err-title"' : '' ?>>
      <?= $err('title') ?>
    </div>
    <div class="ad-field ad-slug">
      <label for="slug">Web address</label>
      <div class="ad-slug__row"><span><?= e(preg_replace('#^https?://#', '', $siteUrl)) ?>/blog/</span><input id="slug" name="slug" value="<?= e($v['slug']) ?>" maxlength="120" data-slug data-locked="<?= $post ? '1' : '0' ?>" placeholder="made-from-the-title"></div>
      <small>Lowercase letters, numbers and hyphens. Changing it after publishing breaks old links.</small>
    </div>

    <div class="ad-field">
      <span class="ad-label" id="content-label">Article</span>
      <div class="ad-quill-wrap<?= isset($errors['content']) ? ' is-invalid' : '' ?>">
        <div id="quill" aria-labelledby="content-label"></div>
      </div>
      <textarea name="content" id="content" hidden><?= e($v['content']) ?></textarea>
      <noscript><p class="ad-error">The editor needs JavaScript. Turn it on to write articles.</p></noscript>
      <div class="ad-editor__stats"><span data-words>0 words</span><span data-read>1 min read</span></div>
      <?= $err('content') ?>
    </div>

    <div class="ad-field">
      <label for="excerpt">Summary <span class="ad-opt">optional</span></label>
      <textarea id="excerpt" name="excerpt" rows="3" maxlength="600" data-count="220"><?= e($v['excerpt']) ?></textarea>
      <small>Shown under the title and in the article list. Leave empty to use the first lines of the article.</small>
    </div>

    <details class="ad-card ad-seo"<?= ($v['seo_title'] || $v['seo_description']) ? ' open' : '' ?>>
      <summary>Search engine listing</summary>
      <div class="ad-serp" aria-hidden="true">
        <span class="ad-serp__url"><?= e(preg_replace('#^https?://#', '', $siteUrl)) ?> › blog › <span data-serp-slug><?= e($v['slug'] ?: '…') ?></span></span>
        <span class="ad-serp__title" data-serp-title><?= e($v['seo_title'] ?: $v['title'] ?: 'Article title') ?></span>
        <span class="ad-serp__desc" data-serp-desc><?= e($v['seo_description'] ?: $v['excerpt'] ?: 'The summary or the first lines of the article appear here.') ?></span>
      </div>
      <div class="ad-field">
        <label for="seo_title">Title in search results <span class="ad-opt">optional</span></label>
        <input id="seo_title" name="seo_title" value="<?= e($v['seo_title']) ?>" maxlength="255" data-count="60" placeholder="Uses the article title">
      </div>
      <div class="ad-field">
        <label for="seo_description">Description in search results <span class="ad-opt">optional</span></label>
        <textarea id="seo_description" name="seo_description" rows="2" maxlength="320" data-count="160" placeholder="Uses the summary"><?= e($v['seo_description']) ?></textarea>
      </div>
    </details>
  </div>

  <aside class="ad-editor__side" aria-label="Article settings">
    <section class="ad-card ad-box">
      <h2 class="ad-h3">Publishing</h2>
      <p class="ad-state">Status: <span class="ad-badge ad-badge--<?= $state ?>"><?= ucfirst($state) ?></span></p>
      <input type="hidden" name="status" value="<?= e($v['status']) ?>">
      <div class="ad-field">
        <label for="published_at">Publish date</label>
        <input id="published_at" name="published_at" type="datetime-local" value="<?= e(to_local_input($v['published_at'])) ?>"<?= isset($errors['published_at']) ? ' aria-invalid="true"' : '' ?>>
        <small>Empty means now. Pick a future time to schedule it. Times are <?= e(site_tz()->getName()) ?>.</small>
        <?= $err('published_at') ?>
      </div>
      <label class="ad-check"><input type="checkbox" name="featured" value="1"<?= $v['featured'] ? ' checked' : '' ?>><span>Pin to the top of the blog</span></label>
      <div class="ad-box__actions">
        <?php if ($v['status'] === 'published' && $post): ?>
          <button class="ad-btn ad-btn--primary" type="submit" data-intent="save">Save changes</button>
          <button class="ad-btn" type="submit" data-intent="unpublish">Move to drafts</button>
        <?php else: ?>
          <button class="ad-btn ad-btn--primary" type="submit" data-intent="publish"><?= $state === 'draft' ? 'Publish' : 'Publish' ?></button>
          <button class="ad-btn" type="submit" data-intent="save">Save draft</button>
        <?php endif; ?>
      </div>
      <p class="ad-hint">Ctrl + S (⌘ + S on Mac) saves.</p>
    </section>

    <section class="ad-card ad-box">
      <h2 class="ad-h3"><label for="category_id">Category</label></h2>
      <select id="category_id" name="category_id">
        <option value="">No category</option>
        <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>"<?= (int)$v['category_id'] === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
      </select>
      <a class="ad-link" href="/admin/categories.php">Manage categories</a>
    </section>

    <section class="ad-card ad-box">
      <h2 class="ad-h3">Cover image</h2>
      <div class="ad-cover" data-cover>
        <img src="<?= $v['cover_image'] ? e(url($v['cover_image'])) : '' ?>" alt="" data-cover-img<?= $v['cover_image'] ? '' : ' hidden' ?>>
        <p class="ad-cover__empty" data-cover-empty<?= $v['cover_image'] ? ' hidden' : '' ?>>No cover image</p>
      </div>
      <input type="hidden" name="cover_existing" value="" data-cover-existing>
      <input type="hidden" name="cover_remove" value="" data-cover-remove>
      <div class="ad-field">
        <label for="cover">Upload an image</label>
        <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-cover-file<?= isset($errors['cover']) ? ' aria-invalid="true"' : '' ?>>
        <?= $err('cover') ?>
      </div>
      <?php if ($library): ?>
        <details class="ad-pick">
          <summary>Choose from media</summary>
          <div class="ad-pick__grid">
            <?php foreach ($library as $m): ?>
              <button type="button" data-pick="<?= e($m['path']) ?>" data-pick-alt="<?= e($m['alt']) ?>" aria-label="Use <?= e($m['alt'] ?: $m['original_name']) ?>"><img src="<?= e(url($m['path'])) ?>" alt="" loading="lazy"></button>
            <?php endforeach; ?>
          </div>
        </details>
      <?php endif; ?>
      <div class="ad-field">
        <label for="cover_alt">Image description</label>
        <input id="cover_alt" name="cover_alt" value="<?= e($v['cover_alt']) ?>" maxlength="255" placeholder="What the image shows">
        <small>Read aloud by screen readers. Describe what’s in the picture.</small>
      </div>
      <button type="button" class="ad-link ad-link--danger" data-cover-clear<?= $v['cover_image'] ? '' : ' hidden' ?>>Remove cover image</button>
    </section>

    <?php if ($post): ?>
      <section class="ad-card ad-box ad-box--quiet">
        <dl class="ad-facts">
          <div><dt>Created</dt><dd><?= e(fmt_date($post['created_at'], 'M j, Y')) ?></dd></div>
          <div><dt>Last saved</dt><dd><?= e(fmt_date($post['updated_at'], 'M j, g:i a')) ?></dd></div>
          <div><dt>Author</dt><dd><?= e($post['author_name'] ?: '—') ?></dd></div>
          <div><dt>Views</dt><dd><?= number_format((int)$post['views']) ?></dd></div>
        </dl>
        <button class="ad-link ad-link--danger" type="submit" form="delete-form" data-confirm="Delete this article? This can’t be undone.">Delete article</button>
      </section>
    <?php endif; ?>
  </aside>
</form>
<?php if ($post): ?>
  <form id="delete-form" method="post" hidden><?= csrf_field() ?><input type="hidden" name="do" value="delete"></form>
<?php endif; ?>
<script>window.TM_CSRF = <?= json_encode(csrf_token()) ?>;</script>
<?php require APP_DIR . '/views/admin-bottom.php';
