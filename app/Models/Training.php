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
        "completed",
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
  <h2>Introduction to the Phishing Problem</h2>
  <p>Hello {USER_NAME}, and welcome to our Anti‑Phishing Training Module! In today’s digital world, staying safe online is more important than ever. This training course is designed to help you recognize and avoid phishing attacks—one of the most common and dangerous threats on the internet.</p>

  <p>Phishing is a fraudulent attempt by cybercriminals to trick you into providing sensitive information such as passwords, banking details, and other personal data. Attackers craft emails, messages, or even web pages that appear to be legitimate, often mimicking trusted companies or individuals. Once you click on a malicious link or open an infected attachment, you may inadvertently give away crucial information that can compromise your online security.</p>

  <p>These cyber attackers often exploit human psychology to succeed in their schemes. They take advantage of emotions such as fear, urgency, or even curiosity. For example, you might receive an email that plays on your worry over a supposed security breach, prompting you to act quickly without thinking things through. Recognizing these psychological triggers is a key step in protecting yourself from phishing attacks.</p>

  <p>Throughout this training module, we will cover a range of topics to empower you with the knowledge and skills needed to safeguard your personal and professional data. First, we will explore a realistic phishing scenario that demonstrates how these attacks operate in the real world. Then, we’ll dive into effective defense strategies to counter phishing efforts. Following that, you’ll participate in interactive exercises designed to reinforce your learning. Finally, we’ll wrap up with a concise conclusion to summarize the essential takeaways.</p>

  <p>Let’s get started on your journey to becoming more secure online. Remember, your awareness and vigilance are your best defenses against phishing attacks!</p>
