<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cartao extends Model {
    protected $connection = 'mysql';
    protected $table = 'cartao';
    public $timestamps = false;
}