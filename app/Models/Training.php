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
        if ($length == "short") {
            $intro = "<div>
              <h2>Introduction to the Phishing Problem</h2>
              <p>Hello {USER_NAME}, and welcome to our anti‑phishing training module. Today, we start by exploring the problem of phishing—a type of cyber attack in which attackers use deceptive emails, messages, or websites to trick you into sharing personal or financial information. Phishing is dangerous because it preys on your trust and exploits everyday habits, making it easier for fraudulent schemes to take root in both your personal and professional digital life.</p>
              <p>Phishing attacks are becoming increasingly sophisticated and can mimic reputable sources like banks, social media platforms, or even government agencies. They often use alarming or enticing language designed to provoke immediate action. This is where psychological vulnerabilities come into play. Attackers know that when people feel urgency, fear, or excitement, they tend to act quickly and without the usual careful thought. By exploiting these emotions, hackers can manipulate even the most cautious individuals. Understanding these tactics is essential to recognizing and preventing such scams.</p>
              <p>In this training module, we will cover a variety of essential topics. First, we will delve deeper into what phishing is and the many forms it can take—helping you to spot the warning signs. Next, we will present realistic phishing scenarios to illustrate how these attacks work in the real world, making the threat tangible and relatable. We will then explore effective defense strategies to safeguard your information. Interactive exercises throughout the module will reinforce these lessons, allowing you to practice identifying and responding to phishing attacks. Finally, we will wrap up with a conclusion that reviews key concepts and provides actionable advice for staying secure online.</p>
              <p>By the end of this session, you will be better equipped to recognize potential threats and understand the simple but powerful practices that can protect you in the digital age. Let’s get started on this journey to boost your cybersecurity awareness, {USER_NAME}!</p>
            </div>";
        } else {
            $intro = "<div>
  <h2>Introduction to the Phishing Problem</h2>
  <p>Hello Francesco,</p>
  <p>Phishing is one of the most common and dangerous cyber threats today. It’s a type of online scam where attackers use fake emails, messages, or websites to trick you into giving away personal information or money. These deceptive tactics often mimic reputable companies or trusted contacts, making it very easy for unsuspecting users to become victims. The danger comes not just from the potential loss or damage to your finances, but also from the broader impact such breaches can have on privacy and security.</p>
  <p>Attackers often exploit basic human emotions and psychological vulnerabilities to make their scams more convincing. For example, they might invoke a sense of urgency by claiming that your account is at risk, or appeal to your curiosity with an unexpected message from someone you know. By playing on feelings of fear, trust, or even excitement, scammers can manipulate even careful individuals if they are not aware of these techniques. Understanding these psychological tactics is an essential first step in protecting yourself.</p>
  <p>Throughout this training module, we will explore several key aspects of phishing and how to guard against it. In the first part of our course, we will take a deep dive into what phishing is, why it is harmful, and the psychological strategies behind it. Next, we will present a realistic phishing scenario to help you recognize the warning signs. Then, we will discuss practical defense strategies to secure your digital life. Following that, interactive exercises will give you a chance to put your new knowledge to the test. Finally, we will conclude with a recap of the best practices and final thoughts to help you stay vigilant online.</p>
  <p>By the end of this training, you will be well-equipped to recognize and deal with phishing attempts, keeping your information safe and secure. Let's get started, Francesco!</p>
</div>";
        }
        $intro = str_replace("{USER_NAME}", $user_name, $intro);
        $this->introduction = $intro;

        if ($length == "short") {
            $scenario = '<div>
              <h2>Realistic Phishing Scenario Presentation</h2>
              <p>Hello {USER_NAME}, welcome to this scenario-based segment where we’ll walk through a realistic phishing attack. Imagine it’s a regular Monday morning, and you’re checking your email over a cup of coffee. Among the many messages, one subject line immediately catches your eye: "URGENT: Verify Your Account Now!" Curious and slightly alarmed, you click it open. The email appears to be sent from your bank’s security team, urging you to confirm your account details before your access is allegedly suspended. This is a scenario that many people, even cybersecurity-aware users, can relate to. Hackers craft such emails to exploit your natural concerns about security and financial matters, hoping you respond without hesitation.</p>
              <p>Below, you will see a simulated phishing email. As you read through it, note that certain parts of the email have been made interactive. If you click on any highlighted element, a short description will explain the specific phishing technique being used. Take your time with this exercise and consider every detail carefully.</p>
              <hr>
              <h3>Simulated Phishing Email</h3>
              <div style="border:1px solid #ccc;padding:15px; margin-bottom:20px;">
                <p><strong>From:</strong> <span style="cursor:pointer; color:#0066cc;" onclick="toggleDescription(\'senderDesc\')">security@bank-update.com</span></p>
                <p><strong>Date:</strong> April 25, 2023, 8:45 AM</p>
                <p><strong>Subject:</strong> <span style="cursor:pointer; color:#0066cc;" onclick="toggleDescription(\'subjectDesc\')">URGENT: Verify Your Account Now!</span></p>
                <p>Dear Valued Customer,</p>
                <p>
                  We have detected unusual activity on your account. For your protection, your account has been temporarily <span style="cursor:pointer; color:#0066cc;" onclick="toggleDescription(\'actionDesc\')">suspended</span>. To restore access, please verify your identity by clicking the secure link provided below:
                </p>
                <p style="text-align:center;">
                  <a href="#" style="cursor:pointer; color:#0066cc; text-decoration:underline;" onclick="toggleDescription(\'linkDesc\')">https://www.banksecure-update.com/login</a>
                </p>
                <p>
                  Failure to verify your information within 24 hours will lead to permanent restrictions on your account. Our team is dedicated to protecting your financial security, and this measure is taken in your best interest.
                </p>
                <p>
                  Thank you,
                  <br>
                  Bank Security Team
                </p>
              </div>

              <!-- Hidden descriptions for interactive elements -->
              <div id="senderDesc" style="display:none; background:#f9f9f9; border:1px dashed #aaa; padding:10px; margin-bottom:10px;">
                <strong>Description:</strong> The sender’s email address is designed to look like it’s coming from your bank, but small differences (like “bank‑update” instead of the official domain) can be a red flag. Spoofed email addresses are a common phishing technique.
              </div>
              <div id="subjectDesc" style="display:none; background:#f9f9f9; border:1px dashed #aaa; padding:10px; margin-bottom:10px;">
                <strong>Description:</strong> The subject line uses urgent language. Attackers often create a sense of urgency to prompt immediate action without allowing you time to think critically about the message.
              </div>
              <div id="actionDesc" style="display:none; background:#f9f9f9; border:1px dashed #aaa; padding:10px; margin-bottom:10px;">
                <strong>Description:</strong> The use of strong, alarming words like “suspended” aims to scare you into reacting quickly. This is a psychological tactic meant to bypass your natural caution.
              </div>
              <div id="linkDesc" style="display:none; background:#f9f9f9; border:1px dashed #aaa; padding:10px; margin-bottom:10px;">
                <strong>Description:</strong> The hyperlink provided in phishing emails often appears similar to an authentic website. However, hovering over the link (or clicking for description) can reveal subtle differences or irregularities that indicate it’s a deceptive URL.
              </div>
              <hr>
              <h3>Interactive Decision Point</h3>
              <form id="decisionForm" onsubmit="handleDecision(event)">
                <p>{USER_NAME}, given the details of the email you just saw, what would you do?</p>
                <label>
                  <input type="radio" name="decision" value="clickLink" required>
                  Click the link immediately to resolve the issue.
                </label>
                <br>
                <label>
                  <input type="radio" name="decision" value="ignoreEmail">
                  Ignore the email and delete it.
                </label>
                <br>
                <label>
                  <input type="radio" name="decision" value="contactBank">
                  Contact your bank directly using official contact information.
                </label>
                <br><br>
                <button type="submit">Submit Your Decision</button>
              </form>
              <div id="feedback" style="display:none; background:#e7f3fe; border:1px solid #2196F3; padding:15px; margin-top:20px;">
                <!-- Feedback will be displayed here -->
              </div>
              <script>
                function toggleDescription(id) {
                  var element = document.getElementById(id);
                  if (element.style.display === "none") {
                    element.style.display = "block";
                  } else {
                    element.style.display = "none";
                  }
                }

                function handleDecision(event) {
                  event.preventDefault();
                  var radios = document.getElementsByName(\'decision\');
                  var choice;
                  for (var i = 0; i < radios.length; i++) {
                    if (radios[i].checked) {
                      choice = radios[i].value;
                    }
                  }
                  var feedback = document.getElementById(\'feedback\');
                  if (choice === \'clickLink\') {
                    feedback.innerHTML = "<strong>Feedback:</strong> Clicking the suspicious link is risky. Attackers design these links to harvest your credentials or introduce malware into your system. This choice could lead to a compromise of your sensitive information.";
                  } else if (choice === \'ignoreEmail\') {
                    feedback.innerHTML = "<strong>Feedback:</strong> You are correct to be cautious. Ignoring and deleting the email helps prevent accidental engagement with a phishing attempt. However, consider reporting such emails to your organization’s IT security team for further analysis.";
                  } else if (choice === \'contactBank\') {
                    feedback.innerHTML = "<strong>Feedback:</strong> Excellent decision! Using official contact details to verify the situation is the safest approach. This method ensures that you receive accurate information and protects you from potential phishing scams.";
                  }
                  feedback.style.display = "block";
                }
              </script>
              <hr>
              <h3>Reflection & Learning</h3>
              <p>
                In this interactive exercise, we demonstrated a phishing scenario that leverages multiple techniques aimed at deceiving the recipient. Each element—from the spoofed sender address to the urgent call for action—exemplifies how attackers manipulate both technology and psychology. When a phishing email contains a deceptive URL, an alarming subject line, or a sense of urgency, it exploits common human tendencies; these include a willingness to trust communications that appear to come from a familiar or authoritative source, and the natural impulse to act quickly when feeling anxious or threatened.
              </p>
              <p>
                As you saw, the most secure approach in such cases is to take a moment to evaluate the email carefully. Your decision on whether to click a link, report the email, or ignore it can have far-reaching consequences. By choosing to contact your bank using official information, you effectively bypass the attacker\'s trap and protect your personal and financial data from unauthorized access.
              </p>
              <p>
                This scenario reinforces the importance of recognizing phishing techniques and understanding your psychological responses to urgent communications. Remember, phishing attacks are designed to play on common vulnerabilities—fear, urgency, and trust. Being aware of these manipulation tactics is your first line of defense in a digital world where cyber threats are continually evolving.
              </p>
              <p>
                Our goal with this exercise is not only to help you identify potential threats but also to instill a habit of verifying unexpected requests for personal information. By cultivating a mindset of skepticism when encountering such emails, you enhance your resilience against phishing attacks. The lessons learned here empower you to act cautiously and confidently in the face of online threats.
              </p>
              <p>
                Thank you for engaging with this interactive scenario, {USER_NAME}. We hope that the detailed explanations and immediate feedback have given you practical insights into how phishing attacks operate and, more importantly, what steps you can take to defend yourself. Stay alert and continue to practice these strategies throughout the rest of our training module.
              </p>
            </div>';
        } else {
            $scenario = "";
        }
        $scenario = str_replace("{USER_NAME}", $user_name, $scenario);
        $this->scenario = $scenario;

        if ($length == "short") {
            $defense_strategies = '<div>
          <h2>Defense Strategies</h2>
          <p>Hello {USER_NAME}, in this section we will explore concrete defense strategies to protect yourself against phishing attacks. Phishing is not only about recognizing deceptive emails, but also about taking proactive measures to safeguard your personal and professional data. Below are a series of actionable items and guidelines that you can follow to build your defenses against these increasingly sophisticated threats.</p>

          <h3>1. Verify the Sender’s Details</h3>
          <p>One of the primary ways to identify a phishing email is by looking closely at the sender\'s email address and name. Here’s how you can do it:</p>
          <ul>
            <li>
              <strong>Double-check the domain:</strong> A genuine email from your bank, for example, should come from an official domain (e.g., "@yourbank.com"). Look for subtle misspellings or extra words (like “bank-update” or “secure-bank”) that suggest the address might be spoofed.
            </li>
            <li>
              <strong>Examine the sender’s name and email combination:</strong> Sometimes, the sender\'s name may look familiar, but if the email address behind it is different from what you expect, that is a red flag. Always verify both parts.
            </li>
            <li>
              <strong>Check email headers:</strong> If you’re technically inclined, review the email header details. Headers can reveal the true source of the email, but this may require additional technical knowhow or guidance from your IT department.
            </li>
          </ul>
          <p>By routinely verifying the sender’s details, you minimize the risk of falling prey to spoofed emails that masquerade as communications from legitimate companies.</p>

          <h3>2. Inspect Links Before Clicking</h3>
          <p>Phishing emails often contain links that appear legitimate at first glance but lead to deceptive websites. To protect yourself:</p>
          <ul>
            <li>
              <strong>Hover over the link:</strong> Before clicking, place your mouse cursor over the link to view the actual URL. This simple action can reveal discrepancies between the displayed text and the real destination.
            </li>
            <li>
              <strong>Look for HTTPS and genuine domain names:</strong> Genuine websites use secure protocols (HTTPS) and usually have a domain that corresponds to the company’s official website. If the link directs you to an unfamiliar address or a website without proper encryption, proceed with caution.
            </li>
            <li>
              <strong>Be cautious with shortened URLs:</strong> Attackers often use URL shorteners to hide the real address. If you see a shortened URL, use an online URL expander or ask your IT team for assistance before proceeding.
            </li>
          </ul>
          <p>Taking these precautionary measures every time you encounter a link will significantly reduce the chances of inadvertently sharing your personal information on a fraudulent website.</p>

          <h3>3. Scrutinize the Email Content</h3>
          <p>Phishing emails are designed to elicit an immediate reaction by creating a sense of urgency. Pay attention to the tone and content:</p>
          <ul>
            <li>
              <strong>Look for urgent language:</strong> Phishing emails often include words like "urgent," "immediate action required," or "account suspended." While your bank or service provider might alert you of critical issues, genuine communications rarely use sensationalized language without prior notice.
            </li>
            <li>
              <strong>Check for grammatical errors and typos:</strong> Many phishing emails contain spelling mistakes or awkward language as they are often produced in haste or by non-native speakers. Consistent poor grammar is usually a sign that the email is not from a reputable source.
            </li>
            <li>
              <strong>Evaluate the message’s structure:</strong> Authentic emails from established companies are generally well-formatted and follow a consistent style. If the formatting looks off or the content seems overly generic, treat it with suspicion.
            </li>
          </ul>
          <p>Learning to identify these small details can help you distinguish between legitimate communications and fraudulent attempts.</p>

          <h3>4. Use Multi-Factor Authentication (MFA)</h3>
          <p>Even if a phishing email successfully convinces you to enter your password, multi-factor authentication adds an additional layer of security. Here’s how MFA helps:</p>
          <ul>
            <li>
              <strong>Additional verification steps:</strong> MFA requires a second form of verification (like a text message code, fingerprint, or authentication app) before granting access to your account.
            </li>
            <li>
              <strong>Stops unauthorized access:</strong> Even if an attacker obtains your password, the second layer of defense can block them from gaining full access.
            </li>
          </ul>
          <p>Enabling MFA on your important accounts significantly increases your security, making it much harder for attackers to compromise your data.</p>

          <h3>5. Keep Your Software Up-to-Date</h3>
          <p>Hackers often exploit vulnerabilities in outdated software. Regular updates can protect you in several ways:</p>
          <ul>
            <li>
              <strong>Security patches:</strong> Updates often include patches for recently discovered security vulnerabilities, reducing the risk of exploitation.
            </li>
            <li>
              <strong>Improved features:</strong> New software versions include improved security features that can help detect or block phishing attempts.
            </li>
          </ul>
          <p>Ensure your operating system, email client, and web browser are always running the latest versions. This is one of the simplest yet most effective defense strategies you can adopt.</p>

          <h3>6. Educate Yourself and Others</h3>
          <p>Staying informed is one of your greatest assets in the fight against phishing:</p>
          <ul>
            <li>
              <strong>Know the common techniques:</strong> As you learn more about phishing methods, such as deceptive hyperlinks, spoofed addresses, or urgent language, you’ll become better at spotting them.
            </li>
            <li>
              <strong>Share knowledge:</strong> Whether it’s with friends, family, or colleagues, spreading awareness can help protect your wider community.
            </li>
            <li>
              <strong>Participate in training:</strong> Engage regularly with cybersecurity training modules. This hands-on practice improves your recognition skills and keeps you updated on the latest phishing trends.
            </li>
          </ul>
          <p>The more informed you are, the better you can defend yourself against the ever-changing tactics used by cybercriminals. This ongoing education is a key component of your overall cybersecurity strategy.</p>

          <h3>7. Recognize Genuine Emails</h3>
          <p>While knowing what to look out for in a phishing email is crucial, it’s equally important to recognize what constitutes a genuine email. Here are some practical tips:</p>
          <ul>
            <li>
              <strong>Official domain and contact details:</strong> A genuine email will come from a verified and consistently used domain. Compare the domain in the email address with previous communications you have received from that organization.
            </li>
            <li>
              <strong>Personalized information:</strong> Legitimate companies will often include identifiable information, like your full name or an account number, which they have on record. Phishing messages, by contrast, often use vague greetings like “Dear Customer.”
            </li>
            <li>
              <strong>Secure links and attachments:</strong> Genuine emails use secure links (look for "https://") and refrain from attaching files unless necessary. If there are attachments, they should be expected and relevant to the communication.
            </li>
            <li>
              <strong>Professional formatting:</strong> Authentic communications from reputable organizations are typically well-formatted, free of spelling errors, and maintain consistent branding. Notice any deviations from the norm in terms of colors, logos, or layout.
            </li>
          </ul>
          <p>By learning these criteria, you can build a mental checklist that helps determine whether an email is truly genuine or should be approached with caution.</p>

          <h3>8. Use Advanced Security Tools</h3>
          <p>While user vigilance is a frontline defense, advanced security tools can further help mitigate risks:</p>
          <ul>
            <li>
              <strong>Email filtering and anti-phishing software:</strong> Utilize software that scans incoming emails for known phishing techniques, suspicious links, or unsafe attachments. Many email providers offer built-in protection features.
            </li>
            <li>
              <strong>Web browser protections:</strong> Consider browser extensions and security settings that warn you about potentially dangerous websites.
            </li>
            <li>
              <strong>Firewall and antivirus programs:</strong> Regularly update and run these programs to help defend your digital environment from malicious software that might be delivered as part of phishing campaigns.
            </li>
          </ul>
          <p>These tools act as your digital security net. While they don’t replace the need for vigilance, they offer an additional layer of protection that can catch threats before they cause harm.</p>

          <h3>9. Report Suspicious Emails</h3>
          <p>If you suspect that you have received a phishing email, taking the right steps can protect yourself and others:</p>
          <ul>
            <li>
              <strong>Use your organization’s reporting procedures:</strong> Many companies have dedicated channels for reporting suspicious emails. This helps IT teams to analyze and respond to potential threats.
            </li>
            <li>
              <strong>Forward the email to your email provider:</strong> Some providers offer mechanisms to report abuse or phishing attempts, which can help block similar emails in the future.
            </li>
            <li>
              <strong>Notify your bank or the impersonated organization:</strong> If you receive an email that appears to be from your bank or another trusted entity, contact them directly using phone numbers or contact details from their official website—not the contact information provided in the suspicious email.
            </li>
          </ul>
          <p>Reporting phishing attempts not only helps protect you, but contributes to a broader effort to mitigate cyber threats across the community.</p>

          <h3>Conclusion</h3>
          <p>To summarize, {USER_NAME}, defending against phishing involves a multi-layered approach. Begin by carefully verifying the sender’s details and inspecting links before clicking. Scrutinize the email content for red flags such as urgent language, typos, and inconsistent formatting. Enrich your defense strategy by enabling multi-factor authentication and keeping all your software up-to-date.</p>

          <p>Furthermore, educating yourself, understanding what constitutes a genuine email, and using advanced security tools equip you with robust measures against phishing attacks. Always remember that when in doubt, take an extra moment to verify the legitimacy of an email or contact the organization directly using verified contact details. Lastly, don’t hesitate to report any suspicious emails to help protect your community and enhance collective security.</p>

          <p>These actionable strategies are designed to be easy to follow and implement, ensuring that you can maintain a strong security posture in your daily online activities. By intentionally integrating these practices into your routine, you are taking proactive steps not only to shield yourself but also those around you from the threats posed by phishing. Stay cautious, stay informed, and remember that vigilance is your best defense in a rapidly evolving digital landscape.</p>
        </div>';
        } else {
            $defense_strategies = "";
        }
        $defense_strategies = str_replace("{USER_NAME}", $user_name, $defense_strategies);
        $this->defense_strategies = $defense_strategies;

        if ($length == "short") {
            $exercises = '<div>
          <h2>Interactive Exercises</h2>
          <p>Hello {USER_NAME}, it’s time to put your newly acquired knowledge to the test with a series of simulated emails. In each exercise, you’ll review an email and use the radio buttons provided to classify it as “Phishing” or “Legitimate.” After you submit your choice, you’ll receive immediate feedback that explains the cues which helped determine the correct classification.</p>

          <!-- Exercise 1: Simulated Phishing Email from a Work Colleague -->
          <h3>Exercise 1: Email from a Work Colleague</h3>
          <div style="border:1px solid #ccc; padding:15px; margin-bottom:10px;">
            <p><strong>From:</strong> Bob Martin &lt;<span style="color:#0066cc;">bob.martin@techsolut1onsinc.com</span>&gt;</p>
            <p><strong>Date:</strong> May 10, 2023, 9:15 AM</p>
            <p><strong>Subject:</strong> Important: Review the Attached Project Update</p>
            <p>Hi {USER_NAME},</p>
            <p>I’ve attached the latest update on our project for your review. Please open the attachment immediately and let me know your thoughts. We need to discuss this in our meeting later today.</p>
            <p>Thanks,</p>
            <p>Bob</p>
            <p><em>Note: The sender’s email address contains a subtle misspelling in the domain name ("techsolut1onsinc.com" instead of "techsolutionsinc.com"), which is a common phishing cue.</em></p>
          </div>
          <form onsubmit="handleExercise(event, \'ex1\')">
            <p>{USER_NAME}, do you think this email is a Phishing attempt or a Legitimate email?</p>
            <label>
              <input type="radio" name="ex1" value="Phishing" required> Phishing
            </label>
            <br>
            <label>
              <input type="radio" name="ex1" value="Legitimate"> Legitimate
            </label>
            <br><br>
            <button type="submit">Submit Answer</button>
          </form>
          <div id="feedback_ex1" style="display:none; background:#e7f3fe; border:1px solid #2196F3; padding:10px; margin-bottom:20px;"></div>

          <!-- Exercise 2: Simulated Phishing Email Mimicking Amazon -->
          <h3>Exercise 2: Email Mimicking Amazon</h3>
          <div style="border:1px solid #ccc; padding:15px; margin-bottom:10px;">
            <p><strong>From:</strong> Order Update &lt;<span style="color:#0066cc;">order-update@amazon-support.com</span>&gt;</p>
            <p><strong>Date:</strong> May 10, 2023, 10:05 AM</p>
            <p><strong>Subject:</strong> Your Order Has Been Delayed – Please Confirm Your Details</p>
            <p>Dear Customer,</p>
            <p>Due to unforeseen circumstances, your recent order has been delayed. Please confirm your shipping details immediately by clicking the secure link below to avoid cancellation:</p>
            <p style="text-align:center;">
              <a href="#" style="color:#0066cc; text-decoration:underline;">http://www.amazxn.com/secure-login</a>
            </p>
            <p>Thank you for shopping with us!</p>
            <p>Amazon Customer Service</p>
            <p><em>Note: Despite the familiar branding, the sender’s domain and the displayed URL have subtle differences (“amazon-support.com” and "amazxn.com") that do not match Amazon’s official domains.</em></p>
          </div>
          <form onsubmit="handleExercise(event, \'ex2\')">
            <p>{USER_NAME}, do you think this email is a Phishing attempt or a Legitimate email?</p>
            <label>
              <input type="radio" name="ex2" value="Phishing" required> Phishing
            </label>
            <br>
            <label>
              <input type="radio" name="ex2" value="Legitimate"> Legitimate
            </label>
            <br><br>
            <button type="submit">Submit Answer</button>
          </form>
          <div id="feedback_ex2" style="display:none; background:#e7f3fe; border:1px solid #2196F3; padding:10px; margin-bottom:20px;"></div>

          <!-- Exercise 3: Simulated Phishing Email Mimicking Airbnb -->
          <h3>Exercise 3: Email Mimicking Airbnb</h3>
          <div style="border:1px solid #ccc; padding:15px; margin-bottom:10px;">
            <p><strong>From:</strong> Airbnb Reservations &lt;<span style="color:#0066cc;">noreply@airbnb-confirm.com</span>&gt;</p>
            <p><strong>Date:</strong> May 10, 2023, 11:20 AM</p>
            <p><strong>Subject:</strong> Action Required: Verify Your Reservation Details</p>
            <p>Dear {USER_NAME},</p>
            <p>We recently noticed some inconsistencies with your upcoming reservation. To ensure the continuity of your booking, please verify your details by clicking on the link below:</p>
            <p style="text-align:center;">
              <a href="#" style="color:#0066cc; text-decoration:underline;">http://www.airb6nb.com/login</a>
            </p>
            <p>If you do not verify within the next 24 hours, your reservation may be canceled.</p>
            <p>Thank you for choosing Airbnb!</p>
            <p><em>Note: While the email seems to come from a familiar service, take a closer look at the sender’s domain and the URL. “airb6nb.com” is a subtle imitation of the genuine Airbnb domain.</em></p>
          </div>
          <form onsubmit="handleExercise(event, \'ex3\')">
            <p>{USER_NAME}, do you think this email is a Phishing attempt or a Legitimate email?</p>
            <label>
              <input type="radio" name="ex3" value="Phishing" required> Phishing
            </label>
            <br>
            <label>
              <input type="radio" name="ex3" value="Legitimate"> Legitimate
            </label>
            <br><br>
            <button type="submit">Submit Answer</button>
          </form>
          <div id="feedback_ex3" style="display:none; background:#e7f3fe; border:1px solid #2196F3; padding:10px; margin-bottom:20px;"></div>

          <!-- Exercise 4: Genuine Email -->
          <h3>Exercise 4: Genuine Email</h3>
          <div style="border:1px solid #ccc; padding:15px; margin-bottom:10px;">
            <p><strong>From:</strong> Airbnb &lt;<span style="color:#0066cc;">noreply@airbnb.com</span>&gt;</p>
            <p><strong>Date:</strong> May 10, 2023, 12:00 PM</p>
            <p><strong>Subject:</strong> Your Upcoming Reservation Confirmation</p>
            <p>Dear {USER_NAME},</p>
            <p>Thank you for booking with Airbnb! Your reservation at “Seaside Retreat” has been confirmed. For details of your booking and additional travel tips, please visit your account dashboard by clicking the secure link below:</p>
            <p style="text-align:center;">
              <a href="https://www.airbnb.com/reservations" style="color:#0066cc; text-decoration:underline;">https://www.airbnb.com/reservations</a>
            </p>
            <p>We look forward to hosting you!</p>
            <p>Warm regards,</p>
            <p>The Airbnb Team</p>
            <p><em>Note: This email uses the correct official domain (<strong>airbnb.com</strong>) and the secure HTTPS protocol, which are reliable indicators of a legitimate email.</em></p>
          </div>
          <form onsubmit="handleExercise(event, \'ex4\')">
            <p>{USER_NAME}, do you think this email is a Phishing attempt or a Legitimate email?</p>
            <label>
              <input type="radio" name="ex4" value="Phishing" required> Phishing
            </label>
            <br>
            <label>
              <input type="radio" name="ex4" value="Legitimate"> Legitimate
            </label>
            <br><br>
            <button type="submit">Submit Answer</button>
          </form>
          <div id="feedback_ex4" style="display:none; background:#e7f3fe; border:1px solid #2196F3; padding:10px; margin-bottom:20px;"></div>

          <script>
            function handleExercise(event, exId) {
              event.preventDefault();
              var radios = document.getElementsByName(exId);
              var selected;
              for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                  selected = radios[i].value;
                }
              }
              var feedbackDiv = document.getElementById(\'feedback_\' + exId);
              var message = "";

              // Define correct answers and feedback messages based on exercise ID.
              switch(exId) {
                case "ex1":
                  if(selected === "Phishing") {
                    message = "<strong>Correct!</strong> Notice the subtle misspelling in the sender’s domain. This is a common phishing technique to deceive you by mimicking a trusted colleague’s email.";
                  } else {
                    message = "<strong>Incorrect.</strong> Although the email appears familiar, the sender’s domain has a slight error. This discrepancy is a key cue that the email may be a phishing attempt.";
                  }
                  break;
                case "ex2":
                  if(selected === "Phishing") {
                    message = "<strong>Correct!</strong> The sender’s email and the URL contain minor discrepancies. The domain \'amazon-support.com\' and the suspicious URL \'amazxn.com/secure-login\' do not match Amazon’s official domains.";
                  } else {
                    message = "<strong>Incorrect.</strong> Even though the email looks like it’s from Amazon, the subtle domain differences give away its true nature. Always hover to inspect link details.";
                  }
                  break;
                case "ex3":
                  if(selected === "Phishing") {
                    message = "<strong>Correct!</strong> A closer look reveals that the sender’s address and URL are off. Instead of the genuine \'airbnb.com\', the phishing email uses \'airb6nb.com\', a classic sign of spoofing.";
                  } else {
                    message = "<strong>Incorrect.</strong> While the email has familiar elements, the altered domain in the URL (\'airb6nb.com\') is a clear sign of phishing. Trust only verified domains.";
                  }
                  break;
                case "ex4":
                  if(selected === "Legitimate") {
                    message = "<strong>Correct!</strong> This email comes from a verified domain (airbnb.com) and uses HTTPS for secure communication. These cues indicate that you’re dealing with a genuine message.";
                  } else {
                    message = "<strong>Incorrect.</strong> The email’s proper formatting, verified sender address, and secure link are typical of legitimate communications. Be sure to check these details carefully.";
                  }
                  break;
                default:
                  message = "Error: Unrecognized exercise.";
              }

              feedbackDiv.innerHTML = message;
              feedbackDiv.style.display = "block";
            }
          </script>

          <p>Review the cues in each email carefully, and remember, paying attention to sender details, URL authenticity, and formatting consistency helps you make an informed decision. Good luck, {USER_NAME}!</p>
        </div>';
        }
        else {
            $exercises = "";
        }
        $exercises = str_replace("{USER_NAME}", $user_name, $exercises);
        $this->exercises = $exercises;

        if ($length == "short") {
            $conclusions = '<div>
          <h2>Conclusions</h2>
          <p>Hello {USER_NAME}, as we wrap up our anti‑phishing training session, let’s take a moment to recap the importance of staying vigilant against phishing attacks. Phishing is a highly effective technique used by cybercriminals to steal sensitive information by exploiting trust and psychological vulnerabilities. Through our exploration of realistic scenarios and interactive exercises, you’ve gained firsthand experience in recognizing techniques like deceptive sender addresses, misleading URLs, and urgent language. This awareness is a crucial first step in protecting yourself from potential cyber threats.</p>

          <p>Throughout the training, we focused on clear, actionable strategies. Remember to always double-check the sender’s domain and be cautious with unfamiliar or urgent messages. By hovering over links, you can reveal hidden URLs and assess if they lead to safe destinations. If something feels off, engage in further scrutiny before responding or clicking on any links. In our exercises, you saw how subtle changes can signal malicious intent, even when the email appears to be from a trusted source like a colleague or a well-known company.</p>

          <p>Other practical actions include scrutinizing email content for mismatches or grammatical errors, relying on multi-factor authentication to add an extra layer of security, and reporting suspicious emails to your IT department or email provider. Each of these steps is designed to help you build a robust defense against phishing attempts and ensure that you remain in control of your digital security.</p>

          <p>Thank you for attending this session, {USER_NAME}. Your engagement and careful consideration of each tip not only help in protecting your own information but also contribute to a safer online environment for everyone. Stay vigilant, apply these strategies in your daily digital interactions, and remember that your awareness is one of your most powerful safeguards against cyber threats.</p>
        </div>';
        } else {
            $conclusions = '';
        }
        $conclusions = str_replace("{USER_NAME}", $user_name, $conclusions);
        $this->conclusions = $conclusions;
        $this->save();
    }

}
