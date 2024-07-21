<?php

namespace App\Services;

use App\Exceptions\BinNotFoundException;

class ApiService
{
    public function __construct(
        protected array $cachedRates = [],
    )
    {
    }

    /** @codeCoverageIgnore  */
    protected function requestBinResults(int $bin): false|array
    {
        return @json_decode(file_get_contents('https://lookup.binlist.net/' . $bin), true);
    }

    /** @codeCoverageIgnore  */
    protected function requestRatesResults(): array
    {
        if (!empty($this->cachedRates)) {
            return $this->cachedRates;
        }

        $result = @json_decode(file_get_contents('https://api.exchangeratesapi.io/latest'), true);
        if (!empty($result['rates'])) {
            $this->cachedRates = $result['rates'];
        }

        return $this->cachedRates;
    }

    public function getBinCode(int $bin): string|false
    {
        $result =$this->requestBinResults($bin);

        return $result && !empty($result['country'])
            ? ($result['country']['alpha2'] ?? false)
            : false;
    }

    public function getRate(string $currency): float
    {
        $ratesResult = $this->requestRatesResults();

        return $ratesResult[$currency] ?? 0;
    }
}