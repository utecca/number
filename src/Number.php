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
    public static function of(Number|string|float|int $value, int|null $decimals = null): self
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
    public function negate(): self
    {
        return $this->mul(-1);
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

    public function inCents(): int
    {
        if ($this->decimals > 2) {
            throw new InvalidArgumentException('The number must have 2 or less decimals.');
        }

        // Force two decimals on the value
        $value = bcadd($this->value, '0', 2);

        return (int) str_replace('.', '', $value);
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
        if ($value instanceof Number) {
            return $value->decimals;
        }

        if (is_string($value)) {
            return strlen(substr($value, strpos($value, ".") + 1));
        }

        if (is_float($value)) {
            $value = (string) $value;
            return strlen(substr($value, strpos($value, ".") + 1));
        }

        return 0;
    }
}
