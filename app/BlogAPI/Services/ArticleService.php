<?php
namespace BlogAPI\Services;

use \DB;
use Illuminate\Support\Arr;
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

    public function findById($id)
    {
        return $this->articleRepository->find($id);
    }

    public function createArticle($params, $user_id)
    {
        return $this->articleRepository->create([
            'title' => $params['title'],
            'content' => $params['content'],
            'user_id' => $user_id
        ]);
    }

    public function updateArticle($params, $id)
    {
        if (Arr::exists($params, 'title')) {
            $attribute['title'] = $params['title'];
        }

        if (Arr::exists($params, 'content')) {
            $attribute['content'] = $params['content'];
        }

        if(! isset($attribute)) {
            return $this;
        }

        return $this->articleRepository->update($id, $attribute);
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

    public function updateArticleAndTag($params, $article_id)
    {
        DB::beginTransaction();
        try {
            $article = $this->updateArticle($params, $article_id);

            if (Arr::exists($params, 'tag')) {
                $tagIds = $this->tagRepository
                    ->firstOrCreateTags($params['tag'])
                    ->pluck('id')
                    ->toArray();
                $this->tagArticleRepository->firstOrCreateTagUsers($tagIds, $article_id);

                $currentTagIds = $this->tagArticleRepository
                    ->findByArticleId($article_id)
                    ->pluck('tag_id')
                    ->toArray();

                $deleteTagIds = array_diff($currentTagIds, $tagIds);
                $criteria = Criteria::create()
                    ->where('article_id', $article_id)
                    ->whereIn('tag_id', $deleteTagIds);
                $this->tagArticleRepository
                    ->get($criteria)
                    ->map(function ($tagArticle) {
                        $tagArticle->delete();
                    });
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw new InternalServerException($e->getMessage());
        }
    }
}
