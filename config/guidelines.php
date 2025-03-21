<?php

$guidelines = [
    // Big 5
    "Open mindedness" => [
        "Communication Style" => [
            "High" => "Use rational, analytical, and exploratory language, stimulating the user's intellectual curiosity.",
            "Low" => "Use straightforward, practical, and concrete language with clear guidelines."
        ],
        "Learning Content" => [
            "High" => "Cover the psychological principles exploited by attackers, technical indicators of phishing, and sophisticated defense strategies.",
            "Low" => "Focus on fundamental phishing techniques and defense strategies, clear rules, and step-by-step procedures to identify phishing emails."
        ],
        "Phishing Scenario" => [
            "High" => "Use sophisticated attacks with subtle deception, requiring deep analysis.",
            "Low" => "Use common phishing attempts with clear and established red flags."
        ]
    ],
    "Conscientiousness" => [
        "Communication Style" => [
            "High" => "Provide structured, sequential, systematic information with clear protocols and checklists based on best practices. Appeal to the user's sense of responsibility by emphasizing the importance of security.",
            "Low" => "Use an engaging and dynamic communication format."
        ],
        "Learning Content" => [
            "High" => "Include thorough email verification steps and reporting protocols. Also emphasize the consequences of security breaches to reinforce the user's commitment to protective behaviors.",
            "Low" => "Focus on quick, easy-to-apply detection methods and bite-sized modules. Provide a simplified decision framework."
        ],
        "Phishing Scenario" => [
            "High" => "Use subtle details requiring methodical examination (e.g., minor URL deviations).",
            "Low" => "Highlight immediate consequences, using clear red flags and simple indicators."
        ]
    ],
    "Extraversion" => [
        "Communication Style" => [
            "High" => "Utilize a lively and conversational style to leverage the user's social nature.",
            "Low" => "Use a measured and reflective communication style."
        ],
        "Learning Content" => [
            "High" => "Address social engineering tactics, social proof, and manipulation strategies.",
            "Low" => "Focus on technical phishing detection (e.g., email headers, digital signatures)."
        ],
        "Phishing Scenario" => [
            "High" => "Show phishing through impersonation, urgency, and authority-based deception.",
            "Low" => "Use technically sophisticated deception (e.g., spoofed domains, disguised attachments)."
        ]
    ],
    "Agreeableness" => [
        "Communication Style" => [
            "High" => "Empathetic but direct about the manipulative nature of phishing attacks. Emphasize security as a way to protect others.",
            "Low" => "Use direct, technical and analytical communication that reinforces the user's natural skepticism."
        ],
        "Learning Content" => [
            "High" => "Explain how attackers exploit trust, emphasizing verification processes.",
            "Low" => "Focus on technical deception detection and systematic verification procedures. Emphasize analytical skills for identifying deception and provide technical criteria for evaluating communications."
        ],
        "Phishing Scenario" => [
            "High" => "Use impersonation-based attacks (e.g., fake requests from trusted figures).",
            "Low" => "Use sophisticated fraud techniques rather than simple trust exploitation, requiring both skepticism and technical analysis."
        ]
    ],
    "Negative emotionality" => [
        "Communication Style" => [
            "High" => "Use reassuring and supportive messaging focused on empowerment rather than fear and alarmist language, which may induce anxiety.",
            "Low" => "Straightforward and matter-of-fact, providing comprehensive insights into risks and realistic consequence."
        ],
        "Learning Content" => [
            "High" => "Provide clear and confidence-building steps. Emphasize that even security experts occasionally encounter convincing phishing attempts, normalizing the experience of uncertainty and providing protocols for  addressing doubts.",
            "Low" => "Provide in-depth information, challenging scenarios, and advanced response training."
        ],
        "Phishing Scenario" => [
            "High" => "Start with a simple phishing attempt. Focus on developing resilience and systematic response patterns that reduce emotional reactivity.",
            "Low" => "Use high-pressure situations (e.g., time-sensitive threats) requiring composure."
        ]
    ],
    // Trait Emotional Intelligence
    "Well-being" => [
        "Communication Style" => [
            "High" => "Use confident, optimistic language that reinforces their positive outlook while encouraging cautious verification. Avoid fear-based messaging.",
            "Low" => "Supportive and non-judgmental, focusing on building confidence without increasing anxiety."
        ],
        "Learning Content" => [
            "High" => "Highlight how optimism can be exploited through too-good-to-be-true scams, emphasizing the need for verification.",
            "Low" => "Show how attackers exploit fear and insecurity while providing clear, actionable detection strategies."
        ],
        "Phishing Scenario" => [
            "High" => "Use examples of prize notifications, unexpected rewards, or flattery-based phishing.",
            "Low" => "Use real-world cases of fear-based phishing, such as urgent warnings or account suspension threats."
        ]
    ],
    "Sociability" => [
        "Communication Style" => [
            "High" => "Conversational and engaging, leveraging their social skills while teaching skepticism in digital interactions.",
            "Low" => "Clear, direct, and factual, minimizing reliance on social judgment."
        ],
        "Learning Content" => [
            "High" => "Focus on social engineering tactics such as authority exploitation, social proof, and manipulated trust.",
            "Low" => "Provide explicit, structured guidelines for detecting phishing without relying on social intuition."
        ],
        "Phishing Scenario" => [
            "High" => "Demonstrate phishing via fake colleague requests, social media impersonation, or urgent team collaboration messages.",
            "Low" => "Use examples emphasizing technical phishing indicators rather than social deception."
        ]
    ],
    "Emotionality" => [
        "Communication Style" => [
            "High" => "Acknowledge emotional responses while guiding them toward verification rather than reaction.",
            "Low" => "Straightforward and fact-based, focusing on observable phishing markers rather than emotional nuance."
        ],
        "Learning Content" => [
            "High" => "Focus on emotional manipulation tactics such as distress-based scams (charity fraud, emergency requests).",
            "Low" => "Teach recognition of common emotional manipulation tactics through structured, logical approaches."
        ],
        "Phishing Scenario" => [
            "High" => "Demonstrate how phishing emails exploit empathy, e.g., fake messages from distressed friends or fraudulent charities.",
            "Low" => "Use explicit, rule-based examples of urgent requests that demand logical verification rather than emotional response."
        ]
    ],
    "Self-control" => [
        "Communication Style" => [
            "High" => "Respect the user's methodical approach, introducing more advanced security techniques.",
            "Low" => "Dynamic and engaging, using short, digestible content to maintain attention."
        ],
        "Learning Content" => [
            "High" => "Focus on complex phishing tactics requiring deep analysis, emphasizing long-term vigilance.",
            "Low" => "Emphasize resisting urgency tactics, using simple decision frameworks for quick but effective security responses."
        ],
        "Phishing Scenario" => [
            "High" => "Use advanced phishing cases that bypass standard verification steps, requiring nuanced decision-making.",
            "Low" => "Use phishing examples with artificial time pressure, teaching pause-and-verify techniques."
        ]
    ],
    // Susceptibility to persuasion
    "Attitudes toward advertising" => [
        "Communication Style" => [
            "High" => "Positive and engaging, reinforcing their openness while introducing skepticism.",
            "Low" => "Acknowledge their skepticism while expanding awareness of non-advertising phishing threats."
        ],
        "Learning Content" => [
            "High" => "Highlight key differences between legitimate marketing and phishing, with side-by-side comparisons.",
            "Low" => "Emphasize phishing attempts disguised as service notifications, security alerts, and invoices."
        ],
        "Phishing Scenario" => [
            "High" => "Simulated phishing attempts mimicking promotional emails, with subtle inconsistencies in branding or sender details.",
            "Low" => "Scenarios featuring phishing disguised as account warnings or IT security updates."
        ]
    ],
    "Social influence" => [
        "Communication Style" => [
            "High" => "Adopt a clear, authoritative tone that emphasizes social norms around security behaviors. ",
            "Low" => "Direct, individual-focused, emphasizing personal security over social responsibility."
        ],
        "Learning Content" => [
            "High" => "Focus on social engineering techniques that exploit social validation (authority exploitation, social proof, urgency, fabricated consequences).",
            "Low" => "Highlight phishing tactics that rely on technical exploits rather than social manipulation."
        ],
        "Phishing Scenario" => [
            "High" => "Phishing email  that appear to come from executives requesting urgent action, messages that suggest \"everyone else\" has already complied with a request, or communications that leverage shared interests to establish rapport before making requests.",
            "Low" => "Spear-phishing attempts targeting personal credentials without social engineering tactics."
        ]
    ],
    "Need for uniqueness" => [
        "Communication Style" => [
            "High" => "Emphasize security as a way to protect the user's unique digital identity.",
            "Low" => "Present standardized security procedures as reliable and essential."
        ],
        "Learning Content" => [
            "High" => "Highlight phishing tactics offering “exclusive” deals or personalized opportunities",
            "Low" => "Reinforce routine security practices while addressing personalized phishing attacks."
        ],
        "Phishing Scenario" => [
            "High" => "Phishing emails targeting individuals with “special access” or \"VIP\" opportunities.",
            "Low" => "Phishing disguised as standard company-wide notifications, reinforcing structured security habits."
        ]
    ],
    "Sensation seeking" => [
        "Communication Style" => [
            "High" => "Dynamic and engaging, positioning security as an evolving challenge.",
            "Low" => "Emphasize stability and predictability in security practices."
        ],
        "Learning Content" => [
            "High" => "Teach how novelty-seeking behavior can be exploited by enticing phishing tactics. Emphasize how phishers exploit curiosity and the desire for  novel experiences of the user.",
            "Low" => "Reinforce habitual security behaviors while ensuring adaptability to new threats."
        ],
        "Phishing Scenario" => [
            "High" => "Phishing lures featuring exciting investment opportunities, unexpected prizes, or unusual requests.",
            "Low" => "Phishing attempt with minor deviations from routine communications, testing their ability to notice subtle changes."
        ]
    ],
    "Risk tolerance" => [
        "Communication Style" => [
            "High" => "Present security as a rational risk-reward balance, using data-driven messaging.",
            "Low" => "Reassuring and clear, emphasizing security as a way to maintain control."
        ],
        "Learning Content" => [
            "High" => "Show how phishing manipulates risk-taking tendencies by masking potential downsides.",
            "Low" => "Expand decision frameworks while ensuring efficiency in urgent situations."
        ],
        "Phishing Scenario" => [
            "High" => "Phishing emails creating artificial urgency, with forced delays to simulate a pause-before-action habit.",
            "Low" => "Time-sensitive phishing simulations requiring quick but structured decision-making."
        ]
    ],
    "Lack of premeditation" => [
        "Communication Style" => [
            "High" => "Direct and action-oriented, promoting quick but thoughtful verification.",
            "Low" => "Comprehensive and detailed, reinforcing deliberate security analysis."
        ],
        "Learning Content" => [
            "High" => "Teach automatic verification habits to interrupt impulsive responses.",
            "Low" => "Expand decision frameworks while ensuring efficiency in urgent situations."
        ],
        "Phishing Scenario" => [
            "High" => "Phishing emails creating artificial urgency, with forced delays to simulate a pause-before-action habit.",
            "Low" => "Time-sensitive phishing simulations requiring quick but structured decision-making."
        ]
    ],
    "Lack of self-control" => [
        "Communication Style" => [
            "High" => "Supportive and structured, breaking security behaviors into manageable steps.",
            "Low" => "Measured and comprehensive, reinforcing vigilance over time."
        ],
        "Learning Content" => [
            "High" => "Focus on environmental safeguards and external security structures.",
            "Low" => "Teach advanced threat analysis while preventing security fatigue."
        ],
        "Phishing Scenario" => [
            "High" => "Simulations including mandatory verification steps before proceeding with actions.",
            "Low" => "Long-term phishing simulations testing vigilance across multiple interactions."
        ]
    ],
    "Need for cognition" => [
        "Communication Style" => [
            "High" => "Intellectually stimulating, incorporating complex security theories with a detailed and analytical communication.",
            "Low" => "Straightforward and practical, avoiding unnecessary complexity."
        ],
        "Learning Content" => [
            "High" => "Provide in-depth analysis of phishing tactics and persuasion techniques, highlighting advanced defense strategies that require careful analysis and attention to detail. Content should challenge the user's analytical abilities while providing frameworks for systematic evaluation of emails.",
            "Low" => "Emphasize simple and quick-to-apply heuristics to recognize phishing indicators with step-by-step response procedures. Reinforce basic detection strategies."
        ],
        "Phishing Scenario" => [
            "High" => "Include sophisticated and technically convincing spoofed emails with subtle inconsistencies that require analytical thinking to identify.",
            "Low" => "Include simple phishing detection indicators that can be identified through application of simple rules rather than complex analysis."
        ]
    ],
    "Need for consistency" => [
        "Communication Style" => [
            "High" => "Frame security vigilance as aligned with their existing values and commitments.",
            "Low" => "Encourage adaptability and situational security awareness."
        ],
        "Learning Content" => [
            "High" => "Explain how phishing exploits consistency biases through incremental requests.",
            "Low" => "Teach both consistent best practices and the need for flexible responses to emerging threats."
        ],
        "Phishing Scenario" => [
            "High" => "Phishing examples using previous commitments (e.g., follow-up emails referencing past interactions).",
            "Low" => "Phishing exercises requiring adaptability, testing their ability to detect non-standard attacks."
        ]
    ],
];
