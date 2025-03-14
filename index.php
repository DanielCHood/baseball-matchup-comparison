<?php

use DanielCHood\BaseballMatchupComparison\DataProvider\LeetisApiEvent;
use DanielCHood\BaseballMatchupComparison\Matchup;
use DanielCHood\BaseballMatchupComparison\Repository\Event;
use GuzzleHttp\Client;

$eventId = 401570895;

$dataProvider = new LeetisApiEvent(
    new Client([
        'base_uri' => 'https://mlb.leetis.com/batting-data/'
    ])
);

$repo = new Event($dataProvider);
$matchups = $repo->getAllMatchups($eventId);

/** @var Matchup $matchup */
foreach ($matchups as $matchup) {
    echo json_encode($matchup->getPitcherStats()->toArray());
    break;
}