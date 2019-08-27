@extends('layouts.single')

@section('content')

<div class="card card-small">
    <div class="card-body p-6 form-float">
        <div class="card-title">Login to your account</div>

        <form method="POST" action="{{ route('login') }}" role="ksubmit">
            @csrf

            <div class="form-group">
                <label for="username" class="form-label">{{ __('Email Address') }}</label>
                <input id="username" name="email" type="email" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}"
                    tabindex="1" value="{{ old('username') }}" placeholder="Email Address" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                    name="password" tabindex="2" required placeholder="Password">
                <a href="{{ route('password.reset') }}" class="float-right pt-1 small">I forgot password</a>
            </div>

            <div class="form-footer">
                <button type="submit" tabindex="3" class="btn btn-primary btn-bold btn-block">
                    {{ __('Login') }}
                </button>
            </div>
        </form>

    </div>
</div>
<div class="text-center text-muted">
    Don't have account yet? <a href="{{ route('register') }}">Sign up</a>
</div>
@endsection
