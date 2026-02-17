<?php

namespace App\Classes;

class FormulaSection
{

    public function tagFormulaFields($config)
    {
        $i = 0;

        $letters = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
            'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC',
            'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK',
            'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS',
            'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
            'BA', 'BB', 'BC', 'BD'
        ];

        $newConfig = [];

        foreach ($config as $k => $v) {
            $f = $v;
            if ($f['hasCalc']) {
                $f['formula_tag'] = $letters[$i];
                $i++;
            }
            array_push($newConfig, $f);
        }

        return $newConfig;
    }
}
