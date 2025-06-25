<?php

namespace BenBjurstrom\Otpz\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use  BenBjurstrom\Otpz\Models\Otp;
trait HasOtps
{
    /**
     * @return HasMany<\Illuminate\Database\Eloquent\Model>
     */
    public function otps(): HasMany
    {
        $otpModel = config('otpz.model.otp', \BenBjurstrom\Otpz\Models\Otp::class);

        return $this->hasMany($otpModel);
    }
}
