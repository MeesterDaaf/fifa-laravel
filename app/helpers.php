<?php

if (! function_exists('get_flag')) {
    function get_flag(string $code): string
    {
        // FIFA-codes (TLA) zoals football-data.org ze teruggeeft → vlag-emoji.
        $flags = [
            'ALG' => '🇩🇿', // Algeria
            'ARG' => '🇦🇷', // Argentina
            'AUS' => '🇦🇺', // Australia
            'AUT' => '🇦🇹', // Austria
            'BEL' => '🇧🇪', // Belgium
            'BIH' => '🇧🇦', // Bosnia-Herzegovina
            'BRA' => '🇧🇷', // Brazil
            'CAN' => '🇨🇦', // Canada
            'CIV' => '🇨🇮', // Ivory Coast
            'COD' => '🇨🇩', // Congo DR
            'COL' => '🇨🇴', // Colombia
            'CPV' => '🇨🇻', // Cape Verde Islands
            'CRO' => '🇭🇷', // Croatia
            'CUW' => '🇨🇼', // Curaçao
            'CZE' => '🇨🇿', // Czechia
            'ECU' => '🇪🇨', // Ecuador
            'EGY' => '🇪🇬', // Egypt
            'ENG' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿', // England
            'ESP' => '🇪🇸', // Spain
            'FRA' => '🇫🇷', // France
            'GER' => '🇩🇪', // Germany
            'GHA' => '🇬🇭', // Ghana
            'HAI' => '🇭🇹', // Haiti
            'IRN' => '🇮🇷', // Iran
            'IRQ' => '🇮🇶', // Iraq
            'JOR' => '🇯🇴', // Jordan
            'JPN' => '🇯🇵', // Japan
            'KOR' => '🇰🇷', // South Korea
            'KSA' => '🇸🇦', // Saudi Arabia
            'MAR' => '🇲🇦', // Morocco
            'MEX' => '🇲🇽', // Mexico
            'NED' => '🇳🇱', // Netherlands
            'NOR' => '🇳🇴', // Norway
            'NZL' => '🇳🇿', // New Zealand
            'PAN' => '🇵🇦', // Panama
            'PAR' => '🇵🇾', // Paraguay
            'POR' => '🇵🇹', // Portugal
            'QAT' => '🇶🇦', // Qatar
            'RSA' => '🇿🇦', // South Africa
            'SCO' => '🏴󠁧󠁢󠁳󠁣󠁴󠁿', // Scotland
            'SEN' => '🇸🇳', // Senegal
            'SUI' => '🇨🇭', // Switzerland
            'SWE' => '🇸🇪', // Sweden
            'TUN' => '🇹🇳', // Tunisia
            'TUR' => '🇹🇷', // Turkey
            'URY' => '🇺🇾', // Uruguay
            'USA' => '🇺🇸', // United States
            'UZB' => '🇺🇿', // Uzbekistan
        ];
        return $flags[$code] ?? '🏳️';
    }
}

if (! function_exists('country_name')) {
    /**
     * Volledige Nederlandse landnaam bij een FIFA-code (TLA).
     * Valt terug op de meegegeven naam (Engels uit de API) of de code zelf.
     */
    function country_name(?string $code, ?string $fallback = null): string
    {
        $names = [
            'ALG' => 'Algerije',
            'ARG' => 'Argentinië',
            'AUS' => 'Australië',
            'AUT' => 'Oostenrijk',
            'BEL' => 'België',
            'BIH' => 'Bosnië-Herzegovina',
            'BRA' => 'Brazilië',
            'CAN' => 'Canada',
            'CIV' => 'Ivoorkust',
            'COD' => 'DR Congo',
            'COL' => 'Colombia',
            'CPV' => 'Kaapverdië',
            'CRO' => 'Kroatië',
            'CUW' => 'Curaçao',
            'CZE' => 'Tsjechië',
            'ECU' => 'Ecuador',
            'EGY' => 'Egypte',
            'ENG' => 'Engeland',
            'ESP' => 'Spanje',
            'FRA' => 'Frankrijk',
            'GER' => 'Duitsland',
            'GHA' => 'Ghana',
            'HAI' => 'Haïti',
            'IRN' => 'Iran',
            'IRQ' => 'Irak',
            'JOR' => 'Jordanië',
            'JPN' => 'Japan',
            'KOR' => 'Zuid-Korea',
            'KSA' => 'Saoedi-Arabië',
            'MAR' => 'Marokko',
            'MEX' => 'Mexico',
            'NED' => 'Nederland',
            'NOR' => 'Noorwegen',
            'NZL' => 'Nieuw-Zeeland',
            'PAN' => 'Panama',
            'PAR' => 'Paraguay',
            'POR' => 'Portugal',
            'QAT' => 'Qatar',
            'RSA' => 'Zuid-Afrika',
            'SCO' => 'Schotland',
            'SEN' => 'Senegal',
            'SUI' => 'Zwitserland',
            'SWE' => 'Zweden',
            'TUN' => 'Tunesië',
            'TUR' => 'Turkije',
            'URY' => 'Uruguay',
            'USA' => 'Verenigde Staten',
            'UZB' => 'Oezbekistan',
        ];

        return $names[$code] ?? $fallback ?? $code ?? '—';
    }
}

