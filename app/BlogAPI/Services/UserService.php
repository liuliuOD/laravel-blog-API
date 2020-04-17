<?php
namespace BlogAPI\Services;

use Recca0120\Repository\Criteria;
use BlogAPI\Repositories\UserRepository;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function findByEmail(string $email)
    {
        $criteria = Criteria::create()->where('email', $email);

        return $this->userRepository->first($criteria);
    }

    public function registerUser($params)
    {
        return $this->userRepository->create([
            'user_name' => $params['user_name'],
            'email' => $params['email'],
            'password' => \Hash::make($params['password'])
        ]);
    }
}