<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $table = "trainings";

    protected $fillable = [
        "introduction",
        "scenario",
        "defense_strategies",
        "exercises",
        "conclusions",
        "generated",
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setNonCustomizedVersion()
    {
        $user_name = $this->user->name;
        $length = $this->user->training_length;
        if ($length == "long") {
            $intro = "<div>
  <p>Hello {USER_NAME}, and welcome to our training course on recognizing phishing attacks. In today’s ever-connected digital world, phishing remains one of the most common and dangerous threats. Phishing is a form of cyber attack where malicious individuals or groups try to trick you into sharing personal information or downloading harmful software. They often disguise themselves as trustworthy entities—like banks, social media platforms, or even friends—to lure you into their trap.</p>

  <p>This problem is dangerous because the attackers are not just using technology; they are also exploiting the natural human emotions and biases. For example, phishing emails often create a sense of urgency by warning you that your account will be locked or that you have won a prize if you act immediately. These tactics tap into psychological vulnerabilities such as fear, curiosity, and trust. As a result, even careful users like you, {USER_NAME}, might sometimes fall for these well-crafted deceptions.</p>

  <p>Throughout this course, we will guide you step by step on how to recognize and avoid these types of attacks. In the next parts of our module, you will journey through several key areas:</p>

  <ul>
    <li>A real-world phishing scenario that shows the clever tricks attackers use.</li>
    <li>Defensive strategies that empower you to safeguard your personal information and secure your devices.</li>
    <li>Interactive exercises that let you practice identifying potential phishing attempts in a safe learning environment.</li>
    <li>Clear conclusions and actionable takeaways that will reinforce your ability to prevent phishing attacks.</li>
  </ul>

  <p>By the end of this training, you will be more informed, confident, and equipped with the practical tips you need to navigate the web safely. Thank you for joining us in this important conversation, {USER_NAME}. Let’s dive in and start strengthening your defenses against phishing.</p>
</div>";
        } else {
            $intro = "<div>
  <p>Hello {USER_NAME}, and welcome to this anti‑phishing training course. In this brief session, you will learn what phishing is, why it poses a serious threat, and how attackers exploit both our technological vulnerabilities and our human psychology. Phishing refers to deceptive practices where cybercriminals trick you into revealing personal or financial information by posing as trustworthy institutions. This crime preys on common psychological vulnerabilities such as the tendency to trust familiar names and urgent or emotionally charged messages that push you into quick decisions.</p>
  <p>Throughout this module, we will examine real-world phishing scenarios, explore practical defense strategies, and engage you in interactive exercises designed to sharpen your awareness and response. By understanding these deceptive tactics and preparing yourself, you will be better equipped to protect your personal data and maintain your online security.</p>
</div>";
        }
        $intro = str_replace("{USER_NAME}", $user_name, $intro);
        $this->introduction = $intro;

        if ($length == "long") {
            $scenario = '<div>
  <p>{USER_NAME}, imagine this scenario: You’ve just finished your morning coffee and are about to start your work when you notice a new email pop up in your inbox. At first glance, it appears to be from your bank—a trusted institution you rely on every day. The email immediately catches your attention with its urgent tone and bold message. However, as you read on, you begin to notice a few red flags that raise your doubts about whether this email is genuine.</p>

  <p>The email begins with details that seem professional at first. It shows a “From” field with an address that looks almost familiar: <span style="color: blue; text-decoration: underline; cursor: pointer;" onclick="toggleDescription(\'descSender\');">support@yourbank.com</span>. Yet, when you consider it more carefully, you wonder if the sender’s email address might have been manipulated to appear trustworthy—a common tactic in phishing attacks known as email spoofing.</p>

  <p>Below, you see the subject line: <strong>Urgent: Your account has been compromised!</strong> The message immediately instills a sense of alarm by using urgency and fear. The body of the email is equally alarming. It warns you that your account security is at risk and insists on immediate action to resolve the issue. This is a classic psychological ploy where attackers exploit emotions like fear and urgency, making you more likely to act without scrutinizing the details.</p>

  <p>Here is the simulated phishing email you might receive:</p>

  <div style="border:1px solid #ccc; padding: 10px; background-color: #fafafa;">
    <p><strong>From:</strong> <span style="color: blue; text-decoration: underline; cursor: pointer;" onclick="toggleDescription(\'descSender\');">support@yourbank.com</span></p>
    <p><strong>Subject:</strong> Urgent: Your account has been compromised!</p>
    <p>Dear {USER_NAME},</p>
    <p>We have detected unusual activity on your account. Due to these suspicious activities, we have temporarily suspended your account to protect your personal data. To restore access, please verify your account immediately by clicking the link below:</p>
    <p><a href="http://www.secure-your-bank-update.com" onclick="event.preventDefault(); toggleDescription(\'descLink\');" style="color: blue; text-decoration: underline;">Click here to verify your account</a></p>
    <p>If you do not verify your account within the next 24 hours, your account will be permanently locked.</p>
    <p>Please ignore this email if you believe this message has been sent to you in error.</p>
    <p>Thank you,</p>
    <p>Your Bank Security Team</p>
    <p>PS: <span style="color: blue; text-decoration: underline; cursor: pointer;" onclick="toggleDescription(\'descUrgency\');">Immediate Action Required</span></p>
  </div>

  <div id="descSender" style="display:none; border:1px dashed #999; margin: 10px 0; padding: 5px; background-color: #eef;">
    <p><strong>Suspicious Element: Spoofed Sender</strong></p>
    <p>This sender address might look familiar, but attackers often use spoofed email addresses that mimic trusted sources. They slightly alter characters or use similar domains to deceive you. Always verify the sender\'s address carefully for any irregularities.</p>
  </div>

  <div id="descLink" style="display:none; border:1px dashed #999; margin: 10px 0; padding: 5px; background-color: #eef;">
    <p><strong>Suspicious Element: Deceptive URL</strong></p>
    <p>The link text appears to lead to a secure page, but the actual URL (http://www.secure-your-bank-update.com) may not belong to your bank. Attackers often craft fake URLs that look similar to real ones. Before clicking on any link, hover over it and check the actual address carefully.</p>
  </div>

  <div id="descUrgency" style="display:none; border:1px dashed #999; margin: 10px 0; padding: 5px; background-color: #eef;">
    <p><strong>Suspicious Element: Urgency and Pressure</strong></p>
    <p>Phishing emails typically create a false sense of urgency. By stating that immediate action is required, they attempt to rush your decision-making process. This urgency might override your usual caution, making you more vulnerable to providing personal information without proper verification.</p>
  </div>

  <p>After reading the email, you, {USER_NAME}, are now at an interactive decision point. What would you do if you received an email like this?</p>

  <form id="decisionForm">
    <fieldset>
      <legend>Please select the action you think is best:</legend>
      <label><input type="radio" name="decision" value="click"> Click the link immediately to verify my account</label><br>
      <label><input type="radio" name="decision" value="report"> Report the email to my IT or security team</label><br>
      <label><input type="radio" name="decision" value="ignore"> Delete the email and ignore it</label>
    </fieldset>
    <br>
    <button type="submit" style="cursor: pointer;">Submit Your Decision</button>
  </form>

  <div id="feedback" style="margin-top: 15px; padding: 10px; border: 1px solid #ccc; display:none;"></div>

  <p>Let’s reflect on what you just encountered. Phishing emails often play on our emotions by using urgency, fear, and even politeness to trick us into taking immediate actions without proper scrutiny. They may also include deceptive sender information and URLs that seem legitimate but are, in fact, designed to capture your sensitive information. In the next part of our training, we’ll explore effective defense strategies to counter these tactics and enhance your ability to detect such threats before any damage can be done.</p>

  <script>
    function toggleDescription(id) {
      var elem = document.getElementById(id);
      if (elem.style.display === "none" || elem.style.display === "") {
        elem.style.display = "block";
      } else {
        elem.style.display = "none";
      }
    }

    document.getElementById("decisionForm").addEventListener("submit", function(e) {
      e.preventDefault();
      var feedbackDiv = document.getElementById("feedback");
      var decision = document.querySelector(\'input[name="decision"]:checked\');

      if (!decision) {
        feedbackDiv.style.display = "block";
        feedbackDiv.innerHTML = "<p>Please select an option before submitting your decision, {USER_NAME}.</p>";
        return;
      }

      var decisionValue = decision.value;
      if (decisionValue === "click") {
        feedbackDiv.innerHTML = "<p><strong>Feedback:</strong> Clicking the link immediately could lead to exposing your personal information to attackers. This choice takes advantage of the urgency created by the email, putting you at risk of malware or data theft. It\'s important, {USER_NAME}, to resist the pressure and verify the legitimacy of the email before taking any action.</p>";
      } else if (decisionValue === "report") {
        feedbackDiv.innerHTML = "<p><strong>Feedback:</strong> Reporting the email to your IT or security team is a wise decision, {USER_NAME}. It helps your organization track such attempts and protect others from falling victim to similar scams. Remember, sharing suspicious emails can play a critical role in your company\'s cybersecurity defense.</p>";
      } else if (decisionValue === "ignore") {
        feedbackDiv.innerHTML = "<p><strong>Feedback:</strong> Simply deleting the email might keep you safe for the moment, but it doesn\'t help your organization understand the threat. By reporting such emails, you contribute to a broader awareness of phishing tactics, which in turn strengthens the overall defense against these scams.</p>";
      }

      feedbackDiv.style.display = "block";
    });
  </script>

  <p>This phishing scenario has given you a firsthand look at how attackers create convincing emails using multiple deceptive techniques. By interacting with this simulation, you learn to recognize the subtle cues—like suspicious sender addresses, deceptive URLs, and manipulative urgency messages—that indicate a potential threat. As you continue with the training, keep these insights in mind and remember that a cautious, informed approach is your best defense against phishing attacks.</p>
</div>';
        } else {
            $scenario = '<div>
  <p>{USER_NAME}, imagine this: You’re checking your inbox during a busy afternoon when you suddenly receive an unexpected email. The subject line reads “Urgent: Verify Your Account Now” from what appears to be a familiar name. The email claims that your account has been compromised, urging you to take immediate action to secure it. As you read through the email, you notice a few details that feel “off” — a sender address that doesn’t quite look right and a clickable link that promises quick resolution. This scenario is realistic and designed to show you how phishing attacks often exploit our natural sense of urgency and trust in well-known brands.</p>

  <p><strong>Simulated Phishing Email:</strong></p>
  <div style="border:1px solid #ccc; padding:10px; margin-bottom:15px;">
    <p><strong>From:</strong> <span style="color:blue; text-decoration:underline; cursor:pointer;" onclick="toggleTechnique(\'techniqueSender\')">support@paypal.com</span> (spoofed)</p>
    <p><strong>Subject:</strong> Urgent: Verify Your Account Now</p>
    <p>Dear {USER_NAME},</p>
    <p>We have detected unusual activity in your account. To secure your funds immediately, please click <a href="http://www.verify-paypal.com" onclick="event.preventDefault(); toggleTechnique(\'techniqueLink\');" style="color:blue; text-decoration:underline;">here</a> to verify your account details.</p>
    <p>This action must be completed within 24 hours to avoid account suspension.</p>
    <p>Thank you,<br>The PayPal Security Team</p>
  </div>

  <div id="techniqueSender" style="display:none; background-color:#f9f9f9; border:1px dashed #888; padding:5px; margin-bottom:10px;">
    <p><strong>Spoofed Sender Details:</strong> Cybercriminals often mimic legitimate email addresses to trick you into believing the message comes from a trusted source.</p>
  </div>
  <div id="techniqueLink" style="display:none; background-color:#f9f9f9; border:1px dashed #888; padding:5px; margin-bottom:10px;">
    <p><strong>Deceptive URL:</strong> The link may look similar to a legitimate site, but hovering over it might reveal a suspicious URL. Attackers use such techniques to lure you into entering sensitive information.</p>
  </div>

  <p><strong>What Would You Do?</strong></p>
  <form id="decisionForm">
    <p>
      <input type="radio" id="option1" name="decision" value="click">
      <label for="option1">Click the link to verify my account.</label>
    </p>
    <p>
      <input type="radio" id="option2" name="decision" value="ignore">
      <label for="option2">Ignore the email and report it to IT/Security.</label>
    </p>
    <button type="submit">Submit Your Decision</button>
  </form>

  <div id="feedback" style="margin-top:15px; font-weight:bold;"></div>

  <script>
    function toggleTechnique(id) {
      var elem = document.getElementById(id);
      if (elem.style.display === "none") {
        elem.style.display = "block";
      } else {
        elem.style.display = "none";
      }
    }

    document.getElementById("decisionForm").addEventListener("submit", function(event) {
      event.preventDefault();
      var decision = document.querySelector(\'input[name="decision"]:checked\');
      var feedbackDiv = document.getElementById("feedback");

      if (!decision) {
        feedbackDiv.textContent = "Please select an option before submitting.";
        return;
      }

      if (decision.value === "click") {
        feedbackDiv.textContent = "{USER_NAME}, clicking the link might lead you to a malicious website that can steal your information. It’s best to double-check and verify with a trusted source.";
      } else if (decision.value === "ignore") {
        feedbackDiv.textContent = "Good choice, {USER_NAME}! Ignoring and reporting suspicious emails is a safe practice that helps protect your personal data.";
      }
    });
  </script>
</div>';
        }
        $scenario = str_replace("{USER_NAME}", $user_name, $scenario);
        $this->scenario = $scenario;

        if ($length == "long") {
            $defense_strategies = '<div>
  <p>{USER_NAME}, in this section we will explore practical defense strategies to help you stay one step ahead of phishing attacks. Understanding and implementing these strategies can mean the difference between keeping your sensitive data safe or falling victim to a sophisticated scam. Let’s dive into clear, actionable steps that you can incorporate into your daily digital habits.</p>

  <p>One of the first lines of defense is to always double-check the information presented in any email that raises your suspicion. For instance, a critical strategy is to verify the sender’s email address. Attackers often use spoofed email addresses that mimic those of reputable organizations. When examining an email, hover your cursor over the sender’s address to display the full email string. This can help you detect subtle errors or unusual domains. For example, if you expect an email from “support@yourbank.com” but notice a slight variation, such as “support@yourbank-security.com,” that discrepancy is an immediate red flag.</p>

  <p>Another vital tactic is to inspect the URL contained within any links present in the email. Phishers often embed deceptive URLs in their messages. Before clicking any link, place your mouse over it. Most browsers will reveal a preview of the link’s destination, which should start with “https://” for a secure connection. Look closely at the domain: if it’s not the official domain of the institution, it might be harmful. For example, an email urging you to check your account might display a link like “http://www.secure-your-bank-update.com” instead of the more authentic “https://www.yourbank.com”. Always be cautious, and if something doesn’t look right, it’s better not to click.</p>

  <p>Here are several actionable defense strategies you can follow:</p>

  <ul>
    <li><strong>Verify Sender Details:</strong> Always inspect the sender’s email address. Check for any misspellings, added characters, or unusual domains. Genuine emails from your bank or trusted companies will typically come from an official and easily recognizable address.</li>
    <li><strong>Inspect URLs Carefully:</strong> Whenever you see a link, hover over it to view the actual URL. Look out for URLs that use “http://” instead of “https://,” and verify that the domain names precisely match those of the authentic organization.</li>
    <li><strong>Check for Personalization:</strong> Authentic messages from companies generally include personal details that confirm your identity, such as your name or part of your account number. Generic greetings such as "Dear Customer" could indicate a mass phishing attempt.</li>
    <li><strong>Be Wary of Urgent Language:</strong> Phishing emails often create a sense of urgency to spark immediate action. An email that pressures you to act quickly, such as claiming your account will be "locked" soon, is often leveraging fear to manipulate you.</li>
    <li><strong>Examine Email Formatting:</strong> Look for inconsistencies or poor formatting. Many phishing emails have typos, grammatical errors, or mismatched logos and colors that don’t align with the organization’s standard branding.</li>
    <li><strong>Use a Trusted Email Client:</strong> Some email services offer built-in phishing filters that help detect and flag suspicious emails. Make sure these features are enabled for an additional layer of protection.</li>
    <li><strong>Update Security Software:</strong> Keep your antivirus and anti-malware software current. These tools often provide extra protection against malicious links and attachments.</li>
    <li><strong>Enable Two-Factor Authentication (2FA):</strong> Even if your credentials are compromised, having 2FA adds an extra step, making it harder for attackers to gain unauthorized access to your accounts.</li>
  </ul>

  <p>Implementing these strategies means that you’re transforming caution into a habit. Remember, no single strategy is fail-safe on its own; the key is to apply multiple layers of security to reduce your vulnerability to phishing attacks. For example, if you receive an email from your bank, take a moment to verify the sender’s address, hover over any links to review their true destination, and carefully read the text for any signs of urgency or pretext that might seem unusual.</p>

  <p>Another fundamental aspect of recognizing genuine emails involves checking the domain name carefully. When you receive a communication from an institution like your bank, the email should originate from the actual bank’s domain. If you receive an email from a domain like “@yourbank.com” along with details that confirm your relationship with the bank, you have a higher degree of confidence about its authenticity. On the other hand, emails from domains that have extra words or characters not typically associated with your bank should be treated with suspicion. Many organizations provide guidelines on their official websites regarding how they will communicate with you, so make it a habit to review those recommendations.</p>

  <p>In addition to these proactive measures, always consider what to do with suspicious emails. If you believe an email might be a phishing attempt, here are some steps you can follow:</p>

  <ul>
    <li><strong>Do Not Click Links or Download Attachments:</strong> Instead of interacting with the email, use the official website or contact the company directly using a known telephone number or email address to confirm its validity.</li>
    <li><strong>Report the Suspicious Email:</strong> Most organizations have a dedicated email address or reporting mechanism for phishing attempts. By forwarding suspicious emails to your IT department or the organization’s fraud team, you help them take further action and safeguard other users.</li>
    <li><strong>Delete the Email:</strong> After reporting, remove the email from your inbox to prevent accidental interaction in the future.</li>
  </ul>

  <p>It is also important to stay informed about the latest phishing techniques and scams. Cybersecurity threats are constantly evolving, and so should your vigilance. Consider subscribing to security newsletters or regularly checking reputable cybersecurity websites for updates. This continuous learning will further empower you to discern genuine communications from fraudulent ones.</p>

  <p>Moreover, be skeptical of emails that do not address you by your correct name. Genuine institutions tend to personalize their communications. For instance, if your bank typically addresses you as “{USER_NAME}” or uses details from your account, and you instead receive a generic salutation like “Dear Valued Customer,” that should prompt you to take extra precautions before proceeding.</p>

  <p>A significant part of defending against phishing is understanding the psychological tactics at work. Phishers count on emotional manipulation—fear, urgency, and even a false sense of security—to manipulate you into making a mistake. Recognize these tactics for what they are, and remind yourself that no matter how convincing an email may appear, taking a moment to verify its legitimacy is always the smarter choice.</p>

  <p>Another crucial tip is to familiarize yourself with your organization’s or service provider’s usual communication practices. Knowing what to expect can help you quickly spot anomalies. For example, if your bank never asks you to confirm credentials via email or directs you to log in through a secure portal, an email requesting immediate verification should be treated with heightened suspicion. Rely on these known patterns to judge the authenticity of any message that lands in your inbox.</p>

  <p>As a final proactive measure, consider using password management tools that help generate strong, unique passwords for each of your accounts. This way, even if a phishing attack is successful in harvesting your credentials, the potential damage is limited to that particular account. Additionally, always remember to log out after using shared or public devices, and never store sensitive information on these platforms.</p>

  <p>In summary, by combining careful inspection of email headers and domains, a skeptical view of urgent calls to action, and vigilant personal habits, you bolster your defenses against phishing. These strategies provide a comprehensive approach that helps you verify the integrity of communications and protect your personal data from malicious actors. Each step you integrate into your daily routine—whether it’s verifying sender details, checking URLs, or reporting suspicious messages—plays a crucial role in creating a safer digital environment.</p>

  <p>Keep these strategies in mind, {USER_NAME}, and remember that the overall goal is to empower you with the ability to recognize and respond to potential phishing threats confidently. Stay alert, ask questions when something doesn’t feel right, and don\'t hesitate to engage your IT or security advisors for further clarification. With these measures in place, you are taking significant steps toward securing your digital life.</p>
</div>';
        } else {
            $defense_strategies = '<div>
  <p>{USER_NAME}, now that you understand how attackers lure you with deceptive emails, let\'s discuss some practical defense strategies you can implement immediately to reduce your risk of falling prey to phishing schemes. These strategies are designed to help you verify authenticity quickly and respond appropriately to suspicious emails.</p>

  <p><strong>1. Scrutinize the Sender’s Details:</strong> Always verify the sender\'s email address closely. Genuine messages from reputable companies typically use official domains (for example, "@company.com"). Pay attention to subtle misspellings or extra characters in the domain name that indicate the email may not be legitimate. If you’re ever in doubt, manually type the known website address into your browser rather than clicking a link.</p>

  <p><strong>2. Hover Over Links:</strong> Before you click on any link, hover your mouse pointer over it to preview the actual URL. This simple step helps you determine if the URL matches the name of the company it purports to represent. Be wary of URLs that are misspelled or include long strings of random characters. Remember, hackers often use look-alike web addresses to mimic authentic sites.</p>

  <p><strong>3. Check for Urgency and Emotional Manipulation:</strong> Phishing emails often pressure you into hurried decisions with urgent language, threatening account suspension or other negative consequences. Take a moment to read through the message critically. If an email seems designed to trigger a strong emotional response, such as fear, excitement, or even curiosity, it could be a red flag. Always pause and assess the situation before taking action.</p>

  <p><strong>4. Look for Poor Grammar and Unexpected Requests:</strong> Many phishing emails contain grammatical errors or awkward phrasing. While not every error indicates a scam, consistent mistakes can be a telltale sign of a phishing attempt. Additionally, think twice if an email is asking for confidential information, such as passwords or credit card details—reputable institutions will generally not make such requests via email.</p>

  <p><strong>5. Use Established Communication Channels:</strong> When in doubt, directly contact the company using a phone number or email address you already trust, rather than using any contact information provided in the email. This extra step confirms whether the alert is genuine or a phishing trick. Many companies also provide guidance on how to report suspicious emails on their official websites.</p>

  <p><strong>6. Keep Your Systems Updated:</strong> Ensure that your software, browsers, and security tools are always up-to-date. Cyber attackers often exploit vulnerabilities in outdated systems. Using updated antivirus software and browser security features can add an extra layer of defense against malicious sites and phishing attacks.</p>

  <p>By following these actionable strategies, you can significantly reduce your risk of phishing attacks. Always remember to double-check critical details and trust your instincts when something feels off. These simple practices empower you to be proactive and maintain control over your personal data and online security.</p>
</div>';
        }
        $defense_strategies = str_replace("{USER_NAME}", $user_name, $defense_strategies);
        $this->defense_strategies = $defense_strategies;

        if ($length == "long") {
            $exercises = '<div>
  <p>{USER_NAME}, it’s time to put your new skills to the test with some interactive exercises. Below you will find four email simulations. For each email, review the sender details, subject line, content, and particularly the URL links. Then use the interactive classification task to decide if the email is “Phishing” or “Legitimate”. Remember, hover over any link to preview its URL before making your decision.</p>

  <!-- Email 1: Work Colleague (Phishing) -->
  <div style="border:1px solid #ccc; padding:10px; margin-bottom:20px; background-color:#f9f9f9;">
    <p><strong>From:</strong> <span style="color: blue; text-decoration: underline;">john.doe@acme-corp.com</span></p>
    <p><strong>Subject:</strong> Urgent: Problem with the Shared Drive Access</p>
    <p>Hi {USER_NAME},</p>
    <p>I’m having trouble accessing our shared drive and need you to immediately update your access credentials to help troubleshoot the issue. Please click the link below to verify your information:</p>
    <p><a href="http://acme-drive-update.com" onclick="event.preventDefault();" style="color: blue; text-decoration: underline;">Verify Your Access</a></p>
    <p>Thanks,<br>John</p>
  </div>
  <form id="form1">
    <p>Is the above email Phishing or Legitimate?</p>
    <label><input type="radio" name="email1" value="Phishing"> Phishing</label><br>
    <label><input type="radio" name="email1" value="Legitimate"> Legitimate</label><br><br>
    <button type="submit" style="cursor:pointer;">Submit Your Decision</button>
  </form>
  <div id="feedback1" style="margin:10px 0; padding:10px; border:1px solid #ccc; display:none;"></div>

  <!-- Email 2: PayPal (Phishing) -->
  <div style="border:1px solid #ccc; padding:10px; margin-bottom:20px; background-color:#f9f9f9;">
    <p><strong>From:</strong> <span style="color: blue; text-decoration: underline;">service@paypal-security.com</span></p>
    <p><strong>Subject:</strong> Important: Confirm Your PayPal Account Information</p>
    <p>Dear {USER_NAME},</p>
    <p>We recently noticed unusual activity on your PayPal account. To protect your account, we have temporarily limited its functionality. To restore full access, please confirm your account details by clicking the link below:</p>
    <p><a href="http://paypal.verify-info.com" onclick="event.preventDefault();" style="color: blue; text-decoration: underline;">Confirm Your Account</a></p>
    <p>Failure to update your information within 24 hours may result in permanent suspension of your account.</p>
    <p>Sincerely,<br>The PayPal Security Team</p>
  </div>
  <form id="form2">
    <p>Is the above email Phishing or Legitimate?</p>
    <label><input type="radio" name="email2" value="Phishing"> Phishing</label><br>
    <label><input type="radio" name="email2" value="Legitimate"> Legitimate</label><br><br>
    <button type="submit" style="cursor:pointer;">Submit Your Decision</button>
  </form>
  <div id="feedback2" style="margin:10px 0; padding:10px; border:1px solid #ccc; display:none;"></div>

  <!-- Email 3: Netflix (Phishing) -->
  <div style="border:1px solid #ccc; padding:10px; margin-bottom:20px; background-color:#f9f9f9;">
    <p><strong>From:</strong> <span style="color: blue; text-decoration: underline;">noreply@netfl1x.com</span></p>
    <p><strong>Subject:</strong> Account Issue: Reactivate Your Netflix Membership</p>
    <p>Dear {USER_NAME},</p>
    <p>Your Netflix account has encountered a billing issue. To avoid service interruption, please update your billing details immediately by visiting the link below:</p>
    <p><a href="http://netflix-confirmation-update.com" onclick="event.preventDefault();" style="color: blue; text-decoration: underline;">Update Billing Information</a></p>
    <p>If you do not update your details within the next 12 hours, your account may be suspended.</p>
    <p>Thank you for your prompt attention,<br>Netflix Billing Support</p>
  </div>
  <form id="form3">
    <p>Is the above email Phishing or Legitimate?</p>
    <label><input type="radio" name="email3" value="Phishing"> Phishing</label><br>
    <label><input type="radio" name="email3" value="Legitimate"> Legitimate</label><br><br>
    <button type="submit" style="cursor:pointer;">Submit Your Decision</button>
  </form>
  <div id="feedback3" style="margin:10px 0; padding:10px; border:1px solid #ccc; display:none;"></div>

  <!-- Email 4: Genuine PayPal Email (Legitimate) -->
  <div style="border:1px solid #ccc; padding:10px; margin-bottom:20px; background-color:#f9f9f9;">
    <p><strong>From:</strong> <span style="color: blue; text-decoration: underline;">service@paypal.com</span></p>
    <p><strong>Subject:</strong> Important Notice: Your Recent Transaction</p>
    <p>Dear {USER_NAME},</p>
    <p>Thank you for your recent transaction with PayPal. We wanted to let you know that your payment of $59.99 was processed successfully. You can view the details of this transaction by visiting your account page through the secure link below:</p>
    <p><a href="https://www.paypal.com/myaccount/transactions" onclick="event.preventDefault();" style="color: blue; text-decoration: underline;">View Transaction Details</a></p>
    <p>If you did not authorize this payment or notice any discrepancies, please contact our support team immediately through your official PayPal account.</p>
    <p>Sincerely,<br>PayPal Customer Service</p>
  </div>
  <form id="form4">
    <p>Is the above email Phishing or Legitimate?</p>
    <label><input type="radio" name="email4" value="Phishing"> Phishing</label><br>
    <label><input type="radio" name="email4" value="Legitimate"> Legitimate</label><br><br>
    <button type="submit" style="cursor:pointer;">Submit Your Decision</button>
  </form>
  <div id="feedback4" style="margin:10px 0; padding:10px; border:1px solid #ccc; display:none;"></div>

  <script>
    // Function to process each form submission
    function processForm(formId, groupName, correctAnswer, feedbackId, explanation) {
      document.getElementById(formId).addEventListener("submit", function(e) {
        e.preventDefault();
        var feedbackDiv = document.getElementById(feedbackId);
        var userChoice = document.querySelector(\'input[name="\' + groupName + \'"]:checked\');
        if (!userChoice) {
          feedbackDiv.style.display = "block";
          feedbackDiv.innerHTML = "<p>Please select an option before submitting your decision, {USER_NAME}.</p>";
          return;
        }
        var answer = userChoice.value;
        if (answer === correctAnswer) {
          feedbackDiv.innerHTML = "<p><strong>Correct!</strong> " + explanation + "</p>";
        } else {
          feedbackDiv.innerHTML = "<p><strong>Incorrect.</strong> " + explanation + "</p>";
        }
        feedbackDiv.style.display = "block";
      });
    }

    // Email 1: Work Colleague Email (Phishing)
    processForm("form1", "email1", "Phishing", "feedback1", "The email appears suspicious because the URL does not match your company\'s standard domain, and the urgency in the request is unusual coming from a colleague.");

    // Email 2: PayPal Phishing Email (Phishing)
    processForm("form2", "email2", "Phishing", "feedback2", "Be cautious—the sender’s email uses an unofficial domain (paypal-security.com instead of paypal.com), and the link points to a non-secure site. These are strong indicators of a phishing attempt.");

    // Email 3: Netflix Phishing Email (Phishing)
    processForm("form3", "email3", "Phishing", "feedback3", "Notice that the sender’s email address is slightly off (netfl1x.com instead of netflix.com). Coupled with the suspicious URL, these clues strongly suggest the email is a phishing attempt.");

    // Email 4: Genuine PayPal Email (Legitimate)
    processForm("form4", "email4", "Legitimate", "feedback4", "This email is legitimate because the sender’s email and the URL match the official PayPal domains, and the content aligns with the usual communication style from PayPal.");
  </script>

  <p>As you complete these exercises, remember to review each opportunity in the emails that served as cues—from mismatched domains, deceptive URLs, to the tone and urgency of the messages. Your keen observation plays a critical role in preventing phishing attacks, and each insight reinforces your cybersecurity skills.</p>

  <p>Great job practicing, {USER_NAME}! Each exercise is a step towards becoming more confident in spotting phishing attempts before they cause harm.</p>
</div>';
        }
        else {
            $exercises = '<div>
  <p>{USER_NAME}, it\'s time to put your new knowledge to the test with some interactive exercises. Below, you will find three email examples. Two of these emails are crafted to simulate phishing attempts, while one is a genuine message from PayPal. Your task is to carefully review each email, assess the cues, and classify it as either “Phishing” or “Legitimate.” For each exercise, select your answer and submit your decision to receive immediate feedback.</p>

  <!-- Simulated Phishing Email 1: Work Colleague -->
  <div style="border:1px solid #ccc; padding:10px; margin-bottom:15px;">
    <p><strong>From:</strong> <span>michael.smith@realcompany.com</span></p>
    <p><strong>Subject:</strong> Urgent: Please Review the Updated Project Files</p>
    <p>Hi {USER_NAME},</p>
    <p>I just received an urgent update regarding our Q3 project, and I need you to review the attached document immediately. Click <a href="http://realcompany-update.org" onclick="event.preventDefault();" style="color:blue; text-decoration:underline;">here</a> for the file.</p>
    <p>Let me know once you\'ve checked it. Thanks!</p>
    <p>Best,<br>Michael</p>
  </div>
  <form id="exercise1">
    <p><strong>Classify Email 1:</strong></p>
    <input type="radio" id="e1_phishing" name="email1" value="phishing">
    <label for="e1_phishing">Phishing</label><br>
    <input type="radio" id="e1_legit" name="email1" value="legitimate">
    <label for="e1_legit">Legitimate</label><br><br>
    <button type="submit">Submit Decision for Email 1</button>
  </form>
  <div id="feedback1" style="margin-top:10px; font-weight:bold;"></div>

  <!-- Simulated Phishing Email 2: PayPal -->
  <div style="border:1px solid #ccc; padding:10px; margin-top:25px; margin-bottom:15px;">
    <p><strong>From:</strong> <span>support@paypal.com</span></p>
    <p><strong>Subject:</strong> Security Alert: Unusual Activity Detected</p>
    <p>Dear {USER_NAME},</p>
    <p>We have observed an unusual login attempt on your account. To ensure your security, please verify your account immediately by clicking <a href="http://www.paypal-secure.com" onclick="event.preventDefault();" style="color:blue; text-decoration:underline;">here</a>.</p>
    <p>If you did not attempt to log in, please secure your account as soon as possible.</p>
    <p>Thank you,<br>PayPal Security Team</p>
  </div>
  <form id="exercise2">
    <p><strong>Classify Email 2:</strong></p>
    <input type="radio" id="e2_phishing" name="email2" value="phishing">
    <label for="e2_phishing">Phishing</label><br>
    <input type="radio" id="e2_legit" name="email2" value="legitimate">
    <label for="e2_legit">Legitimate</label><br><br>
    <button type="submit">Submit Decision for Email 2</button>
  </form>
  <div id="feedback2" style="margin-top:10px; font-weight:bold;"></div>

  <!-- Genuine Email: PayPal -->
  <div style="border:1px solid #ccc; padding:10px; margin-top:25px; margin-bottom:15px;">
    <p><strong>From:</strong> <span>service@paypal.com</span></p>
    <p><strong>Subject:</strong> Your Monthly Account Statement is Ready</p>
    <p>Dear {USER_NAME},</p>
    <p>Your monthly account statement is now available. Please click <a href="https://www.paypal.com/myaccount/summary" onclick="event.preventDefault();" style="color:blue; text-decoration:underline;">here</a> to securely access your statement. For your security, always ensure that you are logged in via the official PayPal website.</p>
    <p>Thank you for choosing PayPal.</p>
    <p>Sincerely,<br>The PayPal Team</p>
  </div>
  <form id="exercise3">
    <p><strong>Classify Email 3:</strong></p>
    <input type="radio" id="e3_phishing" name="email3" value="phishing">
    <label for="e3_phishing">Phishing</label><br>
    <input type="radio" id="e3_legit" name="email3" value="legitimate">
    <label for="e3_legit">Legitimate</label><br><br>
    <button type="submit">Submit Decision for Email 3</button>
  </form>
  <div id="feedback3" style="margin-top:10px; font-weight:bold;"></div>

  <script>
    // Exercise 1: Work Colleague Email
    document.getElementById("exercise1").addEventListener("submit", function(event) {
      event.preventDefault();
      var decision = document.querySelector(\'input[name="email1"]:checked\');
      var feedbackDiv = document.getElementById("feedback1");
      if (!decision) {
        feedbackDiv.textContent = "Please select an option.";
        return;
      }
      if (decision.value === "phishing") {
        feedbackDiv.textContent = "Correct, {USER_NAME}! Though this email appears to be from your colleague Michael, the link directs to an unfamiliar domain. Always verify the URL before clicking.";
      } else {
        feedbackDiv.textContent = "That\'s not correct. Despite looking like it came from Michael, the suspicious link is a red flag. Double-check URLs even in emails from known contacts.";
      }
    });

    // Exercise 2: PayPal Phishing Email
    document.getElementById("exercise2").addEventListener("submit", function(event) {
      event.preventDefault();
      var decision = document.querySelector(\'input[name="email2"]:checked\');
      var feedbackDiv = document.getElementById("feedback2");
      if (!decision) {
        feedbackDiv.textContent = "Please select an option.";
        return;
      }
      if (decision.value === "phishing") {
        feedbackDiv.textContent = "Good job, {USER_NAME}! Although this email seems to be from PayPal, the URL is suspicious. A slight alteration in the domain (from paypal.com to paypal-secure.com) is typical of a phishing attempt.";
      } else {
        feedbackDiv.textContent = "That\'s not correct. Notice the subtle change in the URL? It\'s a common phishing tactic to use similar-looking domains to trick users.";
      }
    });

    // Exercise 3: Genuine PayPal Email
    document.getElementById("exercise3").addEventListener("submit", function(event) {
      event.preventDefault();
      var decision = document.querySelector(\'input[name="email3"]:checked\');
      var feedbackDiv = document.getElementById("feedback3");
      if (!decision) {
        feedbackDiv.textContent = "Please select an option.";
        return;
      }
      if (decision.value === "legitimate") {
        feedbackDiv.textContent = "Well done, {USER_NAME}! This email, with its clear and secure URL (https://www.paypal.com), follows all official standards and is genuine.";
      } else {
        feedbackDiv.textContent = "That\'s not correct. Even though phishing emails can mimic genuine messages, this email uses a secure and official link. Always check for the correct domain.";
      }
    });
  </script>
</div>';
        }
        $exercises = str_replace("{USER_NAME}", $user_name, $exercises);
        $this->exercises = $exercises;

        if ($length == "long") {
            $conclusions = "<div>
  <p>{USER_NAME}, as we wrap up this session, let’s quickly recap the critical importance of phishing awareness. Phishing remains one of the most dangerous cyber threats because attackers exploit our natural curiosity, urgency, and even trust. By understanding how these deceptive tactics work, you become better equipped to identify subtle warning signs that may otherwise lead to costly mistakes.</p>
  <p>Throughout this course, we explored key actions you can take to defend yourself. You learned to carefully inspect the sender’s address for signs of spoofing and to hover over links to verify their actual destinations. We emphasized the importance of recognizing deceptive URL structures—especially when urgent requests prompt you into making hasty decisions. You also practiced with simulated phishing emails, applying your skills to distinguish between genuine communications and fraudulent ones. These practical steps, from checking for personalized details to analyzing the tone and urgency of a message, are your best tools against cyber attackers.</p>
  <p>Remember, the cornerstone of cybersecurity is a healthy dose of skepticism. By consistently verifying senders, checking links, and questioning the legitimacy of urgent messages, you can stop phishing attempts before they compromise your information. These everyday actions are critical defenses in a digital world where threats are constantly evolving.</p>
  <p>Thank you for dedicating your time and attention to this training session. Your active participation and commitment to learning these practical strategies not only protect you, but also contribute to a safer digital community. Stay vigilant, keep practicing the steps we discussed, and always remember that your awareness is the first line of defense against phishing attacks.</p>
</div>";
        } else {
            $conclusions = "<div>
  <p>{USER_NAME}, as we wrap up this training session, let's quickly recap why phishing awareness is crucial. Phishing remains one of the most dangerous cyber threats because it exploits both technological loopholes and our natural psychological tendencies to trust familiar names and urgent communications. Recognizing the tricks attackers use, such as spoofed sender addresses and deceptive URLs, is essential to safeguarding your personal data.</p>
  <p>The practical actions we've discussed include carefully verifying sender details by checking the domain, hovering over links to preview the actual URL, and scrutinizing emails for signs of urgency and grammatical errors. By applying these strategies and using trusted channels to confirm the legitimacy of any unexpected requests, you are well-equipped to defend yourself against phishing attempts.</p>
  <p>Thank you, {USER_NAME}, for your attention and participation. Remember, staying informed and vigilant is your best defense in the digital world. We hope you find these insights valuable and continue to practice safe email habits. Stay secure!</p>
</div>";
        }
        $conclusions = str_replace("{USER_NAME}", $user_name, $conclusions);
        $this->conclusions = $conclusions;
        $this->save();
    }

}
