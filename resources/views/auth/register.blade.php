@extends('layouts.single') 
@section('content')

<div class="card card-small">

    <div class="card-body p-6">
        <div class="card-title">{{ __('Create new account') }}</div>

        <form method="POST" action="{{ route('register') }}" role="easySubmit">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">{{ __('First and last name') }}</label>

                <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}"
                    required placeholder="Your first and last name" />
                <span class="invalid-feedback">{{ $errors->first('name') }}</span>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">{{ __('Mobile Number') }}</label>

                <div class="input-group">
                <span class="input-group-prepend">
                    <span class="input-group-text">+91</span>
                </span>
                <input  name="mobile" id="mobie" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required
                    value="{{ old('mobile') }}" placeholder="Will be your username" />
                </div>
                <span class="invalid-feedback">{{ $errors->first('email') }}</span>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('Password') }}</label>

                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password"
                    required placeholder="Strong password"/> @if ($errors->has('password'))
                <span class="invalid-feedback">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
                @endif
            </div>

            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary btn-block">
                    Let's get started
                </button>
            </div>
        </form>
    </div>
</div>
<div class="text-center text-muted">
    By creating an account, you agreed to the <a href="#">Terms of services & Privacy Policy</a>
</div>

<div class="text-center text-muted">
    Already have account?
    <a href="{{ route('login') }}">Sign in</a>
</div>
@endsection