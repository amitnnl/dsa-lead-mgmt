<?php
/**
 * DSA LeadFlow - Git Rescue Script
 * This script forces the cPanel repository to reset and pull the latest code.
 * Use this when cPanel says "uncommitted changes exist".
 */

header('Content-Type: text/plain');

// Path to your repository (usually the same as public_html or one level up)
$repoPath = __DIR__; 

function run($cmd) {
    global $repoPath;
    echo "Running: $cmd\n";
    $output = [];
    $resultCode = 0;
    // We try to use the full path to git if standard git isn't in path
    exec("cd $repoPath && git $cmd 2>&1", $output, $resultCode);
    echo implode("\n", $output) . "\n";
    echo "Result Code: $resultCode\n\n";
}

echo "=== Git Rescue Started ===\n\n";

// 1. Fetch latest
run("fetch origin main");

// 2. Force reset to match GitHub exactly (this deletes server-only changes!)
run("reset --hard origin/main");

// 3. Clean untracked files
run("clean -fd");

echo "=== Finished ===\n";
echo "Now try to click 'Deploy' in cPanel again.";
