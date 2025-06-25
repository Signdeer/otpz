<?php

namespace BenBjurstrom\Otpz\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use  BenBjurstrom\Otpz\Otpz;
trait HasOtps
{
    /**
     * @return HasMany<\Illuminate\Database\Eloquent\Model>
     */
    public function otps(): HasMany
    {
        $otpModel = Otpz::otpModel();
        return $this->hasMany($otpModel);
    }
}
