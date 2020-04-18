<?php

namespace BlogAPI\Repositories;

use BlogAPI\Models\UserArticlePermission;
use Recca0120\Repository\EloquentRepository;

class UserArticlePermissionRepository extends EloquentRepository
{
    public function __construct(UserArticlePermission $model)
    {
        parent::__construct($model);
    }
}
