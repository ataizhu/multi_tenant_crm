@php
    $name = $subscriber->name ?? 'Неизвестно';
    $initials = collect(explode(' ', $name))->map(fn($word) => mb_substr($word, 0, 1))->take(2)->join('');
    $status = $subscriber->status ?? 'inactive';
    $statusColor = match ($status) {
        'active' => 'bg-green-500',
        'inactive' => 'bg-yellow-500',
        'blocked' => 'bg-red-500',
        default => 'bg-gray-500'
    };
@endphp

<div class="relative">
    <!-- Аватар -->
    <div
        class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-lg">
        {{ $initials }}
    </div>

    <!-- Индикатор статуса -->
    <div
        class="absolute -bottom-1 -right-1 w-6 h-6 {{ $statusColor }} rounded-full border-4 border-white shadow-md flex items-center justify-center">
        <div class="w-2 h-2 bg-white rounded-full"></div>
    </div>
</div>