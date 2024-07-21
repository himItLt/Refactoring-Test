<?php

namespace App\Models;


/**
 * @property int $bin
 * @property float $amount
 * @property string $currency;
 */
class Transaction extends BaseModel
{
    protected array $fields = ['bin', 'amount', 'currency'];
}