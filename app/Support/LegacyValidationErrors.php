<?php

namespace App\Support;

final class LegacyValidationErrors
{
    private array $errors = [];

    public function set(array $errors): void
    {
        $this->errors = [];
        foreach ($errors as $field => $messages) {
            $this->errors[$field] = is_array($messages) ? (string) ($messages[0] ?? '') : (string) $messages;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
