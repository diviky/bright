@component('mail::message')
<p>Hi  {{ $notifiable->name }},</p>
<p>Thanks for registering!</p>

<p>We would like to verify your email address.</p>
<p>Please use the token below for activation.</p>

<p><strong>{{ $token }}</strong></p>

If you don’t use this token within 15 minutes, it will expire.

<p>Please do not reply to this mail as this is an auto-generated email.</p>
@endcomponent
