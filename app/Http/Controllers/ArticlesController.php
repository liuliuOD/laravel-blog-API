<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BlogAPI\Services\TagService;
use BlogAPI\Services\ArticleService;
use BlogAPI\Services\TagArticleService;
use BlogAPI\Validators\ArticleValidator;
use BlogAPI\Exceptions\NotFoundException;
use BlogAPI\Exceptions\ForbiddenException;
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $articles = $this->articleService
            ->findCanReadArticles($request->user()['id'], $limit);

        $articleIds = collect($articles->toArray()['data'])->pluck('id')->toArray();
        $articles = $this->articleService
                    ->findByIds($articleIds)
                    ->map(function ($article) {
                        $article = $article->only(['id', 'title', 'content', 'tags']);
                        $article['tags'] = $article['tags']->pluck('tag');

                        return $article;
                    });
        
        return response()->json($articles, self::RESPONSE_200);
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

        $tagArticles = $this->articleService->createArticleAndTag($params, $request->user()->id);

        return response()->json(self::RESPONSE_OK, self::RESPONSE_201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $article = $this->articleService->findByIdWithTag($id)->first();
        if (! $article) {
            throw new NotFoundException();
        }

        $userId = $request->user()->id;
        $permission = $this->articleService->findPermissionByUserIdAndArticleId($userId, $id)->first();
        if (($userId != $article['user_id']) && ! $article['is_free'] && (! $permission || $permission->read == false)) {
            throw new ForbiddenException();
        }

        $article = $article->only(['id', 'title', 'content', 'tags']);
        $article['tags'] = $article['tags']->pluck('tag');
        return response()->json($article, self::RESPONSE_200);
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

        $article = $this->articleService->findById($id);
        if (! $article) {
            throw new NotFoundException();
        }
        if ($request->user()->id != $article['user_id']) {
            throw new ForbiddenException();
        }

        $this->articleService->updateArticleAndTag($params, $id);

        return response()->json(self::RESPONSE_OK, self::RESPONSE_201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $article = $this->articleService->findById($id);
        if (! $article) {
            throw new NotFoundException();
        }
        if ($request->user()->id != $article['user_id']) {
            throw new ForbiddenException();
        }

        $this->articleService->deleteArticle($id);

        return response()->json(self::RESPONSE_OK, self::RESPONSE_201);
    }
}
