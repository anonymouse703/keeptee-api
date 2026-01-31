<?php

namespace App\Repositories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Contracts\TenantRepositoryInterface;

class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(Tenant::class));
    }

   public function searchByName(string $name): static
    {
        return $this->filter(static fn(Builder $builder) => $builder->where('name', 'LIKE', '%' . $name . '%'));
    }
}
