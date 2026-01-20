<?php

namespace App\Repositories\Contracts;

use App\Models\Review;

/**
 * @method Review|null find(mixed $id)
 * @method Review|null first()
 */
interface ReviewRepositoryInterface extends RepositoryInterface
{
	//define set of methods that ReviewRepositoryInterface Repository must implement
}
