<?php
// Renders a few public pages in-process and exits non-zero if any fails. server-pull.sh
// runs this right after caching config/routes/views and drops the caches again if it
// fails, so a bad cache can't take the live site down.

chdir(dirname(__DIR__));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// With a broken config cache the DB name comes back null and the home page silently
// renders the installer (see commit 3ec9be7), so check that explicitly.
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.connections.' . config('database.default') . '.database') == null) {
    fwrite(STDERR, "smoke-test: database name is empty in cached config\n");
    exit(1);
}

foreach (['/', '/all-events'] as $uri) {
    try {
        $status = $kernel->handle(Illuminate\Http\Request::create($uri, 'GET'))->getStatusCode();
    } catch (Throwable $e) {
        fwrite(STDERR, "smoke-test: {$uri} threw " . get_class($e) . ': ' . $e->getMessage() . "\n");
        exit(1);
    }
    if ($status >= 500) {
        fwrite(STDERR, "smoke-test: {$uri} returned {$status}\n");
        exit(1);
    }
    echo "smoke-test: {$uri} {$status}\n";
}
exit(0);
