@extends('layouts.app')

@section('title', 'Create New Account')

@section('content')
<div class="auth-bg">
    <!-- Abstract geometric shapes -->
    <div class="shape-1"></div>
    <div class="shape-2"></div>
    <div class="shape-3"></div>
    <div class="shape-4"></div>
    <div class="shape-5"></div>

    <div class="auth-card wide register">

        {{-- Flash Messages --}}
        @if ($errors->any())
            <div class="alert-box alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <h1>Create New Account</h1>

        <form method="POST" action="{{ route('register') }}" data-prevent-duplicate>
            @csrf

            <div class="form-group">
                <label for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                >
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="show-btn">SHOW</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-input"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="show-btn">SHOW</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input
                    type="text"
                    id="phone_number"
                    name="phone_number"
                    class="form-input"
                    value="{{ old('phone_number') }}"
                >
            </div>

            <div class="form-group">
                <label for="bio">Short Bio</label>
                <input
                    type="text"
                    id="bio"
                    name="bio"
                    class="form-input"
                    value="{{ old('bio') }}"
                >
            </div>

            <button type="submit" class="btn btn-light">REGISTER ACCOUNT</button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="{{ route('login') }}">SIGN IN HERE!</a>
        </div>
    </div>
</div>
@endsection
