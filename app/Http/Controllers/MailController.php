<?php

namespace App\Http\Controllers;

use App\Models\ActivityLogs;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    const MAILS_NUMBER = 14;

    public function show($folder = 'inbox', $id = null)
    {
        if (!session()->has('consent')) {
            return redirect(route('welcome'));
        }
        info("ID " . $id);
        $emails = DB::table('emails')
            ->where('type', $folder)
            ->get();
        $seed = (int) Auth::id();  // in this way, the order is randomized according to the user id
        MailController::seededShuffle($emails, $seed);

        if (!in_array($folder, ['inbox', 'sent', 'draft', 'trash']))
            return redirect(route('show', ['folder' => 'inbox', 'emails' => $emails]));
        if ($id != null) {    // Show a specific e-mail
            $email = Email::findOr($id, function () {
                return null;
            });
            // get the phishing emails (with the right warning, i.e., passive or active) + legitimate emails
            if ($email != null) {
                $email["warning_type"] = Auth::user()->warning_type;
                return view('email_page', ['folder' => $folder, 'emails' => $emails, 'selected_email' => $email]);
            } else {
                return redirect(route('show', ['folder' => $folder, 'emails' => $emails]));
            }
        }  // Else, show the mails list
        if (count(DB::table('useremailquestionnaire')->where('user_id', Auth::id())->get()) < self::MAILS_NUMBER) {
            if (session()->has('startStudy')) {
                session()->remove('startStudy');
                return view('email_page', ['folder' => $folder, 'emails' => $emails, 'startStudy' => true]);
            } else {
                return view('email_page', ['folder' => $folder, 'emails' => $emails]);
            }
        } else {
            if (Auth::user()->followUpQuestionnaire != null) {
                return redirect(route('thankyou'));
            } else {
                return redirect(route('post_test'));
            }
        }
    }

    public function warningLog(Request $request)
    {
        $log = new ActivityLogs;
        $log->user_id = Auth::id();
        $log->url = $request->input('url');
        $log->warning_type = $request->input('warning_type');
        $action = $request->input('msg');
        if (str_starts_with($action, "warning_shown") || str_starts_with($action, "tooltip_shown")) {
            $user = Auth::User();
            // Based on the email that was shown, add a bit value to the $user->shown_warning property
            if ($request->input('email_id') == 12)  // Instagram email
                $warning_shown_value = 1;  // 001
            else if ($request->input('email_id') == 13)  // Amazon email
                $warning_shown_value = 2;  // 010
            else if ($request->input('email_id') == 14)  // Facebook email
                $warning_shown_value = 4;  // 100
            else   // handle erroneous values = treat it as if no warning was shown
                $warning_shown_value = 0;  // 000

            if ($user->shown_warning + $warning_shown_value <= 7) {  // Check if the sum is maximum = 7
                $user->shown_warning += $warning_shown_value;
            }
            $user->save();
        }
        if (str_starts_with($action, "warning_ignored") || str_starts_with($action, "tooltip_click")) {
            $user = Auth::User();
            $user->ignored_warning = true;
            $user->save();
        }
        $log->user_action = $this->get_user_action($action);
        $log->email_id = $request->input('email_id');
        $log->save();
    }

    /*
        public function warning_browser(Request $request)
        {
            $decodedurl = urldecode($request->input('url'));
            $backurl = urldecode($request->input('backurl'));
            $parsedurl = parse_url($decodedurl);
            $hostname = $parsedurl['host'];
            $log = new ActivityLogs;
            $log->user_id = Auth::id();
            $log->url = $request->url();
            $log->warning_type = "browser_native";
            $log->user_action = "Warning mostrato";
            $log->email_id = $request->input('email_id');
            $log->save();
            return view('chrome_warning', ['url' => $decodedurl, 'hostname' => $hostname, 'backurl' => $backurl, 'email_id' => $request->input('email_id')]);
        }
    */
    private function seededShuffle(&$array, $seed)
    {
        // Generate an array of random numbers based on the seed
        srand($seed);
        $randomNumbers = [];
        for ($i = 0; $i < count($array); $i++) {
            $randomNumbers[] = rand();
        }
        $array_list= $array->all();
        // Sort the array using the random numbers as keys
        array_multisort($randomNumbers, $array_list);
        $array = collect($array_list);
    }

    private function get_user_action($key){
        switch ($key) {
            case "clicked_link":
                return "Clicked link in email";
            case "warning_shown":
                return "Warning shown";
            case "warning_ignored":
                return "Warning ignored";
            case 'tooltip_shown':
                return "Tooltip shown";
            case 'tooltip_click':
                return "Tooltip clicked";

            case 'back_safety':
                return "Back to safety btn clicked";
            case 'hide_details':
                return "Hide details btn clicked ";
            case 'show_details':
                return "Details btn clicked";
            default:
                return filter_var($key, FILTER_SANITIZE_STRING);
        }
    }

}
