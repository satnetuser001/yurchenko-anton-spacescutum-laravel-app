@extends('layout')

@section('title', 'Profile')

@section('content')
    <h1>Profile</h1>
    <p>Name: {{ Auth::user()->name }}</p>
    <p>Email: {{ Auth::user()->email }}</p>

    @if(session('token'))
        <div class="alert alert-info">
            Ваш API-токен: <strong>{{ session('token') }}</strong>
        </div>
    @endif
    <form action="{{ route('regenerate-token') }}" method="POST">
        @csrf
        <button type="submit">Regenerate API Token</button>
    </form>
@endsection
