<?php

namespace App\Filament\Resources\FillItemResource\Pages;

use App\Filament\Resources\FillItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFillItem extends CreateRecord
{
    protected static string $resource = FillItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
