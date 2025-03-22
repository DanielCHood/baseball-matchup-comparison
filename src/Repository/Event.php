<?php

namespace DanielCHood\BaseballMatchupComparison\Repository;

use DanielCHood\BaseballMatchupComparison\DataProvider\EventInterface;
use DanielCHood\BaseballMatchupComparison\Matchup;
use DanielCHood\BaseballMatchupComparison\PlayerStats;
use DateTime;
use Illuminate\Support\Collection;

class Event {
    public function __construct(
        private EventInterface $dataProvider,
    ) {
    }

    public function getEventIdsOnDate(DateTime $date): array {
        return $this->dataProvider->getIdsForDate($date)['ids'] ?? [];
    }

    public function getAllMatchups(int $id, array $tags): Collection {
        $data = $this->dataProvider->load($id);
        $startingPitchers = $data['startingPitchers'] ?? [];
        $batters = $data['batters'] ?? [];
        $homeTeamId = $data['homeTeamId'] ?? null;
        $awayTeamId = $data['awayTeamId'] ?? null;
        $homeTeamMoneyline = $data['homeMoneyLine'] ?? null;
        $awayTeamMoneyline = $data['awayMoneyLine'] ?? null;

        $matchups = new Collection();

        foreach ($startingPitchers as $startingPitcher) {
            $pitcherStats = new PlayerStats(
                $startingPitcher['id'],
                $startingPitcher['teamId'],
                'pitcher',
                $startingPitcher['name'],
                array_filter($startingPitcher['plays'], function($play) use ($id) {
                    return $play['position'] === 'pitcher' && $play['eventId'] < $id;
                }),
                $tags
            );

            foreach ($batters as $batter) {

                if ($startingPitcher['teamId'] === $batter['teamId']) {
                    continue;
                }

                $batterStats = new PlayerStats(
                    $batter['id'],
                    $batter['teamId'],
                    'batter',
                    $batter['name'],
                    array_filter($batter['plays'], function($play) use ($id) {
                        return $play['position'] === 'batter' && $play['eventId'] < $id;
                    }),
                    $tags
                );

                $matchups->push(
                    new Matchup(
                        $homeTeamId,
                        $awayTeamId,
                        $homeTeamMoneyline,
                        $awayTeamMoneyline,
                        $pitcherStats,
                        $batterStats,
                        array_filter($batter['plays'], function($play) use ($id) {
                            return $play['position'] === 'batter' && $play['eventId'] == $id;
                        }),
                    )
                );
            }
        }

        return $matchups;
    }
}