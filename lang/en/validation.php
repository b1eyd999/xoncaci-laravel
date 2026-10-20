<?php

// Laravel's own English messages, with our field names.
return array_replace(require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php'), [
    'attributes' => [
        'customer_name' => 'name', 'phone' => 'phone', 'email' => 'e-mail', 'event_date' => 'date', 'event_time' => 'time',
        'occasion_id' => 'occasion', 'delivery_type' => 'delivery', 'district' => 'district', 'address' => 'address', 'note' => 'note',
    ],
]);
