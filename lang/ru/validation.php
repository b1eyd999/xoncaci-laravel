<?php

return [
    'required' => 'Поле «:attribute» обязательно.',
    'required_if' => 'Поле «:attribute» обязательно.',
    'email' => 'Поле «:attribute» должно быть e-mail адресом.',
    'date' => 'Поле «:attribute» должно быть датой.',
    'after_or_equal' => 'Поле «:attribute» должно быть не раньше :date.',
    'max' => ['string' => 'Поле «:attribute» не длиннее :max символов.', 'numeric' => 'Поле «:attribute» не больше :max.'],
    'min' => ['string' => 'Поле «:attribute» не короче :min символов.', 'numeric' => 'Поле «:attribute» не меньше :min.'],
    'in' => 'Выбрано неверное значение поля «:attribute».',
    'exists' => 'Выбрано неверное значение поля «:attribute».',
    'integer' => 'Поле «:attribute» должно быть целым числом.',
    'numeric' => 'Поле «:attribute» должно быть числом.',
    'string' => 'Поле «:attribute» должно быть текстом.',
    'regex' => 'Поле «:attribute» заполнено неверно.',

    'attributes' => [
        'customer_name' => 'Имя', 'phone' => 'Телефон', 'email' => 'E-mail', 'event_date' => 'Дата', 'event_time' => 'Время',
        'occasion_id' => 'Торжество', 'delivery_type' => 'Доставка', 'district' => 'Район', 'address' => 'Адрес', 'note' => 'Комментарий',
    ],
];
