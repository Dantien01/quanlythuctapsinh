{{-- resources/views/student/tasks/progress/edit.blade.php --}}
@extends('layouts.admin')

@section('title', __('Chỉnh sửa Cập nhật Tiến độ cho: ') . $task->title)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>

    @include('partials.alerts')

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Chi tiết Cập nhật') }}</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('student.tasks.progress.update', ['task' => $task, 'taskProgress' => $taskProgress]) }}">
                @method('PUT')
                @include('student.tasks.progress._form')
            </form>
        </div>
    </div>
</div>
@endsection