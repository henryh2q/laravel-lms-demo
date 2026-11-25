{{-- $fields: ['title' => ['input', required], 'body' => ['textarea', required]], $values: model or array --}}
<div class="grid gap-3 lg:grid-cols-3">
    @foreach (config('app.supported_locales') as $code => $label)
        <fieldset class="space-y-2 rounded-lg border border-slate-200 p-3">
            <legend class="px-1 text-xs font-semibold">{{ $label }} @if ($code === 'en')<span class="text-red-600">*</span>@endif</legend>
            @foreach ($fields as $name => [$type, $placeholder])
                @if ($type === 'textarea')
                    <textarea name="{{ $name }}[{{ $code }}]" rows="{{ $rows ?? 3 }}" placeholder="{{ $placeholder }}" class="input" @required($code === 'en')>{{ $values[$name][$code] ?? '' }}</textarea>
                @else
                    <input name="{{ $name }}[{{ $code }}]" value="{{ $values[$name][$code] ?? '' }}" placeholder="{{ $placeholder }}" class="input" @required($code === 'en')>
                @endif
            @endforeach
        </fieldset>
    @endforeach
</div>
