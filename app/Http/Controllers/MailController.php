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
        if(!in_array($folder, ['inbox', 'sent', 'drafts', 'trash']))
            return redirect(route('show', ['folder' => 'inbox']));
        if($id != null){
            $email = Email::findOr($id, function () { return null; });
            if($email != null && ($email->warning_type == Auth::user()->warning_type || $email->warning_type == null))
                return view('email_page', ['folder' => $folder, 'selected_email' => $email]);
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

    public function warning(Request $request){

        $log = new ActivityLogs;
        $log->user_id = Auth::id();
        $log->url = $request->input('url');
        $log->warning_type = $request->input('warning_type');
        $log->user_action = $this->get_user_action($request->input('msg'));
        $log->email_id = $request->input('email_id');
        $log->save();
    }

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

    private function get_user_action($key){
        switch ($key) {
            case "clicked_link":
                return "Clicked link in the email";
            case "warning_shown":
                return "Shown warning";
            case "warning_ignored":
                return "Ignored warning";
            case 'go_back':
                return "Clicked on btn 'Go back to the safe zone'";
            case 'advanced':
                return "Clicked on btn 'Advanced'";
            case 'hide_advanced':
                return "Clicked on btn 'Hide advanced'";
            case 'tooltip_clicked':
                return "Clicked on the tooltip";
            // Chrome warning
            case 'back_safety':
                return "Clicked on btn 'Back to safety'";
            case 'hide_details':
                return "Clicked on btn 'Hide details'";
            case 'show_details':
                return "Clicked on btn 'Details'";
            default:
                return filter_var($key, FILTER_SANITIZE_STRING);
        }
    }

}
