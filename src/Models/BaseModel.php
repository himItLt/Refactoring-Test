<?php

namespace App\Models;

use App\Exceptions\CreateModelException;

class BaseModel
{
    protected ?array $attributes;
    protected array $fields = [];

    /**
     * @throws CreateModelException
     */
    public function __construct(?array $attributes = null)
    {
        $this->load($attributes);
    }

    /**
     * @throws CreateModelException
     */
    public function load(?array $attributes): void
    {
        if (empty($attributes)) {
            return;
        }

        $this->attributes = $attributes;

        foreach ($this->fields as $attrName) {
            if (!isset($attributes[$attrName])) {
                throw new CreateModelException("Model create: {$attrName} is required");
            }
        }
    }

    public function __get(string $name)
    {
        if (in_array($name, $this->fields)) {
            return $this->attributes[$name] ?? null;
        }

        return $this->$name;
    }

    public function __set(string $name, $value): void
    {
        if (array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $value;
        }

        $this->$name = $value;
    }
}