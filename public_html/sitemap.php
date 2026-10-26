<?php
/* Sitemap for search engines: /sitemap.xml (the site pages plus every published article) */
require __DIR__ . '/app/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$static = [
    ['', '1.0'], ['calculator.html', '1.0'], ['tax-guide.html', '0.8'], ['how-it-works.html', '0.7'], ['faq.html', '0.8'],
    ['blog', '0.8'], ['privacy.html', '0.2'], ['terms.html', '0.2'],
];
[$w, $params] = public_where();
$posts = q_all("SELECT p.slug, p.updated_at FROM posts p WHERE $w ORDER BY p.published_at DESC", $params);
$cats = public_categories();
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($static as [$path, $pr]): ?>
  <url><loc><?= e(abs_url($path)) ?></loc><priority><?= $pr ?></priority></url>
<?php endforeach; ?>
<?php foreach ($cats as $c): ?>
  <url><loc><?= e(abs_url('blog/category/' . $c['slug'])) ?></loc><priority>0.5</priority></url>
<?php endforeach; ?>
<?php foreach ($posts as $p): ?>
  <url><loc><?= e(abs_url('blog/' . $p['slug'])) ?></loc><lastmod><?= e(substr($p['updated_at'], 0, 10)) ?></lastmod><priority>0.7</priority></url>
<?php endforeach; ?>
</urlset>
