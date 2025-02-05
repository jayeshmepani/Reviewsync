@component('mail::message')
# Password Reset Request

You are receiving this email because we received a password reset request for your account.

Click the button below to reset your password:

@component('mail::button', ['url' => url(config('app.url').route('password.reset', ['token' => $token, 'email' => $email], false))])
Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent