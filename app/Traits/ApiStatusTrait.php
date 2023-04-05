<?php

namespace App\Traits;


trait ApiStatusTrait
{
    public $successStatus = 200;
    public $failureStatus = 500;
    public $validationfailureStatus = 400;
    public $notAllowedStatus = 401;
    public $notFoundStatus = 401;
    public $noLongerExist = 410;

    public function successApiResponse($response, $message =null){
        return response()->json([
            'status' => 'Request was successful',
            'message' => $message,
            'data' => $response
        ], $this->successStatus);
    }

    public function failureApiResponse($response){
        return response()->json($response,$this->failureStatus);
    }
    public function validationfailureApiResponse($response){
        return response()->json($response,$this->validationfailureStatus);
    }

    public function notAllowedApiResponse($response){
        return response()->json($response,$this->notAllowedStatus);
    }

    public function notFoundApiResponse($response){
        return response()->json($response,$this->notFoundStatus);
    }

    public function noLongerExistApiResponse($response){
        return response()->json($response,$this->noLongerExist);
    }
}
