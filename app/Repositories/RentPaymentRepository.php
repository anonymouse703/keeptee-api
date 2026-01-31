<?php

namespace App\Repositories;

use App\Models\RentPayment;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\RentPaymentRepositoryInterface;

class RentPaymentRepository extends BaseRepository implements RentPaymentRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(RentPayment::class));
    }

    public function searchByKey(string $key): static
    {
        return $this->filter(static function (Builder $builder) use ($key) {
            $builder->whereHas('property', function ($q) use ($key) {
                $q->where('title', 'LIKE', '%' . $key . '%');
            })
            ->orWhereHas('tenant', function ($q) use ($key) {
                $q->where('name', 'LIKE', '%' . $key . '%');
            });
        });
    }

    public function searchByActive(): static
    {
        return $this->filter(static fn(Builder $builder) => $builder->where('is_active', true));
    }
}
