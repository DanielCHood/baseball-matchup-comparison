<?php

use DanielCHood\BaseballMatchupComparison\DataProvider\LeetisApiEvent;
use DanielCHood\BaseballMatchupComparison\Matchup;
use DanielCHood\BaseballMatchupComparison\Repository\Event;
use GuzzleHttp\Client;

$eventId = $_GET['id'] ?? 401570895;

$dataProvider = new LeetisApiEvent(
    new Client([
        'base_uri' => 'https://mlb.leetis.com/batting-data/'
    ])
);

$repo = new Event($dataProvider);
$matchups = $repo->getAllMatchups($eventId, ['zone;type']);

$matchups = $matchups->sortByDesc(function (Matchup $matchup) {
    return $matchup->getHitScore();
});

echo "hits:<br />";
/** @var Matchup $matchup */
foreach ($matchups as $matchup) {
    echo $matchup->getBatterStats()->getName() . ": " . $matchup->getBatterStats()
        . "; hitStarter=" . ($matchup->didHit(true) ? 'yes' : 'no')
        . "; hitAny=" . ($matchup->didHit(false) ? 'yes' : 'no')
        . "<br />";
}

$matchups = $matchups->sortByDesc(function (Matchup $matchup) {
    return $matchup->getHomeRunScore();
});

echo "<br />home runs:<br />";
foreach ($matchups as $matchup) {
    echo $matchup->getBatterStats()->getName() . ": " . $matchup->getHomeRunScore()
        . "; hrStarter=" . ($matchup->didHomer(true) ? 'yes' : 'no')
        . "; hrAny=" . ($matchup->didHomer(false) ? 'yes' : 'no')
        . "<br />";
}