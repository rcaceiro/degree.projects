<?php

namespace App\Http\Controllers\MyAuth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordEmail;
use App\PasswordReset;
use App\User;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MyForgotPasswordController extends Controller
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(){
        $user = User::where('email', request('email'))->first();
        if($user == null){
            return response()->json(['error'=>"Your e-mail is not registered with us yet!"], 403);
        }

        if($user->isBlocked()){
            return response()->json(['error'=>'You are currently blocked'], 401);
        }

        //CHECK IF PASSWORD RESET EXISTS
        $passwordReset = PasswordReset::where('email', request('email'))->get();
        if(!$passwordReset->isEmpty()){
            return response()->json(['success'=>'You already did this, go check your e-mail'], 200);
        }

        $passwordReset = new PasswordReset();
        $passwordReset->email = $user->email;
        $passwordReset->token = str_random(40);

        Mail::to($user)->send(new ResetPasswordEmail($passwordReset));
        $passwordReset->save();

        return response()->json(['success'=>'We have e-mailed your password reset link!'], 200);
    }
}
