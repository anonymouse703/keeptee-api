<?php

namespace App\Repositories\Contracts;

use App\Models\EmailLog;

/**
 * @method EmailLog|null find(mixed $id)
 * @method EmailLog|null first()
 */
interface EmailLogRepositoryInterface extends RepositoryInterface
{
	//define set of methods that EmailLogRepositoryInterface Repository must implement
}
