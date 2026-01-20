<?php

namespace App\Repositories;

use App\Models\EmailLog;
use App\Repositories\Contracts\EmailLogRepositoryInterface;

class EmailLogRepository extends BaseRepository implements EmailLogRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(EmailLog::class));
    }
}
