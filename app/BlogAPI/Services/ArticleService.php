<?php
namespace BlogAPI\Services;

use Recca0120\Repository\Criteria;
use BlogAPI\Repositories\ArticleRepository;

class ArticleService
{
    protected $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    // public function findByEmail(string $email)
    // {
    //     $criteria = Criteria::create()->where('email', $email);

    //     return $this->userRepository->first($criteria);
    // }

    // public function registerUser($params)
    // {
    //     return $this->userRepository->create([
    //         'user_name' => $params['user_name'],
    //         'email' => $params['email'],
    //         'password' => \Hash::make($params['password'])
    //     ]);
    // }

    // public function resetPassword($email, $password)
    // {
    //     $criteria = Criteria::create()->where('email', $email);

    //     return $this->userRepository->getQuery($criteria)->update([
    //         'password' => \Hash::make($password)
    //     ]);
    // }
}