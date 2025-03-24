<?php

use DanielCHood\BaseballMatchupComparison\DataProvider\LeetisApiEvent;
use DanielCHood\BaseballMatchupComparison\Matchup;
use DanielCHood\BaseballMatchupComparison\Repository\Event;
use GuzzleHttp\Client;

$path = $_SERVER['REQUEST_URI'] ?? '';
$path = ltrim($path, '/');

if (
    !str_starts_with($path, '.')
    && !str_contains($path, '/')
    && file_exists('../'.$path)
    && !is_dir('../'.$path)
) {
    echo nl2br(file_get_contents('../'.$path));
    die;
}

$eventId = $_GET['id'] ?? 401570895;

$dataProvider = new LeetisApiEvent(
    new Client([
        'base_uri' => 'https://mlb.leetis.com/batting-data/'
    ])
);

$repo = new Event($dataProvider);
$matchups = $repo->getAllMatchups($eventId, ['zone;type']);

/** @var Matchup $matchup */
foreach ($matchups as $matchup) {
    echo "<h2>" .$matchup->getPitcherStats()->getName() . " vs " . $matchup->getBatterStats()->getName() . "</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr>";
    echo "<th>Zone</th>";
    echo "<th>Pitch Type</th>";
    echo "<th>Pitch Count</th>";
    echo "<th>Hit Rate</th>";
    echo "<th>Home Run Rate</th>";
    echo "</tr>";
    foreach ($matchup->getBatterStats()->getTagged() as $stats) {
        echo "<tr>";
        echo "<td>" . $stats['zone'] . "</td>";
        echo "<td>" . $stats['type'] . "</td>";
        echo "<td>" . $stats['pitchCount'] . "</td>";
        echo "<td>" . $stats['hitPercent'] . "</td>";
        echo "<td>" . $stats['homeRunPercent'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}