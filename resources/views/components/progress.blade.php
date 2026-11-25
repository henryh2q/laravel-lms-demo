@props(['value' => 0, 'color' => '#e2603a'])
<div {{ $attributes->merge(['class' => 'h-2 w-full overflow-hidden rounded-full bg-stone-200']) }} role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100">
    <div class="h-full rounded-full transition-all" style="width: {{ $value }}%; background: {{ $color }}"></div>
</div>
