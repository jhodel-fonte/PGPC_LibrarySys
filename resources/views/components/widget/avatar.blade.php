@props(['person' => null])

@php
    $name = $person ? trim($person->first_name . ' ' . $person->last_name) : (auth()->user()?->name ?? auth()->user()?->username ?? 'Profile');
    $initial = mb_strtoupper(mb_substr($person?->first_name ?: (auth()->user()?->name ?? auth()->user()?->username ?? 'P'), 0, 1));
    $hasImage = $person && $person->getRawOriginal('image_url');
@endphp

<span x-data="{ imgError: false }" {{ $attributes->class(['inline-flex shrink-0 overflow-hidden rounded-full']) }}>
    @if($hasImage)
        <img src="{{ $person->image_url }}" alt="" class="h-full w-full object-cover" x-show="!imgError" x-on:error="imgError = true">
    @endif
    <span @if($hasImage) x-show="imgError" style="display: none;" @endif class="flex h-full w-full items-center justify-center bg-[#102B70] font-bold text-white" aria-label="{{ $name }}">{{ $initial }}</span>
</span>
