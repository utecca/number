# Number for Laravel

This package provides a number class that can be used to represent numbers.

All operations are done using bcmath which means that arbitrary precision is supported.

## Installation

```bash
composer require utecca/number
```

## Usage

```php
// Init via constructor
new Number(123.456);

// Init via static method
Number::of('123.45');

// Force a certain number of decimals
Number::of('123.45', 1); // Will return 123.5
```

## Laravel casting
You can have models cast to Number by adding the following to your model:

```php
protected $casts = [
    // Amount with two decimals (usually used for monetary values) 
    'amount' => Utecca\Number\Casts\NumberFromDecimal::class,
    // Amount with a custom number of decimals
    'quantity' => Utecca\Number\Casts\NumberFromDecimal::class . ':4'),
];
```

## Operations

When doing operations, you always have the option to specify the number of decimals to use.

If not specified, the max number of decimals will be taken from the first operand's max.

```php
// Addition
$number->add(100);

// Subtraction
$number->sub(100);

// Multiplication
$number->mul(100);

// Division
$number->div(100);

// Percentage
$number->percent(50);

// Round
$number->round(2);

// Floor
$number->floor();

// Ceil
$number->ceil();

// Absolute
$number->abs();
```

## Other methods

```php

// Various getters
$number->isZero();
$number->isPositive();
$number->isPositiveOrZero();
$number->isNegative();
$number->isNegativeOrZero();
$number->lt(100);
$number->lte(100);
$number->gt(100);
$number->gte(100);
$number->eq(100);

// Various formatters
$number->toString();
$number->toInt();
$number->inCents(); // Only works if the number has two decimals or less
```
