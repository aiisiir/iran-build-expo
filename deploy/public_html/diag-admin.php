<?php
// TEMPORARY: shows why /admin/login fails (class, message, file:line only). Upload to public_html, open once, then DELETE it.
use Illuminate\Http\Request;

header('Content-Type: text/plain; charset=utf-8');
$base = is_dir(__DIR__.'/../app/bootstrap') ? __DIR__.'/../app' : __DIR__.'/..';
$short = fn (?string $s) => str_replace((string) realpath($base), 'APP', (string) $s);

try {
    require $base.'/vendor/autoload.php';
    $app = require $base.'/bootstrap/app.php';
    $app->usePublicPath(__DIR__);
    $request = Request::create('/admin/login', 'GET', [], [], [], ['HTTPS' => 'on', 'HTTP_HOST' => parse_url((string) ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? 'localhost')]);
    $response = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
    echo 'status: '.$response->getStatusCode()."\n";
    $e = $response->exception ?? null;
} catch (Throwable $e) {
    echo "boot failed\n";
}

if (isset($e)) {
    do {
        echo get_class($e).': '.$short(mb_substr($e->getMessage(), 0, 300)).' @ '.$short($e->getFile()).':'.$e->getLine()."\n";
    } while ($e = $e->getPrevious());
} else {
    echo "no exception: /admin/login renders fine from PHP, so the 500 comes from Apache/.htaccess or PHP-FPM (check DirectAdmin error log).\n";
}
echo "\nDELETE diag-admin.php NOW.\n";
