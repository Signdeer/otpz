<?php

namespace BenBjurstrom\Otpz\Actions;

use Illuminate\Support\Facades\Mail;
use BenBjurstrom\Otpz\Exceptions\OtpThrottleException;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use CraigPaul\Mail\PostmarkTransportException;

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

        // Generate the OTP (and code)
        [$otp, $code] = (new $createOtpAction)->handle($user, $remember);

        // Send the OTP via mail
        try {
            Mail::to($user)->send(new $mailableClass($otp, $code));
        } catch (Throwable $e) {
            Log::error('Email send failure', [
                'user' => $user,
                'error' => $e->getMessage(),
            ]);

            $friendlyMessage = 'We couldn’t send the email. Please try again.';

            if ($e instanceof PostmarkTransportException) {
                $friendlyMessage = 'Unable to send email — Postmark is still in review or misconfigured.';
            }

            return back()->withErrors([
                'email' => $friendlyMessage,
            ]);
        }


        return $otp;
    }
}
