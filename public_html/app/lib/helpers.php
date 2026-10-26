<?php
declare(strict_types=1);

/** Escape for HTML output. */
function e($s): string
{
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Site-relative URL (the blog is installed at the domain root). */
function url(string $path = '/'): string
{
    return '/' . ltrim($path, '/');
}

/** Absolute URL for canonical links, feeds and share links. */
function abs_url(string $path = '/'): string
{
    return rtrim((string)$GLOBALS['config']['site_url'], '/') . url($path);
}

function post_url(array $post): string
{
    return url('blog/' . $post['slug']);
}

function category_url(array $cat): string
{
    return url('blog/category/' . $cat['slug']);
}

function now_utc(): string
{
    return gmdate('Y-m-d H:i:s');
}

/* ---------- Settings ---------- */
function setting(string $name, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (q_all('SELECT name, value FROM settings') as $r) $cache[$r['name']] = $r['value'];
        } catch (Throwable $e) { /* not installed yet */ }
    }
    return array_key_exists($name, $cache) && $cache[$name] !== null && $cache[$name] !== '' ? $cache[$name] : $default;
}

function save_setting(string $name, ?string $value): void
{
    if (q_val('SELECT COUNT(*) FROM settings WHERE name = ?', [$name])) {
        q('UPDATE settings SET value = ? WHERE name = ?', [$value, $name]);
    } else {
        q('INSERT INTO settings (name, value) VALUES (?, ?)', [$name, $value]);
    }
}

function site_tz(): DateTimeZone
{
    try { return new DateTimeZone((string)setting('timezone', 'America/New_York')); }
    catch (Throwable $e) { return new DateTimeZone('UTC'); }
}

/** Format a stored UTC time in the site's time zone. */
function fmt_date(?string $utc, string $format = 'M j, Y'): string
{
    if (!$utc) return '';
    $d = new DateTimeImmutable($utc, new DateTimeZone('UTC'));
    return $d->setTimezone(site_tz())->format($format);
}

/** ISO 8601 for <time datetime> and structured data. */
function iso_date(?string $utc): string
{
    if (!$utc) return '';
    return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))->format(DATE_ATOM);
}

/** Value for <input type="datetime-local"> in the site time zone. */
function to_local_input(?string $utc): string
{
    return $utc ? fmt_date($utc, 'Y-m-d\TH:i') : '';
}

/** Convert a datetime-local value (site time zone) to stored UTC. */
function local_to_utc(string $local): ?string
{
    $local = trim($local);
    if ($local === '') return null;
    try {
        $d = new DateTimeImmutable($local, site_tz());
        return $d->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    } catch (Throwable $e) {
        return null;
    }
}

/* ---------- Text ---------- */
function slugify(string $s): string
{
    $s = mb_strtolower(trim($s));
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($t !== false) $s = $t;
    }
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim((string)$s, '-');
    return substr($s, 0, 120) ?: 'article';
}

function plain_text(string $html): string
{
    $t = html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>', '</h2>', '</h3>'], ["</p> ", ' ', ' ', ' ', ' '], $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim((string)preg_replace('/\s+/u', ' ', $t));
}

function excerpt_from(string $html, int $len = 180): string
{
    $t = plain_text($html);
    if (mb_strlen($t) <= $len) return $t;
    $cut = mb_substr($t, 0, $len);
    $sp = mb_strrpos($cut, ' ');
    return rtrim(mb_substr($cut, 0, $sp ?: $len), " ,.;:") . '…';
}

function word_count(string $html): int
{
    $t = plain_text($html);
    return $t === '' ? 0 : count(preg_split('/\s+/u', $t));
}

function reading_time(string $html): int
{
    return max(1, (int)ceil(word_count($html) / 225));
}

/* ---------- Requests ---------- */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, $default = '')
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 64);
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
}

/* ---------- Flash messages (admin) ---------- */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Pagination ---------- */
function paginate(int $total, int $perPage, int $page): array
{
    $pages = max(1, (int)ceil($total / max(1, $perPage)));
    $page = min(max(1, $page), $pages);
    return ['page' => $page, 'pages' => $pages, 'per' => $perPage, 'offset' => ($page - 1) * $perPage, 'total' => $total];
}

/** Build a URL keeping current query values, with some replaced. */
function with_query(string $path, array $params): string
{
    if (isset($params['page']) && (int)$params['page'] <= 1) unset($params['page']);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    return $path . ($params ? '?' . http_build_query($params) : '');
}

function human_size(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    return max(1, (int)round($bytes / 1024)) . ' KB';
}

function render_404(): void
{
    http_response_code(404);
    $page = ['title' => 'Page not found | taxmetter', 'description' => 'This page could not be found.', 'noindex' => true, 'current' => '', 'css' => ['blog.css']];
    require APP_DIR . '/views/layout-top.php';
    echo '<main class="ew-main" id="main"><div class="nf glass"><h1>404</h1><p>This article doesn’t exist or has moved. The calculator and the rest of the blog are still here.</p>'
       . '<div class="home-sec__more"><a class="btn btn--primary" href="/blog">Go to the blog</a><a class="btn btn--quiet" href="/calculator.html">Open the calculator</a></div></div></main>';
    require APP_DIR . '/views/layout-bottom.php';
    exit;
}
