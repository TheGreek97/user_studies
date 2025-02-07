<?php

namespace App\Http\Controllers;

use App\Models\ActivityLogs;
use App\Models\Email;
use Carbon\Carbon;
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
        $auth_user = Auth::user();
        info("ID " . $id);
        $emails = DB::table('emails')
            ->where('type', $folder)
            ->get();
        $seed = (int) Auth::id();  // in this way, the order is randomized according to the user id
        MailController::seededShuffle($emails, $seed);

        // preprocess the emails
        foreach ($emails as $k => $e) {
            // Set the date to the email
            $e->date = $this->get_near_date();
            // replace the name in the email parts with the name of the user
            $e->content = str_replace("{user_name}", $auth_user->name, $e->content);
            $e->subject = str_replace("{user_name}", $auth_user->name, $e->subject);
            $e->preview_text = str_replace("{user_name}", $auth_user->name, $e->preview_text);

            // RENDER DATES
            // Replace {now_email_datetime} with the current date time of the email - 3 minutes.
            $email_current_datetime = date('l d F Y H:i', strtotime($e->date . ' -3 minutes')); // Format e.g. "Monday 26 August 2024 20:21"
            $e->content = str_replace("{now_email_datetime}", $email_current_datetime, $e->content);
            // Replace {yesterday_email_date} with the previous date wrt the email's date
            $email_yesterday_date = date('d/F/Y', strtotime($e->date . ' -1 day')); // Format e.g. "26/08/2024
            $e->content = str_replace("{yesterday_email_date}", $email_yesterday_date, $e->content);

            // Set the explanation message for phishing emails
            if ($e->show_warning) {
                $counterfactual = $auth_user->explanation_type == "counterfactual";
                if ($auth_user->show_explanation) {
                    if ($auth_user->show_details == "no") {
                        $e->warning_explanation = $this->get_explanation($e->phishing_feature, $auth_user->llm, $counterfactual);
                    } else {
                        $e->warning_explanation = $this->get_detailed_explanation($e->phishing_feature, $auth_user->llm, $counterfactual);
                    }
                } else {  // if no specific explanation will be shown, just show a generic message
                    $e->warning_explanation = "This email was blocked because it may trick you into doing something dangerous like installing software or revealing personal information like passwords or credit cards.";
                }
            }

            $emails[$k] = $e;
        }
        if (!in_array($folder, ['inbox', 'sent', 'draft', 'trash']))
            return redirect(route('show', ['folder' => 'inbox', 'emails' => $emails]));
        if ($id != null) {    // Show a specific e-mail (this can probably be optimized)
            $email = null;
            // search the email in the collection of emails
            foreach($emails as $e) {
                if ($e->id == $id) {
                    $email = $e;
                    break;
                }
            }
            if ($email != null) {
                // if email was found, show the email page
                $email->warning_type = $auth_user->warning_type;
                return view('email_page', ['folder' => $folder, 'emails' => $emails, 'selected_email' => $email]);
            } else {
                // else show all the emails
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
        } else {  // If all emails have been seen by the participant, show them the last part of the study
            if ($auth_user->followUpQuestionnaire != null) {
                return redirect(route('thank_you'));  // Be sure that the user has not filled in the questionnaire already
            } else {
                return redirect(route('debriefing'));  // the questionnaires come after the debriefing
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
        $user = Auth::User();
        if (str_starts_with($action, "warning_shown") || str_starts_with($action, "tooltip_shown")) {
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
            $user->ignored_warning = true;
            $user->save();
        }
        $log->user_action = $this->get_user_action($action);
        $log->email_id = $request->input('email_id');
        $log->save();
    }

    private function seededShuffle(&$array, int $seed): void
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

    private function get_user_action(string $key): string{
        switch ($key) {
            case "clicked_link":
                return "Clicked link in email";
            case "warning_shown":
                return "Warning shown";
            case "warning_ignored":
                return "Warning ignored";
            case 'tooltip_shown':  // the actual action received was tooltip_shown_{email}
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

    private function get_explanation (string $feature, string $llm, bool $counterfactual=false): string
    {
        if ($counterfactual) {  // Counterfactuals
            $explanations = match ($feature) {
                'ip_url' => [
                    "llama3_3" => "The link http://92.233.24.33 is a string of numbers instead of a normal website name. The email would have been considered safe if it used a recognizable website name, like a company's official name, which helps verify the link's authenticity. A safe link might have looked like: https://instagram.com/login",
                    "claude3_5" => "The link in the email points to a string of numbers (92.233.24.33/instagram/login.php) instead of the official Instagram website name. The email would have been safe if the link used Instagram's actual website name, which helps users verify they're going to the real Instagram site. A safe link would have looked like: https://instagram.com/account/reset"
                ],
                'link_mismatch' => [
                    "llama3_3" => "The link text \"protect your account\" hides the actual link https://www.facebook.com/hacked/disavow?u=100000125023309&nArdInDS2&lit_IT&ext1548538159. The email would have been considered safe if the link text matched the actual link, which helps ensure the link is trustworthy and not trying to trick you. A safe link might have looked like: https://www.facebook.com/account-security",
                    "claude3_5" => "The email shows a Facebook link but actually takes you to a different website: appears as 'facebook.com/hacked' but leads to 'phish-site.net/fake-login'. The email would have been safe if the link you see matched exactly where it takes you, which helps ensure you're going to the real Facebook website. A safe link would show and lead to the same place, like: facebook.com/help/security"
                ],
                'tld_mispositioned' => [
                    "llama3_3" => "The link https://amazonservices.com.cz/account.php has a suspicious web address with a \".com\" in the wrong place. The email could have been considered safe if the \".com\" was at the end of the address. A safe link might have looked like: https://amazon.com/account-update",
                    "claude3_5" => "The link 'amazonservices.com.cz' tries to trick you by putting '.com' in the middle instead of at the end. The email would have been considered safe if the website address ended with '.com' or matched Amazon's official website format. A safe link from Amazon would look like: https://amazon.com/account or https://amazon.it/account"
                ]
            };
        } else {  // Feature-based
            $explanations = match ($feature) {
                'ip_url' => [
                    "llama3_3" => "The link http://92.233.24.33 is a string of numbers instead of a normal website name. This site might be fake and try to trick you. You might be giving away your private information.",
                    "claude3_5" => "The link shows numbers (92.233.24.33) instead of \"instagram.com\" like real Instagram emails would use. This is a deceptive trick to send you to a fake website. If you enter your account details there, attackers will gain control of your Instagram account."
                ],
                'link_mismatch' => [
                    "llama3_3" => "The link \"https://www.facebook.com/hacked/disavow?u=100000125023309&nArdInDS2&lit_IT&ext1548538159\" is an imitation of the original Facebook link. This site might be intended to take you to a different place. You might be disclosing private information.",
                    "claude3_5" => "The link shown as 'protect your account' may take you somewhere different than what you see. Attackers use this trick to make you think you're going to Facebook when you're not. This could lead to your Facebook password being stolen."
                ],
                'tld_mispositioned' => [
                    "llama3_3" => "The link https://amazonservices.com.cz/account.php has a strange company name extension. This site might be pretending to be something it's not. You might be disclosing private information.",
                    "claude3_5" => "The website amazonservices.com.cz is trying to look like a real Amazon page by using 'amazon' in its address. This is a trick to make you think you're visiting Amazon when you're not. If you enter your login details, scammers could take control of your real Amazon account."
                ]
            };
        }

        $explanation = $explanations[$llm];  // choose the explanation of the related llm
        return $explanation;
    }

    /**
     * @param string $feature the name of the feature
     * @param string $llm the name of the LLM
     * @return string The detailed explanation in form of an image to be rendered
     */
    private function get_detailed_explanation(string $feature, string $llm, bool $counterfactual = false) : string {
        $subfolder = $counterfactual ? "counterfactual" : "feature_based";
        $img_path = asset("assets/img/detailed_warnings/$subfolder/$feature - $llm.png");
        $detailed_explanation = `<img style="display: block; margin-left: auto; margin-right: auto;" src="$img_path" alt="" width="300" height="165" />`;
        return $detailed_explanation;
    }

    private function get_near_date(): string
    {
        return Carbon::today()  // returns the date of today at 00:00
        ->subDays(mt_rand(1, 15))  // get a date between 1 and 15 days in the past
        ->addMinutes(mt_rand(540, 1320))  // add between 540 (09:00) and 1320 (22:00) minutes to 00:00
        ->toDateTimeString();
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
}
