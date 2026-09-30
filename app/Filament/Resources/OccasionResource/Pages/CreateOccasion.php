<?php

namespace App\Filament\Resources\OccasionResource\Pages;

use App\Filament\Resources\OccasionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOccasion extends CreateRecord
{
    protected static string $resource = OccasionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
