@props(['title' => 'Modal', 'show' => false, 'size' => '2xl'])

@php
    $sizeClasses = match ($size) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div class="fixed inset-0 bg-black/30  flex items-center justify-center z-50" x-cloak>
    <!-- Modal Container -->
    <div @click.away="open = false" class="bg-white rounded-lg shadow-lg w-full {{ $sizeClasses }}">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <!-- Modal Body -->
        <div class="px-6 py-4">
            {{ $slot }}
        </div>
        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t flex justify-end space-x-2">
            <x-button @click="open = false" varient="ghost">Cancel</x-button>
            <x-button varient="primary">Confirm</x-button>
        </div>
    </div>
</div>
