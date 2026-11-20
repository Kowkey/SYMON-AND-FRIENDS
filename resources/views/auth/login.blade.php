@extends('layouts.app')

@section('title', 'Sign In Account')

@section('content')
<div class="auth-bg">
    <!-- Abstract geometric shapes -->
    <div class="shape-1"></div>
    <div class="shape-2"></div>
    <div class="shape-3"></div>
    <div class="shape-4"></div>
    <div class="shape-5"></div>

    <div class="auth-card">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert-box alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-box alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <h1>Sign In Account</h1>

        <form method="POST" action="{{ route('login') }}" data-prevent-duplicate>
            @csrf

            <div class="form-group">
                <label class="center-label" for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label class="center-label" for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember Account.</label>
            </div>

            <button type="submit" class="btn btn-blue">LOG IN</button>
        </form>

        <div class="auth-footer">
            No Account Yet?<a href="{{ route('register') }}">REGISTER HERE!</a>
        </div>
    </div>
</div>
@endsection
