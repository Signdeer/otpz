<?php

namespace BenBjurstrom\Otpz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use BenBjurstrom\Otpz\Enums\OtpStatus;

class OtpFactory extends Factory
{
    /**
     * Dynamically resolve the OTP model from config.
     */
    public function __construct(...$args)
    {
        parent::__construct(...$args);

        // Dynamically bind the model from config
        $this->model = config('otpz.model', \BenBjurstrom\Otpz\Models\Otp::class);
    }

    public function definition(): array
    {
        $code = Str::upper(Str::random(9));

        return [
            'user_id' => \BenBjurstrom\Otpz\Tests\Support\Models\User::factory(),
            'status' => OtpStatus::ACTIVE,
            'code' => $code,
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OtpStatus::EXPIRED,
            'created_at' => now()->subMinutes(6),
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OtpStatus::USED,
        ]);
    }

    public function superseded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OtpStatus::SUPERSEDED,
        ]);
    }
}
