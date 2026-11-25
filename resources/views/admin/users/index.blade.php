@extends('layouts.admin')
@section('title', 'Users')
@section('content')
<h1 class="mb-4 text-2xl font-bold">Users</h1>
<form class="mb-4 flex flex-wrap gap-2">
    <input name="q" value="{{ request('q') }}" placeholder="Name or email" class="input max-w-xs">
    <select name="role" class="input max-w-[10rem]"><option value="">All roles</option>@foreach (\App\Models\User::ROLES as $r)<option @selected(request('role') === $r)>{{ $r }}</option>@endforeach</select>
    <button class="btn-ghost">Filter</button>
</form>
<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="p-3">User</th><th class="p-3 text-right">Enrollments</th><th class="p-3">Role</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($users as $user)
                <tr>
                    <td class="p-3">{{ $user->name }}<div class="text-xs text-slate-500">{{ $user->email }}</div></td>
                    <td class="p-3 text-right">{{ $user->enrollments_count }}</td>
                    <td class="p-3">
                        @if (str_ends_with($user->email, '@demo.test') || $user->is(auth()->user()))
                            <span class="badge bg-slate-100 text-slate-700">{{ $user->role }}</span>
                        @else
                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex gap-2">@csrf @method('PATCH')
                                <select name="role" class="input w-36 py-1">@foreach (\App\Models\User::ROLES as $r)<option @selected($user->role === $r)>{{ $r }}</option>@endforeach</select>
                                <button class="btn-ghost py-1">Save</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
