<?php

use App\Http\Controllers\MailController;
use App\Http\Controllers\Questionnaire;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\Livewire\TermsOfServiceController;

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

Route::get('/information-sheet', [\App\Http\Controllers\Controller::class, 'download_info_sheet'])
    ->name('download_information_sheet');


Route::get('/', function () {
    //return redirect()->route('login');
    return redirect()->route('welcome');
});

Route::get('/no-consent', function () {
    return view("informed_consent_declined");
})->name('no_consent');

Route::get('/consent-grant', function (){
    session(['consent' => '1']);
    session(['startStudy' => '1']);
    return redirect(route('show', ['folder' => 'inbox']));
})->name('consent');

Route::middleware([
    //'auth:sanctum',
    config('jetstream.auth_session'),
    //'verified'
])->group(function (){
    Route::get('/warning_log', [MailController::class, 'warningLog'])->name('warning_log');
});

Route::middleware([
    //'auth:sanctum',
    config('jetstream.auth_session'),
    //'verified',
    \App\Http\Middleware\StudyAuth::class,
    'log'
])->group(function () {
    Route::get('/welcome', function () {
        if (Auth::user()->followUpQuestionnaire != null) {  // study already completed
            return redirect(route('thankyou'));
        } else {
            if (session()->has('consent')) {
                return redirect(route('show', ['folder' => 'inbox']));
            }
            else {
                return view('welcome');
            }
        }
    })->name('welcome');

    Route::get('/nextstep/{id?}', function ($id = null){
        if($id === null) {
            return redirect(route('show', ['folder' => 'inbox']));
        }
        return view("emailquestionnaire")->with('warning_type', Auth::user()->warning_type);
    })->name('next_step');
    Route::post('/nextstep/{mail?}', [Questionnaire::class, 'storeEmailQuestionnaire']); //->name('next_step');

    Route::get('/finish', function (){
        return view("thank_you_page");
    })->name('thankyou');

    Route::get('/debriefing', function() {
        return view('debriefing');
    })->name('debriefing');

    Route::get('/end', [Questionnaire::class, 'showFollowUp'])->name('post_test');
    Route::post('/end', [Questionnaire::class, 'storeFollowUp']);

    Route::get('/warning_browser', [MailController::class, 'warning_browser'])->name('warning_browser');

    Route::get('/{folder?}/{id?}', [MailController::class, 'show'])->name('show');
});
