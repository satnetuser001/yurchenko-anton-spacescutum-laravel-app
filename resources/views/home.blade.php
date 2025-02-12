@extends('layout')

@section('title', 'Home')

@section('content')
    <h1>Welcome</h1>

    @if (Auth::check())
        <p>You are logged in as {{ Auth::user()->name }}.</p>
        <a href="{{ route('profile') }}">Go to Profile</a>
    @else
        <h2>Login</h2>
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <label>Email: <input type="email" name="email" required></label><br>
            <label>Password: <input type="password" name="password" required></label><br>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="{{ route('register') }}">Register</a></p>
    @endif
@endsection
