<?php

namespace DanielCHood\BaseballMatchupComparison\Repository;

use DanielCHood\BaseballMatchupComparison\DataProvider\EventInterface;
use DanielCHood\BaseballMatchupComparison\Matchup;
use DanielCHood\BaseballMatchupComparison\PlayerStats;
use Illuminate\Support\Collection;

class Event {
    public function __construct(
        private EventInterface $dataProvider,
    ) {
    }

    public function getAllMatchups(int $id): Collection {
        $data = $this->dataProvider->load($id);
        $startingPitchers = $data['startingPitchers'];
        $batters = $data['batters'];

        $matchups = new Collection();

        foreach ($startingPitchers as $startingPitcher) {
            $pitcherStats = new PlayerStats(
                'pitcher',
                $startingPitcher['name'],
                array_filter($startingPitcher['plays'], function($play) use ($id) {
                    return $play['position'] === 'pitcher' && $play['eventId'] < $id;
                })
            );

            foreach ($batters as $batter) {
                $batterStats = new PlayerStats(
                    'batter',
                    $batter['name'],
                    array_filter($batter['plays'], function($play) use ($id) {
                        return $play['position'] === 'batter' && $play['eventId'] < $id;
                    })
                );

                $matchups->push(new Matchup($pitcherStats, $batterStats));
            }
        }

        return $matchups;
    }
}