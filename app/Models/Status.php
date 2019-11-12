<?php
namespace App\Models;

class Status {

    static function get() {
        return [
            'definido',
            'planejado',
            'pago'
        ];
    }

}