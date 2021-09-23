<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consolidado extends Model {
    protected $connection = 'mysql';
    protected $table = 'consolidado';
    public $timestamps = false;

    public static function get($tipo) {
        return Consolidado::where('nome', $tipo)->first()->valor;
    }
}