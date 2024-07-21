<?php

namespace Tests\Unit\Services;

use App\Services\ApiService;
use Codeception\AssertThrows;
use Tests\Support\Helper\Fixture;

class ApiServiceTest extends \Codeception\Test\Unit
{
    use AssertThrows;

    public function testGetRate()
    {
        $rateData = Fixture::load('rates-request');
        $model = $this->make(ApiService::class, [
            'requestRatesResults' => $rateData,
        ]);
        $this->assertEquals(1.2, $model->getRate('EUR'));
        $this->assertEquals(0, $model->getRate('GRN'));
    }

    public function testGetBinCode()
    {
        $binData = Fixture::load('bin-request');
        $model = $this->make(ApiService::class, [
            'requestBinResults' => fn($bin) => $binData[$bin],
        ]);

        $this->assertEquals('DK', $model->getBinCode(45717360), '- check ok');
        $this->assertFalse($model->getBinCode(41417360), '- check failed api');
        $this->assertFalse($model->getBinCode(414173601), '- check no country code');
    }
}
