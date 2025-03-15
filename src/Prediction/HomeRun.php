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
            && $this->getBatterPitchCountRounded() >= 400
            #&& $this->getPitcherPitchCountRounded() >= 400
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
            $frequency = $section['pitchCount'] / $this->getPitcherPitchCountRounded();

            $batterAverageSeenVelocity = $batterSection['velocity'] ?? 0;
            if ($batterAverageSeenVelocity === 0) {
                continue;
            }

            $score += ($batterAverageSeenVelocity - $velocity) * $frequency;
        }

        return $score;
    }

    public function win(): bool {
        return $this->matchup->didHomer(false);
    }

    public function getLabel(): string {
        return ''
            #. 'hitScore=' . floor($this->matchup->getHitScore())
            . '; hrScore=' . round($this->matchup->getHomeRunScore() * 10)
            #. '; pitcherPitchCount=' . (floor($this->getPitcherPitchCountRounded() / 100) * 100)
            #. '; batterPitchCount=' . (floor($this->getBatterPitchCountRounded() / 100) * 100)
            #. '; velocity=' . $this->getVelocityLabel()
            ;
    }

    private function getBatterPitchCountRounded(): float {
        return array_sum(array_column($this->matchup->getBatterStats()->getTagged(), 'pitchCount'));
    }

    private function getPitcherPitchCountRounded(): float {
        return array_sum(array_column($this->matchup->getPitcherStats()->getTagged(), 'pitchCount'));
    }

    private function getVelocityLabel(): string {
        $group = floor($this->getVelocityScore()) * 10;

        if ($group >= 50) {
            return '>=50';
        }

        if ($group >= 40) {
            return '>=40';
        }

        if ($group >= 30) {
            return '>=30';
        }

        if ($group >= 20) {
            return '>=20';
        }

        if ($group >= 10) {
            return '>=10';
        }

        return '>0';
    }
}