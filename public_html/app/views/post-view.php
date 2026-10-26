<?php
/** Article page body. Expects $post, $related, $isPreview. */
[$content, $toc] = add_heading_ids((string)$post['content']);
$minutes = reading_time((string)$post['content']);
$shareUrl = abs_url('blog/' . $post['slug']);
$adjacent = $isPreview || !$post['published_at'] ? ['prev' => null, 'next' => null] : adjacent_posts($post);
?>
    <main class="ew-main ar" id="main">
      <?php if ($isPreview): ?>
        <div class="ar-preview" role="status">Preview. This is how the article will look<?= post_state($post) === 'published' ? '' : '. It isn’t public yet' ?>. <a href="/admin/post-edit.php?id=<?= (int)$post['id'] ?>">Back to editing</a></div>
      <?php endif; ?>
      <article class="ar-article">
        <header class="ar-head">
          <nav class="ar-crumbs" aria-label="Breadcrumb">
            <a href="<?= e(url('blog')) ?>">Blog</a>
            <?php if ($post['category_name']): ?><svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg><a href="<?= e(url('blog/category/' . $post['category_slug'])) ?>"><?= e($post['category_name']) ?></a><?php endif; ?>
          </nav>
          <h1><?= e($post['title']) ?></h1>
          <?php if (trim((string)$post['excerpt']) !== ''): ?><p class="ar-dek"><?= e($post['excerpt']) ?></p><?php endif; ?>
          <p class="ar-meta">
            <?php if ($post['author_name']): ?><span class="ar-author"><span class="ar-avatar" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 15a7.5 7.5 0 1 1 15 0"/><path d="M12 15l3.4-4.6"/></svg></span>By <?= e($post['author_name']) ?></span><?php endif; ?>
            <time datetime="<?= e(iso_date($post['published_at'] ?: $post['updated_at'])) ?>"><?= e(fmt_date($post['published_at'] ?: $post['updated_at'], 'F j, Y')) ?></time>
            <span><?= $minutes ?> min read</span>
          </p>
        </header>

        <?php if ($post['cover_image']): ?>
          <figure class="ar-cover"><img src="<?= e(url($post['cover_image'])) ?>" alt="<?= e($post['cover_alt']) ?>" width="1600" height="900" fetchpriority="high"></figure>
        <?php else: ?>
          <div class="ar-cover ar-cover--art"><?= cover_art($post['category_slug'], 'wide') ?></div>
        <?php endif; ?>

        <div class="ar-body<?= count($toc) >= 3 ? ' ar-body--toc' : '' ?>">
          <?php if (count($toc) >= 3): ?>
            <nav class="ar-toc" aria-label="In this article">
              <b>In this article</b>
              <span class="ar-toc__bar" aria-hidden="true"><i></i></span>
              <ol><?php foreach ($toc as $t): ?><li><a href="#<?= e($t['id']) ?>"><?= e($t['text']) ?></a></li><?php endforeach; ?></ol>
            </nav>
          <?php endif; ?>
          <div class="prose ar-prose">
            <?= $content ?>
          </div>

          <footer class="ar-end">
            <div class="ar-byline">
              <span class="ar-avatar ar-avatar--lg" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 15a7.5 7.5 0 1 1 15 0"/><path d="M12 15l3.4-4.6"/><circle cx="12" cy="15" r="1.6" fill="#fff" stroke="none"/></svg></span>
              <div>
                <b>Written by <?= e($post['author_name'] ?: 'taxmetter') ?></b>
                <p>Plain-English guides for freelancers and contractors, checked against 2026 IRS figures. Updated <?= e(fmt_date($post['updated_at'] ?: $post['published_at'], 'F j, Y')) ?>.</p>
              </div>
            </div>
            <div class="ar-share" aria-label="Share this article">
              <span>Share this article</span>
              <div>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                <a href="https://x.com/intent/post?url=<?= rawurlencode($shareUrl) ?>&amp;text=<?= rawurlencode($post['title']) ?>" target="_blank" rel="noopener noreferrer">X</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
                <button type="button" class="ar-copy" data-url="<?= e($shareUrl) ?>">Copy link</button>
              </div>
            </div>
          </footer>
        </div>
      </article>

      <section class="ar-band" aria-labelledby="ar-band-h">
        <div class="ar-band__text">
          <span class="bh-eyebrow">Free 1099 calculator</span>
          <h2 id="ar-band-h">Put your own numbers in</h2>
          <p>Estimate your 2026 self-employment tax, income tax and quarterly payments in about two minutes. No sign-up, and your numbers stay on your device.</p>
          <a href="/calculator.html" class="bl-primary bl-primary--light">Calculate my tax<span class="bl-primary__ico"><svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></span></a>
        </div>
        <div class="ar-band__card" aria-hidden="true">
          <span class="ar-band__label">Example: $60,000 on 1099-NEC, single</span>
          <b class="ar-band__big">$12,037</b>
          <span class="ar-band__sub">Estimated federal tax for 2026</span>
          <span class="ar-band__bars"><i style="--h:.55"></i><i style="--h:.7"></i><i style="--h:.85"></i><i style="--h:1"></i></span>
          <span class="ar-band__row"><span>Self-employment tax</span><b>$8,478</b></span>
          <span class="ar-band__row"><span>Income tax</span><b>$3,559</b></span>
          <span class="ar-band__row ar-band__row--hl"><span>Pay each quarter</span><b>$3,009</b></span>
        </div>
      </section>

      <?php if ($adjacent['prev'] || $adjacent['next']): ?>
        <nav class="ar-pn" aria-label="More articles">
          <?php foreach (['prev' => 'Previous article', 'next' => 'Next article'] as $k => $label): $a = $adjacent[$k]; ?>
            <?php if ($a): ?>
              <a class="ar-pn__item ar-pn__item--<?= $k ?>" href="<?= e(post_url($a)) ?>">
                <span class="ar-pn__art"><?= $a['cover_image'] ? '<img src="' . e(url($a['cover_image'])) . '" alt="" loading="lazy" width="240" height="180">' : cover_art($a['category_slug'], 'thumb') ?></span>
                <span class="ar-pn__text">
                  <span class="ar-pn__dir"><?= $label ?></span>
                  <span class="ar-pn__t"><?= e($a['title']) ?></span>
                </span>
              </a>
            <?php else: ?>
              <span class="ar-pn__item ar-pn__item--empty" aria-hidden="true"></span>
            <?php endif; ?>
          <?php endforeach; ?>
        </nav>
      <?php endif; ?>

      <?php if ($related): ?>
        <section class="ar-related" aria-labelledby="ar-rel-h">
          <h2 id="ar-rel-h">Keep reading</h2>
          <ul>
            <?php foreach ($related as $r): ?>
              <li>
                <a href="<?= e(post_url($r)) ?>">
                  <?= $r['cover_image'] ? '<img src="' . e(url($r['cover_image'])) . '" alt="" loading="lazy" width="480" height="300">' : cover_art($r['category_slug']) ?>
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
      // Label table cells with their column heading, so tables can stack as cards on phones
      document.querySelectorAll('.ar-prose table').forEach(function (t) {
        var heads = [].map.call(t.querySelectorAll('thead th'), function (th) { return th.textContent.trim(); });
        if (!heads.length) return;
        t.classList.add('has-labels');
        t.querySelectorAll('tbody tr').forEach(function (tr) {
          [].forEach.call(tr.children, function (td, i) { if (heads[i]) td.setAttribute('data-label', heads[i]); });
        });
      });
      (function () {
        var links = [].slice.call(document.querySelectorAll('.ar-toc a'));
        var bar = document.querySelector('.ar-toc__bar i');
        if (!links.length) return;
        var heads = links.map(function (a) { return document.getElementById(a.hash.slice(1)); }).filter(Boolean);
        var mark = function (i) {
          links.forEach(function (a, j) { a.classList.toggle('is-on', j === i); });
          if (bar) bar.style.transform = 'scaleY(' + ((i + 1) / links.length) + ')';
        };
        var ticking = false;
        var update = function () {
          ticking = false;
          var cur = 0;
          heads.forEach(function (h, i) { if (h.getBoundingClientRect().top < window.innerHeight * 0.4) cur = i; });
          mark(cur);
        };
        window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
        update();
      })();
      document.querySelectorAll('.ar-copy').forEach(function (b) {
        b.addEventListener('click', function () {
          var done = function () { b.textContent = 'Link copied'; setTimeout(function () { b.textContent = 'Copy link'; }, 2200); };
          if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(b.dataset.url).then(done, function () { window.prompt('Copy this link:', b.dataset.url); });
          else window.prompt('Copy this link:', b.dataset.url);
        });
      });
    </script>
