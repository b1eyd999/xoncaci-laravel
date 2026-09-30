<?php

namespace App\Filament\Resources\XoncaResource\Pages;

use App\Filament\Resources\XoncaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListXoncas extends ListRecords
{
    protected static string $resource = XoncaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Yeni xonça')];
    }
}
