<?php
// src/Utils/CepFormatter.php
namespace PauloSanda\MultipleCepApi\Utils;

class CepFormatter
{
    public const JUST_NUMBERS = 'apenas_numeros';
    public const USE_HYPHEN = 'com_hifen';
    public const ANY = 'qualquer';

    public static function normalize(string $cep): string
    {
        return preg_replace('/[^0-9]/', '', $cep);
    }

    public static function validate(string $cep): bool
    {
        $normalized = self::normalize($cep);
        return strlen($normalized) === 8 && preg_match('/^[0-9]{8}$/', $normalized);
    }

    public static function format(string $cep, string $format): string
    {
        $normalized = self::normalize($cep);

        return match($format) {
            self::JUST_NUMBERS => $normalized,
            self::USE_HYPHEN => substr($normalized, 0, 5) . '-' . substr($normalized, 5, 3),
            default => $cep,
        };
    }
}