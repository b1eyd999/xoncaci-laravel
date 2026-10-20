<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Russian and English versions of a record's texts. Empty fields show the
 * Azerbaijani text on the site, so translating can be done a bit at a time.
 *
 *   Translations::make(['name' => 'Ad', 'description' => ['Təsvir', 'textarea']])
 */
class Translations
{
    public static function make(array $fields, string $heading = 'Tərcümələr (RU / EN)'): Section
    {
        $tabs = [];
        foreach (['ru' => 'Русский', 'en' => 'English'] as $locale => $label) {
            $inputs = [];
            foreach ($fields as $field => $spec) {
                [$title, $type] = is_array($spec) ? $spec + [1 => 'text'] : [$spec, 'text'];
                $inputs[] = ($type === 'textarea'
                    ? Textarea::make("translations.{$locale}.{$field}")->rows(3)
                    : TextInput::make("translations.{$locale}.{$field}"))
                    ->label($title . ' (' . strtoupper($locale) . ')');
            }
            $tabs[] = Tabs\Tab::make($label)->schema($inputs);
        }

        return Section::make($heading)
            ->description('Boş qalan sahədə saytda azərbaycanca mətn göstərilir.')
            ->icon('heroicon-m-language')
            ->collapsible()
            ->schema([Tabs::make()->tabs($tabs)]);
    }
}
