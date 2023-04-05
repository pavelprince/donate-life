<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends ApiController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            $response['message'] = $validator->errors()->first();
            return $this->validationfailureApiResponse($response);
        }

        $user = new User();
        $user->phone = $request->phone;
        $user->password = Hash::make(12345678);
        $user->save();

        return $this->successApiResponse($user);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            $response['message'] = $validator->errors()->first();
            return $this->validationfailureApiResponse($response);
        }

        $user = User::where('phone', request('phone'))->first();
        if (!$user) {
            return $this->validationfailureApiResponse('User not found');
        }

//        $request['password'] = '12345678';

//        if(!$token = auth()->attempt(['phone' => request('phone'), 'password' => request('password')])){
//        if(!$token = Auth::guard('api')->attempt(['phone' => request('phone'), 'password' => request('password')])){  // worked
//        if (!$token = auth()->attempt($validator->validated())) {
        if(!$token = Auth::login($user)){
            return response()->json(['error' => 'Unauthorized']);
        }

        return $this->responseWithToken($token);
    }

    protected function responseWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
