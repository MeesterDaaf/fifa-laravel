{{-- Vraag van de dag — grappige trivia, telt niet mee voor de ranglijst. --}}
@if(!empty($trivia))
    @php $voted = !empty($triviaVote); @endphp

    <section>
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white">🎲 Vraag van de dag</h2>
            <span class="pill pill-muted uppercase tracking-wide">{{ $trivia->category }}</span>
        </div>

        <div class="card p-5">
            <p class="font-semibold text-white/90 mb-4">{{ $trivia->question }}</p>

            @if(!$voted)
                {{-- Nog niet gestemd: keuzeknoppen --}}
                <form method="POST" action="/trivia/{{ $trivia->id }}/vote" class="space-y-2">
                    @csrf
                    @foreach($trivia->options as $i => $opt)
                        <button type="submit" name="choice" value="{{ $i }}"
                            class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-xl border border-white/10 bg-white/3 hover:border-volt-500/50 hover:bg-volt-500/8 transition-colors cursor-pointer">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-white/8 text-white/70 text-xs font-display font-bold flex items-center justify-center">{{ chr(65 + $i) }}</span>
                            <span class="text-sm text-white/85">{{ $opt }}</span>
                        </button>
                    @endforeach
                </form>
                <p class="text-xs text-white/40 mt-3">Kies een antwoord — daarna zie je meteen wat de rest stemde. Eén keer per dag. 🙂</p>
            @else
                {{-- Gestemd: uitslag met percentages, juiste antwoord en onthulling --}}
                @php
                    $correct = (int) $trivia->correct_index;
                    $mine = (int) $triviaVote->choice;
                    $wasRight = $mine === $correct;
                @endphp

                <div class="space-y-2">
                    @foreach($trivia->options as $i => $opt)
                        @php
                            $pct = $triviaResults['options'][$i]['pct'] ?? 0;
                            $isCorrect = $i === $correct;
                            $isMine = $i === $mine;
                        @endphp
                        <div class="relative overflow-hidden rounded-xl border
                            {{ $isCorrect ? 'border-volt-500/60' : ($isMine ? 'border-signal-red/50' : 'border-white/10') }}">
                            {{-- balk --}}
                            <div class="absolute inset-y-0 left-0 {{ $isCorrect ? 'bg-volt-500/15' : 'bg-white/5' }}"
                                style="width: {{ $pct }}%"></div>
                            <div class="relative flex items-center gap-3 px-4 py-3">
                                <span class="w-6 h-6 shrink-0 rounded-full text-xs font-display font-bold flex items-center justify-center
                                    {{ $isCorrect ? 'bg-volt-500 text-pitch-950' : ($isMine ? 'bg-signal-red text-pitch-950' : 'bg-white/10 text-white/60') }}">{{ chr(65 + $i) }}</span>
                                <span class="text-sm text-white/85 flex-1">
                                    {{ $opt }}
                                    @if($isCorrect) <span class="text-volt-400 font-semibold">✓</span> @endif
                                    @if($isMine && !$isCorrect) <span class="text-signal-red text-xs">(jouw keuze)</span> @endif
                                </span>
                                <span class="text-sm scoreline {{ $isCorrect ? 'text-volt-400' : 'text-white/45' }} shrink-0">{{ $pct }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 alert {{ $wasRight ? 'alert-ok' : 'alert-warn' }}">
                    <p class="font-semibold text-sm mb-1">
                        {{ $wasRight ? 'Goed geraden! 🎉' : 'Helaas, mis! 🙈' }}
                    </p>
                    <p class="text-sm text-white/70">{{ $trivia->explanation }}</p>
                </div>

                <p class="text-xs text-white/40 mt-3 text-center">
                    {{ $triviaResults['total'] ?? 0 }} {{ ($triviaResults['total'] ?? 0) === 1 ? 'deelnemer stemde' : 'deelnemers stemden' }} mee · morgen een nieuwe vraag
                </p>
            @endif
        </div>
    </section>
@endif
