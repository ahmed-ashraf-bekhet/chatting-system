<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Join extends Model
{
    protected $table='joins';
    public $primaryKey='id';
    public $timestamps=false;
}
