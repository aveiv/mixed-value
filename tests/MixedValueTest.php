<?php

declare(strict_types=1);

namespace Aveiv\MixedValue\Tests;

use Aveiv\MixedValue\MixedValue;
use Aveiv\MixedValue\ValueProcessor\ValueProcessorInterface;
use Aveiv\MixedValue\Exception\MissingValueException;
use Aveiv\MixedValue\Exception\ReadOnlyException;
use Aveiv\MixedValue\Exception\UndefinedMethodException;
use Aveiv\MixedValue\Exception\UnexpectedOffsetTypeException;
use Aveiv\MixedValue\Exception\UnexpectedValueException;
use PHPUnit\Framework\TestCase;

class MixedValueTest extends TestCase
{
    public function testIsArray_Array_ReturnsSame(): void
    {
        $mixed = new MixedValue($value = []);
        $this->assertSame($value, $mixed->isArray()->getValue());
    }

    public function testIsArray_NotArray_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value must be an array"');

        $mixed = new MixedValue($value = 'not_array');
        $mixed->isArray();
    }

    public function testIsBool_Bool_ReturnsSame(): void
    {
        $mixed = new MixedValue($value = true);
        $this->assertSame($value, $mixed->isBool()->getValue());
    }

    public function testIsBool_NotBool_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value must be a boolean"');

        $mixed = new MixedValue($value = 'not_bool');
        $mixed->isBool();
    }

    public function testIsFloat_Float_ReturnsSame(): void
    {
        $mixed = new MixedValue($value = 1.0);
        $this->assertSame($value, $mixed->isFloat()->getValue());
    }

    public function testIsFloat_NotFloat_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value must be a float"');

        $mixed = new MixedValue($value = 'not_float');
        $mixed->isFloat();
    }

    public function testIsInt_Int_ReturnsSame(): void
    {
        $mixed = new MixedValue($value = 1);
        $this->assertSame($value, $mixed->isInt()->getValue());
    }

    public function testIsInt_NotInt_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value must be an int"');

        $mixed = new MixedValue($value = 'not_int');
        $mixed->isInt();
    }

    public function testIsString_String_ReturnsSame(): void
    {
        $mixed = new MixedValue($value = 'string');
        $this->assertSame($value, $mixed->isString()->getValue());
    }

    public function testIsString_NotString_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value must be a string"');

        $mixed = new MixedValue($value = 1);
        $mixed->isString();
    }

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
        $mixed = new MixedValue($value);
        $this->assertSame($result, $mixed->toBool()->getValue());
        $this->assertSame($result, $mixed->toBool()->findValue());
    }

    public function testToDateTime_DateTimeString_ReturnsEqualDateTime(): void
    {
        $mixed = new MixedValue($dtStr = '2020-01-01');
        $dt = date_create($dtStr);

        $this->assertInstanceOf(\DateTime::class, $actValue = $mixed->toDateTime()->getValue());
        $this->assertEquals($dt, $actValue);

        $this->assertInstanceOf(\DateTime::class, $actValue = $mixed->toDateTime()->findValue());
        $this->assertEquals($dt, $actValue);
    }

    public function testToDateTime_NotString_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value must be a string"');

        $mixed = new MixedValue(9999);
        $mixed->toDateTime();
    }

    public function testToDateTime_NotDateTimeString_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Failed to parse datetime string"');

        $mixed = new MixedValue('not_datetime_string');
        $mixed->toDateTime();
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
        $mixed = new MixedValue($value);
        $this->assertSame($result, $mixed->toFloat()->getValue());
        $this->assertSame($result, $mixed->toFloat()->findValue());
    }

    public function testToFloat_Object_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value cannot be an object"');

        $mixed = new MixedValue(new \stdClass());
        $mixed->toFloat();
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
        $mixed = new MixedValue($value);

        $this->assertSame($result, $mixed->toInt()->getValue());
        $this->assertSame($result, $mixed->toInt()->findValue());
    }

    public function testToInt_Object_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value cannot be an object"');

        $mixed = new MixedValue(new \stdClass());
        $mixed->toInt();
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
        $mixed = new MixedValue($value);

        $this->assertSame($result, $mixed->toString()->getValue());
        $this->assertSame($result, $mixed->toString()->findValue());
    }

    public function testToString_Array_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value cannot be an array"');

        $mixed = new MixedValue([]);
        $mixed->toString();
    }

    public function testToString_ObjectWithoutToString_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value must implement the __toString method"');

        $mixed = new MixedValue(new \stdClass());
        $mixed->toString();
    }

    public function testToAny_UncastableValueWithOffset_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value "path.to": "Uncastable value"');

        $mixed = new MixedValue([
            'path' => [
                'to' => 'value',
            ]
        ]);
        $mixed->registerValueProcessor('toAny', new class implements ValueProcessorInterface {
            public function __invoke($value)
            {
                throw new UnexpectedValueException('Uncastable value');
            }
        });
        /** @noinspection PhpUndefinedMethodInspection */
        $mixed['path']['to']->toAny();
    }

    public function testMap_MixedArrayItemToInt_ReturnsIntArray(): void
    {
        $mixed = new MixedValue([1, '2', 3]);
        $intArr = $mixed
            ->map(fn(MixedValue $r) => $r->toInt()->getValue())
            ->getValue();
        $this->assertSame([1, 2, 3], $intArr);
    }

    public function testMap_FindValueOnNull_ReturnsNull(): void
    {
        $mixed = new MixedValue(null);
        $this->assertNull(
            $mixed
                ->map(fn(MixedValue $r) => $r->getValue())
                ->findValue()
        );
    }

    public function testMap_NotArray_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value: "Value must be an array"');

        $mixed = new MixedValue('not_array');
        $mixed->map(fn(MixedValue $r) => $r->getValue());
    }

    public function testMap_GetItemValueOfArrayWithNull_ThrowsMissingValueException(): void
    {
        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage('Value "1" does not exists');

        $mixed = new MixedValue([1, null, 3]);
        $mixed
            ->map(fn(MixedValue $r) => $r->getValue());
    }

    public function testMap_MixedArrayItemIsInt_ThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot process value "1": "Value must be an int"');

        $mixed = new MixedValue([1, '2', 3]);
        $mixed
            ->map(fn(MixedValue $r) => $r->isInt()->getValue());
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

        $mixed = new MixedValue([]);
        /** @noinspection PhpExpressionResultUnusedInspection */
        isset($mixed[$invalidOffset]);
    }

    public function testOffsetExists_ExistingOffset_ReturnsTrue(): void
    {
        $mixed = new MixedValue([
            ($k = 'key') => 'value',
        ]);
        $this->assertTrue(isset($mixed[$k]));
    }

    public function testOffsetExists_NonExistingOffset_ReturnsTrue(): void
    {
        $mixed = new MixedValue([]);
        $this->assertFalse(isset($mixed['key']));
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

        $mixed = new MixedValue([]);
        $mixed[$invalidOffset];
    }

    public function testOffsetGet_AnyOffset_ReturnsMixedValue(): void
    {
        $mixed = new MixedValue([
            ($existingKey = 'key') => 'value',
        ]);
        $this->assertInstanceOf(MixedValue::class, $mixed[$existingKey]);
        $this->assertInstanceOf(MixedValue::class, $mixed['non_existing_key']);
    }

    public function testOffsetSet_ThrowsReadOnlyException(): void
    {
        $this->expectException(ReadOnlyException::class);

        $mixed = new MixedValue([]);
        $mixed['key'] = 'value';
    }

    public function testOffsetUnset_ThrowsReadOnlyException(): void
    {
        $this->expectException(ReadOnlyException::class);
        $this->expectExceptionMessage('Aveiv\MixedValue\MixedValue is readonly');

        $mixed = new MixedValue([
            ($k = 'key') => 'value',
        ]);
        unset($mixed[$k]);
    }

    public function testGetValue_ExistingOffset_ReturnsSameValue(): void
    {
        $mixed = new MixedValue([
            'path' => [
                'to' => ($value = 'value'),
            ],
        ]);
        $this->assertSame($value, $mixed['path']['to']->getValue());
    }

    public function testGetValue_NonExistingOffset_ThrowsMissingValueException(): void
    {
        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage('Value "path.to" does not exists');

        $mixed = new MixedValue([]);
        $mixed['path']['to']->getValue();
    }

    public function testGetValue_OffsetOnNotArray_ThrowsMissingValueException(): void
    {
        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage('Value "path.to" does not exists');

        $mixed = new MixedValue(new \stdClass());
        $mixed['path']['to']->getValue();
    }

    public function testFindValue_ExistingOffset_ReturnsSameValue(): void
    {
        $mixed = new MixedValue([
            'path' => [
                'to' => ($value = 'value'),
            ],
        ]);
        $this->assertSame($value, $mixed['path']['to']->findValue());
    }

    public function testFindValue_NonExistingOffset_ReturnsNull(): void
    {
        $mixed = new MixedValue([]);
        $this->assertNull($mixed['path']['to']->findValue());
    }

    public function testFindValue_OffsetOnNotArray_ReturnsNull(): void
    {
        $mixed = new MixedValue([]);
        $this->assertNull($mixed['path']['to']->findValue());
    }

    public function testCall_NonExistingProcessor_ThrowsUndefinedMethodException(): void
    {
        $this->expectException(UndefinedMethodException::class);
        $this->expectExceptionMessage('Call to undefined method Aveiv\MixedValue\MixedValue::toCustomType()');

        $mixed = new MixedValue([]);
        /** @noinspection PhpUndefinedMethodInspection */
        $mixed->toCustomType();
    }

    public function testCall_ExistingProcessor_ReturnsProcessedValue(): void
    {
        $mixed = new MixedValue(10);
        $mixed->registerValueProcessor('toMultipliedValue', new class implements ValueProcessorInterface {
            public function __invoke($value)
            {
                return $value * $value;
            }
        });
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertSame(100, $mixed->toMultipliedValue()->getValue());
    }
}
