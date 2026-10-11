<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    public const MIN_LENGTH = 8;

    public static function rules(): array
    {
        return ['required', 'confirmed', self::password()];
    }

    public static function password(): Password
    {
        return Password::min(self::MIN_LENGTH)->letters()->numbers();
    }

    public static function hint(): string
    {
        return 'At least '.self::MIN_LENGTH.' characters, including a letter and a number.';
    }
}
