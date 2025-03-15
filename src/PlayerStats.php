<?php

namespace DanielCHood\BaseballMatchupComparison;

class PlayerStats {
    private array $tags = [];

    public function __construct(
        private readonly int $id,
        private readonly string $position,
        private readonly string $name,
        private readonly array $plays,
        private readonly array $tagsToUse = ['zone-', 'type-', 'zone-;type-'],
    ) {
        $this->process();
    }

    private function process(): void {
        $plays = array_filter($this->plays, function($play) { return $play['position'] === $this->position; });
        $noCountTypes = ['batter-reached-on-error-batter-to-first', 'catchers-interference-batter-to-firsterror', 'hit-by-pitch', 'ball'];

        $zones = new Zones();

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

        foreach ($this->tags as &$tag) {
            $tag['velocityHomeRuns'] = $tag['homeRuns'] > 0 ? $tag['velocity'] / $tag['homeRuns'] : 0;
            $tag['velocityHits'] = $tag['hits'] > 0 ? $tag['velocity'] / $tag['hits'] : 0;
            $tag['velocity'] = $tag['velocity'] / $tag['pitchCount'];

            $tag['hitPercent'] = ($tag['hits'] / $tag['pitchCount']) * 100;
            $tag['homeRunPercent'] = ($tag['homeRuns'] / $tag['pitchCount']) * 100;
            $tag['hitPercentWeighted'] = $tag['hitPercent'] * ($tag['pitchCount'] / $pitchCount);
            $tag['homeRunPercentWeighted'] = $tag['homeRunPercent'] * ($tag['pitchCount'] / $pitchCount);
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