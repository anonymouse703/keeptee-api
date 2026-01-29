<?php

namespace App\Repositories;

use App\Models\Property;
use App\Enums\Property\Status;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\PropertyRepositoryInterface;

class PropertyRepository extends BaseRepository implements PropertyRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(Property::class));
    }

    public function searchByTitle(string $title): static
    {
        return $this->filter(static fn(Builder $builder) => $builder->where('title', 'LIKE', '%' . $title . '%'));
    }

    public function searchByForRentable(): static
    {
        return $this->filter(static fn(Builder $builder) => $builder->where('status', Status::ForRent));
    }

    public function searchByActive(): static
    {
        return $this->filter(static fn(Builder $builder) => $builder->where('is_active', true));
    }
}
