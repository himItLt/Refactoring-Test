<?php

namespace Tests\Unit\Processors;

use App\Exceptions\BinNotFoundException;
use App\Models\Transaction;
use App\Processors\CommissionProcessor;
use App\Services\ApiService;
use App\Utils\FileReader;
use Codeception\AssertThrows;
use Codeception\Stub\Expected;
use Tests\Support\Helper\Fixture;

class CommissionProcessorTest extends \Codeception\Test\Unit
{
    use AssertThrows;

    protected function fakeFetchRows(array $data): callable
    {
        return function () use ($data) {
            foreach ($data as $row) {
                yield $row;
            }
        };
    }

    protected function mockFailedReader(): FileReader
    {
        $failedData = Fixture::load('input-fail-bin');
        return $this->make(FileReader::class, [
            'fetchRows' => $this->fakeFetchRows($failedData),
        ]);
    }

    protected function mockOkInput(): FileReader
    {
        $okData = Fixture::load('input-ok');
        return $this->make(FileReader::class, [
            'fetchRows' => $this->fakeFetchRows($okData),
        ]);
    }

    protected function mockApi(): ApiService
    {
        $rateData = Fixture::load('rates-request');
        $binData = Fixture::load('bin-request');

        return $this->make(ApiService::class, [
            'requestRatesResults' => $rateData,
            'requestBinResults' => fn($bin) => $binData[$bin],
        ]);
    }

    public function testCalculateSuccess()
    {
        $processor = new CommissionProcessor(reader: $this->mockOkInput(), api: $this->mockApi());
        $noCeilResult = $processor->calculate();
        $this->assertIsArray($noCeilResult);
        $this->assertEquals([1, 64.04098623118796], $noCeilResult, '- without ceiling');

        $processor = new CommissionProcessor(reader: $this->mockOkInput(), api: $this->mockApi(), doCeiling: true);
        $ceilResult = $processor->calculate();
        $this->assertIsArray($ceilResult);
        $this->assertEquals([1, 64.05], $ceilResult, '- with ceiling');

        $processor = $this->construct(CommissionProcessor::class, [
                'reader' => $this->mockOkInput(),
                'api' => $this->mockApi(),
                'doCeiling' => false,
            ],
            [
                'createTransaction' => Expected::atLeastOnce(
                        new Transaction(json_decode('{"bin":"45717360","amount":"100.00","currency":"EUR"}', true))
                    ),
            ]
        );
        $processor->calculate();
    }

    public function testCalculateFail()
    {
        $processor = new CommissionProcessor(reader: $this->mockFailedReader(), api: $this->mockApi());
        $this->assertThrows(BinNotFoundException::class, function() use ($processor) {
            $processor->calculate();
        });
    }

    public function testCeilResult()
    {
        $processor = new CommissionProcessor(reader: $this->mockOkInput(), api: $this->mockApi());
        $this->assertEquals(4.48, $processor->ceilResult(4.4721));
    }

    public function testCalculateResult()
    {
        $processor = new CommissionProcessor(reader: $this->mockOkInput(), api: $this->mockApi());
        $noEuResult = $processor->calculateResult(rate: 3.123, currency: 'JPY', amount: 10000, isEu: false);
        $this->assertEquals(64.04098623118796, $noEuResult, '- not Eu');

        $withEuResult = $processor->calculateResult(rate: 3.123, currency: 'JPY', amount: 10000, isEu: true);
        $this->assertEquals(32.02049311559398, $withEuResult, '- is Eu');

        $withEurEuResult = $processor->calculateResult(rate: 3.123, currency: 'EUR', amount: 10000, isEu: true);
        $this->assertEquals(100.0, $withEurEuResult, '- EUR and Eu');

        $withRate0Result = $processor->calculateResult(rate: 0, currency: 'JPY', amount: 10000, isEu: false);
        $this->assertEquals(200.0, $withRate0Result, '- rate 0');

        $withRate0EuResult = $processor->calculateResult(rate: 0, currency: 'JPY', amount: 10000, isEu: true);
        $this->assertEquals(100.0, $withRate0EuResult, '- rate 0 and isEu');
    }
}
