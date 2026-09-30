<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'İdarə paneli';

    protected static ?string $navigationLabel = 'İdarə paneli';

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 2];
    }
}
