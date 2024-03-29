@component('mail::message')
We heard that you lost your {{ config('app.name') }} password. Sorry about that!

But don’t worry! You can use the following token to reset your password:

**Verification Code: {{ $token }}**

If you don’t use this token within 15 minutes, it will expire.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
