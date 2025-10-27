<?php

/**
 * Version Bump Script for Filament AI Chat Agent
 * 
 * Usage:
 * php bump-version.php [patch|minor|major]
 * 
 * Examples:
 * php bump-version.php patch  # 1.0.0 -> 1.0.1
 * php bump-version.php minor  # 1.0.0 -> 1.1.0
 * php bump-version.php major  # 1.0.0 -> 2.0.0
 */

function bumpVersion($type = 'patch') {
    $composerFile = 'composer.json';
    
    if (!file_exists($composerFile)) {
        echo "Error: composer.json not found\n";
        exit(1);
    }
    
    $composer = json_decode(file_get_contents($composerFile), true);
    $currentVersion = $composer['version'];
    
    echo "Current version: $currentVersion\n";
    
    // Parse version
    $parts = explode('.', $currentVersion);
    $major = (int)$parts[0];
    $minor = (int)$parts[1];
    $patch = (int)$parts[2];
    
    // Bump version based on type
    switch ($type) {
        case 'major':
            $major++;
            $minor = 0;
            $patch = 0;
            break;
        case 'minor':
            $minor++;
            $patch = 0;
            break;
        case 'patch':
        default:
            $patch++;
            break;
    }
    
    $newVersion = "$major.$minor.$patch";
    
    // Update composer.json
    $composer['version'] = $newVersion;
    file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    echo "Version bumped to: $newVersion\n";
    echo "Updated composer.json\n";
    
    // Create git tag
    $tag = "v$newVersion";
    echo "Creating git tag: $tag\n";
    
    exec("git add composer.json");
    exec("git commit -m \"Bump version to $newVersion\"");
    exec("git tag $tag");
    
    echo "Git tag created: $tag\n";
    echo "Ready to push with: git push origin main --tags\n";
}

// Get command line argument
$type = $argv[1] ?? 'patch';

if (!in_array($type, ['patch', 'minor', 'major'])) {
    echo "Error: Invalid version type. Use 'patch', 'minor', or 'major'\n";
    echo "Usage: php bump-version.php [patch|minor|major]\n";
    exit(1);
}

bumpVersion($type);
