<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Pest\Expectation;
use Utecca\Number\Number;

it('can instantiate a NumberFromDecimal from values', function (string|int|float|Number $value) {
    /** @var Expectation<Number> $expectation */
    $expectation = expect(Number::of($value, 8))->toBeInstanceOf(Number::class);

    $type = gettype($value);
    if ($type == 'double') {
        expect($expectation->value->toFloat())->toBeFloat()->toBe($value);
    } elseif ($type == 'string') {
        expect((string) $expectation->value)->toBeString()->toBe($value);
    } elseif ($type == 'integer') {
        expect($expectation->value->toInt())->toBeInt()->toBe($value);
    } elseif ($type == 'object' && $value instanceof Number) {
        expect($expectation->value->value)->toEqual($value->value);
    } else {
        $this->fail('An invalid type was provided in the dataset');
    }
})->with([
    '`1.0` as string' => '1.01',
    '`1` as string' => '1',
    '`0.01` as string' => '0.01',
    '`1` as int' => 1,
    '`10` as int' => 10,
    '`0.1` as float' => 0.1,
    '`0.00000001` as float' => 0.00000001,
    '`1.0` as NumberFromDecimal from string' => Number::of('1.0', 1),
    '`1` as NumberFromDecimal from integer' => Number::of(1),
    '`0.1` as NumberFromDecimal from float' => Number::of(0.1, 1),
]);

it('is immutable', function () {
    $number = Number::of('123');

    expect($number->value)->toEqual(Number::of(123));

    $number->add(Number::of(123));

    expect($number->value)->toEqual(Number::of(123));

    $number->sub(Number::of(123));

    expect($number->value)->toEqual(Number::of(123));

    $number->mul(Number::of(2));

    expect($number->value)->toEqual(Number::of(123));

    $number->div(Number::of(2));

    expect($number->value)->toEqual(Number::of(123));
});

it('can add numbers', function (string|int|float $number, string|int|float $change, string|int|float $result) {
    expect(
        Number::of($number, 3)->add($change)->value
    )->toEqual(Number::of($result, 3)->value);
})->with([
    'integers as strings' => ['123456', '123456', '246912'],
    'integers' => [123456, 123456, 246912],
    'floats as strings' => ['0.001', '0.002', '0.003'],
    'floats' => [0.001, 0.002, 0.003],
]);

it('can subtract numbers', function (string|int|float $number, string|int|float $change, string|int|float $result) {
    expect(
        Number::of($number)->sub($change)->value
    )->toEqual(Number::of($result)->value);
})->with([
    'integers as strings as NumberFromDecimal' => ['123456', Number::of('123456'), '0'],
    'integers as strings' => ['123456', '123456', '0'],
    'integers as NumberFromDecimal' => [123456, Number::of(123456), 0],
    'integers' => [123456, 123456, 0],
    'floats as strings as NumberFromDecimal' => ['0.002', Number::of('0.001'), '0.001'],
    'floats as strings' => ['0.002', '0.001', '0.001'],
    'floats as NumberFromDecimal' => [0.002, Number::of(0.001, 3), 0.001],
    'floats' => [0.002, 0.001, 0.001],
]);

it(
    'can multiply numbers',
    function (string|int|float $number, string|int|float|Number $change, string|int|float $result) {
        expect(
            Number::of($number, 6)->mul($change)->value
        )->toEqual(Number::of($result, 6)->value);
    }
)->with([
    'integers as strings as NumberFromDecimal' => ['2', Number::of('10'), '20'],
    'integers as strings' => ['2', '10', '20'],
    'integers as NumberFromDecimal' => [2, Number::of(10), 20],
    'integers' => [2, 10, 20],
    'floats as strings as NumberFromDecimal' => ['0.001', Number::of('0.002'), '0.000002'],
]);

it(
    'can divide numbers',
    function (string|int|float $number, string|int|float|Number $change, string|int|float $result) {
        expect(
            Number::of($number, 3)->div($change)->value
        )->toEqual(Number::of($result, 3)->value);
    }
)->with([
    'integers as strings as NumberFromDecimal' => ['10', Number::of('2'), '5.00'],
    'integers as strings' => ['10', '2', '5.00'],
    'integers as NumberFromDecimal' => [10, Number::of(2), '5.00'],
    'integers' => [10, 2, '5.00'],
    'floats as strings as NumberFromDecimal' => ['10.02', Number::of('2'), '5.01'],
]);

