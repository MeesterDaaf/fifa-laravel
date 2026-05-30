<?php

/*
|--------------------------------------------------------------------------
| Puntentelling
|--------------------------------------------------------------------------
| Alle puntwaarden van de pool op één plek, zodat ze makkelijk bij te
| stellen zijn. Na een wijziging eventueel de al gespeelde wedstrijden /
| toernooi-uitslag opnieuw laten doorrekenen via de ScoringService.
*/

return [

    // Per wedstrijd
    'match' => [
        'exact'             => 5, // exacte uitslag
        'outcome'           => 2, // juiste winnaar/gelijkspel
        'goal_minute_bonus' => 3, // dichtst bij 1e doelpuntminuut (1 winnaar)
    ],

    // Toernooibreed
    'tournament' => [
        'champion'     => 30, // juiste toernooiwinnaar
        'top_scorer'   => 10, // juiste topscorer
        'yellow_cards' => 5,  // dichtst bij totaal gele kaarten (1 winnaar)
        'red_cards'    => 5,  // dichtst bij totaal rode kaarten (1 winnaar)
    ],

];
