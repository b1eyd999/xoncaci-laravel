<?php

namespace App\Filament\Resources\FillCategoryResource\Pages;

use App\Filament\Resources\FillCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFillCategory extends CreateRecord
{
    protected static string $resource = FillCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
