<?php

use App\Exceptions\BinNotFoundException;
use App\Exceptions\FileNotFoundException;
use App\Exceptions\CreateModelException;
use App\Processors\CommissionProcessor;
use App\Services\ApiService;
use App\Utils\FileReader;

require_once __DIR__ . '/vendor/autoload.php';

// TODO: change to 'production' when on the production, get rates api is required authorization locally and always fails
const ENV = 'local';
const DO_CEIL = true;

$output = fn(array $lines) => implode(PHP_EOL, $lines) . PHP_EOL;

$cachedRates = ENV === 'local' ? [
    'EUR' => 1.2,
    'USD' => 2.5,
    'JPY' => 3.123,
    'GBP' => 5.21,
] : null;

$fileName = !empty($argv[1]) ? __DIR__ . '/' . $argv[1] : '';
$processor = null;
try {
    $processor = new CommissionProcessor(
        reader: new FileReader($fileName),
        api: new ApiService($cachedRates),
        doCeiling: DO_CEIL
    );

    $commissions = $processor->calculate();
    echo $output($commissions);
} catch (FileNotFoundException | CreateModelException | BinNotFoundException $e) {
    echo $output($processor ? $processor->getResults() : []);
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
