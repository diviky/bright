@extends('layouts.single')

@section('content')

<div class="card card-small">
    <div class="card-body p-6">
    <div class="card-title">{{ __('Account Disabled') }}</div>
        <p class="text-muted">Your account has been disabled. Please contact support team to reactivate.</p>
    </div>
</div>
@endsection
