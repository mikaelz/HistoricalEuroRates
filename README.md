# Historical Euro Rates

Rates are downloaded from https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/czk.xml

## Usage example
```
<?php

use mikaelz\HistoricalEuroRates;

require 'HistoricalEuroRates.php';

$histEuroRate = new mikaelz\HistoricalEuroRates('CZK');
echo $histEuroRate->getRate('2017-05-22');

// or all rates
print_r($histEuroRate->getRates());
```
