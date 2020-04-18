<?php

namespace BlogAPI\Repositories;

use BlogAPI\Models\TagArticle;
use Recca0120\Repository\Criteria;
use Recca0120\Repository\EloquentRepository;

class TagArticleRepository extends EloquentRepository
{
    public function __construct(TagArticle $model)
    {
        parent::__construct($model);
    }

    public function firstOrCreateTagUser(int $tagId, int $articleId)
    {
        $tagUser = $this->firstOrCreate([
            'tag_id' => $tagId,
            'article_id' => $articleId
        ]);

        return $tagUser;
    }

    public function firstOrCreateTagUsers(array $tagIds, int $articleId)
    {
        return collect($tagIds)->map(function ($tagId) use ($articleId){
            return $this->firstOrCreateTagUser($tagId, $articleId);
        });
    }

    public function findByArticleId(int $articleId)
    {
        $criteria = Criteria::create()->where('article_id', $articleId);

        return $this->first($criteria);
    }
}