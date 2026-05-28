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

if (! function_exists('format_date')) {
    function format_date(\Carbon\Carbon|string $date): string
    {
        $d = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
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

if (! function_exists('format_date_short')) {
    function format_date_short(\Carbon\Carbon|string $date): string
    {
        $d = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        $months = [
            'Jan' => 'jan', 'Feb' => 'feb', 'Mar' => 'mrt', 'Apr' => 'apr',
            'May' => 'mei', 'Jun' => 'jun', 'Jul' => 'jul', 'Aug' => 'aug',
            'Sep' => 'sep', 'Oct' => 'okt', 'Nov' => 'nov', 'Dec' => 'dec',
        ];
        $month = $months[$d->format('M')] ?? strtolower($d->format('M'));
        return "{$d->format('j')} {$month} {$d->format('H:i')}";
    }
}
