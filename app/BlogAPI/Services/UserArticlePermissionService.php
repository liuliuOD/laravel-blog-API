<?php
namespace BlogAPI\Services;

use DB;
use Recca0120\Repository\Criteria;
use BlogAPI\Repositories\UserArticlePermissionRepository;

class UserArticlePermissionService
{
    protected $userArticlePermissionRepository;

    public function __construct(UserArticlePermissionRepository $userArticlePermissionRepository)
    {
        $this->userArticlePermissionRepository = $userArticlePermissionRepository;
    }

    public function addUnPaidPermission(int $articleId, int $orderId)
    {
        return $this->userArticlePermissionRepository->create([
            'user_id' => request()->user()->id,
            'article_id' => $articleId,
            'read' => false,
            'write' => false,
            'order_id' => $orderId,
        ]);
    }

    public function addUnPaidPermissions(array $articleIds, int $orderId)
    {
        return collect($articleIds)->map(function ($articleId) use($orderId) {
            return $this->addUnPaidPermission($articleId, $orderId);
        });
    }
}
