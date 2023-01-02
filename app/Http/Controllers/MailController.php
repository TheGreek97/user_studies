<?php

namespace App\Http\Controllers;

use App\Models\ActivityLogs;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MailController extends Controller
{
    const MAILS_NUMBER = 14;

    public function show($folder = 'inbox', $id = null){
        info("ID ".$id);
        if(!in_array($folder, ['inbox', 'sent', 'draft', 'trash']))
            return redirect(route('show', ['folder' => 'inbox']));
        if($id != null){
            $email = Email::findOr($id, function () { return null; });

            if($email != null && ($email->warning_type == Auth::user()->warning_type || $email->warning_type == null)) {
                return view('email_page', ['folder' => $folder, 'selected_email' => $email]);
            }
            else
                return redirect(route('show', ['folder' => $folder]));
        }
        if(count(DB::table('useremailquestionnaire')->where('user_id', \Illuminate\Support\Facades\Auth::id())->get()) < self::MAILS_NUMBER)
            return view('email_page', ['folder' => $folder]);
        else {
            if(Auth::user()->followUpQuestionnaire != null)
                return redirect(route('thankyou'));
            else
                return redirect(route('post_test'));
        }
    }

    public function warningLog(Request $request){
        $log = new ActivityLogs;
        $log->user_id = Auth::id();
        $log->url = $request->input('url');
        $log->warning_type = $request->input('warning_type');
        $action = $request->input('msg');
        if (str_starts_with($action, "warning_shown") ||  str_starts_with($action, "tooltip_shown")) {
            $user = Auth::User();
            $user->shown_warning = true;
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
