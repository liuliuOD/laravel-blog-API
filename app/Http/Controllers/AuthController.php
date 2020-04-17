<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BlogAPI\Services\UserService;
use BlogAPI\Validators\UserValidator;
use BlogAPI\Exceptions\InvalidParameterException;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function login (Request $request)
    {

    }

    public function register (Request $request)
    {
        $params = $request->only(['user_name', 'email', 'password']);

        $valid = (new UserValidator($params))->setRegisterValid();

        if (! $valid->passes()) {
            throw new InvalidParameterException($valid->errors()->first());
        }
        
        if ($user = $this->userService->findByEmail($params["email"])) {
            throw new InvalidParameterException("已經是會員囉！！");
        } else {
            $user = $this->userService->registerUser($params);
        }

        return $user;
    }
}
