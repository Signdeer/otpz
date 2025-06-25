<?php

namespace BenBjurstrom\Otpz\Models\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

interface Otpable extends Authenticatable, MustVerifyEmail
{
    /**
     * @return HasMany<Model>
     */
    public function otps(): HasMany;

    /**
     * Send the OTP notification to the user.
     */
    public function notify(mixed $instance): void;
}
