<?php

namespace DanielCHood\BaseballMatchupComparison\DataProvider;

use DateTime;
use GuzzleHttp\Client;

class LeetisApiEvent implements EventInterface {

    public function __construct(
        private Client $client,
    ) {

    }

    public function load(int $id): array {
        $request = $this->client->request('GET', '?eventId=' . $id);
        $response = $request->getBody();

        return json_decode($response, true);
    }

    public function getIdsForDate(DateTime $date): array {
        $request = $this->client->request('GET', 'date.php?date=' . $date->format('Y-m-d'));
        $response = $request->getBody();

        return json_decode($response, true);
    }
}