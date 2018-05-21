@extends('layouts.single')

@section('content')

<div class="card card-small">
    <div class="card-body p-6">
    <div class="card-title">{{ __('Reset Password') }}</div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        <p class="text-muted">Enter your username and your password reset token will be messaged to you.</p>

        <form method="POST" action="{{ route('password.reset') }}" role="easySubmit">
            @csrf

            <div class="form-group">
                <label for="username" class="form-label">{{ __('Username') }}</label>

                <input name="username" id="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" value="{{ old('username') }}" required>

                @if ($errors->has('username'))
                    <span class="invalid-feedback">
                        <strong>{{ $errors->first('username') }}</strong>
                    </span>
                @endif
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary btn-block">
                    {{ __('Send Password Reset Token') }}
                </button>
            </div>
        </form>
    </div>
</div>
<div class="text-center text-muted">
	Forget it, <a href="{{ route('login') }}">send me back</a> to the login.
</div>
@endsection
