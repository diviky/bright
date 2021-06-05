@extends('layouts.single')

@section('content')

<div class="card card-small">
    <div class="card-body p-6 form-float">
        <div class="card-title">Login to your account</div>
         @include('bright::auth.partials.social-signin')

        <form method="POST" action="{{ route('login') }}" role="ksubmit">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                <input id="email" name="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                    tabindex="1" value="{{ old('email') }}" placeholder="Email Address" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input id="password" autocomplete="current-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                    name="password" tabindex="2" required placeholder="Password">
                <a href="{{ url('password/reset') }}" class="float-right pt-1 small">I forgot password</a>
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
