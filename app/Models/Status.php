<?php
namespace App\Models;

class Status {

    static function get() {
        return [
            'planejado',
            'definido',
            'pago'
        ];
    }

}