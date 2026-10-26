<?php
/**
 * Page layout for the PHP (blog) pages. Generated from the same header as the static pages, so they always match.
 * Pages set $page = [title, description, canonical, image, type, noindex, current, css, head] before including this file.
 */
$page += ['title' => 'taxmetter', 'description' => '', 'canonical' => null, 'image' => null, 'type' => 'website', 'noindex' => false, 'current' => '', 'css' => [], 'head' => ''];
$ogImage = $page['image'] ?: abs_url('assets/img/og-image.png');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($page['title']) ?></title>
<meta name="description" content="<?= e($page['description']) ?>">
<?php if ($page['noindex']): ?><meta name="robots" content="noindex">
<?php else: ?><meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
<?php if ($page['canonical']): ?><link rel="canonical" href="<?= e($page['canonical']) ?>">
<?php endif; endif; ?>
<meta property="og:type" content="<?= e($page['type']) ?>">
<meta property="og:site_name" content="taxmetter">
<meta property="og:title" content="<?= e($page['title']) ?>">
<meta property="og:description" content="<?= e($page['description']) ?>">
<?php if ($page['canonical']): ?><meta property="og:url" content="<?= e($page['canonical']) ?>">
<?php endif; ?>
<meta property="og:image" content="<?= e($ogImage) ?>">
<?php if (!$page['image']): ?><meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif; ?><meta property="og:image:alt" content="<?= e($page['image_alt'] ?? 'taxmetter: free 1099 tax calculator for 2026') ?>">
<meta property="og:locale" content="en_US">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($page['title']) ?>">
<meta name="twitter:description" content="<?= e($page['description']) ?>">
<meta name="theme-color" content="#f4f8f8">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/assets/img/favicon-32.png" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="preload" href="/assets/fonts/public-sans-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/site.css?v=6">
<?php foreach ($page['js'] ?? [] as $js): ?><script src="/assets/js/<?= e($js) ?>?v=6" defer></script>
<?php endforeach; ?>
<?php foreach ($page['css'] as $css): ?><link rel="stylesheet" href="/assets/css/<?= e($css) ?>?v=6">
<?php endforeach; ?>
<?= $page['head'] ?>

<script src="/assets/js/site.js?v=6" defer></script>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<div class="ew-hero" id="ew-hero" data-ew-theme="tax">
  <div class="ew-page">

    <svg class="ew-side ew-side--l" data-ew aria-hidden="true" viewBox="0 0 260 900" preserveAspectRatio="none">
      <defs>
        <linearGradient id="ew-sideL" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0" style="stop-color:var(--ew-deco-1)"/><stop offset="1" style="stop-color:var(--ew-deco-2)"/>
        </linearGradient>
      </defs>
      <path d="M0 120 C120 170 150 300 110 430 C70 560 150 700 260 900 L0 900 Z" fill="url(#ew-sideL)"/>
      <path d="M0 260 C80 300 92 400 62 500 C34 600 88 720 170 900 L0 900 Z" opacity=".7" style="fill:var(--ew-deco-3)"/>
    </svg>
    <svg class="ew-side ew-side--r" data-ew aria-hidden="true" viewBox="0 0 260 900" preserveAspectRatio="none">
      <defs>
        <linearGradient id="ew-sideR" x1="1" y1="0" x2="0" y2="1">
          <stop offset="0" style="stop-color:var(--ew-deco-1)"/><stop offset="1" style="stop-color:var(--ew-deco-2)"/>
        </linearGradient>
      </defs>
      <path d="M260 120 C140 170 110 300 150 430 C190 560 110 700 0 900 L260 900 Z" fill="url(#ew-sideR)"/>
      <path d="M260 260 C180 300 168 400 198 500 C226 600 172 720 90 900 L260 900 Z" opacity=".7" style="fill:var(--ew-deco-3)"/>
    </svg>


    <header class="ew-header">
      <div class="ew-header__inner" data-ew>
        <a href="/" class="ew-brand" aria-label="taxmetter home">
          <span class="ew-brand__mark"><svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 15a7.5 7.5 0 1 1 15 0"/><path d="M7.2 9.9l.9.8M12 6.9v1.2M16.8 9.9l-.9.8" stroke-width="1.6" opacity=".7"/><path class="ew-needle" d="M12 15l3.4-4.6"/><circle cx="12" cy="15" r="1.6" fill="#fff" stroke="none"/></svg></span>
          <span aria-hidden="true">tax<span class="ew-brand__accent">metter</span></span>
        </a>

        <nav class="ew-menu" id="ew-menu" aria-label="Main">
          <div class="ew-nav">
            <span class="ew-nav__pill" aria-hidden="true"></span>
            <a href="/calculator.html" style="--i:0"<?= ($page['current'] ?? '') === 'calculator.html' ? ' aria-current="page"' : '' ?>>1099 calculator<svg class="ew-nav__chev" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg></a>
            <a href="/how-it-works.html" style="--i:1"<?= ($page['current'] ?? '') === 'how-it-works.html' ? ' aria-current="page"' : '' ?>>How it works<svg class="ew-nav__chev" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg></a>
            <a href="/tax-guide.html" style="--i:2"<?= ($page['current'] ?? '') === 'tax-guide.html' ? ' aria-current="page"' : '' ?>>Tax guide<svg class="ew-nav__chev" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg></a>
            <a href="/faq.html" style="--i:3"<?= ($page['current'] ?? '') === 'faq.html' ? ' aria-current="page"' : '' ?>>FAQ<svg class="ew-nav__chev" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg></a>
            <a href="/blog" style="--i:4"<?= ($page['current'] ?? '') === 'blog' ? ' aria-current="page"' : '' ?>>Blog<svg class="ew-nav__chev" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg></a>
          </div>
        </nav>

        <div class="ew-actions">
          <a href="/calculator.html" class="ew-signup"><span class="lg">Calculate my tax</span><span class="sm">Calculate</span><svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></a>
          <button type="button" class="ew-burger" aria-label="Open menu" aria-expanded="false" aria-controls="ew-menu">
            <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" style="stroke:var(--ew-text)"><path class="ew-b1" d="M4 7h16"/><path class="ew-b2" d="M4 12h16"/><path class="ew-b3" d="M4 17h16"/></svg>
          </button>
        </div>
      </div>
    </header>
    <div class="ew-header-spacer" aria-hidden="true"></div>

