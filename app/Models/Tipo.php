<?php
namespace App\Models;

class Tipo {

    static function get() {
        return [
            'gasto',
            'renda',
            'terceiros'
        ];
    }

}