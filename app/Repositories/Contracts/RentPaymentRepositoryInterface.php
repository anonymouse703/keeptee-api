<?php

namespace App\Repositories\Contracts;

use App\Models\RentPayment;

/**
 * @method RentPayment|null find(mixed $id)
 * @method RentPayment|null first()
 */
interface RentPaymentRepositoryInterface extends RepositoryInterface
{
	//define set of methods that RentPaymentRepositoryInterface Repository must implement
	public function searchByKey(string $key) : static;
	public function searchByActive() : static;
}
