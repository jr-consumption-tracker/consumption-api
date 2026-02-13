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

function prompt_confirm($question) {
    echo colorize("\n? ", COLOR_GREEN) . colorize($question, COLOR_CYAN) . " (y/n) ";
    $handle = defined('STDIN') ? STDIN : fopen('php://stdin', 'r');
    $line = trim(fgets($handle));
    return strtolower($line) === 'y';
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
        echo "   $line\n";
    }
    echo "\n";
    if (!prompt_confirm("Chceš pokračovat i přes neuložené změny? (Doporučeno: ne)")) {
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

// 3. Confirmations
echo "Chystám se vytvořit tag: " . colorize($tagName, COLOR_GREEN) . "\n\n";

if (!prompt_confirm("Máš vše zmérgované do main/master větve?")) {
    echo colorize("\n❌ Prosím, nejprve zmérguj změny.\n", COLOR_RED);
    exit(0);
}

if (!prompt_confirm("Je changelog aktualizovaný?")) {
    echo colorize("\n❌ Prosím, nejprve aktualizuj changelog.\n", COLOR_RED);
    exit(0);
}

if (!prompt_confirm("Opravdu vytvořit a pushnout tag $tagName?")) {
    echo colorize("\n❌ Operace zrušena.\n", COLOR_RED);
    exit(0);
}

// 4. Create Tag
echo "\n" . colorize("📦 Vytvářím tag $tagName...", COLOR_BLUE);
exec("git tag -a $tagName -m \"Version $version\"", $tagOut, $tagRet);

if ($tagRet !== 0) {
    echo colorize(" ❌ Chyba!\n", COLOR_RED);
    echo implode("\n", $tagOut) . "\n";
    exit(1);
}
echo colorize(" ✓\n", COLOR_GREEN);

// 5. Push Tag
echo colorize("⬆️  Posílám tag na origin...", COLOR_BLUE);
exec("git push origin $tagName", $pushOut, $pushRet);

if ($pushRet !== 0) {
    echo colorize(" ❌ Chyba při pushování!\n", COLOR_RED);
    echo "Zkus manuálně: git push origin $tagName\n";
    echo implode("\n", $pushOut) . "\n";
    exit(1);
}

echo colorize(" ✓\n", COLOR_GREEN);
echo "\n" . colorize("✨ Hotovo! Verze $version byla úspěšně vydána.", COLOR_GREEN) . "\n\n";
