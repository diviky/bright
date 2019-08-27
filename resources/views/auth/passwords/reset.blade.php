@extends('layouts.single') @section('content')

<div class="card card-small">

    <div class="card-body form-float">
        <div class="card-title">{{ __('Reset Password') }}</div>

        <form method="POST" action="{{ route('password.request') }}" role="ksubmit">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>

                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}"
                    required autofocus> @if ($errors->has('email'))
                <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('Password') }}</label>

                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password"
                    required> @if ($errors->has('password'))
                <span class="invalid-feedback">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>

                <input id="password-confirm" type="password" class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                    name="password_confirmation" required> @if ($errors->has('password_confirmation'))
                <span class="invalid-feedback">{{ $errors->first('password_confirmation') }}</span>
                @endif
            </div>

            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary btn-bold btn-block">
                    {{ __('Reset Password') }}
                </button>
            </div>
        </form>
    </div>

</div>

<div class="text-center text-muted">
    Forget it,
    <a href="{{ route('login') }}">send me back</a> to the login.
</div>

@endsection