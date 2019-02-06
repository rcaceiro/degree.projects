<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminControllerAPI extends Controller
{
    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);
        if (Auth::user()->id != $admin->id) {
            return response()->json(null, 403);
        }

        if (request('email') != $admin->email &&
            request('password') == null
        ) {
            $request->validate([
                'email' => 'required|email|max:255|unique:users,email',
            ], [
                'required' => "You can't leave the :attribute empty!",
                'email.email' => 'The email has to be an e-mail! (duhh)',
                'unique' => "Somebody's already using that :attribute!",
                'max' => "Your new :attribute is too long!",
                'min' => "Your new :attribute is too short! (min 6)"
            ]);
            $admin->email = request('email');

        } else if (request('password') != null &&
            request('email') == $admin->email
        ) {
            $request->validate([
                'password' => 'confirmed|max:255|min:6'
            ], [
                'confirmed' => "Entered passwords don't match!",
                'max' => "Your new :attribute is too long!",
                'min' => "Your new :attribute is too short! (min 6)"
            ]);
            $admin->password = bcrypt(request('password'));

        } else if (request('email') != $admin->email &&
            request('password') != null
        ) {
            $request->validate([
                'password' => 'confirmed|max:255|min:6',
                'email' => 'required|email|max:255|unique:users,email',
            ], [
                'required' => "You can't leave the :attribute empty!",
                'email.email' => 'The email has to be an e-mail! (duhh)',
                'unique' => "Somebody's already using that :attribute!",
                'max' => "Your new :attribute is too long!",
                'min' => "Your new :attribute is too short! (min 6)",
                'confirmed' => "Entered passwords don't match!",
            ]);
            $admin->email = request('email');
            $admin->password = bcrypt(request('password'));
        }

        $admin->save();
        return response()->json('Profile updated successfully!', 200);
    }
}
