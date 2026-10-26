<?php
declare(strict_types=1);

/** Shared PDO connection (SQLite or MySQL, chosen in app/config.php). */
function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = db_connect($GLOBALS['config']['db']);
    return $pdo;
}

function db_connect(array $c): PDO
{
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if ($c['driver'] === 'mysql') {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], (int)($c['port'] ?? 3306), $c['name']);
        $pdo = new PDO($dsn, $c['user'], $c['pass'], $opts);
        $pdo->exec("SET time_zone = '+00:00'");
    } else {
        $pdo = new PDO('sqlite:' . APP_DIR . '/data/' . $c['file'], null, null, $opts);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 4000');
    }
    return $pdo;
}

/** Run a prepared query. Integers are bound as integers so LIMIT/OFFSET work on MySQL. */
function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    foreach ($params as $k => $v) {
        $key = is_int($k) ? $k + 1 : ':' . ltrim((string)$k, ':');
        $type = is_int($v) ? PDO::PARAM_INT : (is_null($v) ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $st->bindValue($key, $v, $type);
    }
    $st->execute();
    return $st;
}

function q_one(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function q_val(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

function q_all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

/** Table definitions for both database types. */
function schema_statements(string $driver): array
{
    if ($driver === 'mysql') {
        $id = 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY';
        $fk = 'INT UNSIGNED';
        $text = 'MEDIUMTEXT';
        $end = ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        $s = fn($n) => "VARCHAR($n)";
    } else {
        $id = 'INTEGER PRIMARY KEY AUTOINCREMENT';
        $fk = 'INTEGER';
        $text = 'TEXT';
        $end = ')';
        $s = fn($n) => 'TEXT';
    }
    return [
        "CREATE TABLE users (
            id $id,
            name {$s(120)} NOT NULL,
            email {$s(190)} NOT NULL UNIQUE,
            password_hash {$s(255)} NOT NULL,
            role {$s(20)} NOT NULL DEFAULT 'editor',
            created_at {$s(19)} NOT NULL,
            last_login_at {$s(19)} NULL
        $end",
        "CREATE TABLE categories (
            id $id,
            name {$s(120)} NOT NULL,
            slug {$s(140)} NOT NULL UNIQUE,
            description {$s(500)} NULL,
            created_at {$s(19)} NOT NULL
        $end",
        "CREATE TABLE posts (
            id $id,
            title {$s(255)} NOT NULL,
            slug {$s(190)} NOT NULL UNIQUE,
            excerpt {$s(600)} NULL,
            content $text NOT NULL,
            cover_image {$s(255)} NULL,
            cover_alt {$s(255)} NULL,
            category_id $fk NULL,
            author_id $fk NULL,
            status {$s(20)} NOT NULL DEFAULT 'draft',
            published_at {$s(19)} NULL,
            seo_title {$s(255)} NULL,
            seo_description {$s(320)} NULL,
            featured INTEGER NOT NULL DEFAULT 0,
            views INTEGER NOT NULL DEFAULT 0,
            created_at {$s(19)} NOT NULL,
            updated_at {$s(19)} NOT NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
        $end",
        "CREATE INDEX posts_public ON posts (status, published_at)",
        "CREATE TABLE media (
            id $id,
            path {$s(255)} NOT NULL,
            original_name {$s(255)} NULL,
            mime {$s(60)} NOT NULL,
            width INTEGER NULL,
            height INTEGER NULL,
            size INTEGER NOT NULL DEFAULT 0,
            alt {$s(255)} NULL,
            uploaded_by $fk NULL,
            created_at {$s(19)} NOT NULL,
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
        $end",
        "CREATE TABLE settings (
            name {$s(80)} NOT NULL PRIMARY KEY,
            value $text NULL
        $end",
        "CREATE TABLE login_attempts (
            id $id,
            ip {$s(64)} NOT NULL,
            attempted_at {$s(19)} NOT NULL
        $end",
        "CREATE INDEX login_attempts_ip ON login_attempts (ip, attempted_at)",
    ];
}
