<?php

namespace DanielCHood\BaseballMatchupComparison;

class PlayerStats {
    private array $zones = [];
    private array $pitchTypes = [];

    private array $blended = [];

    public function __construct(
        private readonly string $position,
        private readonly string $name,
        private readonly array $plays,
    ) {
        $this->process();
    }

    private function process(): void {
        $plays = array_filter($this->plays, function($play) { return $play['position'] === $this->position; });
        $noCountTypes = ['batter-reached-on-error-batter-to-first', 'catchers-interference-batter-to-firsterror', 'hit-by-pitch', 'ball'];

        $zones = new Zones();
        $atBatIds = [];

        foreach ($plays as $play) {
            if (in_array($play['result'], $noCountTypes)) {
                continue;
            }

            $zone = $zones->getZone($play['x'], $play['y']);
            if ($zone === null) {
                // zone not found, probably out of frame
                continue;
            }

            if (!isset($this->zones[$zone])) {
                $this->zones[$zone] = [
                    'zone' => $zone,
                    'pitchCount' => 0,
                    #'atBatCount' => 0,
                    'hits' => 0,
                    'homeRuns' => 0,
                ];
            }

            $type = $play['type'];
            if (!isset($this->pitchTypes[$type])) {
                $this->pitchTypes[$type] = [
                    'pitchType' => $type,
                    'pitchCount' => 0,
                    #'atBatCount' => 0,
                    'hits' => 0,
                    'homeRuns' => 0,
                ];
            }

            if (!isset($this->blended[$zone][$type])) {
                $this->blended[$zone][$type] = [
                    'zone' => $zone,
                    'pitchType' => $type,
                    'pitchCount' => 0,
                    #'atBatCount' => 0,
                    'hits' => 0,
                    'homeRuns' => 0,
                ];
            }

            if (!isset($atBatIds['zone-'.$zone])) {
                $atBatIds['zone-'.$zone] = [];
            }

            if (!isset($atBatIds['type-'.$type])) {
                $atBatIds['type-'.$type] = [];
            }

            if (!isset($atBatIds['blended-'.$zone.'-'.$type])) {
                $atBatIds['blended-'.$zone.'-'.$type] = [];
            }

            if (!in_array($play['atBatId'], $atBatIds['zone-'.$zone])) {
                $atBatIds['zone-'.$zone][] = $play['atBatId'];
            }

            if (!in_array($play['atBatId'], $atBatIds['type-'.$type])) {
                $atBatIds['type-'.$type][] = $play['atBatId'];
            }

            if (!in_array($play['atBatId'], $atBatIds['blended-'.$zone.'-'.$type])) {
                $atBatIds['blended-'.$zone.'-'.$type][] = $play['atBatId'];
            }

            $this->zones[$zone]['pitchCount']++;
            #$this->zones[$zone]['atBatCount'] = count($atBatIds['zone-'.$zone]);
            $this->zones[$zone]['hits'] += $this->isHit($play['result']) ? 1 : 0;
            $this->zones[$zone]['homeRuns'] += $this->isHomeRun($play['result']) ? 1 : 0;

            $this->pitchTypes[$type]['pitchCount']++;
            #$this->pitchTypes[$type]['atBatCount'] = count($atBatIds['type-'.$type]);
            $this->pitchTypes[$type]['hits'] += $this->isHit($play['result']) ? 1 : 0;
            $this->pitchTypes[$type]['homeRuns'] += $this->isHomeRun($play['result']) ? 1 : 0;

            $this->blended[$zone][$type]['pitchCount']++;
            #$this->blended[$zone][$type]['atBatCount'] = count($atBatIds['blended-'.$zone.'-'.$type]);
            $this->blended[$zone][$type]['hits'] += $this->isHit($play['result']) ? 1 : 0;
            $this->blended[$zone][$type]['homeRuns'] += $this->isHomeRun($play['result']) ? 1 : 0;
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

    public function toArray(): array {
        return [
            'name' => $this->name,
            'position' => $this->position,
            'zones' => $this->zones,
            'pitchTypes' => $this->pitchTypes,
            'blended' => $this->blended,
        ];
    }
}