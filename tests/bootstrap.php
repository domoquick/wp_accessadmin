<?php
declare(strict_types=1);

// Résolution vendor mutualisé (stack wordpress6) ou fallback standalone
$stack = json_decode((string) file_get_contents(dirname(__DIR__) . '/stack.json'), true)['stack'];
$candidates = [
    dirname(__DIR__, 2) . "/_stacks/{$stack}/vendor/autoload.php",
    dirname(__DIR__)    . '/vendor/autoload.php',
];

foreach ($candidates as $autoload) {
    if (file_exists($autoload)) {
        require_once $autoload;
        break;
    }
}

// Autoload PSR-4 du projet (src/) — indépendant du vendor mutualisé
spl_autoload_register(static function (string $class): void {
    $prefix = 'Domoquick\\WpAccessAdmin\\';
    $baseDir = dirname(__DIR__) . '/src/';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Stub ABSPATH pour éviter le guard exit du plugin principal
if (! defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wp/');
}
