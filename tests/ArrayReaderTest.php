<?php

declare(strict_types=1);

namespace Aveiv\ArrayReader\Tests;

use Aveiv\ArrayReader\ArrayReader;
use Aveiv\ArrayReader\Converter\ConverterInterface;
use Aveiv\ArrayReader\Exception\MissingValueException;
use Aveiv\ArrayReader\Exception\ReadOnlyException;
use Aveiv\ArrayReader\Exception\UndefinedMethodException;
use Aveiv\ArrayReader\Exception\UnexpectedOffsetTypeException;
use Aveiv\ArrayReader\Exception\UnexpectedValueException;
use PHPUnit\Framework\TestCase;

class ArrayReaderTest extends TestCase
{
    public function provideCastableBools(): array
    {
        return [
            [false, false],
            [true, true],
            [0, false],
            [1, true],
            [0.0, false],
            [0.1, true],
            ['', false],
            ['0', false],
            ['1', true],
            [[], false],
            [[1], true],
            [new \stdClass(), true],
        ];
    }

    /**
     * @dataProvider provideCastableBools
     *
     * @param $value
     * @param bool $result
     */
    public function testToBool_CastableBool_ReturnsSame($value, bool $result): void
    {
        $reader = new ArrayReader($value);
        $this->assertSame($result, $reader->toBool()->getValue());
        $this->assertSame($result, $reader->toBool()->findValue());
    }

    public function testToDateTime_DateTimeString_ReturnsEqualDateTime(): void
    {
        $reader = new ArrayReader($dtStr = '2020-01-01');
        $dt = date_create($dtStr);

        $this->assertInstanceOf(\DateTime::class, $actValue = $reader->toDateTime()->getValue());
        $this->assertEquals($dt, $actValue);

        $this->assertInstanceOf(\DateTime::class, $actValue = $reader->toDateTime()->findValue());
        $this->assertEquals($dt, $actValue);
    }

    public function testToDateTime_NotString_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot convert value: "Value must be a string"');

