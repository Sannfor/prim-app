<?php

namespace App\Casts;

use App\Enums\SubscriptionStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast kolom `status` langganan dengan nilai cadangan yang aman.
 *
 * @implements CastsAttributes<SubscriptionStatus, SubscriptionStatus|string>
 */
class SubscriptionStatusCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): SubscriptionStatus
    {
        if ($value instanceof SubscriptionStatus) {
            return $value;
        }

        if (is_string($value)) {
            return SubscriptionStatus::tryFrom($value) ?? SubscriptionStatus::Active;
        }

        return SubscriptionStatus::Active;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof SubscriptionStatus) {
            return $value->value;
        }

        if (is_string($value) && SubscriptionStatus::tryFrom($value) !== null) {
            return $value;
        }

        return $value === null ? null : SubscriptionStatus::Active->value;
    }
}
