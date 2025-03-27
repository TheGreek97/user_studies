<?php

namespace App\Http\Controllers;

use App\Models\ActivityLogs;
use App\Models\Email;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as FRequest;

class MailController extends Controller
{
    const MAILS_NUMBER = 2;

    public function show($folder = 'inbox', $id = null)
    {
        // Logging to track redirection logic along with session data for debugging
        //Log::info('Current route: ' . FRequest::url());
        //Log::info('Current session:', session()->all());

        if (session()->has('expelled')) {
            return redirect(route('expelUser'));
        }

        if (!session()->has('consent')) {
            return redirect(route('welcome'));
        }
        $auth_user = Auth::user();
        $seed = (int) Auth::id();  // Randomize according to user id
        info("ID " . $id);

        // session([
        //     'questionnaire_1done' => true,
        //     'questionnaire_2done' => true,
        //     'questionnaire_3done' => true,
        // ]);

        //First show the 3 questionnaires
        if (!session()->has('questionnaire_0done')) {
            return redirect()->route('questionnaire', ['step' => 0]);
        } elseif (!session()->has('questionnaire_1done')) {
            return redirect()->route('questionnaire', ['step' => 1]);
        } elseif (!session()->has('questionnaire_2done')) {
            return redirect()->route('questionnaire', ['step' => 2]);
        } elseif (!session()->has('questionnaire_3done')) {
            return redirect()->route('questionnaire', ['step' => 3]);
        }

        // Start training generation after questionnaire 3 is completed
        if (session()->has("questionnaire_3done") && !session()->has("training_generation_started")){
            return redirect()->route("training_create");
        }

        // Study completed
        if(session()->has('questionnaire_4done') ) {
            return redirect()->route('save-final-data');
        }

        //Training Reaction Questionnaire
        if (session()->has('post_phase_done') ) {
            return redirect()->route('questionnaire', ['step' => 4]);
        }
        if (session()->has('pre_phase_done') and !session()->has('training_done')) {
            return redirect()->route('training');
        }
        //Else: EMAIL CLASSIFICATION

        session_start();
        unset($_SESSION['emails']);
        //Divide emails proportionally into pre- and post-test groups and return the appropriate group
        if (!isset($_SESSION['emails'])) {
            $_SESSION['emails'] = $this->retrieveEmailsForThePhase($folder);
        }
        $emailGroups = $_SESSION['emails'];

         //ASSIGN A GROUP OF EMAILS
        if(!session('pre_phase_done')){
            //PRE-CLASSIFICATION
            $emails = collect($emailGroups['pre']);
            MailController::seededShuffle($emails, $seed);
            // Shuffle again for pre-test group (to remove the predefinited order of groups)
            Log::info("Final pre-test email count: " . $emails->count());
        } else {
            //POST-CLASSIFICATION
            $emails = collect($emailGroups['post']);
            MailController::seededShuffle($emails, $seed);
            // Shuffle again for post-test group (to remove the predefinited order of groups)
            Log::info("Final post-test email count: " . $emails->count());
        }

        $placeholders = ['{USER NAME}', '{USERNAME}', '{username}', '{user name}'];
        // preprocess the emails
        foreach ($emails as $k => $e) {
            // Set the date to the email
            $e->date = $this->get_near_date();
            // replace the name in the email parts with the name of the user
            // Replace the placeholders in the content, subject, and preview_text
            foreach ($placeholders as $placeholder) {
                $e->content = str_replace($placeholder, $auth_user->name, $e->content);
                $e->subject = str_replace($placeholder, $auth_user->name, $e->subject);
                $e->preview_text = str_replace($placeholder, $auth_user->name, $e->preview_text);
            }

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
                //  // Get the email file path
                return view('email_page', ['folder' => $folder, 'emails' => $emails, 'selected_email' => $email, 'htmlContent' => $email->content]);
            } else {
                // else show all the emails
                return redirect(route('show', ['folder' => $folder, 'emails' => $emails]));
            }
        }  // Else, show the mails list
        if(!session('pre_phase_done')){
            //PRE-CLASSIFICATION
            if (count(DB::table('useremailquestionnaire')->where('user_id', Auth::id())->get()) < self::MAILS_NUMBER) {
                if (session()->has('startStudy')) {
                    session()->remove('startStudy');
                    return view('email_page', ['folder' => $folder, 'emails' => $emails, 'startStudy' => true]);
                } else {
                    return view('email_page', ['folder' => $folder, 'emails' => $emails]);
                }
            } else {  // If all emails have been seen by the participant, show them the training
                    session(['pre_phase_done' => true]);
                    return redirect(route('training'));
            }
        } else {
            //POST-CLASSIFICATION    (double the count of answers for user)
            if (count(DB::table('useremailquestionnaire')->where('user_id', Auth::id())->get()) <  (self::MAILS_NUMBER*2)) {
                if (session()->has('startStudy')) {
                    session()->remove('startStudy');
                    return view('email_page', ['folder' => $folder, 'emails' => $emails, 'startStudy' => true]);
                } else {
                    return view('email_page', ['folder' => $folder, 'emails' => $emails]);
                }
            } else {
                //Training Reaction Questionnaire
                session(['post_phase_done' => true]);
                return redirect()->route('questionnaire', ['step' => 4]);
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

    /**
     * Subdivide emails into pre and post-test groups based on phishing, counterpart, and difficulty level.
     *
     * @param string $folder The folder to filter emails by.
     * @return \Illuminate\Support\Collection A shuffled collection of emails.
     */
    public function retrieveEmailsForThePhase($folder)
    {
        // Retrieve the emails from the database
        $emails = DB::table('emails')
            ->where('type', $folder)
            ->get();
        Log::info("Total emails retrieved: " . $emails->count());
        $seed = (int) Auth::id();  // Randomize according to user id

        // subdivision by phishing
        $phishing_emails = $emails->where('phishing', 1);
        $genuine_emails = $emails->where('phishing', 0);

        // subdivision by counterpart (1 = has a counterpart, 0 = no counterpart)
        $c_phishing_emails = $phishing_emails->where('counterpart', 1);
        $not_c_phishing_emails = $phishing_emails->where('counterpart', 0);
        $c_genuine_emails = $genuine_emails->where('counterpart', 1);
        $not_c_genuine_emails = $genuine_emails->where('counterpart', 0);

        Log::info("Phishing emails: " . $phishing_emails->count());
        Log::info("Genuine emails: " . $genuine_emails->count());
        Log::info("Counterpart phishing emails: " . $c_phishing_emails->count());
        Log::info("No counterpart phishing emails: " . $not_c_phishing_emails->count());
        Log::info("Counterpart genuine emails: " . $c_genuine_emails->count());
        Log::info("No counterpart genuine emails: " . $not_c_genuine_emails->count());

        // Final subdivision by difficulty_level (low, medium, high)
        $groupedEmails = [
            'phishing' => [
                'counterpart' => [
                    'easy' => $c_phishing_emails->where('difficulty_level', 'easy'),
                    'medium' => $c_phishing_emails->where('difficulty_level', 'medium'),
                    'hard' => $c_phishing_emails->where('difficulty_level', 'hard'),
                ],
                'no_counterpart' => [
                    'easy' => $not_c_phishing_emails->where('difficulty_level', 'easy'),
                    'medium' => $not_c_phishing_emails->where('difficulty_level', 'medium'),
                    'hard' => $not_c_phishing_emails->where('difficulty_level', 'hard'),
                ],
            ],
            'genuine' => [
                'counterpart' => [
                    'medium' => $c_genuine_emails->where('difficulty_level', 'medium'),
                    'easy' => $c_genuine_emails->where('difficulty_level', 'easy'),
                ],
                'no_counterpart' => [
                    'medium' => $not_c_genuine_emails->where('difficulty_level', 'medium'),
                    'easy' => $not_c_genuine_emails->where('difficulty_level', 'easy'),
                ],
            ],
        ];

        // Initialize pre and post-test emails arrays
        $pre_test_emails = [];
        $post_test_emails = [];

        //Shafle groupedEmails
        foreach ($groupedEmails as $category => &$types) { // phishing / genuine
            foreach ($types as $counterpartType => &$difficultyGroups) { // counterpart / no_counterpart
                foreach ($difficultyGroups as $difficulty => &$emails) { // low / medium / high
                    MailController::seededShuffle($emails, $seed); // Shuffle the emails
                }
            }
        }

        // Prima di tutto appiattiamo i gruppi:
        $flattenedGroups = $this->flattenGroups($groupedEmails);

        static $iterationCount = 0;
        $allowedDeviations = 0; #per rilassare 2 email per topic per fase se necessario
        $maxDeviations = 5; // massimo numero di deviazioni consentite
        do {
            // Eseguiamo la procedura per l'insieme "pre":
            $assignmentPre = $this->backtrackAssignment($flattenedGroups, 0, [], [], [], 2, $allowedDeviations, $iterationCount, 0);
            if ($assignmentPre === false) {
                Log::warning("backtrackAssignment fallito con allowedDeviations={$allowedDeviations}, riprovo rilassando il vincolo...");
                $allowedDeviations++;
            }
        } while ($assignmentPre === false && $allowedDeviations <= $maxDeviations);

        if ($assignmentPre === false) {
            Log::warning("backtrackAssignment PRE fallito del tutto!");
        } else {
            Log::info("Numero totale di iterazioni di backtracking per PRE: " . $iterationCount);

            // echo "Soluzione per PRE:\n";
            // foreach ($assignmentPre as $groupKey => $emails) {
            //     echo "Gruppo: $groupKey\n";
            //     foreach ($emails as $email) {
            //         echo "- Email: " . $email->id . " (Topic: " . $email->topic . ")\n";
            //     }
            // }

            $usedEmails = []; // Raccogliamo le email usate nell'assegnazione PRE
            foreach ($assignmentPre as $groupKey => $emails) {
                foreach ($emails as $email) {
                    $usedEmails[] = $email->id;
                    // Aggiungi la controparte, se esiste
                    if (isset($email->Counterpart_Email_ID)) {
                        $usedEmails[] = $email->Counterpart_Email_ID;
                    }
                }
            }

            // Rimuoviamo le email già usate dall'insieme per "post"
            $flattenedGroupsForPost = $this->removeUsedEmails($flattenedGroups, $usedEmails);

            // Analogamente, si esegue l'assegnazione per l'insieme "post".
            static $iterationCount = 0;
            $allowedDeviations = 0;
            $maxDeviations = 5;
            do {
                $assignmentPost = $this->backtrackAssignment($flattenedGroupsForPost, 0, [], [], [], 2, $allowedDeviations, $iterationCount, 1);
                if ($assignmentPost === false) {
                    Log::warning("backtrackAssignment fallito con allowedDeviations={$allowedDeviations}, riprovo aumentando...");
                    $allowedDeviations++;
                }
            } while ($assignmentPost === false && $allowedDeviations <= $maxDeviations);

            if ($assignmentPost === false) {
                Log::warning("backtrackAssignment POST fallito del tutto!");
            } else {
                Log::info("Numero totale di iterazioni di backtracking per POST: " . $iterationCount);

                $pre_test_emails = collect($assignmentPre)->flatten(1)->all();
                $post_test_emails = collect($assignmentPost)->flatten(1)->all();
            }

            // if ($assignmentPost === false) {
            //     echo "Nessuna soluzione valida trovata per l'insieme POST.";
            // } else {
            //     echo "Soluzione per POST:\n";
            //     foreach ($assignmentPost as $groupKey => $emails) {
            //         echo "Gruppo: $groupKey\n";
            //         foreach ($emails as $email) {
            //             echo "- Email: " . $email->id . " (Topic: " . $email->topic . ")\n";
            //         }
            //     }
            // }

        }

        // Return the shuffled collection of emails
        return [
            'pre'  => $pre_test_emails,
            'post' => $post_test_emails,
        ];
    }

       /**
         * Funzione per "appiattire" i gruppi in un array lineare, in modo da
         * poter iterare facilmente durante il backtracking.
         */
        function flattenGroups($groupedEmails) {
            $groups = [];
            foreach ($groupedEmails as $mainCategory => $types) {
                foreach ($types as $counterpartStatus => $levels) {
                    foreach ($levels as $difficulty => $emails) {
                        // Ogni gruppo è identificato da una stringa unica (es. phishing_counterpart_easy)
                        $groupKey = "{$mainCategory}_{$counterpartStatus}_{$difficulty}";
                        // Imposta required = 2 per g_c_m e g_n_e
                        $required_pre = ($groupKey === 'genuine_counterpart_easy' || $groupKey === 'genuine_no_counterpart_medium') ? 2 : 1;
                        $required_post = ($groupKey === 'genuine_counterpart_medium' || $groupKey === 'genuine_no_counterpart_easy') ? 2 : 1;

                        // Inseriamo anche metadati utili per i vincoli (es. se appartiene a counterpart)
                        $groups[] = [
                            'key' => $groupKey,
                            'mainCategory' => $mainCategory,
                            'counterpart' => ($counterpartStatus === 'counterpart'),
                            'difficulty' => $difficulty,
                            // La lista delle email per questo gruppo
                            'emails' => $emails->all(), // supponendo che ->all() restituisca un array di email
                            // Numero richiesto di email da estrarre (adattabile)
                            'required_pre' => $required_pre,
                            'required_post' => $required_post
                        ];
                    }
                }
            }
            return $groups;
        }

        /**
         * Genera tutte le combinazioni di dimensione $k da un array $arr.
         */
        function combinations(array $arr, int $k) {
            if ($k === 0) {
                return [[]];
            }
            if (count($arr) < $k) {
                return [];
            }
            $result = [];
            $keys = array_keys($arr); // Otteniamo le chiavi originali
            for ($i = 0; $i <= count($arr) - $k; $i++) {
                $key = $keys[$i]; // Usiamo la chiave originale
                $head = $arr[$key];//$head = $arr[$i];
                $tailCombs = $this->combinations(array_slice($arr, $i + 1), $k - 1);
                foreach ($tailCombs as $comb) {
                    array_unshift($comb, $head);//$comb[$key] = $head;//array_unshift($comb, $head);
                    $result[] = $comb;
                }
            }
            return $result;
        }


        /**
         * Funzione di backtracking per assegnare le email a un insieme (pre o post)
         *
         * @param array $groups          Lista dei gruppi appiattiti
         * @param int   $groupIndex      Indice corrente del gruppo da processare
         * @param array $assignment      Soluzione parziale: array associativo group_key => [email scelte]
         * @param array $topicCounts     Array che conta le occorrenze per topic nell'insieme corrente
         * @param array $usedCounterparts Array per tracciare i Counterpart_Email_ID già usati in gruppi counterpart.
         * @param int   $targetTopicCount Numero target per ciascun topic (ad es. 2)
         * @param int   $allowedDeviations  Numero di Topic che possono violare la condizione di presenza 2 volte
         * @param int   $iterationCount  Contatore globale delle iterazioni del backtracking
         * @param int   $pre_or_post     Requisiti split, 0 per pre, 1 per post
         *
         * @return mixed                 Soluzione completa (assignment) oppure false se nessuna soluzione valida
         */
        function backtrackAssignment($groups, $groupIndex, $assignment, $topicCounts, $usedCounterparts, $targetTopicCount = 2, $allowedDeviations = 0, &$iterationCount, $pre_or_post) {
            $iterationCount++;
            Log::info("Entrata in backtrackAssignment - Group Index: {$groupIndex}");
            if ($groupIndex === count($groups)) {
                Log::info("Verifica finale topicCounts: " . json_encode($topicCounts));

                $violations = 0; // Contatore per i topic che non rispettano esattamente $targetTopicCount
                //$allowedDeviations = 1; // Numero massimo di topic che possono non rispettare il target

                // Verifica finale: ogni topic deve comparire esattamente $targetTopicCount volte
                foreach ($topicCounts as $topic => $count) {
                    if ($count !== $targetTopicCount) {
                        Log::warning("Topic '{$topic}' ha count {$count}, atteso: {$targetTopicCount}.");
                        $violations++;
                        //return false; //nel passo precedente prova un altra combinazione
                    }
                }

                if ($violations > $allowedDeviations) {
                    Log::warning("Troppe deviazioni ({$violations} su {$allowedDeviations} consentite). Fallimento!");
                    return false; // Nel passo precedente prova un'altra combinazione
                }

                Log::info("Assegnamento finale valido con {$violations} deviazioni permesse: " . json_encode($assignment));
                return $assignment;
            }

            $group =    $groups[$groupIndex];
            $groupKey = $group['key'];
            $required = ($pre_or_post == 0) ? $group['required_pre'] : $group['required_post'];

            Log::info("Analizzando gruppo: {$groupKey}, richieste: {$required}, emails disponibili: " . count($group['emails']));
            // Se il gruppo non ha abbastanza email, abortiamo.
            if (count($group['emails']) < $required) {
                Log::error("Gruppo {$groupKey} ha solo " . count($group['emails']) . " email, richieste: {$required}");
                return false;
            }

            // Genera tutte le possibili combinazioni di email per il gruppo corrente.
            $combinations = $this->combinations($group['emails'], $required);

            // Stampa le combinazioni con solo gli ID
            Log::info("Generato " . count($combinations) . " combinazioni per {$groupKey}");

            foreach ($combinations as $comboIndex => $combo) {
                Log::info("Analizzando combinazione #{$comboIndex} per {$groupKey}: ");
                $validCombo = true;
                $topicCountsCopy = $topicCounts;
                $usedCounterpartsCopy = $usedCounterparts;

                // Verifica i vincoli per ciascuna email nella combinazione
                foreach ($combo as $email) {
                    // Verifica il vincolo del topic: se aggiungendo questa email si supera il conteggio target, scarta.
                    $emailTopic = $email->topic; //$email->topic;$email['topic'];
                    $newCount = isset($topicCounts[$emailTopic]) ? $topicCounts[$emailTopic] + 1 : 1;
                    Log::info("Email {$email->id} - Topic: '{$emailTopic}', Nuovo count: {$newCount}");

                    if ($newCount > $targetTopicCount) {
                        Log::warning("Scartata combinazione #{$comboIndex} per {$groupKey}, topic '{$emailTopic}' superato limite di {$targetTopicCount}");
                        $validCombo = false;
                        break;//continue; // scarta questa scelta
                    }
                    $topicCountsCopy[$emailTopic] = $newCount;

                    // Se il gruppo fa parte dei counterpart, controlla la regola di esclusione
                    if ($group['counterpart'] && isset($email->Counterpart_Email_ID)) { //$email->Counterpart_Email_ID $email['Counterpart_Email_ID']
                        $counterpartID = $email['Counterpart_Email_ID']; //$email->Counterpart_Email_ID;
                        // Se l'id è già usato nella controparte dell'altro gruppo, non è ammesso.
                        // se $group['mainCategory'] è 'phishing', controlliamo quelli 'genuine' e viceversa.
                        if (!empty($usedCounterparts)) {
                            // Se il counterpart è già usato da un gruppo di mainCategory differente
                            if (isset($usedCounterparts[$counterpartID]) && $usedCounterparts[$counterpartID] !== $group['mainCategory']) {
                                Log::warning("Scartata combinazione #{$comboIndex} per {$groupKey}, email {$email->id} ha counterpart ID {$counterpartID} già usato in {$usedCounterparts[$counterpartID]}");
                                $validCombo = false;
                                break;//continue;
                            }
                            // Registra il mainCategory per questo counterpart
                            $usedCounterpartsCopy[$counterpartID] = $group['mainCategory'];
                        }
                    }

                }

                if (!$validCombo) {
                    continue; // Prova la prossima combinazione
                }

                Log::info("Combinazione #{$comboIndex} per {$groupKey} è valida, procedo con la ricorsione.");
                // Aggiungi la combinazione (soluzione parziale) all'assegnazione corrente
                $assignmentCopy = $assignment;
                $assignmentCopy[$groupKey] = $combo; // per il momento scegliamo l'email

                // Procedi ricorsivamente al gruppo successivo
                $result = $this->backtrackAssignment($groups, $groupIndex + 1, $assignmentCopy, $topicCountsCopy, $usedCounterpartsCopy, $targetTopicCount, $allowedDeviations, $iterationCount, $pre_or_post);
                if ($result !== false) {
                    Log::info("Soluzione trovata e restituita.");
                    return $result;
                }

            }

            // Se nessuna scelta porta a una soluzione valida, torna indietro
            Log::warning("Nessuna combinazione valida trovata per {$groupKey}, ritorno al livello superiore.");
            return false; //nessuna combinazione del passo precedente va bene, deve andare al passo prima
        }

        function removeUsedEmails($flattenedGroups, $usedEmails) {
            foreach ($flattenedGroups as &$group) {
                // Rimuovi le email già utilizzate
                $group['emails'] = array_filter($group['emails'], function($email) use ($usedEmails) {
                    return !in_array($email->id, $usedEmails);
                });

                // Rimuovi anche le email che sono controparte nelle email già utilizzate
                $group['emails'] = array_filter($group['emails'], function($email) use ($usedEmails) {
                    // Controlla se la proprietà esiste
                    if (isset($email->Counterpart_Email_ID)) {
                        return !in_array($email->Counterpart_Email_ID, $usedEmails);
                    }
                    return true; // Se non esiste, non è da rimuovere
                });
            }

            return $flattenedGroups;
        }

    }
