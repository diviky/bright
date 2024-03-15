@extends('layouts.single')

@section('content')

<div class="card card-small">
    <div class="card-body p-6 form-float">
    <div class="card-title">{{ __('Reset Password') }}</div>

        <x-bright::flash />
        <p class="text-muted">Enter your username and your password reset token will be emailed to you.</p>

        <form method="POST" action="{{ url('password/reset') }}" role="ksubmit">
            @csrf

            <div class="form-group">
                <label for="username" class="form-label">{{ __('Username') }}</label>

                <input name="username" id="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" value="{{ old('username') }}" required>
                <span class="invalid-feedback">{{ $errors->first('username') }}</span>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-bold btn-primary btn-block">
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
