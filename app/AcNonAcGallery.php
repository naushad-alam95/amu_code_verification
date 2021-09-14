<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcNonAcGallery extends Model
{
	use SoftDeletes;
    protected $table = 'ac_non_ac_gallery';

    protected $fillable = [
         'ac_non_ac_id'
    ];
}
