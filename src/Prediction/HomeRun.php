<?php

namespace DanielCHood\BaseballMatchupComparison\Prediction;

use DanielCHood\BaseballMatchupComparison\Matchup;

class HomeRun {
    public function __construct(
        private readonly Matchup $matchup
    ) {

    }

    public function isValid(): bool {
        return $this->getHomeRunScore() > 0
            && $this->getBatterPitchCount() >= 400
            && $this->getPitcherPitchCount() >= 400
            && $this->getHitScore() > 2.5000
            && $this->getVelocityScore() > 1.5;
    }

    public function getHitScore(): float {
        $score = 0;

        foreach ($this->matchup->getPitcherStats()->getTagged() as $sectionName => $section) {
            $batterSection = $this->matchup->getBatterStats()->getTagged()[$sectionName] ?? null;
            // batter hasn't seen a pitch with this tag yet
            if (!$batterSection) {
                continue;
            }

            $score += ($section['hitPercentWeighted'] * $batterSection['hitPercentWeighted']);
        }

        return $score;
    }

    public function getHomeRunScore(): float {
        $score = 0;

        foreach ($this->matchup->getPitcherStats()->getTagged() as $sectionName => $section) {
            $batterSection = $this->matchup->getBatterStats()->getTagged()[$sectionName] ?? null;
            // batter hasn't seen a pitch with this tag yet
            if (!$batterSection) {
                continue;
            }

            $score += ($section['homeRunPercentWeighted'] * $batterSection['homeRunPercentWeighted']);
        }

        return $score;
    }


    public function getVelocityScore(): float {
        $score = 0;

        foreach ($this->matchup->getPitcherStats()->getTagged() as $sectionName => $section) {
            $batterSection = $this->matchup->getBatterStats()->getTagged()[$sectionName] ?? null;
            // batter hasn't seen a pitch with this tag yet
            if (!$batterSection) {
                continue;
            }

            $velocity = $section['velocity'];
            $frequency = $section['pitchCount'] / $this->getPitcherPitchCount();

            $batterAverageSeenVelocity = $batterSection['velocity'] ?? 0;
            if ($batterAverageSeenVelocity === 0) {
                continue;
            }

            $score += ($batterAverageSeenVelocity - $velocity) * $frequency;
        }

        return $score;
    }

    public function win(): bool {
        return $this->matchup->didHomer(true);
    }

    public function getLabel(): string {
        return 'HomeRun-'
            #. 'hitScore=' . floor($this->getHitScore()) * 10
            #. '; hrScore=' . round($this->getHomeRunScore() * 10)
            #. '; pitcherPitchCount=' . (floor($this->getPitcherPitchCount() / 100) * 100)
            #. '; batterPitchCount=' . (floor($this->getBatterPitchCount() / 100) * 100)
            #. '; velocity=' . $this->getVelocityLabel()
            ;
    }

    private function getBatterPitchCount(): float {
        return array_sum(array_column($this->matchup->getBatterStats()->getTagged(), 'pitchCount'));
    }

    private function getPitcherPitchCount(): float {
        return array_sum(array_column($this->matchup->getPitcherStats()->getTagged(), 'pitchCount'));
    }
}