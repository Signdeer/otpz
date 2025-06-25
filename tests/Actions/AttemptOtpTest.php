<?php

use BenBjurstrom\Otpz\Actions\AttemptOtp;
use BenBjurstrom\Otpz\Enums\OtpStatus;
use BenBjurstrom\Otpz\Exceptions\OtpAttemptException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Fake session
    Session::shouldReceive('getId')->andReturn('test-session-id');

    // Fake request signature validation
    Request::macro('hasValidSignature', fn () => true);
    Request::macro('signatureHasNotExpired', fn () => true);
});

it('successfully validates and marks otp as used', function () {
    $code = 'SECRET123';
    $user = config('otpz.user_model')::factory()->create();

    $otpModel = config('otpz.model');
    $otp = $otpModel::factory()->for($user)->create([
        'code' => Hash::make($code),
    ]);

    $attemptedOtp = (new AttemptOtp)->handle($otp->id, $code, 'test-session-id');

    expect($attemptedOtp->refresh())
        ->status->toBe(OtpStatus::USED)
        ->id->toBe($otp->id);
});

it('throws exception for invalid signature', function () {
    Request::macro('hasValidSignature', fn () => false);
    $model = config('otpz.model');
    $otp = $model::factory()->create();

    expect(fn () => (new AttemptOtp)->handle($otp->id, 'CODE', 'test-session-id'))
        ->toThrow(OtpAttemptException::class, OtpStatus::SIGNATURE->errorMessage());
});

it('throws exception for expired signature', function () {
    Request::macro('hasValidSignature', fn () => false);
    Request::macro('signatureHasNotExpired', fn () => false);

    $otp = config('otpz.model')::factory()->create();

    expect(fn () => (new AttemptOtp)->handle($otp->id, 'CODE', 'test-session-id'))
        ->toThrow(OtpAttemptException::class, OtpStatus::SIGNATURE->errorMessage());
});

it('throws exception for non-active otp', function () {
    $otp = config('otpz.model')::factory()->used()->create();

    expect(fn () => (new AttemptOtp)->handle($otp->id, 'CODE', 'test-session-id'))
        ->toThrow(OtpAttemptException::class, OtpStatus::USED->errorMessage());
});

it('throws exception for expired otp', function () {
    $otp = config('otpz.model')::factory()->expired()->create();

    expect(fn () => (new AttemptOtp)->handle($otp->id, 'CODE', 'test-session-id'))
        ->toThrow(OtpAttemptException::class, OtpStatus::EXPIRED->errorMessage());

    expect($otp->refresh()->status)->toBe(OtpStatus::EXPIRED);
});

it('throws exception for invalid code', function () {
    $otp = config('otpz.model')::factory()->create([
        'code' => Hash::make('ACTUAL_CODE'),
    ]);

    expect(fn () => (new AttemptOtp)->handle($otp->id, 'WRONG_CODE', 'test-session-id'))
        ->toThrow(OtpAttemptException::class, OtpStatus::INVALID->errorMessage());
});

it('throws exception for missing OTP model', function () {
    expect(fn () => (new AttemptOtp)->handle(999999, 'CODE', 'test-session-id'))
        ->toThrow(ModelNotFoundException::class);
});

it('allows attempt within 5 minute window', function () {
    $code = 'WITHIN_TIME';
    $user = config('otpz.user_model')::factory()->create();

    $otp = config('otpz.model')::factory()
        ->for($user)
        ->state(['created_at' => now()->subMinutes(4)])
        ->create([
            'code' => Hash::make($code),
        ]);

    $attempted = (new AttemptOtp)->handle($otp->id, $code, 'test-session-id');

    expect($attempted->refresh()->status)->toBe(OtpStatus::USED);
});
