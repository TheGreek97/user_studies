<?php

namespace Database\Seeders;

use App\Models\Email;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailSeeder_only_warnings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //** LEGITIMATE EMAILS **
        // 1
        $email = new Email();
        $email->from_name = "TicketOne Staff";
        $email->from_email = "ecomm.customerservice@ticketone.it";
        $email->subject = "Important notice regarding Blanco in GALLIPOLI";
        $email->preview_text = 'Hi {user_name}, this is to provide you with useful information regarding  Blanco’s event scheduled in GALLIPOLI.';
        $email->content = '<p class="p1"><img style="display: block; margin-left: auto; margin-right: auto;" src="/assets/img/email/ticketone.png" alt="" width="300" height="113" /></p><p class="p1">Hi {user_name},<br /><br />this is to provide you with useful information regarding <strong>Blanco’s event</strong> scheduled in <strong>GALLIPOLI</strong>.<br /><br />We confirm that the concert will be held this evening, <strong>August 4th</strong>, at <strong>Gondar Park</strong>.<br /><br />Below we report what the organizer shared:<br /><br />"BLANCO @ PARCO GONDAR - IMPORTANT SERVICE INFORMATION: <br /><strong>The opening of the gates is scheduled for around 5:30 pm</strong>.<br /><br />We invite you not to go to the venue too early, also to avoid the hottest hours.<br /><br />Blanco\'s concert is supposed to start between 9.30 pm and 10 pm. <br /><br />At the end of the concert, it is advisable to wait at least half an hour before leaving the venue in order not to obstruct traffic. <br /><br />To assure safety, the outflow will be managed in stages by our security personnel, checking the exits at regular intervals of time.<br /><br />Inside the Gondar Park there are food & beverage areas, including vegetarian choices too.<br />The internal regulations of Parco Gondar can be consulted at this link: <a href="/nextstep" style="text-decoration: underline; color: #0001F1;"><span class="s1">https://www.parcogondar.com/regolazione-interno-parco-gondar/</span></a><br /><br /><br />Kind Regards,<br />TicketOne Staff</p>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 365))->toDateTimeString();
        $email->type = 'inbox';
        $email->save();

        // 2
        $email = new Email();
        $email->from_name = "myCicero";
        $email->from_email = "no-reply@mycicero.it";
        $email->subject = "End of parking confirmed";
        $email->preview_text = 'Your parking time is over. Dear user, your parking time is over.';
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
        <td style="background: transparent;" align="center"><img class="CToWUd a6T" style="width: 100%; max-width: 810px; max-height: 170px;" tabindex="0" src="/assets/img/email/mycicero.jpg" alt="header myCicero" data-bit="iit" />
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
        <td style="padding-left: 10px; padding-top: 3px; padding-bottom: 3px; background: #white;" width="97%"><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> <strong> Municipality</strong>: Taranto </span></td>
        <td> </td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-left: 10px; padding-top: 3px; padding-bottom: 3px; background: #white;" width="97%"><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> <strong> Rest area</strong>: Zona 7070 </span></td>
        <td> </td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-left: 10px; padding-top: 3px; padding-bottom: 3px; background: #white;" width="97%"><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> <strong> Vehicle registration number</strong>: DA000DA </span></td>
        <td> </td>
        </tr>
        <tr>
        <td> </td>
        <td style="padding-left: 10px; padding-top: 3px; padding-bottom: 3px; background: #white;" width="97%"><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> <strong> Date and time</strong>: 04/08/2022 14:04 to 04/08/2022 14:36</span></td>
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
        <p><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> If you need help or more details, consult our <a style="color: #a45a95;" href="#"> <span style="font-family: Calibri,sans-serif; color: #a45a95;"> <strong>FAQs</strong></span></a> or contact us at <a style="color: #a45a95;" href="#"> <span style="font-family: Calibri,sans-serif; color: #a45a95;"> <strong>assistenza@mycicero.it</strong></span></a> o at <a style="color: #a45a95; text-decoration: none;"> <span style="font-family: Calibri,sans-serif; color: #a45a95;"> <strong>071 920 7000</strong></span></a>.</span> <br /><br /><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> On our site you will also find: all the information regarding parking, the cities involved in the program , timetables of the public transport and an apposite section to buy the tickets and the transport companies adherent to <strong>myCicero</strong>. </span> <br /><br /><br /><span style="font-family: Calibri,sans-serif; color: #4f4f4f; font-size: 1.1em;"> Have a safe trip! <br /></span> <span style="font-family: Calibri,sans-serif; color: #a45a95; font-size: 1.1em;"> <strong>The myCicero team</strong> </span></p>
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
        $email->date = Carbon::today()->subDays(mt_rand(0, 365))->toDateTimeString();
        $email->type = 'inbox';
        $email->save();

        // 3
        $email = new Email();
        $email->from_name = "UnipolSai Insurance";
        $email->from_email = "insurances@unipolsai.com";
        $email->subject = "Notice of expiry of the Car Policy";
        $email->preview_text = 'Dear customer, we send you an email to remind you of the expiration of your car policy.';
        $email->content = '<div><p class="p1"><img style="display: block; margin-left: auto; margin-right: auto;" src="/assets/img/email/unipolsai.png" alt="" width="200" height="57" /></p>
