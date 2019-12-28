@component('mail::message')

Hi {{ $user->name }}, <br/>
Greetings for the day!

This is a confirmation, that your password has been successfully changed and your account is secure again.
Kindly use your new password to login the next time.

Didn't change your password? Contact our Support so we can make sure no one else is trying to access your account.

@endcomponent
