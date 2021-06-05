@extends('layouts.single')

@section('content')

<div class="card card-small">
    <div class="card-body p-6 form-float">
    <div class="card-title">{{ __('Verify Your Account') }}</div>
        <x-bright::flash />
        <p class="text-muted">Enter the code sent to your email address.</p>

        <form method="POST" action="{{ url('password/verify') }}" role="ksubmit">
            @csrf

            <div class="form-group">
            <label class="form-label">Verification Code</label>
                <input name="token" id="token" type="text" class="form-control" value="" required>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary btn-block">
                    {{ __('Verify My Account') }}
                </button>
            </div>
        </form>
    </div>
</div>
<div class="text-center text-muted">
	Did not receive the code? <a href="#" data-href="{{ url('password/resend') }}">resent it</a>.
</div>
@endsection
