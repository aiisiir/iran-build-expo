<?php
// TEMPORARY deployment smoke test. DELETE this file right after use.
header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$ok = static fn (bool $c): string => $c ? 'OK   ' : 'FAIL ';
$app = __DIR__.'/../app';

echo "PHP version       : ".PHP_VERSION.' '.$ok(version_compare(PHP_VERSION, '8.4.0', '>='))."(target 8.4)\n";
foreach (['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pcre', 'pdo', 'pdo_mysql', 'session', 'tokenizer', 'xml'] as $e) {
    echo str_pad("ext $e", 18).': '.$ok(extension_loaded($e))."\n";
}
echo 'optional intl/gd/zip: '.implode('/', array_map(fn ($e) => extension_loaded($e) ? 'yes' : 'no', ['intl', 'gd', 'zip']))."\n";
echo 'HTTPS             : '.$ok(($_SERVER['HTTPS'] ?? 'off') !== 'off')."\n";
echo 'rewrite           : '.(isset($_GET['rewrite']) ? $ok(true) : 'open /rewrite-test to test')."\n";
echo 'app/ beside here  : '.$ok(is_dir($app))."\n";
echo 'app/ outside web root: '.$ok(is_dir($app) && !str_starts_with((string) realpath($app), __DIR__))."\n";

foreach (['storage', 'bootstrap/cache'] as $d) {
    echo str_pad("writable $d", 18).': '.(is_dir("$app/$d") ? $ok(is_writable("$app/$d")) : 'n/a (no app yet)')."\n";
}

$env = "$app/.env";
if (is_file($env)) {
    $c = parse_ini_file($env, false, INI_SCANNER_RAW) ?: [];
    try {
        new PDO(
            "mysql:host=".($c['DB_HOST'] ?? '127.0.0.1').';port='.($c['DB_PORT'] ?? '3306').';dbname='.($c['DB_DATABASE'] ?? ''),
            $c['DB_USERNAME'] ?? '',
            $c['DB_PASSWORD'] ?? '',
            [PDO::ATTR_TIMEOUT => 3, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo 'database          : '.$ok(true)."\n";
    } catch (Throwable $e) {
        echo 'database          : '.$ok(false).'(code '.$e->getCode().")\n";
    }
    echo 'APP_DEBUG/ENV     : '.($c['APP_DEBUG'] ?? '?').' / '.($c['APP_ENV'] ?? '?')."\n";
} else {
    echo "database          : skipped (no app/.env yet)\n";
}
echo "\nDELETE check.php NOW.\n";
