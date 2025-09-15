{{-- resources/views/training/training_show.blade.php --}}
<x-app-layout>
    @php
        // Split consolidation and CTA from conclusions (CTA after <!--CTA-->)
        [$consolidationHtml, $ctaHtml] = array_pad(
            explode('<!--CTA-->', $training->conclusions ?? '', 2),
            2,
            ''
        );
        $hasCta = trim(strip_tags($ctaHtml)) !== '';
    @endphp

    <x-slot name="slot">
        <div
            x-data="load(
                {{ $training->introduction ? 'true' : 'false' }},
                '{{ optional($training->updated_at)->toIso8601String() }}',
                {{ $timersEnabled ? 'true' : 'false' }},
                {{ $hasCta ? 'true' : 'false' }}
            )"
            x-init="init()"
            class="min-h-screen bg-gray-200 flex justify-center items-center"
        >
            <div class="p-12 m-10 bg-white md:min-w-100 rounded-2xl shadow-xl z-20 w-full md:max-w-4xl">
                <h1 class="text-3xl font-bold text-center mb-5">Anti-phishing Training</h1>

                {{-- SKELETON + POLLING --}}
                <template x-if="!isReady">
                    <div id="training-skeleton" class="py-8">
                        <div class="animate-pulse space-y-4">
                            <div class="h-6 bg-gray-300 rounded w-3/4"></div>
                            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                            <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                            <div class="h-4 bg-gray-200 rounded w-4/5"></div>
                        </div>
                        <p class="text-center text-gray-600 mt-6">Preparing your personalized content…</p>
                    </div>
                </template>

                {{-- FULL TRAINING (preface + 5 or 6 steps depending on CTA) --}}
                <template x-if="isReady">
                    <div>
                        {{-- 0. preface --}}
                        <div x-show="step === 0" id="section-preface">
                            <p class="text-justify text-xl">
                                You are now entering the central part of our study, where you will be exposed to phishing training.
                                <br>
                                Please read the entire training carefully as it is essential to the accuracy of the study results and the effectiveness of the research.
                                <br>
                                You should take approximately <b>{{ Auth::user()?->training_length == "short" ? 8 : 15 }} minutes</b> to read it at a medium/slow pace.
                                <br>
                                Your full attention will help to ensure that the training is properly assessed and that the findings of the study can be used to improve cybersecurity education.
                                <br><br>
                                Thank you for your cooperation!
                            </p>
                            <h2 class="text-l w-full text-center mt-10">Section 0 of <span x-text="num_steps"></span></h2>
                        </div>

                        {{-- 1. intro --}}
                        <div x-show="step === 1" id="section-intro">
                            <div class="text-2xl font-bold text-center mb-2"> -- Introduction -- </div>

                            {{-- Optional risk score badge --}}
                            @isset($risk_score)
                                @if(!is_null($risk_score))
                                    <div class="w-full text-center mb-4">
                                        <span class="inline-flex items-center px-3 py-1 text-sm font-semibold bg-yellow-100 text-yellow-800 rounded-full">
                                            Risk score: {{ round((float)$risk_score) }}%
                                        </span>
                                    </div>
                                @endif
                            @endisset

                            <div class="text-xl text-justify">{!! $training->introduction !!}</div>
                            <h2 class="text-l w-full text-center mt-10">Section 1 of <span x-text="num_steps"></span></h2>
                        </div>

                        {{-- 2. modules --}}
                        <div x-show="step === 2" id="section-modules">
                            <div class="text-2xl font-bold text-center mb-6"> -- Training Modules -- </div>
                            <div class="text-xl text-justify">{!! $training->defense_strategies !!}</div>
                            <h2 class="text-l w-full text-center mt-10">Section 2 of <span x-text="num_steps"></span></h2>
                        </div>

                        {{-- 3. mini-scenarios (realistic email/chat cards) --}}
                        <div x-show="step === 3" id="section-mini-scenarios">
                            <div class="text-2xl font-bold text-center mb-6"> -- Mini-Scenarios -- </div>
                            <div class="text-xl text-justify">{!! $training->scenario !!}</div>
                            <h2 class="text-l w-full text-center mt-10">Section 3 of <span x-text="num_steps"></span></h2>
                        </div>

                        {{-- 4. quiz --}}
                        <div x-show="step === 4" id="section-quiz">
                            <div class="text-2xl font-bold text-center mb-6"> -- Quiz -- </div>
                            <div class="text-xl text-justify">{!! $training->exercises !!}</div>
                            <h2 class="text-l w-full text-center mt-10">Section 4 of <span x-text="num_steps"></span></h2>
                        </div>

                        {{-- 5. consolidation --}}
                        <div x-show="step === 5" id="section-consolidation">
                            <div class="text-2xl font-bold text-center mb-6"> -- Consolidation -- </div>
                            <div class="text-xl text-justify">{!! $consolidationHtml !!}</div>
                            <h2 class="text-l w-full text-center mt-10">Section 5 of <span x-text="num_steps"></span></h2>
                        </div>

                        {{-- 6. CTA (only if present) --}}
                        <div x-show="step === 6 && hasCta" id="section-cta">
                            <div class="text-2xl font-bold text-center mb-6"> -- Next Steps -- </div>
                            <div class="text-xl text-justify">{!! $ctaHtml !!}</div>
                            <h2 class="text-l w-full text-center mt-10">Section 6 of <span x-text="num_steps"></span></h2>
                        </div>

                        {{-- NAV --}}
                        <div class="text-center mt-4">
                            <div class="flex flex-row w-full" :class="{'space-x-6' : step > 0}">
                                <div x-show="step > 1">
                                    <button type="button" id="back" @click="previous()"
                                            class="py-3 w-64 text-lg text-black bg-gray-300 hover:bg-gray-400 rounded-2xl">
                                        Previous
                                    </button>
                                </div>
                                <div x-show="step >= 0" class="flex-1"></div>
                                <div :class="{'flex-1 w-full': step === 0}">
                                    <button type="button" id="next" @click="next()"
                                            :class="{'w-full' : step === 0, 'w-64' : step > 0}"
                                            class="py-3 text-lg text-white bg-blue-500 hover:bg-blue-800 rounded-2xl">
                                        <span x-text="step === num_steps ? 'Finish' : 'Next'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Anti-skimming modal --}}
                        <div x-show="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                            <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
                                <h2 class="text-xl font-semibold">Please read the training content carefully</h2>
                                <p class="mt-2 text-gray-600">It seems you're going too fast! Please take your time before proceeding to the next section.</p>
                                <div class="mt-4 flex justify-end">
                                    <button @click="showModal = false" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700">I understand</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Alpine controller --}}
        <script>
            function load(isReadyInitial, initialUpdatedAt, timersEnabled, hasCtaFlag) {
                const POLL_URL = "{{ route('training.status') }}";
                const hasCta = (hasCtaFlag === true || hasCtaFlag === 'true');

                // num_steps is the HIGHEST step index you can reach (for the "Section X of Y" and Finish logic)
                // With preface(0) + intro(1) + modules(2) + mini(3) + quiz(4) + consolidation(5) + optional cta(6)
                let num_steps = hasCta ? 6 : 5;

                let startTime = Date.now();

                // Match your Controller's $wait_times keys (modules/mini_scenarios/quiz/consolidation/cta)
                let minTimePerStep = {
                    1: {{ $wait_times['introduction'] }},
                    2: {{ $wait_times['modules'] }},
                    3: {{ $wait_times['mini_scenarios'] }},
                    4: {{ $wait_times['quiz'] }},
                    5: {{ $wait_times['consolidation'] }},
                    6: {{ $wait_times['cta'] }},
                };
                if (!hasCta) { delete minTimePerStep[6]; }

                return {
                    isReady: isReadyInitial === true || isReadyInitial === 'true',
                    lastUpdatedAt: initialUpdatedAt,
                    timersEnabled: (timersEnabled === true || timersEnabled === 'true'),
                    hasCta,
                    step: 0,
                    canProceed: 1,
                    showModal: false,
                    num_steps,

                    init() {
                        if (!this.isReady) this.startPolling();
                        else this.setTimer();
                        setTimeout(()=>{ if (typeof window.enhanceMcqsUnified==='function') window.enhanceMcqsUnified(); },0);
                    },

                    startPolling() {
                        const t = setInterval(async () => {
                            try {
                                const r = await fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                                if (!r.ok) return;
                                const j = await r.json();
                                if (!j) return;
                                if ((j.ready === true) || (j.updated_at && j.updated_at !== this.lastUpdatedAt)) {
                                    clearInterval(t);
                                    location.reload();
                                }
                            } catch (e) {}
                        }, 2000);
                    },

                    previous() {
                        if (this.step > 1) this.step--;
                        this.setTimer();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        setTimeout(()=>{ if (typeof window.enhanceMcqsUnified==='function') window.enhanceMcqsUnified(); },0);
                    },

                    next() {
                        if (this.step < this.num_steps) {
                            if (this.timersEnabled && this.step >= this.canProceed) { this.showModal = true; return; }
                            this.step++;
                            this.setTimer();
                        } else if (this.step === this.num_steps) {
                            this.completeTraining();
                        }
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        setTimeout(()=>{ if (typeof window.enhanceMcqsUnified==='function') window.enhanceMcqsUnified(); },0);
                    },

                    setTimer() {
                        if (!this.timersEnabled) { this.canProceed = this.step + 1; return; }
                        if (this.canProceed < this.step) this.canProceed = this.step;
                        setTimeout(() => { this.canProceed = this.step + 1; }, (minTimePerStep[this.step] || 0) * 1000);
                    },

                    completeTraining() {
                        let timeSpent = Math.floor((Date.now() - startTime) / 1000);
                        window.location.href = "{{ route('training.complete') }}?time=" + timeSpent;
                    }
                }
            }
        </script>

        {{-- MCQ runtime & scenario link normalizer --}}
        <style>
            .rem-msg{margin:.5rem 0;font-size:.95rem}
            .rem-msg.ok{color:#0a7a0a}
            .rem-msg.warn{color:#b25c00}

            /* Force our option look even if Tailwind classes are injected server-side */
            .mcq .option-btn{
                display:block !important;
                width:100% !important;
                text-align:left !important;
                margin:.35rem 0 !important;
                border-radius:.75rem !important;
                padding:.65rem .9rem !important;
                background:#1e3a8a !important;
                color:#fff !important;
                border:0 !important;
            }
            .mcq .option-btn.selected{ background:#111827 !important; }

            /* Also style generic [data-letter] items (li/a) so they look like buttons too */
            .mcq [data-letter]:not(.check-answer){
                display:block !important;
                width:100% !important;
                text-align:left !important;
                margin:.35rem 0 !important;
                border-radius:.75rem !important;
                padding:.65rem .9rem !important;
                background:#1e3a8a !important;
                color:#fff !important;
                border:0 !important;
            }
            .mcq [data-letter].selected{ background:#111827 !important; color:#fff !important; }

            .check-answer{
                margin-top:.4rem; padding:.5rem .8rem; border-radius:.5rem;
                background:#2563eb; color:#fff; border:0
            }

            /* Make injected phishing links obvious & breakable */
            .ph-link { color:#1d4ed8; text-decoration:underline; word-break:break-all; }
        </style>

        <script>
            (function(){
                function closest(el, sel){ while (el && el.nodeType===1){ if (el.matches(sel)) return el; el = el.parentElement; } return null; }
                function msg(html, cls){ var p=document.createElement('p'); p.className='rem-msg '+(cls||''); p.innerHTML=html; return p; }
                function decode(s){ const t=document.createElement('textarea'); t.innerHTML=s||''; return t.value; }

                /* ---- Ensure scenario emails always show a visible link ---- */
                function ensureScenarioLinks(){
                    document.querySelectorAll('#section-mini-scenarios .mail-card').forEach(card=>{
                        const body = card.querySelector('.mail-body');
                        if (!body || body.querySelector('a')) return; // already has a link

                        // Build a realistic brand-secure domain from From: email
                        const fromText = (card.querySelector('.mail-from')?.textContent || '');
                        let domain = 'secure-notice.com';
                        const m = fromText.match(/@([a-z0-9.-]+\.[a-z]{2,})/i);
                        if (m){
                            const base = (m[1]||'').split('.')[0].replace(/[^a-z0-9]/ig,'') || 'secure';
                            domain = (base.toLowerCase()) + '-secure.com';
                        }
                        const label = 'https://' + domain + '/verify';

                        const p = document.createElement('p');
                        const a = document.createElement('a');
                        a.href = '#';
                        a.textContent = label;
                        a.className = 'ph-link';
                        p.appendChild(a);
                        body.appendChild(p);
                    });
                }

                /* ---- MCQ normalizer (builds buttons if missing) ---- */
                function normalizeMcq(mcq){
                    if (mcq.querySelector('.option-btn,[data-letter]')) return;
                    const rawHtml = mcq.innerHTML;
                    const plain = collapseSpaces(stripTags(rawHtml));
                    let qText = 'Choose the best answer:', after = plain;

                    const split = /(.*?)(?=\bA[\.\)])/i.exec(plain);
                    if (split && split[1].trim()){
                        qText = trimEndPunct(split[1].trim()) + '?';
                        after = plain.slice(split[0].length);
                    }

                    const optRe = /\b([A-D])[\.\)]\s*([^A-D]+?)(?=\s+[A-D][\.\)]\s+|$)/gi;
                    const opts = {}; let m;
                    while ((m = optRe.exec(after)) !== null){ opts[m[1].toUpperCase()] = m[2].trim(); }

                    if (Object.keys(opts).length === 0){
                        const temp = document.createElement('div'); temp.innerHTML = rawHtml;
                        temp.querySelectorAll('li, p').forEach(n=>{
                            const t = collapseSpaces(n.textContent||'');
                            const mm = /^([A-D])[\.\)]\s*(.+)$/.exec(t);
                            if (mm) opts[mm[1].toUpperCase()] = mm[2].trim();
                        });
                    }
                    if (Object.keys(opts).length === 0) return;

                    const corr = (mcq.getAttribute('data-correct')||'A').toUpperCase();
                    const explain = mcq.getAttribute('data-explain-all') || '';
                    let html = '<p class="font-semibold mb-2">'+escapeHtml(qText)+'</p>';
                    ['A','B','C','D'].forEach(L=>{
                        if (!opts[L]) return;
                        html += '<button type="button" class="option-btn" data-letter="'+L+'" data-explain="'+escapeHtml(explain)+'">'+L+'. '+escapeHtml(opts[L])+'</button>';
                    });
                    mcq.setAttribute('data-correct', ['A','B','C','D'].includes(corr)?corr:'A');
                    mcq.innerHTML = html;
                }

                function stripTags(s){ const d=document.createElement('div'); d.innerHTML=s; return (d.textContent||'').trim(); }
                function collapseSpaces(s){ return s.replace(/\s+/g,' ').trim(); }
                function trimEndPunct(s){ return s.replace(/[:\.\s]+$/,''); }
                function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

                /* ---- UI wiring ---- */
                function ensureCheckBtn(mcq){
                    if (mcq.querySelector('.check-answer')) return;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'check-answer';
                    btn.textContent = 'Check answer';
                    mcq.appendChild(btn);

                    btn.addEventListener('click', function(){
                        mcq.querySelectorAll('.rem-msg').forEach(m=>m.remove());

                        let chosen = (mcq.dataset.chosen||'').toUpperCase();
                        let chosenEl = null;
                        if (!chosen){
                            const selBtn = mcq.querySelector('.option-btn.selected,[data-letter].selected');
                            chosen = (selBtn?.getAttribute('data-letter')||'').toUpperCase();
                            chosenEl = selBtn || null;
                        } else {
                            chosenEl = mcq.querySelector('[data-letter="'+chosen+'"]');
                        }
                        if (!chosen){ mcq.appendChild(msg('Please choose an option.','warn')); return; }

                        const correctLetter = (mcq.getAttribute('data-correct')||'').toUpperCase();
                        let isCorrect = false;
                        if (correctLetter) isCorrect = (chosen === correctLetter);
                        if (!isCorrect && chosenEl && chosenEl.getAttribute('data-correct') === 'true') isCorrect = true;

                        const explain = decode((chosenEl && chosenEl.getAttribute('data-explain')) || mcq.getAttribute('data-explain-all') || '');

                        if (!mcq.dataset.firstChosen){
                            mcq.dataset.firstChosen = chosen;
                            mcq.dataset.firstResult = isCorrect ? '1' : '0';
                        }
                        mcq.dataset.chosen = chosen;
                        mcq.dataset.result = isCorrect ? '1' : '0';
                        mcq.dataset.checked = '1';

                        mcq.appendChild(msg((isCorrect?'Correct ✓ ':'Not quite ✱ ')+explain, isCorrect?'ok':'warn'));
                    });
                }

                window.enhanceMcqsUnified = function(){
                    document.querySelectorAll('.mcq').forEach(mcq => {
                        normalizeMcq(mcq);
                        ensureCheckBtn(mcq);
                    });
                    ensureScenarioLinks();
                }

                document.addEventListener('pointerdown', function(e){
                    const opt = e.target.closest('.mcq .option-btn, .mcq [data-letter], .mcq li[data-letter], .mcq a[data-letter]');
                    if (opt){
                        e.preventDefault();
                        const mcq = closest(opt,'.mcq');
                        mcq.querySelectorAll('.option-btn, [data-letter], li, a').forEach(b=>b.classList.remove('selected'));
                        opt.classList.add('selected');
                        mcq.dataset.chosen = (opt.getAttribute('data-letter')||'').toUpperCase();
                        ensureCheckBtn(mcq);
                    }
                }, false);

                document.addEventListener('click', function(e){
                    if (e.target.matches('a')) e.preventDefault(); // block navigation away from generated HTML
                }, false);

                window.addEventListener('DOMContentLoaded', ()=> setTimeout(window.enhanceMcqsUnified, 0));
            })();
        </script>
    </x-slot>
</x-app-layout>
