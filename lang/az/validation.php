<?php

return [
    'required' => ':attribute mütləqdir.',
    'required_if' => ':attribute mütləqdir.',
    'email' => ':attribute düzgün e-poçt olmalıdır.',
    'date' => ':attribute düzgün tarix olmalıdır.',
    'after_or_equal' => ':attribute :date və ya sonrakı tarix olmalıdır.',
    'max' => ['string' => ':attribute :max simvoldan uzun ola bilməz.', 'numeric' => ':attribute :max-dan böyük ola bilməz.'],
    'min' => ['string' => ':attribute ən azı :min simvol olmalıdır.', 'numeric' => ':attribute ən azı :min olmalıdır.'],
    'in' => 'Seçilmiş :attribute yanlışdır.',
    'exists' => 'Seçilmiş :attribute yanlışdır.',
    'integer' => ':attribute tam ədəd olmalıdır.',
    'numeric' => ':attribute rəqəm olmalıdır.',
    'string' => ':attribute mətn olmalıdır.',
    'regex' => ':attribute formatı yanlışdır.',
    'unique' => 'Bu :attribute artıq istifadə olunur.',
    'image' => ':attribute şəkil olmalıdır.',
    'confirmed' => ':attribute təsdiqi uyğun gəlmir.',

    'attributes' => [
        'customer_name' => 'Ad',
        'phone' => 'Telefon',
        'email' => 'E-poçt',
        'event_date' => 'Tarix',
        'event_time' => 'Saat',
        'occasion_id' => 'Məclis növü',
        'delivery_type' => 'Çatdırılma',
        'district' => 'Rayon',
        'address' => 'Ünvan',
        'note' => 'Qeyd',
    ],
];
