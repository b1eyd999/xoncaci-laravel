<?php

namespace App\Filament\Resources\FillCategoryResource\Pages;

use App\Filament\Resources\FillCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFillCategories extends ListRecords
{
    protected static string $resource = FillCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
