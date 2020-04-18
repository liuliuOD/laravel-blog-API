<?php

namespace BlogAPI\Repositories;

use BlogAPI\Models\Tag;
use Recca0120\Repository\EloquentRepository;

class TagRepository extends EloquentRepository
{
    public function __construct(Tag $model)
    {
        parent::__construct($model);
    }

    public function firstOrCreateTag(string $tag)
    {
        $tag = $this->firstOrCreate([
            'tag' => $tag
        ]);

        return $tag;
    }

    public function firstOrCreateTags(array $tags)
    {
        return collect($tags)->map(function ($tag) {
            return $this->firstOrCreate([
                'tag' => $tag
            ]);
        });
    }
}