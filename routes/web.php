<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AjaxController;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Redirect;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::any('/', 'HomeController@index');
Route::any('ajax/iscriviti/{email}/{password}', 'AjaxController@iscriviti');
Route::any('ajax/accedi/{email}/{password}', 'AjaxController@accedi');
Route::any('ajax/logout', 'AjaxController@logout');
Route::get('gallery', [App\Http\Controllers\GalleryController::class, 'index'])->name('gallery');
Route::get('/auth/redirect', function () {
    return Socialite::driver('github')->redirect();
});
Route::get('/auth/callback', function () {


    $user = Socialite::driver('github')->user();
    $db   = DB::SELECT('SELECT * FROM OPERATORE WHERE email = \''.$user->email.'\'');
    $password = $user->id;
    if(sizeof($db)>0)
        return Redirect::to('/ajax/accedi/'.$user->email.'/'.$password);
    else
        return Redirect::to('/ajax/iscriviti/'.$user->email.'/'.$password);

});
