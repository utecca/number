<?php

declare(strict_types=1);

namespace Utecca\Number;

use InvalidArgumentException;

readonly class Number
{
    public string $value;
    public int $decimals;

    /**
     * If the decimals are not defined, the number of decimals will be counted and limited.
     */
    public function __construct(string $value, int|null $decimals = null)
    {
        $this->decimals = $decimals ?? self::countDecimals($value);

        // Round the value to the specified number of decimals.
        $value = BCMath::round($value, $this->decimals);
        $this->value = $this->sanitize($value);
    }

    /**
     * If the decimals are not defined, the number of decimals will be counted and limited.
     */
    public static function of(Number|string|float|int $value = 0, int|null $decimals = null): self
    {
        if ($decimals === null) {
            $decimals = self::countDecimals($value);
        }

        if ($value instanceof Number) {
            if ($value->decimals === $decimals) {
                return $value;
            }

            return new self($value->value, $decimals);
        }

        if (is_float($value)) {
            // We convert the float to a string with number_format to avoid an E notation.
            $number = number_format($value, $decimals + 1, '.', '');
            return new self(BCMath::round($number, $decimals), $decimals);
        }
        if (is_numeric($value)) {
            return new self((string)$value, $decimals);
        }

        throw new InvalidArgumentException('The value must be numeric or a Number instance.');
    }

    /**
     * Add the number with another number.
     */
    public function add(Number|string|float|int $number, int|null $decimals = null): self
    {
        $number = self::of($number);

        return new self(BCMath::add(
            num1: $this->value,
            num2: $number->value,
            decimals: $decimals ?? $this->decimals
        ), $decimals ?? $this->decimals);
    }

    /**
     * Subtract the number with another number.
     */
    public function sub(Number|string|float|int $number, int|null $decimals = null): self
    {
        $number = self::of($number);

        return new self(BCMath::sub(
            num1: $this->value,
            num2: $number->value,
            decimals: $decimals ?? $this->decimals
        ), $decimals ?? $this->decimals);
    }

    /**
     * Multiply the number with another number.
     */
    public function mul(Number|string|float|int $number, int|null $decimals = null): self
    {
        $number = self::of($number);

        return new self(BCMath::mul(
            num1: $this->value,
            num2: $number->value,
            decimals: $decimals ?? $this->decimals
        ), $decimals ?? $this->decimals);
    }

    /**
     * Divide the number with another number.
     */
    public function div(Number|string|float|int $value, int|null $decimals = null): self
    {
        $value = self::of($value);

        return new self(BCMath::div(
            num1: $this->value,
            num2: $value->value,
            decimals: $decimals ?? $this->decimals
        ), $decimals ?? $this->decimals);
    }

    /**
     * Add or remove the sign of the number.
     */
    public function negate(bool $when = true): self
    {
        if ($when) {
            return $this->mul(-1);
        }

        return $this;
    }

    /**
     * Get the absolute value of the number, i.e. remove the sign.
     */
    public function abs(): self
    {
        return new self(str_replace('-', '', $this->value));
    }

    /**
     * Round the number.
     */
    public function round(int $decimals = 0): self
    {
        return new self(BCMath::round($this->value, $decimals));
    }

    public function ceil(): self
    {
        return new self(BCMath::ceil($this->value));
    }

    public function floor(): self
    {
        return new self(BCMath::floor($this->value));
    }

    /**
     * Calculate a given percentage of the number.
     */
    public function percentage(Number|string|float|int $value, int|null $decimals = null): self
    {
        $value = self::of($value, 25);

        return Number::of($this->value, 25)->div(100)->mul($value, $decimals ?? $this->decimals);
    }

    public function isZero(): bool
    {
        return 0 == (float) $this->value;
    }

    public function isPositive(): bool
    {
        return $this->gt(0);
    }


    public function isPositiveOrZero(): bool
    {
        return $this->gt(0) || $this->isZero();
    }

    public function isNegative(): bool
    {
        return $this->lt(0);
    }

    public function isNegativeOrZero(): bool
    {
        return $this->lt(0) || $this->isZero();
    }

    public function lt(Number|string|float|int $value): bool
    {
        $value = self::of($value);

        return $this->value < $value->value;
    }

    public function lte(Number|string|float|int $value): bool
    {
        $value = self::of($value);

        return $this->value <= $value->value;
    }

    public function gt(Number|string|float|int $value): bool
    {
        $value = self::of($value);

        return $this->value > $value->value;
    }

    public function gte(Number|string|float|int $value): bool
    {
        $value = self::of($value);

        return $this->value >= $value->value;
    }

    public function eq(Number|string|float|int $value): bool
    {
        $value = self::of($value);

        return $this->value === $value->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    public function toInt(): int
    {
        return (int) $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Convert the number to a monetary amount, ie. a number with two decimals.
     */
    public function toMonetaryAmount(): string
    {
        return bcadd($this->value, '0', 2);
    }

    public function inCents(): int
    {
        if ($this->decimals > 2) {
            throw new InvalidArgumentException('The number must have 2 or less decimals.');
        }

        // Force two decimals on the value
        $value = bcadd($this->value, '0', 2);

        return (int) str_replace('.', '', $value);
    }

    public function format(int $decimals, bool $europeanStyle = true): string
    {
        if ($europeanStyle) {
            return number_format($this->toFloat(), $decimals, ',', '.');
        }
        
        return number_format($this->toFloat(), $decimals);
    }

    /**
     * Get the decimal fraction of the number, eg. 0.25 for 1.25 or -0.5 for -2.5.
     */
    public function decimalFraction(): Number
    {
        // Get decimals
        $decimalPosition = strrpos($this->value, '.');

        $value = $decimalPosition === false
            ? '0'
            : substr($this->value, $decimalPosition + 1);

        // Create new value only with decimals
        $value = "0.{$value}";

        return Number::of($value)->negate(when: $this->isNegative());
    }

    /**
     * Exchange the number with an exchange rate, eg. 1000 with exchangeRate 745 will return 7450
     */
    public function exchangeWithRate(Number|string|float|int $exchangeRate, int|null $decimals = null): Number
    {
        return $this->mul($exchangeRate)->div(100, $decimals);
    }

    private function sanitize(string $value): string
    {
        if ($value === "0") {
            return $value;
        }

        if (! str_contains($value, ".")) {
            return $value;
        }

        // Remove trailing zeros
        $value = rtrim($value, "0");

        // Remove trailing dots (if no decimals left)
        return rtrim($value, ".");
    }

    private static function countDecimals(Number|string|float|int $value): int
    {
        $decimals = match (true) {
            $value instanceof Number => $value->decimals,
            is_string($value) => strlen(substr($value, strpos($value, ".") + 1)),
            is_float($value) => strlen(substr((string) $value, strpos((string) $value, ".") + 1)),
            default => 0,
        };

        // Return at least 2 decimals
        return max([2, $decimals]);
    }
}
