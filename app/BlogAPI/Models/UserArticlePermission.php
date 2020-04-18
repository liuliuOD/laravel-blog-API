<?php

namespace BlogAPI\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserArticlePermission extends Model
{
    use SoftDeletes;

    protected $guarded = [];
}