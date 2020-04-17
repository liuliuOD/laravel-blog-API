<?php

namespace BlogAPI\Repositories;

use BlogAPI\Models\User;
use Recca0120\Repository\EloquentRepository;

class UserRepository extends EloquentRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}