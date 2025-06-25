<?php

namespace BenBjurstrom\Otpz\Actions;

use Illuminate\Support\Facades\Mail;
use BenBjurstrom\Otpz\Exceptions\OtpThrottleException;

class SendOtp {
    /**
     * @throws OtpThrottleException
     */
    public function handle(string $email, bool $remember = false): \Illuminate\Database\Eloquent\Model
    {
        $mailableClass = config('otpz.mailable', \BenBjurstrom\Otpz\Mail\OtpzMail::class);
        $userResolverClass = config('otpz.user_resolver', \BenBjurstrom\Otpz\Actions\GetUserFromEmail::class);
        $createOtpAction = config('otpz.create_otp_action', \BenBjurstrom\Otpz\Actions\CreateOtp::class);

        // Resolve the user
        $user = (new $userResolverClass)->handle($email);
        dd($user);

        // Generate the OTP (and code)
        [$otp, $code] = (new $createOtpAction)->handle($user, $remember);

        // Send the OTP via mail
        Mail::to($user)->send(new $mailableClass($otp, $code));

        return $otp;
    }
}
