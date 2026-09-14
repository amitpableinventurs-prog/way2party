@extends('scanner.layout')
@section('title', 'Scanner Login')
@section('body')
    <div class="container" style="padding-top: 60px;">
        <div style="text-align:center; margin-bottom: 24px;">
            <h1 style="font-size: 22px; margin-bottom: 4px;">{{ __('Scanner Login') }}</h1>
            <p class="muted">{{ __('Sign in to check in tickets at your assigned events.') }}</p>
        </div>

        @if (session('error_msg'))
            <div class="flash error">{{ session('error_msg') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash error">{{ $errors->first() }}</div>
        @endif

        <div class="card">
            <form method="post" action="{{ route('scannerPanel.login.post') }}">
                @csrf
                <label for="email">{{ __('Email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>

                <label for="password">{{ __('Password') }}</label>
                <input type="password" name="password" id="password" required>

                <button type="submit" class="btn">{{ __('Login') }}</button>
            </form>
        </div>
    </div>
@endsection
