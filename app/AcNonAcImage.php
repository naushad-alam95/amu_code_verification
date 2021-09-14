<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcNonAcImage extends Model
{
     use SoftDeletes;
     protected $table = 'ac_non_ac_image';

     protected $fillable = [
         'ac_non_ac_id'
    ];
}
