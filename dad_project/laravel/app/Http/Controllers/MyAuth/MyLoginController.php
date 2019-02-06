<?php

namespace App\Http\Controllers\MyAuth;

use App\Http\Controllers\Controller;
use App\Http\Resources\User as UserResource;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;


class MyLoginController extends Controller
{
    /**
     * REQUEST expects camps:
     *      -nicknameOrEmail
     *      -password
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function login()
    {
        $user = User::where('email', request('nicknameOrEmail'))
            ->orWhere('nickname', request('nicknameOrEmail'))->get();

        //NO USER WITH THAT NICKNAME/EMAIL
        if ($user->isEmpty()) {
            return response()
                ->json('User credentials are invalid', 401);
        }
        //USER BLOCKED
        $user = $user->first();
        if ($user->isBlocked()) {
            return response()
                ->json('Unable to login. Reason: ' .
                    $user->reason_blocked, 401);
        }
        //SCOPE FOR TOKEN
        if ($user->admin) {
            $scope = 'admin';
        } else {
            $scope = '';
        }

        $http = new Client;
        $response = $http->post(config('app.url') . 'oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => config('auth.password_client_id'),
                'client_secret' => config('auth.password_client_secret'),
                'username' => $user->email,
                'password' => request('password'),
                'scope' => $scope,
            ],
            'exceptions' => false,
        ]);

        $errorCode = $response->getStatusCode();
        if ($errorCode == '200') {
            return json_decode((string)$response->getBody(), true);
        } else {
            return response()->json('User credentials are invalid', $errorCode);
        }
    }

    public function refresh()
    {
        $http = new Client;
        $response = $http->post(config('app.url') . 'oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => config('auth.password_client_id'),
                'client_secret' => config('auth.password_client_secret'),
                'refresh_token' => request('refreshToken'),
            ],
            'exceptions' => false,
        ]);
        $errorCode = $response->getStatusCode();
        if ($errorCode == '200') {
            return json_decode((string)$response->getBody(), true);
        } else {
            return response()->json('Invalid refresh token', $errorCode);
        }
    }

    public function logout()
    {
        Auth::guard('api')->user()->token()->revoke();
        Auth::guard('api')->user()->token()->delete();
        return response()->json('Token revoked', 200);
    }

    public function details()
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
        //>getTargetUrl()
    }

    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();

        $token = $user->token;
        $nickname = $user->nickname;
        $name = $user->name;
        $email = $user->email;
        $password = 'secret';
        $avatar = $user->avatar;
        dd($user);
        $existingUser = User::where('nickname', $nickname)->orWhere('email', $email)->first();
        if ($existingUser !== null) {

            $http = new Client;
            $response = $http->post(config('app.url') . 'oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('auth.password_client_id'),
                    'client_secret' => config('auth.password_client_secret'),
                    'username' => $existingUser->email,
                    'password' => $existingUser->password
                ],
                'exceptions' => false,
            ]);

            $errorCode = $response->getStatusCode();
            if ($errorCode == '200') {
                return view('index');
            } else {
                return view('index');
            }
            return view('index');
        }

        if ($name == null && $nickname != null) {
            $name = $nickname;
        } else {
            $name = $email;
        }
        if ($nickname == null) {
            $nickname = $name;
        }

        $user = new User();
        $user->nickname = $nickname;
        $user->name = $name;
        $user->email = $email;
        $user->admin = User::IS_NOT_ADMIN;
        $user->blocked = User::IS_NOT_BLOCKED;
        $user->password = bcrypt($password);

        event(new Registered($user->save()));

        $http = new Client;
        $response = $http->post(config('app.url') . 'oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => config('auth.password_client_id'),
                'client_secret' => config('auth.password_client_secret'),
                'username' => $email,
                'password' => $password
            ],
            'exceptions' => false,
        ]);

        $errorCode = $response->getStatusCode();
        if ($errorCode == '200') {
            dd(json_decode((string)$response->getBody(), true));
            return view('index');
        } else {
            return view('index');
        }
    }
}
