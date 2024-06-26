<?php

namespace Plastonick\Euros;

class Flag
{
    public static function from(string $tla): string
    {
        return match ($tla) {
            'ALB' => '🇦🇱',
            'ARG' => '🇦🇷',
            'AUS' => '🇦🇺',
            'AUT' => '🇦🇹',
            'BEL' => '🇧🇪',
            'BRA' => '🇧🇷',
            'CAN' => '🇨🇦',
            'CMR' => '🇨🇲',
            'CRC' => '🇨🇷',
            'CRO' => '🇭🇷',
            'CZE' => '🇨🇿',
            'DEN' => '🇩🇰',
            'ECU' => '🇪🇨',
            'ENG' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
            'ESP' => '🇪🇸',
            'FIN' => '🇫🇮',
            'FRA' => '🇫🇷',
            'GEO' => '🇬🇪',
            'GER' => '🇩🇪',
            'GHA' => '🇬🇭',
            'HUN' => '🇭🇺',
            'IRN' => '🇮🇷',
            'ITA' => '🇮🇹',
            'JPN' => '🇯🇵',
            'KOR' => '🇰🇷',
            'KSA' => '🇸🇦',
            'MAR' => '🇲🇦',
            'MEX' => '🇲🇽',
            'NED' => '🇳🇱',
            'POL' => '🇵🇱',
            'POR' => '🇵🇹',
            'QAT' => '🇶🇦',
            'ROU' => '🇷🇴',
            'RUS' => '🇷🇺',
            'SCO' => '🏴󠁧󠁢󠁳󠁣󠁴󠁿',
            'SEN' => '🇸🇳',
            'SRB' => '🇷🇸',
            'SUI' => '🇨🇭',
            'SVK' => '🇸🇰',
            'SVN' => '🇸🇮',
            'SWE' => '🇸🇪',
            'TUN' => '🇹🇳',
            'TUR' => '🇹🇷',
            'UKR' => '🇺🇦',
            'URU' => '🇺🇾',
            'USA' => '🇺🇸',
            'WAL' => '🏴󠁧󠁢󠁷󠁬󠁳󠁿',
        };
    }
}
