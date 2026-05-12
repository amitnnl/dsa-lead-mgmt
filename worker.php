<?php
/**
 * DSA LeadFlow - Background Worker
 * Run via CLI:  php worker.php
 * Or via cron:  * * * * * php /path/to/worker.php
 * 
 * Processes queued import jobs in chunks for large file imports.
 */

// Ensure CLI only
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [__DIR__ . '/classes/', __DIR__ . '/controllers/'];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) { require_once $file; return; }
    }
});

echo "[" . date('Y-m-d H:i:s') . "] Worker started\n";

$processor = new JobProcessor();
$processed = 0;

// Process all pending jobs
while ($processor->processNext()) {
    $processed++;
    echo "[" . date('Y-m-d H:i:s') . "] Processed job #{$processed}\n";
}

if ($processed === 0) {
    echo "[" . date('Y-m-d H:i:s') . "] No pending jobs\n";
} else {
    echo "[" . date('Y-m-d H:i:s') . "] Finished processing {$processed} job(s)\n";
}
