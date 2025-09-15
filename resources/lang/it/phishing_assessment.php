<?php
return [
    'title'        => 'Questionario Ottimizzato sulla Vulnerabilità al Phishing',
    'version'      => 'v3.4',
    'control_question' => 'Questo elemento è un controllo di qualità, per favore selezionare ',
    'scale'        => [
        '1' => 'Per nulla d’accordo',
        '2' => 'Poco d’accordo',
        '3' => 'Neutrale / Incerto',
        '4' => 'Abbastanza d’accordo',
        '5' => 'Completamente d’accordo',
    ],
    'compileError' => 'Per favore, rispondi a tutte le domande!',
    'instructions' => "Scala Likert utilizzata per tutti gli item (eccetto scenari e attention checks):<br>1 = Per nulla d’accordo, 2 = Poco d’accordo, 3 = Neutrale / Incerto, 4 = Abbastanza d’accordo, 5 = Completamente d’accordo.<br>
    Se non conosci un termine tecnico (es. SPF, DKIM) o non sei sicuro, seleziona 1 = Per nulla d’accordo.<br>Sono presenti 2 attention checks. Fallirli può portare all'esclusione dell'osservazione o alla richiesta di ripetere il questionario.<br>Compilare con attenzione e, se possibile, rispondere rapidamente ma onestamente.",
    'previous'     => 'Precedente',
    'next'         => 'Successiva',
    'submit'       => 'Invia',
    'too_fast_title'   => 'Compila le domande con calma!',
    'too_fast_heading' => 'Stai andando troppo veloce!',
    'too_fast_body'    => "Per favore rallenta e leggi con attenzione ogni domanda prima di selezionare una risposta.<br>Le tue risposte ponderate sono importanti per noi.",
    'close'            => 'Chiudi',
    'already_answered'        => 'Hai già risposto',
    'completed_successfully'  => 'Questionario sulla vulnerabilità al phishing completato con successo!',
    'overclaiming_answer' => 'Non so / Non applicabile',
    'risk_levels' => [
        'very_low' => 'Molto Basso',
        'low'      => 'Basso => Buone competenze e comportamenti protettivi',
        'medium'   => 'Medio => Alcune vulnerabilità, training mirato necessario',
        'high'     => 'Alto  => Alte probabilità di cadere in phishing',
        'very_high'=> 'Molto Alto',
    ],
    'sections'     => [
        [
            'section_id' => 'S0',
            'name'       => "Introduzione",
            'items'      => [
                [
                    'id'        => 'INTRO',
                    'text'      => "Il presente questionario valuta la vulnerabilità individuale al phishing (email fraudolente), smishing (SMS) e svishing (telefonate).<br>Lo scopo è generare un profilo di rischio personalizzato e suggerire interventi formativi mirati.<br>Durata stimata: 8–12 minuti.<br>I dati saranno trattati in forma anonima e aggregata.",
                ],
            ],
        ],
        [
            'section_id' => 'S1',
            'name'       => "Sezione 1 — Conoscenze e Competenze Tecniche (Q1–Q5)",
            'items'      => [
                [
                    'id'        => 'Q1',
                    'text'      => "Riesco a riconoscere i segnali comuni di phishing in un'email.",
                    'source'    => "Ad hoc / HAIS-Q 2.7",
                    'rationale' => "Misura conoscenza diretta dei segnali di phishing - predittore chiave per identificazione",
                    'dimension' => "Technical_Competence",
                ],
                [
                    'id'        => 'Q2',
                    'text'      => "So come verificare se un link in un'email è sicuro prima di cliccarci.",
                    'source'    => "Ad hoc / HAIS-Q 2.8",
                    'rationale' => "Competenza tecnica specifica - azione concreta per prevenzione phishing",
                    'dimension' => "Technical_Competence",
                ],
                [
                    'id'        => 'Q3',
                    'text'      => "So distinguere un indirizzo email autentico da uno falsificato.",
                    'source'    => "Ad hoc / HAIS-Q 2.6",
                    'rationale' => "Competenza discriminativa critica per email spoofing",
                    'dimension' => "Technical_Competence",
                ],
                [
                    'id'        => 'Q4',
                    'text'      => "Conosco protocolli di autenticazione email come SPF o DKIM (se non lo sai, seleziona <em>Per nulla d’accordo</em> oppure <em>Non so / Non applicabile</em>).",
                    'source'    => "Ad hoc - Controllo Overclaiming",
                    'rationale' => "Item di controllo per identificare overconfidence/social desirability bias",
                    'dimension' => "Technical_Competence",
                ],
                [
                    'id'        => 'Q5',
                    'text'      => "So come e a chi segnalare un'email sospetta nella mia azienda/organizzazione.",
                    'source'    => "HAIS-Q 3.1 / SeBIS 14 (Adapted)",
                    'rationale' => "Conoscenza procedurale per response organizational corretta",
                    'dimension' => "Technical_Competence",
                ],
            ],
        ],
        [
            'section_id' => 'S2',
            'name'       => "Sezione 2 — Comportamenti e Abitudini di Sicurezza (Q6–Q11)",
            'items'      => [
                [
                    'id'        => 'Q6',
                    'text'      => "Apro email solo quando riconosco chiaramente mittente e oggetto.",
                    'source'    => "HAIS-Q 2.4 / SeBIS 12 (Reverse)",
                    'rationale' => "Comportamento preventivo diretto - misura cautela nell'apertura email",
                    'dimension' => "Security_Behaviors",
                ],
                [
                    'id'        => 'Q7',
                    'text'      => "A volte clicco su link in email senza verificarne prima l'affidabilità.",
                    'source'    => "SeBIS 15 (Adapted)",
                    'rationale' => "Comportamento di verifica - azione protettiva specifica per phishing",
                    'dimension' => "Security_Behaviors",
                ],
                [
                    'id'        => 'Q8',
                    'text'      => "Se un'email mi sembra sospetta, la verifico usando un altro canale (telefono/chat aziendale, ecc.).",
                    'source'    => "HAIS-Q 2.10 / SeBIS 8 (Adapted)",
                    'rationale' => "Multi-channel verification - gold standard per prevenzione social engineering",
                    'dimension' => "Security_Behaviors",
                ],
                [
                    'id'        => 'Q9',
                    'text'      => "Aggiorno regolarmente le mie password e uso pratiche di password sicure.",
                    'source'    => "SeBIS 9 / HAIS-Q 1.6",
                    'rationale' => "Comportamento di maintenance security generale",
                    'dimension' => "Security_Behaviors",
                ],
                [
                    'id'        => 'Q10',
                    'text'      => "Leggo attentamente il contenuto delle email prima di compiere azioni richieste.",
                    'source'    => "SeBIS 12",
                    'rationale' => "Comportamento di attenzione deliberata - contrasta impulsività",
                    'dimension' => "Security_Behaviors",
                ],
                [
                    'id'        => 'Q11',
                    'text'      => "Mi capita di aprire o cliccare email con offerte o vincite inattese per curiosità.",
                    'source'    => "Ad hoc",
                    'rationale' => "Phishing basato su curiosità/guadagno è molto comune",
                    'dimension' => "Security_Behaviors",
                ],
                [
                    'id'        => 'AC1',
                    'text'      => "Per favore, seleziona <em>Abbastanza d’accordo</em> per questa domanda.",
                    'answer_ac1'    => '4'
                ],
            ],
        ],
        [
            'section_id' => 'S3',
            'name'       => "Sezione 3 — Vulnerabilità Psicologiche (Q12–Q16)",
            'items'      => [
                [
                    'id'        => 'Q12',
                    'text'      => "Rispondo rapidamente ad email che comunicano urgenza, senza riflettere.",
                    'source'    => "Ad hoc (Urgenza/Social Engineering)",
                    'rationale' => "Vulnerabilità a pressure/urgency tactics - predittore forte per social engineering",
                    'dimension' => "Psychological_Vulnerabilities",
                ],
                [
                    'id'        => 'Q13',
                    'text'      => "Tendo a fidarmi di richieste di dati quando sembrano provenire da una fonte ufficiale.",
                    'source'    => "Ad hoc (Overtrust/Authority)",
                    'rationale' => "Vulnerabilità ad authority-based social engineering",
                    'dimension' => "Psychological_Vulnerabilities",
                ],
                [
                    'id'        => 'Q14',
                    'text'      => "Quando sono sotto stress, presto meno attenzione ai dettagli nelle email.",
                    'source'    => "Ad hoc (Cognitive Vulnerability)",
                    'rationale' => "Vulnerabilità cognitiva sotto stress - predittore per errori sotto pressure",
                    'dimension' => "Psychological_Vulnerabilities",
                ],
                [
                    'id'        => 'Q15',
                    'text'      => "Tendo a fidarmi di messaggi che includono nomi o riferimenti noti anche se sono inaspettati.",
                    'source'    => "Ad hoc (Spear Phishing)",
                    'rationale' => "Vulnerabilità a spear phishing e name spoofing",
                    'dimension' => "Psychological_Vulnerabilities",
                ],
                [
                    'id'        => 'Q16',
                    'text'      => "Ho scoperto di essere stato vittima di phishing solo dopo aver subito un danno o una perdita di dati.",
                    'source'    => "Ad hoc",
                    'rationale' => "Storia passata di vittimizzazione - predittore per vulnerabilità futura",
                    'dimension' => "Psychological_Vulnerabilities",
                ],
            ],
        ],
        [
            'section_id' => 'S4',
            'name'       => "Sezione 4 — Metacognizione e Autovalutazione (Q17–Q19)",
            'items'      => [
                [
                    'id'        => 'Q17',
                    'text'      => "Credo di saper riconoscere facilmente qualsiasi tentativo di phishing.",
                    'source'    => "Ad hoc / HAIS-Q 7.3 (Reverse)",
                    'rationale' => "Misura overconfidence - predittore per sottostima del rischio personale",
                    'dimension' => "Metacognitive_Awareness",
                ],
                [
                    'id'        => 'Q18',
                    'text'      => "Mi considero generalmente prudente e attento quando gestisco le email.",
                    'source'    => "SeBIS 1",
                    'rationale' => "Self-assessment generale di cautela",
                    'dimension' => "Metacognitive_Awareness",
                ],
                [
                    'id'        => 'Q19',
                    'text'      => "Conosco tecniche avanzate di autenticazione email (se non lo sai, seleziona <em>Per nulla d’accordo</em> oppure <em>Non so / Non applicabile</em>).",
                    'source'    => "Ad hoc - Controllo Overclaiming",
                    'rationale' => "Item di controllo per overclaiming (tecnica inesistente/molto specifica)",
                    'dimension' => "Metacognitive_Awareness",
                ],
                [
                    'id'        => 'AC2',
                    'text'      => "Per favore, seleziona <em>Per nulla d’accordo</em> per questa domanda.",
                    'answer_ac2'    => '1'
                ],
            ],
        ],
        [
            'section_id' => 'S5',
            'name'       => "Sezione 5 — Scenari Realistici (Q20–Q25)",
            'items'      => [
                [
                    'id'        => 'Q20_SCENARIO',
                    'text'      => "SCENARIO: Capo / Urgenza<br>Il tuo capo ti invia un’email: \"URGENTE: Invia subito il report aggiornato\" con link abbreviato e tono pressante. Cosa faresti?",
                    'source'    => "Ad hoc - Scenario Ecologico",
                    'rationale' => "Test situazionale realistico che combina urgency + authority + technical red flag",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'Contatto il capo tramite un altro canale prima di cliccare',
                    'answer_2'  => 'Clicco immediatamente sul link e invio il report',
                    'answer_3'  => 'Ignoro l’email',
                ],
                [
                    'id'        => 'Q21_SCENARIO',
                    'text'      => "SCENARIO: IT / Password / Stress<br>Ricevi un'email dal 'Reparto IT': \"Aggiorna la password entro 30 minuti o il tuo account verrà sospeso\" con link diretto. Cosa faresti?",
                    'source'    => "Ad hoc - Scenario Stress + Urgency",
                    'rationale' => "Test vulnerabilità sotto stress temporale - simula condizioni reali di lavoro",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'Verifico con IT tramite canale ufficiale prima di usare il link',
                    'answer_2'  => 'Clicco e aggiorno la password tramite il link fornito',
                    'answer_3'  => 'Non clicco subito; segnalo e poi verifico',
                ],
                [
                    'id'        => 'Q22_SCENARIO',
                    'text'      => "SCENARIO: Collega / Allegato<br>Un collega ti manda 'Report_Finanziario_2024.xls' con testo: \"Aprilo subito e fammi sapere.\" Come reagiresti?",
                    'source'    => "Ad hoc - Scenario Attachment + Familiarity",
                    'rationale' => "Test bilanciamento tra fiducia (collega noto) e segnali di warning (tono diverso, estensione sconosciuta)",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'Verifico con il collega tramite telefono/chat prima di aprire',
                    'answer_2'  => "Apro subito l'allegato",
                    'answer_3'  => "Segnalo l'email all'IT per controllo",
                ],
                [
                    'id'        => 'Q23_SCENARIO',
                    'text'      => "SCENARIO: Mobile / Smishing<br>Ricevi un SMS: \"Hai vinto un buono da 200€ su Amazon. Clicca qui: bit.ly/xxx\". Come reagiresti?",
                    'source'    => "Ad hoc - Scenario Attachment + Familiarity",
                    'rationale' => "Test bilanciamento tra fiducia (collega noto) e segnali di warning (tono diverso, estensione sconosciuta)",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'Cancello l’SMS e verifico tramite app/sito ufficiale senza usare il link',
                    'answer_2'  => 'Clicco il link nello SMS',
                    'answer_3'  => "Apro l’app ufficiale per verificare l'offerta",
                ],
                [
                    'id'        => 'Q24_SCENARIO',
                    'text'      => "SCENARIO: Svishing (Telefono)<br>Ti chiamano dicendo di essere della banca e ti chiedono i codici OTP. Come reagiresti?",
                    'source'    => "Ad hoc - Scenario Attachment + Familiarity",
                    'rationale' => "Test bilanciamento tra fiducia (collega noto) e segnali di warning (tono diverso, estensione sconosciuta)",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'Non fornisco dati; richiamo la banca al numero ufficiale',
                    'answer_2'  => "Fornisco i codici all'operatore",
                    'answer_3'  => 'Riattacco e segnalo la chiamata alla banca',
                ],
                [
                    'id'        => 'Q25_SCENARIO',
                    'text'      => "SCENARIO: Offerta / Curiosità<br>Ricevi un'email: \"Partecipa a un sondaggio e ricevi un gadget. Apri l'allegato e rispondi.\" Come reagiresti?",
                    'source'    => "Ad hoc - Scenario Attachment + Familiarity",
                    'rationale' => "Test bilanciamento tra fiducia (collega noto) e segnali di warning (tono diverso, estensione sconosciuta)",
                    'dimension' => "Behavioral_Intentions",
                    'answer_1'  => 'Ignoro o verifico tramite canali ufficiali prima di aprire allegati',
                    'answer_2'  => "Apro l'allegato e rispondo subito",
                    'answer_3'  => "Segnalo l’email come sospetta e chiedo conferma",
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
        'Q20_SCENARIO' => 'Capo',
        'Q21_SCENARIO' => 'IT',
        'Q22_SCENARIO' => 'Collega',
        'Q23_SCENARIO' => 'Smishing',
        'Q24_SCENARIO' => 'Svishing',
        'Q25_SCENARIO' => 'Offerta',
    ],
    'scenario_priority' => ['IT','Capo','Svishing','Smishing','Collega','Offerta'],

    'training_mapping' => [
        'Technical_Competence' => [
            'Modulo 1: Riconoscimento Segnali di Phishing',
            'Modulo 2: Controllo dei Link e Analisi URL',
            'Modulo 3: Verifica Identità Mittenti'
        ],
        'Security_Behaviors' => [
            'Modulo 4: Sviluppo Abitudini Protettive',
            'Modulo 5: Verifica Multi-Canale',
            'Modulo 6: Attenzione e Mindful Reading'
        ],
        'Psychological_Vulnerabilities' => [
            'Modulo 7: Gestione Email Urgenti e Social Engineering',
            'Modulo 8: Resistenza a Tattiche di Autorità',
            'Modulo 9: Decision Making sotto Stress'
        ],
        'Metacognitive_Awareness' => [
            'Modulo 10: Overconfidence e Autovalutazione',
            'Modulo 11: Metacognizione e Bias Awareness'
        ],
        'Behavioral_Intentions' => [
            'Modulo 12: Decision Making in Scenari Reali',
            'Modulo 13: Risk Assessment e Situational Awareness'
        ]
    ],

    'total_possible' => [
        25, // Technical_Competence
        30, // Security_Behaviors
        25, // Psychological_Vulnerabilities
        15, // Metacognitive_Awareness
        30, // Behavioral_Intentions
    ],
];
