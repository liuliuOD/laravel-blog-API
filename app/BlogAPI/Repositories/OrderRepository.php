<?php

namespace BlogAPI\Repositories;

use BlogAPI\Models\Order;
use Recca0120\Repository\EloquentRepository;

class OrderRepository extends EloquentRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }
}