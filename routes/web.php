<?php


use App\Http\Controllers\BFI2XSController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\QuestionnairesController;
use App\Http\Controllers\StPIIBController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\TEIQueSFController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingReactionController;
use App\Http\Middleware\RedirectToTheRightPhase;
use App\Http\Middleware\StudyAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\Livewire\TermsOfServiceController;
use Illuminate\Http\Request;

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

Route::get('/information-sheet', [StudyController::class, 'download_info_sheet'])
    ->name('download_information_sheet');


Route::get('/no-consent', function () {
    return view("informed_consent_declined");
})->name('no_consent');

Route::get('/consent-grant', [StudyController::class, 'giveConsent'])->name('consent');

Route::middleware([
    config('jetstream.auth_session'),
])->group(function (){
    Route::get('/warning_log', [MailController::class, 'warningLog'])->name('warning_log');
});

Route::get('/expelled', [StudyController::class, 'expelUser'])->name('expelUser');

// This needs to stay outside the RedirectToTheRightPhase guard
//Route::get('/create-training', [TrainingController::class, 'createTraining'])->name('training.create');


Route::middleware([
    config('jetstream.auth_session'),
    StudyAuth::class
])->group(function () {
    Route::post('/demographics', [QuestionnairesController::class, 'saveDemographicsData'])->name('demographics.create');
    Route::post('/big-five-inventory', [BFI2XSController::class, 'create'])->name('big-five-inventory.create');
    Route::post('/susceptibility-to-persuasion-ii', [StPIIBController::class, 'create'])->name('susceptibility-to-persuasion-ii.create');
    Route::post('/trait-emotional-intelligence', [TEIQueSFController::class, 'create'])->name('trait-emotional-intelligence.create');
    Route::post('/training-reaction-questionnaire', [TrainingReactionController::class, 'create'])->name('training-reaction-questionnaire.create');

    Route::post('/save-email-classification', [MailController::class, 'saveEmailClassification'])->name('save-email-classification');
    Route::get('/complete-training', [TrainingController::class, 'completeTraining'])->name('training.complete');
});

Route::middleware([
    config('jetstream.auth_session'),
    StudyAuth::class,
    RedirectToTheRightPhase::class,
    //'log'  //removed since we don't need to save URL logs
])->group(function () {
    Route::get('/questionnaire/{step}', [QuestionnairesController::class, 'showQuestionnaire'])
    ->name('questionnaire');

    Route::get('/', function(){
        session(['startStudy' => true]);
        return view("welcome");
    })->name("welcome");

    Route::get('/training', [TrainingController::class, 'showTraining'])->name('training.show');

    Route::get('/goodbye', [StudyController::class, 'endStudy'])->name('goodbye');

    Route::get('/{folder?}/{id?}', [MailController::class, 'show'])->name('emails');
});

