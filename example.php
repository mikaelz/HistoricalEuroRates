<?php

use mikaelz\HistoricalEuroRates;

require 'HistoricalEuroRates.php';

$histEuroRate = new mikaelz\HistoricalEuroRates('CZK');
echo $histEuroRate->getRate('2017-05-22');
