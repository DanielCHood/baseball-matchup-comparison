<?php

namespace DanielCHood\BaseballMatchupComparison\DataProvider;

use DateTime;

interface EventInterface {

    public function load(int $id);

    public function getIdsForDate(DateTime $date);

}