<?php
namespace BlogAPI\Services;

use DB;
use Recca0120\Repository\Criteria;
use BlogAPI\Repositories\TagArticleRepository;

class TagArticleService
{
    protected $tagArticleRepository;

    public function __construct(TagArticleRepository $tagArticleRepository)
    {
        $this->tagArticleRepository = $tagArticleRepository;
    }

    public function findByTagId(int $tagId)
    {
        $criteria = Criteria::create()->where('tag_id', $tagId);

        return $this->tagArticleRepository->first($criteria);
    }
}
