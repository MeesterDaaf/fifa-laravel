{{-- Vraag van de dag — grappige trivia, telt niet mee voor de ranglijst. --}}
@if(!empty($trivia))
    @php $voted = !empty($triviaVote); @endphp

    <section>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-bold text-gray-800">🎲 Vraag van de dag</h2>
            <span class="text-[11px] uppercase tracking-wide text-gray-500 bg-gray-100 rounded-full px-2.5 py-0.5">{{ $trivia->category }}</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="font-semibold text-gray-800 mb-4">{{ $trivia->question }}</p>

            @if(!$voted)
                {{-- Nog niet gestemd: keuzeknoppen --}}
                <form method="POST" action="/trivia/{{ $trivia->id }}/vote" class="space-y-2">
                    @csrf
                    @foreach($trivia->options as $i => $opt)
                        <button type="submit" name="choice" value="{{ $i }}"
                            class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-colors">
                            <span class="w-6 h-6 shrink-0 rounded-full bg-gray-100 text-gray-600 text-xs font-bold flex items-center justify-center">{{ chr(65 + $i) }}</span>
                            <span class="text-sm text-gray-800">{{ $opt }}</span>
                        </button>
                    @endforeach
                </form>
                <p class="text-xs text-gray-400 mt-3">Kies een antwoord — daarna zie je meteen wat de rest stemde. Eén keer per dag. 🙂</p>
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
                            {{ $isCorrect ? 'border-green-400' : ($isMine ? 'border-red-300' : 'border-gray-200') }}">
                            {{-- balk --}}
                            <div class="absolute inset-y-0 left-0 {{ $isCorrect ? 'bg-green-100' : 'bg-gray-100' }}"
                                style="width: {{ $pct }}%"></div>
                            <div class="relative flex items-center gap-3 px-4 py-3">
                                <span class="w-6 h-6 shrink-0 rounded-full text-xs font-bold flex items-center justify-center
                                    {{ $isCorrect ? 'bg-green-500 text-white' : ($isMine ? 'bg-red-400 text-white' : 'bg-gray-200 text-gray-600') }}">{{ chr(65 + $i) }}</span>
                                <span class="text-sm text-gray-800 flex-1">
                                    {{ $opt }}
                                    @if($isCorrect) <span class="text-green-600 font-semibold">✓</span> @endif
                                    @if($isMine && !$isCorrect) <span class="text-red-500 text-xs">(jouw keuze)</span> @endif
                                </span>
                                <span class="text-sm font-bold {{ $isCorrect ? 'text-green-700' : 'text-gray-500' }} shrink-0">{{ $pct }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 rounded-xl p-4 {{ $wasRight ? 'bg-green-50 border border-green-200' : 'bg-amber-50 border border-amber-200' }}">
                    <p class="font-semibold text-sm mb-1 {{ $wasRight ? 'text-green-800' : 'text-amber-800' }}">
                        {{ $wasRight ? 'Goed geraden! 🎉' : 'Helaas, mis! 🙈' }}
                    </p>
                    <p class="text-sm text-gray-700">{{ $trivia->explanation }}</p>
                </div>

                <p class="text-xs text-gray-400 mt-3 text-center">
                    {{ $triviaResults['total'] ?? 0 }} {{ ($triviaResults['total'] ?? 0) === 1 ? 'deelnemer stemde' : 'deelnemers stemden' }} mee · morgen een nieuwe vraag
                </p>
            @endif
        </div>
    </section>
@endif
