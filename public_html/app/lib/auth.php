<?php
declare(strict_types=1);

const LOGIN_MAX_ATTEMPTS = 5;      // failed logins allowed...
const LOGIN_WINDOW_MIN = 15;       // ...per IP address in this many minutes

function start_admin_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('tm_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
    // Sign out after 8 hours without activity
    if (isset($_SESSION['last_seen']) && time() - $_SESSION['last_seen'] > 8 * 3600) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['last_seen'] = time();
}

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) return $user;
    $id = (int)($_SESSION['uid'] ?? 0);
    $user = $id ? q_one('SELECT id, name, email, role FROM users WHERE id = ?', [$id]) : null;
    return $user;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function require_login(): array
{
    start_admin_session();
    $u = current_user();
    if (!$u) {
        $next = $_SERVER['REQUEST_URI'] ?? '/admin/';
        redirect('/admin/login.php?next=' . rawurlencode($next));
    }
    return $u;
}

function require_admin(): array
{
    $u = require_login();
    if ($u['role'] !== 'admin') {
        http_response_code(403);
        exit('Only administrators can open this page.');
    }
    return $u;
}

function login_locked(): bool
{
    $since = gmdate('Y-m-d H:i:s', time() - LOGIN_WINDOW_MIN * 60);
    return (int)q_val('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > ?', [client_ip(), $since]) >= LOGIN_MAX_ATTEMPTS;
}

/** Returns null on success, or an error message. */
function attempt_login(string $email, string $password): ?string
{
    if (login_locked()) {
        return 'Too many failed attempts. Wait ' . LOGIN_WINDOW_MIN . ' minutes and try again.';
    }
    $u = q_one('SELECT id, password_hash FROM users WHERE email = ?', [mb_strtolower($email)]);
    if (!$u || !password_verify($password, $u['password_hash'])) {
        q('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)', [client_ip(), now_utc()]);
        return 'That email and password don’t match an account.';
    }
    if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
        q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $u['id']]);
    }
    q('DELETE FROM login_attempts WHERE ip = ?', [client_ip()]);
    q('DELETE FROM login_attempts WHERE attempted_at < ?', [gmdate('Y-m-d H:i:s', time() - 86400)]);
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$u['id'];
    q('UPDATE users SET last_login_at = ? WHERE id = ?', [now_utc(), $u['id']]);
    return null;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
}

function password_problem(string $pw): ?string
{
    if (mb_strlen($pw) < 10) return 'Use at least 10 characters for the password.';
    return null;
}

/* ---------- CSRF protection for every admin form ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = (string)($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('This form expired. Go back, reload the page and try again.');
    }
}
