<?php
/* RSS feed of the latest articles: /blog/feed */
require __DIR__ . '/app/bootstrap.php';
[$posts] = public_posts(20);
header('Content-Type: application/rss+xml; charset=utf-8');
$title = (string)setting('blog_title', 'The taxmetter blog');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title><?= e($title) ?></title>
    <link><?= e(abs_url('blog')) ?></link>
    <atom:link href="<?= e(abs_url('blog/feed')) ?>" rel="self" type="application/rss+xml"/>
    <description><?= e(setting('blog_description', 'Guides to 1099 taxes for freelancers and contractors.')) ?></description>
    <language>en-us</language>
<?php foreach ($posts as $p): ?>
    <item>
      <title><?= e($p['title']) ?></title>
      <link><?= e(abs_url('blog/' . $p['slug'])) ?></link>
      <guid isPermaLink="true"><?= e(abs_url('blog/' . $p['slug'])) ?></guid>
      <pubDate><?= e(gmdate(DATE_RSS, strtotime($p['published_at'] . ' UTC'))) ?></pubDate>
      <?php if ($p['category_name']): ?><category><?= e($p['category_name']) ?></category><?php endif; ?>
      <description><?= e(post_excerpt($p, 300)) ?></description>
    </item>
<?php endforeach; ?>
  </channel>
</rss>
