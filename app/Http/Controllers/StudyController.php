<?php

namespace App\Http\Controllers;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class StudyController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function redirectUser(Request $request, Closure $next) {
        return $next($request);
    }

    public function download_info_sheet(Request $request)
    {
        //Storage::disk('local')->put('example.txt', 'Contents');
        $file_name = "information-sheet-for-anonymous-studies.pdf";
        //$file_public_path = asset('storage/'. $file_name);
        try {
            return response()->file("storage/$file_name");
        } catch (FileNotFoundException) {
            abort(404, 'File not found');
        }
    }

    public function expelUser(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->expelled = true;
        }
        return view('expelled');
    }

    public function giveConsent(Request $request)
    {
        $user = Auth::user();
        $user->given_consent = now();
        $user->save();
        return redirect()->route('questionnaire', ["step" => 0]);
    }

    public function endStudy(Request $request) {
        $user = Auth::user();
        if (! $user->study_completed) {
            $user->study_completed = Carbon::now();
            $user->save();
        }
        if (session()->has('study_already_taken')) {
            return view('sorry_page');
        }
        session(['study_already_taken' => true]);
        return view("thank_you_page");
    }
}
