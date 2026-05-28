<?php

if (! function_exists('get_flag')) {
    function get_flag(string $code): string
    {
        $flags = [
            'NED' => '🇳🇱', 'BEL' => '🇧🇪', 'GER' => '🇩🇪', 'FRA' => '🇫🇷', 'ESP' => '🇪🇸',
            'POR' => '🇵🇹', 'ENG' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿', 'ITA' => '🇮🇹', 'ARG' => '🇦🇷', 'BRA' => '🇧🇷',
            'USA' => '🇺🇸', 'MEX' => '🇲🇽', 'CAN' => '🇨🇦', 'MAR' => '🇲🇦', 'SEN' => '🇸🇳',
            'GHA' => '🇬🇭', 'NGA' => '🇳🇬', 'JPN' => '🇯🇵', 'KOR' => '🇰🇷', 'AUS' => '🇦🇺',
            'URU' => '🇺🇾', 'COL' => '🇨🇴', 'CHI' => '🇨🇱', 'ECU' => '🇪🇨', 'PER' => '🇵🇪',
            'SUI' => '🇨🇭', 'DEN' => '🇩🇰', 'SWE' => '🇸🇪', 'NOR' => '🇳🇴', 'POL' => '🇵🇱',
            'CRO' => '🇭🇷', 'SRB' => '🇷🇸', 'TUR' => '🇹🇷', 'GRE' => '🇬🇷', 'UKR' => '🇺🇦',
            'IRN' => '🇮🇷', 'SAU' => '🇸🇦', 'QAT' => '🇶🇦', 'EGY' => '🇪🇬', 'TUN' => '🇹🇳',
            'CMR' => '🇨🇲', 'CIV' => '🇨🇮', 'MLI' => '🇲🇱', 'ALG' => '🇩🇿', 'ZAM' => '🇿🇲',
            'IRQ' => '🇮🇶', 'JOR' => '🇯🇴', 'UZB' => '🇺🇿', 'KAZ' => '🇰🇿', 'TJK' => '🇹🇯',
            'NZL' => '🇳🇿', 'FIJ' => '🇫🇯', 'PNG' => '🇵🇬', 'VAN' => '🇻🇺',
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
