<?php

namespace App\Http\Controllers\MyAuth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserFormRequest;
use App\Mail\VerifyEmail;
use App\PasswordReset;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MyRegisterController extends Controller
{
    /**
     * @param RegisterUserFormRequest $request - Custom from request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterUserFormRequest $request)
    {
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        $user = new User();
        $user->fill($input);
        $user->admin = User::IS_NOT_ADMIN;
        $user->blocked = User::IS_BLOCKED;
        $user->reason_blocked = "Please validate your e-mail account.";

//      create token on password reset table
        $confirmEmailToken = new PasswordReset();
        $confirmEmailToken->email = $user->email;
        $confirmEmailToken->token = str_random(40);

        Mail::to($user)->send(new VerifyEmail($confirmEmailToken));

        $confirmEmailToken->save();
        event(new Registered($user->save()));

        return response()->json(['success'=>'User created successfully! Now you just need to validate your e-mail account.'], 200);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeRegistration(Request $request)
    {
        $input = $request->all();

        $user = User::where('email', $input['email'])->firstOrFail();
        $confirmEmailToken = PasswordReset::where('email', $input['email'])
            ->where('token', $input['token'])->firstOrFail();

        $user->blocked = User::IS_NOT_BLOCKED;
        $user->reason_blocked = null;
        $user->save();

        $confirmEmailToken->delete();

        return response()->json(['success'=>'User activated.'], 200);
    }
}
