<?php

namespace BenBjurstrom\Otpz\Actions;

use BenBjurstrom\Otpz\Enums\OtpStatus;
use BenBjurstrom\Otpz\Exceptions\OtpThrottleException;
use BenBjurstrom\Otpz\Models\Concerns\Otpable;
use BenBjurstrom\Otpz\Otpz;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Model run(Otpable $user)
 */
class CreateOtp
{
    /**
     * @return list<Model|string>
     *
     * @throws OtpThrottleException
     */
    public function handle(Otpable $user, bool $remember = false): array
    {
        $this->throttle($user);

        return $this->createOtp($user, $remember);
    }

    /**
     * @throws OtpThrottleException
     */
    public function throttle(Otpable $user): void
    {
        foreach ($this->getThresholds() as $threshold) {
            $count = $this->getOtpCount($user, $threshold['minutes']);

            if ($count >= $threshold['limit']) {
                $remaining = $this->calculateRemainingTime($user, $threshold['minutes']);
                throw new OtpThrottleException($remaining['minutes'], $remaining['seconds']);
            }
        }
    }

    private function getThresholds(): array
    {
        return config('otpz.limits', [
            ['limit' => 1, 'minutes' => 1],
            ['limit' => 3, 'minutes' => 5],
            ['limit' => 5, 'minutes' => 30],
        ]);
    }

    private function getOtpCount(Otpable $user, int $minutes): int
    {
        return $user->otps()
            ->where('status', '!=', OtpStatus::USED)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    private function calculateRemainingTime(Otpable $user, int $minutes): array
    {
        $earliestOtp = $user->otps()
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->orderBy('created_at', 'asc')
            ->first();

        if ($earliestOtp) {
            $availableAt = $earliestOtp->created_at->addMinutes($minutes);
            $remainingSeconds = now()->diffInSeconds($availableAt, false);

            return [
                'minutes' => floor($remainingSeconds / 60),
                'seconds' => $remainingSeconds % 60,
            ];
        }

        return ['minutes' => 0, 'seconds' => 0];
    }

    /**
     * @return list<Model|string>
     */
    private function createOtp(Otpable $user, bool $remember): array
    {
        $otpModel = Otpz::otpModel();

        return DB::transaction(function () use ($user, $otpModel, $remember) {
            $characters = config('otpz.characters') ?? 10;
            $code = Str::upper(Str::random($characters));
            $code = str_replace('O', '0', $code); // Make more readable

            // Supersede any existing active OTPs
            $user->otps()
                ->where('status', OtpStatus::ACTIVE)
                ->update(['status' => OtpStatus::SUPERSEDED]);

            /** @var Model $otp */
            $otp = $user->otps()->create([
                'code' => $code,
                'status' => OtpStatus::ACTIVE,
                'ip_address' => request()->ip(),
                'remember' => $remember,
            ]);

            return [$otp, $code];
        });
    }
}
