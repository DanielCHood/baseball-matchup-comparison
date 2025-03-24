<?php

namespace DanielCHood\BaseballMatchupComparison;

class PlayerStats {
    private array $tags = [];

    public readonly float $battingAverage;

    public function __construct(
        private readonly int $id,
        private readonly int $teamId,
        private readonly string $position,
        private readonly string $name,
        array $plays,
        private readonly array $tagsToUse = ['zone-', 'type-', 'zone-;type-'],
    ) {
        $this->process($plays);
    }

    private function process(array $plays): void {
        $plays = array_filter($plays, function($play) { return $play['position'] === $this->position; });
        $noCountTypes = ['batter-reached-on-error-batter-to-first', 'catchers-interference-batter-to-firsterror', 'hit-by-pitch', 'ball'];

        $zones = new Zones();

        // set overall batting average;
        if ($this->position === 'batter') {
            $hits = array_filter($plays, function ($play) { return $this->isHit($play['result']); });
            $atBats = array_unique(array_column($plays, 'atBatId'));
            $this->battingAverage = count($hits) / max(count($atBats), 1);
        }
        else {
            $this->battingAverage = 0.00;
        }

        $pitchCount = 0;

        foreach ($plays as $play) {
            if (in_array($play['result'], $noCountTypes)) {
                continue;
            }

            $zone = $zones->getZone($play['x'], $play['y']);
            if ($zone === null) {
                // zone not found, probably out of frame
                continue;
            }

            $type = $play['type'];

            foreach ($this->tagsToUse as $tag) {
                $tag = str_replace(
                    [
                        'zone',
                        'type',
                    ],
                    [
                        'zone-' . $zone,
                        'type-' . $type,
                    ],
                    $tag
                );

                if (!isset($this->tags[$tag])) {
                    $this->tags[$tag] = [
                        'pitchCount' => 0,
                        'hits' => 0,
                        'homeRuns' => 0,
                        'velocity' => 0,
                        'velocityHits' => 0,
                        'velocityHomeRuns' => 0,
                    ];

                    if (stristr($tag, 'zone-')) {
                        $this->tags[$tag]['zone'] = $zone;
                    }

                    if (stristr($tag, 'type-')) {
                        $this->tags[$tag]['type'] = $type;
                    }
                }

                $this->tags[$tag]['pitchCount']++;

                $this->tags[$tag]['velocity'] += $play['velocity'];

                if ($this->isHit($play['result'])) {
                    $this->tags[$tag]['hits'] += 1;
                    $this->tags[$tag]['velocityHits'] += $play['velocity'];
                }

                if ($this->isHomeRun($play['result'])) {
                    $this->tags[$tag]['homeRuns'] += 1;
                    $this->tags[$tag]['velocityHomeRuns'] += $play['velocity'];
                }
            }

            $pitchCount++;
        }

        foreach ($this->tags as $key => $tag) {
            $this->tags[$key]['velocityHomeRuns'] = $this->tags[$key]['homeRuns'] > 0 ? $this->tags[$key]['velocity'] / $this->tags[$key]['homeRuns'] : 0;
            $this->tags[$key]['velocityHits'] = $this->tags[$key]['hits'] > 0 ? $this->tags[$key]['velocity'] / $this->tags[$key]['hits'] : 0;
            $this->tags[$key]['velocity'] = $this->tags[$key]['velocity'] / $this->tags[$key]['pitchCount'];

            $this->tags[$key]['hitPercent'] = ($this->tags[$key]['hits'] / $this->tags[$key]['pitchCount']) * 100;
            $this->tags[$key]['homeRunPercent'] = ($this->tags[$key]['homeRuns'] / $this->tags[$key]['pitchCount']) * 100;
            $this->tags[$key]['hitPercentWeighted'] = $this->tags[$key]['hitPercent'] * ($this->tags[$key]['pitchCount'] / $pitchCount);
            $this->tags[$key]['homeRunPercentWeighted'] = $this->tags[$key]['homeRunPercent'] * ($this->tags[$key]['pitchCount'] / $pitchCount);
        }
    }

    private function isHit(string $result): bool {
        $hitResultTypes = ['single', 'bunt-single', 'triple', 'double', 'bunt-double', 'ground-rule-double', 'home-run', 'batters-fielders-choice-all-runners-safe'];

        return in_array($result, $hitResultTypes) ? 1 : 0;
    }

    private function isHomeRun(string $result): bool {
        $hitResultTypes = ['home-run'];

        return in_array($result, $hitResultTypes) ? 1 : 0;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getTeamId(): int {
        return $this->teamId;
    }

    public function getTagged(): array {
        return $this->tags;
    }

    public function toArray(): array {
        $tags = $this->tags;
        uksort($tags, function ($a, $b) {
            return $b < $a;
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
            'tagged' => $tags
        ];
    }
}