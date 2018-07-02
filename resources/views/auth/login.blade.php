@extends('layouts.single')

@section('content')

<div class="card card-small">
    <div class="card-body p-6">
        <div class="card-title">Login to your account</div>

        <form method="POST" action="{{ route('login') }}" role="ksubmit">
            @csrf

            <div class="form-group">
                <label for="username" class="form-label">{{ __('Username') }}</label>
                <input id="username" name="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" tabindex="1" value="{{ old('username') }}" placeholder="Username" required>
                <span class="invalid-feedback">{{ $errors->first('username') }}</span>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('Password') }}
                    <a href="{{ route('password.reset') }}" class="float-right small" data-toggle="tooltip" title="We will send Action code to your registerd mobile number">I forgot password</a>
                </label>
                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" tabindex="2" required placeholder="Password">
                <span class="invalid-feedback">{{ $errors->first('password') }}</span>
            </div>

            <div class="form-footer">
                <button type="submit" tabindex="3" class="btn btn-primary btn-block">
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
