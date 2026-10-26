<?php
/* Latest articles for the home page: /blog/latest.json */
require __DIR__ . '/app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');
[$posts] = public_posts(3);
echo json_encode(['posts' => array_map(fn($p) => [
    'title' => $p['title'],
    'url' => post_url($p),
    'excerpt' => post_excerpt($p, 160),
    'category' => $p['category_name'],
    'date' => fmt_date($p['published_at']),
    'minutes' => reading_time((string)$p['content']),
    'cover' => $p['cover_image'] ? url($p['cover_image']) : null,
], $posts)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
