<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function formatValidationErrors(Validator $validator)
    {
        return [
            'status' => 'error',
            'message' => 'An error occurred',
            'errors' => $validator->errors()
        ];
    }

    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        return [
            'status' => 'error',
            'message' => 'An error occurred',
            'errors' => $errors
        ];
    }
}