it(
    'can get percentage of numbers',
    function (string|int|float $number, int|float|Number $percentage, string|int|float $result) {
        expect(
            Number::of($number)->percentage($percentage, 9)->value
        )->toEqual(Number::of($result, 8)->value);
    }
)->with([
    'integers as strings as NumberFromDecimal' => ['500', Number::of('10'), '50'],
    'integers as strings' => ['500', '10', '50'],
    'integers as NumberFromDecimal' => [500, Number::of(10), 50],
    'integers' => [500, 10, 50],
    'floats' => [500.00412, 10.441255, 52.20670518],
]);

it('can negate numbers', function (string|int|float $number, string|int|float $result) {
    expect(
        Number::of($number, 3)->negate()->value
    )->toEqual(Number::of($result, 3)->value);
})->with([
    'integers as strings' => ['10', '-10'],
    'integers' => [2, -2],
    'floats as strings' => ['0.002', '-0.002'],
    'floats' => [0.002, -0.002],
]);

it('can get underlying value as string', function (string $number, string $result) {
    expect(
        Number::of($number, 10)->value
    )->toEqual(Number::of($result, 10)->value);
})->with([
    'integers as strings' => ['10', '10'],
    'integers' => [2, 2],
    'floats as strings' => ['0.002', '0.002'],
    'floats' => [0.002, 0.002],
    'large floats as strings' => ['1000000001.1000000001', '1000000001.1000000001'],
]);

it('can get underlying value as float', function (string $number, float $result) {
    expect(Number::of($number, 3)->toFloat())->toEqual($result);
})->with([
    'integers as strings' => ['10', 10],
    'integers' => [2, 2],
    'floats as strings' => ['0.002', 0.002],
    'floats' => [0.002, 0.002],
    'large floats as strings' => ['1000000001.1000000001', 1000000001.1000000001],
]);

it('can check whether number is less than', function (int $number, int $lessThan, bool $result) {
    expect(Number::of($number)->lt($lessThan))->toBe($result);
})->with([
    '10 less than 15' => [10, 15, true],
    '15 not less than 10' => [15, 10, false],
]);

it('can check whether number is less than or equal to', function (int $number, int $greaterThan, bool $result) {
    expect(Number::of($number)->lte($greaterThan))->toBe($result);
})->with([
    '10 less than / equal to 15' => [10, 15, true],
    '10 less than / equal to 10' => [10, 10, true],
    '15 not less than / equal to 10' => [15, 10, false],
]);

it('can check whether number is greater than', function (int $number, int $greaterThan, bool $result) {
    expect(Number::of($number)->gt($greaterThan))->toBe($result);
})->with([
    '15 greater than 10' => [15, 10, true],
    '10 not greater than 15' => [10, 15, false],
]);

it('can check whether number is greater than or equal to', function (int $number, int $greaterThan, bool $result) {
    expect(Number::of($number)->gte($greaterThan))->toBe($result);
})->with([
    '15 greater than / equal to 10' => [15, 10, true],
    '10 greater than / equal to 10' => [10, 10, true],
    '10 not greater than / equal to 15' => [10, 15, false],
]);

it('can check whether number is equal to', function (int $number, int $eq, bool $result) {
    expect(Number::of($number)->eq($eq))->toBe($result);
})->with([
    '15 equal to 10' => [10, 10, true],
    '10 not equal to 15' => [10, 15, false],
]);

it('can check whether number is zero', function (int $number, bool $result) {
    expect(Number::of($number, 1)->isZero())->toBe($result);
})->with([
    '0 is zero' => [0, true],
    '10 is not zero' => [10, false],
    '1.0 is not zero' => [1.0, false],
    '-1.0 is not zero' => [-1.0, false],
]);

it('can check whether number is negative', function (int $number, bool $result) {
    expect(Number::of($number)->isNegative())->toBe($result);
})->with([
    '-1 is negative' => [-1, true],
    '-10000 is negative' => [-10000, true],
    '0 is not negative' => [0, false],
    '1 is not negative' => [1, false],
    '10000 is not negative' => [10000, false],
]);

it('can check whether number is negative or zero', function (int $number, bool $result) {
    expect(Number::of($number)->isNegativeOrZero())->toBe($result);
})->with([
    '-1 is negative or zero' => [-1, true],
    '-10000 is negative or zero' => [-10000, true],
    '0 is not negative or zero' => [0, true],
    '1 is not negative or zero' => [1, false],
    '10000 is not negative or zero' => [10000, false],
]);

it('can check whether number is positive', function (int $number, bool $result) {
    expect(Number::of($number)->isPositive())->toBe($result);
})->with([
    '1 is positive' => [1, true],
    '10000 is positive' => [10000, true],
    '0 is not positive' => [0, false],
    '-1 is not positive' => [-1, false],
    '-10000 is not positive' => [-10000, false],
]);

