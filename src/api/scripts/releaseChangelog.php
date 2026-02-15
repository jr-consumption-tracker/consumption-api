#!/usr/bin/env php
<?php

/**
 * Consumption Tracker Changelog Preview
 * 
 * Ported from MUI's release-changelog logic
 */

// ANSI colors for terminal feedback
define('COLOR_RESET', "\033[0m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_CYAN', "\033[36m");

function colorize($text, $color)
{
  return $color . $text . COLOR_RESET;
}

/**
 * Extracts and sorts [tag] prefixes
 */
function parse_tags($commitMessage) {
  if (preg_match('/^(\[[\w-]+\])+/', $commitMessage, $matches)) {
    preg_match_all('/([\w-]+)/', $matches[0], $tagMatches);
    $tags = array_map('strtolower', $tagMatches[0]);
    sort($tags);
    return implode(',', $tags);
  }
  return '';
}

/**
 * Finds the latest tag matching v* from upstream remote
 */
function get_latest_tag() {
  exec('git ls-remote --tags upstream 2>&1', $out, $return);
  if ($return !== 0 || empty($out)) {
    return null;
  }
  
  $tags = [];
  foreach ($out as $line) {
    // Format: "hash refs/tags/v0.2.0" or "hash refs/tags/v0.2.0^{}"
    if (preg_match('#refs/tags/(v[\d.]+(?:-[a-z]+\.\d+)?)(?:\^\{\})?$#', $line, $m)) {
      $tags[] = $m[1];
    }
  }
  
  if (empty($tags)) {
    return null;
  }
  
  // Remove duplicates and sort by version (descending)
  $tags = array_unique($tags);
  usort($tags, function($a, $b) {
    return version_compare($b, $a);
  });
  
  return $tags[0];
}

/**
 * Filters out maintenance and irrelevant commits
 */
function filter_commit($message) {
  return (
    !str_starts_with($message, 'Bump') &&
    !str_starts_with($message, 'Lock file maintenance') &&
    !str_starts_with($message, '[website]')
  );
}

// 1. Get environment info
$composerFile = dirname(__DIR__) . '/composer.json';
$composer = json_decode(file_get_contents($composerFile), true);
$currentVersion = $composer['version'] ?? '0.0.0';

$latestTag = get_latest_tag();
$baseTag = $latestTag;
$releaseVersion = "v$currentVersion";

// 2. Fetch commits from upstream/main
$format = "%s|%h|%an";
// Compare upstream/main against the latest tag
$range = $baseTag ? "$baseTag..upstream/main" : "-n 50 upstream/main";
$cmd = "git log $range --format=\"$format\" 2>&1";
exec($cmd, $rawCommits, $return);

if ($return !== 0 || empty($rawCommits)) {
  $sinceMsg = $baseTag ? "since $baseTag" : "(no tags found)";
  echo colorize("ℹ️  No commits found $sinceMsg\n", COLOR_YELLOW);
  exit(0);
}

// 3. Process and Filter
$commitsItems = [];
$contributors = [];

foreach ($rawCommits as $index => $line) {
  // Use a safer split to handle possible pipes in messages (rare but possible)
  $parts = explode('|', $line);
  if (count($parts) < 3) continue;
  
  $author = array_pop($parts);
  $hash = array_pop($parts);
  $message = implode('|', $parts);
  
  if (!filter_commit($message)) continue;

  $commitsItems[] = [
    'message' => $message,
    'hash' => $hash,
    'author' => $author,
    'index' => $index, // for breaking ties (date desc)
    'tags' => parse_tags($message)
  ];

  if (!in_array($author, $contributors)) {
    $contributors[] = $author;
  }
}

// 4. Sorting (Tags ASC, then Date DESC)
usort($commitsItems, function($a, $b) {
  if ($a['tags'] === $b['tags']) {
    return $a['index'] - $b['index']; // git log provides newest first (lower index = newer)
  }
  return strcmp($a['tags'], $b['tags']);
});

// 5. Generate Markdown
$nowFormatted = date('j. n. Y');
$numContributors = count($contributors);
sort($contributors);
$contributorHandles = implode(', ', array_map(fn($c) => "@$c", $contributors));

$changes = [];
foreach ($commitsItems as $item) {
  $msg = $item['message'];
  // Ensure hash link if no PR reference
  if (!preg_match('/\(#[0-9]+\)$/', $msg)) {
    $msg .= " ({$item['hash']})";
  }
  $changes[] = "- $msg @{$item['author']}";
}

// Czech declension for "A big thanks to X contributors"
if ($numContributors === 1) {
  $thanksLine = "Velké díky 1 přispěvateli, který umožnil toto vydání.";
} else {
  $thanksLine = "Velké díky všem {$numContributors} přispěvatelům, kteří umožnili toto vydání.";
}

$changelog = "\n## TODO NÁZEV VERZE\n";
$changelog .= "<!-- generováno porovnáním $range -->\n";
$changelog .= "_{$nowFormatted}_\n\n";
$changelog .= $thanksLine . " Zde je přehled změn ✨:\n\n";
$changelog .= implode("\n", $changes) . "\n\n";
$changelog .= "Všichni přispěvatelé tohoto vydání v abecedním pořadí: " . $contributorHandles . "\n";

// 6. Terminal Output
echo "\n";
echo colorize("📋 Náhled Changelogu\n", COLOR_BLUE);
echo "───────────────────────────────────────────────────\n";

echo $changelog;

echo "\n" . colorize("Celkem filtrovaných commitů: " . count($commitsItems), COLOR_CYAN) . "\n\n";
