<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Expiration and Throttling
    |--------------------------------------------------------------------------
    |
    | Control how long OTPs are valid, and define throttling rules
    | to protect against abuse (rate-limiting).
    |
    */

    'expiration' => 5, // OTP expires after 5 minutes

    'limits' => [
        ['limit' => 1, 'minutes' => 1],   // Max 1 OTP per minute
        ['limit' => 3, 'minutes' => 5],   // Max 3 OTPs per 5 minutes
        ['limit' => 5, 'minutes' => 30],  // Max 5 OTPs per 30 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | Define your user model and your custom OTP model here.
    | The "authenticatable" model should implement Otpable.
    |
    */

    'models' => [
        'authenticatable' => App\Models\User::class,
        'otp' => App\Models\Otp::class, // <-- your custom OTP model
    ],


    'character_limit' => 10,

    /*
    |--------------------------------------------------------------------------
    | Mailable Configuration
    |--------------------------------------------------------------------------
    |
    | You can override the email that sends the OTP code to users.
    | You can use the built-in OtpzMail or define your own class.
    |
    */

    'mailable' => BenBjurstrom\Otpz\Mail\OtpzMail::class,

    /*
    |--------------------------------------------------------------------------
    | Template Configuration
    |--------------------------------------------------------------------------
    |
    | View used for the OTP email. Override this to use your own Markdown
    | template if needed.
    |
    */

    'template' => 'otpz::mail.otpz', // customize with Blade if needed
    // 'template' => 'otpz::mail.notification', // for Laravel-style notifications

    /*
    |--------------------------------------------------------------------------
    | User Resolver
    |--------------------------------------------------------------------------
    |
    | This class resolves a user by email during OTP creation.
    | You may provide your own to fetch by phone or tenant context.
    |
    */

    'user_resolver' => BenBjurstrom\Otpz\Actions\GetUserFromEmail::class
    // App\Actions\ResolveUserForOtp::class, // or fallback:

];
