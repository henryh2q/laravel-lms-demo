@extends('layouts.admin')
@section('content')
<h1 class="mb-6 text-2xl font-bold">Dashboard</h1>
<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
    @foreach ([
        ['Courses', $stats['courses']], ['Students', $stats['students']], ['Enrollments', $stats['enrollments']],
        ['Completion rate', $stats['completion'].'%'], ['Avg quiz score', $stats['avg_score'].'%'],
    ] as [$label, $value])
        <div class="card p-4"><div class="text-sm text-slate-500">{{ $label }}</div><div class="mt-1 text-2xl font-bold">{{ $value }}</div></div>
    @endforeach
</div>
<div class="grid gap-6 xl:grid-cols-3">
    <div class="card overflow-x-auto xl:col-span-2">
        <div class="border-b border-slate-200 p-4 font-semibold">Courses</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="p-3">Course</th><th class="p-3 text-right">Lessons</th><th class="p-3 text-right">Enrolled</th><th class="p-3 text-right">Completed</th><th class="p-3">Avg progress</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($courses as $course)
                    <tr>
                        <td class="p-3"><a href="{{ route('admin.courses.edit', $course) }}" class="font-medium text-brand-600 hover:underline">{{ $course->translate('title', 'en') }}</a>@unless ($course->is_published)<span class="badge ml-2 bg-amber-100 text-amber-800">draft</span>@endunless</td>
                        <td class="p-3 text-right">{{ $course->lessons_count }}</td>
                        <td class="p-3 text-right"><a href="{{ route('admin.courses.students', $course) }}" class="hover:underline">{{ $course->enrollments_count }}</a></td>
                        <td class="p-3 text-right">{{ $course->completed_count }}</td>
                        <td class="w-40 p-3"><div class="flex items-center gap-2"><x-progress :value="(int) $course->avg_progress" :color="$course->color" /><span class="text-xs">{{ (int) $course->avg_progress }}%</span></div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card">
        <div class="border-b border-slate-200 p-4 font-semibold">Latest quiz attempts</div>
        <ul class="divide-y divide-slate-100 text-sm">
            @forelse ($recentAttempts as $a)
                <li class="flex items-center justify-between gap-2 p-3"><span>{{ $a->user->name }}<span class="block text-xs text-slate-500">{{ $a->course->translate('title', 'en') }}</span></span><span @class(['badge', 'bg-emerald-100 text-emerald-800' => $a->passed, 'bg-red-100 text-red-700' => ! $a->passed])>{{ $a->percent() }}%</span></li>
            @empty
                <li class="p-4 text-slate-500">No attempts yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
