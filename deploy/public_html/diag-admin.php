<?php
// TEMPORARY: shows why /admin/login or saving fails (class, message, file:line and table counts only). Upload to public_html, open once, then DELETE it.
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain; charset=utf-8');
$base = is_dir(__DIR__.'/../app/bootstrap') ? __DIR__.'/../app' : __DIR__.'/..';
$short = fn (?string $s) => str_replace((string) realpath($base), 'APP', (string) $s);

try {
    require $base.'/vendor/autoload.php';
    $app = require $base.'/bootstrap/app.php';
    $app->usePublicPath(__DIR__);
    // ?path=/admin/... renders that admin page in-process as the first admin (GET only; prints status and error, never the page).
    $path = isset($_GET['path']) && preg_match('#^/admin(/[\w./-]*)?$#', $_GET['path']) ? $_GET['path'] : '/admin/login';
    $request = Request::create($path, 'GET', [], [], [], ['HTTPS' => 'on', 'HTTP_HOST' => parse_url((string) ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? 'localhost')]);
    if ($path !== '/admin/login') {
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $app['auth']->guard('web')->setUser(App\Models\User::where('role', 'admin')->where('is_active', true)->firstOrFail());
    }
    $response = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
    echo "page: $path\n";
    echo 'status: '.$response->getStatusCode()."\n";
    $e = $response->exception ?? null;
} catch (Throwable $e) {
    echo "boot failed\n";
}

// Writes and encryption are what the public pages never use, so check them one by one.
$step = function (string $name, Closure $fn) use ($short): void {
    try {
        echo str_pad($name, 22).': '.$fn()."\n";
    } catch (Throwable $t) {
        echo str_pad($name, 22).': FAIL '.get_class($t).': '.$short(mb_substr($t->getMessage(), 0, 300))."\n";
    }
};
if (isset($app)) {
    echo "\n";
    $step('encrypt/decrypt', fn () => decrypt(encrypt('x')) === 'x' ? 'OK' : 'FAIL');
    $step('file cache write', fn () => cache()->put('diag', 1, 60) && cache()->get('diag') === 1 ? 'OK' : 'FAIL');
    foreach (['languages', 'users', 'pages', 'categories', 'audit_events'] as $t) {
        $step("rows $t", fn () => (string) DB::table($t)->count());
    }
    $step('Language::active()', fn () => (string) App\Models\Language::active()->count());
    $step('audit insert (undone)', function () {
        DB::beginTransaction();
        try {
            App\Models\AuditEvent::log('diag');

            return 'OK';
        } finally {
            DB::rollBack();
        }
    });
    echo "\n";
}

if (isset($e)) {
    do {
        echo get_class($e).': '.$short(mb_substr($e->getMessage(), 0, 300)).' @ '.$short($e->getFile()).':'.$e->getLine()."\n";
    } while ($e = $e->getPrevious());
} else {
    echo "no exception: this page renders fine from PHP, so the 500 comes from Apache/.htaccess or PHP-FPM (check DirectAdmin error log).\n";
}
echo "\nDELETE diag-admin.php NOW.\n";
