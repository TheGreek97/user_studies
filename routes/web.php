<?php


use App\Http\Controllers\BFI2XSController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\Questionnaire;
use App\Http\Controllers\QuestionnairesController;
use App\Http\Controllers\StPIIBController;
use App\Http\Controllers\TEIQueSFController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingReactionController;
use App\Http\Middleware\RedirectIfStudyCompleted;
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

Route::get('/information-sheet', [\App\Http\Controllers\Controller::class, 'download_info_sheet'])
    ->name('download_information_sheet');


Route::get('/', function () {
    $prolific_pid = request()->input('PROLIFIC_PID');
    if ($prolific_pid) {
        return redirect()->route('welcome')
            ->with(['PROLIFIC_PID' => $prolific_pid]);
    } else {
        return redirect()->route('welcome');
    }
});

Route::get('/no-consent', function () {
    return view("informed_consent_declined");
})->name('no_consent');

Route::get('/consent-grant', function (){
    session(['consent' => '1']);
    session(['startStudy' => '1']);
    return redirect(route('questionnaire', ['step' => 1]));
    //return redirect(route('show', ['folder' => 'inbox']));
})->name('consent');

Route::middleware([
    config('jetstream.auth_session'),
])->group(function (){
    Route::get('/warning_log', [MailController::class, 'warningLog'])->name('warning_log');
});

// Route::get('/goodbye', function () {
//     if (Auth::check() && Auth::user()->study_completed === null) {
//         return redirect()->route('next_step');
//     }

//     // Redirect based on session flag
//     if (session()->has('study_already_taken')) {
//         return view('sorry_page');
//     }

//     return view("thank_you_page");
// })->name('thank_you');


Route::middleware([
    config('jetstream.auth_session'),
    StudyAuth::class,
    RedirectIfStudyCompleted::class,
    //'log'  //removed since we don't need to save URL logs
])->group(function () {
    Route::get('/welcome', function () {
        if (session()->has('consent')) {
            //return redirect(route('show', ['folder' => 'inbox']));
            return redirect(route('questionnaire', ['step' => 1]));
        } else {
            return view('welcome');
        }
    })->name('welcome');

    Route::get('/nextstep/{id?}', function ($id = null){
        if($id === null) {
            return redirect(route('show', ['folder' => 'inbox']));
        }
        return view("emailquestionnaire")->with('warning_type', Auth::user()->warning_type);
    })->name('next_step');

    Route::post('/nextstep/{mail?}', [Questionnaire::class, 'storeEmailQuestionnaire']); //->name('next_step');

    Route::get('/goodbye', function (){
        if (Auth::check() && Auth::user()->study_completed === null) {
            return redirect()->route('show', ['folder' => 'inbox']);
        }

        // Redirect based on session flag
        if (session()->has('study_already_taken')) {
            return view('sorry_page');
        }
        session(['study_already_taken' => true]);
        return view("thank_you_page");
    })->name('thank_you');

    Route::get('/expelled', [QuestionnairesController::class, 'expelUser'])->name('expelUser');

    Route::get('/set-post-phase', function () {
        session(['training_done' => true]);
        session(['startStudy' => '1']); //show again the popup message for the post classification
        return redirect()->route('show', ['folder' => 'inbox']);
    })->name('setPostPhase');

    Route::get('/end', [Questionnaire::class, 'showFollowUp'])->name('post_test');

    Route::post('/end', [Questionnaire::class, 'storeFollowUp']);

    Route::post('/demographics', [QuestionnairesController::class, 'saveDemographicsData'])->name('demographics.create');
    Route::post('/big-five-inventory', [BFI2XSController::class, 'create'])->name('big-five-inventory.create');
    Route::post('/susceptibility-to-persuasion-ii', [StPIIBController::class, 'create'])->name('susceptibility-to-persuasion-ii.create');
    Route::post('/trait-emotional-intelligence', [TEIQueSFController::class, 'create'])->name('trait-emotional-intelligence.create');
    Route::post('/training-reaction-questionnaire', [TrainingReactionController::class, 'create'])->name('training-reaction-questionnaire.create');

    Route::get('/questionnaire/{step}', [QuestionnairesController::class, 'showQuestionnaire'])
    ->name('questionnaire');

    Route::post('/save-email-classification', [QuestionnairesController::class, 'saveEmailClassification'])->name('save-email-classification');
    //Route::get('/final-data', [QuestionnairesController::class, 'finalData'])->name('final-data');
    Route::post('/save-final-data', [QuestionnairesController::class, 'saveFinalData'])->name('save-final-data');

    Route::get('/training', [TrainingController::class, 'showTraining'])->name('training');
    Route::get('/create-training', [TrainingController::class, 'createTraining'])->name('training_create');
    Route::get('/complete-training', [TrainingController::class, 'completeTraining'])->name('training_complete');

    Route::get('/{folder?}/{id?}', [MailController::class, 'show'])->name('show');
});

