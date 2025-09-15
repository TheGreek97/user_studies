<?php

use App\Http\Controllers\BFI2XSController;
use App\Http\Controllers\PhishingAssessmentController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\QuestionnairesController;
use App\Http\Controllers\StPIIBController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\TEIQueSFController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingReactionController;
use App\Http\Middleware\RedirectToTheRightPhase;
use App\Http\Middleware\StudyAuth;
use Illuminate\Support\Facades\Route;

Route::get('/information-sheet', [StudyController::class, 'download_info_sheet'])
    ->name('download_information_sheet');

Route::get('/no-consent', fn () => view("informed_consent_declined"))->name('no_consent');
Route::get('/consent-grant', [StudyController::class, 'giveConsent'])->name('consent');

Route::middleware([config('jetstream.auth_session')])->group(function (){
    Route::get('/warning_log', [MailController::class, 'warningLog'])->name('warning_log');
});

Route::get('/expelled', [StudyController::class, 'expelUser'])->name('expelUser');

Route::middleware([config('jetstream.auth_session'), StudyAuth::class])->group(function () {
    Route::post('/demographics', [QuestionnairesController::class, 'saveDemographicsData'])->name('demographics.create');
    Route::post('/phishing-assessment', [PhishingAssessmentController::class, 'store'])->name('phishing-assessment.store');
    Route::post('/training-reaction-questionnaire', [TrainingReactionController::class, 'create'])->name('training-reaction-questionnaire.create');
    Route::post('/save-email-classification', [MailController::class, 'saveEmailClassification'])->name('save-email-classification');

    Route::get('/complete-training', [TrainingController::class, 'completeTraining'])->name('training.complete');

    // status + quiz score API
    Route::get('/training/status', [TrainingController::class, 'status'])->name('training.status');
    Route::post('/training/quiz-score', [TrainingController::class, 'storeQuizScore'])->name('training.quiz_score');
});

Route::middleware([config('jetstream.auth_session'), StudyAuth::class, RedirectToTheRightPhase::class])->group(function () {
    Route::get('/questionnaire/{step}', [QuestionnairesController::class, 'showQuestionnaire'])->name('questionnaire');
    Route::get('/phishing-assessment', [PhishingAssessmentController::class, 'create'])->name('phishing-assessment.create');
    Route::get('/', [StudyController::class, 'welcomeUser'])->name("welcome");
    Route::get('/training', [TrainingController::class, 'showTraining'])->name('training.show');
    Route::get('/goodbye', [StudyController::class, 'endStudy'])->name('goodbye');
    Route::get('/{folder?}/{id?}', [MailController::class, 'show'])->name('emails');
});
