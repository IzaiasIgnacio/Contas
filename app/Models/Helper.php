<?php
namespace App\Models;

class Helper {

    public function format($valor) {
        return @number_format(str_replace(",","",$valor), 2, ',', '');
    }
    
    public function getTotalAtual() {
        return Consolidado::get('nubank')+Consolidado::get('savings');
    }

}