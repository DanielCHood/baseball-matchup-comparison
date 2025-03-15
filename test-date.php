<?php

require_once('vendor/autoload.php');

use DanielCHood\BaseballMatchupComparison\DataProvider\LeetisApiEvent;
use DanielCHood\BaseballMatchupComparison\Matchup;
use DanielCHood\BaseballMatchupComparison\Prediction\HomeRun;
use DanielCHood\BaseballMatchupComparison\Repository\Event;
use GuzzleHttp\Client;

$date = new DateTime($argv[1] ?? 'now');

$dataProvider = new LeetisApiEvent(
    new Client([
        'base_uri' => 'https://mlb.leetis.com/batting-data/'
    ])
);

$iterations = 0;

$groups = [];

while (true) {
    $repo = new Event($dataProvider);
    $eventIds = $repo->getEventIdsOnDate($date);

    $iterations++;
    $date = $date->modify('+1 day');

    foreach ($eventIds as $eventId) {
        $matchups = $repo->getAllMatchups($eventId, ['zone;type']);

        /** @var Matchup $matchup */
        foreach ($matchups as $matchup) {

            $predict = new HomeRun($matchup);
            if ($predict->isValid()) {
                if (!isset($groups[$predict->getLabel()])) {
                    $groups[$predict->getLabel()] = ['wins' => 0, 'losses' => 0];
                }

                $groups[$predict->getLabel()]['wins'] += ($predict->win() ? 1 : 0);
                $groups[$predict->getLabel()]['losses'] += (!$predict->win() ? 1 : 0);

                $groups[$predict->getLabel()]['rate'] = $groups[$predict->getLabel()]['wins'] / ($groups[$predict->getLabel()]['wins'] + $groups[$predict->getLabel()]['losses']) * 100;
            }
        }
    }

    if ($iterations === 90) {
        break;
    }
}

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