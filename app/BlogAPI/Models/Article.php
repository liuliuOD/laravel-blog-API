<?php

namespace BlogAPI\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function tags()
    {
        return $this->hasManyThrough(
            Tag::class,
            TagArticle::class,
            'article_id',
            'id',
            'id',
            'tag_id'
        );
    }
}