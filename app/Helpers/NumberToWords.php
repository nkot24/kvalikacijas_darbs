<?php

namespace App\Helpers;

class NumberToWords
{
    protected static $ones = [
        0 => "nulle", 1 => "viens", 2 => "divi", 3 => "trīs", 4 => "četri",
        5 => "pieci", 6 => "seši", 7 => "septiņi", 8 => "astoņi", 9 => "deviņi",
        10 => "desmit", 11 => "vienpadsmit", 12 => "divpadsmit", 13 => "trīspadsmit",
        14 => "četrpadsmit", 15 => "piecpadsmit", 16 => "sešpadsmit",
        17 => "septiņpadsmit", 18 => "astoņpadsmit", 19 => "deviņpadsmit"
    ];

    protected static $tens = [
        2 => "divdesmit", 3 => "trīsdesmit", 4 => "četrdesmit",
        5 => "piecdesmit", 6 => "sešdesmit", 7 => "septiņdesmit",
        8 => "astoņdesmit", 9 => "deviņdesmit"
    ];

    protected static $thousands = [
        "", "tūkstotis", "miljons", "miljards"
    ];

    /**
     * Konvertē skaitli vārdos (eiro skaitļiem u.c.)
     */
    public static function convert($number)
    {
        if ($number == 0) {
            return ucfirst(self::$ones[0]);
        }

        $parts = [];
        $chunkCount = 0;

        while ($number > 0) {
            $chunk = $number % 1000;
            if ($chunk) {
                $chunkWords = self::convertChunk($chunk);

                if ($chunkCount > 0) {
                    if ($chunk == 1) {
                        $chunkWords .= " " . self::$thousands[$chunkCount];
                    } else {
                        if ($chunkCount == 1) {
                            $chunkWords .= " tūkstoši";
                        } elseif ($chunkCount == 2) {
                            $chunkWords .= " miljoni";
                        } elseif ($chunkCount == 3) {
                            $chunkWords .= " miljardi";
                        }
                    }
                }

                array_unshift($parts, $chunkWords);
            }
            $number = intdiv($number, 1000);
            $chunkCount++;
        }

        return ucfirst(implode(" ", $parts));
    }

    /**
     * Konvertē 0-999 bloku
     */
    protected static function convertChunk($number)
    {
        $words = [];

        if ($number >= 100) {
            $hundreds = intdiv($number, 100);
            if ($hundreds == 1) {
                $words[] = "simts";
            } else {
                $words[] = self::$ones[$hundreds] . " simti";
            }
            $number %= 100;
        }

        if ($number >= 20) {
            $tens = intdiv($number, 10);
            $words[] = self::$tens[$tens];
            $number %= 10;
        }

        if ($number > 0) {
            $words[] = self::$ones[$number];
        }

        return implode(" ", $words);
    }

    /**
     * Konvertē naudas summu eiro + centi
     */
    public static function convertMoney($amount)
    {
        $euros = floor($amount);
        $cents = round(($amount - $euros) * 100);

        // eiro locījums
        if ($euros == 1) {
            $words = self::convert($euros) . " eiro";
        } else {
            $words = self::convert($euros) . " eiro";
        }

        // centu locījums
        if ($cents > 0) {
            if ($cents == 1) {
                $words .= " un " . self::convert($cents) . " cents";
            } else {
                $words .= " un " . self::convert($cents) . " centi";
            }
        }

        return ucfirst($words);
    }

}
