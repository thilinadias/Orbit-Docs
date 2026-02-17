<?php
// Fix Storage Link Script
$target = __DIR__ . '/../storage/app/public';
$link = __DIR__ . '/storage';

echo "<pre>";
echo "Fixing Storage Link...\n";
echo "Target: $target\n";
echo "Link: $link\n";

if (file_exists($link)) {
    echo "Using standard unlink...\n";
    if (is_link($link)) {
        echo "Found existing link. Removing...\n";
        unlink($link);
    } elseif (is_dir($link)) {
        echo "Found existing DIRECTORY at link path. Attempting to remove...\n";
        // Simple rmdir might fail if not empty
        rmdir($link); 
    } else {
        echo "Found existing file. Removing...\n";
        unlink($link);
    }
}

echo "Creating new symlink...\n";
try {
    if (symlink($target, $link)) {
        echo "SUCCESS: Symlink created.\n";
    } else {
        echo "ERROR: Failed to create symlink.\n";
        // Fallback for Windows if symlink fails (requires admin usually, but copy might work for simple test)
        echo "Note: Windows requires Admin privileges or Developer Mode for symlinks.\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

// Check if logo exists
$logoPath = $target . '/logos';
if (!is_dir($logoPath)) {
    echo "Creating logos directory...\n";
    mkdir($logoPath, 0755, true);
}

echo "\nDone. Please refresh your dashboard.";
echo "</pre>";
