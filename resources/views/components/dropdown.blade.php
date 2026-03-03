@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-[#0B0F14]/95'])

@php
$alignmentClasses = match ($align) {
    'left' => 'origin-top-left left-0',
    'top' => 'origin-top',
    default => 'origin-top-right right-0',
};

$width = match ($width) {
    '48' => 'w-48',
    '56' => 'w-56',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 mt-2 {{ $width }} rounded-xl shadow-2xl {{ $alignmentClasses }}"
         style="display: none;"
         @click="open = false">
        <div class="rounded-xl border border-white/10 bg-[#0F172A]/95 backdrop-blur {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>