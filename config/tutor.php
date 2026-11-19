<?php

return [
    /*
    | Module 4 tutor (app/Services/Tutor). Materials are read at runtime: emails-m4.json,
    | tutor-system-prompt.md, tutor-output-schema.json, test-dialogs.md.
    */
    'materials_dir' => env('TUTOR_MATERIALS_DIR', resource_path('tutor')),

    // Per-call timeout of a tutor turn (ms); one retry, then the deterministic fallback (cap4 4.4.5).
    'timeout_ms' => (int) env('TUTOR_TIMEOUT_MS', 20000),

    // Tutor client (container binding "tutor.llm"): Claude, low effort for chat latency, no SDK retries.
    'model' => env('TUTOR_MODEL', env('ANTHROPIC_MODEL', 'claude-sonnet-5-5')),
    'effort' => env('TUTOR_EFFORT', 'low'),
    'max_tokens' => (int) env('TUTOR_MAX_TOKENS', 2000),

    // Local demo only: offline DemoLlmClient instead of Claude. Ignored unless APP_ENV=local (AppServiceProvider).
    'fake' => filter_var(env('TUTOR_FAKE', false), FILTER_VALIDATE_BOOLEAN),

    // TutorChat (Livewire): user messages sent to the tutor per user per minute; over the limit no LLM call is made.
    'rate_limit_per_minute' => (int) env('TUTOR_RATE_LIMIT_PER_MINUTE', 20),

    // Maximum length (characters) of one user message to the tutor.
    'max_message_chars' => (int) env('TUTOR_MAX_MESSAGE_CHARS', 1000),

    // Per-user lock while a reply is being processed (s): covers 2 turns x 2 attempts x timeout.
    'lock_seconds' => (int) env('TUTOR_LOCK_SECONDS', 120),

    // Module 5 personal rules (PersonalRules, prompt 5b): total characters of the 2-3 rules, and submissions per
    // user per minute (the comment itself is one call per user at most, whatever the limit).
    'rules_max_chars' => (int) env('TUTOR_RULES_MAX_CHARS', 1000),
    'rules_rate_limit_per_minute' => (int) env('TUTOR_RULES_RATE_LIMIT_PER_MINUTE', 6),
];
