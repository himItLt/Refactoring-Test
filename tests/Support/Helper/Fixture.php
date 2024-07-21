<?php

namespace Tests\Support\Helper;

class Fixture
{
    public static function load(string $name): array
    {
        return require __DIR__ . '/../fixtures/' . $name . '.php';
    }
}