@component('mail::message')
We heard that you lost your {{ config('app.name') }} password. Sorry about that!

But don’t worry! You can use the following token to reset your password:

<strong>Username: {{ $notifiable->username }}</strong>
<strong>Token: {{ $token }}</strong>

If you don’t use this token within 3 hours, it will expire.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
