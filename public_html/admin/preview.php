<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require_login();
$post = post_by_id((int)($_GET['id'] ?? 0));
if (!$post) render_404();
header('X-Robots-Tag: noindex');
$related = [];
$isPreview = true;
require APP_DIR . '/views/post-page.php';
