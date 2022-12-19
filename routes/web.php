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
    'verified'])->group(function (){
    Route::get('/warning_log', [\App\Http\Controllers\MailController::class, 'warningLog'])->name('warning_log');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'log'
])->group(function () {
    Route::get('/welcome', function (){
        if (Auth::user()->followUpQuestionnaire != null) {
            return redirect(route('thankyou'));
        } else {
            if (session()->has('welcome_shown'))
                return redirect(route('show', ['folder' => 'inbox']));
            else {
                session(['welcome_shown' => '1']);
                return view('welcome');
            }
        }
    })->name('welcome');

    Route::get('/nextstep/{id?}', function ($id = null){
        if($id === null)
            return redirect(route('show', ['folder' => 'inbox']));
        return view("emailquestionnaire")->with('warning_type', Auth::user()->warning_type);
    })->name('next_step');

    Route::post('/nextstep/{mail?}', [\App\Http\Controllers\Questionnaire::class, 'storeEmailQuestionnaire'])->name('next_step');

    Route::get('/finish', function (){
        return view("thank_you_page");
    })->name('thankyou');

    Route::get('/end', [\App\Http\Controllers\Questionnaire::class, 'showFollowUp'])->name('post_test');

    Route::post('/end', [\App\Http\Controllers\Questionnaire::class, 'storeFollowUp']);

    Route::get('/warning_browser', [\App\Http\Controllers\MailController::class, 'warning_browser'])->name('warning_browser');

    Route::get('/{folder?}/{id?}', [\App\Http\Controllers\MailController::class, 'show'])->name('show');
});
