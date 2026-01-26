<?php

namespace App\Repositories;

use App\Models\PropertyInquiry;
use App\Repositories\Contracts\PropertyInquiryRepositoryInterface;

class PropertyInquiryRepository extends BaseRepository implements PropertyInquiryRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(PropertyInquiry::class));
    }
}
