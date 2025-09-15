<?php
return [
    'title'        => 'Questionnaire Optimized for Phishing Vulnerability',
    'version'      => 'v3.4',
    'control_question' => 'This item is a quality control check, please select ',
    'scale'        => [
        '1' => 'Not at all agree',
        '2' => 'Slightly agree',
        '3' => 'Neutral / Unsure',
        '4' => 'Fairly agree',
        '5' => 'Completely agree',
    ],
    'compileError' => 'Please answer all the questions!',
    'instructions' => "Likert scale used for all non scenario items:<br>1 = Not at all agree, 2 = Slightly agree, 3 = Neutral / Unsure, 4 = Fairly agree, 5 = Completely agree.<br>
    If you do not know a technical term (e.g., SPF, DKIM) or are unsure, choose 1 = Not at all agree.<br>There are 2 attention checks. Failing them may lead to exclusion of the observation or a request to repeat the questionnaire.<br>Please answer carefully and honestly.",
    'previous'     => 'Previous',
    'next'         => 'Next',
    'submit'       => 'Submit',
    'too_fast_title'   => 'Please complete the questions slowly!',
    'too_fast_heading' => "You're going too fast!",
    'too_fast_body'    => "Please slow down and carefully read each question before selecting an answer.<br>Your thoughtful responses matter to us.",
    'close'            => 'Close',
    'already_answered'        => 'Already answered',
    'completed_successfully'  => 'Phishing Vulnerability Assessment completed successfully!',
    'overclaiming_answer' => "I don't know / Not applicable",
    'risk_levels' => [
        'very_low' => 'Very Low',
        'low'    => 'Low => Good skills and protective behaviors',
        'medium' => 'Medium => Some vulnerabilities; targeted training needed',
        'high'   => 'High => High likelihood of falling for phishing',
        'very_high'=> 'Very High',
    ],
    'sections'     => [
        [
            'section_id' => 'S0',
            'name'       => "Introduction",
            'items'      => [
                [
                    'id'        => 'INTRO',
                    'text'      => "This questionnaire assesses individual vulnerability to phishing (fraudulent emails), smishing (fraudulent SMS) and svishing (fraudulent phone calls).<br>The aim is to produce a personalized risk profile and recommend targeted training.<br>Estimated completion time: 8–12 minutes.<br>Responses will be processed anonymously and in aggregate.",
                ],
            ],
        ],
        [
            'section_id' => 'S1',
            'name'       => "Section 1 — Technical Knowledge and Skills (Q1–Q5)",
            'items'      => [
                [
                    'id'        => 'Q1',
                    'text'      => "I can recognize common phishing indicators in an email.",
                    'source'    => "Ad hoc / HAIS-Q 2.7",
                    'rationale' => "Measures firsthand knowledge of phishing signals - key predictor for identification",
                    'dimension' => "Technical_Competence",
                ],
                [
                    'id'        => 'Q2',
                    'text'      => "I know how to check whether a link in an email is safe before clicking it.",
                    'source'    => "Ad hoc / HAIS-Q 2.8",
                    'rationale' => "Specific technical expertise - concrete action for phishing prevention",
                    'dimension' => "Technical_Skills",
                ],
                [
                    'id'        => 'Q3',
                    'text'      => "I can tell the difference between a genuine email address and a spoofed/fake one.",
                    'source'    => "Ad hoc / HAIS-Q 2.6",
                    'rationale' => "Critical Discrimination Skills for Email Spoofing",
                    'dimension' => "Technical_Competence",
                ],
                [
                    'id'        => 'Q4',
                    'text'      => "I am familiar with email authentication protocols such as SPF or DKIM (if you don't know, select <em>Not at all agree</em>, or <em>I don't know / Not applicable</em>).",
                    'source'    => "Ad hoc - Overclaiming Control",
                    'rationale' => "Control items to identify overconfidence/social desirability bias",
                    'dimension' => "Bias_Control",
                ],
                [
                    'id'        => 'Q5',
                    'text'      => "I know how and to whom to report a suspicious email within my organization.",
                    'source'    => "HAIS-Q 3.1 / SeBIS 14 (Adapted)",
                    'rationale' => "Procedural knowledge for correct organizational response",
                    'dimension' => "Procedural_Knowledge",
                ],
            ],
        ],
        [
            'section_id' => 'S2',
            'name'       => "Section 2 — Security Behaviors and Habits (Q6–Q11)",
            'items'      => [
                [
                    'id'        => 'Q6',
                    'text'      => "I only open emails when I clearly recognize the sender and the subject.",
                    'source'    => "HAIS-Q 2.4 / SeBIS 12 (Reverse)",
                    'rationale' => "Direct preventive behavior - exercise caution when opening emails",
                    'dimension' => "Preventive_Behavior",
                ],
                [
                    'id'        => 'Q7',
                    'text'      => "I sometimes click links in emails without checking their reliability first.",
                    'source'    => "SeBIS 15 (Adapted)",
                    'rationale' => "Verification behavior - specific protective action for phishing",
                    'dimension' => "Verification_Behavior",
                ],
                [
                    'id'        => 'Q8',
                    'text'      => "If an email seems suspicious, I verify it through another channel (phone, company chat, etc.).",
                    'source'    => "HAIS-Q 2.10 / SeBIS 8 (Adapted)",
                    'rationale' => "Multi-channel verification - the gold standard for preventing social engineering",
                    'dimension' => "Verification_Behavior",
                ],
                [
                    'id'        => 'Q9',
                    'text'      => "I regularly update my passwords and follow good password practices.",
                    'source'    => "SeBIS 9 / HAIS-Q 1.6",
                    'rationale' => "General security maintenance behavior",
                    'dimension' => "General_Security_Behavior",
                ],
                [
                    'id'        => 'Q10',
                    'text'      => "I read emails carefully before taking any requested actions.",
                    'source'    => "SeBIS 12",
                    'rationale' => "Deliberate attention behavior - counteracts impulsivity",
                    'dimension' => "Deliberate_Attention",
                ],
                [
                    'id'        => 'Q11',
                    'text'      => "I sometimes open or click emails about unexpected offers or prize notifications out of curiosity.",
                    'source'    => "Ad hoc",
                    'rationale' => "Phishing based on curiosity/gain is very common",
                    'dimension' => "Deliberate_Attention",
                ],
                [
                    'id'        => 'AC1',
                    'text'      => "Please select <em>Fairly agree</em> for this item.",
                    'answer_ac1'    => '4'
                ],
            ],
        ],
        [
            'section_id' => 'S3',
            'name'       => "Section 3 — Psychological Vulnerabilities (Q12–Q16) ",
            'items'      => [
                [
                    'id'        => 'Q12',
                    'text'      => "I respond quickly to emails that claim urgency, without thinking.",
                    'source'    => "Ad hoc (Urgency/Social Engineering)",
                    'rationale' => "Vulnerability to pressure/urgency tactics – a strong predictor of social engineering",
                    'dimension' => "Psychological_Vulnerabilities",
                ],
                [
                    'id'        => 'Q13',
                    'text'      => "I tend to trust requests for information when they appear to come from an official source.",
                    'source'    => "Ad hoc (Overtrust/Authority)",
                    'rationale' => "Vulnerability to authority-based social engineering",
                    'dimension' => "Psychological_Vulnerabilities",
                ],
                [
                    'id'        => 'Q14',
                    'text'      => "When I am stressed, I pay less attention to details in emails.",
                    'source'    => "Ad hoc (Cognitive Vulnerability)",
                    'rationale' => "Cognitive vulnerability under stress - a predictor of errors under pressure",
                    'dimension' => "Psychological_Vulnerabilities",
                ],
                [
                    'id'        => 'Q15',
                    'text'      => "I tend to trust messages that include familiar names or references, even if they are unexpected.",
                    'source'    => "Ad hoc (Spear Phishing)",
                    'rationale' => "Vulnerability to spear phishing and name spoofing",
                    'dimension' => "Psychological_Vulnerabilities",
                ],
                [
                    'id'        => 'Q16',
                    'text'      => "I have discovered I was phished only after I experienced a loss or data compromise.",
                    'source'    => "Ad hoc",
                    'rationale' => "Past history of victimization - a predictor of future vulnerability",
                    'dimension' => "Psychological_Vulnerabilities",
                ],
            ],
        ],
        [
            'section_id' => 'S4',
            'name'       => "Section 4 — Metacognition and Self Assessment (Q17–Q19)",
            'items'      => [
                [
                    'id'        => 'Q17',
                    'text'      => "I believe I can easily recognize any phishing attempt.",
                    'source'    => "Ad hoc / HAIS-Q 7.3 (Reverse)",
                    'rationale' => "Overconfidence measure - predictor of underestimation of personal risk",
                    'dimension' => "Metacognitive_Awareness",
                ],
                [
                    'id'        => 'Q18',
                    'text'      => "I consider myself generally cautious and careful when handling emails.",
                    'source'    => "SeBIS 1",
                    'rationale' => "General self-assessment of caution",
                    'dimension' => "Metacognitive_Awareness",
                ],
                [
                    'id'        => 'Q19',
                    'text'      => "I know advanced email authentication techniques (if you don't know, select <em>Not at all agree</em>, or <em>I don't know / Not applicable</em>).",
                    'source'    => "Ad hoc - Overclaiming Control",
                    'rationale' => "Control item for overclaiming (non-existent/very specific technique)",
                    'dimension' => "Bias_Control",
                ],
                [
                    'id'        => 'AC2',
                    'text'      => "Please select <em>Not at all agree</em> for this item.",
                    'answer_ac2'    => '1'
                ],
            ],
        ],
        [
            'section_id' => 'S5',
            'name'       => "Section 5 — Realistic Scenarios (Q20–Q25)",
            'items' => [
                [
                    'id'        => 'Q20_SCENARIO',
                    'text'      => "SCENARIO: Boss / Urgency<br>Your manager sends you an email: \"URGENT: Send the updated report now\" with a shortened link and a pressuring tone. What would you do?",
                    'source'    => "Ad hoc - Ecological Scenario",
                    'rationale' => "Realistic situational test combining urgency + authority + technical red flag",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'I contact my manager through another channel (phone/chat) before clicking',
                    'answer_2'  => 'I click the link immediately and send the report',
                    'answer_3'  => 'I ignore the email',
                ],
                [
                    'id'        => 'Q21_SCENARIO',
                    'text'      => "SCENARIO: IT / Password / Stress<br>You receive an email from 'IT Department' saying: \"Update your password within 30 minutes or your account will be suspended\" with a direct link. What would you do?",
                    'source'    => "Ad hoc - Stress + Urgency Scenario",
                    'rationale' => "Tests vulnerability under time pressure—simulates real working conditions",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'I verify with IT through an official channel before using the link',
                    'answer_2'  => 'I click and update my password via the provided link',
                    'answer_3'  => 'I do not click immediately; I report it and then verify',
                ],
                [
                    'id'        => 'Q22_SCENARIO',
                    'text'      => "SCENARIO: Colleague / Attachment<br>A colleague sends you an attachment: 'Financial_Report_2024.xls' with the message: \"Open it now and let me know.\" How would you react?",
                    'source'    => "Ad hoc - Attachment + Familiarity Scenario",
                    'rationale' => "Tests the balance between trust (known colleague) and warning signals (different tone, unknown extension)",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'I verify with the colleague by phone/chat before opening the file',
                    'answer_2'  => 'I open the attachment immediately',
                    'answer_3'  => 'I report the email to IT for checking',
                ],
                [
                    'id'        => 'Q23_SCENARIO',
                    'text'      => "SCENARIO: Mobile / Smishing<br>You get an SMS: \"You won a €200 voucher on Amazon. Click here to claim: bit.ly/xxx.\" How would you react?",
                    'source'    => "Ad hoc - Attachment + Familiarity Scenario",
                    'rationale' => "Tests the balance between trust and warning signals",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'I delete the SMS and check via the official app or website without using the link',
                    'answer_2'  => 'I click the link in the SMS',
                    'answer_3'  => 'I open the official app to verify the offer',
                ],
                [
                    'id'        => 'Q24_SCENARIO',
                    'text'      => "SCENARIO: Svishing (Phone)<br>You receive a call claiming to be from your bank asking you to confirm OTP codes to stop suspicious transactions. How would you react?",
                    'source'    => "Ad hoc - Attachment + Familiarity Scenario",
                    'rationale' => "Tests the balance between trust and warning signals",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'I do not provide any codes; I call the bank back using the official number',
                    'answer_2'  => 'I give the codes to the caller to resolve the issue immediately',
                    'answer_3'  => 'I hang up and report the call to the bank',
                ],
                [
                    'id'        => 'Q25_SCENARIO',
                    'text'      => "SCENARIO: Offer / Curiosity<br>You receive an email: \"Take a short survey and receive a free gadget. Open the attached file and respond.\" How would you react?",
                    'source'    => "Ad hoc - Attachment + Familiarity Scenario",
                    'rationale' => "Tests the balance between trust and warning signals",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'I ignore the offer or verify via official channels before opening attachments',
                    'answer_2'  => 'I open the attachment and respond immediately',
                    'answer_3'  => 'I mark the email as suspicious and ask for confirmation',
                ],
            ],
            'points'  => [
                'answer_1' => 5,
                'answer_2' => 1,
                'answer_3' => 3,
            ],
        ],
    ],

    'scoring' => [
        'reverse_items' => ['Q7','Q11','Q12','Q13','Q14','Q15','Q16','Q17'],
        'control_items' => ['Q4','Q19'],
        'dimensions'    => [
            'TECH_SCORE'            => ['Q1','Q2','Q3','Q5'],
            'BEH_SCORE'             => ['Q6','Q7','Q8','Q9','Q10','Q11'],
            'PSY_SCORE'             => ['Q12','Q13','Q14','Q15','Q16'],
            'META_SCORE'            => ['Q17','Q18'],
            'SCENARIO_RISK_SCORE'   => ['Q20_SCENARIO','Q21_SCENARIO','Q22_SCENARIO','Q23_SCENARIO','Q24_SCENARIO','Q25_SCENARIO'],
        ],
        'thresholds'    => [
            'TECH_SCORE'            => 4,
            'BEH_SCORE'             => 6,
            'PSY_SCORE'             => 5,
            'META_SCORE'            => 3,
            'SCENARIO_RISK_SCORE'   => 6,
        ],
    ],

    'scenario_types' => [
        'Q20_SCENARIO' => 'Boss',
        'Q21_SCENARIO' => 'IT',
        'Q22_SCENARIO' => 'Colleague',
        'Q23_SCENARIO' => 'Smishing',
        'Q24_SCENARIO' => 'Svishing',
        'Q25_SCENARIO' => 'Offer',
    ],
    'scenario_priority' => ['IT','Boss','Svishing','Smishing','Colleague','Offer'],

    'training_mapping' => [
        'Technical_Competence' => [
            'Module 1: Recognizing Phishing Signals',
            'Module 2: Link Verification and URL Analysis',
            'Module 3: Sender Identity Verification',
        ],
        'Security_Behaviors' => [
            'Module 4: Developing Protective Habits',
            'Module 5: Multi-Channel Verification',
            'Module 6: Mindful Reading',
        ],
        'Psychological_Vulnerabilities' => [
            'Module 7: Social Engineering Management',
            'Module 8: Resistance to Authority Tactics',
            'Module 9: Decision-Making Under Stress',
        ],
        'Metacognitive_Awareness' => [
            'Module 10: Overconfidence and Self-Assessment',
            'Module 11: Metacognition and Bias Awareness',
        ],
        'Behavioral_Intentions' => [
            'Module 12: Real-World Scenarios',
            'Module 13: Risk Assessment and Awareness',
        ],
    ],

    'total_possible' => [
        25, // Technical_Competence
        30, // Security_Behaviors
        25, // Psychological_Vulnerabilities
        15, // Metacognitive_Awareness
        30, // Behavioral_Intentions
    ],
];
