@if (session('status'))
    <div class="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif
