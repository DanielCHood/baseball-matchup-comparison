<?php

namespace DanielCHood\BaseballMatchupComparison\Repository;

use DanielCHood\BaseballMatchupComparison\DataProvider\EventInterface;
use DanielCHood\BaseballMatchupComparison\Entity\Athlete;
use DanielCHood\BaseballMatchupComparison\Entity\Team;
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
            $pitcher = new Athlete(
                new Team(
                    $startingPitcher['teamId'],
                    $data['teams'][$startingPitcher['teamId']]['name'],
                    $data['teams'][$startingPitcher['teamId']]['abbreviation'],
                ),
                $startingPitcher['id'],
                $startingPitcher['name'],
                'pitcher',
            );

            $pitcherStats = new PlayerStats(
                $pitcher,
                array_filter($startingPitcher['plays'], function($play) use ($id) {
                    return $play['position'] === 'pitcher' && $play['eventId'] < $id;
                }),
                $tags
            );

            foreach ($batters as $batter) {

                if ($startingPitcher['teamId'] === $batter['teamId']) {
                    continue;
                }

                $player = new Athlete(
                    new Team(
                        $batter['teamId'],
                        $data['teams'][$batter['teamId']]['name'],
                        $data['teams'][$batter['teamId']]['abbreviation'],
                    ),
                    $batter['id'],
                    $batter['name'],
                    'batter',
                );

                $batterStats = new PlayerStats(
                    $player,
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