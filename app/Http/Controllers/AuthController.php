<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use BlogAPI\Services\UserService;
use BlogAPI\Validators\UserValidator;
use BlogAPI\Foundations\responseWithToken;
use BlogAPI\Exceptions\InvalidParameterException;

class AuthController extends Controller
{
    use responseWithToken;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'resetPassword']]);

        $this->userService = $userService;
    }

    public function login (Request $request)
    {
        $params = $request->only(['email', 'password']);

        $valid = (new UserValidator($params))->setLoginRule();

        if(! $valid->passes()) {
            throw new InvalidParameterException($valid->errors()->first());
        }
        
        if (! $token = auth()->attempt($params, true)) {
            throw new InvalidParameterException('請確認您的電子郵件和密碼輸入正確。');
        }

        return $this->responseWithToken($token);
    }

    public function register (Request $request)
    {
        $params = $request->only(['user_name', 'email', 'password']);

        $valid = (new UserValidator($params))->setRegisterRule();

        if (! $valid->passes()) {
            throw new InvalidParameterException($valid->errors()->first());
        }
        
        if ($user = $this->userService->findByEmail($params["email"])) {
            throw new InvalidParameterException("已經是會員囉！！");
        }

        $user = $this->userService->registerUser($params);

        return Response()->json([self::RESPONSE_OK], self::RESPONSE_OK_CODE);
    }

    public function resetPassword (Request $request)
    {
        $params = $request->only(['email', 'password']);

        $valid = (new UserValidator($params))->setResetPasswordRule();

        if (! $valid->passes()) {
            throw new InvalidParameterException($valid->errors()->first());
        }

        if (! $user = $this->userService->findByEmail($params["email"])) {
            throw new InvalidParameterException("你不是會員！！");
        }

        $this->userService->resetPassword($params['email'], $params['password']);

        return $this->responseWithToken(auth()->login($user));
    }
}
