@extends('layouts.single') @section('content')
    <div class="card card-small">

        <div class="card-body form-float">
            <div class="card-title">{{ __('Change Your Password') }}</div>

            <form method="POST" action="{{ url('password/change') }}" easysubmit>
                @csrf
                <div class="form-group">
                    <label for="password" class="form-label">{{ __('New Password') }}</label>

                    <input id="password" autocomplete="new-password" type="password"
                        class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                    @if ($errors->has('password'))
                        <span class="invalid-feedback">{{ $errors->first('password') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>

                    <input id="password-confirm" autocomplete="new-password" type="password"
                        class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                        name="password_confirmation" required>
                    @if ($errors->has('password_confirmation'))
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
