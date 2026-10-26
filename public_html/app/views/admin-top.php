<?php
/** Admin layout top. Expects $title and $active (menu key). */
$u = current_user();
$flashes = take_flashes();
$nav = [
    'dashboard' => ['/admin/', 'Dashboard', '<path d="M4 13h6V4H4zM14 20h6v-9h-6zM4 20h6v-4H4zM14 8h6V4h-6z"/>'],
    'posts' => ['/admin/posts.php', 'Articles', '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h7M9 17h5"/>'],
    'new' => ['/admin/post-edit.php', 'New article', '<path d="M12 5v14M5 12h14"/>'],
    'categories' => ['/admin/categories.php', 'Categories', '<path d="M4 6h16M4 12h10M4 18h6"/>'],
    'media' => ['/admin/media.php', 'Media', '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/>'],
];
if (is_admin()) {
    $nav['users'] = ['/admin/users.php', 'Users', '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/>'];
    $nav['settings'] = ['/admin/settings.php', 'Settings', '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>'];
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> | taxmetter admin</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preload" href="/assets/fonts/public-sans-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/admin/assets/admin.css?v=1">
<?= $headExtra ?? '' ?>
</head>
<body class="ad">
<a class="ad-skip" href="#ad-main">Skip to content</a>
<div class="ad-shell">
  <aside class="ad-side" id="ad-side" aria-label="Admin menu">
    <a class="ad-brand" href="/admin/" aria-label="taxmetter admin home">
      <span class="ad-brand__mark"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 15a7.5 7.5 0 1 1 15 0"/><path d="M12 15l3.4-4.6"/><circle cx="12" cy="15" r="1.6" fill="#fff" stroke="none"/></svg></span>
      <span>tax<b>metter</b> <small>admin</small></span>
    </a>
    <nav aria-label="Admin">
      <ul>
        <?php foreach ($nav as $key => [$href, $label, $icon]): ?>
          <li><a href="<?= e($href) ?>"<?= ($active ?? '') === $key ? ' aria-current="page"' : '' ?>><svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><?= $icon ?></svg><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="ad-side__foot">
      <a href="/blog" target="_blank" rel="noopener">View blog</a>
      <a href="/admin/profile.php"<?= ($active ?? '') === 'profile' ? ' aria-current="page"' : '' ?>><?= e($u['name']) ?></a>
      <form method="post" action="/admin/logout.php"><?= csrf_field() ?><button type="submit">Log out</button></form>
    </div>
  </aside>
  <div class="ad-body">
    <header class="ad-top">
      <button type="button" class="ad-menu" aria-controls="ad-side" aria-expanded="false" aria-label="Open menu"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      <h1><?= e($title) ?></h1>
      <div class="ad-top__actions"><?= $topActions ?? '' ?></div>
    </header>
    <main class="ad-main" id="ad-main" tabindex="-1">
      <?php foreach ($flashes as $f): ?>
        <div class="ad-flash ad-flash--<?= e($f['type']) ?>" role="<?= $f['type'] === 'error' ? 'alert' : 'status' ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
