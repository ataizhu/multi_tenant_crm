<?php

return [
    'methods' => [
        'cash' => 'Cash',
        'card' => 'Card',
        'bank_transfer' => 'Bank Transfer',
        'online' => 'Online',
        'check' => 'Check',
    ],
    'statuses' => [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
        'cancelled' => 'Cancelled',
    ],
    'labels' => [
        'payment_number' => 'Payment Number',
        'payment_date' => 'Payment Date',
        'amount' => 'Amount',
        'payment_method' => 'Payment Method',
        'status' => 'Status',
        'reference' => 'Reference',
        'notes' => 'Notes',
        'transaction_id' => 'Transaction ID',
    ],
    'sections' => [
        'basic' => 'Basic Information',
        'transaction' => 'Transaction Information',
        'status' => 'Status and Notes',
    ],
    'tabs' => [
        'invoice' => 'Related Invoice',
        'details' => 'Details',
    ],
];
