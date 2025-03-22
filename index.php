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

echo "hits:<br />";
/** @var Matchup $matchup */
foreach ($matchups as $matchup) {
    echo $matchup->getBatterStats()->getName() . ": "
        . "; hitStarter=" . ($matchup->didHit(true) ? 'yes' : 'no')
        . "; hitAny=" . ($matchup->didHit(false) ? 'yes' : 'no')
        . "<br />";
}

echo "<br />home runs:<br />";
foreach ($matchups as $matchup) {
    echo $matchup->getBatterStats()->getName() . ": "
        . "; hrStarter=" . ($matchup->didHomer(true) ? 'yes' : 'no')
        . "; hrAny=" . ($matchup->didHomer(false) ? 'yes' : 'no')
        . "<br />";
}