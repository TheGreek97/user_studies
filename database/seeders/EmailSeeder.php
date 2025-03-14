<?php

namespace Database\Seeders;

use PhpParser\Lexer\TokenEmulator\EnumTokenEmulator;
define("EMAIL_DIR", base_path("public/email_files"));
use App\Models\Email;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
    // //test distribution of emails 
    // $totalEmails = 40;
    // $phishingSplit = $totalEmails / 2; // 20 phishing, 20 non-phishing
    // $counterpartRanges = [[1, 12], [21, 32]];

    // for ($i = 1; $i <= $totalEmails; $i++) {
    //   $isPhishing = $i <= $phishingSplit ? 1 : 0;
    //   $hasCounterpart = ($i >= 1 && $i <= 12) || ($i >= 21 && $i <= 32) ? 1 : 0;

    //   // Assign difficulty levels based on rules
    //   if (($i >= 1 && $i <= 4) || ($i >= 13 && $i <= 16) || ($i >= 21 && $i <= 24) || ($i >= 33 && $i <= 36)) {
    //     $difficulty = 'low';
    //   } elseif (($i >= 5 && $i <= 8) || ($i >= 17 && $i <= 18) || ($i >= 25 && $i <= 28) || ($i >= 37 && $i <= 38)) {
    //     $difficulty = 'medium';
    //   } else {
    //     $difficulty = 'high';
    //   }

    //   $email = new Email();
    //   $email->from_name = "Test Sender";
    //   $email->from_email = "test.sender@example.com";
    //   $email->subject = "Test Email #$i";
    //   $email->preview_text = "This is a preview for email #$i";
    //   $email->content = '<p>This is a test email with ID #' . $i . '</p>';
    //   $email->show_warning = false;
    //   $email->type = 'inbox';
    //   $email->difficulty_level = $difficulty;
    //   $email->phishing = $isPhishing;
    //   $email->counterpart = $hasCounterpart;

    //   $email->save();
    // }
    //** LEGITIMATE EMAILS **
    // 1
    $email = new Email();
    $email->from_name = "Amazon";
    $email->from_email = "no-reply@amazon.com";
    $email->subject = "Payment Declined – Order #1078945780";
    $email->preview_text = 'Hello, Your payment for the item listed below has been declined. Valid payment information must be received within 5 days, otherwise, your order will be cancelled.';
    $email->content = file_get_contents(EMAIL_DIR . "/amazon.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 2
    $email = new Email();
    $email->from_name = "Barclays Banking";
    $email->from_email = "client.support@barclays.com";
    $email->subject = "Important Disclosure Regarding Your Credit Card Account";
    $email->preview_text = 'The following disclosure represents important details concerning your credit card. ';
    $email->content = file_get_contents(EMAIL_DIR . "/barclays.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 3
    $email = new Email();
    $email->from_name = "Leonardo Hotel Munich via Booking.com";
    $email->from_email = "noreply@booking.com";
    $email->subject = "You have a new message from Leonardo Hotel Munich via Booking.com";
    $email->preview_text = 'Dear customer, weve received your request for the additional service for your reservation.';
    $email->content = file_get_contents(EMAIL_DIR . "/booking.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->save();

    // 4
    $email = new Email();
    $email->subject = "A new device is using your account";
    $email->from_name = " eBay";
    $email->from_email = "ebay@ebay.com";
    $email->preview_text = 'Hello, {USER NAME}. We noticed a new login to your eBay.com account.';
    $email->content = file_get_contents(EMAIL_DIR . "/ebay.htm");
    //$email->date = Carbon::parse('2022-08-19 15:28')->toDateTimeString();
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->save();

    // 5
    $email = new Email();
    $email->subject = "Your Etsy Purchase (23456726491799)";
    $email->from_name = "Etsy Transactions";
    $email->from_email = "transaction@etsy.com";
    $email->preview_text = 'The seller will start working on this right away. Your order number is 23456726491799.';
    $email->content = file_get_contents(EMAIL_DIR . "/etsy.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->save();

    // 6
    $email = new Email();
    $email->subject = "Welcome to Facebook";
    $email->from_name = "Facebook";
    $email->from_email = "registration@facebookmail.com";
    $email->preview_text = 'Your account has been created – now it will be easier than ever to share and connect with your friends and family.';
    $email->content = file_get_contents(EMAIL_DIR . "/facebook.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 7
    $email = new Email();
    $email->subject = "New sign-in on Windows device";
    $email->from_name = "Google (Google Account Security Team)";
    $email->from_email = "no-reply@accounts.google.com";
    $email->preview_text = 'A new sign-in to your Google Account was detected on a Windows device.';
    $email->content = file_get_contents(EMAIL_DIR . "/google.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 8
    $email = new Email();
    $email->subject = "Confirm email address";
    $email->from_name = "Instagram";
    $email->from_email = "no-reply@mail.instagram.com";
    $email->preview_text = 'You recently added a new email address to your Instagram account.';
    $email->content = file_get_contents(EMAIL_DIR . "/instagram.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 9
    $email = new Email();
    $email->subject = "Your LinkedIn Account Has Been Temporarily Restricted";
    $email->from_name = "Linkedin";
    $email->from_email = "noreply@linkedin.com";
    $email->preview_text = 'Dear {USER NAME}, We noticed unusual activity from your account.';
    $email->content = file_get_contents(EMAIL_DIR . "/linkedin.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 10
    $email = new Email();
    $email->subject = "Redeem Your Microsoft Gift Card";
    $email->from_name = "Microsoft Store";
    $email->from_email = "noreply@microsoft.com";
    $email->preview_text = 'Dear Microsoft Customer, With your recent Xbox purchase, you received a three-month Groove Music Pass token card. ';
    $email->content = file_get_contents(EMAIL_DIR . "/microsoft.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 11
    $email = new Email();
    $email->subject = "Your Payment Method Has Been Updated";
    $email->from_name = "Netflix";
    $email->from_email = "info@account.netflix.com";
    $email->preview_text = 'Hello {USER NAME}, As per your request, we have updated your account with the new payment details. ';
    $email->content = file_get_contents(EMAIL_DIR . "/netflix.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->save();

    // 12
    $email = new Email();
    $email->subject = "Password Update";
    $email->from_name = "Nike";
    $email->from_email = "nike@notifications.nike.com";
    $email->preview_text = 'Your Nike Member Profile Password Has Been Updated. ';
    $email->content = file_get_contents(EMAIL_DIR . "/nike.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 13
    $email = new Email();
    $email->subject = "Reset your password";
    $email->from_name = "Nord Account";
    $email->from_email = "support@nordaccount.com";
    $email->preview_text = 'Reset your Nord Account password. ';
    $email->content = file_get_contents(EMAIL_DIR . "/nordVPN.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->save();

    // 14
    $email = new Email();
    $email->subject = "Receipt for your payment to Ebay.com";
    $email->from_name = "PayPal";
    $email->from_email = "service@paypal.com";
    $email->preview_text = 'You payed 259,99 € EUR to Ebay.com. ';
    $email->content = file_get_contents(EMAIL_DIR . "/paypal.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 15
    $email = new Email();
    $email->subject = "Have you provided your financial status? ⚠";
    $email->from_name = "Revolut";
    $email->from_email = "no-reply@revolut.com";
    $email->preview_text = 'Hi {USER NAME}, We\'re required to collect some information about your financial status, in order to continue providing crypto products to you.';
    $email->content = file_get_contents(EMAIL_DIR . "/revolut.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 16
    $email = new Email();
    $email->subject = "Rate your experience - You could win a €100 Ryanair Gift Card";
    $email->from_name = "Ryanair";
    $email->from_email = "service@ryanairemail.com";
    $email->preview_text = 'Hi {USER NAME}, Thanks for flying with Ryanair. Win a €100 Ryanair Gift Card.';
    $email->content = file_get_contents(EMAIL_DIR . "/ryanair.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->save();

    // 17
    $email = new Email();
    $email->subject = "SHEIN Order Delivered Notification";
    $email->from_name = "SHEIN";
    $email->from_email = "noreply@notice.shein.com";
    $email->preview_text = 'Dear customer, Your order GSONWX57M00N3UB tracking number BDM000134200007789569 shows delivered.';
    $email->content = file_get_contents(EMAIL_DIR . "/shein.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->save();

    // 18
    $email = new Email();
    $email->subject = "Please update your Spotify password";
    $email->from_name = "Spotify";
    $email->from_email = "no-reply@spotify.com";
    $email->preview_text = 'Hi To protect your Spotify account, we\'ve reset your password due to detected suspicious activity.';
    $email->content = file_get_contents(EMAIL_DIR . "/spotify.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->save();

    // 19
    $email = new Email();
    $email->subject = "Invoice Payment Reminder - customer code: 1.4509833";
    $email->from_name = "Vodafone";
    $email->from_email = "invoices@vodafone.com";
    $email->preview_text = 'Dear Customer, We invite you to settle your outstanding balance.';
    $email->content = file_get_contents(EMAIL_DIR . "/vodafone.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 20
    $email = new Email();
    $email->subject = "Your Order Tracking Information";
    $email->from_name = "Zalando Order Tracking";
    $email->from_email = "tracking@zalando.com";
    $email->preview_text = 'Hello, Your return is on its way. We will send you an email once the process is completed.';
    $email->content = file_get_contents(EMAIL_DIR . "/zalando.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 1;
    $email->save();

    // 21
    $email = new Email();
    $email->subject = "e-gift card delivery confirmation";
    $email->from_name = "Zara";
    $email->from_email = "noreply@zara.com";
    $email->preview_text = 'Hello {USER NAME}, Please note that your E-Gift Card has been shipped and includes the data shown below.';
    $email->content = file_get_contents(EMAIL_DIR . "/zara.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 0;
    $email->counterpart = 0;
    $email->save();



    // ** PHISHING EMAILS **

    // PHISHING
    // 1 
    $email = new Email();
    $email->subject = "Loan Pre-Approval Notification – Action Required";
    $email->from_name = "Barclays Bank Services";
    $email->from_email = "noreply@89.30.204.109";
    $email->preview_text = 'We are pleased to inform you that your recent loan application has pre-approved by Barclays Banking group.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy_barclays.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'low';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 2 
    $email = new Email();
    $email->subject = "Customs Clearance Fee Payment Required for Shipment Release";
    $email->from_name = "Customer Service";
    $email->from_email = "support-dhlexpress@gmail.com";
    $email->preview_text = 'We regret to inform you that due to incomplet shipment clearance procedures, your package has been held at our facility.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy_barclays.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'low';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->save();

    // 3
    $email = new Email();
    $email->subject = "Urgent: Action Required for Facebook Account Verification";
    $email->from_name = "Facebook Security Alert";
    $email->from_email = "no-reply_faceb00k.com@live.com";
    $email->preview_text = 'Dear Facebook User, We have detect a suspecious activity on your account.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy_facebook.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'low';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 4
    $email = new Email();
    $email->subject = "Alert: Multiple Failed Login Attempts on Your LinkedIn Account";
    $email->from_name = "LinkedIn Security Team";
    $email->from_email = "alerts@243.108.10.100";
    $email->preview_text = 'Dear LinkedIn Member, We have noticed several failed login attempts on your LinkedIn account.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy_linkedin.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'low';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 5
    $email = new Email();
    $email->subject = "Exclusive Sephora Gift Card Offfer – Verify Now";
    $email->from_name = "Sephora Customer Service Team";
    $email->from_email = "noreply@sefora.ru";
    $email->preview_text = 'We are excited to inform you that an exclusive Sephora gift card of <b>50€</b> has been issued to your account ...';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy_sephora.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'low';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->save();

    // 6
    $email = new Email();
    $email->subject = "Your FedEx Package Tracking Update";
    $email->from_name = "FedEx Tracking";
    $email->from_email = "tracking@fedxe.com";
    $email->preview_text = 'We are pleased to inform you that your package has been processed and is now in transit.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy-fedex.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'low';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->save();

    // 7
    $email = new Email();
    $email->subject = "Action Required: Update Your Revolut Card Information";
    $email->from_name = "Revolut Support";
    $email->from_email = "anna.kraviz91@gmail.com";
    $email->preview_text = 'We have detected that your credit card details on file require an update to ensure uninterrupted service.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/easy-revolut.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'low';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 8
    $email = new Email();
    $email->subject = "Your Pre-Approved Loan Offer Awaits";
    $email->from_name = "Crédit Agricole";
    $email->from_email = "service@credit-agricole-group.com";
    $email->preview_text = 'Dear Valued Customer, We are pleased to inform you that, based on your excellent financial history, you have been ...';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard_credit-agricole.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->save();

    // 9
    $email = new Email();
    $email->subject = "Important Security Alert – Suspicious Activity Detected";
    $email->from_name = "Google Security Team";
    $email->from_email = "no-reply@accounts.google.com.secure-login.com";
    $email->preview_text = 'Dear {USER NAME}, We have detected suspicious activity on your Google Account.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard_google.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 10
    $email = new Email();
    $email->subject = "Important Security Alert - Action Required on Your PayPal Account";
    $email->from_name = "PayPal Security";
    $email->from_email = "security@pay-pal.com";
    $email->preview_text = 'Security Alert. We have detected multiple unsuccessful login attempts on your PayPal account from an unrecognized d...';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard_paypal.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 11
    $email = new Email();
    $email->subject = "Important Notification: Refund Processing Update";
    $email->from_name = "Italia Customer Service";
    $email->from_email = "no-reply@vodafone-secure.it";
    $email->preview_text = 'Dear Valued Customer, We have recently identified a billing error on your account resulting from an overcharge.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard_vodafone.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 12
    $email = new Email();
    $email->subject = "Gift Card Warner Bros Studio London Harry Potter";
    $email->from_name = "No-Reply Warner Bros Studio Tour London Harry Potter";
    $email->from_email = "no-reply@warner-service.com";
    $email->preview_text = 'Dear Valued Customer, As a token of our appreciation for your loyalty, we are delighted to offer you an exclusive ...';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard_warnerbros.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->save();

    // 13
    $email = new Email();
    $email->subject = "Password Expiration";
    $email->from_name = "Nike";
    $email->from_email = "no-reply@nike.nike-notifications.com";
    $email->preview_text = 'Your Password Has Expired. Your password must be changed periodically for security reasons.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard-nike.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 14
    $email = new Email();
    $email->subject = "Your Order Tracking Information";
    $email->from_name = "Zalando Order Tracking";
    $email->from_email = "tracking@zalando.return-services.de";
    $email->preview_text = 'Hello {USER NAME}, Your return is on its way. We will send you an email once the process is completed.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/hard-zalando.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'high';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 15
    $email = new Email();
    $email->subject = "Order delayed - Confirm your details";
    $email->from_name = "Amazon Orders Support";
    $email->from_email = "amazon-orders-support@gmail.com";
    $email->preview_text = 'Dear Customer, We have detected unusual activity on your recent order and need to verify your payment information ...';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_amazon.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 16
    $email = new Email();
    $email->subject = "Urgent: Action Required to Finalize Your Loan Pre-Approval";
    $email->from_name = "Deutsche Bank Loan Services";
    $email->from_email = "oreply@dutsche-bank.it";
    $email->preview_text = 'Dear Valued Customer, We are pleased to inform you that your loan pre-approval is almost complete.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_deutschebank.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->save();

    // 17
    $email = new Email();
    $email->subject = "Important: Your GLS Package Tracking Information";
    $email->from_name = "GLS Group Tracking";
    $email->from_email = "gls-group-tracking@live.com";
    $email->preview_text = 'Dear Customer, Your package is scheduled for delivery soon.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_GLS.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->save();

    // 18
    $email = new Email();
    $email->subject = "Security Alert: Unusual Login Activity on Your Instagram Account";
    $email->from_name = "Instagram Security";
    $email->from_email = "security@instagrann.com";
    $email->preview_text = 'We have detected several unsuccessful login attempts on your Instagram account from an unfamiliar device.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_instagram.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
    $email->save();

    // 19
    $email = new Email();
    $email->subject = "Congratulations! Claim Your Exclusive Temu Gift Card Today";
    $email->from_name = "Temu Customer Support";
    $email->from_email = "support@temuu-secure.com";
    $email->preview_text = 'Dear Customer, We are excited to inform you that as a new member of our community, you have been selected to receive ...';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_temu.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->save();

    // 20
    $email = new Email();
    $email->subject = "Action Required: Delivery Advance Fee Notice";
    $email->from_name = "UPS Customer Service";
    $email->from_email = "noreply@ups-supp0rt.com";
    $email->preview_text = 'Dear UPS Customer, We are writing regarding your recent shipment.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium_ups.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 0;
    $email->save();

    // 21
    $email = new Email();
    $email->subject = "Action Required: Update Your Account Password";
    $email->from_name = "Microsoft Account Team";
    $email->from_email = "no-reply@m1crosoft.com";
    $email->preview_text = 'Dear Microsoft Account Holder, We have recently detected unusual activity on your account.';
    $email->content = file_get_contents(EMAIL_DIR . "/phishing/medium-microsoft.htm");
    $email->show_warning = false;
    $email->type = 'inbox';
    $email->difficulty_level = 'medium';  // or 'medium' or 'high'
    $email->phishing = 1;
    $email->counterpart = 1;
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
