<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcNonAcCommitteeFiles extends Model
{
	use SoftDeletes;
    protected $table = 'committee_files';

    protected $fillable = [
         'ac_non_ac_id'
    ];
}
