<?php

namespace App\Repositories\Contracts;

use App\Models\Lease;

/**
 * @method Lease|null find(mixed $id)
 * @method Lease|null first()
 */
interface LeaseRepositoryInterface extends RepositoryInterface
{
	//define set of methods that LeaseRepositoryInterface Repository must implement
}
