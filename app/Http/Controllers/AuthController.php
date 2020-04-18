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
        $this->userService = $userService;
    }

    /**
     * /login 帳號登入
     *
     * @bodyParam email string required 電子郵件
     * @bodyParam password string required 密碼
     *
     * @responseFile 200 responses/auth/login.json
     * @responseFile 400 responses/errors/400.json
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws InvalidParameterException
     */
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

    /**
     * /register 申請帳號
     *
     * @bodyParam user_name string required 使用者名稱
     * @bodyParam email string required 電子郵件
     * @bodyParam password string required 密碼
     *
     * @responseFile 201 responses/auth/register.json
     * @responseFile 400 responses/errors/400.json
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws InvalidParameterException
     */
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

    /**
     * /reset-password 忘記密碼 - 密碼更新
     * tested.
     *
     * @bodyParam email string required 電子郵件
     * @bodyParam password string required 密碼
     *
     * @responseFile 200 responses/v1/auth/login.json
     * @responseFile 400 responses/errors/400.json
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws InvalidParameterException
     */
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

    public function me()
    {
        return response()->json(auth()->user());
    }
}
