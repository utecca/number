<?php

declare(strict_types=1);

namespace Utecca\Number\Exceptions;

use Exception;

class NotANumberException extends Exception
{
    protected $message = 'The given value is not a number';
}
