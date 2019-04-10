<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model {
    protected $connection = 'mysql';
    protected $table = 'movimentacao';
    public $timestamps = false;
}