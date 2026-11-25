<form method="POST" action="{{ $action }}" class="space-y-3 border-t border-slate-100 p-4">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    @include('admin.courses._translations', ['fields' => ['prompt' => ['input', 'Question']], 'values' => ['prompt' => $question->prompt ?? []]])
    <div class="space-y-2">
        @foreach ($question->options as $k => $option)
            <div class="flex flex-wrap items-center gap-2">
                <label class="flex w-24 items-center gap-1 text-sm"><input type="radio" name="correct_option" value="{{ $k }}" @checked($question->correct_option === $k)> Correct</label>
                @foreach (array_keys(config('app.supported_locales')) as $code)
                    <input name="options[{{ $k }}][{{ $code }}]" value="{{ $option[$code] ?? '' }}" placeholder="Option {{ $k + 1 }} ({{ strtoupper($code) }})" class="input min-w-40 flex-1" @required($code === 'en')>
                @endforeach
            </div>
        @endforeach
    </div>
    <button class="btn-primary">{{ $method === 'POST' ? 'Add question' : 'Save question' }}</button>
</form>
