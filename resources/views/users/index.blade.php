@extends('layouts.default')
@section('title', '所有用户')

@section('content')
<div class="offset-md-2 col-md-8">
  <h2 class="mb-4 text-center">所有用户</h2>
  <div class="list-group list-group-flush">
    @foreach ($users as $user)
      <div class="list-group-item">
        <div class="row align-items-center">
          <div class="col-md-2">
            <img src="{{ $user->gravatar() }}" alt="{{ $user->name }}" class="img-thumbnail rounded-circle">
          </div>
          <div class="col-md-7">
            <h5 class="mb-1">
              <a href="{{ route('users.show', $user) }}">
                {{ $user->name }}
              </a>
            </h5>
            <p class="text-muted mb-0">{{ $user->email }}</p>
          </div>
          <div class="col-md-3 text-right">
            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-primary">
              查看
            </a>
            @can('update', $user)
              <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-secondary">
                编辑
              </a>
            @endcan
            @can('destroy', $user)
              <form action="{{ route('users.destroy', $user->id) }}" method="post" class="d-inline">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <button type="submit" class="btn btn-sm btn-danger delete-btn">
                  删除
                </button>
              </form>
            @endcan
          </div>
        </div>
      </div>
    @endforeach
  </div>
  <div class="mt-3">
    {!! $users->render() !!}
  </div>
</div>
@stop

