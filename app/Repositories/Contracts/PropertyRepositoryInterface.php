<?php

namespace App\Repositories\Contracts;

use App\Models\Property;

/**
 * @method Property|null find(mixed $id)
 * @method Property|null first()
 */
interface PropertyRepositoryInterface extends RepositoryInterface
{
	//define set of methods that PropertyRepositoryInterface Repository must implement
	public function searchByTitle(string $title) : static;
	public function searchByForRentable() : static;
	public function searchByActive() : static;
}