        $reader = new ArrayReader(9999);
        $reader->toDateTime();
    }

    public function testToDateTime_NotDateTimeString_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot convert value: "Failed to parse datetime string"');

        $reader = new ArrayReader('not_datetime_string');
        $reader->toDateTime();
    }

    public function provideCastableFloats(): array
    {
        return [
            [false, 0.0],
            [true, 1.0],
            [0, 0.0],
            [1, 1.0],
            [0.0, 0.0],
            [0.1, 0.1],
            ['', 0.0],
            ['0', 0.0],
            ['1', 1.0],
            [[], 0.0],
            [[1], 1.0],
        ];
    }

    /**
     * @dataProvider provideCastableFloats
     *
     * @param mixed $value
     * @param float $result
     */
    public function testToFloat_CastableFloat_ReturnsSame($value, float $result): void
    {
        $reader = new ArrayReader($value);
        $this->assertSame($result, $reader->toFloat()->getValue());
        $this->assertSame($result, $reader->toFloat()->findValue());
    }

    public function testToFloat_Object_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot convert value: "Value cannot be an object"');

        $reader = new ArrayReader(new \stdClass());
        $reader->toFloat();
    }

    public function provideCastableInt(): array
    {
        return [
            [false, 0],
            [true, 1],
            [0, 0],
            [1, 1],
            [0.0, 0],
            [0.1, 0],
            ['', 0],
            ['0', 0],
            ['1', 1],
            [[], 0],
            [[1], 1],
        ];
    }

    /**
     * @dataProvider provideCastableInt
     *
     * @param mixed $value
     * @param int $result
     */
    public function testToInt_CastableInt_ReturnsSame($value, int $result): void
    {
        $reader = new ArrayReader($value);

        $this->assertSame($result, $reader->toInt()->getValue());
        $this->assertSame($result, $reader->toInt()->findValue());
    }

    public function testToInt_Object_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot convert value: "Value cannot be an object"');

        $reader = new ArrayReader(new \stdClass());
        $reader->toInt();
    }

    public function provideCastableString(): array
    {
        $obj = new class {
            public function __toString()
            {
                return 'obj_string';
            }
        };
        return [
            [false, ''],
            [true, '1'],
            [0, '0'],
            [1, '1'],
            [0.0, '0'],
            [0.1, '0.1'],
            ['', ''],
            ['0', '0'],
            ['1', '1'],
            [$obj, 'obj_string'],
        ];
    }

    /**
     * @dataProvider provideCastableString
     *
     * @param mixed $value
     * @param string $result
     */
    public function testToString_CastableString_ReturnsSame($value, string $result): void
    {
        $reader = new ArrayReader($value);

        $this->assertSame($result, $reader->toString()->getValue());
        $this->assertSame($result, $reader->toString()->findValue());
    }

    public function testToString_Array_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot convert value: "Value cannot be an array"');

        $reader = new ArrayReader([]);
        $reader->toString();
    }

    public function testToString_ObjectWithoutToString_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot convert value: "Value must implement the __toString method"');

        $reader = new ArrayReader(new \stdClass());
        $reader->toString();
    }

    public function testToAny_UncastableValueWithOffset_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot convert value "path.to": "Uncastable value"');

        $reader = new ArrayReader([
            'path' => [
                'to' => 'value',
            ]
        ]);
        $reader->registerConverter('toAny', new class implements ConverterInterface {
            public function __invoke($value)
            {
                throw new UnexpectedValueException('Uncastable value');
            }
        });
        $reader['path']['to']->toAny();
    }

    public function provideInvalidOffsets(): array
    {
        return [
            [null],
            [false],
            [true],
            [0.0],
            [[]],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider provideInvalidOffsets
     *
     * @param mixed $invalidOffset
     */
    public function testOffsetExists_InvalidOffset_ThrowsUnexpectedOffsetTypeException($invalidOffset): void
    {
        $this->expectException(UnexpectedOffsetTypeException::class);

        $reader = new ArrayReader([]);
        /** @noinspection PhpExpressionResultUnusedInspection */
        isset($reader[$invalidOffset]);
    }

    public function testOffsetExists_ExistingOffset_ReturnsTrue(): void
    {
        $reader = new ArrayReader([
            ($k = 'key') => 'value',
        ]);
        $this->assertTrue(isset($reader[$k]));
    }

    public function testOffsetExists_NonExistingOffset_ReturnsTrue(): void
    {
        $reader = new ArrayReader([]);
        $this->assertFalse(isset($reader['key']));
    }

    /**
     * @dataProvider provideInvalidOffsets
     *
     * @param mixed $invalidOffset
     */
    public function testOffsetGet_InvalidOffset_ThrowsUnexpectedOffsetTypeException($invalidOffset): void
    {
        $this->expectException(UnexpectedOffsetTypeException::class);
        $this->expectExceptionMessage('Offset must be a string or an integer');

        $reader = new ArrayReader([]);
        $reader[$invalidOffset];
    }

    public function testOffsetGet_AnyOffset_ReturnsArrayReader(): void
    {
        $reader = new ArrayReader([
            ($existingKey = 'key') => 'value',
        ]);
        $this->assertInstanceOf(ArrayReader::class, $reader[$existingKey]);
        $this->assertInstanceOf(ArrayReader::class, $reader['non_existing_key']);
    }

    public function testOffsetSet_ThrowsReadOnlyException(): void
    {
        $this->expectException(ReadOnlyException::class);

        $reader = new ArrayReader([]);
        $reader['key'] = 'value';
    }

    public function testOffsetUnset_ThrowsReadOnlyException(): void
    {
        $this->expectException(ReadOnlyException::class);
        $this->expectExceptionMessage('Aveiv\ArrayReader\ArrayReader is readonly');

        $reader = new ArrayReader([
            ($k = 'key') => 'value',
        ]);
        unset($reader[$k]);
    }

    public function testGetValue_ExistingOffset_ReturnsSameValue(): void
    {
        $reader = new ArrayReader([
            'path' => [
                'to' => ($value = 'value'),
            ],
        ]);
        $this->assertSame($value, $reader['path']['to']->getValue());
    }

    public function testGetValue_NonExistingOffset_ThrowsMissingValueException(): void
    {
        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage('Value "path.to" does not exists');

        $reader = new ArrayReader([]);
        $reader['path']['to']->getValue();
    }

    public function testGetValue_OffsetOnNotArray_ThrowsMissingValueException(): void
    {
        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage('Value "path.to" does not exists');

        $reader = new ArrayReader(new \stdClass());
        $reader['path']['to']->getValue();
    }

    public function testFindValue_ExistingOffset_ReturnsSameValue(): void
    {
        $reader = new ArrayReader([
            'path' => [
                'to' => ($value = 'value'),
            ],
        ]);
        $this->assertSame($value, $reader['path']['to']->findValue());
    }

    public function testFindValue_NonExistingOffset_ReturnsNull(): void
    {
        $reader = new ArrayReader([]);
        $this->assertNull($reader['path']['to']->findValue());
    }

    public function testFindValue_OffsetOnNotArray_ReturnsNull(): void
    {
        $reader = new ArrayReader([]);
        $this->assertNull($reader['path']['to']->findValue());
    }

    public function testCall_NonExistingConverter_ThrowsUndefinedMethodException(): void
    {
        $this->expectException(UndefinedMethodException::class);
        $this->expectExceptionMessage('Call to undefined method Aveiv\ArrayReader\ArrayReader::toCustomType()');

        $reader = new ArrayReader([]);
        /** @noinspection PhpUndefinedMethodInspection */
        $reader->toCustomType();
    }

    public function testCall_ExistingConverter_ReturnsConvertedValue(): void
    {
        $reader = new ArrayReader(10);
        $reader->registerConverter('toMultipliedValue', new class implements ConverterInterface {
            public function __invoke($value)
            {
                return $value * $value;
            }
        });
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertSame(100, $reader->toMultipliedValue()->getValue());
    }
}
