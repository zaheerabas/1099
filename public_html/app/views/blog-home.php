<?php
/**
 * Blog home sections (first page of /blog only), each with its own layout:
 * featured + latest, 2026 numbers, topics, a reading path for beginners and the article index.
 * Expects $lead, $posts (the rest of page 1) and $cats from blog.php. Everything is built from published articles.
 */
[$allPosts] = public_posts(60);
$bySlug = [];
foreach ($allPosts as $p) $bySlug[$p['slug']] = $p;
$postLink = fn(string $slug): string => isset($bySlug[$slug]) ? post_url($bySlug[$slug]) : url('blog');
$arrow = '<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';

$latest = array_slice($posts, 0, 3);

// Key 2026 figures, each linked to the article that explains it. The last one is the next estimated tax due date.
$next = null;
foreach ([['Q1', '2026-04-15'], ['Q2', '2026-06-15'], ['Q3', '2026-09-15'], ['Q4', '2027-01-15']] as $d) {
    if ($d[1] >= gmdate('Y-m-d')) { $next = $d; break; }
}
$numbers = [
    ['15.3%', 'Self-employment tax, charged on 92.35% of your profit', 'self-employment-tax-explained'],
    ['$16,100', 'Standard deduction for a single filer in 2026', 'how-much-to-set-aside-for-1099-taxes'],
    ['$2,000', 'Pay from one client before you get a 1099-NEC', '1099-thresholds-2026'],
    $next
        ? [date('M j', strtotime($next[1])), 'Next estimated tax payment (' . $next[0] . ', ' . date('Y', strtotime($next[1])) . ')', 'quarterly-estimated-taxes-2026']
        : ['4', 'Estimated tax payments each year', 'quarterly-estimated-taxes-2026'],
];

// Reading path for new freelancers: these three if they exist, otherwise the three newest articles
$path = array_values(array_filter(array_map(fn($s) => $bySlug[$s] ?? null,
    ['how-much-to-set-aside-for-1099-taxes', 'self-employment-tax-explained', 'quarterly-estimated-taxes-2026'])));
if (count($path) < 3) $path = array_slice($allPosts, 0, 3);

