<x-app-layout>
    <x-slot name="slot">
        @php($failed = isset($training) && $training->generation_failed_at !== null)
        <div class="min-h-screen text-2xl bg-gray-200 flex justify-center items-center" style="flex-direction: column">
            <p class="flex font-bold">⚙️ Training Status: </p>

            <div id="training-generating" @if($failed) style="display: none" @endif>
                <p style="margin: 3%">You will shortly have to attend a brief training course as part of this study. <br>
                    However, we're still generating the training material for you...<br>
                    This page will update automatically when it is ready. Thank you for your patience.</p>
            </div>

            <div id="training-failed" @unless($failed) style="display: none" @endunless>
                <p style="margin: 3%">Sorry, something went wrong while generating your training material.<br>
                    Please try again. If the problem persists, contact the researchers.</p>
                <form method="POST" action="{{ route('training.retry') }}" class="flex justify-center">
                    @csrf
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full">
                        Try again
                    </button>
                </form>
            </div>
        </div>

        <script>
            // Poll the generation status every 5 s: reload when ready, show the retry button on failure.
            (function () {
                const statusUrl = @json(route('training.status'));
                const generating = document.getElementById('training-generating');
                const failed = document.getElementById('training-failed');

                async function poll() {
                    try {
                        const res = await fetch(statusUrl, {
                            headers: {'Accept': 'application/json'},
                            credentials: 'same-origin',
                            cache: 'no-store',
                        });
                        if (res.ok) {
                            const status = await res.json();
                            if (status.generated) {
                                window.location.reload();
                                return;
                            }
                            generating.style.display = status.failed ? 'none' : '';
                            failed.style.display = status.failed ? '' : 'none';
                            if (status.failed) {
                                return;  // stop polling until the participant presses "Try again"
                            }
                        }
                    } catch (e) {
                        // Network hiccup: keep polling.
                    }
                    setTimeout(poll, 5000);
                }

                if (failed.style.display === 'none') {
                    setTimeout(poll, 5000);
                }
            })();
        </script>
    </x-slot>
</x-app-layout>
