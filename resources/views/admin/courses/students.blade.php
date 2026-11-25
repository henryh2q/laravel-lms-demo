@extends('layouts.admin')
@section('title', 'Students')
@section('content')
<a href="{{ route('admin.courses.edit', $course) }}" class="text-sm text-slate-500 hover:underline">← {{ $course->translate('title', 'en') }}</a>
<h1 class="mb-4 mt-2 text-2xl font-bold">Students</h1>
<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="p-3">Student</th><th class="p-3">Progress</th><th class="p-3">Enrolled</th><th class="p-3">Completed</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($enrollments as $e)
                <tr>
                    <td class="p-3">{{ $e->user->name }}<div class="text-xs text-slate-500">{{ $e->user->email }}</div></td>
                    <td class="w-56 p-3"><div class="flex items-center gap-2"><x-progress :value="$e->progress" :color="$course->color" /><span class="text-xs">{{ $e->progress }}%</span></div></td>
                    <td class="p-3 text-slate-500">{{ $e->created_at->toDateString() }}</td>
                    <td class="p-3">{{ $e->completed_at?->toDateString() ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-4 text-slate-500">No students yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $enrollments->links() }}</div>
@endsection
