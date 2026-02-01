<?php

namespace App\Repositories;

use App\Models\File;
use App\Repositories\Contracts\FileRepositoryInterface;

class FileRepository extends BaseRepository implements FileRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(File::class));
    }
}
