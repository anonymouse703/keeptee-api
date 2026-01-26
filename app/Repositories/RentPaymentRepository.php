<?php

namespace App\Repositories;

use App\Models\RentPayment;
use App\Repositories\Contracts\RentPaymentRepositoryInterface;

class RentPaymentRepository extends BaseRepository implements RentPaymentRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(app(RentPayment::class));
    }
}
