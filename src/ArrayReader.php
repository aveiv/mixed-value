<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader;

use Aveiv\ArrayReader\ValueProcessor\ToBoolProcessor;
use Aveiv\ArrayReader\ValueProcessor\ValueProcessorInterface;
use Aveiv\ArrayReader\ValueProcessor\ToDateTimeProcessor;
use Aveiv\ArrayReader\ValueProcessor\ToFloatProcessor;
use Aveiv\ArrayReader\ValueProcessor\ToIntProcessor;
use Aveiv\ArrayReader\ValueProcessor\IsArrayProcessor;
use Aveiv\ArrayReader\ValueProcessor\IsBoolProcessor;
use Aveiv\ArrayReader\ValueProcessor\IsFloatProcessor;
use Aveiv\ArrayReader\ValueProcessor\IsIntProcessor;
use Aveiv\ArrayReader\ValueProcessor\IsStringProcessor;
use Aveiv\ArrayReader\ValueProcessor\ToStringProcessor;
use Aveiv\ArrayReader\Exception\MissingValueException;
use Aveiv\ArrayReader\Exception\ReadOnlyException;
use Aveiv\ArrayReader\Exception\UndefinedMethodException;
use Aveiv\ArrayReader\Exception\UnexpectedOffsetTypeException;
use Aveiv\ArrayReader\Exception\UnexpectedValueException;

/**
 * @psalm-template TValue
 */
final class ArrayReader implements \ArrayAccess
{
    /**
     * @psalm-var TValue
     *
     * @var mixed
     */
    private $value;

    /**
     * @var string[]
     */
    private array $path = [];

    /**
     * @var ValueProcessorInterface[]
     */
    private array $valueProcessors = [];

    /**
     * @psalm-param TValue $value
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;

        $this->registerValueProcessor('isArray', new IsArrayProcessor());
        $this->registerValueProcessor('isBool', new IsBoolProcessor());
        $this->registerValueProcessor('isFloat', new IsFloatProcessor());
        $this->registerValueProcessor('isInt', new IsIntProcessor());
        $this->registerValueProcessor('isString', new IsStringProcessor());

        $this->registerValueProcessor('toBool', new ToBoolProcessor());
        $this->registerValueProcessor('toDateTime', new ToDateTimeProcessor());
        $this->registerValueProcessor('toFloat', new ToFloatProcessor());
        $this->registerValueProcessor('toInt', new ToIntProcessor());
        $this->registerValueProcessor('toString', new ToStringProcessor());
    }

    public function registerValueProcessor(string $key, ValueProcessorInterface $processor): void
    {
        $this->valueProcessors[mb_strtolower($key)] = $processor;
    }

    /**
     * @psalm-return self<array>
     *
     * @return self
     */
    public function isArray(): self
    {
        return $this->to('isArray');
    }

    /**
     * @psalm-return self<bool>
     *
     * @return self
     */
    public function isBool(): self
    {
        return $this->to('isBool');
    }

    /**
     * @psalm-return self<float>
     *
     * @return self
     */
    public function isFloat(): self
    {
        return $this->to('isFloat');
    }

    /**
     * @psalm-return self<int>
     *
     * @return self
     */
    public function isInt(): self
    {
        return $this->to('isInt');
    }

    /**
     * @psalm-return self<string>
     *
     * @return self
     */
    public function isString(): self
    {
        return $this->to('isString');
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
     * @param callable $cb
     * @return self
     *
     * @psalm-template T
     * @psalm-param callable(self):T $cb
     * @psalm-return self<array<T>>
     */
    public function map(callable $cb): self
    {
        if ($this->hasValue()) {
            $arr = $this->isArray()->getValue();
            $arr = array_map(function ($k, $v) use ($cb) {
                $vReader = $this->newStatic($v, strval($k));
                return $cb($vReader);
            }, array_keys($arr), $arr);
            return $this->newStatic($arr);
        } else {
            return $this;
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return self
     */
    public function __call(string $name, array $arguments): self
    {
        $processorKey = mb_strtolower($name);

        if (!isset($this->valueProcessors[$processorKey])) {
            throw new UndefinedMethodException(get_class($this), $name);
        }

        return $this->to($processorKey);
    }

    /**
     * @param string $processorKey
     * @return self
     */
    private function to(string $processorKey): self
    {
        $processorKey = mb_strtolower($processorKey);
        $value = $this->value;
        if ($this->hasValue()) {
            try {
                $value = $this->valueProcessors[$processorKey]($this->value);
            } catch (UnexpectedValueException $e) {
                if ($pathAsStr = $this->pathAsStr()) {
                    $msg = sprintf('Cannot process value "%s": "%s"', $this->pathAsStr(), $e->getMessage());
                } else {
                    $msg = sprintf('Cannot process value: "%s"', $e->getMessage());
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

    /**
     * @param mixed $offset
     * @return self
     */
    public function offsetGet($offset): self
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
     * @psalm-return TValue
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
     * @psalm-return TValue|null
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
        $reader->valueProcessors = $this->valueProcessors;
        return $reader;
    }
}
