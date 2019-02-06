<?php

namespace App\Http\Controllers\MyAuth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordFormRequest;
use App\PasswordReset;
use App\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MyResetPasswordController extends Controller
{

    /**
     * @param $email - User email which received the ResetPasswordEmail
     * @param $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function verifyPasswordReset(Request $request)
    {
        $input = $request->all();

        $user = User::where('email', $input['email'])->firstOrFail();
        if($user->isBlocked()){
            throw new NotFoundHttpException;
        }
        //IF INVALID RESET PASSWORD TOKEN RETURN
        PasswordReset::where('email', $input['email'])
            ->where('token', $input['token'])->firstOrFail();

        return response()->json(['success'=>'Valid password reset token'], 200);
    }

    /**
     * @param ResetPasswordFormRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordFormRequest $request){
        $input = $request->all();

        $user = User::where('email', $input['email'])->firstOrFail();
        if($user->isBlocked()){
            throw new NotFoundHttpException;
        }
        //IF INVALID RESET PASSWORD TOKEN RETURN
        $passwordResetToken = PasswordReset::where('email', $input['email'])
            ->where('token', $input['token'])->firstOrFail();
        $user->password = bcrypt($input['password']);

        $passwordResetToken->delete();
        $user->save();
        return response()->json(['success'=>'Password changed.'], 200);

    }

}
