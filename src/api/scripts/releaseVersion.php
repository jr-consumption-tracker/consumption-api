#!/usr/bin/env php
<?php

/**
 * Interactive Release Script for Consumption Tracker API
 * 
 * Features:
 * - Interactive version selection (major/minor/patch)
 * - Pre-release support (alpha, beta, rc)
 * - Updates composer.json and .env
 * - Generates changelog from git commits
 * - Creates git commit and tag
 */

// ANSI colors
define('COLOR_RESET', "\033[0m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_RED', "\033[31m");
define('COLOR_CYAN', "\033[36m");

// Paths
$rootDir = dirname(__DIR__);
$composerFile = $rootDir . '/composer.json';
$envFile = $rootDir . '/.env';
$changelogFile = $rootDir . '/CHANGELOG.md';

// Helper functions
function colorize($text, $color)
{
  return $color . $text . COLOR_RESET;
}

/**
 * Calculate version bump options for a given version
 */
function get_version_options($current)
{
  // Simple semver parse: major.minor.patch[-pre.num]
  preg_match('/^(\d+)\.(\d+)\.(\d+)(?:-([a-z]+)\.(\d+))?$/i', $current, $m);
  if (!$m) return [];

  $major = (int)$m[1];
  $minor = (int)$m[2];
  $patch = (int)$m[3];
  $preTag = $m[4] ?? null;
  $preNum = isset($m[5]) ? (int)$m[5] : null;

  $options = [];

  // 1. Regular bumps
  $options['patch'] = [
    'label' => sprintf("Patch (%d.%d.%d)", $major, $minor, $patch + 1),
    'new' => sprintf("%d.%d.%d", $major, $minor, $patch + 1)
  ];
  $options['minor'] = [
    'label' => sprintf("Minor (%d.%d.0)", $major, $minor + 1),
    'new' => sprintf("%d.%d.0", $major, $minor + 1)
  ];
  $options['major'] = [
    'label' => sprintf("Major (%d.0.0)", $major + 1),
    'new' => sprintf("%d.0.0", $major + 1)
  ];

  // 2. Pre-releases
  if ($preTag !== null) {
    // Already in pre-release
    $preTagLower = strtolower($preTag);
    
    // Always offer next increment of current tag
    $options['prerelease'] = [
      'label' => sprintf("Next %s (%d.%d.%d-%s.%d)", $preTag, $major, $minor, $patch, $preTag, $preNum + 1),
      'new' => sprintf("%d.%d.%d-%s.%d", $major, $minor, $patch, $preTag, $preNum + 1)
    ];

    // Transitions based on hierarchy: alpha -> beta -> rc -> stable
    if ($preTagLower === 'alpha') {
      $options['to-beta'] = [
        'label' => sprintf("Graduate to Beta (%d.%d.%d-beta.0)", $major, $minor, $patch),
        'new' => sprintf("%d.%d.%d-beta.0", $major, $minor, $patch)
      ];
    } elseif ($preTagLower === 'beta') {
      $options['to-rc'] = [
        'label' => sprintf("Graduate to RC (%d.%d.%d-rc.0)", $major, $minor, $patch),
        'new' => sprintf("%d.%d.%d-rc.0", $major, $minor, $patch)
      ];
    }

    $options['graduate'] = [
      'label' => sprintf("Graduate to stable (%d.%d.%d)", $major, $minor, $patch),
      'new' => sprintf("%d.%d.%d", $major, $minor, $patch)
    ];
  } else {
    // New pre-releases
    $options['prepatch'] = [
      'label' => sprintf("Prepatch (%d.%d.%d-alpha.0)", $major, $minor, $patch + 1),
      'new' => sprintf("%d.%d.%d-alpha.0", $major, $minor, $patch + 1)
    ];
    $options['preminor'] = [
      'label' => sprintf("Preminor (%d.%d.0-alpha.0)", $major, $minor + 1),
      'new' => sprintf("%d.%d.0-alpha.0", $major, $minor + 1)
    ];
    $options['premajor'] = [
      'label' => sprintf("Premajor (%d.0.0-alpha.0)", $major + 1),
      'new' => sprintf("%d.0.0-alpha.0", $major + 1)
    ];
  }

  return $options;
}

/**
 * Standard header for version selection prompts
 */
/**
 * Standard header for version selection prompts
 */
function render_header($msg)
{
    echo colorize("? ", COLOR_GREEN);
    
    // Parse for highlighting: "Prefix (currently VERSION)"
    $parts = preg_split('/(\(currently .*?\))/', $msg, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    
    foreach ($parts as $part) {
        if (str_starts_with($part, '(currently ')) {
            echo colorize($part, COLOR_YELLOW);
        } else {
            echo colorize($part, COLOR_CYAN);
        }
    }
    echo "\n";
}

function prompt($question, $options = [])
{
  $isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

  if ($isWin && !empty($options)) {
    return windows_menu($question, $options);
  }

  // Fallback for non-Windows or if menu fails
  if ($question) echo colorize("\n? " . $question . "\n", COLOR_CYAN);
  foreach ($options as $k => $o) {
    echo "  [" . colorize($k, COLOR_YELLOW) . "] " . $o['label'] . "\n";
  }

  $handle = defined('STDIN') ? STDIN : fopen('php://stdin', 'r');
  while (true) {
    echo colorize("Choice: ", COLOR_BLUE);
    $input = trim(fgets($handle));
    if (isset($options[$input])) return $input;
    if ($input === 'q') exit(0);
    echo colorize("Invalid choice.\n", COLOR_RED);
  }
}

/**
 * Interactive menu for Windows using PowerShell
 */
function windows_menu($question, $options)
{
  $keys = array_keys($options);
  $labels = array_map(fn($o) => str_replace("'", "''", $o['label']), $options);
  $descs = array_map(fn($o) => str_replace("'", "''", $o['desc'] ?? ''), $options);
  $optColors = array_map(fn($o) => $o['color'] ?? '', $options); // Support custom colors
  
  $labelsStr = "'" . implode("','", $labels) . "'";
  $descsStr = "'" . implode("','", $descs) . "'";
  $colorsStr = "'" . implode("','", $optColors) . "'";
  $keysStr = "'" . implode("','", $keys) . "'";
  
  // Header parsing for highlighting: "Prefix (currently VERSION)" or "Are you sure...?"
  $qParts = preg_split('/(\(currently .*?\))/', $question, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
  $cleanParts = array_map(fn($p) => str_replace("'", "''", $p), $qParts);
  
  // Explicitly check for confirmation prompt
  $isConfirm = (stripos($question, 'Are you sure') !== false);
  $isConfirmStr = $isConfirm ? '$true' : '$false';

  // Fix: If question is empty, ensure array is empty for PS to avoid stray "?"
  if (empty($question) || empty($cleanParts)) {
      $qPartsStr = "";
  } else {
      $qPartsStr = "'" . implode("','", $cleanParts) . "'";
  }

  $tempDir = sys_get_temp_dir();
  $ps1File = $tempDir . DIRECTORY_SEPARATOR . 'php_release_menu_' . uniqid() . '.ps1';
  $resultFile = $tempDir . DIRECTORY_SEPARATOR . 'php_release_result_' . uniqid() . '.txt';

  $psScript = <<<PS
\$options = @($labelsStr)
\$descs = @($descsStr)
\$colors = @($colorsStr)
\$keys = @($keysStr)
\$qParts = @($qPartsStr)
\$selected = 0
\$isConfirm = $isConfirmStr

# Check if any description is actually present
\$hasDescriptions = \$false
foreach (\$d in \$descs) {
    if (-not [string]::IsNullOrWhiteSpace(\$d)) {
        \$hasDescriptions = \$true
        break
    }
}

# Capture starting position
\$isCursorSupported = \$true
\$startRow = try { [Console]::CursorTop } catch { \$isCursorSupported = \$false; 0 }

function Get-Width {
    try { return [Console]::WindowWidth } catch { return 80 }
}

function Render {
    param(\$qParts, \$opts, \$descriptions, \$optColors, \$current, \$top, \$isFirst, \$isConf)
    \$w = Get-Width
    \$esc = [char]27
    
    if (\$isCursorSupported) {
        try { 
            [Console]::SetCursorPosition(0, \$top)
        } catch { \$script:isCursorSupported = \$false }
    } 
    
    # Calculate height based on mode
    if (\$script:hasDescriptions) {
        \$h = (\$opts.Count * 2) + 2
    } else {
        \$h = \$opts.Count + 2
    }
    
    if (\$qParts.Count -gt 0) { \$h += 2 }

    # If not first render and no cursor support, move back up
    if (-not \$isFirst -and -not \$isCursorSupported) {
        Write-Host ("{0}[{1}A" -f \$esc, \$h) -NoNewline
    }

    if (\$qParts.Count -gt 0) {
        Write-Host ""
        
        # Standard prompt style using ANSI for exact match with PHP
        # Green "?"
        Write-Host "\${esc}[32m? \${esc}[0m" -NoNewline
        
        foreach (\$part in \$qParts) {
            # Highlight "(currently ...)"
            if (\$part -match "^\(currently") {
                # Yellow
                Write-Host "\${esc}[33m\$part\${esc}[0m" -NoNewline
            } else {
                # Cyan (standard for all other text)
                Write-Host "\${esc}[36m\$part\${esc}[0m" -NoNewline
            }
        }
        Write-Host ""
    }
    
    for (\$i = 0; \$i -lt \$opts.Count; \$i++) {
        if (\$i -eq \$current) { \$line = "  > \$(\$opts[\$i])" } else { \$line = "    \$(\$opts[\$i])" }
        try { \$line = \$line.PadRight(\$w - 1) } catch {}
        
        # ANSI Colors mapping
        # Green=32, Red=31, Yellow=33, Cyan=36, White=37, Gray=90
        \$cCode = "37" # Default White
        
        # Determine strict color
        \$hasColor = (\$optColors.Count -gt \$i -and -not [string]::IsNullOrEmpty(\$optColors[\$i]))
        \$reqColor = if (\$hasColor) { \$optColors[\$i] } else { '' }
        
        # Resolve color code
        if (\$reqColor -eq 'Green') { \$cCode = "32" }
        elseif (\$reqColor -eq 'Red') { \$cCode = "31" }
        elseif (\$reqColor -eq 'Yellow') { \$cCode = "33" }
        elseif (\$reqColor -eq 'Cyan') { \$cCode = "36" }
        
        if (\$i -eq \$current) {
             # Selected: Use custom color or Yellow (33)
             if (\$reqColor -ne '') { } else { \$cCode = "33" }
        } else {
             # Unselected
             if (\$isConf -and \$reqColor -ne '') {
                 # Keep Color
             } else {
                 \$cCode = "37" # White
             }
        }
        
        # Background is always Black (40)
        # Write-Host uses standard output, so we wrap in ANSI
        Write-Host "\${esc}[\${cCode}m\$line\${esc}[0m" -NoNewline
        Write-Host "" # Newline
        
        # Only print description line if we are in standard mode
        if (\$script:hasDescriptions) {
            if (\$descriptions[\$i]) {
                if (\$i -eq \$current) { 
                    \$d = "        \$(\$descriptions[\$i])" 
                    try { \$d = \$d.PadRight(\$w - 1) } catch {}
                    Write-Host "\${esc}[90m\$d\${esc}[0m"
                } else { 
                    # Spacer for non-selected if needed, or just nothing
                    Write-Host ""
                }
            } else {
                # Spacer
                Write-Host ""
            }
        }
    }
    \$footer = "`n  (ŠIPKY: pohyb, ENTER: výběr, ESC: zrušit)"
    # try { \$footer = \$footer.PadRight(\$w - 1) } catch {}
    Write-Host "\${esc}[90m\$footer\${esc}[0m"
}

Render \$qParts \$options \$descs \$colors \$selected \$startRow \$true \$isConfirm

while (\$true) {
    if ([Console]::KeyAvailable) {
        \$key = [Console]::ReadKey(\$true)
        if (\$key.Key -eq 'UpArrow') {
            if (\$selected -gt 0) { \$selected = \$selected - 1 } else { \$selected = \$options.Count - 1 }
            Render \$qParts \$options \$descs \$colors \$selected \$startRow \$false \$isConfirm
        }
        elseif (\$key.Key -eq 'DownArrow') {
            if (\$selected -lt \$options.Count - 1) { \$selected = \$selected + 1 } else { \$selected = 0 }
            Render \$qParts \$options \$descs \$colors \$selected \$startRow \$false \$isConfirm
        }
        elseif (\$key.Key -eq 'Enter') {
            \$esc = [char]27
            
            if (\$script:hasDescriptions) {
                \$h = (\$options.Count * 2) + 2
            } else {
                \$h = \$options.Count + 2
            }
            
            if (\$qParts.Count -gt 0) { \$h += 2 }
            
            if (\$isCursorSupported) {
                for (\$i = 0; \$i -lt (\$h + 1); \$i++) {
                    try {
                        [Console]::SetCursorPosition(0, \$startRow + \$i)
                        Write-Host (" " * (Get-Width)) -NoNewline
                    } catch {}
                }
                try { [Console]::SetCursorPosition(0, \$startRow) } catch {}
            } else {
                # ANSI Clear
                Write-Host ("{0}[{1}A{0}[J" -f \$esc, \$h) -NoNewline
            }
            
            Set-Content -Path '$resultFile' -Value \$keys[\$selected] -NoNewline
            exit 0
        }
        elseif (\$key.Key -eq 'Escape') { exit 1 }
    }
    Start-Sleep -Milliseconds 50
}
PS;

  file_put_contents($ps1File, "\xEF\xBB\xBF" . $psScript);
  $cmd = sprintf('powershell -NoProfile -ExecutionPolicy Bypass -File "%s" < CON', $ps1File);
  system($cmd, $returnCode);

  $input = '';
  if (file_exists($resultFile)) {
    $input = trim(file_get_contents($resultFile));
    @unlink($resultFile);
  }
  @unlink($ps1File);

  if ($returnCode !== 0 || $input === '') {
    echo colorize("\n❌ Operace byla zrušena.\n", COLOR_RED);
    exit(0);
  }

  return $input;
}

// MAIN LOGIC
echo "\n";
echo colorize("🚀 Consumption Tracker Version Management", COLOR_BLUE) . "\n\n";

// 1. Discover files
$files = [];
if (file_exists($composerFile)) {
  $composer = json_decode(file_get_contents($composerFile), true);
  $files['composer.json'] = [
    'path' => $composerFile,
    'current' => $composer['version'] ?? '0.0.0',
    'type' => 'json'
  ];
}

// Find all .env* files
foreach (glob($rootDir . '/.env*') as $path) {
  if (is_file($path)) {
    $content = file_get_contents($path);
    if (preg_match('/^APP_VERSION=(.*)$/m', $content, $m)) {
      $files[basename($path)] = [
        'path' => $path,
        'current' => trim($m[1], " '\""),
        'type' => 'env'
      ];
    }
  }
}

if (empty($files)) {
  echo colorize("❌ No versionable files found.\n", COLOR_RED);
  exit(1);
}

// 2. Sequential Prompting
$plannedChanges = [];
foreach ($files as $name => $info) {
  $options = get_version_options($info['current']);
  if (empty($options)) {
    echo colorize("⚠️ Skipping $name: Invalid version format ({$info['current']})\n", COLOR_YELLOW);
    continue;
  }

  // Print header once
  render_header("Select a new version for $name (currently {$info['current']})");

  // Call binary menu without its own header
  $choice = prompt("", $options);
  
  // Inline feedback (MUI style)
  // Reconstruct message with colors: "Select..." (Cyan) "currently X" (Yellow)
  $msgColored = colorize("Select a new version for $name ", COLOR_CYAN) . 
                colorize("(currently {$info['current']})", COLOR_YELLOW);
                
  // Move up 1 line to overwrite the question
  echo "\033[1A\r"; 
  echo colorize("? ", COLOR_GREEN) . $msgColored . " " . colorize($options[$choice]['label'], COLOR_GREEN) . "\n";

  $plannedChanges[$name] = [
    'old' => $info['current'],
    'new' => $options[$choice]['new'],
    'path' => $info['path'],
    'type' => $info['type']
  ];

  // Professional feedback like Lerna (removed to match mockup)
  // echo colorize("   ✓ ", COLOR_GREEN) . "$name chosen: " . colorize($options[$choice]['new'], COLOR_GREEN) . "\n";
}

if (empty($plannedChanges)) {
  echo colorize("❌ No changes planned.\n", COLOR_RED);
  exit(1);
}

// 3. Changes Summary
echo "\n" . colorize("Changes:", COLOR_YELLOW) . "\n";
foreach ($plannedChanges as $name => $change) {
  echo " - $name: " . colorize($change['old'], COLOR_YELLOW) . " => " . colorize($change['new'], COLOR_GREEN) . "\n";
}

// 4. Final Confirmation (using the menu)
echo "\n";
$confirmOptions = [
  'y' => ['label' => 'Yes, apply these changes', 'desc' => 'Update files and exit', 'color' => 'Green'],
  'n' => ['label' => 'No, cancel everything', 'desc' => 'Exit without saving', 'color' => 'Red']
];
$confirm = prompt("Are you sure you want to create these versions", $confirmOptions);

if ($confirm !== 'y') {
  echo colorize("\n❌ Bump zrušen. Žádné soubory nebyly upraveny.\n", COLOR_RED);
  exit(0);
}

// 5. Apply Changes
echo "\n" . colorize("🚀 Applying changes...", COLOR_BLUE) . "\n";

foreach ($plannedChanges as $name => $change) {
  echo "📝 Updating $name...";
  
  if ($change['type'] === 'json') {
    $data = json_decode(file_get_contents($change['path']), true);
    $data['version'] = $change['new'];
    file_put_contents(
      $change['path'],
      json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    );
  } else {
    $content = file_get_contents($change['path']);
    $content = preg_replace('/^APP_VERSION=.*$/m', "APP_VERSION={$change['new']}", $content);
    file_put_contents($change['path'], $content);
  }
  
  echo colorize(" ✓\n", COLOR_GREEN);
}

echo "\n";
echo colorize("  ✨ Version updates complete!", COLOR_GREEN) . "\n";
echo "\n";

