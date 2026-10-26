<?php
/** Head + structured data for an article, then the article body. Used by post.php and the admin preview. */
$canonical = abs_url('blog/' . $post['slug']);
$desc = trim((string)$post['seo_description']) ?: post_excerpt($post, 155);
$image = $post['cover_image'] ? abs_url($post['cover_image']) : null;
$ld = [
    '@context' => 'https://schema.org',
    '@graph' => [
        array_filter([
            '@type' => 'BlogPosting',
            'headline' => $post['title'],
            'description' => $desc,
            'image' => $image,
            'datePublished' => iso_date($post['published_at']),
            'dateModified' => iso_date($post['updated_at']),
            'author' => $post['author_name'] ? ['@type' => 'Person', 'name' => $post['author_name']] : null,
            'publisher' => ['@type' => 'Organization', 'name' => 'taxmetter', 'logo' => ['@type' => 'ImageObject', 'url' => abs_url('assets/img/icon-512.png')]],
            'mainEntityOfPage' => $canonical,
        ]),
        ['@type' => 'BreadcrumbList', 'itemListElement' => array_values(array_filter([
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Blog', 'item' => abs_url('blog')],
            $post['category_name'] ? ['@type' => 'ListItem', 'position' => 2, 'name' => $post['category_name'], 'item' => abs_url('blog/category/' . $post['category_slug'])] : null,
            ['@type' => 'ListItem', 'position' => $post['category_name'] ? 3 : 2, 'name' => $post['title'], 'item' => $canonical],
        ]))],
    ],
];
$page = [
    'title' => (trim((string)$post['seo_title']) ?: $post['title']) . ' | taxmetter',
    'description' => $desc,
    'canonical' => $canonical,
    'image' => $image,
    'image_alt' => $post['cover_alt'] ?: $post['title'],
    'type' => 'article',
    'noindex' => $isPreview,
    'current' => 'blog',
    'css' => ['blog.css'],
    'js' => ['blog.js'],
    'head' => '<meta property="article:published_time" content="' . e(iso_date($post['published_at'])) . '">'
        . "\n" . '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . '</script>',
];
require APP_DIR . '/views/layout-top.php';
require APP_DIR . '/views/post-view.php';
require APP_DIR . '/views/layout-bottom.php';
