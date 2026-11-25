@extends('layouts.admin')
@section('title', 'Courses')
@section('content')
<div class="mb-4 flex items-center"><h1 class="text-2xl font-bold">Courses</h1><a href="{{ route('admin.courses.create') }}" class="btn-primary ml-auto">New course</a></div>
<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="p-3">Course</th><th class="p-3">Instructor</th><th class="p-3">Level</th><th class="p-3 text-right">Lessons</th><th class="p-3 text-right">Questions</th><th class="p-3 text-right">Students</th><th class="p-3">Status</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($courses as $course)
                <tr class="hover:bg-slate-50">
                    <td class="p-3"><a href="{{ route('admin.courses.edit', $course) }}" class="font-medium text-brand-600 hover:underline">{{ $course->translate('title', 'en') }}</a><div class="text-xs text-slate-500">{{ $course->translate('title', 'ja') }}</div></td>
                    <td class="p-3">{{ $course->instructor->name }}</td>
                    <td class="p-3">{{ $course->level }}</td>
                    <td class="p-3 text-right">{{ $course->lessons_count }}</td>
                    <td class="p-3 text-right">{{ $course->questions_count }}</td>
                    <td class="p-3 text-right"><a href="{{ route('admin.courses.students', $course) }}" class="hover:underline">{{ $course->enrollments_count }}</a></td>
                    <td class="p-3">{!! $course->is_published ? '<span class="badge bg-emerald-100 text-emerald-800">published</span>' : '<span class="badge bg-amber-100 text-amber-800">draft</span>' !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $courses->links() }}</div>
@endsection
