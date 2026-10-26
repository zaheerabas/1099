#!/usr/bin/env bash
# Builds a static copy of the blog for hosts that can't run PHP (Vercel).
# Installs the blog into a temporary copy of public_html with the example articles,
# saves every public blog page as HTML, and writes the result to static-blog/.
# Run it again after changing app/lib/sample-posts.php or the blog templates.
# Needs PHP 8 with pdo_sqlite, dom and mbstring, and curl.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/static-blog"
SITE_URL="${SITE_URL:-https://YOUR-DOMAIN.com}"
PORT="${PORT:-8765}"
TMP="$(mktemp -d)"
trap 'kill "$SERVER" 2>/dev/null || true; rm -rf "$TMP"' EXIT

cp -a "$ROOT/public_html/." "$TMP/"
rm -f "$TMP/app/config.php" "$TMP"/app/data/*.sqlite
php -S "127.0.0.1:$PORT" -t "$TMP" >/dev/null 2>&1 &
SERVER=$!
for _ in $(seq 50); do curl -s -o /dev/null "http://127.0.0.1:$PORT/install.php" && break; sleep 0.1; done

BASE="http://127.0.0.1:$PORT"
pw="$(head -c 24 /dev/urandom | od -An -tx1 | tr -d ' \n')"
curl -sf -X POST "$BASE/install.php" \
  --data-urlencode "site_url=$SITE_URL" --data-urlencode "blog_title=The taxmetter blog" \
  --data "driver=sqlite&samples=1" --data-urlencode "name=taxmetter team" \
  --data "email=blog@example.com&password=$pw&password2=$pw" | grep -q 'The blog is installed'

fetch() { curl -sf -A 'static-build-bot' "$BASE/$1" > "$2"; }
rm -rf "$OUT"; mkdir -p "$OUT/blog/category"
fetch blog.php "$OUT/blog.html"
fetch feed.php "$OUT/blog/feed.xml"
fetch latest.php "$OUT/blog/latest.json"
fetch sitemap.php "$OUT/sitemap.xml"

list() { php -r 'define("APP_DIR",$argv[1]."/app"); $GLOBALS["config"]=require APP_DIR."/config.php"; require APP_DIR."/lib/db.php";
  foreach (db()->query($argv[2])->fetchAll(PDO::FETCH_COLUMN) as $s) echo $s, "\n";' "$TMP" "$1"; }
for slug in $(list "SELECT slug FROM posts WHERE status = 'published'"); do
  fetch "post.php?slug=$slug" "$OUT/blog/$slug.html"
done
for slug in $(list "SELECT DISTINCT c.slug FROM categories c JOIN posts p ON p.category_id = c.id AND p.status = 'published'"); do
  fetch "blog.php?category=$slug" "$OUT/blog/category/$slug.html"
done

# There is no server search on the static copy: mark the search box so assets/js/blog.js filters on the page instead
find "$OUT" -name '*.html' -exec perl -pi -e 's#<form class="bl-search" role="search"#<form class="bl-search" role="search" data-static#g' {} +

echo "Static blog written to $OUT"
find "$OUT" -type f | sed "s|$ROOT/||" | sort
