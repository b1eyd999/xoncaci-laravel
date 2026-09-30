<?php

// Filament's own Azerbaijani file leaves this word in English.
return array_replace_recursive(
    require base_path('vendor/filament/filament/resources/lang/az/resources/pages/list-records.php'),
    ['breadcrumb' => 'Siyahı'],
);
