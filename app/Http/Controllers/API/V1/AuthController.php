<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('token.is.valid'); 
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logon()
    {
        return $this->send_response(
            auth('api')->user(),
            self::STATUS_OK,
            self::MESSAGE_SUCCESS_LOGIN
        );
    }
}
