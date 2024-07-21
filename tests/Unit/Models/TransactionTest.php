<?php

namespace Tests\Unit\Models;

use App\Exceptions\CreateModelException;
use App\Models\Transaction;
use Codeception\AssertThrows;

class TransactionTest extends \Codeception\Test\Unit
{
    use AssertThrows;

    public function testCreateSuccess()
    {
        $string = '{"bin":"45717360","amount":"100.00","currency":"EUR"}';
        $transaction = new Transaction(json_decode($string, true));
        $this->assertEquals('45717360', $transaction->bin);
    }

    public function testCreateFail()
    {
        $this->assertThrows(CreateModelException::class, function() {
            $string = '{"bin":"45717360","wrong":"100.00","currency":"EUR"}';
            (new Transaction())->load(json_decode($string, true));
        });
    }
}
