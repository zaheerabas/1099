<?php
/* Blog listing: /blog, /blog/category/{slug}, /blog?q=search, ?page=N */
require __DIR__ . '/app/bootstrap.php';

$catSlug = isset($_GET['category']) ? (string)$_GET['category'] : '';
$search = trim(mb_substr((string)($_GET['q'] ?? ''), 0, 80));
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$per = max(3, min(30, (int)setting('posts_per_page', 9)));

$category = null;
if ($catSlug !== '') {
    $category = q_one('SELECT * FROM categories WHERE slug = ?', [$catSlug]);
    if (!$category) render_404();
}

$total = public_posts(1, 0, $category['id'] ?? null, $search)[1];
$pg = paginate($total, $per, $pageNo);
[$posts] = public_posts($per, $pg['offset'], $category['id'] ?? null, $search);
$cats = public_categories();

$basePath = $category ? category_url($category) : url('blog');
$isHome = !$category && $search === '' && $pg['page'] === 1;
$lead = $isHome && $posts ? array_shift($posts) : null;

$blogTitle = (string)setting('blog_title', 'The taxmetter blog');
$blogIntro = (string)setting('blog_description', 'Plain-English guides to 1099 taxes, deductions and quarterly payments for freelancers and contractors.');

if ($category) {
    $h1 = $category['name'];
    $intro = $category['description'] ?: "Articles about {$category['name']} from the taxmetter blog.";
    $title = $category['name'] . ' | ' . $blogTitle;
} elseif ($search !== '') {
    $h1 = 'Search results';
    $intro = $total === 1 ? '1 article matches “' . $search . '”.' : $total . ' articles match “' . $search . '”.';
    $title = 'Search: ' . $search . ' | ' . $blogTitle;
} else {
    $h1 = $blogTitle;
    $intro = $blogIntro;
    $title = $blogTitle . ' | taxmetter';
}
if ($pg['page'] > 1) $title = 'Page ' . $pg['page'] . ' | ' . $title;

$page = [
    'title' => $title,
    'description' => $intro,
    'canonical' => abs_url(ltrim(with_query($basePath, ['page' => $pg['page']]), '/')),
    'noindex' => $search !== '',
    'current' => 'blog',
    'css' => ['blog.css'],
    'head' => '<link rel="alternate" type="application/rss+xml" title="' . e($blogTitle) . '" href="' . e(abs_url('blog/feed')) . '">',
];
require APP_DIR . '/views/layout-top.php';

function post_meta(array $p): string
{
    $bits = [];
    if ($p['category_name']) $bits[] = '<a class="bl-cat" href="' . e(url('blog/category/' . $p['category_slug'])) . '">' . e($p['category_name']) . '</a>';
    $bits[] = '<time datetime="' . e(iso_date($p['published_at'])) . '">' . e(fmt_date($p['published_at'])) . '</time>';
    $bits[] = '<span>' . reading_time((string)$p['content']) . ' min read</span>';
    return '<p class="bl-meta">' . implode('', $bits) . '</p>';
}
?>
    <main class="ew-main bl" id="main">
      <header class="bl-intro<?= $isHome ? '' : ' bl-intro--sub' ?>">
        <?php if ($category): ?><a class="bl-back" href="<?= e(url('blog')) ?>">All articles</a><?php endif; ?>
        <h1><?= e($h1) ?></h1>
        <p><?= e($intro) ?></p>
      </header>

      <div class="bl-tools">
        <nav class="bl-cats" aria-label="Categories">
          <a href="<?= e(url('blog')) ?>"<?= !$category && $search === '' ? ' aria-current="page"' : '' ?>>All</a>
          <?php foreach ($cats as $c): ?>
            <a href="<?= e(category_url($c)) ?>"<?= ($category['id'] ?? 0) == $c['id'] ? ' aria-current="page"' : '' ?>><?= e($c['name']) ?></a>
          <?php endforeach; ?>
        </nav>
        <form class="bl-search" role="search" action="<?= e(url('blog')) ?>" method="get">
          <label class="ew-sr" for="bl-q">Search articles</label>
          <input id="bl-q" type="search" name="q" value="<?= e($search) ?>" placeholder="Search articles" maxlength="80">
          <button type="submit" aria-label="Search"><svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
        </form>
      </div>

      <?php if (!$lead && !$posts): ?>
        <div class="bl-empty glass">
          <?php if ($search !== ''): ?>
            <h2>No articles match “<?= e($search) ?>”</h2>
            <p>Try a shorter word, such as “deduction” or “quarterly”.</p>
            <a class="btn btn--quiet" href="<?= e(url('blog')) ?>">See all articles</a>
          <?php else: ?>
            <h2>No articles here yet</h2>
            <p>New guides are on the way. Meanwhile, the calculator can estimate your 2026 tax.</p>
            <a class="btn btn--primary" href="/calculator.html">Open the calculator</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($lead): ?>
        <article class="bl-lead">
          <a class="bl-lead__img" href="<?= e(post_url($lead)) ?>" tabindex="-1" aria-hidden="true">
            <?php if ($lead['cover_image']): ?><img src="<?= e(url($lead['cover_image'])) ?>" alt="" width="1200" height="750"><?php else: ?><span class="bl-ph"></span><?php endif; ?>
          </a>
          <div class="bl-lead__text">
            <?= post_meta($lead) ?>
            <h2><a href="<?= e(post_url($lead)) ?>"><?= e($lead['title']) ?></a></h2>
            <p><?= e(post_excerpt($lead, 220)) ?></p>
            <a class="bl-more" href="<?= e(post_url($lead)) ?>" aria-label="Read “<?= e($lead['title']) ?>”">Read the article</a>
          </div>
        </article>
      <?php endif; ?>

      <?php if ($posts): ?>
        <ul class="bl-list">
          <?php foreach ($posts as $p): ?>
            <li>
              <article class="bl-row">
                <div class="bl-row__text">
                  <?= post_meta($p) ?>
                  <h2><a href="<?= e(post_url($p)) ?>"><?= e($p['title']) ?></a></h2>
                  <p><?= e(post_excerpt($p)) ?></p>
                </div>
                <a class="bl-row__img" href="<?= e(post_url($p)) ?>" tabindex="-1" aria-hidden="true">
                  <?php if ($p['cover_image']): ?><img src="<?= e(url($p['cover_image'])) ?>" alt="" loading="lazy" width="480" height="300"><?php else: ?><span class="bl-ph"></span><?php endif; ?>
                </a>
              </article>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php if ($pg['pages'] > 1): ?>
        <nav class="bl-pages" aria-label="Pages">
          <?php if ($pg['page'] > 1): ?><a rel="prev" href="<?= e(with_query($basePath, ['q' => $search, 'page' => $pg['page'] - 1])) ?>">Newer</a><?php endif; ?>
          <span>Page <?= $pg['page'] ?> of <?= $pg['pages'] ?></span>
          <?php if ($pg['page'] < $pg['pages']): ?><a rel="next" href="<?= e(with_query($basePath, ['q' => $search, 'page' => $pg['page'] + 1])) ?>">Older</a><?php endif; ?>
        </nav>
      <?php endif; ?>

      <section class="bl-cta glass" aria-labelledby="bl-cta-h">
        <div>
          <h2 id="bl-cta-h">See what you owe for 2026</h2>
          <p>Free, no sign-up, and your numbers stay on your device.</p>
        </div>
        <a class="btn btn--primary" href="/calculator.html">Calculate my tax</a>
      </section>
    </main>
<?php require APP_DIR . '/views/layout-bottom.php';
