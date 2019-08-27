@extends('layouts.single')

@section('content')

<div class="card card-small">
    <div class="card-body p-6 form-float">
        <div class="card-title">{{ __('Activate Your Account') }}</div>

        @if (session('status'))
        <div class="alert alert-{{ session('status') }}">
            {{ session('message') }}
        </div>
        @endif
        <p class="text-muted">Enter the code sent to your email address.</p>

        <form method="POST" action="{{ route('user.activate') }}" role="ksubmit">
            @csrf

            <div class="form-group">
                <input name="token" id="token" type="text" class="form-control" value="" required>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary btn-bold btn-block">
                    {{ __('Verify My Account') }}
                </button>
            </div>
        </form>
    </div>
</div>
<div class="text-center text-muted">
    Did not receive the code?, <a data-ajax href="{{ route('activate.resend') }}">resent it</a>.
</div>
@endsection
