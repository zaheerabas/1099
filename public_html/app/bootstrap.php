<?php
/**
 * taxmetter blog: loads config, database and helpers.
 * Every PHP page starts with: require __DIR__ . '/app/bootstrap.php';
 */
declare(strict_types=1);

if (PHP_VERSION_ID < 80000) {
    http_response_code(500);
    exit('The taxmetter blog needs PHP 8.0 or newer. You can change the PHP version in cPanel under "Select PHP Version".');
}

define('APP_DIR', __DIR__);
define('ROOT_DIR', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_DIR . '/uploads');

$configFile = APP_DIR . '/config.php';
if (!is_file($configFile)) {
    header('Location: /install.php');
    exit;
}
$GLOBALS['config'] = require $configFile;

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');
ini_set('display_errors', !empty($GLOBALS['config']['debug']) ? '1' : '0');

require APP_DIR . '/lib/db.php';
require APP_DIR . '/lib/helpers.php';
require APP_DIR . '/lib/auth.php';
require APP_DIR . '/lib/sanitize.php';
require APP_DIR . '/lib/posts.php';
require APP_DIR . '/lib/media.php';
require APP_DIR . '/lib/covers.php';
