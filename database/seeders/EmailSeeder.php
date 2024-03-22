<?php

namespace Database\Seeders;
define("EMAIL_DIR", base_path("database/seeders/email_files"));
use App\Models\Email;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        function get_explanation ($type='basic', $url=null): string
        {
            /*$success = preg_match("/(?:https?:\/\/)?[^\/]*\//", $url, $matches);
            if ($success) {
                $url = substr($matches[0], 0, strlen($matches[0])-1);
            }*/
            switch ($type) {
                case 'basic':
                    //if ($url !== null)
                    return "This email was blocked because it may trick you into doing something dangerous like installing software or revealing personal information like passwords or credit cards.";
                    //else
                    // return "";
                    #return "The target URL in the mail can be a fake one. This site might be intended to take you to a different place. You might be disclosing private information.";
                case 'image':
                    return "In the e-mail there is an image, a typical element of possible malicious e-mails. There is a potential risk to your data if you continue";
                case 'links':
                    return "The presence of many links in the body makes the email suspicious. This email may have been created to cheat you. Your private information is at risk.";
                case 'grammar':
                    return "This email has many grammatical errors, a typical feature of a scam email. Your data could be stolen";
                case 'spec_chars':
                    return "Special characters have been detected in the email body. This increases the likelihood of the risk of having received a fake email. There is a potential risk of being scammed if you proceed";
                case 'sus_words':
                    return "Suspicious words such as “remove“ or “dear“ were found in the content of the e-mail. This is typical of fraudulent emails. Your private information is at risk.";
                case 'age':
                    return "The URL in the email leads to a website created recently. Young websites are famous for criminal activity. There is a potential risk if you proceed.";
                case 'expiration':
                    return "The URL in the email leads to a site hosted on a dangerous domain that is about to expire. This is typical of websites used only for criminal activity. Your private information may be stolen.";
                case 'ranking':
                    return "Google calculates a ranking for web pages. A high ranking indicates a high possibility that the searcher will click on this result. The URL in the email leads to a low-ranking website, typical of malicious websites. Proceeding might expose your private information to theft.";
                case 'no_https':
                    return "The e-mail contains a link to a website. However, this website has no certificate that guarantees a secure connection to the site. The absence of a certificate is a clear sign of a fake website. Your data is not safe.";
                case 'self_https':
                    return "A protected connection seems guaranteed by this website. Nevertheless, a self-signed certificate is adopted to create the connection. This is a reason why a website may be fake. You are likely to be exposed to the theft of private information";
                case 'spec_chars_url':
                    return "A URL made up of suspicious special characters was found in the email body. These types of URLs may be meant to take you to a different place. The risk of disclosing private information is high.";
                case 'sensitive_words_url':
                    return "The URL present in the e-mail body contains many sensitive words. This might happen when a URL is fraudulent. If you continue, you are likely to be exposed to the theft of private information";
                case 'ip_url':
                    return "The link in the email uses a string of numbers instead of a normal web address. This is unusual and often a sign of a fake site trying to steal your details. If you enter your information, it could be stolen and misused.";
                    //return "Usually, websites use the URL instead of the IP address to make it easier for you to browse the web. However, an IP address was found in the email. Similar e-mails are harmful and steal private information. There is a potential risk of being cheated if you proceed.";
                case 'link_mismatch':
                    return "The link text says \"protect your account\", but it points somewhere else. This could trick you into visiting a harmful site. You might give away your personal details or password.";
                    //return "This email reports a link that is different from the actual one ". $url .". This site might be intended to take you to a different place. You might be disclosing private information.";
                case 'tld_mispositioned':
                    return "The sender's email address has a familiar company name in an unusual place. This could trick you into thinking it's from a trusted source when it's not. If fooled, you might give away personal details to scammers.";
                    //return "In the URL present in the email (". $url .") the top-level domain (e.g., “.com“) is in an abnormal position. This could indicate that the URL leads to a fake website. Such websites might steal your personal information";
                case 'num_subdomains':
                    return "There are many subdomains in the URL contained in the email. This anomaly is a sign of malicious email. Don't share your private information";
                case 'url_length':
                    return "There is a long URL in the email. An email with a very long URL is more likely to have criminal aims. Your information might be stolen if you proceed";
                case 'url_shortened':
                    return "The link in the email has been shortened through external services. This practice is used to hide the true destination of the link and avoid being detected as a scam site. If you proceed you will be exposed to a site that has the intent to steal your data";
                default:
                    return "";
            }
        }

        //** LEGITIMATE EMAILS **
        // 1
        $email = new Email();
        $email->from_name = "TicketOne Staff";
        $email->from_email = "ecomm.customerservice@ticketone.it";
        $email->subject = "Important notice regarding U2 in London";
        $email->preview_text = 'Hi {user_name}, this is to provide you with useful information regarding U2’s event scheduled in LONDON.';
        $email->content = '<p class="p1"><img style="display: block; margin-left: auto; margin-right: auto;" src="' . asset("/assets/img/email/ticketone.png"). '" alt="" width="300" height="113" /></p>
            <p class="p1">Hi {user_name},<br /><br />this is to provide you with useful information regarding <strong>U2’s event</strong> scheduled in <strong>LONDON</strong>.<br /><br />We confirm that the concert will be held this evening, <strong>November 16th</strong>, at <strong>The London Palladium</strong>.<br /><br />Below we report what the organizer shared:<br /><br />"U2 @ The London Palladium - IMPORTANT SERVICE INFORMATION: <br /><strong>The opening of the gates is scheduled for around 5:30 pm</strong>.<br /><br />We invite you not to go to the venue too early, also to avoid the hottest hours.<br /><br />U2\'s concert is supposed to start between 9.30 pm and 10 pm. <br /><br />At the end of the concert, it is advisable to wait at least half an hour before leaving the venue in order not to obstruct traffic. <br /><br />To assure safety, the outflow will be managed in stages by our security personnel, checking the exits at regular intervals of time.<br /><br />Inside the venue there are food & beverage areas, including vegetarian choices too.<br />The internal regulations of The London Palladium can be consulted at this link: <a href="https://lwtheatres.co.uk/lw-theatres-audience-guide/" style="text-decoration: underline; color: #0001F1;"><span class="s1">https://lwtheatres.co.uk/lw-theatres-audience-guide/</span></a><br /><br /><br />Kind Regards,<br />TicketOne Staff</p>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->show_warning = false;
        $email->type = 'inbox';
        $email->save();

        // 2
        $email = new Email();
        $email->from_name = "myCicero";
        $email->from_email = "no-reply@mycicero.eu";
        $email->subject = "End of parking confirmed";
        $email->preview_text = 'Your parking time is over. Dear user, your parking time is over.';
        $date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->content = '<div>
        <div class="adM"> </div>
        <table style="border-collapse: collapse; max-width: 900px;" width="100%" align="center">
        <tbody>
        <tr>
        <td style="min-width: 20px;" width="5%"> </td>
        <td style="max-width: 810px; background-repeat: no-repeat; background-size: cover; background-position: center;" bgcolor="#F9F9F9" width="90%">
        <table style="border-collapse: collapse; background: transparent;" width="100%" align="center">
        <tbody>
        <tr>
        <td style="background: transparent;" align="center"><img class="CToWUd a6T" style="width: 100%; max-width: 810px; max-height: 170px;" tabindex="0" src=" ' . asset("/assets/img/email/mycicero.jpg"). '" alt="header myCicero" data-bit="iit" />
        <div class="a6S" dir="ltr" style="opacity: 0.01; left: 524.195px; top: 75px;">
        <div id=":rx" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" tabindex="0" role="button" aria-label="Download attachment " data-tooltip-class="a1V" data-tooltip="Download">
        <div class="akn">
        <div class="aSK J-J5-Ji aYr"> </div>
        </div>
        </div>
        </div>
        </td>
        </tr>
        </tbody>
        </table>
        <br /><br />
        <table width="100%">
        <tbody>
        <tr>
        <td style="min-width: 40px;" width="15%"> </td>
        <td width="70%">
        <table style="max-width: 568px; border-radius: 15px; border-spacing: 7px; text-align: justify; border: 1px solid #dfdfdf; width: 100%;" width="100%" align="center" bgcolor="white">
        <tbody>
        <tr>
        <td style="max-width: 17px; background: white; border-color: white; border-radius: 20px; width: 3%;" width="3%"> </td>
        <td style="max-width: 534px; background: white; border-color: white; border-radius: 20px; width: 94%;" width="94%"> </td>
        <td style="max-width: 17px; background: white; border-color: white; border-radius: 20px; width: 3%;" width="3%"> </td>
        </tr>
        <tr>
        <td style="max-width: 17px; background: white; border-color: white; width: 3%;" width="3%"> </td>
        <td style="max-width: 534px; background: white; border-color: white; width: 94%;" width="94%"><center><span style="font-family: Calibri,sans-serif; color: #a45a95; font-size: 1.7em;"><strong>End of parking confirmed</strong></span></center><br /><br /><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;">Dear User, <br />your parking time is <strong>over</strong>. <br /><br />Here are all the details: <br /></span>
        <table style="border-spacing: 0px; background: white; border-radius: 10px; border: 1px solid #e3e3e3;" width="100%" align="center">
        <tbody>
        <tr>
        <td style="background: #a45a95; border-radius: 10px 0px 0px 0px;" width="10px"> </td>
        <td style="text-align: center; background: #a45a95; padding-top: 8px; padding-bottom: 8px;" width="97%"><span style="color: #ffffff; font-family: Calibri, sans-serif;"><span style="font-size: 16.8px;"><strong>DETAILS</strong></span></span></td>
        <td style="background: #a45a95; border-radius: 0px 10px 0px 0px;" width="10px"> </td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-left: 10px; padding-top: 12px; padding-bottom: 3px; background: #white;" width="97%"><span style="font-family: Calibri,sans-serif; color: #a45a95; font-size: 1.1em;"> <strong> ID: 950/2715698</strong> </span></td>
        <td> </td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-left: 10px; padding-top: 3px; padding-bottom: 3px; background: #white;" width="97%"><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> <strong> Municipality</strong>: Rome </span></td>
        <td> </td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-left: 10px; padding-top: 3px; padding-bottom: 3px; background: #white;" width="97%"><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> <strong> Rest area</strong>: Termini Train Station </span></td>
        <td> </td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-left: 10px; padding-top: 3px; padding-bottom: 3px; background: #white;" width="97%"><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> <strong> Vehicle registration number</strong>: DA000DA </span></td>
        <td> </td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-left: 10px; padding-top: 3px; padding-bottom: 3px; background: #white;" width="97%"><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;">
        <strong> Date and time</strong>: '. $date. ' 14:04 to '. $date.' 14:36</span></td>
        <td> </td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-left: 10px; padding-top: 3px; padding-bottom: 15px; background: #white;" width="97%"><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> <strong> IParking amount</strong>: € 0,53 </span></td>
        <td> </td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-left: 10px; border-top: 1px solid #dfdfdf; padding-top: 12px; padding-bottom: 12px; background: #white; border-radius: 0px 0px 10px 10px;" width="97%"><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> <strong> Your current credit</strong>: € 10,50 </span></td>
        <td> </td>
        </tr>
        </tbody>
        </table>
        <p> </p>
        <p><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;">For any tax deduction it is necessary to keep this email. This operation is not subject to the obligation of invoicing pursuant to art. 74 1st paragraph letter e) of Presidential Decree 633/72.</span></p>
        <p><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> If you need help or more details, consult our <a style="color: #a45a95;" href="https://www.mycicero.eu/details"> <span style="font-family: Calibri,sans-serif; color: #a45a95;"> <strong>FAQs</strong></span></a> or contact us at <a style="color: #a45a95;" href="mailto:support@mycicero.eu"> <span style="font-family: Calibri,sans-serif; color: #a45a95;"> <strong>support@mycicero.eu</strong></span></a> or at <a style="color: #a45a95; text-decoration: none;"> <span style="font-family: Calibri,sans-serif; color: #a45a95;"> <strong>071 920 7000</strong></span></a>.</span> <br /><br /><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> On our site you will also find: all the information regarding parking, the cities involved in the program , timetables of the public transport and an apposite section to buy the tickets and the transport companies adherent to <strong>myCicero</strong>. </span> <br /><br /><br /><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> Have a safe trip! <br /></span> <span style="font-family: Calibri,sans-serif; color: #a45a95; font-size: 1.1em;"> <strong>The myCicero team</strong> </span></p>
        </td>
        <td style="max-width: 17px; background: white; border-color: white; width: 3%;" width="3%"> </td>
        </tr>
        <tr>
        <td style="max-width: 17px; background: white; border-color: white; width: 3%;" width="3%"> </td>
        <td style="max-width: 534px; background: white; border-color: white; width: 94%;" width="94%">
        <table style="background: white; border-color: white;" width="100%">
        <tbody>
        <tr>
        <td style="background: white; border-color: white; color: white;" align="center" width="33%">_______________</td>
        <td style="background: white; border-color: white; color: white;" align="center" width="34%">_______________</td>
        <td style="background: white; border-color: white; color: white;" align="center" width="33%">_______________</td>
        </tr>
        </tbody>
        </table>
        </td>
        <td style="max-width: 17px; background: white; border-color: white; width: 3%;" width="3%"> </td>
        </tr>
        </tbody>
        </table>
        <table width="100%">
        <tbody>
        <tr>
        <td> </td>
        </tr>
        </tbody>
        </table>
        </td>
        <td style="min-width: 40px;" width="15%"> </td>
        </tr>
        </tbody>
        </table>
        </td>
        <td style="min-width: 20px;" width="5%"> </td>
        </tr>
        </tbody>
        </table>
        <div class="yj6qo"> </div>
        <div class="adL"> </div>
        </div>';
        $email->show_warning = false;
        $email->date = $date;
        $email->type = 'inbox';
        $email->save();

        // 3
        $email = new Email();
        $email->from_name = "Allianz Insurance";
        $email->from_email = "insurances@allianz.com";
        $email->subject = "Notice of expiry of the Car Insurance Policy";
        $email->preview_text = 'Dear customer, we\'re sending you this email as a reminder of the expiration of your car insurance policy.';
        $email->content = '<div><p class="p1"><img style="display: block; margin-left: auto; margin-right: auto;" src="'. asset("/assets/img/email/allianz.png").'" alt="" width="200" height="57" /></p>
        <p class="p1">Dear Customer,</p>
        <p class="p2"> </p>
        <p class="p1">we\'re sending you this email as a reminder of the near expiration of your car insurance policy.</p>
        <p class="p2"> </p>
        <p class="p1">Your Allianz Agent is available to evaluate together new solutions for the renewal of your insurance policy.</p>
        <p class="p2"> </p>
        <p class="p1"><span class="s1">We’d like to</span> thank you for the trust you had put in us and hope you would like to entrust your policy to us in the future.</p>
        <p class="p1">Our best regards.</p></div>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->show_warning = false;
        $email->type = 'inbox';
        $email->save();

        // 4
        $email = new Email();
        $email->subject = "Amazon Password Reset";
        $email->from_name = "Amazon.com";
        $email->from_email = "no-reply@amazon.com";
        $email->preview_text = 'Hi {user_name}, to reset your password follow the steps in this email.';
        $email->content = '<div>
        <table style="width:100%;" class="m_-4745720069737180512body" cellspacing="0" cellpadding="0" align="center">
        <tbody>
        <tr>
        <td>
        <table cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td>
        <table style="height: 49px; padding: 5px;" width="100%" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td style="width: 50%;"><img id="m_-4745720069737180512amazonLogo" class="CToWUd" width="100"  src="'. asset("/assets/img/email/amazon.jpg").'" data-bit="iit" /></td>
        <td id="m_-4745720069737180512title" style="width: 50%;" align="right" valign="top">
        <p>Password assistance</p>
        </td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td style="padding: 5px;">
        <p>Hi {user_name},</p>
        <br>
        <p>To reset your password click <a href="https://www.amazon.com/reset" style="text-decoration: underline; color: #0001F1;">here</a>.</p>
        </td>
        </tr>
        <tr>
        <td style="padding: 5px;">
        <p>Do not share this email with anyone. Our customer support team won\'t ask you; never your password, credit card or banking information.</p>
        </td>
        </tr>
        <tr>
        <td style="padding: 5px;">
        <p>Thank you.</p>
        </td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        </tbody>
        </table>
        </div>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        //$email->date = Carbon::parse('2022-08-19 15:28')->toDateTimeString();
        $email->show_warning = false;
        $email->type = 'inbox';
        $email->save();

        // 5
        $email = new Email();
        $email->subject = "Changes to the YouTube Terms of Service";
        $email->from_name = "YouTube";
        $email->from_email = "no-reply@youtube.com";
        $email->preview_text = 'We have sent you this email to notify you of an update of the Terms of Service.';
        $email->content = file_get_contents(EMAIL_DIR . "/youtube_legit.htm");
        $email->content = str_replace ("____asset_path_____", asset("/assets/img/email/youtube.png"), $email->content);
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->show_warning = false;
        $email->type = 'inbox';
        $email->save();

        // 6
        $email = new Email();
        $email->subject = "PIN reset";
        $email->from_name = "UniCredit";
        $email->from_email = "no-reply@unicreditgroup.eu";
        $email->preview_text = 'You have reset your PIN and we have updated your account.';
        $email->content = '<div><p><img style="display: block; margin-left: auto; margin-right: auto;" src="'. asset("/assets/img/email/unicredit.jpg") .'" alt="" width="300" height="109" /></p>
        <p>Hi {user_name},</p><br>
        <p>Your confirmation PIN for your online banking services has been successfully reset!</p>
        <br>
        <p>
        If you did not request to reset your PIN, nor you did modify it, start an online chat with one of our operators by visiting
        <a href="https://www.unicredit.it/support" style="text-decoration: underline; color: #e31a0e;">www.unicredit.it/support</a>
        to verify the integrity of your account. <br/>
        Alternatively, you can also visit one of our branches near you - <a style="text-decoration: underline; color: #e31a0e;" href="https://www.unicredit.it/it/contatti-e-agenzie/locator.html">where is the closest branch?</a>
        </p>
        <br><p>Best regards,<br />UniCredit Bank</p></div>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->show_warning = false;
        $email->type = 'inbox';
        $email->save();

        // 7
        $email = new Email();
        $email->subject = "Our tips to better prepare you for the upcoming season";
        $email->from_name = "DAZN";
        $email->from_email = "newsletter@dazn.it";
        $email->preview_text = 'Hi {user_name}, We are ready for a new season of great sport.';
        $email->content = '<div><p class="p1"><img style="display: block; margin-left: auto; margin-right: auto;" src="'.asset("/assets/img/email/dazn.png").'" alt="" width="102" height="102" /></p>
        <p class="p1">Hi {user_name},</p>
        <p class="p1">We are ready for a new season of great sport. What about you, are you ready to live it to the fullest on <strong>DAZN</strong>?</p>
        <p class="p1">We want to give you some simple advice to prepare yourself for the vision of <strong>upcoming events</strong>.</p>
        <br><h3 class="p1" style="font-size: 20px"><strong>⬆️ UPDATE THE APP AND LOG IN</strong></h3>
        <p class="p1">First, check that you have downloaded the latest version of the DAZN app and the operating system of your device. Then connect in advance the device with which you will watch the game using your<span class="Apple-converted-space">  </span>credentials: email and password.</p>
        <p class="p1">You don’t remember your password? Quickly reset it by clicking here.</p>
        <p class="p1">You will receive an email with a link to create a new one, so check from the "Your details" section that your email is correct.</p>
        <br><h3 class="p1" style="font-size: 20px"><strong>🌐 CHECK YOUR INTERNET CONNECTION</strong></h3>
        <p class="p1">To check your connection speed, just do a simple test through this link by logging in with the device you want to use to watch DAZN on.</p>
        <p class="p1">To optimize your connection, we suggest you reduce the number of devices connected to the same network, avoid downloading heavy files and if possible use a cable connection by placing the modem in a central area of ​​the house, not inside furniture and at least one meter from the floor.</p>
        <br><h3 class="p1" style="font-size: 20px"><strong>📱 MANAGE YOUR DEVICES</strong></h3>
        <p class="p1">You can manage your devices directly from the "<em>Devices</em>" section within the "<em>My Account</em>" area.</p>
        <br><p class="p1">For any further information, please read our guide to <strong>DAZN subscription plans</strong> at <a href="https://www.dazn.com">this link</a> or contact our <a href="https://www.dazn.com/contacts">customer service</a>.</p>
        <p class="p1"> </p>
        <p class="p1">The DAZN Team</p></div>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->show_warning = false;
        $email->type = 'inbox';
        $email->save();

        // 8
        $email = new Email();
        $email->subject = "SARS Covid-19 analysis results";
        $email->from_name = "European Hospital spa - Rome";
        $email->from_email = "pharmacy-24@europeanhospital.it";
        $email->preview_text = 'Hi {user_name}, the analysis result is ready.';
        $email->content = '<div><p>Hi {user_name},</p><br>
        <p>the result of the swab carried out today is <strong>negative</strong>.</p>
        <br>
        <p>Thank you for having chosen our services.<br /><br />Best regards,<br />European Hospital spa</p>
        <p>
        <br/><img src="'.asset('/assets/img/email/farmacia.jpeg').'" alt="" width="100" height="75" /></p></div>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->show_warning = false;
        $email->type = 'inbox';
        $email->save();

        // 9
        $email = new Email();
        $email->subject = "Your Unity Student Plan has expired";
        $email->from_name = "Unity Technologies";
        $email->from_email = "accounts@unity3d.com";
        $email->preview_text = 'Here\'s how to renew your plan';
        $email->content = file_get_contents(EMAIL_DIR . "/unity_legit.htm");
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->show_warning = false;
        $email->type = 'inbox';
        $email->save();

        // 10
        $email = new Email();
        $email->subject = "Delivered: Your Amazon.com order #550-6505006-6505006";
        $email->from_name = "Amazon";
        $email->from_email = "order-confirmation@amazon.com";
        $email->preview_text = 'Delivered: Your Amazon.com order';
        $email->content = file_get_contents(EMAIL_DIR . "/amazon_legit.htm");
        $email->content = str_replace('____asset_path_____', asset("img/email/amazon.jpg"), $email->content);
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->show_warning = false;
        $email->type = 'inbox';
        $email->save();

        // 11
        $email = new Email();
        $email->subject = "Password Reset";
        $email->from_name = "Live Nation";
        $email->from_email = "info@livenation.com";
        $email->preview_text = 'Reset your password';
        $email->content = "<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width=\"100%\"
           style='width:100.0%;border-collapse:collapse;mso-yfti-tbllook:1184;
           mso-padding-alt:0cm 0cm 0cm 0cm'>
           <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
            <td valign=top style='padding:15.0pt 0cm 15.0pt 0cm'>
            <div align=center>
            <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
             style='border-collapse:collapse;mso-yfti-tbllook:1184;mso-padding-alt:
             0cm 0cm 0cm 0cm'>
             <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
              <td width=600 style='width:450.0pt;padding:0cm 0cm 0cm 0cm'>
              <div>
              <div align=center>
              <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
               width=\"100%\" style='width:100.0%;border-collapse:collapse;mso-yfti-tbllook:
               1184;mso-padding-alt:0cm 0cm 0cm 0cm'>
               <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
                <td valign=top style='padding:0cm 0cm 15.0pt 0cm'>
                <div align=center>
                <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
                 style='border-collapse:collapse;mso-yfti-tbllook:1184;mso-padding-alt:
                 0cm 0cm 0cm 0cm'>
                 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:
                  yes'>
                  <td width=600 valign=top style='width:450.0pt;padding:0cm 0cm 0cm 0cm'>
                  <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
                   width=\"100%\" style='width:100.0%;border-collapse:collapse;
                   mso-yfti-tbllook:1184;mso-padding-alt:0cm 0cm 0cm 0cm'>
                   <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:
                    yes'>
                    <td style='padding:0cm 0cm 0cm 0cm'>
                    <div align=center>
                    <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
                     style='border-collapse:collapse;mso-yfti-tbllook:1184;mso-padding-alt:
                     0cm 0cm 0cm 0cm'>
                     <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:
                      yes'>
                      <td width=152 style='width:114.0pt;padding:0cm 0cm 0cm 0cm'>
                      <p class=MsoNormal align=center style='text-align:center'><span
                      style='mso-fareast-font-family:\"Times New Roman\"'><img width=152
                      height=32 id=\"_x0000_i1025\"
                      src=\"https://staticmedia.livenationinternational.com/felix/images/logos/it_logo.png\"
                      style='border-radius:0;outline:0;border-bottom-style:none;
                      border-left-style:none;border-right-style:none;border-top-style:
                      none;display:block;font-size:13px;height:32px;text-align:center;
                      text-decoration:none;width:100%'><o:p></o:p></span></p>
                      </td>
                     </tr>
                    </table>
                    </div>
                    </td>
                   </tr>
                  </table>
                  </td>
                 </tr>
                </table>
                </div>
                </td>
               </tr>
              </table>
              </div>
              </div>
              </td>
             </tr>
             <tr style='mso-yfti-irow:1;mso-yfti-lastrow:yes'>
              <td width=600 style='width:450.0pt;padding:0cm 0cm 0cm 0cm'>
              <div align=center>
              <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
               width=\"100%\" style='width:100.0%;border-collapse:collapse;mso-yfti-tbllook:
               1184;mso-padding-alt:0cm 0cm 0cm 0cm' role=presentation>
               <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
                <td valign=top style='padding:0cm 0cm 0cm 0cm'>
                <div align=center>
                <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
                 style='border-collapse:collapse;mso-yfti-tbllook:1184;mso-padding-alt:
                 0cm 0cm 0cm 0cm' role=presentation>
                 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:
                  yes'>
                  <td width=600 valign=top style='width:450.0pt;padding:0cm 0cm 0cm 0cm'>
                  <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
                   width=\"100%\" style='width:100.0%;border-collapse:collapse;
                   mso-yfti-tbllook:1184;mso-padding-alt:0cm 0cm 0cm 0cm'
                   role=presentation>
                   <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
                    <td style='padding:7.5pt 0cm 7.5pt 0cm'>
                    <h3 align=center style='margin:0cm;text-align:center;line-height:
                    24.0pt;mso-line-height-rule:exactly'><span style='font-size:21.0pt;
                    font-family:Montserrat;mso-fareast-font-family:\"Times New Roman\";
                    color:black'>Hi {user_name}<o:p></o:p></span></h3>
                    </td>
                   </tr>
                   <tr style='mso-yfti-irow:1'>
                    <td style='padding:7.5pt 0cm 7.5pt 0cm'>
                    <p style='margin:0cm;line-height:19.5pt;mso-line-height-rule:exactly'><span
                    style='font-size:12.0pt;font-family:Montserrat;color:black'> to change your password on www.livenation.com, click on the link below.<o:p></o:p></span></p>
                    </td>
                   </tr>
                   <tr style='mso-yfti-irow:2'>
                    <td style='padding:7.5pt 0cm 7.5pt 0cm'>
                    <p class=MsoNormal style='line-height:19.5pt;mso-line-height-rule:
                    exactly'><span style='font-size:12.0pt;font-family:Montserrat;
                    mso-fareast-font-family:\"Times New Roman\";color:black'><a
                    href=\"https://www.livenation.com/myln/resetpassword?mid=17cb06b7-6126-4be8-a7e5-351d1e9c8373&amp;auth=aba703yd-7f7c-479e-9305-b1fd5dcd50d1\"
                    style='cursor:pointer;pointer-events:auto'><i><span
                    style='color:#E21937;text-decoration:none;text-underline:none'>RESET PASSWORD</span></i></a><o:p></o:p></span></p>
                    </td>
                   </tr>
                   <tr style='mso-yfti-irow:3'>
                    <td style='padding:7.5pt 0cm 7.5pt 0cm'>
                    <p style='margin:0cm;line-height:19.5pt;mso-line-height-rule:exactly'><span
                    style='font-size:12.0pt;font-family:Montserrat;color:black'> Remember, your access data allows you to login in every LiveNation website of the world!
                    <o:p></o:p></span></p>
                    </td>
                   </tr>
                   <tr style='mso-yfti-irow:4;mso-yfti-lastrow:yes'>
                    <td style='padding:7.5pt 0cm 7.5pt 0cm'>
                    <p style='margin:0cm;line-height:19.5pt;mso-line-height-rule:exactly'><span
                    style='font-size:12.0pt;font-family:Montserrat;color:black'>See you in the next show,<o:p></o:p></span></p>
                    <p style='margin:0cm;line-height:19.5pt;mso-line-height-rule:exactly'><span
                    style='font-size:12.0pt;font-family:Montserrat;color:black'>the Live Nation team<o:p></o:p></span></p>
                    </td>
                   </tr>
                  </table>
                  </td>
                 </tr>
                </table>
                </div>
                </td>
               </tr>
              </table>
              </div>
              </td>
             </tr>
            </table>
            </div>
            </td>
           </tr>
          </table>";
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->show_warning = false;
        $email->type = 'inbox';
        $email->save();

        /*
        // 12 - False Negative
        $email = new Email();
        $email->subject = "Your Norton Protection subscription expires today";
        $email->from_name = "Norton";
        $email->from_email = "norton.services@protonmail.com";
        $email->preview_text = 'Your Norton Protection subscription expires today';
        $email->content = file_get_contents(EMAIL_DIR . "/norton_phishing.htm");
        $email->date = Carbon::today()->toDateTimeString();
        $email->type = 'inbox';
        $email->save();
        */

        // ** PHISHING EMAILS **

        // PHISHING
        // Email 1 - INSTAGRAM
        $email = new Email();
        $email->subject = "New device login detected";
        $email->from_name = "Instagram";
        $email->from_email = "noreply@lnstagram.com";
        $date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->preview_text = 'Dear user, we\'re writing to inform you that we detected a login to your account from a new device.';
        $email->content = '<div><p class="p1"><strong><img style="display: block; margin-left: auto; margin-right: auto;" src="'. asset("assets/img/email/instagram.png").'" alt="" width="300" height="165" /></strong><strong>Hey user,</strong></p>
        <p> </p>
        <p class="p1">We\'re writing to inform you that we detected a login to your account from a new device.<br /><br /></p>
        <p class="p1"><strong>When:</strong></p>
        <p class="p1"><em> '.  $date .' CEST</em></p>
        <p class="p1"><strong>Device:</strong></p>
        <p class="p1"><em>Huawei P30 Pro</em></p>
        <p class="p1"><strong>Near:<span class="Apple-converted-space"> </span></strong></p>
        <p class="p1"><em>Nashik, India<br /><br /></em></p>
        <p class="p1"><strong>If this was you</strong>, you can ignore this message.<br /><br /></p>
        <p class="p1"><strong>If this wasn\'t you</strong>, click the link below and reset your password to be safe.<br>
        <a id="phishing_link" href="http://92.233.24.33/instagram/login.php" style="text-decoration: underline; color:#3366CC;">instagram.com/reset.</a>
        </p>
        <br/>
        <p class="p1" style="margin-bottom: 5rem">Sincerely,<br/>Instagram Technical Staff</p>
        </div>';
        $email->date = $date;
        //$email->date = Carbon::parse('2022-12-10 18:12')->toDateTimeString();
        $email->type = 'inbox';
        $email->show_warning = true;
        $email->warning_explanation_1 = get_explanation("basic", "http://92.233.24.33/instagram/login.php");
        $email->warning_explanation_2 = get_explanation("ip_url");
        $email->save();


        // Email 2 - AMAZON
        $email = new Email();
        $email->subject = "Problem with your account";
        $email->from_name = "Amazon";
        $email->from_email = "amazon.it@amazonservices.com.cz";
        $email->preview_text = 'Hello customer, We have faced some problems with your account.';
        $email->content = '<div><p class="p1"><img src="'. asset("/assets/img/email/amazon.jpg"). '" alt="" width="100" /></p>
        <p class="p1">Dear Customer,<br /><br />
        We are sorry to inform you that we have registered some problems related to your account. As a consequence, you need to update your account details.
        Unless you update your details within 24 hours (since the opening of this email), your account will be officially and <b>permanently disabled</b>,
        as mentioned in our <i>Terms of Use and Conditions</i>.
        <br>
        You can proceed to the updating procedure by clicking the link below:</p>
        <br>
        <div style="margin-left: auto; margin-right: auto; border-radius: 5px; background-color: #ffd814; color: #000000; display: inline-block; text-align: center;" align="center" id="tooltip-button">
            <a style="color: #000000; text-decoration: none; display: block; padding: 14px 30px 15px;" id="phishing_link" href="https://amazonservices.com.cz/account.php"> Update now </a>
            </div>
        <br><br><p class="p1">Thank you for being part of the Amazon community,<span class="Apple-converted-space"> </span></p>
        <p class="p1">we look forward to hearing from you soon.</p><br>
        <p class="p1">Best regards, <br>Amazon Customer Service</p></div>';
        $email->show_warning = true;
        $email->warning_explanation_1 = get_explanation("basic","https://amazonservices.com.cz/account.php");
        $email->warning_explanation_2 = get_explanation("tld_mispositioned","https://amazonservices.com.cz/account.php");
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->type = 'inbox';
        $email->save();

        //False positive - Facebook
        $email = new Email();
        $email->subject = "Your Facebook password has been modified";
        $email->from_name = "Facebook";
        $email->from_email = "security@facebookmail.com";
        $email->preview_text = 'Hello {user_name}, Your Facebook password has been modified.';
        $email->content ='<body style="margin:0;padding:0;" dir="ltr" bgcolor="#ffffff">
          <table border="0" cellspacing="0" cellpadding="0" align="center" id="email_table" style="border-collapse:collapse;">
            <tr>
              <td id="email_content" style="font-family:Helvetica Neue,Helvetica,Lucida Grande,tahoma,verdana,arial,sans-serif;background:#ffffff;">
                  <table border="0" cellspacing="0" cellpadding="0" style=" border-collapse:collapse;">
                    <tr>
                      <td valign="top" style="padding-right:10px;font-size: 0px;">
                        <img src="' . asset("/assets/img/email/facebook.png").  '" style="border:0;">
                      </td>
                      <td valign="top" style="width:100%;">
                        <table border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:14px;color:#3D4452;width:100%;">
                          <tr>
                            <td style="font-size:14px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;color:#3D4452;padding-bottom:6px;">Hello {user_name},</td>
                          </tr>
                          <tr>
                            <td style="font-size:14px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;color:#3D4452;padding-top:6px;padding-bottom:6px;">Your Facebook password has been modified Saturday 17 December 2022 at 22:29 (UTC+01)
                            </td>
                          </tr>
                          <tr>
                            <td style="font-size:14px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;color:#3D4452;padding-top:6px;padding-bottom:6px;">
                              <table border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-top:5px;margin-bottom:5px;">
                                <tr style="">
                                  <td style="padding-left: 10px">
                                    <span style="color:#808080;">Operating System: </span>
                                    <span style="color:#808080;">Operating System: </span>
                                  </td>
                                  <td style="padding-left: 10px">Windows</td>
                                </tr>
                                <tr style="">
                                  <td style="padding-left: 10px">
                                    <span style="color:#808080;">Browser: </span>
                                  </td>
                                  <td style="padding-left: 10px">Chrome</td>
                                </tr>
                                <tr style="">
                                  <td style="padding-left: 10px">
                                    <span style="color:#808080;">IP Address: </span>
                                  </td>
                                  <td style="padding-left: 10px"> 93.40.196.198</td>
                                </tr>
                                <tr style="">
                                  <td style="padding-left: 10px">
                                    <span style="color:#808080;">Approximate Location: </span>
                                  </td>
                                  <td style="padding-left: 10px">Rome, IT</td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                          <tr>
                            <td style="font-size:14px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;color:#3D4452;padding-top:6px;padding-bottom:6px;">
                              <strong>If you performed this action,</strong>  you can ignore this email.
                            </td>
                          </tr>
                          <tr>
                            <td style="font-size:14px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;color:#3D4452;padding-top:6px;padding-bottom:6px;">
                              <strong>If it wasn\'t you,</strong> <br/>
                              <a href="https://www.facebook.com/hacked/disavow?u=100000125023309&amp;nArdInDS2&amp;lit_IT&amp;ext1548538159" id="phishing_link" style="color:#3b5998;text-decoration:none;">protect your account</a>
                            </td>
                          </tr>
                          <tr>
                            <td style="font-size:14px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;color:#3D4452;padding-top:6px;padding-bottom:6px;"></td>
                          </tr>
                          <tr>
                            <td style="font-size:14px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;color:#3D4452;padding-top:6px;padding-bottom:6px;">Thank you, <br> the Facebook security team</td>
                          </tr>
                          <tr>
                            <td style="font-size:14px;font-family:LucidaGrande,tahoma,verdana,arial,sans-serif;color:#3D4452;padding-top:6px;">&nbsp;</td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
              </td>
              <td width="15" style="display:block;width:15px;">&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
              <td style="">
                <table border="0" width="100%" cellspacing="0" cellpadding="0" align=" left" style="border-collapse:collapse;">
                  <tr style="border-top:solid 1px #e5e5e5;">
                    <td height="19" style="line-height:19px;">&nbsp;</td>
                  </tr>
                   <tr style="align-items: center; display: block">
                    <td style="font-family:Helvetica Neue,Helvetica,Lucida Grande,tahoma,verdana,arial,sans-serif;font-size:11px;color:#aaaaaa;line-height:16px;"> This email was sent by Facebook. If you do not want to receive this kind of emails from Facebook, <a href="https://www.facebook.com/o.php?k=3DAS3IzEaiunZaSIOZ&amp;u=3D100000125023309&amp;mid=3D57fd6086d799fG5af317edf44dG57fd652037c71G18e" style="color:#3b5998;text-decoration:none;">cancel your subscription</a>. <br>Facebook Ireland Ltd., Attention: Community Operations, 4 Grand Canal Square, Dublin 2, Ireland </td>
                  </tr>
                </table>
              </td>
              <td width="15" style="display:block;width:15px;">&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr style="align-items: center; display: block">
              <td width="15" style="display:block;width:15px;">&nbsp;&nbsp;&nbsp;</td>
              <td style="">
                <table border="0" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                  <tr>
                    <td style="font-family:Helvetica Neue,Helvetica,Lucida Grande,tahoma,verdana,arial,sans-serif;font-size:11px;color:#aaaaaa;line-height:16px;">
                      <span style="font-family:Helvetica Neue,Helvetica,LucidaGrande,tahoma,verdana,arial,sans-serif;font-size:11px;color:#aaaaaa;line-height:16px;">To protect your account, do not forward this email. <a href="https://www.facebook.com/email_forward_notice/?mid=57fd6086d799fG5af317edf44dG57fd652037c71G18e" style="color:#3b5998;text-decoration:none;">Discover more.</a>
                      </span>
                    </td>
                  </tr>
                </table>
              </td>
              <td width="15" style="display:block;width:15px;">&nbsp;&nbsp;&nbsp; </td>
                </tr>
            <tr style="">
              <td height="20" style="line-height:20px;" colspan="3">&nbsp;</td>
            </tr>
          </table>
          </td>
          </tr>
          </table>
          </body>
        ';
        $url = "https://www.facebook.com/hacked/disavow?u=100000125023309&amp;nArdInDS2&amp;lit_IT&amp;ext1548538159";
        $email->show_warning = true;
        $email->warning_explanation_1 = get_explanation("basic", $url);
        $email->warning_explanation_2 = get_explanation("link_mismatch", $url);
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->type = 'inbox';
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
