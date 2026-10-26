<?php
declare(strict_types=1);

/**
 * Cleans article HTML from the editor so only safe formatting is stored.
 * Anything not on the allow list is removed; scripts, styles, iframes and event handlers never survive.
 */
function sanitize_html(string $html): string
{
    $html = trim($html);
    if ($html === '') return '';

    $allowed = [
        'p' => [], 'br' => [], 'h2' => ['id'], 'h3' => ['id'], 'h4' => ['id'],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'sub' => [], 'sup' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'ul' => [], 'ol' => ['start'], 'li' => [],
        'blockquote' => [], 'pre' => [], 'code' => [], 'hr' => [],
        'img' => ['src', 'alt', 'width', 'height', 'title'],
        'figure' => [], 'figcaption' => [],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => ['colspan', 'rowspan', 'scope'], 'td' => ['colspan', 'rowspan'],
        'span' => [],
    ];
    $rename = ['h1' => 'h2', 'h5' => 'h4', 'h6' => 'h4', 'div' => 'p'];
    $drop = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'link', 'meta', 'svg', 'math', 'template', 'noscript', 'video', 'audio', 'canvas', 'base', 'frame', 'frameset'];

    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();
    $root = $doc->getElementById('__root');
    if (!$root) return '';

    $walk = function (DOMNode $node) use (&$walk, $doc, $allowed, $rename, $drop) {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof DOMElement) continue;
            $tag = strtolower($child->tagName);

            if (in_array($tag, $drop, true)) { $node->removeChild($child); continue; }

            if (isset($rename[$tag])) {
                $new = $doc->createElement($rename[$tag]);
                while ($child->firstChild) $new->appendChild($child->firstChild);
                $node->replaceChild($new, $child);
                $child = $new;
                $tag = $rename[$tag];
            }

            if (!isset($allowed[$tag])) {
                // Unknown tag: keep its text and allowed children, lose the tag
                $walk($child);
                while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
                $node->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                $name = strtolower($attr->name);
                if (!in_array($name, $allowed[$tag], true)) { $child->removeAttribute($attr->name); continue; }
                $val = trim($attr->value);
                if ($name === 'href' && !safe_url($val, true)) $child->removeAttribute($attr->name);
                if ($name === 'src' && !safe_url($val, false)) $child->removeAttribute($attr->name);
                if (in_array($name, ['width', 'height', 'colspan', 'rowspan', 'start'], true) && !ctype_digit($val)) $child->removeAttribute($attr->name);
                if ($name === 'id' && !preg_match('/^[a-z][a-z0-9-]{0,80}$/', $val)) $child->removeAttribute($attr->name);
            }

            if ($tag === 'img' && !$child->hasAttribute('src')) { $node->removeChild($child); continue; }
            if ($tag === 'img') {
                if (!$child->hasAttribute('alt')) $child->setAttribute('alt', '');
                $child->setAttribute('loading', 'lazy');
                $child->setAttribute('decoding', 'async');
            }
            if ($tag === 'a') {
                $href = $child->getAttribute('href');
                $external = preg_match('#^https?://#i', $href) && !str_starts_with($href, rtrim((string)($GLOBALS['config']['site_url'] ?? ''), '/'));
                if ($external || $child->getAttribute('target') === '_blank') {
                    $child->setAttribute('target', '_blank');
                    $child->setAttribute('rel', 'noopener noreferrer');
                } else {
                    $child->removeAttribute('target');
                    $child->removeAttribute('rel');
                }
            }
            $walk($child);
        }
    };
    $walk($root);

    $out = '';
    foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
    // Drop empty paragraphs the editor leaves behind
    $out = preg_replace('#<p>(\s|&nbsp;|\xC2\xA0|<br>)*</p>#u', '', $out);
    return trim((string)$out);
}

/** Fill empty image descriptions from the Media library, so a description written there is used in articles. */
function fill_image_alts(string $html): string
{
    return (string)preg_replace_callback('#<img\b([^>]*?)\bsrc="/(uploads/[^"]+)"([^>]*)>#i', function ($m) {
        $tag = $m[0];
        if (preg_match('#\balt="[^"]+"#', $tag)) return $tag;
        $alt = (string)q_val('SELECT alt FROM media WHERE path = ?', [$m[2]]);
        if ($alt === '') return $tag;
        $tag = preg_replace('#\balt=""#', '', $tag);
        return preg_replace('#^<img#i', '<img alt="' . e($alt) . '"', $tag);
    }, $html);
}

function safe_url(string $u, bool $isLink): bool
{
    if ($u === '') return false;
    if (preg_match('/[\x00-\x1f]/', $u)) return false;
    if (str_starts_with($u, '/') && !str_starts_with($u, '//')) return true;
    if ($isLink && (str_starts_with($u, '#') || str_starts_with($u, '?'))) return true;
    $scheme = strtolower((string)parse_url($u, PHP_URL_SCHEME));
    if ($isLink) return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    return in_array($scheme, ['http', 'https'], true);
}

/** Give h2/h3 headings ids so readers can link to sections. Returns [html, table of contents]. */
function add_heading_ids(string $html): array
{
    $toc = [];
    $used = [];
    $html = preg_replace_callback('#<(h2|h3)(?: id="([^"]*)")?>(.*?)</\1>#is', function ($m) use (&$toc, &$used) {
        $text = trim(html_entity_decode(strip_tags($m[3]), ENT_QUOTES, 'UTF-8'));
        $id = $m[2] ?: slugify($text);
        $base = $id; $n = 2;
        while (isset($used[$id])) $id = $base . '-' . $n++;
        $used[$id] = true;
        if ($m[1] === 'h2') $toc[] = ['id' => $id, 'text' => $text];
        return '<' . $m[1] . ' id="' . e($id) . '">' . $m[3] . '</' . $m[1] . '>';
    }, $html);
    return [(string)$html, $toc];
}
