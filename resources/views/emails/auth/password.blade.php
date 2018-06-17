@component('mail::message')
<p>We heard that you lost your {{ config('app.name') }} password. Sorry about that!</p>

<p>But don’t worry! You can use the following token to reset your password:</p>

<strong>{{ $token }}</strong>

<p>If you don’t use this token within 3 hours, it will expire.</p>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
