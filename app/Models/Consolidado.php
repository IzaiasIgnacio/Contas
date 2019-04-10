<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consolidado extends Model {
    protected $connection = 'mysql';
    protected $table = 'consolidado';
    public $timestamps = false;
}