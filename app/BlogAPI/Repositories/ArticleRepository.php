<?php

namespace BlogAPI\Repositories;

use BlogAPI\Models\Article;
use Recca0120\Repository\EloquentRepository;

class ArticleRepository extends EloquentRepository
{
    public function __construct(Article $model)
    {
        parent::__construct($model);
    }
}