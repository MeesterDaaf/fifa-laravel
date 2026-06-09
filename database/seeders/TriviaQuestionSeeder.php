<?php

namespace Database\Seeders;

use App\Models\TriviaQuestion;
use Illuminate\Database\Seeder;

class TriviaQuestionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->questions() as $q) {
            // Idempotent: bestaande vraag (zelfde tekst) blijft ongemoeid,
            // inclusief een eventueel al toegewezen 'used_on'.
            TriviaQuestion::firstOrCreate(
                ['question' => $q['question']],
                [
                    'category'      => $q['category'],
                    'options'       => $q['options'],
                    'correct_index' => $q['correct_index'],
                    'explanation'   => $q['explanation'],
                ]
            );
        }
    }

    private function questions(): array
    {
        return [
            [
                'category' => 'records',
                'question' => 'Welk land speelde als enige élk WK sinds 1930?',
                'options' => ['Duitsland', 'Italië', 'Brazilië', 'Argentinië'],
                'correct_index' => 2,
                'explanation' => 'Brazilië is het enige land dat op álle WK\'s present was. Italië en Argentinië misten er allebei een paar.',
            ],
            [
                'category' => 'records',
                'question' => 'Welk land won de meeste WK-titels?',
                'options' => ['Brazilië (5)', 'Duitsland (4)', 'Italië (4)', 'Argentinië (3)'],
                'correct_index' => 0,
                'explanation' => 'Brazilië pakte er vijf (1958, \'62, \'70, \'94, 2002). Argentinië kwam in 2022 op drie.',
            ],
            [
                'category' => 'spelers',
                'question' => 'Wie is de topscorer aller tijden op WK-eindrondes?',
                'options' => ['Ronaldo (Brazilië)', 'Miroslav Klose', 'Gerd Müller', 'Lionel Messi'],
                'correct_index' => 1,
                'explanation' => 'De Duitser Klose maakte 16 WK-goals, net één meer dan de Braziliaan Ronaldo (15).',
            ],
            [
                'category' => 'oranje',
                'question' => 'Hoe vaak verloor Nederland een WK-finale — zonder er ooit één te winnen?',
                'options' => ['1 keer', '2 keer', '3 keer', '4 keer'],
                'correct_index' => 2,
                'explanation' => '1974, 1978 én 2010 — drie finales, drie keer net niet. Het beroemdste "beste team dat nooit won".',
            ],
            [
                'category' => 'momenten',
                'question' => 'Tegen welk land scoorde Maradona in 1986 zijn beruchte "Hand van God"?',
                'options' => ['Engeland', 'Duitsland', 'België', 'Brazilië'],
                'correct_index' => 0,
                'explanation' => 'In de kwartfinale tegen Engeland tikte Maradona \'m met de hand erin. Vier minuten later volgde zijn "Goal van de Eeuw".',
            ],
            [
                'category' => 'wk-2026',
                'question' => 'Hoeveel landen doen mee aan het WK 2026 — het eerste met het uitgebreide format?',
                'options' => ['32', '40', '48', '64'],
                'correct_index' => 2,
                'explanation' => 'Voor het eerst 48 landen (was 32), verdeeld over de VS, Canada en Mexico samen.',
            ],
            [
                'category' => 'curiosa',
                'question' => 'De WK-trofee werd in 1966 vlak voor het toernooi gestolen in Engeland. Wie vond \'m terug?',
                'options' => ['Scotland Yard', 'een hond genaamd Pickles', 'een schoonmaker van het stadion', 'een toevallige toerist'],
                'correct_index' => 1,
                'explanation' => 'Pickles snuffelde de in krantenpapier gewikkelde beker op onder een heggetje in een voortuin. De hond werd nationale held.',
            ],
            [
                'category' => 'curiosa',
                'question' => 'Welk dier werd in 2010 wereldberoemd door WK-uitslagen te "voorspellen"?',
                'options' => ['een papegaai', 'een olifant', 'een octopus genaamd Paul', 'een kameel'],
                'correct_index' => 2,
                'explanation' => 'Paul de octopus koos zijn maaltijd uit bakjes met landenvlaggen — en had het in 2010 elke keer goed, finale inbegrepen.',
            ],
            [
                'category' => 'curiosa',
                'question' => 'Hoe snel viel het snelste WK-doelpunt ooit (Hakan Şükür, 2002)?',
                'options' => ['na 11 seconden', 'na 38 seconden', 'na 1 minuut', 'na 2 minuten'],
                'correct_index' => 0,
                'explanation' => 'Elf tellen na de aftrap stond het al 1-0. De tegenstander had de bal nog amper aangeraakt.',
            ],
            [
                'category' => 'momenten',
                'question' => 'Bij de opening van WK 1994 zou Diana Ross een penalty in een doel schieten dat daarna "uit elkaar zou vallen". Wat ging er mis?',
                'options' => ['ze miste compleet — maar het doel viel toch uit elkaar', 'de bal knalde tegen de lat', 'ze gleed uit bij de aanloop', 'het doel viel te vroeg om'],
                'correct_index' => 0,
                'explanation' => 'Ze schoot \'m metersnaast, maar het doel klapte volgens script alsnog keurig doormidden. Ongemakkelijk én geweldig.',
            ],
            [
                'category' => 'spelers',
                'question' => 'Waarmee stal de 38-jarige Roger Milla (Kameroen) op WK 1990 ieders hart na elke goal?',
                'options' => ['een salto', 'een dansje bij de cornervlag', 'op zijn handen lopen', 'zijn shirt over zijn hoofd trekken'],
                'correct_index' => 1,
                'explanation' => 'Milla huppelde steevast naar de cornervlag voor een dansje. In 1994 werd hij met 42 jaar de oudste WK-doelpuntenmaker ooit.',
            ],
            [
                'category' => 'historie',
                'question' => 'In de allereerste WK-finale (1930, Uruguay–Argentinië) ontstond ruzie over... wat?',
                'options' => ['de kleur van de shirts', 'met welke bal er gespeeld werd', 'het aanvangstijdstip', 'de keuze van de scheidsrechter'],
                'correct_index' => 1,
                'explanation' => 'Onoplosbaar, dus: eerste helft de bal van Argentinië (rust: 2-1 vóór), tweede helft die van Uruguay — dat met 4-2 won. Toeval?',
            ],
            [
                'category' => 'curiosa',
                'question' => 'Brazilië mocht de Jules Rimet-trofee in 1970 voor altijd houden. Wat gebeurde ermee?',
                'options' => ['staat veilig in een museum in Rio', 'in 1983 gestolen en nooit teruggevonden', 'geschonken aan de FIFA', 'verloren bij een brand'],
                'correct_index' => 1,
                'explanation' => 'In 1983 uit de kast van de Braziliaanse voetbalbond geroofd — vermoedelijk omgesmolten tot goud. Spoorloos.',
            ],
            [
                'category' => 'curiosa',
                'question' => 'Keepers klaagden op WK 2010 steen en been over de officiële bal (de "Jabulani"). Waarmee vergeleken ze \'m?',
                'options' => ['een strandbal', 'een kanonskogel', 'een ballon', 'een biljartbal'],
                'correct_index' => 0,
                'explanation' => 'De bal vloog zó grillig dat keepers hem een "strandbal" of "supermarktbal" noemden.',
            ],
            [
                'category' => 'records',
                'question' => 'Welke ploeg scoorde de meeste goals ooit in één WK-wedstrijd?',
                'options' => ['Hongarije — 10 (tegen El Salvador, 1982)', 'Duitsland — 8 (tegen Saoedi-Arabië, 2002)', 'Joegoslavië — 9 (tegen Zaïre, 1974)', 'Brazilië — 7 (tegen Duitsland, 2014)'],
                'correct_index' => 0,
                'explanation' => 'Hongarije walste in 1982 met 10-1 over El Salvador heen. Tien goals door één ploeg: nog altijd record.',
            ],
            [
                'category' => 'spelers',
                'question' => 'De Braziliaanse dribbelkoning Garrincha (WK-winnaar 1958 én 1962) had een opvallend lichamelijk kenmerk. Welk?',
                'options' => ['zijn benen waren krom, naar binnen en buiten gebogen', 'hij had zes tenen', 'hij was kleurenblind', 'hij miste een vinger'],
                'correct_index' => 0,
                'explanation' => 'Geboren met een kromme rug en benen die de verkeerde kant op bogen — tóch werd hij een van de beste dribbelaars aller tijden.',
            ],
            [
                'category' => 'records',
                'question' => 'Hoeveel goals maakte de Fransman Just Fontaine op één WK (1958) — nog altijd een record?',
                'options' => ['9', '11', '13', '16'],
                'correct_index' => 2,
                'explanation' => 'Dertien goals in zes wedstrijden. In meer dan 65 jaar kwam niemand in de buurt.',
            ],
            [
                'category' => 'records',
                'question' => 'Wie was de eerste speler met een hattrick in een WK-finale?',
                'options' => ['Pelé', 'Geoff Hurst', 'Gerd Müller', 'Kylian Mbappé'],
                'correct_index' => 1,
                'explanation' => 'De Engelsman Hurst deed het in 1966 (4-2 tegen West-Duitsland). Pas in 2022 evenaarde Mbappé het — maar die verloor de finale.',
            ],
            [
                'category' => 'historie',
                'question' => 'Welk land won het allereerste WK (1930), op eigen bodem?',
                'options' => ['Brazilië', 'Argentinië', 'Uruguay', 'Italië'],
                'correct_index' => 2,
                'explanation' => 'Uruguay organiseerde én won het eerste WK, met 4-2 tegen buurland Argentinië in de finale.',
            ],
            [
                'category' => 'momenten',
                'question' => 'Hoe eindigde de WK-carrière van Zinédine Zidane in de finale van 2006?',
                'options' => ['met een rode kaart na een kopstoot', 'met een gemiste strafschop', 'met een blessure in de rust', 'met de winnende goal'],
                'correct_index' => 0,
                'explanation' => 'Zidane gaf Marco Materazzi een kopstoot tegen de borst en werd in zijn állerlaatste wedstrijd weggestuurd. Italië won na penalty\'s.',
            ],
            [
                'category' => 'oranje',
                'question' => 'Met welke goal schakelde Dennis Bergkamp in 1998 Argentinië uit in de kwartfinale?',
                'options' => ['een penalty in de laatste minuut', 'een wereldgoal in de 90e minuut: aannemen, uitkappen, raak', 'een kopbal uit een corner', 'een vrije trap'],
                'correct_index' => 1,
                'explanation' => 'Bergkamp plukte een lange bal uit de lucht, kapte zijn man uit en schoot raak — 2-1 in de 90e minuut. Een van de mooiste goals uit de Oranje-historie.',
            ],
            [
                'category' => 'curiosa',
                'question' => 'Hoeveel goals vielen er in de doelpuntrijkste WK-wedstrijd ooit (Oostenrijk–Zwitserland, 1954)?',
                'options' => ['8', '10', '12', '14'],
                'correct_index' => 2,
                'explanation' => 'Oostenrijk won met 7-5 — twaalf goals in één pot, tot vandaag record.',
            ],
            [
                'category' => 'gastlanden',
                'question' => 'Welk land sprong in 1986 bij als gastland nadat Colombia zich had teruggetrokken?',
                'options' => ['Mexico', 'Verenigde Staten', 'Brazilië', 'Argentinië'],
                'correct_index' => 0,
                'explanation' => 'Colombia haakte af om financiële redenen; Mexico sprong in en werd het eerste land dat tweemaal een WK organiseerde (na 1970).',
            ],
            [
                'category' => 'gastlanden',
                'question' => 'Op welk continent vond het WK 2010 plaats — een primeur?',
                'options' => ['Afrika', 'Azië', 'Zuid-Amerika', 'Oceanië'],
                'correct_index' => 0,
                'explanation' => 'Zuid-Afrika 2010 was het allereerste WK op Afrikaanse bodem.',
            ],
            [
                'category' => 'gastlanden',
                'question' => 'WK 2002 was het eerste in Azië én het eerste met twéé gastlanden. Welke?',
                'options' => ['Zuid-Korea en Japan', 'China en Japan', 'Zuid-Korea en China', 'Japan en Thailand'],
                'correct_index' => 0,
                'explanation' => 'Twee primeurs tegelijk: eerste WK in Azië, en voor het eerst georganiseerd door twee landen samen.',
            ],
            [
                'category' => 'wk-2026',
                'question' => 'Over hoeveel gaststeden is het WK 2026 verdeeld?',
                'options' => ['10', '12', '16', '20'],
                'correct_index' => 2,
                'explanation' => '16 steden in de VS, Canada en Mexico — qua omvang het grootste WK ooit.',
            ],
            [
                'category' => 'wk-2026',
                'question' => 'Hoeveel wedstrijden worden er in totaal gespeeld op WK 2026?',
                'options' => ['64', '80', '104', '128'],
                'correct_index' => 2,
                'explanation' => 'Door de uitbreiding naar 48 landen telt het toernooi 104 duels — fors meer dan de 64 van voorheen.',
            ],
            [
                'category' => 'momenten',
                'question' => 'Welke twee landen speelden de WK-finale van 2022?',
                'options' => ['Argentinië en Frankrijk', 'Brazilië en Duitsland', 'Frankrijk en Kroatië', 'Argentinië en Kroatië'],
                'correct_index' => 0,
                'explanation' => 'Na 3-3 besliste Argentinië het op penalty\'s — Messi eindelijk wereldkampioen.',
            ],
            [
                'category' => 'oranje',
                'question' => 'Welk land werd in 2010 voor het eerst wereldkampioen, ten koste van Oranje?',
                'options' => ['Spanje', 'Italië', 'Duitsland', 'Frankrijk'],
                'correct_index' => 0,
                'explanation' => 'Andrés Iniesta scoorde in de verlenging: 1-0. Spaanse primeur, pijnlijk voor Nederland.',
            ],
            [
                'category' => 'historie',
                'question' => 'Wie bezorgde gastland Brazilië in 1950 de "Maracanazo" in de beslissende wedstrijd?',
                'options' => ['Uruguay', 'Argentinië', 'Italië', 'Zweden'],
                'correct_index' => 0,
                'explanation' => 'Voor zo\'n 200.000 toeschouwers won Uruguay met 2-1. Het verstilde Maracanã ging de boeken in als "Maracanazo".',
            ],
            [
                'category' => 'spelers',
                'question' => 'Wie won als enige speler ooit drie keer het WK?',
                'options' => ['Pelé', 'Diego Maradona', 'Franz Beckenbauer', 'Johan Cruijff'],
                'correct_index' => 0,
                'explanation' => 'Pelé pakte drie wereldtitels: 1958, 1962 en 1970.',
            ],
            [
                'category' => 'spelers',
                'question' => 'Hoe oud was Pelé toen hij scoorde in de WK-finale van 1958?',
                'options' => ['16', '17', '19', '21'],
                'correct_index' => 1,
                'explanation' => 'Met 17 jaar de jongste finalescorer ooit — hij maakte er meteen twee tegen Zweden.',
            ],
            [
                'category' => 'momenten',
                'question' => 'Wie maakte het door FIFA verkozen "Doelpunt van de Eeuw" op WK 1986?',
                'options' => ['Diego Maradona', 'Pelé', 'Carlos Alberto', 'Ronaldinho'],
                'correct_index' => 0,
                'explanation' => 'Maradona dribbelde tegen Engeland langs het halve elftal — later verkozen tot doelpunt van de eeuw. Dezelfde wedstrijd als de "Hand van God".',
            ],
            [
                'category' => 'oranje',
                'question' => 'Welke bondscoach leidde Oranje in 2010 naar de WK-finale?',
                'options' => ['Bert van Marwijk', 'Louis van Gaal', 'Guus Hiddink', 'Dick Advocaat'],
                'correct_index' => 0,
                'explanation' => 'Van Marwijk haalde de finale van 2010. Van Gaal pakte vier jaar later het brons.',
            ],
            [
                'category' => 'oranje',
                'question' => 'Hoe ver kwam Nederland op het WK 2014 in Brazilië?',
                'options' => ['groepsfase', 'kwartfinale', 'derde plaats (brons)', 'finale'],
                'correct_index' => 2,
                'explanation' => 'Onder Van Gaal werd Oranje derde — na die legendarische 5-1 tegen wereldkampioen Spanje in de openingswedstrijd.',
            ],
            [
                'category' => 'curiosa',
                'question' => 'Welke tegenstander beet Luis Suárez op het WK 2014?',
                'options' => ['Giorgio Chiellini (Italië)', 'Sergio Ramos', 'Pepe', 'Thiago Silva'],
                'correct_index' => 0,
                'explanation' => 'Suárez zette zijn tanden in de schouder van de Italiaan Chiellini — zijn dérde bijtincident, en een flinke schorsing.',
            ],
            [
                'category' => 'curiosa',
                'question' => 'Welk geluid domineerde het WK 2010 in Zuid-Afrika?',
                'options' => ['de vuvuzela', 'trommels', 'scheidsrechtersfluitjes', 'autotoeters'],
                'correct_index' => 0,
                'explanation' => 'De zoemende vuvuzela-hoorns maakten zo\'n herrie dat tv-zenders speciale geluidsfilters inzetten.',
            ],
            [
                'category' => 'curiosa',
                'question' => 'Wat gebeurde er met Ronaldo (Brazilië) vlak vóór de WK-finale van 1998?',
                'options' => ['hij kreeg die ochtend een mysterieuze toeval', 'hij kwam te laat in het stadion', 'hij kreeg ruzie met de bondscoach', 'hij blesseerde zich bij het inspelen'],
                'correct_index' => 0,
                'explanation' => 'Uren voor de finale kreeg Ronaldo een onverklaarde stuip. Hij speelde toch, kwam niet in vorm, en Brazilië verloor met 3-0 van Frankrijk. Tot vandaag een mysterie.',
            ],
            [
                'category' => 'records',
                'question' => 'Hoe snel viel de snelste rode kaart ooit op een WK (José Batista, 1986)?',
                'options' => ['na 56 seconden', 'na 2 minuten', 'na 5 minuten', 'na 8 minuten'],
                'correct_index' => 0,
                'explanation' => 'De Uruguayaan Batista vloog tegen Schotland al na 56 seconden van het veld — record.',
            ],
            [
                'category' => 'historie',
                'question' => 'Welk Afrikaans land bereikte als eerste de kwartfinale van een WK?',
                'options' => ['Kameroen (1990)', 'Senegal (2002)', 'Ghana (2010)', 'Nigeria (1994)'],
                'correct_index' => 0,
                'explanation' => 'Kameroen verraste in 1990 de hele wereld en haalde de kwartfinale — destijds een Afrikaans record.',
            ],
            [
                'category' => 'historie',
                'question' => 'Welk Aziatisch land stuntte zich in 2002 naar de halve finale?',
                'options' => ['Zuid-Korea', 'Japan', 'China', 'Saoedi-Arabië'],
                'correct_index' => 0,
                'explanation' => 'Co-gastland Zuid-Korea haalde in 2002 de halve finale — nog altijd het beste Aziatische resultaat ooit.',
            ],
            [
                'category' => 'records',
                'question' => 'Welk land speelde de meeste WK-finales (gewonnen én verloren)?',
                'options' => ['Duitsland — 8', 'Brazilië — 7', 'Italië — 6', 'Argentinië — 6'],
                'correct_index' => 0,
                'explanation' => 'Duitsland (incl. West-Duitsland) stond acht keer in de finale — vaker dan welk land ook.',
            ],
            [
                'category' => 'records',
                'question' => 'Hoe vaak werd Duitsland (incl. West-Duitsland) wereldkampioen?',
                'options' => ['2', '3', '4', '5'],
                'correct_index' => 2,
                'explanation' => 'Vier titels: 1954, 1974, 1990 en 2014.',
            ],
            [
                'category' => 'historie',
                'question' => 'Sinds welk WK deelt de scheidsrechter gele en rode kaarten uit?',
                'options' => ['1950', '1962', '1970', '1986'],
                'correct_index' => 2,
                'explanation' => 'De kleurenkaarten werden in 1970 ingevoerd. Daarvoor was vaak onduidelijk wie nu eigenlijk bestraft werd.',
            ],
            [
                'category' => 'spelers',
                'question' => 'Wie is de enige keeper die ooit de Gouden Bal (beste speler van het toernooi) won?',
                'options' => ['Oliver Kahn (2002)', 'Iker Casillas', 'Gianluigi Buffon', 'Manuel Neuer'],
                'correct_index' => 0,
                'explanation' => 'De Duitser Kahn werd in 2002 als enige doelman ooit verkozen tot beste speler van een WK.',
            ],
        ];
    }
}
