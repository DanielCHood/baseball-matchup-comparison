# baseball-matchup-comparison

## Usage

### Initializing a data provider

```php
use DanielCHood\BaseballMatchupComparison\DataProvider\LeetisApiEvent;
use GuzzleHttp\Client;

$dataProvider = new LeetisApiEvent(
    new Client([
        'base_uri' => 'https://mlb.leetis.com/batting-data/'
    ])
);
```

### Retrieving a list of events for a date

```php
use DanielCHood\BaseballMatchupComparison\Repository\Event;
use DateTime;

$repo = new Event($dataProvider);
$eventIds = $repo->getEventIdsOnDate(new DateTime("now"));
```

### Retrieving all matchups for an event

```php
use DanielCHood\BaseballMatchupComparison\Repository\Event;

$eventId = 123;
$matchups = $repo->getAllMatchups($eventId);
```