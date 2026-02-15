#!/usr/bin/env php
<?php

/**
 * Release Tag Script for Consumption Tracker API
 * 
 * Features:
 * - Checks for uncommitted changes
 * - Prompts for confirmation (merge/changelog check)
 * - Creates git tag based on composer.json version
 * - Pushes tag to remote
 */

// ANSI colors
define('COLOR_RESET', "\033[0m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_RED', "\033[31m");
define('COLOR_CYAN', "\033[36m");

function colorize($text, $color) {
    return $color . $text . COLOR_RESET;
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
  
  // Explicitly check for confirmation prompt - treat all these Czech questions as confirmations
  $isConfirm = true; 
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
        
        # Standard prompt style for ALL questions: Green "?", Cyan/Yellow text
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

function prompt_confirm($question) {
    $options = [
        'y' => ['label' => 'Ano, pokračovat', 'desc' => '', 'color' => 'Green'],
        'n' => ['label' => 'Ne, zrušit', 'desc' => '', 'color' => 'Red']
    ];
    $choice = prompt($question, $options);
    return $choice === 'y';
}

// Header
echo "\n";
echo colorize("🚀 Consumption Tracker Release Tagging", COLOR_BLUE) . "\n";
echo "──────────────────────────────────────────\n\n";

// 1. Check Git Status
exec('git status --porcelain', $output, $returnCode);
if (!empty($output)) {
    echo colorize("⚠️  Máš neuložené změny (uncommitted changes):\n", COLOR_YELLOW);
    foreach ($output as $line) {
        $status = substr($line, 0, 2);
        $file = substr($line, 3);
        
        $desc = match (trim($status)) {
            'M' => colorize('[Změněno]     ', COLOR_CYAN),
            'A' => colorize('[Nové]        ', COLOR_GREEN),
            'D' => colorize('[Smazáno]     ', COLOR_RED),
            'R' => colorize('[Přejmenováno]', COLOR_BLUE),
            '??' => colorize('[Nové/Netrack]', COLOR_GREEN),
            default => colorize("[$status]        ", COLOR_YELLOW),
        };
        
        echo "   $desc $file\n";
    }
    echo "\n";
    
    $options = [
        'y' => ['label' => 'Ano, ignorovat změny', 'desc' => 'Vytvoří tag jen pro aktuální commit', 'color' => 'Red'],
        'n' => ['label' => 'Ne, zrušit', 'desc' => 'Vrátím se k uložení změn', 'color' => 'Green']
    ];
    $choice = prompt("Chceš pokračovat i přes neuložené změny?", $options);
    
    if ($choice !== 'y') {
        echo colorize("\n❌ Operace zrušena.\n", COLOR_RED);
        exit(1);
    }
}

// 2. Load Version
$rootDir = dirname(__DIR__);
$composerFile = $rootDir . '/composer.json';
if (!file_exists($composerFile)) {
    echo colorize("❌ composer.json nenalezen!\n", COLOR_RED);
    exit(1);
}
$composer = json_decode(file_get_contents($composerFile), true);
$version = $composer['version'] ?? '0.0.0';
$tagName = "v$version";

// 3. Verify Upstream Remote
exec('git remote', $remotes, $remoteRet);
if (!in_array('upstream', $remotes)) {
    echo colorize("❌ Remote 'upstream' neexistuje!\n", COLOR_RED);
    echo "Tento skript vyžaduje nastavený 'upstream' pro hlavní repozitář.\n";
    echo "Přidej ho pomocí: git remote add upstream <url>\n";
    exit(1);
}
$targetRemote = 'upstream';

// 4. Check Upstream State
echo "\nKontroluji upstream ($targetRemote)...\n";
exec("git ls-remote --tags $targetRemote $tagName 2>&1", $remoteOut, $remoteRet);
if (!empty($remoteOut)) {
    echo colorize("❌ Tag $tagName již existuje na upstream!\n", COLOR_RED);
    echo "Nelze vydat stejnou verzi znovu.\n";
    exit(0);
}

// 5. Check Local Tag
exec("git rev-parse $tagName 2>&1", $void, $localExists);
if ($localExists === 0) {
    echo colorize("⚠️  Lokální tag $tagName existuje. Smažu ho a vytvořím nový pro aktuální verzi.\n", COLOR_YELLOW);
    exec("git tag -d $tagName");
}

// 6. Confirmations
echo "\nChystám se:\n";
echo " 1. Vytvořit tag: " . colorize($tagName, COLOR_GREEN) . "\n";
echo " 2. Pushnout na: " . colorize($targetRemote, COLOR_CYAN) . " (Hlavní repozitář)\n\n";

if (!prompt_confirm("Máš vše zmérgované do main/master větve?")) {
    echo colorize("\n❌ Prosím, nejprve zmérguj změny.\n", COLOR_RED);
    exit(0);
}

if (!prompt_confirm("Je changelog aktualizovaný?")) {
    echo colorize("\n❌ Prosím, nejprve aktualizuj changelog.\n", COLOR_RED);
    exit(0);
}

if (!prompt_confirm("Opravdu vytvořit tag a pushnout na $targetRemote?")) {
    echo colorize("\n❌ Operace zrušena.\n", COLOR_RED);
    exit(0);
}

// 7. Create Tag
echo "\n" . colorize("📦 Vytvářím tag $tagName...", COLOR_BLUE);
// Force create? No, we deleted it.
exec("git tag -a $tagName -m \"Version $version\" 2>&1", $tagOut, $tagRet);

if ($tagRet !== 0) {
    echo colorize(" ❌ Chyba!\n", COLOR_RED);
    echo colorize(implode("\n", $tagOut), COLOR_RED) . "\n";
    exit(1);
}
echo colorize(" ✓\n", COLOR_GREEN);

// 8. Push Tag
echo colorize("⬆️  Posílám tag na $targetRemote...", COLOR_BLUE);
exec("git push $targetRemote $tagName 2>&1", $pushOut, $pushRet);

if ($pushRet !== 0) {
    echo colorize(" ❌ Chyba při pushování!\n", COLOR_RED);
    echo "Zkus manuálně: git push $targetRemote $tagName\n";
    echo colorize(implode("\n", $pushOut), COLOR_RED) . "\n";
    exit(1);
}

echo colorize(" ✓\n", COLOR_GREEN);
echo "\n" . colorize("✨ Hotovo! Verze $version byla úspěšně vydána na $targetRemote.", COLOR_GREEN) . "\n\n";
