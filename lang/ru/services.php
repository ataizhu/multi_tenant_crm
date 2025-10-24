<?php

return [
    'types' => [
        'water' => 'Водоснабжение',
        'electricity' => 'Электроснабжение',
        'gas' => 'Газоснабжение',
        'heating' => 'Отопление',
        'internet' => 'Интернет',
        'phone' => 'Телефон',
        'maintenance' => 'Обслуживание',
    ],
    'statuses' => [
        'active' => 'Активная',
        'inactive' => 'Неактивная',
        'suspended' => 'Приостановлена',
        'terminated' => 'Завершена',
    ],
    'labels' => [
        'name' => 'Название услуги',
        'type' => 'Тип услуги',
        'description' => 'Описание',
        'price' => 'Цена',
        'unit' => 'Единица измерения',
        'status' => 'Статус',
        'start_date' => 'Дата начала',
        'end_date' => 'Дата окончания',
        'billing_cycle' => 'Цикл биллинга',
    ],
    'sections' => [
        'basic' => 'Основная информация',
        'pricing' => 'Ценообразование',
        'schedule' => 'Расписание',
    ],
    'subscribers_count' => 'Количество абонентов',
    'invoices_count' => 'Количество счетов',
];
