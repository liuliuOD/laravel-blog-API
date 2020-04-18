<?php
namespace BlogAPI\Services;

use \DB;
use Illuminate\Support\Arr;
use Recca0120\Repository\Criteria;
use BlogAPI\Repositories\TagRepository;
use BlogAPI\Repositories\ArticleRepository;
use BlogAPI\Repositories\TagArticleRepository;
use BlogAPI\Exceptions\InternalServerException;
use BlogAPI\Repositories\UserArticlePermissionRepository;

class ArticleService
{
    protected $tagRepository;
    protected $articleRepository;
    protected $tagArticleRepository;
    protected $userArticlePermissionRepository;

    public function __construct(
        TagRepository $tagRepository,
        ArticleRepository $articleRepository,
        TagArticleRepository $tagArticleRepository,
        UserArticlePermissionRepository $userArticlePermissionRepository
    )
    {
        $this->tagRepository = $tagRepository;
        $this->articleRepository = $articleRepository;
        $this->tagArticleRepository = $tagArticleRepository;
        $this->userArticlePermissionRepository = $userArticlePermissionRepository;
    }

    public function findCanReadArticles($userId, $limit)
    {
        return DB::table('articles')
        ->select(['articles.id', 'articles.title', 'articles.content'])
            ->leftJoin('user_article_permissions', 'articles.id', 'user_article_permissions.article_id')
            ->where('user_article_permissions.user_id', $userId)
            ->where('user_article_permissions.read', true)
            ->orWhere('articles.is_free', true)
            ->paginate($limit);
    }

    public function findPermissionByUserIdAndArticleId($userId, $articleId)
    {
        $criteria = Criteria::create()
            ->where('user_id', $userId)
            ->where('article_id', $articleId);
        return $this->userArticlePermissionRepository->get($criteria);
    }

    public function findById($id)
    {
        return $this->articleRepository->find($id);
    }

    public function findByIds(array $ids)
    {
        return collect($ids)->map(function ($id) {
                return $this->articleRepository->find($id);
            }
        );
    }

    public function findByIdWithTag($id)
    {
        $criteria = Criteria::create()->where('id', $id)->with(['tags']);
        return $this->articleRepository->get($criteria);
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

    public function deleteArticle($id)
    {
        return $this->articleRepository->delete($id);
    }
}