if (! function_exists('group_label')) {
    /** "GROUP_A" → "Groep A"; lege/onbekende waarde → ''. */
    function group_label(?string $group): string
    {
        if (! $group) {
            return '';
        }
        if (str_starts_with($group, 'GROUP_')) {
            return 'Groep '.substr($group, 6);
        }
        return $group;
    }
}

if (! function_exists('to_nl_time')) {
    /** Zet een (in UTC opgeslagen) tijd om naar Nederlandse tijd voor weergave. */
    function to_nl_time(\Carbon\Carbon|string $date): \Carbon\Carbon
    {
        $d = $date instanceof \Carbon\Carbon ? $date->copy() : \Carbon\Carbon::parse($date);
        return $d->setTimezone('Europe/Amsterdam');
    }
}

if (! function_exists('format_date')) {
    function format_date(\Carbon\Carbon|string $date): string
    {
        $d = to_nl_time($date);
        $days = [
            'Monday' => 'maandag', 'Tuesday' => 'dinsdag', 'Wednesday' => 'woensdag',
            'Thursday' => 'donderdag', 'Friday' => 'vrijdag', 'Saturday' => 'zaterdag', 'Sunday' => 'zondag',
        ];
        $months = [
            'January' => 'januari', 'February' => 'februari', 'March' => 'maart',
            'April' => 'april', 'May' => 'mei', 'June' => 'juni', 'July' => 'juli',
            'August' => 'augustus', 'September' => 'september', 'October' => 'oktober',
            'November' => 'november', 'December' => 'december',
        ];
        $day = $days[$d->format('l')] ?? strtolower($d->format('l'));
        $month = $months[$d->format('F')] ?? strtolower($d->format('F'));
        return "{$day} {$d->format('j')} {$month} {$d->format('H:i')}";
    }
}

if (! function_exists('format_day')) {
    /** Nederlandse datum zonder tijd, bijv. "donderdag 11 juni". */
    function format_day(\Carbon\Carbon|string $date): string
    {
        $d = to_nl_time($date);
        $days = [
            'Monday' => 'maandag', 'Tuesday' => 'dinsdag', 'Wednesday' => 'woensdag',
            'Thursday' => 'donderdag', 'Friday' => 'vrijdag', 'Saturday' => 'zaterdag', 'Sunday' => 'zondag',
        ];
        $months = [
            'January' => 'januari', 'February' => 'februari', 'March' => 'maart',
            'April' => 'april', 'May' => 'mei', 'June' => 'juni', 'July' => 'juli',
            'August' => 'augustus', 'September' => 'september', 'October' => 'oktober',
            'November' => 'november', 'December' => 'december',
        ];
        $day = $days[$d->format('l')] ?? strtolower($d->format('l'));
        $month = $months[$d->format('F')] ?? strtolower($d->format('F'));
        return "{$day} {$d->format('j')} {$month}";
    }
}

if (! function_exists('format_date_short')) {
    function format_date_short(\Carbon\Carbon|string $date): string
    {
        $d = to_nl_time($date);
        $months = [
            'Jan' => 'jan', 'Feb' => 'feb', 'Mar' => 'mrt', 'Apr' => 'apr',
            'May' => 'mei', 'Jun' => 'jun', 'Jul' => 'jul', 'Aug' => 'aug',
            'Sep' => 'sep', 'Oct' => 'okt', 'Nov' => 'nov', 'Dec' => 'dec',
        ];
        $month = $months[$d->format('M')] ?? strtolower($d->format('M'));
        return "{$d->format('j')} {$month} {$d->format('H:i')}";
    }
}
