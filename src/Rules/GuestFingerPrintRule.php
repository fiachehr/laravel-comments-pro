<?php

namespace Fiachehr\Comments\Rules;

use Closure;
use Fiachehr\Comments\Helper\GuestFingerprint;
use Illuminate\Contracts\Validation\ValidationRule;

class GuestFingerPrintRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        if (! GuestFingerprint::validate($value)) {
            $fail('The :attribute must be a valid guest fingerprint.');
        }
    }
}
