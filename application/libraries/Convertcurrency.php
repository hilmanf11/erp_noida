<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Convertcurrency
{
    public function convertCurrencyToWords($amount, $currency) {
        $words = "";
    
        $currencyNames = array(
            "USD" => "Dollar",
            "EUR" => "Euro",
            "JPY" => "Yen",
            "IDR" => "Rupiah",
            "INR" => "Rupee",
            // Add other currencies as needed
        );
    
        // $words .= $this->numberToWords($amount, $currency) . " " . $currencyNames[$currency];
        $words .= $this->numberToWords($amount, $currency);
    
        return $words;
    }
    
    function numberToWords($number, $currency) {
        $words = "";
        $number = abs($number);
        $decimal = round($number - intval($number), 2) * 100;

        if($currency == "INR"){
            $no = floor($number);
            $point = round($number - $no, 2) * 100;
            $hundred = null;
            $digits_1 = strlen($no);
            $i = 0;
            $str = [];
            $words = [
                0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
                7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven', 12 => 'twelve',
                13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen',
                18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty', 30 => 'thirty', 40 => 'forty',
                50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety'
            ];
            $digits = ['', 'hundred', 'thousand', 'lakh', 'crore'];
            while ($i < $digits_1) {
                $divider = ($i == 2) ? 10 : 100;
                $number = floor($no % $divider);
                $no = floor($no / $divider);
                $i += ($divider == 10) ? 1 : 2;
                if ($number) {
                    $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                    $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                    $str[] = ($number < 21) ? $words[$number] .
                        " " . $digits[$counter] . $plural . " " . $hundred :
                        $words[floor($number / 10) * 10] . " " . $words[$number % 10] .
                        " " . $digits[$counter] . $plural . " " . $hundred;
                } else $str[] = null;
            }
            $str = array_reverse($str);
            $result = implode('', $str);
            $paise = ($point) ? " and " . $words[$point / 10] . " " . $words[$point % 10] . " paise only" : '';
            return ($result ? $result . " rupees " : '') . $paise;
        }else{
            $numberInWords = array(
                0 => 'Zero',
                1 => 'One',
                2 => 'Two',
                3 => 'Three',
                4 => 'Four',
                5 => 'Five',
                6 => 'Six',
                7 => 'Seven',
                8 => 'Eight',
                9 => 'Nine',
                10 => 'Ten',
                11 => 'Eleven',
                12 => 'Twelve',
                13 => 'Thirteen',
                14 => 'Fourteen',
                15 => 'Fifteen',
                16 => 'Sixteen',
                17 => 'Seventeen',
                18 => 'Eighteen',
                19 => 'Nineteen',
                20 => 'Twenty',
                30 => 'Thirty',
                40 => 'Forty',
                50 => 'Fifty',
                60 => 'Sixty',
                70 => 'Seventy',
                80 => 'Eighty',
                90 => 'Ninety',
                100 => 'Hundred',
                1000 => 'Thousand',
                1000000 => 'Million',
                1000000000 => 'Billion'
            );

            if ($number < 20) {
                $words = $numberInWords[$number];
            } elseif ($number < 100) {
                $words = $numberInWords[10 * floor($number / 10)];
                $remainder = $number % 10;
                if ($remainder > 0) {
                    $words .= " " . $numberInWords[$remainder];
                }
            } elseif ($number < 1000) {
                $words = $numberInWords[floor($number / 100)] . " " . $numberInWords[100];
                $remainder = $number % 100;
                if ($remainder > 0) {
                    $words .= " and " . $this->numberToWords($remainder, $currency);
                }
            } elseif ($number < 1000000) {
                $words = $this->numberToWords(floor($number / 1000), $currency) . " " . $numberInWords[1000];
                $remainder = $number % 1000;
                if ($remainder > 0) {
                    $words .= ", " . $this->numberToWords($remainder, $currency);
                }
            } elseif ($number < 1000000000) {
                $words = $this->numberToWords(floor($number / 1000000), $currency) . " " . $numberInWords[1000000];
                $remainder = $number % 1000000;
                if ($remainder > 0) {
                    $words .= ", " . $this->numberToWords($remainder, $currency);
                }
            } else {
                $words = "Value is too large";
            }
        
            if ($decimal > 0) {
                $words .= " Point " . $this->numberToWords($decimal, $currency);
            }
        }
    
        return $words;
    }
}
