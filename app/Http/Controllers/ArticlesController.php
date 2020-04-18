<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BlogAPI\Services\TagService;
use BlogAPI\Services\ArticleService;
use BlogAPI\Services\TagArticleService;
use BlogAPI\Validators\ArticleValidator;
use BlogAPI\Exceptions\UnauthorizationException;
use BlogAPI\Exceptions\InvalidParameterException;

class ArticlesController extends Controller
{
    protected $tagService;
    protected $articleService;
    protected $tagArticleService;

    public function __construct(
        TagService $tagService,
        ArticleService $articleService,
        TagArticleService $tagArticleService
    )
    {
        $this->tagService = $tagService;
        $this->articleService = $articleService;
        $this->tagArticleService = $tagArticleService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $params = $request->only(['title', 'content', 'tag']);

        $valid = (new ArticleValidator($params))->setCreateArticleRule();

        if (! $valid->passes()) {
            throw new InvalidParameterException($valid->errors()->first());
        }

        if (! $user = auth()->user()) {
            throw new UnauthorizationException();
        }

        $tagArticles = $this->articleService->createArticleAndTag($params, $user['id']);

        return response()->json(self::RESPONSE_OK, self::RESPONSE_OK_CODE);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $params = $request->only(['title', 'content', 'tag']);

        $valid = (new ArticleValidator($params))->setUpdateArticleRule();

        if (! $valid->passes()) {
            throw new InvalidParameterException($valid->errors()->first());
        }

        if (! $user = auth()->user()) {
            throw new UnauthorizationException();
        }

        $article = $this->articleService->findById($id);
        if ($user['id'] != $article['user_id']) {
            throw new UnauthorizationException('你不能修改別人的文章。');
        }

        $this->articleService->updateArticleAndTag($params, $id);

        return response()->json(self::RESPONSE_OK, self::RESPONSE_OK_CODE);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
