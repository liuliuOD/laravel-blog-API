<?php
namespace BlogAPI\Services;

use \DB;
use Recca0120\Repository\Criteria;
use BlogAPI\Repositories\TagRepository;
use BlogAPI\Repositories\ArticleRepository;
use BlogAPI\Repositories\TagArticleRepository;
use BlogAPI\Exceptions\InternalServerException;

class ArticleService
{
    protected $tagRepository;
    protected $articleRepository;
    protected $tagArticleRepository;

    public function __construct(
        TagRepository $tagRepository,
        ArticleRepository $articleRepository,
        TagArticleRepository $tagArticleRepository
        )
    {
        $this->tagRepository = $tagRepository;
        $this->articleRepository = $articleRepository;
        $this->tagArticleRepository = $tagArticleRepository;
    }

    public function createArticle($params, $user_id)
    {
        return $this->articleRepository->create([
            'title' => $params['title'],
            'content' => $params['content'],
            'user_id' => $user_id
        ]);
    }

    public function createArticleAndTag($params, $user_id)
    {
        DB::beginTransaction();
        try {
            $article = $this->createArticle($params, $user_id);

            $tagIds = $this->tagRepository
                ->firstOrCreateTags($params['tag'])
                ->pluck('id')
                ->toArray();

            $tagArticles = $this->tagArticleRepository->firstOrCreateTagUsers($tagIds, $article->id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw new InternalServerException();
        }

        return $tagArticles;
    }
}
