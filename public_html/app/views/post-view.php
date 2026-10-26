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

        <div class="ar-body ar-body--side<?= count($toc) >= 3 ? ' ar-body--toc' : '' ?>">
          <?php if (count($toc) >= 3): ?>
            <nav class="ar-toc" aria-label="In this article">
              <b>In this article</b>
              <div class="ar-toc__list">
                <span class="ar-toc__bar" aria-hidden="true"><i></i></span>
                <ol><?php foreach ($toc as $t): ?><li><a href="#<?= e($t['id']) ?>"><?= e($t['text']) ?></a></li><?php endforeach; ?></ol>
              </div>
              <p class="ar-toc__left" aria-hidden="true" data-left="<?= $minutes ?>"><?= $minutes ?> min read</p>
              <div class="ar-toc__share">
                <span>Share</span>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Share on LinkedIn"><svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.5h4v11H3zM9.5 9.5h3.8v1.6h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1v5.35h-4v-4.74c0-1.13-.02-2.59-1.58-2.59-1.58 0-1.82 1.23-1.82 2.5v4.83h-4z"/></svg></a>
                <a href="https://x.com/intent/post?url=<?= rawurlencode($shareUrl) ?>&amp;text=<?= rawurlencode($post['title']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Share on X"><svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.75 3h3.07l-6.7 7.66L22 21h-6.17l-4.83-6.32L5.47 21H2.4l7.17-8.2L2 3h6.33l4.37 5.77zm-1.08 16.17h1.7L7.4 4.74H5.58z"/></svg></a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Share on Facebook"><svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.5h2.6l.4-3h-3V8.6c0-.87.25-1.46 1.5-1.46h1.6V4.45A21 21 0 0 0 14.27 4.3c-2.3 0-3.87 1.4-3.87 3.98v2.22H7.8v3h2.6V21z"/></svg></a>
                <button type="button" class="ar-copy ar-copy--icon" data-url="<?= e($shareUrl) ?>" aria-label="Copy link"><svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14a4.5 4.5 0 0 0 6.4 0l3.2-3.2a4.5 4.5 0 0 0-6.4-6.4L12 5.6"/><path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3.2 3.2a4.5 4.5 0 0 0 6.4 6.4l1.2-1.2"/></svg></button>
              </div>
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

          <aside class="ar-side" aria-label="Tools and more articles">
            <?php if ($related): ?>
              <section class="ar-widget ar-more" aria-labelledby="ar-more-h">
                <h2 id="ar-more-h"><?= $post['category_name'] ? 'More on ' . e(mb_strtolower($post['category_name'])) : 'More articles' ?></h2>
                <ol>
                  <?php foreach (array_slice($related, 0, 3) as $i => $r): ?>
                    <li><a href="<?= e(post_url($r)) ?>"><span><?= $i + 1 ?></span><?= e($r['title']) ?></a></li>
                  <?php endforeach; ?>
                </ol>
              </section>
            <?php endif; ?>
            <div class="ar-side__stick">
              <section class="ar-widget ar-est" aria-labelledby="ar-est-h" data-est>
                <h2 id="ar-est-h">Quick estimate</h2>
                <div class="ar-est__in">
                  <label for="ar-est-r">Your 1099 income in 2026</label>
                  <output for="ar-est-r" data-est-inc>$60,000</output>
                </div>
                <input id="ar-est-r" type="range" min="5000" max="250000" step="1000" value="60000" data-est-range>
                <dl class="ar-est__out">
                  <div><dt>Federal tax for the year</dt><dd data-est-total>$12,037</dd></div>
                  <div><dt>Pay each quarter</dt><dd data-est-q>$3,009</dd></div>
                  <div class="ar-est__bar"><dt>Set aside from each payment</dt><dd data-est-pct>20%</dd><span aria-hidden="true"><i data-est-fill style="width:20%"></i></span></div>
                </dl>
                <a class="bl-primary ar-est__go" href="/calculator.html?status=single&amp;nec=60000" data-est-link>Open full calculator<span class="bl-primary__ico"><svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></span></a>
                <p class="ar-est__note">Single filer, no expenses, 2026 IRS figures. Add expenses and more in the full calculator.</p>
              </section>

              <section class="ar-widget ar-due" aria-labelledby="ar-due-h" data-due>
                <h2 id="ar-due-h">Next estimated payment</h2>
                <ol class="ar-due__qs">
                  <?php foreach ([['Q1', '2026-04-15'], ['Q2', '2026-06-15'], ['Q3', '2026-09-15'], ['Q4', '2027-01-15']] as [$q, $d]): ?>
                    <li data-date="<?= $d ?>"><b><?= $q ?></b><span><?= date('M j', strtotime($d)) ?></span></li>
                  <?php endforeach; ?>
                </ol>
                <p class="ar-due__next" data-due-text>Four payments a year: April 15, June 15, September 15 and January 15.</p>
                <a class="bl-more" href="<?= e(url('blog/quarterly-estimated-taxes-2026')) ?>">How quarterly taxes work<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></a>
              </section>
            </div>
          </aside>
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
    <script src="/assets/js/tax-engine.js?v=6"></script>
    <script>
      // Sidebar quick estimate, using the same tax maths as the calculator
      (function () {
        var box = document.querySelector('[data-est]');
        if (!box || !window.TMTax) return;
        var range = box.querySelector('[data-est-range]');
        var money = function (n) { return '$' + Math.round(n).toLocaleString('en-US'); };
        var out = function (sel) { return box.querySelector(sel); };
        var update = function () {
          var inc = +range.value;
          var r = TMTax.calculate({ status: 'single', nec: inc, k: 0, misc: 0, int: 0, exp: 0, sqft: 0, health: 0, mi1: 0, mi2: 0, w2: 0, wh: 0, paid: 0, ret: 0, prior: 0, hi: false });
          var pct = inc > 0 ? r.total / inc : 0;
          out('[data-est-inc]').textContent = money(inc);
          out('[data-est-total]').textContent = money(r.total);
          out('[data-est-q]').textContent = money(r.total / 4);
          out('[data-est-pct]').textContent = Math.round(pct * 100) + '%';
          out('[data-est-fill]').style.width = Math.min(100, pct * 100 / 0.4) + '%';
          out('[data-est-link]').href = '/calculator.html?status=single&nec=' + inc;
          range.style.setProperty('--fill', ((inc - range.min) / (range.max - range.min) * 100) + '%');
        };
        range.addEventListener('input', update);
        update();
      })();
      // Next estimated tax payment, worked out from today's date
      (function () {
        var box = document.querySelector('[data-due]');
        if (!box) return;
        var today = new Date(); today.setHours(0, 0, 0, 0);
        var items = [].slice.call(box.querySelectorAll('[data-date]'));
        var next = null;
        items.forEach(function (li) {
          var d = new Date(li.getAttribute('data-date') + 'T00:00:00');
          if (d < today) li.classList.add('is-past');
          else if (!next) { next = { li: li, d: d }; li.classList.add('is-next'); }
        });
        var text = box.querySelector('[data-due-text]');
        if (!next) { text.textContent = 'All 2026 payments are due. Check the new year’s dates in the tax guide.'; return; }
        var days = Math.round((next.d - today) / 86400000);
        var when = next.d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        text.innerHTML = '<b>' + (days === 0 ? 'Due today' : days + (days === 1 ? ' day' : ' days') + ' left') + '</b>' + next.li.querySelector('b').textContent + ' is due ' + when + '.';
      })();
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
          var icon = b.classList.contains('ar-copy--icon');
          var done = function () {
            if (window.TMToast) window.TMToast('Link copied');
            if (icon) { b.classList.add('is-done'); b.setAttribute('aria-label', 'Link copied'); setTimeout(function () { b.classList.remove('is-done'); b.setAttribute('aria-label', 'Copy link'); }, 2200); return; }
            b.textContent = 'Link copied'; setTimeout(function () { b.textContent = 'Copy link'; }, 2200);
          };
          if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(b.dataset.url).then(done, function () { window.prompt('Copy this link:', b.dataset.url); });
          else window.prompt('Copy this link:', b.dataset.url);
        });
      });
    </script>
