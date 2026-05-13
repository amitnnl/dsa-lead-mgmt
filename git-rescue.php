<?php
/**
 * DSA LeadFlow - Git Rescue Script
 * Fixes "local changes would be overwritten" errors on cPanel shared hosting.
 * 
 * USAGE: Upload to your live site, visit in browser, then DELETE this file.
 */

header('Content-Type: text/plain');
echo "=== DSA LeadFlow Git Rescue ===\n\n";

// Step 1: Find the repository path
// cPanel clones repos to ~/repositories/ or the deploy path itself
$homeDir = getenv('HOME') ?: '/home/hotelsunplaza';
$possiblePaths = [
    $homeDir . '/repositories/dsa-lead-mgmt',
    $homeDir . '/repositories/dsa-lead mgmt',
    $homeDir . '/repositories/dsa_lead_mgmt',
    __DIR__,  // Sometimes the repo IS the deploy directory
];

$repoPath = null;
foreach ($possiblePaths as $path) {
    if (is_dir($path . '/.git')) {
        $repoPath = $path;
        break;
    }
}

// Also scan ~/repositories/ for any git repo
if (!$repoPath && is_dir($homeDir . '/repositories')) {
    $dirs = scandir($homeDir . '/repositories');
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..') continue;
        $check = $homeDir . '/repositories/' . $dir;
        if (is_dir($check . '/.git')) {
            $repoPath = $check;
            echo "Found repository at: $check\n";
            break;
        }
    }
}

if (!$repoPath) {
    echo "ERROR: Could not find the Git repository.\n";
    echo "Searched paths:\n";
    foreach ($possiblePaths as $p) echo "  - $p\n";
    echo "\nPlease check cPanel > Git Version Control for the repository path.\n";
    exit;
}

echo "Repository found at: $repoPath\n\n";

function run($cmd, $repoPath) {
    echo "Running: $cmd\n";
    $output = [];
    $resultCode = 0;
    exec("cd " . escapeshellarg($repoPath) . " && git $cmd 2>&1", $output, $resultCode);
    echo implode("\n", $output) . "\n";
    echo "Exit code: $resultCode\n\n";
    return $resultCode;
}

// Step 2: Save the production database.php if it exists
$dbFile = $repoPath . '/config/database.php';
$dbBackup = $homeDir . '/database_backup_' . date('YmdHis') . '.php';
if (file_exists($dbFile)) {
    copy($dbFile, $dbBackup);
    echo "Backed up database.php to: $dbBackup\n\n";
}

// Step 3: Discard local changes and force sync with GitHub
echo "--- Fixing Git conflict ---\n";
run("stash", $repoPath);
run("fetch origin", $repoPath);
run("reset --hard origin/main", $repoPath);
run("clean -fd", $repoPath);

// Step 4: Restore the production database.php
if (file_exists($dbBackup)) {
    copy($dbBackup, $dbFile);
    echo "Restored database.php from backup.\n\n";
}

echo "=== RESCUE COMPLETE ===\n";
echo "Now go to cPanel > Git Version Control > Deploy.\n";
echo "IMPORTANT: Delete this git-rescue.php file from your server after use!\n";
