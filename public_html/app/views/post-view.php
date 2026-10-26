<?php
/** Article page body. Expects $post, $related, $isPreview. */
[$content, $toc] = add_heading_ids((string)$post['content']);
$minutes = reading_time((string)$post['content']);
$shareUrl = abs_url('blog/' . $post['slug']);
?>
    <main class="ew-main ar" id="main">
      <?php if ($isPreview): ?>
        <div class="ar-preview" role="status">Preview. This is how the article will look<?= post_state($post) === 'published' ? '' : '. It isn’t public yet' ?>. <a href="/admin/post-edit.php?id=<?= (int)$post['id'] ?>">Back to editing</a></div>
      <?php endif; ?>
      <article class="ar-article">
        <header class="ar-head">
          <nav class="ar-crumbs" aria-label="Breadcrumb">
            <a href="<?= e(url('blog')) ?>">Blog</a>
            <?php if ($post['category_name']): ?><span aria-hidden="true">/</span><a href="<?= e(url('blog/category/' . $post['category_slug'])) ?>"><?= e($post['category_name']) ?></a><?php endif; ?>
          </nav>
          <h1><?= e($post['title']) ?></h1>
          <?php if (trim((string)$post['excerpt']) !== ''): ?><p class="ar-dek"><?= e($post['excerpt']) ?></p><?php endif; ?>
          <p class="ar-meta">
            <?php if ($post['author_name']): ?><span>By <?= e($post['author_name']) ?></span><?php endif; ?>
            <time datetime="<?= e(iso_date($post['published_at'] ?: $post['updated_at'])) ?>"><?= e(fmt_date($post['published_at'] ?: $post['updated_at'], 'F j, Y')) ?></time>
            <span><?= $minutes ?> min read</span>
          </p>
        </header>

        <?php if ($post['cover_image']): ?>
          <figure class="ar-cover"><img src="<?= e(url($post['cover_image'])) ?>" alt="<?= e($post['cover_alt']) ?>" width="1600" height="900" fetchpriority="high"></figure>
        <?php endif; ?>

        <div class="ar-body">
          <?php if (count($toc) >= 3): ?>
            <nav class="ar-toc" aria-label="In this article">
              <b>In this article</b>
              <ol><?php foreach ($toc as $t): ?><li><a href="#<?= e($t['id']) ?>"><?= e($t['text']) ?></a></li><?php endforeach; ?></ol>
            </nav>
          <?php endif; ?>
          <div class="prose ar-prose">
            <?= $content ?>
          </div>

          <div class="ar-share" aria-label="Share this article">
            <span>Share</span>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
            <a href="https://x.com/intent/post?url=<?= rawurlencode($shareUrl) ?>&amp;text=<?= rawurlencode($post['title']) ?>" target="_blank" rel="noopener noreferrer">X</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
            <button type="button" class="ar-copy" data-url="<?= e($shareUrl) ?>">Copy link</button>
          </div>

          <aside class="ar-cta" aria-labelledby="ar-cta-h">
            <h2 id="ar-cta-h">Put your own numbers in</h2>
            <p>The taxmetter calculator estimates your 2026 self-employment tax, income tax and quarterly payments in about two minutes.</p>
            <a class="btn btn--primary" href="/calculator.html">Calculate my tax</a>
          </aside>
        </div>
      </article>

      <?php if ($related): ?>
        <section class="ar-related" aria-labelledby="ar-rel-h">
          <h2 id="ar-rel-h">Keep reading</h2>
          <ul>
            <?php foreach ($related as $r): ?>
              <li>
                <a href="<?= e(post_url($r)) ?>">
                  <?php if ($r['cover_image']): ?><img src="<?= e(url($r['cover_image'])) ?>" alt="" loading="lazy" width="480" height="300"><?php else: ?><span class="bl-ph"></span><?php endif; ?>
                  <span class="ar-related__cat"><?= e($r['category_name'] ?: 'Article') ?></span>
                  <span class="ar-related__t"><?= e($r['title']) ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>
    </main>
    <script>
      document.querySelectorAll('.ar-copy').forEach(function (b) {
        b.addEventListener('click', function () {
          var done = function () { b.textContent = 'Link copied'; setTimeout(function () { b.textContent = 'Copy link'; }, 2200); };
          if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(b.dataset.url).then(done, function () { window.prompt('Copy this link:', b.dataset.url); });
          else window.prompt('Copy this link:', b.dataset.url);
        });
      });
    </script>
