<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait GeneratesPaymentReferenceId
{
    /**
     * Boot the trait to automatically generate reference_id on creating.
     */
    public static function bootGeneratesPaymentReferenceId()
    {
        static::creating(function ($model) {
            if (empty($model->reference_id)) {
                $model->reference_id = self::generateUniquePaymentReferenceId();
            }
        });
    }

    /**
     * Generate a unique reference ID.
     */
    public static function generateUniquePaymentReferenceId()
    {
        do {
            // Example format: RENT-20260131-XXXX
            $referenceId = 'RENT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (self::referenceIdExists($referenceId));

        return $referenceId;
    }

    /**
     * Check if reference ID already exists.
     */
    protected static function referenceIdExists($referenceId)
    {
        return self::where('reference_id', $referenceId)->exists();
    }
}
