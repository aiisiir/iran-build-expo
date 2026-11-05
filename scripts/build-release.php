<?php
// Builds release.zip for the no-SSH host: app/ (project) beside public_html/ (public/).
// Run locally on PHP 8.4 after `composer install --no-dev --optimize-autoloader`:  php scripts/build-release.php
// Never packs .env, .git, tests, docs, node_modules or runtime files in storage/.

$root = dirname(__DIR__);
$out = $argv[1] ?? "$root/release.zip";

$installed = @json_decode((string) @file_get_contents("$root/vendor/composer/installed.json"), true);
if (! is_array($installed)) {
    exit("vendor/ is missing. Run: composer install --no-dev --optimize-autoloader\n");
}
if (($installed['dev'] ?? true) !== false) {
    exit("vendor/ contains dev packages. Run: composer install --no-dev --optimize-autoloader\n");
}

// Top-level entries that never ship; public/ is mapped to public_html/ separately.
$skipTop = ['.git', '.github', '.idea', '.vscode', 'tests', 'docs', 'deploy', 'node_modules', 'scripts', 'public', 'release.zip', '.phpunit.cache'];
$emptyDirs = ['storage/app/private', 'storage/app/public', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'];

$zip = new ZipArchive;
if ($zip->open($out, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    exit("Cannot write $out\n");
}

$add = function (string $dir, string $prefix, callable $skip) use ($zip): int {
    $n = 0;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        $rel = substr($file->getPathname(), strlen($dir) + 1);
        if (! $file->isFile() || $skip($rel)) {
            continue;
        }
        $zip->addFile($file->getPathname(), $prefix.str_replace('\\', '/', $rel));
        $n++;
    }

    return $n;
};

$files = $add($root, 'app/', function (string $rel) use ($skipTop): bool {
    $top = explode('/', str_replace('\\', '/', $rel))[0];

    return in_array($top, $skipTop, true)
        || preg_match('#(^|/)\.env(\..*)?$#', $rel) && ! str_ends_with($rel, '.example')
        || preg_match('#^storage/(logs|framework/(cache|sessions|views|testing))/.#', $rel)
        || preg_match('#^storage/app/(private|public)/.#', $rel)
        || preg_match('#^bootstrap/cache/.+\.php$#', $rel)
        || str_ends_with($rel, '.sqlite');
});
$files += $add("$root/public", 'public_html/', fn (string $rel): bool => $rel === 'hot' || str_starts_with($rel, 'build/.')
    // Local test uploads never ship (the host keeps its own); only the tracked demo images do.
    || (str_starts_with($rel, 'uploads/') && ! in_array($rel, ['uploads/.htaccess', 'uploads/.gitignore'], true) && ! str_starts_with($rel, 'uploads/demo/')));

foreach ($emptyDirs as $d) {
    $zip->addEmptyDir("app/$d");
}
$zip->close();

echo "Wrote $out ($files files). Next: upload, Extract, create app/.env, import SQL (docs/07-operations/deployment.md).\n";
