<?php
namespace App\Models;

class Helper {

    static function format($valor) {
        return @number_format(str_replace(",","",$valor), 2, ',', '');
    }

    static function saveMes($sobra, $mensal, $indice_mes) {
        if ($indice_mes == 1) {
            return $sobra-($mensal/4*$indice_mes);
        }

        return $sobra-340*$indice_mes;
    }
    

}