it('can check whether number is positive or zero', function (int $number, bool $result) {
    expect(Number::of($number)->isPositiveOrZero())->toBe($result);
})->with([
    '1 is positive or zero' => [1, true],
    '10000 is positive or zero' => [10000, true],
    '0 is positive or zero' => [0, true],
    '-1 is not positive or zero' => [-1, false],
    '-10000 is not positive or zero' => [-10000, false],
]);

it('can get NumberFromDecimal in cents', function (int|string|float $number, int $result) {
    expect(Number::of($number, 2)->inCents())->toBe($result);
})->with([
    '1.00 as string is 1' => ['1.00', 100],
    '10.01 is 1001' => [10.01, 1001],
    '0 is 0' => [0.00, 0],
    '101.10 as string is 10110' => ['101.10', 10110],
    '1111111101.99 as string is 111111110199' => ['1111111101.99', 111111110199],
    '1.10 as string is 110' => ['1.10', 110],
    '-1.10 is -110' => [-1.10, -110],
    '-1111111101.99 is -111111110199' => [-1111111101.99, -111111110199],
]);

it('can round numbers correctly', function (float $number, int $decimals, float $result) {
    expect(Number::of($number)->round($decimals)->toFloat())->toBe($result);
})->with([
    '1.005 rounds to 1.01' => [1.005, 2, 1.01],
    '1.014 rounds to 1.01' => [1.014, 2, 1.01],
]);

it('can ceil numbers correctly', function () {
    $this->assertTrue(Number::of(1.005)->ceil()->eq(2));
});

it('can floor numbers correctly', function () {
    $this->assertTrue(Number::of(1.005)->floor()->eq(1));
});

it('can return decimal fraction', function (float $value, float $result) {
    $number = Number::of($value)->decimalFraction();
    $decimals = $number->decimalFraction();

    $this->assertTrue($decimals->eq($result));
})->with([
    [1.005, 0.005],
    [-1.005, -0.005],
]);

it('sets number of decimals correctly', function (int|float $value, int|float $quantity) {
    $number = Number::of($value);
    $this->assertTrue($number->decimals === $quantity);
})
->with([
    ['value' => 1, 'quantity' => 2], // It should default to two decimals, if there are fewer than two
    ['value' => 1.1, 'quantity' => 2], // It should default to two decimals, if there are fewer than two
    ['value' => 1.005, 'quantity' => 3],
    ['value' => 1.0000000005, 'quantity' => 10],
]);

it('can format number correctly', function (float $value, string $result, bool $europeanStyle) {
    $this->assertEquals($result, Number::of($value)->format(2, $europeanStyle));
})->with([
    'american style' => [1_234_567.89, '1,234,567.89', false],
    'european style' => [1_234_567.89, '1.234.567,89', true],
]);

it('can instantiate a Number from cents', function (float $expected, int $cents) {
    $this->assertEquals($expected, Number::fromCents($cents)->toFloat());
})->with([
    [1.00, 100],
    [10.01, 1001],
    [0.00, 0],
    [0.01, 1],
    [0.10, 10],
    [101.10, 10110],
    [1111111101.99, 111111110199],
    [1.10, 110],
    [-1.10, -110],
    [-1111111101.99, -111111110199],
]);

it('can determine the min of numbers', function (int|float $expected, array $numbers) {
    $numbers = array_map(fn ($number) => Number::of($number), $numbers);

    // Test with array
    $this->assertEquals(
        expected: $expected,
        actual: Number::min($numbers)->toFloat()
    );

    // Test with collection
    $this->assertEquals(
        expected: $expected,
        actual: Number::min(new Collection($numbers))->toFloat()
    );
})->with([
    [1.24, [2.1, 1.24]],
    [1, [2, 5, 1, 5]],
    [-2, [1, -2, -1, 5]],
    [-2, [-1, -2]],
]);

it('can determine the max of numbers', function (int|float $expected, array $numbers) {
    $numbers = array_map(fn ($number) => Number::of($number), $numbers);

    // Test with array
    $this->assertEquals(
        expected: $expected,
        actual: Number::max($numbers)->toFloat()
    );

    // Test with collection
    $this->assertEquals(
        expected: $expected,
        actual: Number::max(new Collection($numbers))->toFloat()
    );
})->with([
    [2.1, [2.1, 1.24]],
    [5, [2, 5, 1, 5]],
    [5, [1, -2, -1, 5]],
    [-1, [-1, -2]],
]);
