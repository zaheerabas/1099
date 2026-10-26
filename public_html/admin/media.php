<?php
require dirname(__DIR__) . '/app/bootstrap.php';
$user = require_login();

if (is_post()) {
    csrf_check();
    $do = (string)input('do');
    if ($do === 'upload') {
        $files = $_FILES['images'] ?? null;
        $ok = 0; $fails = [];
        if ($files && is_array($files['name'])) {
            foreach ($files['name'] as $i => $name) {
                if ($name === '') continue;
                $one = ['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                try { store_upload($one, (int)$user['id']); $ok++; }
                catch (RuntimeException $ex) { $fails[] = $name . ': ' . $ex->getMessage(); }
            }
        }
        if ($ok) flash('success', $ok === 1 ? 'Image uploaded.' : "$ok images uploaded.");
        foreach ($fails as $f) flash('error', $f);
        if (!$ok && !$fails) flash('error', 'Choose at least one image to upload.');
    } elseif ($do === 'alt') {
        q('UPDATE media SET alt = ? WHERE id = ?', [mb_substr((string)input('alt'), 0, 255), (int)input('id')]);
        flash('success', 'Image description saved.');
    } elseif ($do === 'delete') {
        $m = q_one('SELECT * FROM media WHERE id = ?', [(int)input('id')]);
        if ($m) { delete_media($m); flash('success', 'Image deleted.'); }
    }
    redirect('/admin/media.php' . (isset($_GET['page']) ? '?page=' . (int)$_GET['page'] : ''));
}

$total = (int)q_val('SELECT COUNT(*) FROM media');
$pg = paginate($total, 24, (int)($_GET['page'] ?? 1));
$items = q_all('SELECT * FROM media ORDER BY id DESC LIMIT ? OFFSET ?', [$pg['per'], $pg['offset']]);

$title = 'Media';
$active = 'media';
require APP_DIR . '/views/admin-top.php';
?>
<form class="ad-card ad-upload" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="do" value="upload">
  <label class="ad-drop" for="m-files">
    <svg aria-hidden="true" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4M7 9l5-5 5 5"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
    <span><b>Choose images</b> or drop them here</span>
    <small>JPG, PNG, WebP or GIF, up to 8 MB each. Large images are resized to 1,800 px wide.</small>
    <input id="m-files" name="images[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-drop-input>
  </label>
  <div class="ad-row"><span class="ad-muted" data-drop-count>No images chosen</span><button class="ad-btn ad-btn--primary" type="submit">Upload</button></div>
</form>

<?php if (!$items): ?>
  <div class="ad-card ad-empty"><p>No images yet. Upload images here or straight from the article editor.</p></div>
<?php else: ?>
  <p class="ad-muted"><?= $total ?> image<?= $total === 1 ? '' : 's' ?></p>
  <ul class="ad-media">
    <?php foreach ($items as $m): $uses = media_usage($m); ?>
      <li class="ad-card">
        <a class="ad-media__img" href="<?= e(url($m['path'])) ?>" target="_blank" rel="noopener"><img src="<?= e(url($m['path'])) ?>" alt="<?= e($m['alt']) ?>" loading="lazy"></a>
        <div class="ad-media__info">
          <p class="ad-media__name" title="<?= e($m['original_name']) ?>"><?= e($m['original_name'] ?: basename($m['path'])) ?></p>
          <p class="ad-muted"><?= (int)$m['width'] ?> × <?= (int)$m['height'] ?>, <?= e(human_size((int)$m['size'])) ?><?= $uses ? ', used in ' . $uses . ' article' . ($uses === 1 ? '' : 's') : '' ?></p>
          <form method="post" class="ad-media__alt"><?= csrf_field() ?><input type="hidden" name="do" value="alt"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
            <label class="ad-sr" for="alt-<?= (int)$m['id'] ?>">Image description</label>
            <input id="alt-<?= (int)$m['id'] ?>" name="alt" value="<?= e($m['alt']) ?>" placeholder="Describe the image" maxlength="255">
            <button class="ad-btn ad-btn--sm" type="submit">Save</button>
          </form>
          <div class="ad-row">
            <button type="button" class="ad-link" data-copy="<?= e(abs_url($m['path'])) ?>">Copy address</button>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <button class="ad-link ad-link--danger" type="submit" data-confirm="<?= $uses ? 'This image is used in ' . $uses . ' article' . ($uses === 1 ? '' : 's') . ' and will disappear from ' . ($uses === 1 ? 'it' : 'them') . '. Delete it anyway?' : 'Delete this image? This can’t be undone.' ?>">Delete</button></form>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if ($pg['pages'] > 1): ?>
    <nav class="ad-pages" aria-label="Pages">
      <?php if ($pg['page'] > 1): ?><a class="ad-btn" href="?page=<?= $pg['page'] - 1 ?>">Previous</a><?php endif; ?>
      <span>Page <?= $pg['page'] ?> of <?= $pg['pages'] ?></span>
      <?php if ($pg['page'] < $pg['pages']): ?><a class="ad-btn" href="?page=<?= $pg['page'] + 1 ?>">Next</a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
<?php require APP_DIR . '/views/admin-bottom.php';
