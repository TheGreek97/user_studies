<?php

namespace App\Http\Controllers;

use App\Models\ActivityLogs;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MailController extends Controller
{
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
        if(count(DB::table('useremailquestionnaire')->where('user_id', \Illuminate\Support\Facades\Auth::id())->get()) < 10)
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
        $log->user_action = $request->input('msg');
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
}