$index = array_merge([$lead], $posts);
?>
      <section class="bh-sec bh-feature" aria-labelledby="bh-feature-h">
        <h2 class="ew-sr" id="bh-feature-h">Featured and latest</h2>
        <article class="bh-feature__main">
          <a class="bh-feature__img" href="<?= e(post_url($lead)) ?>" tabindex="-1" aria-hidden="true">
            <?= $lead['cover_image'] ? '<img src="' . e(url($lead['cover_image'])) . '" alt="" width="1200" height="750">' : cover_art($lead['category_slug'], 'lead') ?>
          </a>
          <div class="bh-feature__text">
            <span class="bl-flag">Featured</span>
            <?= post_meta($lead) ?>
            <h3><a href="<?= e(post_url($lead)) ?>"><?= e($lead['title']) ?></a></h3>
            <p><?= e(post_excerpt($lead, 200)) ?></p>
            <a class="bl-more" href="<?= e(post_url($lead)) ?>" aria-label="Read “<?= e($lead['title']) ?>”">Read the article<?= $arrow ?></a>
          </div>
        </article>
        <?php if ($latest): ?>
          <div class="bh-latest">
            <h3 class="bh-mini">Latest</h3>
            <ol>
              <?php foreach ($latest as $p): ?>
                <li>
                  <a class="bh-latest__item" href="<?= e(post_url($p)) ?>">
                    <span class="bh-latest__img"><?= $p['cover_image'] ? '<img src="' . e(url($p['cover_image'])) . '" alt="" loading="lazy" width="240" height="180">' : cover_art($p['category_slug'], 'thumb') ?></span>
                    <span class="bh-latest__text">
                      <span class="bh-latest__cat"><?= e($p['category_name'] ?: 'Article') ?></span>
                      <span class="bh-latest__t"><?= e($p['title']) ?></span>
                      <span class="bh-latest__meta"><?= e(fmt_date($p['published_at'])) ?> · <?= reading_time((string)$p['content']) ?> min read</span>
                    </span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ol>
            <a class="bh-latest__all" href="#bh-index-h">See all articles<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="m6 13 6 6 6-6"/></svg></a>
          </div>
        <?php endif; ?>
      </section>

      <section class="bh-sec bh-numbers" aria-labelledby="bh-numbers-h">
        <div class="bh-numbers__head">
          <span class="bh-eyebrow">2026 at a glance</span>
          <h2 id="bh-numbers-h">The numbers every 1099 worker should know</h2>
        </div>
        <ul>
          <?php foreach ($numbers as [$big, $label, $slug]): ?>
            <li>
              <a href="<?= e($postLink($slug)) ?>">
                <b><?= e($big) ?></b>
                <span><?= e($label) ?></span>
                <i>Read why<?= $arrow ?></i>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>

      <?php if ($cats): ?>
        <section class="bh-sec bh-topics" aria-labelledby="bh-topics-h">
          <header class="bh-head">
            <span class="bh-eyebrow">Browse by topic</span>
            <h2 id="bh-topics-h">Find the answer you need</h2>
          </header>
          <ul class="bh-topics__grid">
            <?php foreach ($cats as $c): $inCat = array_values(array_filter($allPosts, fn($p) => (int)$p['category_id'] === (int)$c['id'])); ?>
              <li class="bh-topic">
                <a class="bh-topic__art" href="<?= e(category_url($c)) ?>" tabindex="-1" aria-hidden="true"><?= cover_art($c['slug']) ?></a>
                <div class="bh-topic__body">
                  <h3><a href="<?= e(category_url($c)) ?>"><?= e($c['name']) ?></a></h3>
                  <?php if ($c['description']): ?><p><?= e($c['description']) ?></p><?php endif; ?>
                  <ul>
                    <?php foreach (array_slice($inCat, 0, 3) as $p): ?>
                      <li><a href="<?= e(post_url($p)) ?>"><?= e($p['title']) ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                  <a class="bl-more" href="<?= e(category_url($c)) ?>">All <?= (int)$c['n'] ?> <?= (int)$c['n'] === 1 ? 'article' : 'articles' ?><?= $arrow ?></a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>

      <?php if (count($path) === 3): ?>
        <section class="bh-sec bh-path" aria-labelledby="bh-path-h">
          <header class="bh-path__head">
            <span class="bh-eyebrow">New to 1099 work?</span>
            <h2 id="bh-path-h">Start here, in <span class="bl-accent">three reads.</span></h2>
            <p>About six minutes in all. By the end you’ll know what you owe, why, and when to pay it.</p>
          </header>
          <ol class="bh-path__steps">
            <?php foreach ($path as $i => $p): ?>
              <li>
                <a href="<?= e(post_url($p)) ?>">
                  <span class="bh-step"><?= $i + 1 ?></span>
                  <span class="bh-path__t"><?= e($p['title']) ?></span>
                  <span class="bh-path__x"><?= e(post_excerpt($p, 120)) ?></span>
                  <span class="bh-path__meta"><?= reading_time((string)$p['content']) ?> min read<?= $arrow ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ol>
        </section>
      <?php endif; ?>

      <section class="bh-sec bh-index" aria-labelledby="bh-index-h">
        <header class="bh-head">
          <span class="bh-eyebrow">The archive</span>
          <h2 id="bh-index-h">All articles</h2>
        </header>
        <ol class="bh-index__list">
          <?php foreach ($index as $i => $p): ?>
            <li>
              <a href="<?= e(post_url($p)) ?>">
                <span class="bh-index__n"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <span class="bh-index__main">
                  <span class="bh-index__t"><?= e($p['title']) ?></span>
                  <span class="bh-index__x"><?= e(post_excerpt($p, 140)) ?></span>
                </span>
                <span class="bh-index__cat"><?= e($p['category_name'] ?: 'Article') ?></span>
                <span class="bh-index__meta"><time datetime="<?= e(iso_date($p['published_at'])) ?>"><?= e(fmt_date($p['published_at'])) ?></time><span><?= reading_time((string)$p['content']) ?> min</span></span>
                <span class="bh-index__go" aria-hidden="true"><?= $arrow ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ol>
      </section>
