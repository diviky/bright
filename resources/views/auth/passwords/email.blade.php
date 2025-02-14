@extends('layouts.single')

@section('content')
    <div class="card card-small">
        <div class="card-body p-6 form-float">
            <div class="card-title">{{ __('Reset Password') }}</div>
            <x-bright::flash />
            <p class="text-muted">Enter your email address and your password reset link will be mailed to you.</p>

            <form method="POST" action="{{ route('password.email') }}" easysubmit>
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>

                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                        name="email" value="{{ old('email') }}" required>

                    @if ($errors->has('email'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary btn-bold btn-block">
                        {{ __('Send Password Reset Link') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center text-muted">
        Forget it, <a href="{{ route('login') }}">send me back</a> to the login.
    </div>
@endsection
