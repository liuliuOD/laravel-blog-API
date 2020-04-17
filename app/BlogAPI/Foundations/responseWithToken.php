<?php
declare(strict_types=1);

namespace BlogAPI\Foundations;

trait responseWithToken
{
    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    private function responseWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}