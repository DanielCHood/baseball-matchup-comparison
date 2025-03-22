<?php

require_once('vendor/autoload.php');

use DanielCHood\BaseballMatchupComparison\DataProvider\LeetisApiEvent;
use DanielCHood\BaseballMatchupComparison\Matchup;
use DanielCHood\BaseballMatchupComparison\Prediction\HomeRunStartingPitcher;
use DanielCHood\BaseballMatchupComparison\Prediction\HomeRunAnyPitcher;
use DanielCHood\BaseballMatchupComparison\Prediction\PredictionInterface;
use DanielCHood\BaseballMatchupComparison\Repository\Event;
use GuzzleHttp\Client;

$startDate = new DateTime($argv[1] ?? 'now');
$maxIterations = $argv[2] ?? 1;
$maxIterations = intval($maxIterations);

$dataProvider = new LeetisApiEvent(
    new Client([
        'base_uri' => 'https://mlb.leetis.com/batting-data/'
    ])
);

$iterations = 0;

$groups = [];

$date = $startDate;

$predicters = [
    #HomeRunAnyPitcher::class,
    HomeRunStartingPitcher::class,
];

while (true) {
    $repo = new Event($dataProvider);
    $eventIds = $repo->getEventIdsOnDate($startDate);

    $iterations++;

    #echo $date->format('Y-m-d') . " (iteration: " . $iterations . "/" . $maxIterations . ")\n";

    $date = $date->modify('+1 day');

    foreach ($eventIds as $eventId) {
        $groupPrefix = '';
        #$groupPrefix .= $date->format('m') . '-';

        try {
            $matchups = $repo->getAllMatchups($eventId, ['zone;type']);
        } catch (\TypeError $e) {
            echo "Error processing event id: " . $eventId . "\n";
            continue;
        }

        /** @var Matchup $matchup */
        foreach ($matchups as $matchup) {
            /** @var PredictionInterface $predicter */
            foreach ($predicters as $predicter) {
                $predict = new $predicter($matchup);
                if ($predict->isValid()) {
                    $label = $groupPrefix . $predict->getLabel();

                    if (!isset($groups[$label])) {
                        $groups[$label] = ['wins' => 0, 'losses' => 0];
                    }

                    $groups[$label]['wins'] += ($predict->win() ? 1 : 0);
                    $groups[$label]['losses'] += (!$predict->win() ? 1 : 0);

                    $groups[$label]['rate'] = $groups[$label]['wins'] / ($groups[$label]['wins'] + $groups[$label]['losses']) * 100;
                }
            }
        }
    }

    if ($iterations === $maxIterations) {
        break;
    }
}

echo $date->format('Y-m-d') . "\n";

uasort($groups, function ($a, $b) {
    return $a['wins'] <=> $b['wins'];
});

var_dump($groups);

echo "Total: "
    . array_sum(array_column($groups, 'wins')) . "-"
    . array_sum(array_column($groups, 'losses'))
    . " ("
    . (
        array_sum(array_column($groups, 'wins'))
        / (array_sum(array_column($groups, 'wins')) + array_sum(array_column($groups, 'losses')))
        * 100
    )
    . "%)\n\n";