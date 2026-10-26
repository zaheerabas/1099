<?php
declare(strict_types=1);

const POST_COLUMNS = 'p.id, p.title, p.slug, p.excerpt, p.cover_image, p.cover_alt, p.category_id, p.author_id, p.status, p.published_at,
    p.seo_title, p.seo_description, p.featured, p.views, p.created_at, p.updated_at,
    c.name AS category_name, c.slug AS category_slug, u.name AS author_name';

function post_from(): string
{
    return ' FROM posts p LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN users u ON u.id = p.author_id ';
}

/** Published and not scheduled for later. */
function public_where(): array
{
    return ["p.status = 'published' AND p.published_at <= ?", [now_utc()]];
}

/** Display state: draft, scheduled or published. */
function post_state(array $p): string
{
    if ($p['status'] !== 'published') return 'draft';
    return ($p['published_at'] && $p['published_at'] > now_utc()) ? 'scheduled' : 'published';
}

function public_posts(int $limit, int $offset = 0, ?int $categoryId = null, string $search = '', bool $withContent = false): array
{
    [$w, $params] = public_where();
    if ($categoryId) { $w .= ' AND p.category_id = ?'; $params[] = $categoryId; }
    if ($search !== '') {
        $w .= " AND (p.title LIKE ? ESCAPE '!' OR p.excerpt LIKE ? ESCAPE '!' OR p.content LIKE ? ESCAPE '!')";
        $like = like_pattern($search);
        array_push($params, $like, $like, $like);
    }
    $total = (int)q_val('SELECT COUNT(*)' . post_from() . ' WHERE ' . $w, $params);
    $rows = q_all('SELECT ' . POST_COLUMNS . ', p.content' . post_from() . " WHERE $w ORDER BY p.featured DESC, p.published_at DESC, p.id DESC LIMIT ? OFFSET ?",
        array_merge($params, [$limit, $offset]));
    return [$rows, $total];
}

/** Search text as a LIKE pattern, with % and _ treated literally. Use with ESCAPE '!'. */
function like_pattern(string $s): string
{
    return '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $s) . '%';
}

function public_post_by_slug(string $slug): ?array
{
    [$w, $params] = public_where();
    return q_one('SELECT ' . POST_COLUMNS . ', p.content' . post_from() . " WHERE p.slug = ? AND $w", array_merge([$slug], $params));
}

function post_by_id(int $id): ?array
{
    return q_one('SELECT ' . POST_COLUMNS . ', p.content' . post_from() . ' WHERE p.id = ?', [$id]);
}

function related_posts(array $post, int $limit = 3): array
{
    [$w, $params] = public_where();
    $rows = [];
    if ($post['category_id']) {
        $rows = q_all('SELECT ' . POST_COLUMNS . ', p.content' . post_from() . " WHERE $w AND p.id <> ? AND p.category_id = ? ORDER BY p.published_at DESC LIMIT ?",
            array_merge($params, [(int)$post['id'], (int)$post['category_id'], $limit]));
    }
    if (count($rows) < $limit) {
        $ids = array_merge([(int)$post['id']], array_map(fn($r) => (int)$r['id'], $rows));
        $in = implode(',', array_fill(0, count($ids), '?'));
        $more = q_all('SELECT ' . POST_COLUMNS . ', p.content' . post_from() . " WHERE $w AND p.id NOT IN ($in) ORDER BY p.published_at DESC LIMIT ?",
            array_merge($params, $ids, [$limit - count($rows)]));
        $rows = array_merge($rows, $more);
    }
    return $rows;
}

/** Categories that have at least one live article, with counts. */
function public_categories(): array
{
    [$w, $params] = public_where();
    return q_all("SELECT c.id, c.name, c.slug, c.description, COUNT(p.id) AS n FROM categories c
        JOIN posts p ON p.category_id = c.id AND $w GROUP BY c.id, c.name, c.slug, c.description ORDER BY c.name", $params);
}

function all_categories(): array
{
    return q_all('SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id) AS n FROM categories c ORDER BY c.name');
}

/** Make a slug unique in a table by adding -2, -3 … */
function unique_slug(string $table, string $slug, int $exceptId = 0): string
{
    $slug = slugify($slug);
    $base = $slug; $n = 2;
    while ((int)q_val("SELECT COUNT(*) FROM $table WHERE slug = ? AND id <> ?", [$slug, $exceptId]) > 0) {
        $slug = substr($base, 0, 110) . '-' . $n++;
    }
    return $slug;
}

function post_excerpt(array $p, int $len = 180): string
{
    return trim((string)$p['excerpt']) !== '' ? (string)$p['excerpt'] : excerpt_from((string)($p['content'] ?? ''), $len);
}
