<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified']);//->group(function (){
    //Route::get('/warning_log', [\App\Http\Controllers\MailController::class, 'warning'])->name('warning_log');
//});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'log'
])->group(function () {
    Route::get('/welcome', function (){
        /*if (Auth::user()->followUpQuestionnaire != null) {
            return redirect(route('thankyou'));
        } else {
            if (session()->has('welcome_shown'))
                return redirect(route('show', ['folder' => 'inbox']));
            else {
                session(['welcome_shown' => '1']);
                return view('welcome');
            }
        }*/
         return view('welcome');
    })->name('welcome');

    Route::get('/nextstep/{id?}', function ($id = null){
        if($id === null)
            return view("skillsquestionnaire");
        return redirect(route('questionnaire', ['id' => $id]));
    })->name('next_step');

    Route::get('/nextstep/{id?}', [\App\Http\Controllers\Questionnaire::class, 'showQuestionnaire'])->name('questionnaire');
    Route::post('/nextstep/{id?}', [\App\Http\Controllers\Questionnaire::class, 'levelQuestionnaire'])->name('next_step');
    # Route::post('/nextstep/{mail?}', [\App\Http\Controllers\Questionnaire::class, 'storeEmailQuestionnaire'])->name('next_step');

    Route::get('/skills', [\App\Http\Controllers\Questionnaire::class, 'showFollowUp'])->name('post_test');

    Route::post('/skills', [\App\Http\Controllers\Questionnaire::class, 'storeFollowUp'])->name('show');

    Route::get('/finish', function (){
        return view("thank_you_page");
    })->name('thankyou');
});
