<?php

namespace App\Repositories\Contracts;

use App\Models\ContactMessage;

/**
 * @method ContactMessage|null find(mixed $id)
 * @method ContactMessage|null first()
 */
interface ContactMessageRepositoryInterface extends RepositoryInterface
{
	//define set of methods that ContactMessageRepositoryInterface Repository must implement
}
