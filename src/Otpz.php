<?php

namespace BenBjurstrom\Otpz;

use BenBjurstrom\Otpz\Models\Otp;

class Otpz {

    protected static string $otpModel = Otp::class;

    public static function useOtpModel(string $modelClass): void {
        static::$otpModel = $modelClass;
    }

    public static function otpModel(): string {
        return static::$otpModel;
    }

    public static function newOtpInstance(): Otp {
        $class = static::$otpModel;
        return new $class;
    }
    
}
