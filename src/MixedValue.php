<?php

declare(strict_types=1);

namespace Aveiv\MixedValue;

use Aveiv\MixedValue\Exception\MissingValueException;
use Aveiv\MixedValue\Exception\ReadOnlyException;
use Aveiv\MixedValue\Exception\UndefinedMethodException;
use Aveiv\MixedValue\Exception\UnexpectedOffsetTypeException;
use Aveiv\MixedValue\Exception\UnexpectedValueException;
use Aveiv\MixedValue\ValueProcessor\IsArrayProcessor;
use Aveiv\MixedValue\ValueProcessor\IsBoolProcessor;
use Aveiv\MixedValue\ValueProcessor\IsFloatProcessor;
use Aveiv\MixedValue\ValueProcessor\IsIntProcessor;
use Aveiv\MixedValue\ValueProcessor\IsNumericProcessor;
use Aveiv\MixedValue\ValueProcessor\IsStringProcessor;
use Aveiv\MixedValue\ValueProcessor\ToBoolProcessor;
use Aveiv\MixedValue\ValueProcessor\ToDateTimeProcessor;
use Aveiv\MixedValue\ValueProcessor\ToFloatProcessor;
use Aveiv\MixedValue\ValueProcessor\ToIntProcessor;
use Aveiv\MixedValue\ValueProcessor\ToStringProcessor;
use Aveiv\MixedValue\ValueProcessor\ValueProcessorInterface;

/**
 * @psalm-template TValue
 */
final class MixedValue implements \ArrayAccess
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
        $this->registerValueProcessor('isNumeric', new IsNumericProcessor());
        $this->registerValueProcessor('isString', new IsStringProcessor());

        $this->registerValueProcessor('toBool', new ToBoolProcessor());
        $this->registerValueProcessor('toDateTime', new ToDateTimeProcessor());
        $this->registerValueProcessor('toDateTimeImmutable', new ToDateTimeProcessor(true));
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
     * @psalm-return self<numeric>
     *
     * @return self
     */
    public function isNumeric(): self
    {
        return $this->to('isNumeric');
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
     * @psalm-return self<\DateTimeImmutable>
     *
     * @return self
     */
    public function toDateTimeImmutable(): self
    {
        return $this->to('toDateTimeImmutable');
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
                $vMixed = $this->newStatic($v, strval($k));
                return $cb($vMixed);
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
                    $msg = sprintf('Cannot process value "%s": "%s"', $pathAsStr, $e->getMessage());
                } else {
                    $msg = sprintf('Cannot process value: "%s"', $e->getMessage());
                }
                throw new UnexpectedValueException($msg, intval($e->getCode()), $e);
            }
        }
        return $this->newStatic($value);
    }

    public function offsetExists($offset): bool
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

    public function offsetSet($offset, $value): void
    {
        throw new ReadOnlyException();
    }

    public function offsetUnset($offset): void
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

    public function path(string $path): self
    {
        $path = explode('.', $path);

        $currentValue = $this;
        foreach ($path as $offset) {
            $currentValue = $currentValue[$offset];
        }

        return $currentValue;
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
        $mixed = new static($value);
        $mixed->path = $this->path;
        if ($addPath) {
            $mixed->path[] = $addPath;
        }
        $mixed->valueProcessors = $this->valueProcessors;
        return $mixed;
    }
}
