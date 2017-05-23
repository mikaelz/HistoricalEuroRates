<?php

/**
 * Historical Euro Rates
 *
 * @author  Michal Zuber <info@nevilleweb.sk>
 * @license MIT license
 * @link    https://github.com/mikaelz/HistoricalEuroRates
 */
namespace mikaelz;

/**
 * Historical Euro Rates
 *
 * @category ExchangeRate
 * @package  Currency
 */
class HistoricalEuroRates
{
    const RATES_XML = 'https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html';

    /** @var string $xmlPath */
    private $xmlPath;

    /** @var array $rates */
    private $rates = [];

    /** @var string $currencyCode */
    private $currencyCode;

    public function __construct($currencyCode)
    {
        if (empty($currencyCode)) {
            return;
        }

        $this->currencyCode = strtoupper($currencyCode);

        $this->xmlPath = sprintf(
            '%s/ecb_rate_%s.xml',
            sys_get_temp_dir(),
            strtolower($currencyCode)
        );

        if (!is_file($this->xmlPath) || date('d') != date('d', filemtime($this->xmlPath))) {
            $this->downloadRatesXml();
        }

        $this->setRates();
    }

    /**
     * Get rate for date (Y-m-d).
     *
     * @param string $date Y-m-d formatted date
     *
     * @return float
     */
    public function getRate($date)
    {
        $rate = 0;
        if (isset($this->rates[$date])) {
            $rate = (float) $this->rates[$date];
        }

        return $rate;
    }

    /**
     * Get all rates.
     *
     * @return array
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * Download XML rates
     *
     * @return void
     */
    private function downloadRatesXml()
    {
        $xml = sprintf(
            '%s/%s.xml',
            self::RATES_XML,
            strtolower($this->currencyCode)
        );

        if (($handle = fopen($xml, 'rb')) !== false) {
            $xml = stream_get_contents($handle);
            fclose($handle);

            $output = fopen($this->xmlPath, 'wb');
            fwrite($output, $xml);
            fclose($output);
        }
    }

    /**
     * Parse rates into array
     *
     * @return array
     */
    private function setRates()
    {
        if (!is_file($this->xmlPath)) {
            throw new \Exception("XML {$this->xmlPath} does not exists.");
        }

        if (1 > filesize($this->xmlPath)) {
            throw new \Exception("XML {$this->xmlPath} is empty.");
        }

        $xml = simplexml_load_file($this->xmlPath);
        $rates = [];
        foreach ($xml->DataSet->Series->Obs as $Obs) {
            $rates[(string)$Obs['TIME_PERIOD']] = (float) $Obs['OBS_VALUE'];
        }

        // Set for weekends
        $period = new \DatePeriod(
            new \DateTime(current(array_keys($rates))),
            new \DateInterval('P1D'),
            new \DateTime(date('Y-m-d'))
        );
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $rate = isset($rates[$dateStr]) ? $rates[$dateStr] : $this->rates[$date->modify('-1 day')->format('Y-m-d')];
            $this->rates[$dateStr] = $rate;
        }
    }
}
