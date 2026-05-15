<?php

namespace App\Exceptions;

use RuntimeException;

class ClinicalScribeException extends RuntimeException
{
    public static function invalidJson(string $raw): self
    {
        return new self('OpenAI returned malformed JSON: '.mb_substr($raw, 0, 200));
    }

    public static function missingSection(string $section): self
    {
        return new self("SOAP response is missing required section: {$section}");
    }

    public static function apiFailure(string $message): self
    {
        return new self("Clinical scribe API call failed: {$message}");
    }
}
