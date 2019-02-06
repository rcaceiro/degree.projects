<?php

use Illuminate\Http\Request;


// TODO verify rules for RESTFUL API
//Verb Path Action Route Name
//GET       /project            index       project.index
//GET       /project/create     create      project.create
//POST      /project            store       project.store
//GET       /project/{id}       show        project.show
//GET       /project/{id}/edit  edit        project.edit
//PUT/PATCH /project/{id}       update      project.update
//DELETE    /project/{id}       destroy     project.destroy

//-------------NON PROTECTED ROUTES-------------
Route::post('login', 'MyAuth\\MyLoginController@login');
Route::post('login/refresh', 'MyAuth\\MyLoginController@refresh');

Route::post('register', 'MyAuth\\MyRegisterController@register');
Route::post('complete-registration', 'MyAuth\\MyRegisterController@completeRegistration');

Route::post('forgot-password', 'MyAuth\\MyForgotPasswordController@forgotPassword');
Route::post('verify-reset-request', 'MyAuth\\MyResetPasswordController@verifyPasswordReset');
Route::post('reset-password', 'MyAuth\\MyResetPasswordController@resetPassword');
Route::get('game/publicStatistics','GameControllerAPI@publicStatistics');

Route::group(['middleware' => ['auth:api', 'CheckIsBlocked']], function(){
    //--------------AUTH ROUTES----------------------
    Route::get('details', 'MyAuth\\MyLoginController@details');
    Route::post('logout', 'MyAuth\\MyLoginController@logout');
    //PROFILE UPDATE
    Route::post('verify-user', 'UserControllerAPI@verify');
    Route::patch('players/{id}', 'UserControllerAPI@update');
    Route::delete('players/{id}', 'UserControllerAPI@destroy');

    //GAME ROUTES
    Route::get('get-shuffled-pieces/{numberOfPieces}',
        'ImageControllerAPI@getShuffledPieces');
    //Route::get('game', 'GameControllerAPI@index');
    Route::post('game', 'GameControllerAPI@store');

    Route::get('game/playerStatistics/{nickname}','GameControllerAPI@playerStatistics');

    Route::group(['middleware' => ['CheckIsAdmin']], function(){
        //-----------ADMIN ROUTES---------------------
        //Player management
        Route::get('players', 'UserControllerAPI@index');
        Route::patch('players/block/{id}', 'UserControllerAPI@updateBlocked');

        //STATISTICS ADMIN
        Route::get('game/adminStatistics','GameControllerAPI@adminStatistics');

        //Image management
        Route::get('images', 'ImageControllerAPI@index');
        Route::delete('images/{id}', 'ImageControllerAPI@destroy');
        Route::post('images', 'ImageControllerAPI@store');
        Route::patch('images/{id}', 'ImageControllerAPI@updateActive');
        //PAGINATED IMAGES
        Route::get('imagestile', 'ImageControllerAPI@getTiles');
        Route::get('imageshidden', 'ImageControllerAPI@getHidden');
        //Edit admin
        Route::patch('admin/{id}', 'AdminControllerAPI@update');
        //Platform email management
        Route::get('platform-email/{id}', 'ConfigControllerAPI@show');
        Route::put('platform-email/{id}', 'ConfigControllerAPI@update');
    });
});
