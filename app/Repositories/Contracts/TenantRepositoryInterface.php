<?php

namespace App\Repositories\Contracts;

use App\Models\Tenant;

/**
 * @method Tenant|null find(mixed $id)
 * @method Tenant|null first()
 */
interface TenantRepositoryInterface extends RepositoryInterface
{
	//define set of methods that TenantRepositoryInterface Repository must implement
}
