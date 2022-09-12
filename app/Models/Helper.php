<?php
namespace App\Models;

class Helper {

    public function format($valor) {
        return @number_format(str_replace(",","",$valor), 2, ',', '');
    }
    
    public function getTotalSavingsAtual() {
        return Consolidado::get('bmg');
    }

    public function getTotal() {
        return $this->format(Consolidado::where('totais', 1)->sum('valor'));
    }

    public function getTotalAtual() {
        return $this->format(Consolidado::where('atual', 1)->sum('valor'));
    }

}