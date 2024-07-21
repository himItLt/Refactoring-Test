<?php

namespace App\Processors;

use App\Exceptions\BinNotFoundException;
use App\Exceptions\CreateModelException;
use App\Models\Transaction;
use App\Services\ApiService;
use App\Utils\FileReader;

class CommissionProcessor
{
    const EU_CODES = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PO',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK',
    ];

    protected array $results = [];

    public function __construct(
        protected FileReader $reader,
        protected ApiService $api,
        protected bool       $doCeiling = false,
    )
    {
    }

    /**
     * @throws CreateModelException
     * @throws BinNotFoundException
     */
    public function calculate(): array
    {
        foreach ($this->reader->fetchRows() as $inputRow) {
            if (empty($inputRow)) {
                continue;
            }

            $transaction = $this->createTransaction($inputRow);
            $binCode = $this->api->getBinCode($transaction->bin);

            if (!$binCode) {
                throw new BinNotFoundException("BIN country code is empty for {$transaction->bin}");
            }

            $isEu = $this->isEu($binCode);
            $rate = $this->api->getRate($transaction->currency);

            $commission = $this->calculateResult(
                rate: $rate,
                currency: $transaction->currency,
                amount: $transaction->amount,
                isEu: $isEu
            );

            $this->results[] = ($this->doCeiling ? $this->ceilResult($commission) : $commission);
        }

        return $this->results;
    }

    public function calculateResult(float $rate, string $currency, float $amount, bool $isEu): float
    {
        $amntFixed = ($currency === 'EUR' || $rate == 0) ? $amount : $amount / $rate;
        return $amntFixed * ($isEu ? 0.01 : 0.02);
    }

    public function ceilResult(float $value): float
    {
        return ceil($value * 100) / 100;
    }

    /** @codeCoverageIgnore  */
    public function getResults(): array
    {
        return $this->results;
    }

    /** @codeCoverageIgnore  */
    protected function isEu(string $countryCode): bool
    {
        return in_array($countryCode, self::EU_CODES);
    }

    /**
     * @throws CreateModelException
     */
    protected function createTransaction(string $jsonRow): Transaction
    {
        return new Transaction(json_decode($jsonRow, true));
    }
}