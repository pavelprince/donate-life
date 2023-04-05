<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function generate(Request $request){
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            $response['message'] = $validator->errors()->first();
            return $this->validationfailureApiResponse($response);
        }

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            $user = new User();
            $user->phone = $request->phone;
            $user->password = Hash::make(12345678);
            $user->save();
        }

        $user_otp = $this->generateOtp($request->phone);
        $sent_otp = $user_otp->sendSMS($request->phone);

        if ($sent_otp->status() == 200) {
            $response['otp'] = $user_otp->otp;
            $response['message'] = 'Otp sent successfully !';
            return $this->successApiResponse($response);
        }
    }

    public function generateOtp($phone)
    {
        $user = User::where('phone', $phone)->first();
        $user_otp = UserOtp::where('user_id', $user->id)->latest()->first();

        $now = now();

        if ($user_otp && $now->isBefore($user_otp->expire_at)) {
            return $user_otp;
        }

        return UserOtp::create([
            'user_id' => $user->id,
            'otp' => rand(123456, 999999),
            'expire_at' => $now->addMinute(10),
        ]);
    }

    public function verification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|max:12',
            'otp' => 'required'
        ]);
        if ($validator->fails()) {
            $response['message'] = $validator->errors()->first();
            return $this->failureApiResponse($response);
        }

        $user = User::where('phone', $request->phone)->first();

        if ($user && $user->email_verified_at != null){
            $response['message'] = "Authenticated";
            $response['token'] = 'Bearer ' . $user->createToken('access_token')->plainTextToken;
            $response['user'] = $user->only(['phone', 'id']);

            return $this->successApiResponse($response);
        }

        $user_otp = UserOtp::where('user_id', $user->id)->where('otp', $request->otp)->latest()->first();

        $now = now();

        if (!$user_otp) {
            return $this->notAllowedApiResponse([
                'message' => "OTP didnt not matched",
            ]);
        } else if ($user_otp && $now->isAfter($user_otp->expire_at)) {
            return $this->noLongerExistApiResponse([
                'message' => "Your OTP has been Expired",
            ]);
        } else {
            $user->email_verified_at = now();
            $user->save();

            $user_otp->update([
                'expire_at' => now()
            ]);

            if(!$token = Auth::login($user)){
                return response()->json(['error' => 'Unauthorized']);
            }

//            $response['message'] = "Authenticated";
//            $response['token'] = 'Bearer ' . $user->createToken(Str::random(40))->accessToken;
//            $response['token'] = 'Bearer ' . $user->createToken('access_token')->plainTextToken;
//            $response['user'] = $user->only(['phone', 'id']);

//            return $this->successApiResponse($response);
            return $this->responseWithToken($token);
        }
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
