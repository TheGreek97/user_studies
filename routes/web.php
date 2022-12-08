<?php

use App\Http\Controllers\Questionnaire;
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
    'verified',
    'log',
    \App\Http\Middleware\CheckTestCompleted::class
])->group(function () {
    Route::get('/welcome', function (){
         return view('welcome');
    })->name('welcome');

    Route::get('/skills', function (){
        return view('skillsquestionnaire');
    })->name('skills');
    Route::post('/skills', [Questionnaire::class, 'store_skills_questionnaire'])->name('skills');

    Route::get('/questionnaire/', function () {
        if (Auth::user()->skillsQuestionnaire()) {
            return view('questionnaire_basic');
        } else {
            return redirect(route('skills'));
        }
    })->name('questionnaire');
    Route::post('/questionnaire/', [Questionnaire::class, 'store_questionnaire'])->name('questionnaire');

    Route::any('/nextstep/', [Questionnaire::class, 'showFollowUp'])->name('next_step');

    Route::get('/advanced/', function (){
        return view('questionnaire_expert');
    })->name('advanced');
    Route::post('/advanced/', [Questionnaire::class, 'storeAdvanced'])->name('advanced');

    Route::get('/final_comments', function () {
        return view ('questionnaire_final');
    })->name('post_test');
    Route::post('/final_comments', [Questionnaire::class, 'storeFinalComments'])->name('post_test');

    Route::get('/finish', function (){
        return view("thank_you_page");
    })->name('thankyou');
});

Route::get('/test_completed', function (){
    return view("thank_you_page");
})->name('test_completed');
