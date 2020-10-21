@extends('layouts.single')
@section('content')

<div class="card card-small">
    <div class="card-body p-6 form-float">
        <div class="card-title">{{ __('Create an Account') }}</div>
            @include('bright::auth.partials.social-signup')

        <form method="POST" action="{{ route('register') }}" role="ksubmit">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">{{ __('Your Full Name') }}</label>
                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required
                    placeholder="Your first and last name" />
                <span class="invalid-feedback">{{ $errors->first('name') }}</span>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                <input name="email" id="email" type="text" class="form-control" required value="{{ app('request')->input('email') }}"
                    placeholder="Will be your login" />
                <span class="invalid-feedback">{{ $errors->first('email') }}</span>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input id="password" type="password" class="form-control" name="password" required placeholder="Strong password" />
                <span class="invalid-feedback">{{ $errors->first('password') }}</span>
            </div>

            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary btn-block">
                    Let's get Started
                </button>
            </div>
        </form>
    </div>
</div>

<div class="text-center text-muted">
    Already have account?
    <a href="{{ route('login') }}">Sign in</a>
</div>
@endsection
