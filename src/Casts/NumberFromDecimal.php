<?php

declare(strict_types=1);

namespace Utecca\Number\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Utecca\Number\Exceptions\NotANumberException;
use Utecca\Number\Number;

/**
 * @implements CastsAttributes<Number, Number>
 */
readonly class NumberFromDecimal implements CastsAttributes
{
    public function __construct(
        private int $decimals = 2,
    ) {
    }

    /**
     * @param  float|string  $value
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value == null) {
            return null;
        }

        return Number::of($value, $this->decimals);
    }

    /**
     * @param Number $value
     * @throws NotANumberException
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value == null) {
            return null;
        }

        if (! $value instanceof Number) {
            throw new NotANumberException();
        }

        return $value->toString();
    }
    public static function decimals(int $decimals): self
    {
        return new self($decimals);
    }
}
