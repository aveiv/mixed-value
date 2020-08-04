<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader;

use Aveiv\ArrayReader\Converter\BoolConverter;
use Aveiv\ArrayReader\Converter\ConverterInterface;
use Aveiv\ArrayReader\Converter\DateTimeConverter;
use Aveiv\ArrayReader\Converter\FloatConverter;
use Aveiv\ArrayReader\Converter\IntConverter;
use Aveiv\ArrayReader\Converter\StringConverter;
use Aveiv\ArrayReader\Exception\MissingValueException;
use Aveiv\ArrayReader\Exception\ReadOnlyException;
use Aveiv\ArrayReader\Exception\UndefinedMethodException;
use Aveiv\ArrayReader\Exception\UnexpectedOffsetTypeException;
use Aveiv\ArrayReader\Exception\UnexpectedValueException;

/**
 * @psalm-template T
 */
final class ArrayReader implements \ArrayAccess
{
    /**
     * @psalm-var T
     *
     * @var mixed
     */
    private $value;

    /**
     * @var string[]
     */
    private array $path = [];

    /**
     * @var ConverterInterface[]
     */
    private array $converters = [];

    /**
     * @psalm-param T $value
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;

        $this->registerConverter('toBool', new BoolConverter());
        $this->registerConverter('toDateTime', new DateTimeConverter());
        $this->registerConverter('toFloat', new FloatConverter());
        $this->registerConverter('toInt', new IntConverter());
        $this->registerConverter('toString', new StringConverter());
    }

    public function registerConverter(string $key, ConverterInterface $converter): void
    {
        $this->converters[mb_strtolower($key)] = $converter;
    }

    /**
     * @psalm-return self<bool>
     *
     * @return self
     */
    public function toBool(): self
    {
        return $this->to('toBool');
    }

    /**
     * @psalm-return self<\DateTime>
     *
     * @return self
     */
    public function toDateTime(): self
    {
        return $this->to('toDateTime');
    }

    /**
     * @psalm-return self<float>
     *
     * @return self
     */
    public function toFloat(): self
    {
        return $this->to('toFloat');
    }

    /**
     * @psalm-return self<int>
     *
     * @return self
     */
    public function toInt(): self
    {
        return $this->to('toInt');
    }

    /**
     * @psalm-return self<string>
     *
     * @return self
     */
    public function toString(): self
    {
        return $this->to('toString');
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return self
     */
    public function __call(string $name, array $arguments): self
    {
        $converterKey = mb_strtolower($name);

        if (!isset($this->converters[$converterKey])) {
            throw new UndefinedMethodException(get_class($this), $name);
        }

        return $this->to($converterKey);
    }

    /**
     * @param string $converterKey
     * @return self
     */
    private function to(string $converterKey): self
    {
        $converterKey = mb_strtolower($converterKey);
        $value = $this->value;
        if ($this->hasValue()) {
            try {
                $value = $this->converters[$converterKey]($this->value);
            } catch (UnexpectedValueException $e) {
                if ($pathAsStr = $this->pathAsStr()) {
                    $msg = sprintf('Cannot convert value "%s": "%s"', $this->pathAsStr(), $e->getMessage());
                } else {
                    $msg = sprintf('Cannot convert value: "%s"', $e->getMessage());
                }
                throw new UnexpectedValueException($msg, intval($e->getCode()), $e);
            }
        }
        return $this->newStatic($value);
    }

    public function offsetExists($offset)
    {
        $offset = $this->prepareOffset($offset);
        return is_array($this->value) && isset($this->value[$offset]);
    }

    public function offsetGet($offset)
    {
        $offset = $this->prepareOffset($offset);
        $value = null;
        if (is_array($this->value) && isset($this->value[$offset])) {
            $value = $this->value[$offset];
        }

        return $this->newStatic($value, $offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new ReadOnlyException();
    }

    public function offsetUnset($offset)
    {
        throw new ReadOnlyException();
    }

    /**
     * @param mixed $offset
     * @return string
     */
    private function prepareOffset($offset): string
    {
        if (!is_int($offset) && !is_string($offset)) {
            throw new UnexpectedOffsetTypeException();
        }
        return strval($offset);
    }

    /**
     * @psalm-return T
     *
     * @return mixed
     */
    public function getValue()
    {
        if (!$this->hasValue()) {
            throw new MissingValueException($this->pathAsStr());
        }
        return $this->value;
    }

    /**
     * @psalm-return T|null
     *
     * @return mixed|null
     */
    public function findValue()
    {
        return $this->value;
    }

    private function hasValue(): bool
    {
        return $this->value !== null;
    }

    private function pathAsStr(): string
    {
        return implode('.', $this->path);
    }

    /**
     * @param mixed $value
     * @param string|null $addPath
     * @return self
     */
    private function newStatic($value, ?string $addPath = null): self
    {
        $reader = new static($value);
        $reader->path = $this->path;
        if ($addPath) {
            $reader->path[] = $addPath;
        }
        $reader->converters = $this->converters;
        return $reader;
    }
}