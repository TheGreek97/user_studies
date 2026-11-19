<?php

namespace App\Providers;

use App\Services\Llm\AnthropicClient;
use App\Services\Llm\LlmClient;
use App\Services\Llm\OpenAiClient;
use App\Services\Tutor\DemoLlmClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Training generation LLM, chosen by TRAINING_LLM_PROVIDER (default "openai" = paper setup).
        $this->app->bind(LlmClient::class, function () {
            $timeout = (int) config('services.llm.timeout', 180);

            if (config('services.llm.training_provider') === 'anthropic') {
                return new AnthropicClient(
                    apiKey: (string) config('services.anthropic.key'),
                    model: (string) config('services.anthropic.model', 'claude-sonnet-5-5'),
                    effort: (string) config('services.anthropic.effort', 'medium'),
                    timeout: $timeout,
                    maxTokens: (int) config('services.anthropic.max_tokens', 16000),
                    maxRetries: (int) config('services.anthropic.max_retries', 2),
                );
            }

            return new OpenAiClient(
                apiKey: (string) config('services.openai.key'),
                model: (string) config('services.openai.model', 'o3-mini'),
                reasoningEffort: (string) config('services.openai.reasoning_effort', 'medium'),
                timeout: $timeout,
            );
        });

        // Module 4 tutor LLM (always Claude). No SDK retries: the tutor does its own single retry and then
        // falls back deterministically, so SDK retries would multiply the chat latency (up to ~3x the timeout).
        $this->app->bind('tutor.llm', function ($app) {
            if (self::fakeTutorEnabled($app)) {
                return new DemoLlmClient(delayMs: 700);  // local demo: no network, no cost
            }

            return new AnthropicClient(
                apiKey: (string) config('services.anthropic.key'),
                model: (string) config('tutor.model'),
                effort: (string) config('tutor.effort', 'low'),
                timeout: (int) ceil(config('tutor.timeout_ms', 20000) / 1000),
                maxTokens: (int) config('tutor.max_tokens', 2000),
                maxRetries: 0,
            );
        });
    }

    /**
     * Offline demo tutor: only when TUTOR_FAKE=true AND the app environment is exactly "local".
     * In any other environment (production, testing, staging, ...) the flag is ignored.
     */
    public static function fakeTutorEnabled(Application $app): bool
    {
        return $app->environment('local') && config('tutor.fake') === true;
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
