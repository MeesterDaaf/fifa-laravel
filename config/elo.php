<?php

/*
|--------------------------------------------------------------------------
| Elo-ratings per land (TLA-code volgens football-data.org)
|--------------------------------------------------------------------------
| Bron: World Football Elo Ratings (eloratings.net), benadering begin 2026.
| Gebruikt om win/gelijk/verlies-kansen per wedstrijd te berekenen.
| Onbekende codes (bv. 'TBD') vallen terug op config('elo.default').
*/

return [

    'default' => 1500,

    // Kleine voorsprong voor het als 'thuis' genoteerde team. Bij een WK
    // grotendeels neutraal terrein, dus laag gehouden.
    'home_advantage' => 25,

    // Vormparameters van het kansmodel.
    'draw_base'  => 0.27,  // max kans op gelijkspel (bij exact gelijke teams)
    'draw_sigma' => 250,   // hoe snel de gelijkspelkans daalt bij verschil

    'ratings' => [
        'ARG' => 2143, // Argentina
        'FRA' => 2096, // France
        'ESP' => 2079, // Spain
        'BRA' => 2048, // Brazil
        'ENG' => 2025, // England
        'POR' => 2003, // Portugal
        'NED' => 1995, // Netherlands
        'GER' => 1962, // Germany
        'BEL' => 1936, // Belgium
        'CRO' => 1907, // Croatia
        'URY' => 1901, // Uruguay
        'COL' => 1888, // Colombia
        'MAR' => 1853, // Morocco
        'SUI' => 1835, // Switzerland
        'JPN' => 1834, // Japan
        'USA' => 1801, // United States
        'SEN' => 1800, // Senegal
        'MEX' => 1798, // Mexico
        'AUT' => 1792, // Austria
        'ECU' => 1791, // Ecuador
        'IRN' => 1783, // Iran
        'TUR' => 1781, // Turkey
        'NOR' => 1779, // Norway
        'CZE' => 1761, // Czechia
        'ALG' => 1752, // Algeria
        'SWE' => 1751, // Sweden
        'SCO' => 1748, // Scotland
        'EGY' => 1734, // Egypt
        'CIV' => 1731, // Ivory Coast
        'AUS' => 1722, // Australia
        'BIH' => 1719, // Bosnia-Herzegovina
        'KOR' => 1771, // South Korea
        'CAN' => 1703, // Canada
        'PAR' => 1700, // Paraguay
        'TUN' => 1693, // Tunisia
        'GHA' => 1690, // Ghana
        'COD' => 1682, // Congo DR
        'QAT' => 1680, // Qatar
        'RSA' => 1641, // South Africa
        'KSA' => 1622, // Saudi Arabia
        'PAN' => 1620, // Panama
        'IRQ' => 1602, // Iraq
        'UZB' => 1600, // Uzbekistan
        'CPV' => 1571, // Cape Verde Islands
        'JOR' => 1561, // Jordan
        'HAI' => 1503, // Haiti
        'NZL' => 1500, // New Zealand
        'CUW' => 1452, // Curaçao
    ],
];
