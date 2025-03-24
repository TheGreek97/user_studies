<?php

namespace Database\Seeders;

use PhpParser\Lexer\TokenEmulator\EnumTokenEmulator;
define("EMAIL_DIR", base_path("public/email_files"));
use App\Models\Email;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EmailSeeder extends Seeder
{
  /**
   * Run the database seeds.
   * Insert the string {USER NAME} within the email->content to render the user's name
   * Insert the string {now_email_datetime} within the email->content to render the current email's datetime (at runtime)
   * Insert the string {yesterday_email_date} within the email->content to render yesterday's date wrt the email
   * @return void
   */
  public function run()
  {
//     $totalEmails = 36;
//     $topics = [
//         'Suspicious activity', 'Payment required', 'Failed login attempts',
//         'Gift card', 'Action required', 'Tracking information'
//     ];

//     $difficulty_levels = ['easy', 'medium', 'hard'];
//     $phishingEmails = [];
//     $genuineEmails = [];

//     // **Step 1: Creazione Email di Phishing**
//     foreach ($topics as $topic) {
//         foreach ($difficulty_levels as $difficulty) {
//             $email = Email::create([
//                 'from_name' => "Phishing Sender",
//                 'from_email' => "phishing@example.com",
//                 'subject' => "Phishing Alert - $topic",
//                 'preview_text' => "Phishing attempt related to $topic",
//                 'content' => "<p>This is a phishing email about $topic.</p>",
//                 'type' => 'inbox',
//                 'difficulty_level' => $difficulty,
//                 'phishing' => 1,
//                 'counterpart' => false,
//                 'counterpart_email_id' => null,
//                 'topic' => $topic, 
//             ]);
//             $phishingEmails[$topic][$difficulty] = $email;
//         }
//     }

//     // Passo 1: Selezioniamo casualmente 6 email per ogni livello di difficoltà
//     $selectedEasy = collect($phishingEmails)->pluck('easy')->shuffle()->take(4);
//     $selectedMedium = collect($phishingEmails)->pluck('medium')->shuffle()->take(4);
//     $selectedHard = collect($phishingEmails)->pluck('hard')->shuffle()->take(4);

//     // Passo 2: Aggiorniamo il campo "counterpart" a 1 per le email selezionate
//     $emailsWithCounterpart = $selectedEasy->merge($selectedMedium)->merge($selectedHard);

//     foreach ($emailsWithCounterpart as $email) {
//         $email->counterpart = 1;
//         $email->save();
//     }

//     // **Step 2: Creazione delle Email Genuine**
//     foreach ($phishingEmails as $topic => $difficulties) {
//         foreach ($difficulties as $difficulty => $email) {
//             $counterpartEmailId = ($email->counterpart == 1) ? $email->id : null;
//             $genuineEmail = Email::create([
//                 'from_name' => "Genuine Sender",
//                 'from_email' => "genuine@example.com",
//                 'subject' => "Genuine Alert - $topic",
//                 'preview_text' => "Genuine email related to $topic",
//                 'content' => "<p>This is a genuine email about $topic.</p>",
//                 'type' => 'inbox',
//                 'difficulty_level' => 'medium', // La difficulty iniziale è medium
//                 'phishing' => 0, // Email genuina
//                 'counterpart' => $email->counterpart, // Stesso valore di counterpart dell'email di phishing
//                 'counterpart_email_id' => $counterpartEmailId, // La controparte è l'ID dell'email di phishing
//                 'topic' => $email->topic, 
//             ]);
//             $genuineEmails[$topic][$difficulty] = $genuineEmail;
//         }
//     }

//     // **Step 3: Cambia la difficoltà a "easy" per le email genuine con counterpart**
//     $counterpartEmails = Email::where('phishing', 0) // Solo email genuine
//         ->where('counterpart', 1) // Con counterpart
//         ->get(); 

//     if ($counterpartEmails->count() >= 5) { // Assicuriamoci che ci siano almeno 5 email
//         $counterpartEmailsToUpdate = $counterpartEmails->random(5);
//         foreach ($counterpartEmailsToUpdate as $email) {
//             $email->update(['difficulty_level' => 'easy']);
//             Log::info("Genuine Email Updated", [
//                 'ID' => $email->id,
//                 'New Difficulty' => 'easy'
//             ]);
//         }
//     }

//     // **Step 4: Cambia la difficoltà a "easy" per le email genuine senza counterpart**
//     $nonCounterpartEmails = Email::where('phishing', 0) // Solo email genuine
//         ->where('counterpart', 0) // Senza counterpart
//         ->get(); 

//     if ($nonCounterpartEmails->count() >= 4) { // Assicuriamoci che ci siano almeno 4 email
//         $nonCounterpartEmailsToUpdate = $nonCounterpartEmails->random(4);
//         foreach ($nonCounterpartEmailsToUpdate as $email) {
//             $email->update(['difficulty_level' => 'easy']);
//             Log::info("Genuine Email Updated", [
//                 'ID' => $email->id,
//                 'New Difficulty' => 'easy'
//             ]);
//         }
//     }

//     $emails = Email::orderBy('phishing', 'desc') // Prima ordina per phishing (prima le phishing, poi le genuine)
//     ->orderBy('topic') // Poi ordina per topic (ordine alfabetico)
//     ->orderBy('difficulty_level') // Infine ordina per difficoltà
//     ->get();

//     foreach ($emails as $email) {
//       Log::info(($email->phishing ? "Phishing" : "Genuine") . " Email Created", [
//           'Topic' => $email->topic,
//           'Difficulty' => $email->difficulty_level,
//           'ID' => $email->id,
//           'Counterpart' => $email->counterpart,
//           'Counterpart_Email_ID' => $email->counterpart_email_id ?? null
//       ]);
//   }

    //** LEGITIMATE EMAILS **
    // 1
    $email = new Email();
    $email->id=1;
    $email->from_name = "Amazon";
    $email->from_email = "no-reply@amazon.com";
    $email->subject = "Payment Declined – Order #1078945780";
    $email->preview_text = 'Hello, Your payment for the item listed below has been declined. Valid payment information must be received within 5 days, otherwise, your order will be cancelled.';
    $email->content = file_get_contents(EMAIL_DIR . "/amazon.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 31;
    $email->topic = 'Payment required';
    $email->save();

    // 2
    $email = new Email();
    $email->id=2;
    $email->from_name = "Leonardo Hotel Munich via Booking.com";
    $email->from_email = "noreply@booking.com";
    $email->subject = "You have a new message from Leonardo Hotel Munich via Booking.com";
    $email->preview_text = 'Dear customer, weve received your request for the additional service for your reservation.';
    $email->content = file_get_contents(EMAIL_DIR . "/booking.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Payment required';
    $email->save();

    // 3
    $email = new Email();
    $email->id=3;
    $email->subject = "A new device is using your account";
    $email->from_name = " eBay";
    $email->from_email = "ebay@ebay.com";
    $email->preview_text = 'Hello, {USER NAME}. We noticed a new login to your eBay.com account.';
    $email->content = file_get_contents(EMAIL_DIR . "/ebay.html");
    //$email->date = Carbon::parse('2022-08-19 15:28')->toDateTimeString();
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Failed login attempts';
    $email->save();

    // 4
    $email = new Email();
    $email->id=4;
    $email->subject = "Your Etsy Purchase (23456726491799)";
    $email->from_name = "Etsy Transactions";
    $email->from_email = "transaction@etsy.com";
    $email->preview_text = 'The seller will start working on this right away. Your order number is 23456726491799.';
    $email->content = file_get_contents(EMAIL_DIR . "/etsy.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Tracking information';
    $email->save();

    // 5
    $email = new Email();
    $email->id=5;
    $email->subject = "New sign-in on Windows device";
    $email->from_name = "Google (Google Account Security Team)";
    $email->from_email = "no-reply@accounts.google.com";
    $email->preview_text = 'A new sign-in to your Google Account was detected on a Windows device.';
    $email->content = file_get_contents(EMAIL_DIR . "/google.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 25;
    $email->topic = 'Suspicious activity';
    $email->save();

    // 6
    $email = new Email();
    $email->id=6;
    $email->subject = "Confirm email address";
    $email->from_name = "Instagram";
    $email->from_email = "no-reply@mail.instagram.com";
    $email->preview_text = 'You recently added a new email address to your Instagram account.';
    $email->content = file_get_contents(EMAIL_DIR . "/instagram.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 33;
    $email->topic = 'Action required';
    $email->save();

    // 7
    $email = new Email();
    $email->id=7;
    $email->subject = "Your LinkedIn Account Has Been Temporarily Restricted";
    $email->from_name = "Linkedin";
    $email->from_email = "noreply@linkedin.com";
    $email->preview_text = 'Dear {USER NAME}, We noticed unusual activity from your account.';
    $email->content = file_get_contents(EMAIL_DIR . "/linkedin.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 21;
    $email->topic = 'Failed login attempts';
    $email->save();

    // 8
    $email = new Email();
    $email->id=8;
    $email->subject = "A new device is using your account";
    $email->from_name = "Netflix";
    $email->from_email = "info@account.netflix.com";
    $email->preview_text = 'Hi {USER NAME}, A new device signed in to your Netflix account.';
    $email->content = file_get_contents(EMAIL_DIR . "/netflix.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Suspicious activity';
    $email->save();

    // 9
    $email = new Email();
    $email->id=9;
    $email->subject = "Reset your password";
    $email->from_name = "Nord Account";
    $email->from_email = "support@nordaccount.com";
    $email->preview_text = 'Reset your Nord Account password. ';
    $email->content = file_get_contents(EMAIL_DIR . "/nordVPN.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Action required';
    $email->save();

    // 10
    $email = new Email();
    $email->id=10;
    $email->subject = "Receipt for your payment to Ebay.com";
    $email->from_name = "PayPal";
    $email->from_email = "service@paypal.com";
    $email->preview_text = 'Hello {USER NAME} You payed 259,99 € EUR to Ebay.com.';
    $email->content = file_get_contents(EMAIL_DIR . "/paypal.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 35;
    $email->topic = 'Suspicious activity';
    $email->save();

    // 11
    $email = new Email();
    $email->id=11;
    $email->subject = "Have you provided your financial status? ⚠";
    $email->from_name = "Revolut";
    $email->from_email = "no-reply@revolut.com";
    $email->preview_text = 'Hi {USER NAME}, We\'re required to collect some information about your financial status, in order to continue providing crypto products to you.';
    $email->content = file_get_contents(EMAIL_DIR . "/revolut.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 23;
    $email->topic = 'Action required';
    $email->save();

    // 12
    $email = new Email();
    $email->id=12;
    $email->subject = "Rate your experience - You could win a €100 Ryanair Gift Card";
    $email->from_name = "Ryanair";
    $email->from_email = "service@ryanairemail.com";
    $email->preview_text = 'Hi {USER NAME}, Thanks for flying with Ryanair. Win a €100 Ryanair Gift Card.';
    $email->content = file_get_contents(EMAIL_DIR . "/ryanair.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 22;
    $email->topic = 'Gift card';
    $email->save();

    // 13
    $email = new Email();
    $email->id=13;
    $email->subject = "SHEIN Order Delivered Notification";
    $email->from_name = "SHEIN";
    $email->from_email = "noreply@notice.shein.com";
    $email->preview_text = 'Dear customer, Your order GSONWX57M00N3UB tracking number BDM000134200007789569 shows delivered.';
    $email->content = file_get_contents(EMAIL_DIR . "/shein.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 24;
    $email->topic = 'Tracking information';
    $email->save();

    // 14
    $email = new Email();
    $email->id=14;
    $email->subject = "Fwd: New login attempt";
    $email->from_name = "Vinted";
    $email->from_email = "mailto:no-reply@vinted.it";
    $email->preview_text = '{USER NAME}, there\'s been a login attempt from a new device.';
    $email->content = file_get_contents(EMAIL_DIR . "/vinted.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 29;
    $email->topic = 'Failed login attempts';
    $email->save();

    // 15
    $email = new Email();
    $email->id=15;
    $email->subject = "Invoice Payment Reminder - customer code: 1.4509833";
    $email->from_name = "Vodafone";
    $email->from_email = "invoices@vodafone.com";
    $email->preview_text = 'Dear Customer, We invite you to settle your outstanding balance.';
    $email->content = file_get_contents(EMAIL_DIR . "/vodafone.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 27;
    $email->topic = 'Payment required';
    $email->save();

    // 16
    $email = new Email();
    $email->id=16;
    $email->subject = "Your Gifted Tickets - Order number: 5923551";
    $email->from_name = "Warner Bros. Studio Tour London";
    $email->from_email = "thank.you@wbstudiotour.co.uk";
    $email->preview_text = 'Dear {USER NAME}, This is your free ticket to Warner Bros. Studio Tour London - The Making of Harry Potter. ';
    $email->content = file_get_contents(EMAIL_DIR . "/warner bros.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 28;
    $email->topic = 'Gift card';
    $email->save();

    // 17
    $email = new Email();
    $email->id=17;
    $email->subject = "Your Order Tracking Information";
    $email->from_name = "Zalando Order Tracking";
    $email->from_email = "tracking@zalando.com";
    $email->preview_text = 'Hello, Your return is on its way. We will send you an email once the process is completed.';
    $email->content = file_get_contents(EMAIL_DIR . "/zalando.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->counterpart_email_id = 30; 
    $email->topic = 'Tracking information';
    $email->save();

    // 18
    $email = new Email();
    $email->id=18;
    $email->subject = "e-gift card delivery confirmation";
    $email->from_name = "Zara";
    $email->from_email = "noreply@zara.com";
    $email->preview_text = 'Hello {USER NAME}, Please note that your E-Gift Card has been shipped and includes the data shown below.';
    $email->content = file_get_contents(EMAIL_DIR . "/zara.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'hard';  // or 'medium' or 'hard'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Gift card';
    $email->save();



    // ** PHISHING EMAILS **

    // 19
    $email = new Email();
    $email->id=19;
    $email->subject = "Customs Clearance Fee Payment Required for Shipment Release";
    $email->from_name = "Customer Service";
    $email->from_email = "support-dhlexpress@gmail.com";
    $email->preview_text = 'We regret to inform you that due to incomplet shipment clearance procedures, your package has been held at our facility.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy_dhl.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Payment required';
    $email->save();

    // 20
    $email = new Email();
    $email->id=20;
    $email->subject = "Urgent: Action Required for Facebook Account Verification";
    $email->from_name = "Facebook Security Alert";
    $email->from_email = "no-reply_faceb00k.com@live.com";
    $email->preview_text = 'Dear Facebook User, We have detect a suspecious activity on your account.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy_facebook.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Suspicious activity';
    $email->save();

    // 21
    $email = new Email();
    $email->id=21;
    $email->subject = "Alert: Multiple Failed Login Attempts on Your LinkedIn Account";
    $email->from_name = "LinkedIn Security Team";
    $email->from_email = "alerts@243.108.10.100";
    $email->preview_text = 'Dear LinkedIn Member, We have noticed several failed login attempts on your LinkedIn account.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy_linkedin.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 7;
    $email->topic = 'Failed login attempts';
    $email->save();

    // 22
    $email = new Email();
    $email->id=22;
    $email->subject = "Exclusive Ryanair Gift Card Offfer – Verify Now";
    $email->from_name = "Ryanair Customer Service Team";
    $email->from_email = "noreply@rynair.ru";
    $email->preview_text = 'Dear Valued Costumer, We are excited to inform you that an exclusive Ryanair gift card of 50€ has been issued to your account as ...';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy_ryanair.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 12;
    $email->topic = 'Gift card';
    $email->save();

    // 23
    $email = new Email();
    $email->id=23;
    $email->subject = "Action Required: Update Your Revolut Card Information";
    $email->from_name = "Revolut Support";
    $email->from_email = "anna.kraviz91@gmail.com";
    $email->preview_text = 'We have detected that your credit card details on file require an update to ensure uninterrupted service.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy-revolut.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 11;
    $email->topic = 'Action required';
    $email->save();

    // 24
    $email = new Email();
    $email->id=24;
    $email->subject = "Your Shein Package Tracking Update";
    $email->from_name = "Shein Tracking";
    $email->from_email = "tracking@fedxe.com";
    $email->preview_text = 'We are pleased to inform you that your package has been processed and is now in transit.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy-shein.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'easy';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 13;
    $email->topic = 'Tracking information';
    $email->save();

    // 25
    $email = new Email();
    $email->id=25;
    $email->subject = "Important Security Alert – Suspicious Activity Detected";
    $email->from_name = "Google Security Team";
    $email->from_email = "no-reply@accounts.google.com.secure-login.com";
    $email->preview_text = 'Dear {USER NAME}, We have detected suspicious activity on your Google Account.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard_google.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'hard';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 5;
    $email->topic = 'Suspicious activity';
    $email->save();

    // 26
    $email = new Email();
    $email->id=26;
    $email->subject = "Password Expiration";
    $email->from_name = "Nike";
    $email->from_email = "no-reply@nike.nike-notifications.com";
    $email->preview_text = 'Your password must be changed periodically for security reasons.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard_nike.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'hard';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Action required';
    $email->save();

    // 27
    $email = new Email();
    $email->id=27;
    $email->subject = "Important Notification: Refund Processing Update";
    $email->from_name = "Italia Customer Service";
    $email->from_email = "no-reply@vodafone-secure.it";
    $email->preview_text = 'Dear Valued Customer, We have recently identified a billing error on your account resulting from an overcharge.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard_vodafone.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'hard';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 15;
    $email->topic = 'Payment required';
    $email->save();

    // 28
    $email = new Email();
    $email->id=28;
    $email->subject = "Gift Card Warner Bros Studio London Harry Potter";
    $email->from_name = "No-Reply Warner Bros Studio Tour London Harry Potter";
    $email->from_email = "no-reply@warner-service.com";
    $email->preview_text = 'Dear Valued Customer, As a token of our appreciation for your loyalty, we are delighted to offer you an exclusive ...';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard_warnerbros.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'hard';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 16;
    $email->topic = 'Gift card';
    $email->save();

    // 29
    $email = new Email();
    $email->id=29;
    $email->subject = "Fwd: New login attempt";
    $email->from_name = "Vinted";
    $email->from_email = "no-reply@pws.vintd.fr";
    $email->preview_text = '{USER NAME}, there\'s been a login attempt from a new device.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard-vinted.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'hard';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 14;
    $email->topic = 'Failed login attempts';
    $email->save();

    // 30
    $email = new Email();
    $email->id=30;
    $email->subject = "Your Order Tracking Information";
    $email->from_name = "Zalando Order Tracking";
    $email->from_email = "tracking@zalando.return-services.de";
    $email->preview_text = 'Hello {USER NAME}, Your return is on its way. We will send you an email once the process is completed.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard-zalando.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 17;
    $email->topic = 'Tracking information';
    $email->save();

    // 31
    $email = new Email();
    $email->id=31;
    $email->subject = "Payment declined - Confirm your details";
    $email->from_name = "Amazon Orders Support";
    $email->from_email = "amazon-orders-support@gmail.com";
    $email->preview_text = 'We have verified that your payment was declined on your recent order and need to verify your payment information to ...';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_amazon.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 1;
    $email->topic = 'Payment required';
    $email->save();

    // 32
    $email = new Email();
    $email->id=32;
    $email->subject = "Important: Your GLS Package Tracking Information";
    $email->from_name = "GLS Group Tracking";
    $email->from_email = "gls-group-tracking@live.com";
    $email->preview_text = 'Dear Customer, Your package is scheduled for delivery soon.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_GLS.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'hard';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Tracking information';
    $email->save();

    // 33
    $email = new Email();
    $email->id=33;
    $email->subject = "Security Alert: Update your password for Your Instagram Account";
    $email->from_name = "Instagram Security";
    $email->from_email = "security@instagrann.com";
    $email->preview_text = 'We noticed that you have not changed the password for your Instagram account in a while.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_instagram.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 6;
    $email->topic = 'Action required';
    $email->save();

    // 34
    $email = new Email();
    $email->id=34;
    $email->subject = "Are you trying to log in?";
    $email->from_name = "Microsoft Account Team";
    $email->from_email = "no-reply@m1crosoft.com";
    $email->preview_text = 'Dear Microsoft Account Holder, We have recently detected many login attempts on your account.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_microsoft.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Failed login attempts';
    $email->save();

    // 35
    $email = new Email();
    $email->id=35;
    $email->subject = "Important Security Alert - Action Required on Your PayPal Account";
    $email->from_name = "PayPal Security";
    $email->from_email = "security@pay-pal.com";
    $email->preview_text = 'We have detected a new login on your PayPal account from an unrecognized device and location: Google ... ';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_paypal.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->counterpart_email_id = 10;
    $email->topic = 'Suspicious activity';
    $email->save();

    // 36
    $email = new Email();
    $email->id=36;
    $email->subject = "Congratulations! Claim Your Exclusive Temu Gift Card Today";
    $email->from_name = "Temu Customer Support";
    $email->from_email = "support@temuu-secure.com";
    $email->preview_text = 'Dear Customer, We are excited to inform you that as a new member of our community, you have been selected to receive ...';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_temu.html");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'hard'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->counterpart_email_id = null;
    $email->topic = 'Gift card';
    $email->save();


    foreach (["sent", "trash", "draft"] as $folder) {
      $email = new Email();
      $email->subject = "Not available";
      $email->from_name = "System";
      $email->from_email = "";
      $email->preview_text = 'For the purposes of this test, this area is currently inaccessible';
      $email->content = '-';
      $email->date = Carbon::today()->toDateTimeString();
      $email->type = $folder;
      $email->save();
    }
  }

}
