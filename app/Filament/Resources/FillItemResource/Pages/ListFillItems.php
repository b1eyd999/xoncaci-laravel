<?php

namespace App\Filament\Resources\FillItemResource\Pages;

use App\Filament\Resources\FillItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFillItems extends ListRecords
{
    protected static string $resource = FillItemResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
