<?php

namespace App\Tools;

use App\Exceptions\FileNotFoundException;

/**
 *  @codeCoverageIgnore
 */
class FileReader
{
    protected $handler = null;

    /**
     * @throws FileNotFoundException
     */
    public function __construct(string $fileName)
    {
        if (!file_exists($fileName)) {
            throw new FileNotFoundException('Input file is not found');
        }
        $this->handler = fopen($fileName, 'r');
    }

    public function fetchRows(): \Generator
    {
        while (!feof($this->handler)) {
            yield trim(fgets($this->handler));
        }
        rewind($this->handler);
    }

    public function __destruct()
    {
        fclose($this->handler);
    }
}