<?php

declare(strict_types=1);

namespace Utecca\Number;

/**
 * @internal
 */
final readonly class BCMath
{
    public static function add(string $num1, string $num2, int $decimals): string
    {
        return self::round(bcadd($num1, $num2, $decimals + 1), $decimals);
    }

    public static function sub(string $num1, string $num2, int $decimals): string
    {
        return self::round(bcsub($num1, $num2, $decimals + 1), $decimals);
    }

    public static function mul(string $num1, string $num2, int $decimals): string
    {
        return self::round(bcmul($num1, $num2, $decimals + 1), $decimals);
    }

    public static function div(string $num1, string $num2, int $decimals): string
    {
        return self::round(bcdiv($num1, $num2, $decimals + 1), $decimals);
    }

    public static function round(string $num, int $decimals): string
    {
        $e = bcpow("10", (string) ($decimals + 1));

        return bcdiv(
            num1: bcadd(
                num1: bcmul($num, $e, 0),
                num2: str_starts_with($num, '-') ? "-5" : "5"
            ),
            num2: $e,
            scale: $decimals
        );
    }

    public static function ceil(string $num): string
    {
        return str_starts_with($num, '-')
            ? (($v = self::floor(substr($num, 1))) ? "-$v" : $v)
            : bcadd(strtok($num, '.'), strtok('.') != 0 ? "1" : "0");
    }

    public static function floor(string $num): string
    {
        return str_starts_with($num, '-')
            ? '-' . self::ceil(substr($num, 1))
            : strtok($num, '.');
    }
}
