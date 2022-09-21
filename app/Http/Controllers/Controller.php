<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    const STATUS_OK                     = 200;
    const STATUS_UNAUTHORIZED           = 401;
    const STATUS_INTERNAL_SERVER_ERROR  = 500;

    const MESSAGE_SUCCESS       = 'success';
    const MESSAGE_ERROR         = 'error';

    const MESSAGE_SUCCESS_LOGIN = 'Success Login';

    protected function send_response($data = null, $status = self::STATUS_OK, $message = self::MESSAGE_SUCCESS)
    {
        if ($data) {
            $response['data'] = $data;
        }
        $response['status'] = $status;
        $response['message'] = $message;

        return response()->json($response);
    }

    protected function fail_mandatory($data)
    {
        return $this->send_response($data, 500, 'Mandatory fields');
    }

    public function sendResponse($status, $message, $data = null)
    {
        $response['status'] = $status;
        $response['message'] = $message;
        $response['data'] = $data;

        return response()->json($response);
    }

    public function auth()
    {
        return Auth::user();
    }
}
