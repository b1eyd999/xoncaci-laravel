<?php

namespace App\Models\Concerns;

/**
 * Azerbaijani lives in the ordinary columns; Russian and English in the
 * `translations` JSON. A field not yet translated falls back to Azerbaijani,
 * so a new xonça is never shown with an empty name.
 */
trait Translatable
{
    public function initializeTranslatable(): void
    {
        $this->mergeCasts(['translations' => 'array']);
    }

    public function tr(string $field): ?string
    {
        $locale = app()->getLocale();
        if ($locale !== 'az') {
            $value = $this->translations[$locale][$field] ?? null;
            if (filled($value)) {
                return $value;
            }
        }

        return $this->{$field};
    }
}
