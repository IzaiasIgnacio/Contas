<?php
namespace App\Models;

class Status {

    static function get() {
        return [
            'normal',
            'definido',
            'pago'
        ];
    }

}