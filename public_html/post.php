<?php
/* Single article: /blog/{slug} */
require __DIR__ . '/app/bootstrap.php';

$slug = (string)($_GET['slug'] ?? '');
if (!preg_match('/^[a-z0-9-]{1,190}$/', $slug)) render_404();
$post = public_post_by_slug($slug);
if (!$post) render_404();

// Count a view (skip obvious bots)
$ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
if ($ua !== '' && !preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit/i', $ua)) {
    q('UPDATE posts SET views = views + 1 WHERE id = ?', [(int)$post['id']]);
}

$related = related_posts($post);
$isPreview = false;
require APP_DIR . '/views/post-page.php';
