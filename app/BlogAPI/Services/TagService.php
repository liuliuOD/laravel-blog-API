<?php
namespace BlogAPI\Services;

use DB;
use Recca0120\Repository\Criteria;
use BlogAPI\Repositories\TagRepository;

class TagService
{
    protected $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function findByTag(string $tag)
    {
        $criteria = Criteria::create()->where('tag', $tag);

        return $this->tagRepository->first($criteria);
    }
}
