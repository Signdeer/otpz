<?php

namespace BenBjurstrom\Otpz\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use BenBjurstrom\Otpz\Enums\OtpStatus;

class GetOtpController {
    public function __invoke(Request $request, string $id): View|RedirectResponse {
        if (! $request->hasValidSignature()) {
            Session::flash('status', __(OtpStatus::SIGNATURE->errorMessage()));
            return redirect()->route('login');
        }

        if ($request->sessionId !== $request->session()->getId()) {
            Session::flash('status', __(OtpStatus::SESSION->errorMessage()));
            return redirect()->route('login');
        }

        $otpModel = Otpz::otpModel();
        // $otpModel = config('otpz.model', \BenBjurstrom\Otpz\Models\Otp::class);
        $otp = $otpModel::findOrFail($id);

        $url = URL::temporarySignedRoute(
            'otpz.post',
            now()->addMinutes(5),
            [
                'id' => $otp->id,
                'sessionId' => $request->session()->getId(),
            ]
        );

        return view('otpz::otp', [
            'email' => $otp->user->email ?? 'unknown',
            'url' => $url,
            'code' => $request->code,
        ]);
    }
}
