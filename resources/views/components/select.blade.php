
<select
    {{ $attributes->merge([
        'class' => '
            w-full
            border border-input
            bg-background
            px-3 py-2
            text-sm
            ring-offset-background
            placeholder:text-muted-foreground
            focus:outline-none
            focus:ring-2
            focus:ring-ring
            focus:ring-offset-2
            disabled:cursor-not-allowed
            disabled:opacity-50
            rounded-md mt-2
        '
    ]) }}
>
{{ $slot }}
</select>
