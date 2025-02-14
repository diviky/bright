@extends('layouts.single')

@section('content')
    <div class="card card-small">
        <div class="card-body p-6 form-float">
            <div class="card-title">{{ __('Activate Your Account') }}</div>
            <x-bright::flash />
            <p class="text-muted">Enter the code sent to your email address.</p>

            <form method="POST" action="{{ url('activate') }}" easysubmit>
                @csrf

                <div class="form-group">
                    <label class="form-label">Code</label>
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
        Did not receive the code? <a href="#" data-post="{{ url('resend') }}">resent it</a>.
    </div>
@endsection
