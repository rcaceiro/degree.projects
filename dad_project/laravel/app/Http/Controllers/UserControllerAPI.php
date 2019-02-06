<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserFormRequest;
use App\Http\Resources\User as UserResource;
use App\Mail\ReasonBlockedMail;
use App\Mail\ReasonUnblockedMail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class UserControllerAPI extends Controller
{
    public function index(Request $request)
    {
        //checks to get just the players not the admins!!
        if ($request->has('page')) {
            return UserResource::collection(User::where('admin', User::IS_NOT_ADMIN)->paginate(10));
        }
        return UserResource::collection(User::where('admin', User::IS_NOT_ADMIN)->get());

    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        //ONLY ADMIN AND SELF CAN DELETE
        if(!Auth::user()->isAdmin() && Auth::user()->nickname !== $user->nickname){
            return response()->json(null, 403);
        }
        //CANT DELETE ADMIN
        if ($user->isAdmin()) {
            return response()->json(null, 403);
        }
        $user->delete();
        return response()->json(null, 204);
    }

    public function update(UpdateUserFormRequest $request, $id)
    {
        $user = User::findOrFail($id);
        //can only edit his own profile
        if(Auth::user()->nickname !== $user->nickname){
            return response()->json(null,403);
        }

        $oldNickname = $user->nickname;
        $oldEmail = $user->email;
        $oldPassword = $user->password;
        $oldName = $user->name;


        $input = $request->all();
        if(array_key_exists('password', $input)){
            $input['password'] = bcrypt($input['password']);

            if($input['password'] != $oldPassword){
                $user->password = $input['password'];
            }
        }

        if(array_key_exists('password_confirmation', $input)){
            if(!array_key_exists('password', $input)){
                return response()->json('Entered passwords don\'t match!', 422);
            }
        }


        if($input['nickname'] != $oldNickname){
            $user->nickname = $input['nickname'];
        }

        if($input['email'] != $oldEmail){
            $user->email = $input['email'];
        }

        if($input['name'] != $oldName){
            $user->name = $input['name'];
        }


        $user->save();
        return response()->json('Profile updated successfully!', 200);
        //$user->update($request->all());
        //return response()->json(null, 204);
    }

    public function updateBlocked($id, Request $request)
    {
        $input = $request->all();

        $user = User::findOrFail($id);
        if ($user->isAdmin()) {
            return response()->json(null, 403);
        }

        if (!array_key_exists('blocked', $input)) {
            return response()->json(null, 400);
        }


        if ($input['blocked'] == User::IS_NOT_BLOCKED && array_key_exists('reason_reactivated', $input) && !empty($input['reason_reactivated'])) {

            $user->blocked = User::IS_NOT_BLOCKED;
            $user->reason_reactivated = $input['reason_reactivated'];
            $user->reason_blocked = null;
            Mail::to($user)->send(new ReasonUnblockedMail($user->reason_reactivated));

            $user->save();
            return new UserResource($user);
        }

        if ($input['blocked'] == User::IS_BLOCKED && array_key_exists('reason_blocked', $input) && !empty($input['reason_blocked'])) {
            $user->blocked = User::IS_BLOCKED;
            $user->reason_reactivated = null;
            $user->reason_blocked = $input['reason_blocked'];
            Mail::to($user)->send(new ReasonBlockedMail($user->reason_blocked));

            $user->save();
            return new UserResource($user);
        }


        return response()->json(null, 400);
    }


    /**
     *
     * Use to verify admin password to allow edit profile
     *
     *
     * REQUEST expects camps:
     *      -nicknameOrEmail
     *      -password
     * @return \Illuminate\Http\JsonResponse
     */


    public function verify()
    {
        $user = User::where('email', request('nicknameOrEmail'))
            ->orWhere('nickname', request('nicknameOrEmail'))->get();

        //NO USER WITH THAT NICKNAME/EMAIL
        if ($user->isEmpty()) {
            return response()->json('User credentials are invalid', 401);
        }
        //ATTEMPT TO VERIFY WITH STOLEN TOKEN
        $user = $user->first();
        if ($user->id != Auth::user()->id) {
            return response()->json('User credentials are invalid', 401);
        }
        //COMPARE PASSWORDS BY HASH
        if (Hash::check(request('password'), $user->password)) {
            return response()->json(null, 204);
        } else {
            return response()->json('User credentials are invalid', 401);
        }
    }


}
