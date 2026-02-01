<?php

namespace App\Repositories\Contracts;

use App\Models\File;

/**
 * @method File|null find(mixed $id)
 * @method File|null first()
 */
interface FileRepositoryInterface extends RepositoryInterface
{
	//define set of methods that FileRepositoryInterface Repository must implement
}
