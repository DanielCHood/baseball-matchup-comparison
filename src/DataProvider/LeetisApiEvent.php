<?php

namespace DanielCHood\BaseballMatchupComparison\DataProvider;

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

}