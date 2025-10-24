<?php

return [
    'methods' => [
        'cash' => 'Наличные',
        'card' => 'Карта',
        'bank_transfer' => 'Банковский перевод',
        'online' => 'Онлайн',
        'check' => 'Чек',
    ],
    'statuses' => [
        'pending' => 'Ожидает',
        'completed' => 'Завершен',
        'failed' => 'Неудачный',
        'refunded' => 'Возвращен',
        'cancelled' => 'Отменен',
    ],
    'labels' => [
        'payment_number' => 'Номер платежа',
        'payment_date' => 'Дата платежа',
        'amount' => 'Сумма',
        'payment_method' => 'Способ оплаты',
        'status' => 'Статус',
        'reference' => 'Ссылка',
        'notes' => 'Примечания',
        'transaction_id' => 'ID транзакции',
    ],
    'sections' => [
        'basic' => 'Основная информация',
        'transaction' => 'Информация о транзакции',
        'status' => 'Статус и примечания',
    ],
    'tabs' => [
        'invoice' => 'Связанный счет',
        'details' => 'Детали',
    ],
];