<p class="p1">Dear Customer,</p>
<p class="p2"> </p>
<p class="p1">we send you an email to remind you of the expiration of your car policy.</p>
<p class="p2"> </p>
<p class="p1">Your UnipolSai Agent is available to evaluate together new solutions for the renewal of your policy.</p>
<p class="p2"> </p>
<p class="p1"><span class="s1">We’d like to</span> thank you for the trust you had put in us and hope you would like to entrust your policy to us in the future.</p>
<p class="p1">Our best regards.</p></div>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 365))->toDateTimeString();
        $email->type = 'inbox';
        $email->save();

        // 4
        $email = new Email();
        $email->subject = "Reset Password Amazon";
        $email->from_name = "Amazon.it";
        $email->from_email = "no-reply@amazon.it";
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
        <td style="width: 50%;"><img id="m_-4745720069737180512amazonLogo" class="CToWUd" width="100"  src="/assets/img/email/amazon.jpg" data-bit="iit" /></td>
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
        <p>To reset your password click <a href="' . url('/nextstep') . '" style="text-decoration: underline; color: #0001F1;">here</a>.</p>
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
        $email->date = Carbon::parse('2022-08-19 15:28')->toDateTimeString();
        $email->type = 'inbox';
        $email->save();

        // 5
        $email = new Email();
        $email->subject = "Changes to the YouTube Terms of Service";
        $email->from_name = "YouTube";
        $email->from_email = "no-reply@youtube.com";
        $email->preview_text = 'We have sent you this email to notify you of an update of the Terms of Service.';
        $email->content = '<div style="margin: 0 auto; padding: 0px; font-family: Roboto,Helvetica Neue,Helvetica,Arial,sans-serif; font-size: 12px; color: #333333;"><center style="width: 100%; table-layout: fixed; background-color: #ffffff;">
        <div style="margin: 0 auto; max-width: 600px;">
        <table style="margin: 0 auto; width: 100%; max-width: 600px;" cellspacing="0" cellpadding="0" align="center">
        <tbody>
        <tr>
        <td class="m_2866944059712879390table" style="padding-top: 10px;" align="center" valign="top">
        <table border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
        <tbody>
        <tr>
        <td align="center" valign="top">
        <table style="height: 791px; width: 100%;" border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
        <tbody>
        <tr style="height: 37px;">
        <td class="m_2866944059712879390logo_pad" style="padding: 27px 30px; border-radius: 3px 3px 0px 0px; height: 37px;" align="center" valign="top" bgcolor="#ffffff">
        <table border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
        <tbody>
        <tr>
        <td align="left" valign="middle">
        <table cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td align="left" valign="middle"><a style="text-decoration: none; width: 131px;" href="https://www.google.com/appserve/mkt/p/AFnwnKWD9_sVxZ8nrhbie12LAHZUxJFaRjYZ9Jl2qDnq9DXoGZaPE1AwH9Qh5cHd22e9jvAQSZwfx-KDxyBcWCBGAvL-qbxkOZEe3LlJ4eglgNTlQk5ud1wM"  data-saferedirecturl="https://www.google.com/url?q=https://www.google.com/appserve/mkt/p/AFnwnKWD9_sVxZ8nrhbie12LAHZUxJFaRjYZ9Jl2qDnq9DXoGZaPE1AwH9Qh5cHd22e9jvAQSZwfx-KDxyBcWCBGAvL-qbxkOZEe3LlJ4eglgNTlQk5ud1wM&source=gmail&ust=1661002230640000&usg=AOvVaw1c_v7LbLUn1cZsv_ZyUMHS"><img class="m_2866944059712879390logo_size CToWUd" style="display: block; max-width: 131px;" title="YouTube" src="/assets/img/email/youtube.png" alt="YouTube" width="150" height="auto" border="0" data-bit="iit" /></a></td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr style="height: 100px;">
        <td class="m_2866944059712879390pad-15" style="font-family: Roboto, Arial, sans-serif; color: #808080; font-weight: 300; font-size: 16px; line-height: 20px; vertical-align: middle; text-decoration: none; padding: 0px 30px; height: 100px;" align="left" valign="top">We have sent you this email to notify you of an update of the <a style="text-decoration: none; color: #2196f3;" href="' . url('/next_step') . '">Terms of Service</a> (the "Terms") with some clarifications and greater transparency for our users. The Terms were similarly updated in the United States in November 2020. Your access to and use of YouTube services are not expected to change significantly as far as you are concerned.</td>
        </tr>
        <tr style="height: 40px;">
        <td class="m_2866944059712879390pad-15" style="font-family: Roboto, Arial, sans-serif; color: #808080; font-weight: 300; font-size: 16px; line-height: 20px; vertical-align: middle; text-decoration: none; padding: 20px 30px 0px; height: 40px;" align="left" valign="top">Here is a summary of the variations:<br /><br /></td>
        </tr>
        <tr style="height: 359px;">
        <td class="m_2866944059712879390pad-25" style="padding: 10px 30px 0px 50px; height: 329px;" align="left" valign="top">
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td style="font-family: Roboto,Arial,sans-serif; color: #808080; font-weight: 500; font-size: 16px; line-height: 20px; padding-right: 10px;" align="left" valign="top">•</td>
        <td style="font-family: Roboto,Arial,sans-serif; color: #808080; font-weight: 300; font-size: 16px; line-height: 20px;" align="left" valign="top"><strong>Facial Recognition Restrictions:</strong> The Terms of Service already state that you are not allowed to collect any information that could be potentially used to identify a person without their permission. While this principle has always included facial recognition data as well, the new Terms clearly state this.</td>
        </tr>
        <tr>
        <td style="font-family: Roboto,Arial,sans-serif; color: #808080; font-weight: 500; font-size: 16px; line-height: 20px; padding-right: 10px; padding-top: 10px;" align="left" valign="top">•</td>
        <td style="font-family: Roboto,Arial,sans-serif; color: #808080; font-weight: 300; font-size: 16px; line-height: 20px; padding-top: 10px;" align="left" valign="top"><strong>YouTube\'s right to monetize content:</strong> YouTube has the right to monetize all content on the platform and to place ads on videos from channels that are not members of the YouTube Partner Program.</td>
        </tr>
        <tr>
        <td style="font-family: Roboto,Arial,sans-serif; color: #808080; font-weight: 500; font-size: 16px; line-height: 20px; padding-right: 10px; padding-top: 10px;" align="left" valign="top">•</td>
        <td style="font-family: Roboto,Arial,sans-serif; color: #808080; font-weight: 300; font-size: 16px; line-height: 20px; padding-top: 10px;" align="left" valign="top"><strong><strong>Royalty Payments and Withholding Tax: </strong></strong>For creators who receive income, such as payment, will be treated as royalty for US tax purposes and Google will apply withholding taxes where required by the law.</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr style="height: 120px;">
        <td class="m_2866944059712879390pad-15" style="font-family: Roboto, Arial, sans-serif; color: #808080; font-weight: 300; font-size: 16px; line-height: 20px; vertical-align: middle; padding: 20px 30px 0px; height: 120px;" align="left" valign="top"><strong>We encourage you to read the updates to the Terms carefully. The new <a style="text-decoration: none; color: #2196f3;" href="https://www.google.com/appserve/mkt/p/AFnwnKUUVx0drzA8Oi3K4pHw2GVkd4WcOCYpb8uvPB7LMjQgKadXtsBLUh6DUsEIfIpIZg_FpyQJzKXZlJ2aaZbC8o3DgZ1p6umNQDRCTHQ6yCgblGaeLxsHVRHIbqqfAaWoQHxNfjFc9Ks" rel="noopener" data-saferedirecturl="https://www.google.com/url?q=https://www.google.com/appserve/mkt/p/AFnwnKUUVx0drzA8Oi3K4pHw2GVkd4WcOCYpb8uvPB7LMjQgKadXtsBLUh6DUsEIfIpIZg_FpyQJzKXZlJ2aaZbC8o3DgZ1p6umNQDRCTHQ6yCgblGaeLxsHVRHIbqqfAaWoQHxNfjFc9Ks&source=gmail&ust=1661002230641000&usg=AOvVaw3q3-sNp3QGGepmXXEck01R">Terms</a></strong><strong> will become effective on June 1, 2021 for users outside the United States.</strong>
        <p class="p1">By keep on using YouTube after that date, you are implicitly accepting the new Terms. Please note that if you allow your child to use YouTube Kids, you will also accept the new Terms on their behalf.</p>
        <p class="p1">If you need more information, please visit our <a style="text-decoration: none; color: #2196f3;" href="#" rel="noopener">Help Center</a>.</p>
        </td>
        </tr>
        <tr style="height: 45px;">
        <td style="height: 45px;" align="left" width="100%">
        <table style="width: 100%!important;" border="0" width="100%" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td align="center" width="100%" height="10"> </td>
        </tr>
        <tr>
        <td style="max-width: 540px; margin: 0 auto; background-color: #cccccc; font-size: 1px; line-height: 1px; height: 1px; max-height: 1px; display: block;" bgcolor="#cccccc" height="1"> </td>
        </tr>
        <tr>
        <td align="center" width="100%" height="10"> </td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr style="height: 120px;">
        <td style="padding-top: 0px; padding-bottom: 10px; height: 120px;" align="left" valign="top">
        <table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
        <tbody>
        <tr>
        <td class="m_2866944059712879390padfoot" style="font-family: Roboto,Helvetica Neue,Helvetica,Arial,sans-serif; font-size: 12px; color: #9a9a9a; line-height: 18px; font-weight: 300; padding: 20px 30px 20px 30px;" align="left" valign="top"><a style="text-decoration: none; color: #9a9a9a;" href="#m_2866944059712879390_">© 2022 Google Ireland Ltd, Gordon House, Barrow Street, Dublin 4, Ireland. </a></td>
        </tr>
        <tr>
        <td class="m_2866944059712879390padfoot" style="padding-bottom: 20px; font-family: Roboto,Helvetica Neue,Helvetica,Arial,sans-serif; font-size: 12px; color: #9a9a9a; line-height: 18px; padding-left: 30px; padding-right: 30px; font-weight: 300;" align="left" valign="top">We sent you this email to notify you of important changes affecting the YouTube Terms of Service.</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        </tbody>
        </table>
        </div>
        </center></div>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 365))->toDateTimeString();
        $email->type = 'inbox';
        $email->save();

        // 6
        $email = new Email();
        $email->subject = "Access to Digital Banking";
        $email->from_name = "Monte dei Paschi di Siena Bank";
        $email->from_email = "no-reply@mps.it";
        $email->preview_text = '{user_name} you have just logged into mps.it';
        $email->content = '<div><p><img style="display: block; margin-left: auto; margin-right: auto;" src="/assets/img/email/mps.png" alt="" width="300" height="109" /></p>
        <p>Hi {user_name},</p><br>
        <p>you have just logged into <a href="' . url('/nextstep') . '" style="text-decoration: underline; color: #0001F1;">www.mps.it</a>.</p>
        <br><p>Best regards,<br />the M.P.S. Team.</p></div>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 365))->toDateTimeString();
        $email->type = 'inbox';
        $email->save();

        // 7
        $email = new Email();
        $email->subject = "Our tips to better prepare you for the upcoming season";
        $email->from_name = "DAZN";
        $email->from_email = "newsletter@dazn.it";
        $email->preview_text = 'Hi {user_name}, We are ready for a new season of great sport.';
        $email->content = '<div><p class="p1"><img style="display: block; margin-left: auto; margin-right: auto;" src="/assets/img/email/dazn.png" alt="" width="102" height="102" /></p>
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
        <br><p class="p1">For any further information, please read our guide to <strong>DAZN subscription plans</strong> at <a href="#">this link</a> or contact our <a href="/endtask">customer service</a>.</p>
        <p class="p1"> </p>
        <p class="p1">The DAZN Team</p></div>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 365))->toDateTimeString();
        $email->type = 'inbox';
        $email->save();

        // 8
        $email = new Email();
        $email->subject = "Covid analysis results";
        $email->from_name = "Pharmacy";
        $email->from_email = "pharmacy-24@asl.bari.it";
        $email->preview_text = 'Hi {user_name}, the analysis result is ready.';
        $email->content = '<div><p>Hi {user_name},</p><br>
        <p>the result of the swab carried out today is <strong>negative</strong>.</p>
        <br>
        <p>Thank you for having chosen our services.<br /><br />Best regards,<br />Pharmacy</p>
        <p><img src="/assets/img/email/farmacia.jpeg" alt="" width="100" height="75" /></p></div>';
        $email->date = Carbon::today()->subDays(mt_rand(0, 365))->toDateTimeString();
        $email->type = 'inbox';
        $email->save();

        // ** PHISHING EMAILS **

        // Tooltip
        // 1
        $email = new Email();
        $email->subject = "New device login detected";
        $email->from_name = "TikTok";
        $email->from_email = "noreply@tiktok.com";
        $email->preview_text = 'Hi, we\'re writing to inform you that we detected a login to your account from a new device.';
        $email->content = '<div><p class="p1"><strong><img style="display: block; margin-left: auto; margin-right: auto;" src="/assets/img/email/tiktok.jpeg" alt="" width="300" height="165" /></strong><strong>Hey user,</strong></p>
        <p> </p>
        <p class="p1">We\'re writing to inform you that we detected a login to your account from a new device.<br /><br /></p>
        <p class="p1"><strong>When:</strong></p>
        <p class="p1"><em>10/08/2022 17:38 CEST</em></p>
        <p class="p1"><strong>Device:</strong></p>
        <p class="p1"><em>Huawei P30 Pro</em></p>
        <p class="p1"><strong>Near:<span class="Apple-converted-space"> </span></strong></p>
        <p class="p1"><em>Nashik, India<br /><br /></em></p>
        <p class="p1"><strong>If this was you</strong>, you can ignore this message.<br /><br /></p>
        <p class="p1"><strong>If this wasn\'t you</strong>, click the link below and reset your password to be safe.
        <a href="http://92.233.24.33/tiktok/login.php" style="text-decoration: underline; color:#3366CC; margin:auto; border: 4px solid  #b80000;">tiktok.com/reset</span>.</a></p>
        <span id="tooltip_link" class="tooltiptext">The target URL <b>http://92.233.24.33/tiktok/login.php</b> is an imitation of the original one, <b>https://tiktok.com</b>. This site might be intended to take you to a different place. You might be disclosing private information.
        Link goes to: <a href="#" style="text-decoration: underline;/* color: #0001F1; */"><span class="s2">http://92.233.24.33/tiktok/login.php</span></a>
        </span>
        </div>';
        $email->date = Carbon::parse('2022-08-10 17:38')->toDateTimeString();
        $email->type = 'inbox';
        $email->warning_type = 'tooltip';
        $email->save();
        // 2
        $phish_url = "amazonservices.sc03osd.cz/account.php";
        $email = new Email();
        $email->subject = "Problem with your account";
        $email->from_name = "Amazon";
        $email->from_email = "amazon.it@amazonservices.sc03osd.cz";
        $email->preview_text = 'Hello customer, We have faced some problems with your account.';
        $email->content = '<div><p class="p1"><img src="/assets/img/email/amazon.jpg" alt="" width="100" /></p>
        <p class="p1">Dear Customer,<br /><br />
        We are sorry to inform you that we have registered some problems related to your account. As a consequence, you need to update your account details.
        Unless you update your details within 24 hours (since the opening of this email), your account will be officially and <b>permanently disabled</b>,
        as mentioned in our <i>Terms of Use and Conditions</i>.
        <br>
        You can proceed to the updating procedure by clicking the link below:</p>
        <br>
        <div style="margin-left: auto; margin-right: auto; border-radius: 5px; background-color: #ffd814; color: #000000; display: inline-block; text-align: center;" align="center">
        <table class="tooltip" data="amazon link"  style="border: 4px solid  #b80000;">
            <tbody>
               <tr>
                  <td>
                    <a style="color: #000000; text-decoration: none; display: block; padding: 14px 30px 15px;" href="'. $phish_url .'"> Update now </a>
                    <span id="tooltip_link" class="tooltiptext">
                    The target URL <b>'. $phish_url .'</b> is an imitation of the original one, <b>https://amazon.com</b>. This site might be intended to take you to a different place. You might be disclosing private information.
                    Link goes to: <a href="#" style="text-decoration: underline;/* color: #0001F1; */"><span class="s2">'. $phish_url .'</span></a>
                    </span>
                  </td>
               </tr>
           </tbody>
        </table>
        </div>
        <br><br><p class="p1">Thank you for being part of the Amazon community,<span class="Apple-converted-space"> </span></p>
        <p class="p1">we look forward to hearing from you soon.</p><br>
        <p class="p1">Best regards, <br>Amazon Customer Service</p></div>';
        $email->warning_type = 'tooltip';
        $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
        $email->type = 'inbox';
        $email->save();

        // Other warnings
        foreach (['popup_link', 'popup_email', 'base_passive', 'no_warning'] as $type) {
            // Email 1
            $email = new Email();
            $email->subject = "New device login detected";
            $email->from_name = "TikTok";
            $email->from_email = "noreply@tiktok.com";
            $email->preview_text = 'Hi, we\'re writing to inform you that we detected a login to your account from a new device.';
            $email->content = '<div><p class="p1"><strong><img style="display: block; margin-left: auto; margin-right: auto;" src="/assets/img/email/tiktok.jpeg" alt="" width="300" height="165" /></strong><strong>Hey user,</strong></p>
            <p> </p>
            <p class="p1">We\'re writing to inform you that we detected a login to your account from a new device.<br /><br /></p>
            <p class="p1"><strong>When:</strong></p>
            <p class="p1"><em>10/08/2022 17:38 CEST</em></p>
            <p class="p1"><strong>Device:</strong></p>
            <p class="p1"><em>Huawei P30 Pro</em></p>
            <p class="p1"><strong>Near:<span class="Apple-converted-space"> </span></strong></p>
            <p class="p1"><em>Nashik, India<br /><br /></em></p>
            <p class="p1"><strong>If this was you</strong>, you can ignore this message.<br /><br /></p>
            <p class="p1"><strong>If this wasn\'t you</strong>, click the link below and reset your password to be safe.
            <a href="http://92.233.24.33/tiktok/login.php" style="text-decoration: underline; color:#3366CC;">tiktok.com/reset</span>.</a></p>
            </div>';
            $email->date = Carbon::parse('2022-08-10 17:38')->toDateTimeString();
            $email->type = 'inbox';
            if ($type != ['no_warning']) {
                $email->warning_explanation_1 = "The target URL <b>http://92.233.24.33/tiktok/login.php</b> is an imitation of the original one, <b>https://tiktok.com</b>. This site might be intended to take you to a different place. You might be disclosing private information.";
            }
            $email->warning_type = $type;
            $email->save();

            // Email 2
            $email = new Email();
            $email->subject = "Problem with your account";
            $email->from_name = "Amazon";
            $email->from_email = "amazon.it@amazonservices.sc03osd.cz";
            $email->preview_text = 'Hello customer, We have faced some problems with your account.';
            $email->content = '<div><p class="p1"><img src="/assets/img/email/amazon.jpg" alt="" width="100" /></p>
            <p class="p1">Dear Customer,<br /><br />
            We are sorry to inform you that we have registered some problems related to your account. As a consequence, you need to update your account details.
            Unless you update your details within 24 hours (since the opening of this email), your account will be officially and <b>permanently disabled</b>,
            as mentioned in our <i>Terms of Use and Conditions</i>.
            <br>
            You can proceed to the updating procedure by clicking the link below:</p>
            <br>
            <div style="margin-left: auto; margin-right: auto; border-radius: 5px; background-color: #ffd814; color: #000000; display: inline-block; text-align: center;" align="center">
            <a style="color: #000000; text-decoration: none; display: block; padding: 14px 30px 15px;" href="https://amazonservices.sc03osd.cz/account.php"> Update now </a></div>
            <br><br><p class="p1">Thank you for being part of the Amazon community,<span class="Apple-converted-space"> </span></p>
            <p class="p1">we look forward to hearing from you soon.</p><br>
            <p class="p1">Best regards, <br>Amazon Customer Service</p></div>';
            if ($type != ['no_warning']) {
                $email->warning_explanation_1 = "The target URL <b>https://amazonservices.sc03osd.cz/account.php</b> is an imitation of the original one, <b>https://amazon.com</b>. This site might be intended to take you to a different place. You might be disclosing private information.";
            }
            $email->warning_type = $type;
            $email->date = Carbon::today()->subDays(mt_rand(0, 15))->toDateTimeString();
            $email->type = 'inbox';
            $email->save();
        }
    }
}
