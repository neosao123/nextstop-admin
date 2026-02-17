<?php

namespace App\Classes;

class Helper
{
    function numberToCurrency($number)
    {
        $checkMinusVal = explode('-', $number)[0];
        $checkMinus = $final = '';
        $allStr = explode('.', $number);
        if ($checkMinusVal == '') {
            $checkMinus = '-';
            $allStr = explode('.', explode('-', $number)[1]);
        }
        $str = $allStr[0];
        $length = strlen($str);
        $count = $first = 0;
        for ($i = $length; $i >= 0; $i--) {
            if ($count == 3 && $first == 0) {
                $final .= $str[$i];
                if ($str[$i + 1] != '') {
                    $final .= ',';
                }
                $count = 0;
                $first = 1;
            } elseif ($count == 2 && $first == 1) {
                if (($i - 1) < 0) {
                    $final .= $str[$i];
                } else {
                    $final .= $str[$i] . ',';
                }
                $count = 0;
            } else {
                $final .= $str[$i];
            }
            $count++;
        }
        $final = strrev($final);
        if (array_key_exists("1", $allStr)) {
            $decimalVal = $allStr[1][0];
            if (!empty($allStr[1][1])) {
                $decimalVal .= $allStr[1][1];
            } else {
                $decimalVal .= 0;
            }
            if ($allStr[1][2] >= 5) {
                $decimalVal++;
            }
            $final .= '.' . $decimalVal;
        }
        return $checkMinus . $final;
    }
}