</div>";
        } else {
            $intro = "<div>
  <h2>Introduction to the Phishing Problem</h2>
  <p>Hello {USER_NAME}, and welcome to our anti‑phishing training course. Over the next few minutes, we will equip you with the knowledge you need to identify and avoid phishing attacks – a serious threat in today’s digital world.</p>
  <p>Phishing is a form of cyberattack where attackers send fraudulent messages designed to trick you into revealing sensitive information such as passwords or credit card numbers. These scams often exploit common human vulnerabilities like trust, urgency, or fear, making it easy for even the most cautious users to fall prey.</p>
  <p>In this module, we will explore real-life phishing scenarios and identify the key tactics used by attackers. You will learn effective defense strategies and participate in interactive exercises. Finally, we will bring all the insights together so you can confidently protect yourself online.</p>
</div>";
        }
        $intro = str_replace("{USER_NAME}", $user_name, $intro);
        $this->introduction = $intro;

        if ($length == "long") {
            $scenario = '<div>
  <h2>Realistic Phishing Scenario Presentation</h2>
  <p>Let\'s imagine this scenario: It’s a regular workday, you’re busy with your tasks when suddenly you receive an unexpected email. The subject line reads “Urgent: Update Your Account Information Immediately” in bold red letters. You feel a bit uneasy, but you’re curious because the email seems to come from a familiar name. The sender’s email address appears as <span style="color: blue; text-decoration: underline; cursor: pointer;" onclick="showDescription(\'desc1\')">support@amazonsupport.com</span>, which looks similar to an address you might expect from a reputable company. However, before you proceed, pause and analyze the situation.</p>

  <p>The body of the email warns you that due to unusual activity on your account, immediate verification is needed. Below the message, you see a button-styled link that says: <span style="color: blue; text-decoration: underline; cursor: pointer;" onclick="showDescription(\'desc2\')">Verify Your Account Now</span>. The link itself directs you to a web address that looks almost legitimate: <span style="color: blue; text-decoration: underline; cursor: pointer;" onclick="showDescription(\'desc3\')">http://amazonsupport.secure-login.com</span>. At first glance, every part of this email seems urgent and important.</p>

  <p>Let’s break down some of the interactive elements and techniques used in this email:</p>

  <ul>
    <li>
      <strong>Spoofed Sender Details:</strong> The email address of the sender, <span style="color: blue; text-decoration: underline; cursor: pointer;" onclick="showDescription(\'desc1\')">support@amazonsupport.com</span>, appears valid at first but is crafted to mimic a trusted source by slightly altering the domain name. Attackers often use this method to trick you into believing the email is from a legitimate institution.
    </li>
    <li>
      <strong>Deceptive URL:</strong> The link labeled “Verify Your Account Now” points to <span style="color: blue; text-decoration: underline; cursor: pointer;" onclick="showDescription(\'desc3\')">http://amazonsupport.secure-login.com</span>. Although it appears similar to an authentic website, a closer look reveals subtle differences. Such deceptive URLs are a hallmark of phishing emails designed to capture your sensitive information.
    </li>
    <li>
      <strong>Urgency and Fear Tactics:</strong> The email leverages urgency by stating that your account will be restricted without immediate action. This plays on natural psychological vulnerabilities like fear and anxiety, prompting quick, unthoughtful decisions.
    </li>
  </ul>

  <p>Now, let’s take a closer look at each suspicious element. Click on the highlighted parts of the email to see detailed explanations of the techniques employed:</p>

  <div id="desc1" style="display: none; margin: 10px; padding: 10px; border: 1px solid #ccc;">
    <strong>Spoofed Sender Details Explanation:</strong>
    <p>Attackers can manipulate the “From” field to show an email address resembling one from a trusted source. In this case, even though the sender seems to be associated with a familiar company, the slight discrepancy in the domain (amazonsupport.com instead of an official domain) is a sign of spoofing.</p>
  </div>

  <div id="desc2" style="display: none; margin: 10px; padding: 10px; border: 1px solid #ccc;">
    <strong>Deceptive Call-to-Action Button:</strong>
    <p>The button “Verify Your Account Now” is designed to simulate urgency. It uses a design similar to legitimate buttons on trusted websites, which makes it hard for users to doubt its authenticity. This is a common tactic used to encourage immediate action without proper verification.</p>
  </div>

  <div id="desc3" style="display: none; margin: 10px; padding: 10px; border: 1px solid #ccc;">
    <strong>Deceptive URL Explanation:</strong>
    <p>The link provided leads to a web address that mimics the official website but includes subtle alterations. Attackers use these minor differences—such as additional words or altered spellings—to dupe unwary users into believing they are visiting a secure site, while in reality, it redirects them to a malicious page.</p>
  </div>

  <p>At this point, you\'re probably wondering what the best course of action would be. Let’s put you in the decision-maker’s role:</p>

  <form id="decisionForm">
    <p><strong>Interactive Decision Point:</strong> What would you do if you received an email like this?</p>
    <input type="radio" id="option1" name="decision" value="click">
    <label for="option1">Click the link immediately to verify my account.</label><br>
    <input type="radio" id="option2" name="decision" value="report">
    <label for="option2">Do not click the link and report the email as suspicious.</label><br>
    <input type="radio" id="option3" name="decision" value="ignore">
    <label for="option3">Ignore the email and continue with my work.</label><br><br>
    <button type="button" onclick="evaluateDecision()">Submit Your Choice</button>
  </form>

  <div id="feedback" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc; display: none;"></div>

  <p>Reflection & Learning: Once you make your decision, take a moment to reflect on the potential outcomes. Cybercriminals exploit common psychological triggers such as urgency and fear—emotions that can cloud your judgment. By rehearsing a decision in a safe, simulated environment, you’re better prepared to recognize these tactics in real life. Being cautious and verifying the legitimacy of unexpected emails—especially those that create a sense of urgency—can save you from unintended breaches of personal or professional data.</p>

  <p>This realistic scenario demonstrates the clever ways in which phishing emails are designed to deceive even the most vigilant users. As you progress through this training module, keep in mind the importance of scrutinizing every unexpected email, questioning its authenticity, and taking a moment to think before clicking.</p>

  <p>Remember, every interactive element in this simulation is a learning opportunity. By clicking on the suspicious parts and reading the descriptions, you are equipping yourself with the knowledge to identify these threats in your daily life. Your careful and informed decisions are the key to thwarting phishing attacks and maintaining a secure digital presence.</p>

  <p>Now, let’s see how your decision-making skills measure up. Reflect on the simulated phishing email you just experienced, and when you’re ready, select the option that best represents a safe response. Your immediate feedback will help reinforce the practices you need to adopt to protect yourself when real threats occur.</p>

  <script>
    function showDescription(id) {
      var element = document.getElementById(id);
      if (element.style.display === "none") {
        element.style.display = "block";
      } else {
        element.style.display = "none";
      }
    }

    function evaluateDecision() {
      var feedbackDiv = document.getElementById("feedback");
      var decision = document.querySelector(\'input[name="decision"]:checked\');
      if (!decision) {
        feedbackDiv.style.display = "block";
        feedbackDiv.style.backgroundColor = "#f8d7da";
        feedbackDiv.innerHTML = "Please select an option before submitting your choice.";
        return;
      }
      if (decision.value === "report") {
        feedbackDiv.style.display = "block";
        feedbackDiv.style.backgroundColor = "#d4edda";
        feedbackDiv.innerHTML = "Correct, {USER_NAME}! Reporting the email as suspicious and not clicking any links is the most secure choice. This action prevents potential exposure to malicious websites and helps protect your sensitive information.";
      } else {
        feedbackDiv.style.display = "block";
        feedbackDiv.style.backgroundColor = "#f8d7da";
        feedbackDiv.innerHTML = "That may not be the best decision, {USER_NAME}. Clicking on the link or ignoring the warning can lead to a security breach. Remember, phishing emails are designed to create a sense of urgency and provoke emotional responses that bypass rational decision making. The safest choice is to avoid interacting with such emails and report them.";
      }
    }
  </script>
</div>';
        } else {
            $scenario = '<div>
  <h2>Realistic Phishing Scenario Presentation</h2>
  <p>{USER_NAME}, imagine starting your day as usual only to receive an unexpected email. It appears to be from PayPal, warning you of suspicious activity on your account and urging you to verify your details immediately. The urgency of the message creates a sense of alarm, prompting a rapid reaction without time to think it through. This is a typical scenario used by attackers to exploit psychological vulnerabilities such as trust and fear.</p>

  <div style="border:1px solid #ccc; padding: 10px; background-color:#f9f9f9;">
    <p><strong>From:</strong> <span style="color:blue; cursor:pointer;" onclick="alert(\'Spoofed Sender: Although the email appears to be from an official source like PayPal, the sender address has been manipulated to build false trust.\')">security@paypal.com</span></p>
    <p><strong>Subject:</strong> Urgent: Verify Your Account Now!</p>
    <p>Dear {USER_NAME},</p>
    <p>We have detected unusual activity on your account. To protect your information, please <span style="color:blue; text-decoration:underline; cursor:pointer;" onclick="alert(\'Deceptive Link: This link seems to direct you to PayPal, but it actually sends you to a malicious site designed to capture your personal data.\')">click here</span> to verify your account immediately. Failure to verify within the next 24 hours may result in temporary suspension.</p>
    <p>Thank you,<br/>PayPal Security Team</p>
  </div>

  <p>Notice how the email leverages urgency and uses a familiar brand to trick you. Such techniques exploit human vulnerabilities like the need for quick resolution and compliance with authority.</p>

  <form id="decisionForm">
    <p><strong>Interactive Decision Point:</strong> What would you do in response to this email?</p>
    <label>
      <input type="radio" name="decision" value="click"> Click the link immediately to secure the account.
    </label><br/>
    <label>
      <input type="radio" name="decision" value="verify"> Reach out to the company directly using contact details from their official website.
    </label><br/>
    <label>
      <input type="radio" name="decision" value="ignore"> Simply ignore the email assuming it’s spam.
    </label><br/>
    <button type="button" onclick="checkDecision();">Submit Your Decision</button>
  </form>

  <p id="feedback" style="font-weight:bold;"></p>

  <script>
    function checkDecision(){
      var decisions = document.getElementsByName(\'decision\');
      var selected = false;
      var value = \'\';
      for(var i = 0; i < decisions.length; i++){
        if(decisions[i].checked){
          selected = true;
          value = decisions[i].value;
          break;
        }
      }
      var feedbackEl = document.getElementById(\'feedback\');
      if(!selected){
        feedbackEl.innerText = \'Please select an option before submitting.\';
        return;
      }
      if(value === \'click\'){
        feedbackEl.innerText = \'Choosing to click the link immediately can be dangerous. This option may expose you to malware or data theft. Always verify the source first.\';
      } else if(value === \'verify\'){
        feedbackEl.innerText = \'Correct! Contacting the company through verified channels ensures that you receive accurate information and avoid potential scams.\';
      } else if(value === \'ignore\'){
        feedbackEl.innerText = \'Ignoring the email completely might not be ideal either, as you could miss important notifications. It’s best to confirm the legitimacy through official means.\';
      }
    }
  </script>

  <p>This scenario is designed to help you recognize common phishing tactics. By interacting with the email’s suspicious elements, you gain insight into how attackers lure you into a trap. Let\'s continue to learn how to safeguard yourself against these deceptive techniques.</p>
</div>';
        }
        $scenario = str_replace("{USER_NAME}", $user_name, $scenario);
        $this->scenario = $scenario;

        if ($length == "long") {
            $defense_strategies = '<div>
  <h2>Defense Strategies</h2>
  <p>{USER_NAME}, in this section we’ll discuss practical strategies that you can use to defend yourself against phishing attacks. Cybercriminals use a variety of tactics to lure you into clicking malicious links, providing sensitive information, or even compromising your device. The key to safe computing lies in knowing what to look for in suspicious emails and understanding how to confirm an email’s legitimacy. Here are several actionable steps and strategies to help you stay secure:</p>

  <h3>1. Evaluate the Sender’s Domain Carefully</h3>
  <p>One of the easiest ways to recognize a phishing email is by checking the sender\'s domain. Cybercriminals often create email addresses that look nearly identical to those of legitimate organizations but include slight deviations. For example, an email that appears to be from “noreply@paypal.com” might be mimicked as “help@paypai.com.” To verify:</p>
  <ul>
    <li>Examine the email address closely by hovering your mouse over the sender’s name if your email client displays additional details.</li>
    <li>Do a quick search online or check previous communications from the organization to confirm the correct domain.</li>
    <li>If you’re unsure, contact the company directly using their official contact information.</li>
  </ul>
  <p>This simple check can often help you spot discrepancies that indicate a phishing attempt.</p>

  <h3>2. Inspect URLs Before Clicking</h3>
  <p>Phishing emails frequently include links that appear authentic but lead to fraudulent websites. Instead of clicking directly:</p>
  <ul>
    <li>Hover over any link to see the actual URL in the status bar of your browser. Make sure it exactly matches the expected domain.</li>
    <li>Look for subtle misspellings or extra words inserted into the domain name. For instance, while a legitimate URL might be “https://www.bankofexample.com,” a phishing attempt might use “https://www.bankofexamples.com.”</li>
    <li>Be cautious if the URL contains unusual characters or if it uses a non-secure “http” protocol instead of “https.”</li>
  </ul>
  <p>By taking a moment to check URLs, you can avoid inadvertently visiting malicious websites that capture your personal data.</p>

  <h3>3. Look Out for Urgency and Pressure Tactics</h3>
  <p>Phishing emails often create a sense of urgency, pressuring you to act without thinking. They might claim that there’s a problem with your account or that immediate action is required to secure your information. To defend against this:</p>
  <ul>
    <li>Take a deep breath and read the entire email, looking beyond the headline. Phishers rely on emotional reactions, such as fear or excitement, to bypass your rational thinking.</li>
    <li>Question any email that insists on immediate action, especially if the consequences of inaction seem disproportionately severe.</li>
    <li>Remember that legitimate companies rarely demand sensitive actions in a rush.</li>
  </ul>
  <p>Allow yourself a moment to verify the information through independent channels rather than reacting immediately to inherent pressure.</p>

  <h3>4. Analyze the Email Content for Inconsistencies</h3>
  <p>Pay attention to the language and presentation of the email. Phishing emails often contain subtle mistakes or inconsistencies that can tip you off. Here are some clues:</p>
  <ul>
    <li>Look for spelling mistakes, grammatical errors, or awkward phrasing. Professional companies usually have rigorous proofreading processes.</li>
    <li>Examine the email’s formatting. Emails that are cluttered with excessive images or inconsistent fonts may suggest that they haven’t been produced by a reputable company.</li>
    <li>Evaluate the overall tone of the email. Overly aggressive or emotional language may signal a phishing attempt.</li>
  </ul>
  <p>A careful review of the email’s content can reveal hidden red flags that compromise its authenticity.</p>

  <h3>5. Verify the Email’s Authenticity Using Known Channels</h3>
  <p>When in doubt, use an independent method to verify an email’s claims. This might involve:</p>
  <ul>
    <li>Contacting the company directly via its official website or by telephone. Do not use the contact information provided within the suspicious email.</li>
    <li>Logging into your account through a direct URL that you know to be correct, rather than clicking on links from an email.</li>
    <li>Checking your account notifications on the official website to see if there are any messages corresponding to the content of the email.</li>
  </ul>
  <p>By validating communications through trusted sources, you minimize the risk of falling victim to a well-crafted phishing scam.</p>

  <h3>6. Keep Your Software and Security Systems Updated</h3>
  <p>Regular software updates aren’t just about adding new features—they play a vital role in defense against phishing and other cyber threats. Here’s how you can stay up-to-date:</p>
  <ul>
    <li>Always install updates for your operating system, email client, web browser, and any security software.</li>
    <li>Configure your software to download and install updates automatically, ensuring you have the latest protection against emerging threats.</li>
    <li>Regular updates close vulnerabilities that attackers might exploit through phishing or other malware delivery methods.</li>
  </ul>
  <p>Keeping your systems current is an essential line of defense that complements careful scrutiny of suspicious emails.</p>

  <h3>7. Use Multi-Factor Authentication (MFA)</h3>
  <p>Even if a phisher succeeds in capturing your password, multi-factor authentication offers another layer of security. MFA requires a second form of verification—such as a code sent to your mobile device—that significantly reduces the risk of unauthorized access:</p>
  <ul>
    <li>Enable MFA on your most critical accounts, including email, banking, and social media.</li>
    <li>Check that your service providers offer MFA and configure it as early as possible.</li>
    <li>Understand that MFA is not infallible, but when combined with other best practices, it greatly increases your security posture.</li>
  </ul>

  <h3>8. Educate Yourself Continuously</h3>
  <p>The landscape of cyber threats is constantly evolving, and attackers are always devising new phishing techniques. Commitment to continuous learning is a vital component of your defense strategy:</p>
  <ul>
    <li>Participate in training sessions like this one to keep abreast of the latest phishing tactics and defense methods.</li>
    <li>Follow trusted cybersecurity blogs, news outlets, or newsletters that highlight current threats and new security measures.</li>
    <li>Create a habit of reviewing real-world examples of phishing emails to learn how to spot even the most subtle red flags.</li>
  </ul>
  <p>By keeping yourself informed, you not only improve your own safety but also contribute to a smarter, more secure community.</p>

  <h3>9. Establish a Habit of Cautious Interaction</h3>
  <p>Finally, cultivate an overall mindset of critical thinking and cautious interaction whenever you engage with digital communications:</p>
  <ul>
    <li>Whenever you receive unexpected emails asking for sensitive information, pause and scrutinize every detail before taking any action.</li>
    <li>Adopt a “trust but verify” approach, where you remain open to communication but always confirm the legitimacy from a trusted source.</li>
    <li>Remember that your first instinct should always be to analyze the email’s content and context rather than act on impulse.</li>
  </ul>
  <p>This habit will help you stand as a formidable barrier against phishing attempts and reduce the risk of accidentally compromising your sensitive data.</p>

  <h3>Recognizing a Genuine Email</h3>
  <p>Understanding how to differentiate a genuine email from a phishing attempt is crucial. Here are some specific cues to help you identify an authentic communication:</p>
  <ul>
    <li><strong>Domain Consistency:</strong> Genuine emails come from verified domains. Always check the sender’s email address and ensure it matches historical communications from that organization.</li>
    <li><strong>Personalized Greetings:</strong> Legitimate emails often address you by the name you provided to the company. A generic greeting like “Dear Customer” might be a hint that the email is not genuine.</li>
    <li><strong>Secure Links:</strong> Before clicking, hover over any link to verify that the URL corresponds exactly with the official website. Look for the “https://” indicator and trusted certificates.</li>
    <li><strong>Content Quality:</strong> Look for proper grammar, spelling, and professional layout. An email littered with errors is likely a scam.</li>
    <li><strong>Contextual Relevance:</strong> Assess whether the email message makes sense in the context of your ongoing interactions with the company. If the content appears unrelated or unsolicited, take caution.</li>
  </ul>
  <p>By integrating these practices into your daily routine, you can significantly mitigate the risks associated with phishing. Each strategy and tip provided here tools you with the knowledge to remain skeptical and verify any suspicious digital communication.</p>

  <p>Implement these defense strategies consistently, {USER_NAME}. Your vigilance, combined with these actionable tips, creates a strong defense against phishing attacks. Remember that security is a process—one that requires continual awareness, critical thinking, and practiced habits. With these strategies at hand, you are well-equipped to spot phishing attempts, verify the legitimacy of emails, and protect your sensitive data in an increasingly complex digital landscape.</p>

  <p>Stay safe and remember: in the realm of cybersecurity, caution is your most powerful ally.</p>
</div>';
        } else {
            $defense_strategies = '<div>
  <h2>Defense Strategies</h2>
  <p>Hello {USER_NAME}, now that you’ve seen how phishing attacks operate, let’s focus on practical defense strategies that will help you safeguard your information online. Phishers rely on urgency, familiarity with brands, and subtle manipulation of social cues to deceive you. Recognizing these tactics and knowing how to respond appropriately can make all the difference.</p>

  <p>Here are several actionable strategies you can use to defend against common phishing techniques:</p>

  <ul>
    <li>
      <strong>Double-check the Sender’s Domain:</strong> Always verify the sender’s email address closely. Even if the sender’s name seems familiar or trustworthy, phishers often use slight misspellings or misleading characters to mimic official domains. For example, an email from "support@paypa1.com" (with a numeral “1” instead of an “l”) is a red flag. Take a moment to hover over the email address to confirm its authenticity.
    </li>
    <li>
      <strong>Inspect Links by Hovering:</strong> Before clicking on any link in an email, hover your cursor over the link. This reveals the actual URL, which might differ from what is shown. If the URL looks odd or doesn’t match the domain of the purported sender (for example, a PayPal email linking to an unrelated domain), do not click it.
    </li>
    <li>
      <strong>Verify with the Official Source:</strong> If an email urges you to take immediate action, such as updating your payment information or resetting your password, always log in to your account directly from the official website or use a trusted app. Do not use the contact information or links provided in the email.
    </li>
    <li>
      <strong>Look for Poor Grammar or Unusual Formatting:</strong> Many phishing emails contain spelling mistakes, awkward phrases, or inconsistent formatting. While not every mistake indicates a scam, multiple errors or phrases that seem off should prompt caution.
    </li>
    <li>
      <strong>Be Wary of Urgent Requests and Threats:</strong> Phishers create a sense of urgency by threatening account suspension or immediate loss of service. Take a deep breath and analyze the email logically rather than reacting impulsively. A genuine organization will never pressure you to make snap decisions.
    </li>
    <li>
      <strong>Enable Multi-Factor Authentication (MFA):</strong> Even if someone gains access to your password, MFA provides an additional layer of security by requiring a second verification step. This extra hurdle can prevent unauthorized access to your personal information.
    </li>
    <li>
      <strong>Keep Your Software Updated:</strong> Regularly update your operating system, browser, and antivirus software, as these updates often include security patches that protect against new forms of phishing and malware.
    </li>
  </ul>

  <p>By incorporating these defense strategies into your daily digital practices, you can more effectively identify and defuse phishing attempts before any harm is done. Always remember: genuine emails can be recognized by their consistent sender domains, verified links, professional language, and the absence of undue urgency. Taking these steps will help you navigate your online interactions more safely and confidently.</p>

  <p>Let\'s continue to build on this knowledge as we learn even more practical ways to secure your digital world.</p>
</div>';
        }
        $defense_strategies = str_replace("{USER_NAME}", $user_name, $defense_strategies);
        $this->defense_strategies = $defense_strategies;

        if ($length == "long") {
            $exercises = '<div>
  <h2>Defense Strategies</h2>
  <p>In this section we’ll discuss practical strategies that you can use to defend yourself against phishing attacks. Cybercriminals use a variety of tactics to lure you into clicking malicious links, providing sensitive information, or even compromising your device. The key to safe computing lies in knowing what to look for in suspicious emails and understanding how to confirm an email’s legitimacy. Here are several actionable steps and strategies to help you stay secure:</p>

  <h3>1. Evaluate the Sender’s Domain Carefully</h3>
  <p>One of the easiest ways to recognize a phishing email is by checking the sender\'s domain. Cybercriminals often create email addresses that look nearly identical to those of legitimate organizations but include slight deviations. For example, an email that appears to be from “noreply@paypal.com” might be mimicked as “help@paypai.com.” To verify:</p>
  <ul>
    <li>Examine the email address closely by hovering your mouse over the sender’s name if your email client displays additional details.</li>
    <li>Do a quick search online or check previous communications from the organization to confirm the correct domain.</li>
    <li>If you’re unsure, contact the company directly using their official contact information.</li>
  </ul>
  <p>This simple check can often help you spot discrepancies that indicate a phishing attempt.</p>

  <h3>2. Inspect URLs Before Clicking</h3>
  <p>Phishing emails frequently include links that appear authentic but lead to fraudulent websites. Instead of clicking directly:</p>
  <ul>
    <li>Hover over any link to see the actual URL in the status bar of your browser. Make sure it exactly matches the expected domain.</li>
    <li>Look for subtle misspellings or extra words inserted into the domain name. For instance, while a legitimate URL might be “https://www.bankofexample.com,” a phishing attempt might use “https://www.bankofexamples.com.”</li>
    <li>Be cautious if the URL contains unusual characters or if it uses a non-secure “http” protocol instead of “https.”</li>
  </ul>
  <p>By taking a moment to check URLs, you can avoid inadvertently visiting malicious websites that capture your personal data.</p>

  <h3>3. Look Out for Urgency and Pressure Tactics</h3>
  <p>Phishing emails often create a sense of urgency, pressuring you to act without thinking. They might claim that there’s a problem with your account or that immediate action is required to secure your information. To defend against this:</p>
  <ul>
    <li>Take a deep breath and read the entire email, looking beyond the headline. Phishers rely on emotional reactions, such as fear or excitement, to bypass your rational thinking.</li>
    <li>Question any email that insists on immediate action, especially if the consequences of inaction seem disproportionately severe.</li>
    <li>Remember that legitimate companies rarely demand sensitive actions in a rush.</li>
  </ul>
  <p>Allow yourself a moment to verify the information through independent channels rather than reacting immediately to inherent pressure.</p>

  <h3>4. Analyze the Email Content for Inconsistencies</h3>
  <p>Pay attention to the language and presentation of the email. Phishing emails often contain subtle mistakes or inconsistencies that can tip you off. Here are some clues:</p>
  <ul>
    <li>Look for spelling mistakes, grammatical errors, or awkward phrasing. Professional companies usually have rigorous proofreading processes.</li>
    <li>Examine the email’s formatting. Emails that are cluttered with excessive images or inconsistent fonts may suggest that they haven’t been produced by a reputable company.</li>
    <li>Evaluate the overall tone of the email. Overly aggressive or emotional language may signal a phishing attempt.</li>
  </ul>
  <p>A careful review of the email’s content can reveal hidden red flags that compromise its authenticity.</p>

  <h3>5. Verify the Email’s Authenticity Using Known Channels</h3>
  <p>When in doubt, use an independent method to verify an email’s claims. This might involve:</p>
  <ul>
    <li>Contacting the company directly via its official website or by telephone. Do not use the contact information provided within the suspicious email.</li>
    <li>Logging into your account through a direct URL that you know to be correct, rather than clicking on links from an email.</li>
    <li>Checking your account notifications on the official website to see if there are any messages corresponding to the content of the email.</li>
  </ul>
  <p>By validating communications through trusted sources, you minimize the risk of falling victim to a well-crafted phishing scam.</p>

  <h3>6. Keep Your Software and Security Systems Updated</h3>
  <p>Regular software updates aren’t just about adding new features—they play a vital role in defense against phishing and other cyber threats. Here’s how you can stay up-to-date:</p>
  <ul>
    <li>Always install updates for your operating system, email client, web browser, and any security software.</li>
    <li>Configure your software to download and install updates automatically, ensuring you have the latest protection against emerging threats.</li>
    <li>Regular updates close vulnerabilities that attackers might exploit through phishing or other malware delivery methods.</li>
  </ul>
  <p>Keeping your systems current is an essential line of defense that complements careful scrutiny of suspicious emails.</p>

  <h3>7. Use Multi-Factor Authentication (MFA)</h3>
  <p>Even if a phisher succeeds in capturing your password, multi-factor authentication offers another layer of security. MFA requires a second form of verification—such as a code sent to your mobile device—that significantly reduces the risk of unauthorized access:</p>
  <ul>
    <li>Enable MFA on your most critical accounts, including email, banking, and social media.</li>
    <li>Check that your service providers offer MFA and configure it as early as possible.</li>
    <li>Understand that MFA is not infallible, but when combined with other best practices, it greatly increases your security posture.</li>
  </ul>

  <h3>8. Educate Yourself Continuously</h3>
  <p>The landscape of cyber threats is constantly evolving, and attackers are always devising new phishing techniques. Commitment to continuous learning is a vital component of your defense strategy:</p>
  <ul>
    <li>Participate in training sessions like this one to keep abreast of the latest phishing tactics and defense methods.</li>
    <li>Follow trusted cybersecurity blogs, news outlets, or newsletters that highlight current threats and new security measures.</li>
    <li>Create a habit of reviewing real-world examples of phishing emails to learn how to spot even the most subtle red flags.</li>
  </ul>
  <p>By keeping yourself informed, you not only improve your own safety but also contribute to a smarter, more secure community.</p>

  <h3>9. Establish a Habit of Cautious Interaction</h3>
  <p>Finally, cultivate an overall mindset of critical thinking and cautious interaction whenever you engage with digital communications:</p>
  <ul>
    <li>Whenever you receive unexpected emails asking for sensitive information, pause and scrutinize every detail before taking any action.</li>
    <li>Adopt a “trust but verify” approach, where you remain open to communication but always confirm the legitimacy from a trusted source.</li>
    <li>Remember that your first instinct should always be to analyze the email’s content and context rather than act on impulse.</li>
  </ul>
  <p>This habit will help you stand as a formidable barrier against phishing attempts and reduce the risk of accidentally compromising your sensitive data.</p>

  <h3>Recognizing a Genuine Email</h3>
  <p>Understanding how to differentiate a genuine email from a phishing attempt is crucial. Here are some specific cues to help you identify an authentic communication:</p>
  <ul>
    <li><strong>Domain Consistency:</strong> Genuine emails come from verified domains. Always check the sender’s email address and ensure it matches historical communications from that organization.</li>
    <li><strong>Personalized Greetings:</strong> Legitimate emails often address you by the name you provided to the company. A generic greeting like “Dear Customer” might be a hint that the email is not genuine.</li>
    <li><strong>Secure Links:</strong> Before clicking, hover over any link to verify that the URL corresponds exactly with the official website. Look for the “https://” indicator and trusted certificates.</li>
    <li><strong>Content Quality:</strong> Look for proper grammar, spelling, and professional layout. An email littered with errors is likely a scam.</li>
    <li><strong>Contextual Relevance:</strong> Assess whether the email message makes sense in the context of your ongoing interactions with the company. If the content appears unrelated or unsolicited, take caution.</li>
  </ul>
  <p>By integrating these practices into your daily routine, you can significantly mitigate the risks associated with phishing. Each strategy and tip provided here tools you with the knowledge to remain skeptical and verify any suspicious digital communication.</p>

  <p>Implement these defense strategies consistently, {USER_NAME}. Your vigilance, combined with these actionable tips, creates a strong defense against phishing attacks. Remember that security is a process—one that requires continual awareness, critical thinking, and practiced habits. With these strategies at hand, you are well-equipped to spot phishing attempts, verify the legitimacy of emails, and protect your sensitive data in an increasingly complex digital landscape.</p>

  <p>Stay safe and remember: in the realm of cybersecurity, caution is your most powerful ally.</p>
</div>';
        }
        else {
            $exercises = '<div>
  <h2>Interactive Exercises</h2>

  <!-- Phishing Email 1: Work colleague -->
  <div style="border:1px solid #ccc; padding: 10px; margin-bottom:20px; background-color:#f9f9f9;">
    <p><strong>From:</strong> <span style="color:blue;">alice.johnson@techsolutions.com</span></p>
    <p><strong>Subject:</strong> Urgent: Updated Project Files</p>
    <p>Hi {USER_NAME},</p>
    <p>
      I just received an update regarding our ongoing project and need you to review the document immediately. Please click
      <span style="color:blue; text-decoration:underline; cursor:pointer;" onclick="alert(\'Deceptive URL: The link text may look genuine, but the underlying URL is manipulated to mislead users.\')">
        here
      </span> to access the updated files on our company’s portal.
    </p>
    <p>Thank you,<br/>Alice</p>

    <form id="email1Form">
      <p><strong>Classify this email:</strong></p>
      <label>
        <input type="radio" name="email1" value="Phishing"> Phishing
      </label>
      <label>
        <input type="radio" name="email1" value="Legitimate"> Legitimate
      </label>
      <br/><br/>
      <button type="button" onclick="checkEmail1();">Submit</button>
      <p id="feedback1" style="font-weight:bold;"></p>
    </form>
  </div>

  <!-- Phishing Email 2: Fake Booking.com -->
  <div style="border:1px solid #ccc; padding: 10px; margin-bottom:20px; background-color:#f9f9f9;">
    <p><strong>From:</strong> <span style="color:blue;">noreply@booking.com</span></p>
    <p><strong>Subject:</strong> Action Required: Verify Your Booking Information</p>
    <p>Dear {USER_NAME},</p>
    <p>
      We are having trouble confirming the details of your recent booking. To ensure your reservation is not canceled, please
      <span style="color:blue; text-decoration:underline; cursor:pointer;" onclick="alert(\'Deceptive URL: Although this email appears to come from Booking.com, the link directs you to a suspicious domain attempting to mimic the real site.\')">
        verify your booking details
      </span> immediately using our secure portal.
    </p>
    <p>Regards,<br/>Booking.com Customer Support</p>

    <form id="email2Form">
      <p><strong>Classify this email:</strong></p>
      <label>
        <input type="radio" name="email2" value="Phishing"> Phishing
      </label>
      <label>
        <input type="radio" name="email2" value="Legitimate"> Legitimate
      </label>
      <br/><br/>
      <button type="button" onclick="checkEmail2();">Submit</button>
      <p id="feedback2" style="font-weight:bold;"></p>
    </form>
  </div>

  <!-- Genuine Email: Booking.com -->
  <div style="border:1px solid #ccc; padding: 10px; margin-bottom:20px; background-color:#e8f5e9;">
    <p><strong>From:</strong> <span style="color:blue;">confirmations@booking.com</span></p>
    <p><strong>Subject:</strong> Your Booking.com Reservation Confirmation</p>
    <p>Dear {USER_NAME},</p>
    <p>
      Thank you for booking with us! Your reservation has been confirmed. You can view your booking details by accessing your account at
      <a href="https://www.booking.com/your-account" style="color:blue; text-decoration:underline;" onclick="alert(\'Genuine URL: This link takes you to the official Booking.com website.\')">Booking.com</a>.
    </p>
    <p>We look forward to hosting you soon.</p>
    <p>Best regards,<br/>Booking.com Team</p>

    <form id="email3Form">
      <p><strong>Classify this email:</strong></p>
      <label>
        <input type="radio" name="email3" value="Phishing"> Phishing
      </label>
      <label>
        <input type="radio" name="email3" value="Legitimate"> Legitimate
      </label>
      <br/><br/>
      <button type="button" onclick="checkEmail3();">Submit</button>
      <p id="feedback3" style="font-weight:bold;"></p>
    </form>
  </div>

  <script>
    function checkEmail1(){
      var radios = document.getElementsByName(\'email1\');
      var selected;
      for(var i=0; i<radios.length; i++){
        if(radios[i].checked){
          selected = radios[i].value;
          break;
        }
      }
      var feedback = document.getElementById(\'feedback1\');
      if(!selected){
        feedback.innerText = \'Please select an option before submitting.\';
        return;
      }
      // For Email 1, cues: the suspicious link (deceptive URL) and slight mismatch in sender signature.
      if(selected === \'Phishing\'){
        feedback.innerText = \'Correct! Notice the clickable link that reveals a deceptive URL. Always investigate unexpected links, even if the sender appears familiar.\';
      } else {
        feedback.innerText = \'Not quite. Although the sender seems to be a colleague, the link provided leads to a suspicious URL. Always verify external links, even in internal communications.\';
      }
    }

    function checkEmail2(){
      var radios = document.getElementsByName(\'email2\');
      var selected;
      for(var i=0; i<radios.length; i++){
        if(radios[i].checked){
          selected = radios[i].value;
          break;
        }
      }
      var feedback = document.getElementById(\'feedback2\');
      if(!selected){
        feedback.innerText = \'Please select an option before submitting.\';
        return;
      }
      // For Email 2, cues: deceptive link text and urgency in language.
      if(selected === \'Phishing\'){
        feedback.innerText = \'Correct! The email uses urgency and a deceptive link that, when inspected, does not direct to the official Booking.com domain.\';
      } else {
        feedback.innerText = \'That is incorrect. The urgent request and the link that hides its true destination are classic signs of a phishing attempt.\';
      }
    }

    function checkEmail3(){
      var radios = document.getElementsByName(\'email3\');
      var selected;
      for(var i=0; i<radios.length; i++){
        if(radios[i].checked){
          selected = radios[i].value;
          break;
        }
      }
      var feedback = document.getElementById(\'feedback3\');
      if(!selected){
        feedback.innerText = \'Please select an option before submitting.\';
        return;
      }
      // For Email 3, cues: verified sender address, professional language, and a genuine URL.
      if(selected === \'Legitimate\'){
        feedback.innerText = \'Correct! This email comes from an official Booking.com email address and includes a genuine link to their site. Always check for these verifiable details.\';
      } else {
        feedback.innerText = \'Not quite. The email is genuine, indicated by its verified sender address, proper formatting, and a secure URL linking to Booking.com.\';
      }
    }
  </script>

  <p>Each exercise is designed to test your ability to identify key indicators of phishing attacks versus genuine communications. Reflect on the cues you observed – subtle errors, urgent language, and deceptive URLs are common hallmarks of phishing attempts. Keep these strategies in mind as you continue to protect your digital identity.</p>
</div>';
        }
        $exercises = str_replace("{USER_NAME}", $user_name, $exercises);
        $this->exercises = $exercises;

        if ($length == "long") {
            $conclusions = '<div>
  <h2>Conclusions</h2>
  <p>As we wrap up this training module, let’s take a moment to recap the key points that will help you stay safe from phishing attacks. Phishing awareness is critical in today’s digital landscape because cybercriminals use increasingly sophisticated techniques to deceive even the most vigilant users. By understanding the nature of phishing attacks—from deceptive sender details and suspicious URLs to the psychological tactics of urgency and fear—you are better equipped to spot potential threats before they compromise your personal or professional information.</p>

  <p>Throughout this module, we’ve covered practical actions you can take to defend yourself. You learned to meticulously check the sender’s domain, inspect and verify URLs by hovering over links, and scrutinize the overall content for inconsistencies like poor grammar or urgent language designed to trigger a rushed reaction. These steps, coupled with the habit of verifying communications directly with the organization through secure channels, create a robust defense against phishing attempts. The interactive exercises further reinforced your ability to differentiate between phishing scams and legitimate emails, empowering you to recognize subtle cues of deception in real-life scenarios.</p>

  <p>Thank you for investing your time in this training session, {USER_NAME}. Remember, vigilance and careful verification are your best allies in combating phishing. Stay aware, keep practicing these defensive strategies, and continue to educate yourself about emerging cybersecurity threats. With these tools at your disposal, you can confidently navigate digital communications and protect your essential data. Stay safe and secure!</p>
</div>';
        } else {
            $conclusions = '<div>
  <h2>Conclusions</h2>
  <p>{USER_NAME}, as we wrap up this training session, let’s quickly recap what we’ve learned. Phishing is a dangerous tactic that exploits human vulnerabilities such as trust and urgency, making it crucial to stay alert and informed. Recognizing the signs of phishing – whether through deceptive sender addresses, misleading URLs, or urgent language demanding immediate action – is the first step in protecting yourself online.</p>
  <p>Remember the practical actions we discussed: always double-check the sender’s domain, hover over links to inspect their true destination, verify email content by contacting companies through official channels, and never let urgency rush your decisions. These simple yet effective strategies can help you differentiate between genuine communications and potential scams.</p>
  <p>Thank you for attending this anti-phishing training. Your awareness and proactive steps are essential in keeping you safe in the digital world. Stay vigilant and safe online!</p>
</div>';
        }
        $conclusions = str_replace("{USER_NAME}", $user_name, $conclusions);
        $this->conclusions = $conclusions;
        $this->save();
    }

}
